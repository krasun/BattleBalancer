<?php

namespace WorldOfTanks\Api\Cache;

use Doctrine\Common\Cache\Cache;
use GuzzleHttp\ClientInterface;
use WorldOfTanks\Api\Client as ApiClient;
use WorldOfTanks\Api\Model\TankRegistry;

class Client extends ApiClient
{
    /**
     * How often tanks information updated from encyclopedia.
     */
    const TANKS_INFO_LIFETIME = 604800; // one week

    const TANK_REGISTRY_CACHE_KEY = 'tankRegistry';

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var TankRegistry
     */
    private $tankRegistry = null;

    /**
     * @param Cache $cache
     */
    public function __construct($applicationId, ClientInterface $httpClient, Cache $cache)
    {
        parent::__construct($applicationId, $httpClient);

        $this->cache = $cache;
    }

    /**
     * Loads tanks information from cache.
     *
     * @param array|int $tankIds
     * @return array
     */
    public function loadTankInfo($tankIds)
    {
        $this->tryLoadTankRegistry();

        return $this->tankRegistry->getByIds($tankIds);
    }

    private function tryLoadTankRegistry()
    {
        if (! empty($this->tankRegistry)) {
            return;
        }

        /** @var TankRegistry $tankRegistry */
        $tankRegistry = $this->cache->fetch(self::TANK_REGISTRY_CACHE_KEY);
        if (empty($tankRegistry)) {
            $this->tankRegistry = $this->loadTankTankRegistry();
        } else {
            $latestVersion = parent::loadTankInfoVersion();
            $this->tankRegistry = ($tankRegistry->getVersion() < $latestVersion)
                ? $this->loadTankTankRegistry()
                : $tankRegistry
            ;
        }

        $this->cache->save(self::TANK_REGISTRY_CACHE_KEY, $this->tankRegistry, self::TANKS_INFO_LIFETIME);
    }

    /**
     * @return TankRegistry
     */
    private function loadTankTankRegistry()
    {
        $tankIds = parent::loadAllTankIds();

        return new TankRegistry(parent::loadTankInfoVersion(), parent::loadTankInfo($tankIds));
    }
} 