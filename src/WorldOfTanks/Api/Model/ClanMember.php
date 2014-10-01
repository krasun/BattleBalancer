<?php

namespace WorldOfTanks\Api\Model;

class ClanMember
{
    /**
     * @var int
     */
    private $accountId;

    /**
     * @var int
     */
    private $clanId;

    /**
     * @param int $accountId
     * @param int $clanId
     */
    public function __construct($accountId, $clanId)
    {
        $this->accountId = $accountId;
        $this->clanId = $clanId;
    }

    /**
     * @return int
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @return int
     */
    public function getClanId()
    {
        return $this->clanId;
    }
}