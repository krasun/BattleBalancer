<?php

namespace WorldOfTanks\BattleBalancer\Model;

class BattleInfo 
{
    /**
     * @var
     */
    private $memberNumPerTeam;

    /**
     * @var
     */
    private $minTankLevel;

    /**
     * @var
     */
    private $maxTankLevel;

    /**
     * @param int $memberNumPerTeam
     * @param int $minTankLevel
     * @param int $maxTankLevel
     */
    public function __construct($memberNumPerTeam, $minTankLevel, $maxTankLevel)
    {
        $this->memberNumPerTeam = $memberNumPerTeam;
        $this->minTankLevel = $minTankLevel;
        $this->maxTankLevel = $maxTankLevel;
    }
} 