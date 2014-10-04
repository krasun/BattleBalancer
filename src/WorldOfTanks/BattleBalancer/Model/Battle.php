<?php

namespace WorldOfTanks\BattleBalancer\Model;

class Battle
{
    /**
     * @var Team
     */
    private $teamA;

    /**
     * @var Team
     */
    private $teamB;

    /**
     * @var BattleInfo
     */
    private $battleInfo;

    /**
     * @param BattleInfo $battleInfo
     * @param Team $teamA
     * @param Team $teamB
     */
    public function __construct(BattleInfo $battleInfo, Team $teamA, Team $teamB)
    {
        $this->teamA = $teamA;
        $this->teamB = $teamB;
        $this->battleInfo = $battleInfo;
    }

    /**
     * @return Team
     */
    public function getTeamA()
    {
        return $this->teamA;
    }

    /**
     * @return Team
     */
    public function getTeamB()
    {
        return $this->teamB;
    }

    /**
     * @return BattleInfo
     */
    public function getBattleInfo()
    {
        return $this->battleInfo;
    }
} 