<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

$config['game_test'] = 'test';

//错误码
$config['game_code']['ok']=200;
$config['game_code']['message']=300;
$config['game_code']['err']=400;

//全局配置
$config['game_configs'] = array(
    'users_amount' => 2,
    'game_speed' => 2500,
    'fleet_speed' => 2500,
    'resource_multiplier' => 1,
    'Fleet_Cdr' => 30,
    'Defs_Cdr' => 30,
    'initial_fields' => 163,
    'COOKIE_NAME' => 'XNova',
    'game_name' => 'XNova',
    'game_disable' => 1,
    'close_reason' => '',
    'metal_basic_income' => 20,
    'crystal_basic_income' => 10,
    'deuterium_basic_income' => 0,
    'energy_basic_income' => 0,
    'BuildLabWhileRun' => 0,
    'LastSettedGalaxyPos' => 1,
    'LastSettedSystemPos' => 1,
    'LastSettedPlanetPos' => 2,
    'urlaubs_modus_erz' => 1,
    'noobprotection' => 1,
    'noobprotectiontime' => 5000,
    'noobprotectionmulti' => 5,
    'forum_url' => 'http://www.xnova.fr/forum',
    'OverviewNewsFrame' => 1,
    'OverviewNewsText' => '',
    'OverviewExternChat' => 0,
    'OverviewExternChatCmd' => '',
    'OverviewBanner' => 0,
    'OverviewClickBanner' => '',
    'debug' => 0
);


/* End of file */
