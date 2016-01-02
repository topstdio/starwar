<?php

/**
 * IsTechnologieAccessible.php
 *
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 */

// Verification si l'on a le droit ou non a un element
function IsTechnologieAccessible($user, $planet, $Element) {
	global $ge_requeriments, $ge_resource;

	if (isset($ge_requeriments[$Element])) {
		$enabled = true;
		foreach($ge_requeriments[$Element] as $ReqElement => $EleLevel) {
			if (@$user[$ge_resource[$ReqElement]] && $user[$ge_resource[$ReqElement]] >= $EleLevel) {
				// break;
			} elseif (isset($planet[$ge_resource[$ReqElement]]) && $planet[$ge_resource[$ReqElement]] >= $EleLevel) {
				$enabled = true;
			} else {
				return false;
			}
		}
		return $enabled;
	} else {
		return true;
	}
}

?>