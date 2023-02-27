<?php
/*
Plugin Name: Eugene Magazine Force HTTPS
Description: Redirect to https version of the website
Author: Radley Sustaire
Author URI: https://radleysustaire.com/
Version: 1.0.0
*/

// https://stackoverflow.com/a/5106355/470480
if ( empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ) {
	$location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . $location);
	exit;
}