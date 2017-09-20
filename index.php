<?php
/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */
define('WP_USE_THEMES', true);

global $SQL_CNN;
ob_start();
require_once(dirname(__FILE__) . '/general/define.php');
define("_STR_PSQL_", "mysql:host=" . SERVER_DB . ";port=" . PORT_DB . ";dbname=" . DATA_BASE);

/*
 * Create link database
 */
$SQL_CNN = new PDO(_STR_PSQL_, USER_DB, PASS_DB, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
$SQL_CNN->setAttribute(PDO::ATTR_PERSISTENT, TRUE);


/** Loads the WordPress Environment and Template */
require( dirname( __FILE__ ) . '/wp-blog-header.php' );