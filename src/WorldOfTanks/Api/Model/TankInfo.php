<?php

namespace WorldOfTanks\Api\Model;

class TankInfo 
{
    /**
     * @var int
     */
    private $tankId;

    /**
     * @var int
     */
    private $level;

    /**
     * @var int
     */
    private $maxHealth;

    /**
     * @var int
     */
    private $gunDamageMin;

    /**
     * @var int
     */
    private $gunDamageMax;

    /**
     * @param int $tankId
     * @param int $level
     * @param int $maxHealth
     * @param int $gunDamageMin
     * @param int $gunDamageMax
     */
    public function __construct($tankId, $level, $maxHealth, $gunDamageMin, $gunDamageMax)
    {
        $this->tankId = $tankId;
        $this->level = $level;
        $this->maxHealth = $maxHealth;
        $this->gunDamageMin = $gunDamageMin;
        $this->gunDamageMax = $gunDamageMax;
    }

    /**
     * @return int
     */
    public function getTankId()
    {
        return $this->tankId;
    }

    /**
     * @return int
     */
    public function getMaxHealth()
    {
        return $this->maxHealth;
    }

    /**
     * @return int
     */
    public function getGunDamageMin()
    {
        return $this->gunDamageMin;
    }

    /**
     * @return int
     */
    public function getGunDamageMax()
    {
        return $this->gunDamageMax;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }
}