<?php

namespace WorldOfTanks\BattleBalancer\Balance;

use WorldOfTanks\BattleBalancer\Model\PlayerTank;

class SimpleBalanceWeightCalculator extends BaseBalanceWeightCalculator
{
    /**
     * {@inheritdoc}
     */
    public function computeWeight(
        $markOfMastery,
        $maxHealth,
        $gunDamageMin,
        $gunDamageMax,
        $maxMarkOfMastery,
        $overallMinHealth,
        $overallMaxHealth,
        $overallGunDamageMin,
        $overallGunDamageMax
    )
    {
        // All impact weights must be between 0 and 1
        $healthImpactWeight = 0.85;
        $gunDamageImpactWeight = 0.9;
        $markOfMasteryImpactWeight = 0.1;

        // Scale all values to 0..1
        $gunDamageMedium = ($gunDamageMin + $gunDamageMax) / 2;
        $gunDamageNorm = $this->norm($gunDamageMedium, $overallGunDamageMin, $overallGunDamageMax);
        $healthNorm = $this->norm($maxHealth, $overallMinHealth, $overallMaxHealth);
        $markOfMasteryNorm = $this->norm($markOfMastery, 0, $maxMarkOfMastery);

        // Calculate weight with impact
        $weight =
            $healthNorm * $healthImpactWeight
            + $gunDamageNorm * $gunDamageImpactWeight
            + $markOfMasteryNorm * $markOfMasteryImpactWeight
        ;

        // Scale to 0..1
        $weightNorm = $this->norm($weight, 0, $healthImpactWeight + $gunDamageImpactWeight + $markOfMasteryImpactWeight);

        return round($weightNorm, 3);
    }
}