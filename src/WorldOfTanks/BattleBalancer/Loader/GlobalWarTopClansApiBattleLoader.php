<?php

namespace WorldOfTanks\BattleBalancer\Loader;

use WorldOfTanks\Api\Client as ApiClient;
use WorldOfTanks\Api\Model\Clan;
use WorldOfTanks\Api\Model\ClanMember;
use WorldOfTanks\Api\Model\TankInfo;
use WorldOfTanks\Api\Model\TankStats;
use WorldOfTanks\BattleBalancer\Model\Battle;
use WorldOfTanks\BattleBalancer\Model\BattleInfo;
use WorldOfTanks\BattleBalancer\Model\Player;
use WorldOfTanks\BattleBalancer\Model\PlayerTank;
use WorldOfTanks\BattleBalancer\Model\Tank;
use WorldOfTanks\BattleBalancer\Model\Team;
use WorldOfTanks\BattleBalancer\Model\TeamInfo;

class GlobalWarTopClansApiBattleLoader implements BattleLoaderInterface
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @param ApiClient $apiClient
     */
    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * {@inheritdoc}
     */
    public function load(BattleConfig $battleConfig)
    {
        $teams = $this->loadTeams($battleConfig);
        @ list($teamA, $teamB) = $teams;

        $battleInfo = new BattleInfo(
            $battleConfig->getRequiredMemberNumPerTeam(),
            $battleConfig->getMinTankLevel(),
            $battleConfig->getMaxTankLevel()
        );
        $battle = new Battle($battleInfo, $teamA, $teamB);

        return $battle;
    }

    /**
     * Loads teams based on battle configuration.
     *
     * @param BattleConfig $battleConfig
     *
     * @return Team[]
     */
    private function loadTeams(BattleConfig $battleConfig)
    {
        $requiredTeamNum = 2;
        $requiredMemberNum = $battleConfig->getRequiredMemberNumPerTeam();
        $clans = $this->loadClans($requiredTeamNum, $requiredMemberNum);
        /** @var Clan $clanA */
        /** @var Clan $clanB */
        @ list($clanA, $clanB) = array_values($clans);

        $teamPlayers = $this->loadTeamPlayers([$clanA->getId(), $clanB->getId()]);

        return [
            new Team(new TeamInfo($clanA->getId()), $teamPlayers[$clanA->getId()]),
            new Team(new TeamInfo($clanB->getId()), $teamPlayers[$clanB->getId()])
        ];
    }

    /**
     * Loads team players.
     *
     * @param Player[] $clanIds
     *
     * @return array
     */
    private function loadTeamPlayers($clanIds)
    {
        $groupedClanMembers = $this->apiClient->loadClanMembers($clanIds);

        $accountIds = $this->flattenMap($groupedClanMembers, function (ClanMember $member) {
            return $member->getAccountId();
        });
        $playerTanks = $this->loadPlayerTanks($accountIds);

        $players = [];
        /** @var ClanMember $clanMember */
        foreach ($groupedClanMembers as $clanId => $clanMembers) {
            $players[$clanId] = [];
            foreach ($clanMembers as $clanMember) {
                $players[$clanId][] = new Player(
                    $clanMember->getAccountId(),
                    $playerTanks[$clanMember->getAccountId()]
                );
            }
        }

        return $players;
    }

    /**
     * Loads player tanks.
     *
     * @param int[] $accountIds
     *
     * @return array
     */
    private function loadPlayerTanks(array $accountIds)
    {
        $groupedTankStats = $this->apiClient->loadTankStats($accountIds);
        $tankIds = $this->flattenMap($groupedTankStats, function (TankStats $tankStats) {
            return $tankStats->getTankId();
        });

        $tankInfos = $this->apiClient->loadTankInfo($tankIds);

        $playerTanks = [];
        $tanks = [];
        foreach ($groupedTankStats as $accountId => $accountTankStats) {

            $playerTanks[$accountId] = [];
            /** @var TankStats $tankStats */
            foreach ($accountTankStats as $tankStats) {
                $tankId = $tankStats->getTankId();
                if (! isset($tanks[$tankId])) {
                    /** @var TankInfo $tankInfo */
                    $tankInfo = $tankInfos[$tankId];
                    $tanks[$tankId] = new Tank(
                        $tankInfo->getTankId(),
                        $tankInfo->getLevel(),
                        $tankInfo->getMaxHealth(),
                        $tankInfo->getGunDamageMin(),
                        $tankInfo->getGunDamageMax()
                    );
                }

                $playerTanks[$accountId][] = new PlayerTank(
                    $tankStats->getMarkOfMastery(),
                    $tanks[$tankId]
                );
            }
        }

        return $playerTanks;
    }

    /**
     * @param int $requiredClanNum
     * @param int $requiredMemberNum
     *
     * @return Clan[]
     */
    private function loadClans($requiredClanNum, $requiredMemberNum)
    {
        $clans = $this->apiClient->loadGlobalWarTopClans('globalmap', 'provinces_count');
        $allowedClans = array_filter($clans, function (Clan $clan) use ($requiredMemberNum) {
            return $clan->getMembersCount() >= $requiredMemberNum;
        });
        if (count($allowedClans) < $requiredClanNum) {
            throw new \RuntimeException('Required number of clans with required number of members not found');
        }

        $selectedClans = [];
        $allowedCount = count($allowedClans);
        while (count($selectedClans) != $requiredClanNum) {
            $randomIndex = mt_rand(0, $allowedCount - 1);
            if (! isset($selectedClans[$randomIndex])) {
                $selectedClans[$randomIndex] = $allowedClans[$randomIndex];
            }
        }

        return $selectedClans;
    }

    /**
     * @param array $elements
     * @param callable $callback
     *
     * @return array
     */
    private function flattenMap(array $elements, callable $callback)
    {
        $mapped = array();
        array_walk_recursive($elements, function($el) use (&$mapped, $callback) { $mapped[] = $callback($el); });

        return $mapped;
    }
}