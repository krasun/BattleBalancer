<?php

namespace WorldOfTanks\Api\Model;

class Clan
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $membersCount;

    /**
     * @param int $id
     * @param int $membersCount
     */
    public function __construct($id, $membersCount)
    {
        $this->id = $id;
        $this->membersCount = $membersCount;
    }

    /**
     * Clan identifier.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Members count.
     *
     * @return int
     */
    public function getMembersCount()
    {
        return $this->membersCount;
    }
} 