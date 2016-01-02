<?php

/**
 * GetMissileRange.php
 *
 * @version 1
 * @copyright 2008 by Chlorel for XNova
 */

function GetMissileRange () {
	global $gc_resource ; //, $user;

	if ($user[$gc_resource[117]] > 0) {
		$MissileRange = ($user[$gc_resource[117]] * 5) - 1;
	} elseif ($user[$gc_resource[117]] == 0) {
		$MissileRange = 0;
	}

	return $MissileRange;
}

?>