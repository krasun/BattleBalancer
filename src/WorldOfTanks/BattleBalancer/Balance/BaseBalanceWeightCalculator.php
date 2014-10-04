<?php

namespace WorldOfTanks\BattleBalancer\Balance;

use WorldOfTanks\Api\Client as ApiClient;
use WorldOfTanks\Api\Model\TankRegistry;
use WorldOfTanks\Api\Model\TankStats;
use WorldOfTanks\BattleBalancer\Model\Player;
use WorldOfTanks\BattleBalancer\Model\PlayerTank;

abstract class BaseBalanceWeightCalculator implements BalanceWeightCalculatorInterface
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var TankRegistry
     */
    private $tankRegistry;

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
    public function compute(PlayerTank $playerTank)
    {
        if (empty($this->tankRegistry)) {
            $this->tankRegistry = $this->apiClient->loadTankRegistry();
        }

        return $this->computeWeight(
            $playerTank->getMarkOfMastery(),
            $playerTank->getTank()->getMaxHealth(),
            $playerTank->getTank()->getGunDamageMin(),
            $playerTank->getTank()->getGunDamageMax(),
            TankStats::MAX_MARK_OF_MASTERY,
            $this->tankRegistry->computeOverallMinHealth(),
            $this->tankRegistry->computeOverallMaxHealth(),
            $this->tankRegistry->computeOverallGunDamageMin(),
            $this->tankRegistry->computeOverallGunDamageMax()
        );
    }

    /**
     * @param int $markOfMastery
     * @param int $maxHealth
     * @param int $gunDamageMin
     * @param int $gunDamageMax
     * @param int $maxMarkOfMastery
     * @param int $overallMinHealth
     * @param int $overallMaxHealth
     * @param int $overallGunDamageMin
     * @param int $overallGunDamageMax
     *
     * @return float
     */
    abstract function computeWeight(
        $markOfMastery,
        $maxHealth,
        $gunDamageMin,
        $gunDamageMax,
        $maxMarkOfMastery,
        $overallMinHealth,
        $overallMaxHealth,
        $overallGunDamageMin,
        $overallGunDamageMax
    );

    /**
     * Normalizes by scaling between 0 and 1.
     *
     * @param int $val
     * @param int $min
     * @param int $max
     *
     * @return float Normalized value
     */
    protected function norm($val, $min, $max)
    {
        return ($val - $min) / ($max - $min);
    }
}
