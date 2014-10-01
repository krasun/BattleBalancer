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
} 