<?php

use Pimple\Container;
use GuzzleHttp\Client as HttpClient;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Doctrine\Common\Cache\FilesystemCache;

use WorldOfTanks\Api\Cache\Client as CachedApiClient;
use WorldOfTanks\Command\BalanceCommand;
use WorldOfTanks\BattleBalancer\Loader\BattleConfig;
use WorldOfTanks\BattleBalancer\Loader\GlobalWarTopClansApiBattleLoader;
use WorldOfTanks\BattleBalancer\Balancer;
use WorldOfTanks\Command\TankInfoCommand;

$container = new Container();

$container['application_name'] = 'balancer';
$parameters = require_once __DIR__ . '/parameters.php';
foreach ($parameters as $key => $value) {
    $container[$key] = $value;
};

$container['event_dispatcher'] = function () {
    return new EventDispatcher();
};

$container['tank_info_command'] = function ($c) {
    return new TankInfoCommand(null, $c['api_client']);
};

$container['balancer_command'] = function ($c) {
    return new BalanceCommand(
        null,
        $c['battle_config'],
        $c['battle_loader'],
        $c['battle_balancer'],
        $c['api_client'],
        $c['event_dispatcher'],
        $c['developer_contacts']
    );
};
$container['battle_loader'] = function ($c) {
    return new GlobalWarTopClansApiBattleLoader($c['api_client'], $c['event_dispatcher']);
};
$container['battle_balancer'] = function ($c) {
    return new Balancer(new \WorldOfTanks\BattleBalancer\Balance\DummyBalanceWeightCalculator());
};
$container['battle_config'] = function ($c) {
    return (new BattleConfig())
        ->setRequiredMemberNumPerTeam($c['required_member_num_per_team'])
        ->setMinTankLevel($c['min_tank_level'])
        ->setMaxTankLevel($c['max_tank_level'])
    ;
};

$container['api_client'] = function ($c) {
    return new CachedApiClient($c['api_application_id'], $c['http_client'], $c['cache']);
};
$container['http_client'] = function () {
    return new HttpClient();
};
$container['cache'] = function($c) {
    return new FilesystemCache($c['cache_dir']);
};

