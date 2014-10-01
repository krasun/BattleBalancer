<?php

namespace WorldOfTanks\Api\Model;

class TankStats 
{
    /**
     * @var int
     */
    private $tankId;

    /**
     * @var int
     */
    private $accountId;

    /**
     * @var int
     */
    private $markOfMastery;

    /**
     * @param int $tankId
     * @param int $accountId
     * @param int $markOfMastery
     */
    public function __construct($tankId, $accountId, $markOfMastery)
    {
        $this->tankId = $tankId;
        $this->accountId = $accountId;
        $this->markOfMastery = $markOfMastery;
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
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @return int
     */
    public function getMarkOfMastery()
    {
        return $this->markOfMastery;
    }
} 