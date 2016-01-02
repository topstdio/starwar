<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . '/libraries/GameController.php';

class Infos extends GameController
{

    function __construct()
    {
        parent::__construct();
        fs()->includeLang('infos');
    }

    function info_get($gid)
    {
        if ($gid) {
            $query = $this->_info($gid);
            $query['code'] = $this->g_errcode['ok'];
            $this->_g_response_without_update($query);
        } else {
            $query['code'] = $this->g_errcode['err'];
            $this->_g_response_without_update($query);
        }
    }

    private function _info($gid)
    {
        global $lang, $ge_pricelist, $ge_CombatCaps;

        $parse['name'] = $lang['info'][$gid]['name'];
        $parse['description'] = $lang['info'][$gid]['description'];

        $p = array(1, 2, 3, 4, 12, 42);
        if (in_array($gid, $p)) {
            $parse['product'] = $this->_production($gid);
        } elseif ($gid == 43) { //传送门
            //TODO:
            $parse['gate_fleet_rows'] = $this->_fleet_list();
        } elseif ($gid >= 202 && $gid <= 215) {
            $parse['element_typ'] = $lang['tech'][200];
            $parse['rf_info_to'] = $this->_fire_to($gid);   // Rapid Fire vers
            $parse['rf_info_fr'] = $this->_fire_from($gid); // Rapid Fire de
            $parse['hull_pt'] = $ge_pricelist[$gid]['metal'] + $ge_pricelist[$gid]['crystal'];
            $parse['shield_pt'] = $ge_CombatCaps[$gid]['shield'];
            $parse['attack_pt'] = $ge_CombatCaps[$gid]['attack'];
            $parse['capacity_pt'] = $ge_pricelist[$gid]['capacity'];
            $parse['base_speed'] = $ge_pricelist[$gid]['speed'];
            $parse['base_conso'] = $ge_pricelist[$gid]['consumption'];
            if ($gid == 202) {
                $parse['upd_speed'] = $ge_pricelist[$gid]['speed2'];
                $parse['upd_conso'] = $ge_pricelist[$gid]['consumption2'];
            } elseif ($gid == 211) {
                $parse['upd_speed'] = $ge_pricelist[$gid]['speed2'];
            }
        } elseif ($gid >= 401 && $gid <= 408) {
            $parse['element_typ'] = $lang['tech'][400];

            $parse['rf_info_to'] = $this->_fire_to($gid);   // Rapid Fire vers
            $parse['rf_info_fr'] = $this->_fire_from($gid); // Rapid Fire de
            $parse['hull_pt'] = $ge_pricelist[$gid]['metal'] + $ge_pricelist[$gid]['crystal'];
            $parse['shield_pt'] = $ge_CombatCaps[$gid]['shield'];
            $parse['attack_pt'] = $ge_CombatCaps[$gid]['attack'];
        } elseif ($gid >= 502 && $gid <= 503) {
            $parse['element_typ'] = $lang['tech'][400];
            $parse['hull_pt'] = $ge_pricelist[$gid]['metal'] + $ge_pricelist[$gid]['crystal'];
            $parse['shield_pt'] = $ge_CombatCaps[$gid]['shield'];
            $parse['attack_pt'] = $ge_CombatCaps[$gid]['attack'];
        }


        return array('info' => $parse);
    }

    private function _fleet_list()
    {
        global $ge_resource, $lang;

        $Result = array();
        for ($Ship = 300; $Ship > 200; $Ship--) {
            if (isset($ge_resource[$Ship])) {
                if ($this->g_planet[$ge_resource[$Ship]] > 0) {
                    $bloc['fleet_id'] = $Ship;
                    $bloc['fleet_name'] = $lang['tech'][$Ship];
                    $bloc['fleet_max'] = $this->g_planet[$ge_resource[$Ship]];
                    $bloc['gate_ship_dispo'] = $lang['gate_ship_dispo'];
                    $Result[] = $bloc;

                }
            }
        }
        return $Result;
    }

