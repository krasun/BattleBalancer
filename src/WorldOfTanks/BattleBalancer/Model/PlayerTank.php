<?php

namespace WorldOfTanks\BattleBalancer\Model;

class PlayerTank 
{
    /**
     * @var int
     */
    private $markOfMastery;

    /**
     * @var Tank
     */
    private $tank;

    /**
     * @var bool
     */
    private $willPlay;

    /**
     * @param int $markOfMastery
     * @param Tank $tank
     */
    public function __construct($markOfMastery, Tank $tank)
    {
        $this->markOfMastery = $markOfMastery;
        $this->tank = $tank;
    }

    /**
     * @return int
     */
    public function getMarkOfMastery()
    {
        return $this->markOfMastery;
    }

    /**
     * @return Tank
     */
    public function getTank()
    {
        return $this->tank;
    }

    /**
     * @return int
     */
    public function getTankId()
    {
        return $this->tank->getId();
    }

    /**
     * @param boolean $willBeUsed
     *
     * @return PlayerTank
     */
    public function setWillPlay($willBeUsed)
    {
        $this->willPlay = $willBeUsed;

        return $this;
    }

    /**
     * @return boolean
     */
    public function willBeUsed()
    {
        return $this->willPlay;
    }
}