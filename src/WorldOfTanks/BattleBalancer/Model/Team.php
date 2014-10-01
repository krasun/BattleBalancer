<?php

namespace WorldOfTanks\BattleBalancer\Model;

class Team 
{
    /**
     * @var TeamInfo
     */
    private $teamInfo;

    /**
     * @var array|Player[]
     */
    private $players;

    /**
     * @param TeamInfo $teamInfo
     * @param Player[]|array $players
     */
    public function __construct(TeamInfo $teamInfo, array $players = [])
    {
        $this->teamInfo = $teamInfo;
        $this->players = $players;
    }

    /**
     * @return TeamInfo
     */
    public function getTeamInfo()
    {
        return $this->teamInfo;
    }

    /**
     * @return array|Player[]
     */
    public function getPlayers()
    {
        return $this->players;
    }
}