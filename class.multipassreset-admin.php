<?php
/*==========================================================================================
= Felelős az Admin kezelő oldal funkciójairól és szükséges kódok regisztrációjáról         =
============================================================================================*/
	class MPR_OPTIONS{
		/*----------  Menűpont és ajax hivás regisztrációja  ----------*/
		public $MPRGLOBAL;
		function __construct(){
			global $MPRGLOBALRESET;
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
			add_action( 'wp_ajax_mpr_reset_all_pass', array($this,'mpr_reset_all_pass_cb'));
			add_action('admin_init', array($this, 'initialize_menu_settings'));
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
				'mpr_cron_section', 
				'Cronjob Settings', 
				array($this, 'mpr_cron_section'), 
				'mpreset' 
			);
			add_settings_section( 
				'mpr_reset_pass_section', 
				'Reset Passwords', 
				array($this, 'mpr_reset_pass_section_cb'), 
				'mpreset' 
			);
			
			add_settings_field( 
				'mpr_cron_activate', 
				'Activate Cronjob', 
				array($this, 'mpr_activate_cronjob_cb'), 
				'mpreset', 
				'mpr_cron_section'
			);
			add_settings_field( 
				'mpr_cron_settings', 
				'Cronjob Settings', 
				array($this, 'mpr_cron_settings_cb'), 
				'mpreset', 
				'mpr_cron_section'
			);

			register_setting( 
				'mpr_cron_activate', 
				array($this, 'mpr_activate_cronjob_cb')
			);
			register_setting( 
				'mpr_cron_settings', 
				array($this, 'mpr_cron_settings_cb')
			);
		}		
		/*----------  A cronjob menü szekció tartalmai  ----------*/
		public function mpr_cron_section(){
			
		}
		public function mpr_activate_cronjob_cb(){
			$meta = (get_site_option('mpr_cron_activate')) ? get_site_option('mpr_cron_activate') : 0;
			echo '<input type="checkbox" name="mpr_cron_activate" id="mpr_cron_activate" value="1" ',($meta == 1) ? 'checked="checked"' : '',' />';
		}
		public function mpr_cron_settings_cb(){
			$meta = (get_site_option('mpr_cron_settings', 1)) ? get_site_option('mpr_cron_settings', 1) : '';
			echo '<input type="number" id="mpr_cron_settings" name="mpr_cron_settings" value="'.$meta.'" /><br />';
			echo '<p class="description">Enter number of months</p>';
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
			  if(isset($_POST["mpr_save_data"])){
			        if(isset($_POST["mpr_cron_settings"])){
			             update_site_option( "mpr_cron_settings", $_POST["mpr_cron_settings"]);
			        }
			        if(isset($_POST["mpr_cron_activate"]) && $_POST["mpr_cron_activate"] == 1){
			             update_site_option( "mpr_cron_activate", $_POST["mpr_cron_activate"]);
			             wp_schedule_event(strtotime($this->mpr_calculate_intervals(true)), 'mpr_monthly_event', 'mpr_cronjob_handler');
			        } else {
			        	 update_site_option( "mpr_cron_activate", 0);
			        	 wp_clear_scheduled_hook('mpr_monthly_event');
			        }
			    }
			?>
			<div class='wrap'>
		          <h2>Settings</h2>
		          <form method='post' action=''>
		          	   
		          	   	<?php
		          	   		settings_fields('mpr_cron_section'); 
		          	   		do_settings_sections('mpreset'); 
		          	   		submit_button( 'Save Changes', 'submit', 'mpr_save_data');
		          	   	?>
		          	   
		          </form>
		     </div>
		     <?php
		}
		/*----------  Ajax Funkció kezelője  ----------*/
		public function mpr_reset_all_pass_cb(){
			$this->mpr_cron_activate();
			print('All Done!');
			die();
		}
		/*----------  Cronjob Funkció kezelője  ----------*/
		public function mpr_cron_activate(){
			if(!wp_next_scheduled ( 'mpr_variable_event' )) {
				wp_schedule_event(time(), 'mpr_variable_event', 'mpr_cronjob_handler');
		    }
		}
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
		/*----------  Cronjob Funkció kezelője  ----------*/
		public function mpr_calculate_intervals($a){
			$number_of_months = get_site_option('mpr_cron_settings');
			$one_month_interval = 2635200;
			$interval = $number_of_months * $one_month_interval;
			if($a == true){
			 	return time($interval);
			} else {
				return $interval;
			}
		}
		public function mpr_cron_intervals($schedules){
			$schedules['mpr_monthly_event'] = array(
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