<?php
/*====================================
= Get user IDs and set new passwords =
=====================================*/

class Surbma_Password_Reset {

	// Wordpress user IDs
	public function get_all_user_ids() {
		global $wpdb;

		$allusers = $wpdb->get_col( "SELECT ID FROM $wpdb->users" );
		return $allusers;
	}

	// New passwords
	public function reset_all_users_password() {
		// Get user IDs
		$array_of_user_ids = $this->get_all_user_ids();
		foreach ( $array_of_user_ids as $user_id ) {
			// Exclude Super Administrator
			if( !is_super_admin( $user_id ) ) {
				// Generating new passwords
				$password = wp_generate_password();
				// Change all passwords
				wp_set_password( $password, $user_id );
				// send notification email
				$this->send_user_notification( $user_id );
			}
		}
	}

	// Email to all users
	public function send_user_notification( $user_id ){
		// getting user data by ID
		$current_user = get_user_by( 'ID', $user_id );

		$display_name = $current_user->display_name;
		$user_login = $current_user->user_login;
		$email = $current_user->user_email;

		$adt_rp_key = get_password_reset_key( $current_user );
		$rp_link = '<a href="' . wp_login_url() . "?action=rp&key=$adt_rp_key&login=" . rawurlencode( $user_login ) . '">' . wp_login_url() . "?action=rp&key=$adt_rp_key&login=" . rawurlencode( $user_login ) . '</a>';

		$to = $email;
		$subject = 'Your Email Was Reseted';

		$body = "Hi " . $display_name . ",<br><br>";
		$body .= "Email address: " . $email . "<br>";
		$body .= "Click here to set the password for your account: <br>";
		$body .= $rp_link . '<br>';

		$headers = array('Content-Type: text/html; charset=UTF-8');

		wp_mail( $to, $subject, $body, $headers );
	}
}
