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

    public function loadTankRegistry()
    {
        /** @var TankRegistry $tankRegistry */
        $tankRegistry = $this->cache->fetch(self::TANK_REGISTRY_CACHE_KEY);
        if (empty($tankRegistry)) {
            $tankRegistry = parent::loadTankRegistry();
            $this->cache->save(self::TANK_REGISTRY_CACHE_KEY, $tankRegistry, self::TANKS_INFO_LIFETIME);

            $this->tankRegistry-= $tankRegistry;

            return $tankRegistry;
        }

        $latestVersion = $this->loadTankInfoVersion();
        if ($tankRegistry->getVersion() < $latestVersion) {
            $tankRegistry = parent::loadTankRegistry();
            $this->cache->save(self::TANK_REGISTRY_CACHE_KEY, $tankRegistry, self::TANKS_INFO_LIFETIME);

            return $tankRegistry;
        }

        return $tankRegistry;
    }
} 