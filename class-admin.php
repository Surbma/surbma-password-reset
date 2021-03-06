<?php

/*=================
= Admin functions =
==================*/

class Surbma_Password_Reset_Options {

	public $MPRGLOBAL;

	// Admin menu & Ajax registration
	function __construct() {
		if( is_multisite() ) {
			add_action( 'network_admin_menu', array( $this, 'init_sub_menu_network' ) );
		} else {
			add_action( 'admin_menu', array( $this, 'init_sub_menu') );
		}
		add_action( 'wp_ajax_mpr_reset_all_pass', array( $this, 'mpr_reset_all_pass_cb' ) );
		add_action( 'admin_init', array( $this, 'initialize_menu_settings' ) );
	}

	public function mpr_activation_cb() {
		wp_schedule_event( current_time( 'timestamp' ), 'mpr_variable_event', 'mpr_run_cronjob' );
	}

	// Enqueue JS
	public function add_admin_scripts() {
		wp_enqueue_script( 'mpr_global', plugins_url( 'js/global.js', __FILE__ ), '', false, false );
	}

	// Single site menu item
	public function init_sub_menu() {
		add_submenu_page( 'options-general.php', 'Multipass Reset Settings', 'Multipass Reset Settings', 'administrator', 'mpreset', array( $this, 'settings' ) );
	}

	// Multisite menu item
	public function init_sub_menu_network() {
		add_submenu_page( 'settings.php', 'Multipass Reset Settings', 'Multipass Reset Settings', 'administrator', 'mpreset', array( $this, 'settings' ) );
	}

	// Menu
	public function initialize_menu_settings() {
		add_settings_section(
			'mpr_reset_pass_section',
			'Reset Passwords',
			array( $this, 'mpr_reset_pass_section_cb' ),
			'mpreset'
		);
	}

	// Password reset menu content
	public function mpr_reset_pass_section_cb() {
		?>
		<div class="success-message-wrapper"></div>
		<img style="display: none;margin: 47px;height: 42px;" src="<?php echo plugins_url( 'images/preloader.gif', __FILE__ );?>" id="mprpreloader" alt="">
		<p class="submit">
			<input name="submit" type="submit" id="submit-mpr" class="button-primary" value="<?php _e( 'Reset All Passwords' );?>" />
		</p>
		<?php
	}

	// Show content
	public function settings() {
		?>
		<div class="wrap">
			<h2>Settings</h2>
				<form method="post" action="">
				<?php do_settings_sections( 'mpreset' ); ?>
				</form>
		</div>
		<?php
	}

	// Ajax
	public function mpr_reset_all_pass_cb() {

		if ( !wp_next_scheduled( 'mpr_run_cronjob' ) ) {
			wp_schedule_event( time(), 'mpr_variable_event', 'mpr_run_cronjob' );
		} else {
			wp_clear_scheduled_hook( 'mpr_run_cronjob' );
			wp_schedule_event( time(), 'mpr_variable_event', 'mpr_run_cronjob' );
		}
		print( 'All Done!' );
		die();
	}

	// Cronjob
	public function mpr_cronjob_handler() {
		$passreset = new Surbma_Password_Reset();
		$passreset->reset_all_users_password();
	}

}
