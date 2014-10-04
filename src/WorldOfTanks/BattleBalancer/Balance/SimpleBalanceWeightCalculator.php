<?php

namespace WorldOfTanks\BattleBalancer\Balance;

use WorldOfTanks\Api\Client as ApiClient;

use WorldOfTanks\BattleBalancer\Model\Player;
use WorldOfTanks\BattleBalancer\Model\PlayerTank;

class SimpleBalanceWeightCalculator implements BalanceWeightCalculatorInterface
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
    public function compute(Player $player, PlayerTank $playerTank)
    {
        if (! isset($this->currWeight)) {
            $this->currWeight = 100000;
        }
        $this->currWeight -= mt_rand(0, 10);

        return $this->currWeight;
    }
}