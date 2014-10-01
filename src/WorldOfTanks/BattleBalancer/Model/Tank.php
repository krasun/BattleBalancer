<?php

namespace WorldOfTanks\BattleBalancer\Model;

class Tank 
{
    /**
     * @var int
     */
    private $id;

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
     * @param int $id
     * @param int $level
     * @param int $maxHealth
     * @param int $gunDamageMin
     * @param int $gunDamageMax
     */
    public function __construct($id, $level, $maxHealth, $gunDamageMin, $gunDamageMax)
    {
        $this->id = $id;
        $this->level = $level;
        $this->maxHealth = $maxHealth;
        $this->gunDamageMin = $gunDamageMin;
        $this->gunDamageMax = $gunDamageMax;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
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
} 