<?php
if (!defined('ABSPATH')) exit;
/**
 * @package WccGfLawmatics
 */

/**
 * Class WccGfLawmatics_Register.
 *
 * @since 1.0.0
 */
class WccGfLawmatics_Register {


	/**
	 * Register menu.
	 * @since 1.0.0
	 * @access public
	 * @return array {
	 *  @type string The Mortgage Platform page's hook_suffix.
	 *  @type string|false The Mortgage Platform settings page's hook_suffix, or false if the user does not have the capability required.
	 * }
	 */
	public function register_menu() {
		if (class_exists( 'WccEntries' )){
			$menu_page = add_submenu_page(
				WccEntries::$domain,
				WccGfLawmatics::$plugin_name,
				WccGfLawmatics::$plugin_name,
				'manage_options',
				WccGfLawmatics::$domain,
				array( $this, 'integration_index' ),
				10
			);
			return array($menu_page);
		}else{
			$menu_page = add_menu_page(
				WccGfLawmatics::$plugin_name,
				WccGfLawmatics::$plugin_name,
				'manage_options',
				WccGfLawmatics::$domain,
				array( $this, 'integration_index' ),
				'dashicons-table-col-after',
				69
			);

			$settings = add_submenu_page(
				null,
				__('Integration','wcc-gf-lawmatics'),
				__('Integration','wcc-gf-lawmatics'),
				'manage_options',
				'wcc_gf_lawmatics-integration',
				array( $this, 'integration_index' ),
				80
			);;		
		}

		return array($menu_page,$settings);
	}


	/**
	 * Template Admin Page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function integration_index() {
		/**
		 * Template admin page.
		 */
		$tab = (!empty($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : "");
		$tab = sanitize_text_field($tab);
		switch ($tab) {
			case 'settings':
				$this->settings_index();
			break;
			case 'api_error_logs':
				$this->api_error_logs_index();
			break;
			case 'log':
				$this->log_index();
			break;
			case 'configuration':
				$this->configuration_index();
			break;
			default:
				
				if(!empty($_GET['add'])){
					global $wpdb;
					$table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds';  
					if(!empty($_GET['edit_id'])){
						$info = $wpdb->get_row(
							$wpdb->prepare(
						      "SELECT * FROM `$table_name` WHERE id = %d",sanitize_text_field(wp_unslash($_GET['edit_id']))
						   ),ARRAY_A);
						if($info){
							$table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds_to_field';  
							$info['field'] = $wpdb->get_results(
								$wpdb->prepare("SELECT * FROM `$table_name` WHERE feed_id = %s ORDER BY ID ASC",$info['id'])
							,ARRAY_A);
							if($info['field']){
								foreach ($info['field'] as $key => $value) {
									$info['field_keys'][] = $value['crm_field'];
								}
							}
							if($info['account_id'] && $info['module']){
								$table_name = $wpdb->prefix . 'wcc_gf_lawmatics_accounts';
					            $account_info = $wpdb->get_row(
					            	$wpdb->prepare("SELECT * FROM `$table_name` WHERE id = %s ",$info['account_id'])
					            ,ARRAY_A);
					            $forms = new WccGfLawmatics_Forms($account_info);
					            $connector_fields = $forms->getModuleFields($info['module']);    
							}

							if($info['form_id']){
					            $form_fields = array();
					            $form = array();
					            if(class_exists("GFAPI")){
					            	$form = GFAPI::get_form( $info['form_id'] );
					            }

					            if($form){
					                foreach ($form['fields'] as $key => $value) {
					                  if(!$value['label']) continue;
					                  $form_fields[$value['id']] = $value['label'];
					                }
					            }
							}

						}
					}


					$table_name = $wpdb->prefix . 'wcc_gf_lawmatics_accounts';  
					$accounts = $wpdb->get_results(
						"SELECT * FROM `$table_name` ORDER BY date_added DESC"
					,ARRAY_A);

					$forms = array();

			        if ( class_exists( 'GFAPI' ) ) {
			            $posts = GFAPI::get_forms();
			            foreach ($posts as $key => $value) {
			              $forms[]  = array(
			                "name" => $value['title'],
			                "id" => $value['id'],
			              );
			            }
			        }


					require_once plugin_dir_path( __FILE__ ) . '../templates/integration_info.php';
				}else{
					require_once plugin_dir_path( __FILE__ ) . '../templates/integration.php';
				}
			break;
		}
	}



