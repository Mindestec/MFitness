<?php
/*
Author: Mindestec
Author URI: https://mindestec.com
License: GPLv2 o anterior
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: Mindestec Fitness*/

function mdtf_Desinstalar(){
	if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		die;
	}

	$option_name = 'wporg_option';

	delete_option( $option_name );

	// for site options in Multisite
	delete_site_option( $option_name );

	// drop a custom database table
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mf" );
}