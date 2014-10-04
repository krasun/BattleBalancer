<?php

namespace WorldOfTanks\BattleBalancer\Model;

class BalanceWeight 
{
    /**
     * @var float
     */
    private $weight;

    /**
     * @var Team
     */
    private $team;

    /**
     * @var PlayerTank
     */
    private $player;

    /**
     * @var Tank
     */
    private $playerTank;

    /**
     * @param float $weight
     * @param Team $team
     * @param Player $player
     * @param PlayerTank $playerTank
     */
    public function __construct($weight, Team $team, Player $player, PlayerTank $playerTank)
    {
        $this->weight = $weight;
        $this->team = $team;
        $this->player = $player;
        $this->playerTank = $playerTank;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @return Player
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * @return PlayerTank
     */
    public function getPlayerTank()
    {
        return $this->playerTank;
    }
} 