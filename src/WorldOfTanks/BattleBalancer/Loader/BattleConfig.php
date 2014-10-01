<?php

namespace WorldOfTanks\BattleBalancer\Loader;

class BattleConfig
{
    /**
     * @var int
     */
    private $requiredMemberNumPerTeam;

    /**
     * @var int
     */
    private $minTankLevel;

    /**
     * @var int
     */
    private $maxTankLevel;

    /**
     * @param int $requiredMemberNumPerTeam
     *
     * @return BattleConfig
     */
    public function setRequiredMemberNumPerTeam($requiredMemberNumPerTeam)
    {
        $this->requiredMemberNumPerTeam = $requiredMemberNumPerTeam;

        return $this;
    }

    /**
     * @return int
     */
    public function getRequiredMemberNumPerTeam()
    {
        return $this->requiredMemberNumPerTeam;
    }

    /**
     * @param int $minTankLevel
     *
     * @return BattleConfig
     */
    public function setMinTankLevel($minTankLevel)
    {
        $this->minTankLevel = $minTankLevel;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinTankLevel()
    {
        return $this->minTankLevel;
    }

    /**
     * @param int $maxTankLevel
     *
     * @return BattleConfig
     */
    public function setMaxTankLevel($maxTankLevel)
    {
        $this->maxTankLevel = $maxTankLevel;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxTankLevel()
    {
        return $this->maxTankLevel;
    }
}