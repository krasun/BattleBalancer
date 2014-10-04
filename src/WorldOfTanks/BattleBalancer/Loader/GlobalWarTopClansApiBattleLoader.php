<?php

namespace WorldOfTanks\BattleBalancer\Loader;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use WorldOfTanks\Api\Client as ApiClient;
use WorldOfTanks\Api\Model\Clan;
use WorldOfTanks\Api\Model\ClanMember;
use WorldOfTanks\Api\Model\TankInfo;
use WorldOfTanks\Api\Model\TankStats;
use WorldOfTanks\BattleBalancer\Loader\Event\LoadedTeamsEvent;
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
     * We need only two teams.
     */
    const TEAM_NUM = 2;

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param ApiClient $apiClient
     */
    public function __construct(ApiClient $apiClient, EventDispatcherInterface $dispatcher)
    {
        $this->apiClient = $apiClient;
        $this->dispatcher = $dispatcher;
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
        $this->dispatcher->dispatch(Events::BEFORE_LOAD_TEAMS);
        $clans = $this->loadClans($battleConfig);
        $this->dispatcher->dispatch(Events::TEAMS_LOADED, new LoadedTeamsEvent(count($clans)));

        if (count($clans) < self::TEAM_NUM) {
            throw new LoaderException(sprintf(
                'At least two commands with %s members need to be loaded from API',
                $battleConfig->getRequiredMemberNumPerTeam()
            ));
        }

        // Try to load at least two teams with required players number and with players
        // who at least have one required tank
        $satisfiedClans = [];
        $processedClanIds = [];
        $satisfiedTeamPlayers = [];
        while ((count($satisfiedTeamPlayers) < self::TEAM_NUM) && (count($processedClanIds) < count($clans))) {
            // Take some random clans
            $randomClans = $this->takeNotProcessedRandomClans($clans, self::TEAM_NUM, $processedClanIds);
            $teamPlayers = $this->loadTeamPlayers($battleConfig, array_keys($randomClans));
            // Find satisfied clans
            foreach ($teamPlayers as $clanId => $players) {
                if (count($players) >= $battleConfig->getRequiredMemberNumPerTeam()) {
                    $satisfiedClans[$clanId] = $clans[$clanId];
                    $satisfiedTeamPlayers[$clanId] = $players;
                }
                $processedClanIds[$clanId] = true;
            }
        }

        if (count($satisfiedClans) < self::TEAM_NUM) {
            throw new LoaderException(sprintf(
                'Can`t find two clans with %s players who have at least one required tank',
                $battleConfig->getRequiredMemberNumPerTeam()
            ));
        }

        /** @var Clan $clanA */
        /** @var Clan $clanB */
        @ list($clanA, $clanB) = array_values($satisfiedClans);

        return [
            new Team(new TeamInfo($clanA->getId()), $satisfiedTeamPlayers[$clanA->getId()]),
            new Team(new TeamInfo($clanB->getId()), $satisfiedTeamPlayers[$clanB->getId()])
        ];
    }

    /**
     * Loads team players.
     *
     * @param BattleConfig $battleConfig
     * @param int $clanIds
     *
     * @return array
     */
    private function loadTeamPlayers(BattleConfig $battleConfig, $clanIds)
    {
        $this->dispatcher->dispatch(Events::BEFORE_LOAD_TEAM_PLAYERS);
        $groupedClanMembers = $this->apiClient->loadClanMembers($clanIds);
        $this->dispatcher->dispatch(Events::TEAM_PLAYERS_LOADED);

        $accountIds = $this->flattenMap($groupedClanMembers, function (ClanMember $member) {
            return $member->getAccountId();
        });
        $playerTanks = $this->loadPlayerTanks($battleConfig, $accountIds);

        $players = [];
        /** @var ClanMember $clanMember */
        foreach ($groupedClanMembers as $clanId => $clanMembers) {
            $players[$clanId] = [];
            foreach ($clanMembers as $clanMember) {
                $tanks = $playerTanks[$clanMember->getAccountId()];
                if (count($tanks) == 0) {
                    continue;
                }
                $players[$clanId][] = new Player(
                    $clanMember->getAccountId(),
                    $tanks
                );
            }
        }

        return $players;
    }

    /**
     * Loads player tanks.
     *
     * @param BattleConfig $battleConfig
     * @param int[] $accountIds
     *
     * @return array
     */
    private function loadPlayerTanks(BattleConfig $battleConfig, array $accountIds)
    {
        $this->dispatcher->dispatch(Events::BEFORE_LOAD_PLAYER_TANKS);
        $groupedTankStats = $this->apiClient->loadTankStats($accountIds);
        $this->dispatcher->dispatch(Events::PLAYER_TANKS_LOADED);

        $this->dispatcher->dispatch(Events::BEFORE_LOAD_TANKS);
        $tankIds = $this->flattenMap($groupedTankStats, function (TankStats $tankStats) {
            return $tankStats->getTankId();
        });
        $tankInfos = $this->apiClient->loadTankInfo($tankIds);
        $this->dispatcher->dispatch(Events::TANKS_LOADED);

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

                    if ($tankInfo->getLevel() < $battleConfig->getMinTankLevel() ||
                        $tankInfo->getLevel() > $battleConfig->getMaxTankLevel()) {
                        continue;
                    }

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
     * @param BattleConfig $battleConfig
     *
     * @return Clan[]
     */
    private function loadClans(BattleConfig $battleConfig)
    {
        $clans = $this->apiClient->loadGlobalWarTopClans('globalmap', 'provinces_count');

        $suitableClans = [];
        foreach ($clans as $clan) {
            if ($clan->getMembersCount() >= $battleConfig->getRequiredMemberNumPerTeam()) {
                $suitableClans[$clan->getId()] = $clan;
            }
        }

        return $suitableClans;
    }

    /**
     * @param Clan[] $clans
     * @param int $clanNum
     * @param array $processedClanIds Key is clan id
     *
     * @return Clan[] Returns empty array if it can satisfy requirements
     */
    private function takeNotProcessedRandomClans($clans, $clanNum, $processedClanIds)
    {
        if ((count($clans) - count($processedClanIds) < $clanNum)) {
            return [];
        }

        $randomClans = [];
        $clanIds = array_keys($clans);

        // No need for check for count($clans) - count($processedClans) >= $clanNum and
        while (count($randomClans) != $clanNum) {
            $randomIndex = mt_rand(0, count($clanIds) - 1);
            $randomClanId = $clanIds[$randomIndex];
            if (isset($processedClanIds[$randomClanId])) {
                continue;
            }

            $randomClans[$randomClanId] = $clans[$randomClanId];
        }

        return $randomClans;
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