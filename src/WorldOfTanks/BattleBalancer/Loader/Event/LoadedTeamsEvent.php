<?php

namespace WorldOfTanks\BattleBalancer\Loader\Event;

use Symfony\Component\EventDispatcher\Event;

class LoadedTeamsEvent extends Event
{
    /**
     * @var int
     */
    private $teamNum;

    /**
     * @param int $teamNum
     */
    public function __construct($teamNum)
    {
        $this->teamNum = $teamNum;
    }

    /**
     * @return int
     */
    public function getTeamNum()
    {
        return $this->teamNum;
    }
}