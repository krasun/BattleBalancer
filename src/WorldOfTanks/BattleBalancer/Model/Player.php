<?php
namespace WorldOfTanks\BattleBalancer\Model;

class Player 
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var array|PlayerTank[]
     */
    private $tanks;

    /**
     * @var bool
     */
    private $willPlay = false;

    /**
     * @param int $id
     * @param PlayerTank[]|array $tanks
     */
    public function __construct($id, array $tanks)
    {
        $this->id = $id;
        $this->tanks = $tanks;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array|PlayerTank[]
     */
    public function getTanks()
    {
        return $this->tanks;
    }

    /**
     * @param boolean $willPlay
     *
     * @return Player
     */
    public function setWillPlay($willPlay)
    {
        $this->willPlay = $willPlay;

        return $this;
    }

    /**
     * @return boolean
     */
    public function willPlay()
    {
        return $this->willPlay;
    }
}