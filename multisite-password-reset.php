<?php

/*
Plugin Name: Password Reset
Plugin URI: https://surbma.com
Description: Reset all wordpress users password

Version: 1.1

Author: Surbma
Author URI: https://surbma.com

License: GPLv2 or later
Text Domain: multisite-password-reset
*/

// No direct load
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define('MULTIPASSRESET__PLUGIN_DIR', plugin_dir_path(__FILE__));

include (MULTIPASSRESET__PLUGIN_DIR.'class.multipassreset.php');
include (MULTIPASSRESET__PLUGIN_DIR.'class.multipassreset-admin.php');

// Multisite Network
$MPR_OPTIONS = new MPR_OPTIONS();
function load_if_networkadmin() {
	global $MPR_OPTIONS;
	if ( current_user_can( 'edit_users' ) ) {
		//JS regisztrálása
		add_action( 'admin_enqueue_scripts', array( $MPR_OPTIONS, 'add_admin_scripts' ) );
	}
}
add_action( 'wp_loaded', 'load_if_networkadmin' );

// Delete Cron after deactivation
function mpr_deactivation() {
	wp_clear_scheduled_hook('mpr_run_cronjob');
	update_option('mpr_cron_active', 'false');
}
register_deactivation_hook(__FILE__, 'mpr_deactivation');
add_action( 'mpr_run_cronjob', array( $MPR_OPTIONS, 'mpr_cronjob_handler' ) );

/*===============================
=     Cronjob filter            =
===============================*/

global $MPRGLOBALRESET;
$MPRGLOBAL = (isset($MPRGLOBALRESET) && $MPRGLOBALRESET !== null)?$MPRGLOBALRESET:12;

// Cronjob date filter
function mpr_calculate_intervals($a) {
	global $MPRGLOBAL;
	$interval = 2592000*$MPRGLOBAL;
	if ($a == true) {
		return time($interval);
	} else {
		return $interval;
	}
}

function mpr_cron_intervals($schedules) {
	$schedules['mpr_variable_event'] = array(
		'interval' => mpr_calculate_intervals(false),
		'display'  => __('Variable months')
	);
	return $schedules;
}
add_filter('cron_schedules', 'mpr_cron_intervals');
