<?php

/*
Plugin Name: Password Reset
Plugin URI: https://surbma.com
Description: Reset all WordPress users passwords. Multisite compatible.

Version: 2.0

Author: Surbma
Author URI: https://surbma.com

License: GPLv2 or later
Text Domain: surbma-password-reset
*/

// No direct load
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'SURBMA_PR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

include SURBMA_PR_PLUGIN_DIR . 'class-password-reset.php';
include SURBMA_PR_PLUGIN_DIR . 'class-admin.php';

// Multisite Network
$Surbma_Password_Reset_Options = new Surbma_Password_Reset_Options();
function load_if_networkadmin() {
	global $Surbma_Password_Reset_Options;
	if ( current_user_can( 'edit_users' ) ) {
		//JS regisztrálása
		add_action( 'admin_enqueue_scripts', array( $Surbma_Password_Reset_Options, 'add_admin_scripts' ) );
	}
}
add_action( 'wp_loaded', 'load_if_networkadmin' );

// Delete Cron after deactivation
function mpr_deactivation() {
	wp_clear_scheduled_hook( 'mpr_run_cronjob' );
	update_option( 'mpr_cron_active', 'false' );
}
register_deactivation_hook( __FILE__, 'mpr_deactivation' );
add_action( 'mpr_run_cronjob', array( $Surbma_Password_Reset_Options, 'mpr_cronjob_handler' ) );

/*===============================
=     Cronjob filter            =
===============================*/

global $SURBMA_PR_MONTH;
$MPRGLOBAL = ( isset( $SURBMA_PR_MONTH ) && $SURBMA_PR_MONTH !== null ) ? $SURBMA_PR_MONTH : 12;

// Cronjob date filter
function mpr_calculate_intervals( $a ) {
	global $MPRGLOBAL;
	$interval = 2592000*$MPRGLOBAL;
	if ($a == true) {
		return time( $interval );
	} else {
		return $interval;
	}
}

function mpr_cron_intervals( $schedules ) {
	$schedules['mpr_variable_event'] = array(
		'interval' => mpr_calculate_intervals( false ),
		'display'  => __( 'Variable months' )
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'mpr_cron_intervals' );
