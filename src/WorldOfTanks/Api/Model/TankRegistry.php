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
} 