<?php

/**
 * GetBuildingTime
 *
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 */

// Calcul du temps de construction d'un Element (Batiment / Recherche / Defense / Vaisseau )
// $user       -> Le Joueur lui meme
// $planet     -> La planete sur laquelle l'Element doit etre construit
// $Element    -> L'Element que l'on convoite
//获取建造时间
function GetBuildingTime($user, $planet, $Element)
{
    global $ge_pricelist, $ge_resource, $ge_reslist; //, $game_config;
    $game_config = fs()->game_config();


    $level = isset($planet[$ge_resource[$Element]]) ? $planet[$ge_resource[$Element]] : $user[$ge_resource[$Element]];
    if (in_array($Element, $ge_reslist['build'])) {
        // Pour un batiment ...
        $cost_metal = floor($ge_pricelist[$Element]['metal'] * pow($ge_pricelist[$Element]['factor'], $level));
        $cost_crystal = floor($ge_pricelist[$Element]['crystal'] * pow($ge_pricelist[$Element]['factor'], $level));
        $time = ((($cost_crystal) + ($cost_metal)) / $game_config['game_speed']) * (1 / ($planet[$ge_resource['14']] + 1)) * pow(0.5, $planet[$ge_resource['15']]);
        $time = floor(($time * 60 * 60) * (1 - (($user['rpg_constructeur']) * 0.1)));
    } elseif (in_array($Element, $ge_reslist['tech'])) {
        // Pour une recherche
        $cost_metal = floor($ge_pricelist[$Element]['metal'] * pow($ge_pricelist[$Element]['factor'], $level));
        $cost_crystal = floor($ge_pricelist[$Element]['crystal'] * pow($ge_pricelist[$Element]['factor'], $level));
        $intergal_lab = $user[$ge_resource[123]];
        if ($intergal_lab < "1") {
            $lablevel = $planet[$ge_resource['31']];
        } elseif ($intergal_lab >= "1") {
            $empire = fs()->doquery("SELECT * FROM {{table}} WHERE id_owner='" . $user[id] . "';", 'planets');
            $NbLabs = 0;
            while ($colonie = mysql_fetch_array($empire)) {
                $techlevel[$NbLabs] = $colonie[$ge_resource['31']];
                $NbLabs++;
            }
            if ($intergal_lab >= "1") {
                $lablevel = 0;
                for ($lab = 1; $lab <= $intergal_lab; $lab++) {
                    asort($techlevel);
                    $lablevel += $techlevel[$lab - 1];
                }
            }
        }
        $time = (($cost_metal + $cost_crystal) / $game_config['game_speed']) / (($lablevel + 1) * 2);
        $time = floor(($time * 60 * 60) * (1 - (($user['rpg_scientifique']) * 0.1)));
    } elseif (in_array($Element, $ge_reslist['defense'])) {
        // Pour les defenses ou la flotte 'tarif fixe' durée adaptée a u niveau nanite et usine robot
        $time = (($ge_pricelist[$Element]['metal'] + $ge_pricelist[$Element]['crystal']) / $game_config['game_speed']) * (1 / ($planet[$ge_resource['21']] + 1)) * pow(1 / 2, $planet[$ge_resource['15']]);
        $time = floor(($time * 60 * 60) * (1 - (($user['rpg_defenseur']) * 0.375)));
    } elseif (in_array($Element, $ge_reslist['fleet'])) {
        $time = (($ge_pricelist[$Element]['metal'] + $ge_pricelist[$Element]['crystal']) / $game_config['game_speed']) * (1 / ($planet[$ge_resource['21']] + 1)) * pow(1 / 2, $planet[$ge_resource['15']]);
        $time = floor(($time * 60 * 60) * (1 - (($user['rpg_technocrate']) * 0.05)));
    }

//+sbdx 14:17 2008-6-20
//修正时间出现负数！
    $time = $time < 1 ? 0 : $time;
    return $time;
}

?>