<?php

namespace WorldOfTanks\Api\Model;

class TankRegistry 
{
    /**
     * @var TankInfo[]
     */
    private $tankInfos = [];

    /**
     * @var int
     */
    private $version;

    /**
     * @var int
     */
    private $overallMinHealth = PHP_INT_MAX;

    /**
     * @var int
     */
    private $overallMaxHealth = 0;

    /**
     * @var int
     */
    private $overallGunDamageMin = PHP_INT_MAX;

    /**
     * @var int
     */
    private $overallGunDamageMax = 0;

    /**
     * @var bool
     */
    private $computed = false;

    /**
     * @param int $version
     * @param TankInfo[] $tankInfos
     */
    public function __construct($version, array $tankInfos = [])
    {
        $this->tankInfos = $tankInfos;
        $this->version = $version;
    }

    /**
     * Gets tanks information by list of identifiers.
     *
     * @param int[] $ids
     *
     * @return TankInfo[]
     */
    public function getByIds($ids)
    {
        $infos = [];
        foreach ($ids as $id) {
            if (isset($this->tankInfos[$id])) {
                $infos[$id] = $this->tankInfos[$id];
            }
        }

        return $infos;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return array|TankInfo[]
     */
    public function all()
    {
        return $this->tankInfos;
    }

    public function computeOverallMinHealth()
    {
        if (! $this->computed) {
            $this->computeOveralls();
        }

        return $this->overallMinHealth;
    }

    public function computeOverallMaxHealth()
    {
        if (! $this->computed) {
            $this->computeOveralls();
        }

        return $this->overallMaxHealth;
    }

    public function computeOverallGunDamageMin()
    {
        if (! $this->computed) {
            $this->computeOveralls();
        }

        return $this->overallGunDamageMin;
    }

    public function computeOverallGunDamageMax()
    {
        if (! $this->computed) {
            $this->computeOveralls();
        }

        return $this->overallGunDamageMax;
    }

    private function computeOveralls()
    {
        /** @var TankInfo $tankInfo */
        foreach ($this->tankInfos as $tankInfo) {
            if ($tankInfo->getMaxHealth() <= $this->overallMinHealth) {
                $this->overallMinHealth = $tankInfo->getMaxHealth();
            }
            if ($tankInfo->getMaxHealth() >= $this->overallMaxHealth) {
                $this->overallMaxHealth = $tankInfo->getMaxHealth();
            }
            if ($tankInfo->getGunDamageMin() <= $this->overallGunDamageMin) {
                $this->overallGunDamageMin = $tankInfo->getGunDamageMin();
            }
            if ($tankInfo->getGunDamageMax() >= $this->overallGunDamageMax) {
                $this->overallGunDamageMax = $tankInfo->getGunDamageMax();
            }
        }
    }
} 