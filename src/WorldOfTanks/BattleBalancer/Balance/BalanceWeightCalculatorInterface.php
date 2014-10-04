<?php

namespace WorldOfTanks\BattleBalancer\Balance;

use WorldOfTanks\BattleBalancer\Model\Player;
use WorldOfTanks\BattleBalancer\Model\PlayerTank;

interface BalanceWeightCalculatorInterface
{
    /**
     * Computes balance weight for player and his tank.
     *
     * @param Player $player
     *
     * @return float
     */
    function compute(PlayerTank $playerTank);
} 