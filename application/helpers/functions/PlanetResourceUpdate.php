<?php

/**
 * PlanetResourceUpdate.php
 * 资源更新函数
 * @version 1.1
 * @copyright 2008 By Chlorel for XNova
 */

function PlanetResourceUpdate($CurrentUser, /*&$CurrentPlanet,*/ $UpdateTime, $Simul = false)
{
    global $ge_ProdGrid, $ge_resource, $ge_reslist; //, $game_config;
    $game_config = fs()->game_config();
    $CurrentPlanet = &fs()->current_planet();

    // 计算存储空间
    $CurrentPlanet['metal_max'] = (floor(BASE_STORAGE_SIZE * pow(1.5, $CurrentPlanet[$ge_resource[22]]))) * (1 + ($CurrentUser['rpg_stockeur'] * 0.5));
    $CurrentPlanet['crystal_max'] = (floor(BASE_STORAGE_SIZE * pow(1.5, $CurrentPlanet[$ge_resource[23]]))) * (1 + ($CurrentUser['rpg_stockeur'] * 0.5));
    $CurrentPlanet['deuterium_max'] = (floor(BASE_STORAGE_SIZE * pow(1.5, $CurrentPlanet[$ge_resource[24]]))) * (1 + ($CurrentUser['rpg_stockeur'] * 0.5));
    // 计算存储空间 (允许溢出范围内的空间)
    $MaxMetalStorage = $CurrentPlanet['metal_max'] * MAX_OVERFLOW;
    $MaxCristalStorage = $CurrentPlanet['crystal_max'] * MAX_OVERFLOW;
    $MaxDeuteriumStorage = $CurrentPlanet['deuterium_max'] * MAX_OVERFLOW;

    // Calcul de production linaire des divers types
    $Caps = array();
    $Caps['metal_perhour']=0;
    $Caps['crystal_perhour']=0;
    $Caps['deuterium_perhour']=0;
    $Caps['energy_used']=0;
    $Caps['energy_max']=0;
    $BuildTemp = $CurrentPlanet['temp_max'];

    for ($ProdID = 0; $ProdID < 300; $ProdID++) {
        if (in_array($ProdID, $ge_reslist['prod'])) {
            $BuildLevelFactor = $CurrentPlanet[$ge_resource[$ProdID] . "_porcent"];
            $BuildLevel = $CurrentPlanet[$ge_resource[$ProdID]];
            $Caps['metal_perhour'] += floor(eval  ($ge_ProdGrid[$ProdID]['formule']['metal']) * ($game_config['resource_multiplier']) * (1 + ($CurrentUser['rpg_geologue'] * 0.05)));
            $Caps['crystal_perhour'] += floor(eval  ($ge_ProdGrid[$ProdID]['formule']['crystal']) * ($game_config['resource_multiplier']) * (1 + ($CurrentUser['rpg_geologue'] * 0.05)));
            $Caps['deuterium_perhour'] += floor(eval  ($ge_ProdGrid[$ProdID]['formule']['deuterium']) * ($game_config['resource_multiplier']) * (1 + ($CurrentUser['rpg_geologue'] * 0.05)));
            if ($ProdID < 4) {
                $Caps['energy_used'] += floor(eval  ($ge_ProdGrid[$ProdID]['formule']['energy']) * ($game_config['resource_multiplier']) * (1 + ($CurrentUser['rpg_ingenieur'] * 0.05)));
            } elseif ($ProdID >= 4) {
                $Caps['energy_max'] += floor(eval  ($ge_ProdGrid[$ProdID]['formule']['energy']) * ($game_config['resource_multiplier']) * (1 + ($CurrentUser['rpg_ingenieur'] * 0.05)));
            }
        }
    }

    // Il n'y a pas de production de base sur une lune (ni de production tout court d'ailleurs
    if ($CurrentPlanet['planet_type'] == 3) {
        $game_config['metal_basic_income'] = 0;
        $game_config['crystal_basic_income'] = 0;
        $game_config['deuterium_basic_income'] = 0;
        $CurrentPlanet['metal_perhour'] = 0;
        $CurrentPlanet['crystal_perhour'] = 0;
        $CurrentPlanet['deuterium_perhour'] = 0;
        $CurrentPlanet['energy_used'] = 0;
        $CurrentPlanet['energy_max'] = 0;
    } else {
        $CurrentPlanet['metal_perhour'] = $Caps['metal_perhour'];
        $CurrentPlanet['crystal_perhour'] = $Caps['crystal_perhour'];
        $CurrentPlanet['deuterium_perhour'] = $Caps['deuterium_perhour'];
        $CurrentPlanet['energy_used'] = $Caps['energy_used'];
        $CurrentPlanet['energy_max'] = $Caps['energy_max'];
    }

    // Depuis quand n'avons nous pas les infos ressources a jours ?
    $ProductionTime = ($UpdateTime - $CurrentPlanet['last_update']);
    $CurrentPlanet['last_update'] = $UpdateTime;

    if ($CurrentPlanet['energy_max'] == 0) {
        // Ah ha ... l'energie max est 0 ...
        // Soit pas de production d'energie ... Soit mode vacance
        $CurrentPlanet['metal_perhour'] = $game_config['metal_basic_income'];
        $CurrentPlanet['crystal_perhour'] = $game_config['crystal_basic_income'];
        $CurrentPlanet['deuterium_perhour'] = $game_config['deuterium_basic_income'];
        $production_level = 100;
    } elseif ($CurrentPlanet["energy_max"] >= $CurrentPlanet["energy_used"]) {
        // Cas normal (Y a assez d'energie toutes les mines tournent a plein rendement)
        $production_level = 100;
    } else {
        // Cas ou il manque de l'energie ... On calcule un pourcentage de production
        $production_level = floor(($CurrentPlanet['energy_max'] / $CurrentPlanet['energy_used']) * 100);
    }
    // Mise a l'echele des valeurs
    if ($production_level > 100) {
        $production_level = 100;
    } elseif ($production_level < 0) {
        $production_level = 0;
    }

//假期模式不增加资源
//+sbdx 0:37 2008-7-18 
    if ($CurrentUser['urlaubs_modus'] != 1) {
        if ($CurrentPlanet['metal'] <= $MaxMetalStorage) {
            $MetalProduction = (($ProductionTime * ($CurrentPlanet['metal_perhour'] / 3600)) * $game_config['resource_multiplier']) * (0.01 * $production_level);
            $MetalBaseProduc = (($ProductionTime * ($game_config['metal_basic_income'] / 3600)) * $game_config['resource_multiplier']);
            $MetalTheorical = $CurrentPlanet['metal'] + $MetalProduction + $MetalBaseProduc;
            if ($MetalTheorical <= $MaxMetalStorage) {
                $CurrentPlanet['metal'] = $MetalTheorical;
            } else {
                $CurrentPlanet['metal'] = $MaxMetalStorage;
            }
        }

        if ($CurrentPlanet['crystal'] <= $MaxCristalStorage) {
            $CristalProduction = (($ProductionTime * ($CurrentPlanet['crystal_perhour'] / 3600)) * $game_config['resource_multiplier']) * (0.01 * $production_level);
            $CristalBaseProduc = (($ProductionTime * ($game_config['crystal_basic_income'] / 3600)) * $game_config['resource_multiplier']);
            $CristalTheorical = $CurrentPlanet['crystal'] + $CristalProduction + $CristalBaseProduc;
            if ($CristalTheorical <= $MaxCristalStorage) {
                $CurrentPlanet['crystal'] = $CristalTheorical;
            } else {
                $CurrentPlanet['crystal'] = $MaxCristalStorage;
            }
        }

        if ($CurrentPlanet['deuterium'] <= $MaxDeuteriumStorage) {
            $DeuteriumProduction = (($ProductionTime * ($CurrentPlanet['deuterium_perhour'] / 3600)) * $game_config['resource_multiplier']) * (0.01 * $production_level);
            $DeuteriumBaseProduc = (($ProductionTime * ($game_config['deuterium_basic_income'] / 3600)) * $game_config['resource_multiplier']);
            $DeuteriumTheorical = $CurrentPlanet['deuterium'] + $DeuteriumProduction + $DeuteriumBaseProduc;
            if ($DeuteriumTheorical <= $MaxDeuteriumStorage) {
                $CurrentPlanet['deuterium'] = $DeuteriumTheorical;
            } else {
                $CurrentPlanet['deuterium'] = $MaxDeuteriumStorage;
            }
        }
    }
    if ($Simul == false) {
        // Gestion de l'eventuelle queue de fabrication d'elements
        $Builded = fs()->HandleElementBuildingQueue($CurrentUser, $ProductionTime);

        // On enregistre la planete !
        $QryUpdatePlanet = "UPDATE {{table}} SET ";
        //假期模式不增加资源
        //+sbdx 0:37 2008-7-18
        if ($CurrentUser['urlaubs_modus'] != 1) {
            $QryUpdatePlanet .= "`metal` = '" . $CurrentPlanet['metal'] . "', ";
            $QryUpdatePlanet .= "`crystal` = '" . $CurrentPlanet['crystal'] . "', ";
            $QryUpdatePlanet .= "`deuterium` = '" . $CurrentPlanet['deuterium'] . "', ";
        }
        $QryUpdatePlanet .= "`last_update` = '" . $CurrentPlanet['last_update'] . "', ";
        $QryUpdatePlanet .= "`b_hangar_id` = '" . $CurrentPlanet['b_hangar_id'] . "', ";
        $QryUpdatePlanet .= "`metal_perhour` = '" . $CurrentPlanet['metal_perhour'] . "', ";
        $QryUpdatePlanet .= "`crystal_perhour` = '" . $CurrentPlanet['crystal_perhour'] . "', ";
        $QryUpdatePlanet .= "`deuterium_perhour` = '" . $CurrentPlanet['deuterium_perhour'] . "', ";
        $QryUpdatePlanet .= "`energy_used` = '" . $CurrentPlanet['energy_used'] . "', ";
        $QryUpdatePlanet .= "`energy_max` = '" . $CurrentPlanet['energy_max'] . "', ";
        // Par hasard des elements etaient finis ....
        if ($Builded != '') {
            foreach ($Builded as $Element => $Count) {
                if ($Element <> '') {
                    $QryUpdatePlanet .= "`" . $ge_resource[$Element] . "` = '" . $CurrentPlanet[$ge_resource[$Element]] . "', ";
                }
            }
        }
        $QryUpdatePlanet .= "`b_hangar` = '" . $CurrentPlanet['b_hangar'] . "' ";
        $QryUpdatePlanet .= "WHERE ";
        $QryUpdatePlanet .= "`id` = '" . $CurrentPlanet['id'] . "';";

        fs()->doquery("LOCK TABLE {{table}} WRITE", 'planets');
        fs()->doquery($QryUpdatePlanet, 'planets');
        fs()->doquery("UNLOCK TABLES", '');
    }

}

// Revision History
// - 1.0 Mise en module initiale
// - 1.1 Mise a jour automatique mines / silos / energie ...
?>