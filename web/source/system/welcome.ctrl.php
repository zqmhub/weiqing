<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
$_W['page']['title'] = '系统';

load()->model('cloud');

$cloud_registered = cloud_prepare();
$cloud_registered = $cloud_registered === true ? true : false;

template('system/welcome');
