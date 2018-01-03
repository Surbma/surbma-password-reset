<?php 
/*====================================
= Get user IDs and set new passwords =
=====================================*/
	
class MULTIPASSRESET {

	// Wordpress user IDs
	public function get_all_user_ids(){
		global $wpdb;
		
		$allusers = $wpdb->get_results( "SELECT ID FROM $wpdb->users" );
		$user_ids = array();
		foreach ( $allusers as $u ) {
			$user_ids[] = $u->ID;
		}
		return $user_ids;
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
			}
		}
	}

}
