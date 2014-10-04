<?php


namespace WorldOfTanks\BattleBalancer\Model;

class TeamInfo 
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $name;
    /**
     * @var int
     */
    private $membersCount;
    /**
     * @var int
     */
    private $winsCount;
    /**
     * @var int
     */
    private $combatsCount;
    /**
     * @var int
     */
    private $provincesCount;

    /**
     * @param int $id
     * @param string $name
     * @param int $membersCount
     * @param int $winsCount
     * @param int $combatsCount
     * @param int $provincesCount
     */
    public function __construct($id, $name, $membersCount, $winsCount, $combatsCount, $provincesCount)
    {
        $this->id = $id;
        $this->name = $name;
        $this->membersCount = $membersCount;
        $this->winsCount = $winsCount;
        $this->combatsCount = $combatsCount;
        $this->provincesCount = $provincesCount;
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
    public function getCombatsCount()
    {
        return $this->combatsCount;
    }

    /**
     * @return int
     */
    public function getMembersCount()
    {
        return $this->membersCount;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getProvincesCount()
    {
        return $this->provincesCount;
    }

    /**
     * @return int
     */
    public function getWinsCount()
    {
        return $this->winsCount;
    }
}