<?php

/**
 * UpdatePlanetBatimentQueueList.php
 *
 * @version 1.1
 * @copyright 2008 By Chlorel for XNova
 */

function UpdatePlanetBatimentQueueList(/*&$CurrentPlanet, &$CurrentUser*/)
{
    $CurrentPlanet = &fs()->current_planet();
    $CurrentUser = &fs()->current_user();

    $RetValue = false;
    if ($CurrentPlanet['b_building_id'] != 0) {
        while ($CurrentPlanet['b_building_id'] != 0) {
            if ($CurrentPlanet['b_building'] <= time()) {
                fs()->PlanetResourceUpdate($CurrentUser, /*$CurrentPlanet,*/ $CurrentPlanet['b_building'], false);
                $IsDone = fs()->CheckPlanetBuildingQueue(/*$CurrentPlanet, $CurrentUser*/);
                if ($IsDone == true) {
                    fs()->SetNextQueueElementOnTop($CurrentPlanet, $CurrentUser);
                }
            } else {
                $RetValue = true;
                break;
            }
        }
    }
    return $RetValue;
}

// Revision History
// - 1.0 Mise en module initiale
// - 1.1 Mise a jour des ressources sur la planete verifi�e (pour prendre en compte les ressources produites
//       pendant la construction et avant l'evolution evantuel d'une mine ou d'en batiment

?>