<?php

$parameters = require_once __DIR__ . '/parameters.php';

use Pimple\Container;
use GuzzleHttp\Client as HttpClient;
use WorldOfTanks\Api\Client as ApiClient;
use WorldOfTanks\BattleBalancer\Loader\BattleConfig;
use WorldOfTanks\Command\BalancerCommand;
use WorldOfTanks\BattleBalancer\Balancer;
use WorldOfTanks\BattleBalancer\Loader\GlobalWarTopClansApiBattleLoader;


$container = new Container();

$container['application_name'] = 'balancer';
foreach ($parameters as $key => $value) {
    $container[$key] = $value;
};

$container['balancer_command'] = function ($c) {
    return new BalancerCommand(null, $c['battle_config'], $c['battle_loader'], $c['battle_balancer']);
};
$container['battle_loader'] = function ($c) {
    return new GlobalWarTopClansApiBattleLoader($c['api_client']);
};
$container['battle_balancer'] = function ($c) {
    return new Balancer();
};
$container['battle_config'] = function ($c) {
    return (new BattleConfig())
        ->setRequiredMemberNumPerTeam($c['required_member_num_per_team'])
        ->setMinTankLevel($c['min_tank_level'])
        ->setMaxTankLevel($c['max_tank_level'])
    ;
};

$container['api_client'] = function ($c) {
    return new ApiClient($c['api_application_id'], $c['http_client']);
};
$container['http_client'] = function () {
    return new HttpClient();
};

