<?php

namespace WorldOfTanks\BattleBalancer\Balance;

use WorldOfTanks\BattleBalancer\Model\Player;
use WorldOfTanks\BattleBalancer\Model\PlayerTank;

class DummyBalanceWeightCalculator implements BalanceWeightCalculatorInterface
{
    private $currWeight = 100000;

    /**
     * {@inheritdoc}
     */
    public function compute(Player $player, PlayerTank $playerTank)
    {
        $this->currWeight -= mt_rand(0, 10);

        return $this->currWeight;
    }
} 