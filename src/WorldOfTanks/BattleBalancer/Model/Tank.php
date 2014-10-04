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
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $nation;

    /**
     * @param int $id
     * @param string $name
     * @param string $nation
     * @param int $level
     * @param int $maxHealth
     * @param int $gunDamageMin
     * @param int $gunDamageMax
     */
    public function __construct($id, $name, $nation, $level, $maxHealth, $gunDamageMin, $gunDamageMax)
    {
        $this->id = $id;
        $this->level = $level;
        $this->maxHealth = $maxHealth;
        $this->gunDamageMin = $gunDamageMin;
        $this->gunDamageMax = $gunDamageMax;
        $this->name = $name;
        $this->nation = $nation;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNation()
    {
        return $this->nation;
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