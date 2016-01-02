<?php

/**
 * BuildingSaveUserRecord.php
 *
 * @version 1
 * @copyright 2008 by Chlorel for XNova
 */

function BuildingSaveUserRecord ( $CurrentUser ) {

	$QryUpdateUser  = "UPDATE {{table}} SET ";
	$QryUpdateUser .= "`xpminier` = '".      $CurrentUser['xpminier']      ."' ";
	$QryUpdateUser .= "WHERE ";
	$QryUpdateUser .= "`id` = '".            $CurrentUser["id"]            ."';";
	fs()->doquery( $QryUpdateUser, 'users');

	return;
}

?>