    private function _production($BuildID)
    {
        global $ge_ProdGrid, $ge_resource; //, $game_config;
        $game_config = $this->g_config;
        $CurrentPlanet = $this->g_planet;
        $CurrentUser = $this->g_user;

        $BuildLevelFactor = $CurrentPlanet[$ge_resource[$BuildID] . "_porcent"];
        $BuildTemp = $CurrentPlanet['temp_max'];
        $CurrentBuildtLvl = $CurrentPlanet[$ge_resource[$BuildID]];

        $BuildLevel = ($CurrentBuildtLvl > 0) ? $CurrentBuildtLvl : 1;
        $Prod[1] = (floor(eval($ge_ProdGrid[$BuildID]['formule']['metal']) * $game_config['resource_multiplier']) * (1 + ($CurrentUser['rpg_geologue'] * 0.05)));
        $Prod[2] = (floor(eval($ge_ProdGrid[$BuildID]['formule']['crystal']) * $game_config['resource_multiplier']) * (1 + ($CurrentUser['rpg_geologue'] * 0.05)));
        $Prod[3] = (floor(eval($ge_ProdGrid[$BuildID]['formule']['deuterium']) * $game_config['resource_multiplier']) * (1 + ($CurrentUser['rpg_geologue'] * 0.05)));
        $Prod[4] = (floor(eval($ge_ProdGrid[$BuildID]['formule']['energy']) * $game_config['resource_multiplier']) * (1 + ($CurrentUser['rpg_ingenieur'] * 0.05)));
        $BuildLevel = "";

        $ActualProd = floor($Prod[$BuildID]);
        if ($BuildID != 12) {
            $ActualNeed = floor($Prod[4]);
        } else {
            $ActualNeed = floor($Prod[3]);
        }

        $BuildStartLvl = $CurrentBuildtLvl - 2;
        if ($BuildStartLvl < 1) {
            $BuildStartLvl = 1;
        }
        $rtn = array();
        $ProdFirst = 0;
        for ($BuildLevel = $BuildStartLvl; $BuildLevel < $BuildStartLvl + 10; $BuildLevel++) {
            if ($BuildID != 42) {
                $Prod[1] = (floor(eval($ge_ProdGrid[$BuildID]['formule']['metal']) * $game_config['resource_multiplier']) * (1 + ($CurrentUser['rpg_geologue'] * 0.05)));
                $Prod[2] = (floor(eval($ge_ProdGrid[$BuildID]['formule']['crystal']) * $game_config['resource_multiplier']) * (1 + ($CurrentUser['rpg_geologue'] * 0.05)));
                $Prod[3] = (floor(eval($ge_ProdGrid[$BuildID]['formule']['deuterium']) * $game_config['resource_multiplier']) * (1 + ($CurrentUser['rpg_geologue'] * 0.05)));
                $Prod[4] = (floor(eval($ge_ProdGrid[$BuildID]['formule']['energy']) * $game_config['resource_multiplier']) * (1 + ($CurrentUser['rpg_ingenieur'] * 0.05)));

                $bloc['current_build_lvl'] = $CurrentBuildtLvl;
                $bloc['build_lvl'] = $BuildLevel;
                if ($ProdFirst > 0) {
                    if ($BuildID != 12) {
                        $bloc['build_gain'] = floor($Prod[$BuildID] - $ProdFirst);
                    } else {
                        $bloc['build_gain'] = floor($Prod[4] - $ProdFirst);
                    }
                } else {
                    $bloc['build_gain'] = "";
                }
                if ($BuildID != 12) {
                    $bloc['build_prod'] = floor($Prod[$BuildID]);
                    $bloc['build_prod_diff'] = floor($Prod[$BuildID] - $ActualProd);
                    $bloc['build_need'] = floor($Prod[4]);
                    $bloc['build_need_diff'] = floor($Prod[4] - $ActualNeed);
                } else {
                    $bloc['build_prod'] = floor($Prod[4]);
                    $bloc['build_prod_diff'] = floor($Prod[4] - $ActualProd);
                    $bloc['build_need'] = floor($Prod[3]);
                    $bloc['build_need_diff'] = floor($Prod[3] - $ActualNeed);
                }
                if ($ProdFirst == 0) {
                    if ($BuildID != 12) {
                        $ProdFirst = floor($Prod[$BuildID]);
                    } else {
                        $ProdFirst = floor($Prod[4]);
                    }
                }
            } else {
                // Cas particulier de la phalange
                $bloc['current_build_lvl'] = $CurrentBuildtLvl;
                $bloc['build_lvl'] = $BuildLevel;
                $bloc['build_range'] = ($BuildLevel * $BuildLevel) - 1;
            }
            $rtn[] = $bloc;
        }
        return $rtn;
    }

    private function _fire_from($BuildID)
    {
        global $lang, $ge_CombatCaps;

        $rtn = array();
        for ($Type = 200; $Type < 500; $Type++) {
            if (isset($ge_CombatCaps[$Type]['sd'][$BuildID]) && $ge_CombatCaps[$Type]['sd'][$BuildID] > 1) {
                $tp = array(
                    'fire_from' => $lang['nfo_rf_from'],
                    'tech_type' => $lang['tech'][$Type],
                    'building_type' => $ge_CombatCaps[$Type]['sd'][$BuildID],
                );
                $rtn[] = $tp;
            }
        }

        return $rtn;
    }

    private function _fire_to($BuildID)
    {
        global $lang, $ge_CombatCaps;
        $rtn = array();
        for ($Type = 200; $Type < 500; $Type++) {
            if (isset($ge_CombatCaps[$BuildID]['sd'][$Type]) &&
                $ge_CombatCaps[$BuildID]['sd'][$Type] > 1
            ) {
                $tp = array(
                    'fire_again' => $lang['nfo_rf_again'],
                    'tech_type' => $lang['tech'][$Type],
                    'building_type' => $ge_CombatCaps[$BuildID]['sd'][$Type],
                );
            }
        }
        return $rtn;
    }


}

/* End of file */
