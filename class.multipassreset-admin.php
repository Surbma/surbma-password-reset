<?php
/*==========================================================================================
= Felelős az Admin kezelő oldal funkciójairól és szükséges kódok regisztrációjáról         =
============================================================================================*/
	class MPR_OPTIONS{
		/*----------  Menűpont és ajax hivás regisztrációja  ----------*/
		public $MPRGLOBAL;
		function __construct(){
			$this->MPRGLOBALRESET;
			$mprcron = (is_multisite()) ? get_site_option('mpr_cron_active') : get_option('mpr_cron_active');
			if(isset($MPRGLOBALRESET) && $MPRGLOBALRESET !== null){
				$this->MPRGLOBAL = $MPRGLOBALRESET;
			} else {
				$this->MPRGLOBAL = 12;
			}
			if(is_multisite()){
				add_action('network_admin_menu', array($this, 'init_sub_menu_network'));
			} else {
				add_action('admin_menu', array($this, 'init_sub_menu'));
			}
			add_filter( 'cron_schedules', array($this, 'mpr_cron_intervals'));
			add_action('mpr_run_cronjob', array($this, 'mpr_cronjob_handler'));
			add_action( 'wp_ajax_mpr_reset_all_pass', array($this,'mpr_reset_all_pass_cb'));
			add_action('admin_init', array($this, 'initialize_menu_settings'));
			if(!wp_next_scheduled ( 'mpr_run_cronjob' ) && $mprcron == 'true') {
				wp_schedule_event(time(), 'mpr_variable_event', 'mpr_run_cronjob');
			}
			
		}
		/*----------  Js file regisztráció funkciója  ----------*/
		public function add_admin_scripts(){
			wp_enqueue_script( 'mpr_global', plugins_url( 'js/global.js' , __FILE__ ),'',  false, false);
		}
		/*----------  Menüpont regisztráció funkciója  ----------*/
		public function init_sub_menu(){
			add_submenu_page( 'options-general.php', 'Multipass Reset Settings', 'Multipass Reset Settings', 'administrator', 'mpreset', array($this, 'settings'));
		}
		/*----------  Menüpont Multisite regisztráció funkciója  ----------*/
		public function init_sub_menu_network(){
			add_submenu_page( 'settings.php', 'Multipass Reset Settings', 'Multipass Reset Settings', 'administrator', 'mpreset', array($this, 'settings'));
		}
		
		/*=============================================
		=            Menüpont tartalmai               =
		=============================================*/
		public function initialize_menu_settings(){

			add_settings_section( 
				'mpr_reset_pass_section', 
				'Reset Passwords', 
				array($this, 'mpr_reset_pass_section_cb'), 
				'mpreset' 
			);
			
		}		
		/*----------  Az azonalli jelszó csere menü szekció tartalmai  ----------*/
		public function mpr_reset_pass_section_cb(){
			?>
			<div class="success-message-wrapper">
      	   		<img style="display: none;margin: 47px;height: 42px;" src="<?php echo plugins_url('imgs/preloader.gif', __FILE__); ?>" id="mprpreloader" alt="">
      	   	</div>
          	 <p class='submit'>
                <input name='submit' type='submit' id='submit-mpr' class='button-primary' value='<?php _e("Reset All Passwords"); ?>' />
           	</p>
           	<?php
		}
		/*----------  Menüpont Megjelenitése  ----------*/
		public function settings(){
			?>
			<div class='wrap'>
		          <h2>Settings</h2>
		          <form method='post' action=''>
		          	   
		          	   	<?php
		          	   		do_settings_sections('mpreset'); 
		          	   	?>
		          	   
		          </form>
		     </div>
		     <?php
		}
		/*----------  Ajax Funkció kezelője  ----------*/
		public function mpr_reset_all_pass_cb(){
			if(is_multisite() && get_site_option('mpr_cron_active') !== true){
				add_site_option( 'mpr_cron_active' );
			} else {
				if(get_option( 'mpr_cron_active') !== true){
					add_option( 'mpr_cron_active', 'true', '', 'yes' );
				}
			}
			print('All Done!');
			die();
		}
		/*----------  Cronjob Funkció kezelője  ----------*/
		
		public function mpr_calculate_intervals($a){
			$interval = 2635200 * $this->MPRGLOBAL;
			if($a == true){
			 	return time($interval);
			} else {
				return $interval;
			}
		}
		public function mpr_cron_intervals($schedules){
			$schedules['mpr_variable_event'] = array(
				'interval' => $this->mpr_calculate_intervals(false),
				'display' => __('Variable months')
			);
			return $schedules;
		}
		public function mpr_cronjob_handler(){
			$passreset = new MULTIPASSRESET();
			$passreset->reset_all_users_password();
		}

	}
?>