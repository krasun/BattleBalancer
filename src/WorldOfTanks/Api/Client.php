<?php

namespace WorldOfTanks\Api;

use GuzzleHttp\ClientInterface;
use WorldOfTanks\Api\Model\Clan;
use WorldOfTanks\Api\Model\ClanMember;
use WorldOfTanks\Api\Model\TankInfo;
use WorldOfTanks\Api\Model\TankRegistry;
use WorldOfTanks\Api\Model\TankStats;

/**
 * Low-level API for "World of Tanks".
 */
class Client
{
    /**
     * Basic URL for WoT API.
     */
    const URL = 'http://api.worldoftanks.ru/wot/';

    /**
     * Successful request to API.
     */
    const STATUS_SUCCESSFUL = 'ok';

    /**
     * Failed request to API.
     */
    const STATUS_FAILED = 'error';

    /**
     * Max possible record number for every request to API.
     */
    const MAX_RECORD_NUM_PER_REQUEST = 100;

    /**
     * Source is not available.
     */
    const ERROR_CODE_UNAVAILABLE = 504;

    /**
     * Retry interval in microseconds.
     */
    const RETRY_INTERVAL_IN_MICROSECONDS = 500000;

    /**
     * @var string
     */
    private $applicationId;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var int
     */
    private $requestNum = 0;

    /**
     * @param $apiUrl
     * @param $applicationId
     * @param ClientInterface $httpClient
     */
    public function __construct($applicationId, ClientInterface $httpClient)
    {
        $this->applicationId = $applicationId;
        $this->httpClient = $httpClient;
    }

    /**
     * Loads "Global war" top clans.
     *
     * Can be ordered by "wins_count", "combats_count" or "provinces_count".
     *
     * @param string $mapId Global map identifier
     * @param string $orderBy Order by field
     *
     * @return Clan[]
     */
    public function loadGlobalWarTopClans($mapId, $orderBy = 'wins_count', $limit = 100)
    {
        $result = $this->callMethod('globalwar/top', [
            'fields' => 'clan_id,members_count',
            'map_id' => $mapId,
            'order_by' => $orderBy
        ]);

        $clans = [];
        foreach ($result as $clanRow) {
            $clans[] = new Clan($clanRow['clan_id'], $clanRow['members_count']);
        }

        return $clans;
    }

    /**
     * Loads clan members.
     *
     * @param int|array $clanIds Identifiers
     *
     * @return array Array with clan members split by clan identifier as key
     */
    public function loadClanMembers($clanIds)
    {
        $clanIds = is_array($clanIds) ? $clanIds : [$clanIds];

        $result = $this->callMethod('clan/info', [
            'clan_id' => join(',', $clanIds),
            'fields' => 'clan_id,members.account_id'
        ]);

        $members = [];
        foreach ($result as $clanId => $clanRow) {
            if (empty($clanRow['members'])) {
                continue;
            }

            $members[$clanId] = [];
            foreach ($clanRow['members'] as $memberRow) {
                $members[$clanId][$memberRow['account_id']] = new ClanMember($memberRow['account_id'], $clanId);
            }
        }

        return $members;
    }

    /**
     * Loads tank stats for specified accounts.
     *
     * @param array|int $accountIds
     *
     * @return array Array Of TankStats split by account id as key
     */
    public function loadTankStats($accountIds)
    {
        $accountIds = is_array($accountIds) ? $accountIds : [$accountIds];

        $accountIdPacks = array_chunk(array_unique($accountIds), self::MAX_RECORD_NUM_PER_REQUEST);
        $result = [];
        foreach ($accountIdPacks as $ids) {
            $result += $this->callMethod('account/tanks', [
                'account_id' => join(',', $ids),
                'fields' => 'tank_id,mark_of_mastery'
            ]);
        }

        $tankStats = [];
        foreach ($result as $accountId => $accountRow) {
            $tankStats[$accountId] = [];
            foreach ($accountRow as $tankStatsRow) {
                $tankStats[$accountId][$tankStatsRow['tank_id']] = new TankStats(
                    $tankStatsRow['tank_id'],
                    $accountId,
                    $tankStatsRow['mark_of_mastery']
                );
            }
        }

        return $tankStats;
    }

    /**
     * Loads tanks information from encyclopedia.
     *
     * @param int|array $tankIds
     *
     * @return array
     */
    public function loadTankInfo($tankIds)
    {
        $tankIds = is_array($tankIds) ? $tankIds : [$tankIds];

        $tankIdPacks = array_chunk(array_unique($tankIds), self::MAX_RECORD_NUM_PER_REQUEST);
        $result = [];
        foreach ($tankIdPacks as $ids) {
            $result += $this->callMethod('encyclopedia/tankinfo', [
                'tank_id' => join(',', $ids),
                'fields' => 'tank_id,level,max_health,gun_damage_min,gun_damage_max'
            ]);
        }

        $tankInfos = [];
        foreach ($result as $tankId => $tankInfoRow) {
            $tankInfos[$tankId] = new TankInfo(
                $tankId,
                $tankInfoRow['level'],
                $tankInfoRow['max_health'],
                $tankInfoRow['gun_damage_min'],
                $tankInfoRow['gun_damage_max']
            );
        }

        return $tankInfos;
    }

    /**
     * Timestamp when tanks information from encyclopedia was updated.
     *
     * @return int
     */
    public function loadTankInfoVersion()
    {
        $info = $this->callMethod('encyclopedia/info');

        return $info['tanks_updated_at'];
    }

    /**
     * Loads all tank identifiers from encyclopedia.
     *
     * @return int[]
     */
    public function loadAllTankIds()
    {
        $result = $this->callMethod('encyclopedia/tanks', ['fields' => 'tank_id']);

        return array_map(function ($row) {
            return $row['tank_id'];
        }, $result);
    }

    /**
     * @return TankRegistry
     */
    public function loadTankTankRegistry()
    {
        $tankIds = $this->loadAllTankIds();

        return new TankRegistry($this->loadTankInfoVersion(), $this->loadTankInfo($tankIds));
    }

    /**
     * Sends request to WoT H1TTP API and returns data from decoded JSON as array, if
     * status was OK.
     *
     * Method name examples: "globalwar/top", or "globalwar.top".
     *
     * @param string $methodName Method name with block name
     * @param array $parameters Method parameters
     * @param int $tryCount Number of tries
     *
     * @return array
     */
    protected function callMethod($methodName, $parameters = [], $tryCount = 3)
    {
        $parameters = array_merge($parameters, [
            'application_id' => $this->applicationId
        ]);

        $methodName = str_replace('.', '/', $methodName);
        $response = $this->httpClient->get(self::URL . $methodName . '/', [
            'query' => $parameters
        ]);
        $this->requestNum++;

        $result = $response->json();
        if ($result['status'] == self::STATUS_SUCCESSFUL) {
            return $result['data'];
        }

        if ($result['error']['code'] == self::ERROR_CODE_UNAVAILABLE && $tryCount > 0) {
            usleep(self::RETRY_INTERVAL_IN_MICROSECONDS);

            return $this->callMethod($methodName, $parameters, $tryCount - 1);
        }

        throw new \RuntimeException('Api error');
    }

    /**
     * @return int
     */
    public function getRequestNum()
    {
        return $this->requestNum;
    }
}