	/**
	 * Template log Page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function log_index() {
		
		/**
		 * Template log page.
		 */
		$tab = (!empty($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : "");
		$tab = sanitize_text_field($tab);
		$feeds = array();     
        $feed_id = (!empty($_GET['feed_id']) ? sanitize_text_field(wp_unslash($_GET['feed_id'])) : 0);
        $feed_id = sanitize_text_field($feed_id);

   	
        global $wpdb;
        $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds';
	    $feeds         = $wpdb->get_results( ("SELECT * FROM `$table_name` WHERE 1  GROUP BY id ORDER BY date_added DESC") ,ARRAY_A);


		require_once plugin_dir_path( __FILE__ ) . '../templates/log.php';

	}


	/**
	 * Register Integration Page.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string The integration page's hook_suffix.
	 */
	protected function register_integration_page() {
		$widget = add_submenu_page(
			null,
			__('Integration','wcc-gf-lawmatics'),
			__('Integration','wcc-gf-lawmatics'),
			'manage_options',
			'wcc_gf_lawmatics-integration',
			array( $this, 'integration_index' ),
			80
		);

		return $widget;

	}

	/**
	 * Register Configuration Page.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string The configuration page's hook_suffix.
	 */
	protected function register_configuration_page() {
		$widget = add_submenu_page(
			null,
			__('Configuration','wcc-gf-lawmatics'),
			__('Configuration','wcc-gf-lawmatics'),
			'manage_options',
			'wcc-gf-to-lawmatics-configuration',
			array( $this, 'configuration_index' ),
			80
		);

		return $widget;

	}

	/**
	 * Template configuration Page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function configuration_index() {
		
		/**
		 * Template configuration page.
		 */

		$redirect_url = esc_url(menu_page_url( 'wcc-gf-to-lawmatics', 0 )).'&wcc_gf_lawmatics_redirect=1';
		$tab = (!empty($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : "");
		$tab = sanitize_text_field($tab);
		$nonce = wp_create_nonce( 'wcc_gf_lawmatics_configuration_delete' );
		$nonce_redirect = wp_create_nonce( 'wcc_gf_lawmatics_configuration_redirect' );
		global $wpdb;
		$table_name = $wpdb->prefix . 'wcc_gf_lawmatics_accounts';  
		if(!empty($_GET['add'])){
			if(!empty($_GET['id'])){
				$info = $wpdb->get_row($wpdb->prepare("SELECT * FROM `$table_name` WHERE id = %d ",sanitize_text_field(wp_unslash($_GET['id']))),ARRAY_A);
			}
			require_once plugin_dir_path( __FILE__ ) . '../templates/configuration_add.php';
		}else{
			$accounts = $wpdb->get_results(("SELECT * FROM `$table_name` ORDER BY date_added DESC"),ARRAY_A);
			require_once plugin_dir_path( __FILE__ ) . '../templates/configuration.php';			
		}

	}



	/**
	 * Register Lawmatics CRM API Error Logs Page.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string The api_error_logs page's hook_suffix.
	 */
	protected function register_api_error_logs_page() {
		$widget = add_submenu_page(
			null,
			__('Lawmatics CRM API Error Logs','wcc-gf-lawmatics'),
			__('Lawmatics CRM API Error Logs','wcc-gf-lawmatics'),
			'manage_options',
			'wcc-gf-to-lawmatics-api_error_logs',
			array( $this, 'api_error_logs_index' ),
			80
		);

		return $widget;

	}

