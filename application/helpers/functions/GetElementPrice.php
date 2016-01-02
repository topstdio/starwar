<?php

/**
 * GetElementPrice.php
 *
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 */

// ----------------------------------------------------------------------------------------------------------------
// Calcul du prix d'un Element (Batiment / Recherche / Defense / Vaisseau )
// $user       -> Le Joueur lui meme
// $planet     -> La planete sur laquelle l'Element doit etre construit
// $Element    -> L'Element que l'on convoite
// $userfactor -> true  pour un batiment ou une recherche
// -> false pour une defense ou un vaisseau
//
// Reponse : Une chaine de caractère decrivant proprement le tarif pret a etre affichée
function GetElementPrice ($user, $planet, $Element, $userfactor = true) {
	global $ge_pricelist, $ge_resource, $lang;

	if ($userfactor) {
		$level = isset($planet[$ge_resource[$Element]]) ? $planet[$ge_resource[$Element]] : $user[$ge_resource[$Element]];
	}

	$array = array(
		'metal'      => $lang["Metal"],
		'crystal'    => $lang["Crystal"],
		'deuterium'  => $lang["Deuterium"],
		'energy_max' => $lang["Energy"]
		);

	$price = array();
	foreach ($array as $ResType => $ResTitle) {
		if (isset($ge_pricelist[$Element][$ResType]) &&  $ge_pricelist[$Element][$ResType]!= 0) {

			if ($userfactor) {
				$cost = floor($ge_pricelist[$Element][$ResType] * pow($ge_pricelist[$Element]['factor'], $level));
			} else {
				$cost = floor($ge_pricelist[$Element][$ResType]);
			}
//			if ($cost > $planet[$ResType]) {
//				$text .= "<b style=\"color:red;\"> <t title=\"-" . pretty_number ($cost - $planet[$ResType]) . "\">";
//				$text .= "<span class=\"noresources\">" . pretty_number($cost) . "</span></t></b> ";
//
//			} else {
//				$text .= "<b style=\"color:lime;\"> <span class=\"noresources\">" . pretty_number($cost) . "</span></b> ";
//			}
            $tmp=array('type'=>$ResType,'title'=>$ResTitle,'cost'=>$cost,'rest'=>$planet[$ResType]);
            $price[]=$tmp;
		}
	}
	return $price;
}

?>