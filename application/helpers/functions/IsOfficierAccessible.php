<?php

/**
 * IsOfficierAccessible.php
 *
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 *  �ж�������Ƿ��ܻ�ȡ
 */

// Verification si l'on a le droit ou non a un officier
// Retour:
//  0 => pas les Officiers necessaires
//  1 => Tout va tres bien on peut le faire celui l
// -1 => On pouvait le faire, mais on est d�ja au level max
function IsOfficierAccessible ($CurrentUser, $Officier) {
	global $ge_requeriments, $ge_resource, $ge_pricelist;

	if (isset($ge_requeriments[$Officier])) {
		$enabled = true;
		foreach($ge_requeriments[$Officier] as $ReqOfficier => $OfficierLevel) {
			if ($CurrentUser[$ge_resource[$ReqOfficier]] &&
				$CurrentUser[$ge_resource[$ReqOfficier]] >= $OfficierLevel) {
				$enabled = 1;
			} else {
				return 0;
			}
		}
	}
	if ($CurrentUser[$ge_resource[$Officier]] < $ge_pricelist[$Officier]['max']  ) {
		return 1;
	} else {
		return -1;
	}
}

?>