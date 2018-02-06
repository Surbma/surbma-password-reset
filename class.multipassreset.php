<?php 
/*====================================
= Get user IDs and set new passwords =
=====================================*/
 
class MULTIPASSRESET {

	// Wordpress user IDs
	public function get_all_user_ids(){
		global $wpdb;
		
		$allusers = $wpdb->get_col( "SELECT ID FROM $wpdb->users" );
		return $allusers;
	}

	// New passwords
	public function reset_all_users_password(){
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
	public function send_user_notification( $user_id ){
		// getting user data by ID
		$current_user = get_user_by( 'ID', $user_id );
		// getting user email
		$to = $current_user->user_email;
		// set email subject
		$subject = 'Your Email Was Reseted';
		//set email body
		$body = 'Here email content goes';
		// set usage of html in content
		$headers = array('Content-Type: text/html; charset=UTF-8');		 
		// send email
		wp_mail( $to, $subject, $body, $headers );
	}
}