	/**
	 * Template api_error_logs Page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function api_error_logs_index() {
		

		/**
		 * Template api_error_logs page.
		 */
		$tab = (!empty($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : "");
		$tab = sanitize_text_field($tab);
        
        $file_data = get_option("wcc_gf_lawmatics_debug");
        
		require_once plugin_dir_path( __FILE__ ) . '../templates/api_error_logs.php';

	}


	/**
	 * Register Settings Page.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string The settings page's hook_suffix.
	 */
	protected function register_settings_page() {
		$widget = add_submenu_page(
			null,
			__('Settings','wcc-gf-lawmatics'),
			__('Settings','wcc-gf-lawmatics'),
			'manage_options',
			'wcc_gf_lawmatics-settings',
			array( $this, 'settings_index' ),
			80
		);

		return $widget;

	}

	/**
	 * Template settings Page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function settings_index() {
		

		/**
		 * Template settings page.
		 */
		$tab = (!empty($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : "");
		$tab = sanitize_text_field($tab);

        if (!empty($_POST['nonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), WccGfLawmatics::$prefix.'setting' ) && isset( $_POST['submit'] ) ) {
            $notification_subject = isset($_POST['wcc_gf_lawmatics_notification_subject']) ? sanitize_text_field( wp_unslash($_POST['wcc_gf_lawmatics_notification_subject']) ) : "";
            update_option( 'wcc_gf_lawmatics_notification_subject', $notification_subject );
            
            $notification_send_to = isset($_POST['wcc_gf_lawmatics_notification_send_to']) ? sanitize_text_field( wp_unslash($_POST['wcc_gf_lawmatics_notification_send_to']) ) : "";
            update_option( 'wcc_gf_lawmatics_notification_send_to', $notification_send_to );
            
            $ignore_spam_entry = isset($_POST['wcc_gf_lawmatics_ignore_spam_entry']) ? (int)sanitize_text_field(wp_unslash($_POST['wcc_gf_lawmatics_ignore_spam_entry'])) : "";
            update_option( 'wcc_gf_lawmatics_ignore_spam_entry', $ignore_spam_entry );
            
            $uninstall = isset($_POST['wcc_gf_lawmatics_uninstall']) ? (int)sanitize_text_field(wp_unslash($_POST['wcc_gf_lawmatics_uninstall'])) : "";
            update_option( 'wcc_gf_lawmatics_uninstall', $uninstall );
            
        }
        
        $notification_subject = get_option( 'wcc_gf_lawmatics_notification_subject' );
        if ( ! $notification_subject ) {
            $notification_subject = esc_html__( 'API Error Notification', 'wcc-gf-lawmatics' );
        }
        $notification_send_to = get_option( 'wcc_gf_lawmatics_notification_send_to' );
        $ignore_spam_entry = get_option( 'wcc_gf_lawmatics_ignore_spam_entry' );
        $uninstall = get_option( 'wcc_gf_lawmatics_uninstall' );
        $licence = get_site_option( 'wcc_gf_lawmatics_licence' );

        $redirect_url = esc_url(menu_page_url( 'wcc-gf-to-lawmatics', 0 )).'&wcc_gf_lawmatics_redirect=1';

		require_once plugin_dir_path( __FILE__ ) . '../templates/settings.php';

	}


	/**
	 * Register submenus.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_submenu() {
	}



	/**
	 * Set default Settings for Mortgage Platform.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array
	 */
	public function default_settings() {
		$set      = new MP_Calculator_Settings();
		$setarr   = $set->set_fields();
		$settings = array();
		foreach ( $setarr as $option ) {
			$settings[ $option['id'] ] = $option['args']['default'];
		}

		return $settings;
	}

	/**
	 * Register users submenus.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string|false The resulting page's hook_suffix, or false.
	 */
	public function register_users_submenu() {

		return $this->register_users_page();

	}

}