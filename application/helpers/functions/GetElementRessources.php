<?php

/**
 * GetElementRessources.php
 *
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 */

// Retourne un tableau des ressources necessaires par type pour le lot d'elements
// $Element   -> L'element visé
// $Count     -> Nombre d'elements a construire
function GetElementRessources ( $Element, $Count ) {
	global $ge_pricelist;

	$ResType['metal']     = ($ge_pricelist[$Element]['metal']     * $Count);
	$ResType['crystal']   = ($ge_pricelist[$Element]['crystal']   * $Count);
	$ResType['deuterium'] = ($ge_pricelist[$Element]['deuterium'] * $Count);

	return $ResType;
}

?>