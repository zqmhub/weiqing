<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/source/system/__init.php : v 11b5f8a4d66a : 2014/05/29 03:01:21 : RenChao $
 */

define('IN_GW', true);
if ($controller == 'system' && $action == 'content_provider') {
	$system_activie = 2;
} else {
	$system_activie = 1;
}