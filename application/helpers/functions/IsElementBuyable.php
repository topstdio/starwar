<?php

/**
 * IsElementBuyable.php
 *
 * @version 1
 * @copyright 2008 by Chlorel for XNova
 */

// Verifie si un element est achetable au moment demandé
// $CurrentUser   -> Le Joueur lui meme
// $CurrentPlanet -> La planete sur laquelle l'Element doit etre construit
// $Element       -> L'Element que l'on convoite
// $Incremental   -> true  pour un batiment ou une recherche
//                -> false pour une defense ou un vaisseau
// $ForDestroy    -> false par defaut pour une construction
//                -> true pour calculer la demi valeur du niveau en cas de destruction
//
// Reponse        -> boolean (oui / non)
function IsElementBuyable ($CurrentUser, $CurrentPlanet, $Element, $Incremental = true, $ForDestroy = false) {
	global $ge_pricelist, $ge_resource;

	if ($Incremental) {
		$level  = isset($CurrentPlanet[$ge_resource[$Element]]) ? $CurrentPlanet[$ge_resource[$Element]] : $CurrentUser[$ge_resource[$Element]];
	}

	$RetValue = true;
	$array    = array('metal', 'crystal', 'deuterium', 'energy_max');

	foreach ($array as $ResType) {
		if (isset($ge_pricelist[$Element][$ResType]) && $ge_pricelist[$Element][$ResType] != 0) {
			if ($Incremental) {
				$cost[$ResType]  = floor($ge_pricelist[$Element][$ResType] * pow($ge_pricelist[$Element]['factor'], $level));
			} else {
				$cost[$ResType]  = floor($ge_pricelist[$Element][$ResType]);
			}

			if ($ForDestroy) {
				$cost[$ResType]  = floor($cost[$ResType] / 2);
			}

			if ($cost[$ResType] > $CurrentPlanet[$ResType]) {
				$RetValue = false;
			}
		}
	}
	return $RetValue;
}

?>