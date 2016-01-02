<?php

/**
 * GetMaxConstructibleElements.php
 *
 * @version 1.2
 * @copyright 2008 By Chlorel for XNova
 */
// Retourne un entier du nombre maximum d'elements constructible
// par rapport aux ressources disponibles
// $Element    -> L'element visé
// $Ressources -> Un table contenant metal, crystal, deuterium, energy de la planete
//                sur laquelle on veut construire l'Element
function GetMaxConstructibleElements($Element, $Ressources)
{
    global $ge_pricelist;
    // On test les 4 Type de ressource pour voir si au moins on sait en construire 1
    $Buildable = 0;
    $MaxElements = 0;
    if ($ge_pricelist[$Element]['metal'] != 0) {
        $ResType_1_Needed = $ge_pricelist[$Element]['metal'];
        $Buildable = floor($Ressources["metal"] / $ResType_1_Needed);
        $MaxElements = $Buildable;
    }

    if ($ge_pricelist[$Element]['crystal'] != 0) {
        $ResType_2_Needed = $ge_pricelist[$Element]['crystal'];
        $Buildable = floor($Ressources["crystal"] / $ResType_2_Needed);
    }
    if ($MaxElements <= 0) {
        $MaxElements = $Buildable;
    } elseif ($MaxElements > $Buildable) {
        $MaxElements = $Buildable;
    }

    if ($ge_pricelist[$Element]['deuterium'] != 0) {
        $ResType_3_Needed = $ge_pricelist[$Element]['deuterium'];
        $Buildable = floor($Ressources["deuterium"] / $ResType_3_Needed);
    }
    if ($MaxElements <= 0) {
        $MaxElements = $Buildable;
    } elseif ($MaxElements > $Buildable) {
        $MaxElements = $Buildable;
    }

    if ($ge_pricelist[$Element]['energy'] != 0) {
        $ResType_4_Needed = $ge_pricelist[$Element]['energy'];
        $Buildable = floor($Ressources["energy_max"] / $ResType_4_Needed);
    }
    if ($Buildable < 1) {
        $MaxElements = 0;
    }

    return $MaxElements;
}

// Verion History
// - 1.0 Version initiale (creation)
// - 1.1 Correction bug ressources n�gatives ...
// - 1.2 Correction bug quand pas de m�tal
?>