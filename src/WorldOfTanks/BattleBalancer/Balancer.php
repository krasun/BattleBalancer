<?php

namespace WorldOfTanks\BattleBalancer;

use WorldOfTanks\BattleBalancer\Balance\BalanceWeightCalculatorInterface;
use WorldOfTanks\BattleBalancer\Loader\BattleConfig;
use WorldOfTanks\BattleBalancer\Model\BalanceWeight;
use WorldOfTanks\BattleBalancer\Model\Battle;
use WorldOfTanks\BattleBalancer\Model\Player;
use WorldOfTanks\BattleBalancer\Model\PlayerTank;
use WorldOfTanks\BattleBalancer\Model\Team;

class Balancer
{
    /**
     * @var BalanceWeightCalculatorInterface
     */
    private $weightCalculator;

    /**
     * @param BalanceWeightCalculatorInterface $weightCalculator
     */
    public function __construct(BalanceWeightCalculatorInterface $weightCalculator)
    {
        $this->weightCalculator = $weightCalculator;
    }

    /**
     * Balances battle and returns balanced battle.
     *
     * @param BattleConfig $battleConfig
     * @param Battle $battle
     *
     * @return Battle Balanced battle
     */
    public function balance(BattleConfig $battleConfig, Battle $battle)
    {
        $teamAWeights = $this->computeWeights($battle->getTeamA());
        $teamBWeights = $this->computeWeights($battle->getTeamB());

        // Sort all computed weights in descending order
        $weights = array_merge($teamAWeights, $teamBWeights);
        usort($weights, function (BalanceWeight $weightLeft, BalanceWeight $weightRight) {
            return ($weightRight->getWeight() - $weightLeft->getWeight());
        });

        $teamPlayingPlayerNum = [];
        $processedPlayers  = [];
        /** @var BalanceWeight $weight */
        foreach ($weights as $weight) {
            $teamId = $weight->getTeam()->getTeamInfo()->getId();
            if (! isset($teamPlayingPlayerNum[$teamId])) {
                $teamPlayingPlayerNum[$teamId] = 0;
            }

            if ($teamPlayingPlayerNum[$teamId] < $battleConfig->getRequiredMemberNumPerTeam()) {
                $player = $weight->getPlayer();
                if (isset($processedPlayers[$player->getId()])) {
                    continue;
                }

                $player->setWillPlay(true);
                $weight->getPlayerTank()->setWillPlay(true);

                $processedPlayers[$player->getId()] = true;
                $teamPlayingPlayerNum[$teamId]++;
            }
        }
    }

    /**
     * @param Player[] $players
     *
     * @return BalanceWeight[]
     */
    protected function computeWeights(Team $team)
    {
        $weights = [];
        /** @var Player $player */
        foreach ($team->getPlayers() as $player) {
            /** @var PlayerTank $playerTank */
            foreach ($player->getTanks() as $playerTank) {
                $weights[] = new BalanceWeight(
                    $this->weightCalculator->compute($playerTank),
                    $team,
                    $player,
                    $playerTank
                );
            }
        }

        return $weights;
    }
} 