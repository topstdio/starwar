<?php

/**
 *
 * CheckPlanetUsedFields.php
 *
 * @version 1
 * @copyright 2008 By Chlorel for XNova
 */

// Verification du nombre de cases utilisées sur la planete courrante
function CheckPlanetUsedFields ( /*&$planet*/ ) {
	global $ge_resource;
    $planet = &fs()->current_planet();

	// Tous les batiments
	$cfc  = $planet[$ge_resource[1]]  + $planet[$ge_resource[2]]  + $planet[$ge_resource[3]] ;
	$cfc += $planet[$ge_resource[4]]  + $planet[$ge_resource[12]] + $planet[$ge_resource[14]];
	$cfc += $planet[$ge_resource[15]] + $planet[$ge_resource[21]] + $planet[$ge_resource[22]];
	$cfc += $planet[$ge_resource[23]] + $planet[$ge_resource[24]] + $planet[$ge_resource[31]];
	$cfc += $planet[$ge_resource[33]] + $planet[$ge_resource[34]] + $planet[$ge_resource[44]];

	// Si on se trouve sur une lune ... Y a des choses a ajouter aussi
	if ($planet['planet_type'] == '3') {
		$cfc += $planet[$ge_resource[41]] + $planet[$ge_resource[42]] + $planet[$ge_resource[43]];
	}

	// Mise a jour du nombre de case dans la BDD si incorrect
	if ($planet['field_current'] != $cfc) {
		$planet['field_current'] = $cfc;
		fs()->doquery("UPDATE {{table}} SET field_current=$cfc WHERE id={$planet['id']}", 'planets');
	}
}

?>