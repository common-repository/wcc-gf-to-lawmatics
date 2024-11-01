<?php
if (!defined('ABSPATH')) exit;
/**
 * @package WccGfLawmatics
 */

/**
 * Class WccGfLawmatics_Actions.
 *
 * @since 1.0.0
 */
class WccGfLawmatics_Actions {

    public static $wcc_gf_lawmatics_redirect_uri = "";

    /**
     * Actions constructor.
     *
     * @since  1.0.0
     * @access public
     */
    public function __construct() {

        $this->init();
        $this->enqueue();
        
    }


    /**
     * Init Actions and Filters.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function init() {
        add_action( 'init', array( $this , 'checkActions' ));
        

        if(!session_id()){
            session_start();
        }
        add_action( 'admin_enqueue_scripts', array( $this, 'style' ), 10 );

        add_action( 'admin_menu', array( new WccGfLawmatics_Register, 'register_menu' ), 9 );
        add_action( 'admin_menu', array( new WccGfLawmatics_Register, 'register_submenu' ) );
        


        if(class_exists("WccEntries")){
            add_action( 'wcc_entries_form_gform_submit_action', array( $this, 'wcc_gf_lawmatics_integration' ), 20, 3 );
        }else{
            add_action( 'gform_after_submission', array( $this, 'wcc_gf_lawmatics_integration' ), 20, 2 );
        }



        add_action( 'wp_ajax_nopriv_wcc_gf_lawmatics_get_module_fields', array($this,"wcc_gf_lawmatics_get_module_fields") );
        add_action( 'wp_ajax_wcc_gf_lawmatics_get_module_fields', array($this,"wcc_gf_lawmatics_get_module_fields") );


        add_action( 'wp_ajax_nopriv_wcc_gf_lawmatics_get_module_fields_and_form_field', array($this,"wcc_gf_lawmatics_get_module_fields_and_form_field") );
        add_action( 'wp_ajax_wcc_gf_lawmatics_get_module_fields_and_form_field', array($this,"wcc_gf_lawmatics_get_module_fields_and_form_field") );

        add_action( 'wp_ajax_nopriv_wcc_gf_lawmatics_status', array( $this, 'wcc_gf_lawmatics_status' ) );

        add_action( 'wp_ajax_wcc_gf_lawmatics_status', array( $this, 'wcc_gf_lawmatics_status' ) );

        add_action( 'wcc_entries_below_view_page_left', array( $this, 'wcc_entries_details' ) );


    }
    
    function style($hook){
        if ($hook != 'toplevel_page_wcc-gf-to-lawmatics') { // Change 'toplevel_page_my-plugin' to your actual page hook
            return;
        }
        
        wp_register_style(WccGfLawmatics::$prefix . 'main', plugins_url('../assets/css/style.css', __FILE__), array(), WccGfLawmatics::$wcc_gf_lawmatics_version);
        wp_enqueue_style(WccGfLawmatics::$prefix . 'main');
        wp_register_style(WccGfLawmatics::$prefix . 'style_list', plugins_url('../assets/css/style_list.css', __FILE__), array(), WccGfLawmatics::$wcc_gf_lawmatics_version);
        wp_enqueue_style(WccGfLawmatics::$prefix . 'style_list');


        wp_register_style(WccGfLawmatics::$prefix . 'datepicker', plugins_url('../assets/css/jquery-ui.min.css', __FILE__), array(), WccGfLawmatics::$wcc_gf_lawmatics_version);
        wp_enqueue_style(WccGfLawmatics::$prefix . 'datepicker');

        wp_enqueue_script(WccGfLawmatics::$prefix . 'tablesorter', plugins_url('../assets/js/jquery.tablesorter.js', __FILE__), array(), WccGfLawmatics::$wcc_gf_lawmatics_version);
        wp_enqueue_script('jquery-ui-datepicker' );

        wp_localize_script('jquery', 'ajax', array(
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(WccGfLawmatics::$prefix.'ajax-nonce'),
        ));
        
        if(!empty($_GET['tab']) && sanitize_text_field(wp_unslash($_GET['tab'])) == "log"){
            wp_enqueue_script(WccGfLawmatics::$prefix . 'log', plugins_url('../assets/js/log.js', __FILE__), array(), WccGfLawmatics::$wcc_gf_lawmatics_version);
        }
        if(!empty($_GET['tab']) && sanitize_text_field(wp_unslash($_GET['tab'])) == "integration"){
            wp_enqueue_script(WccGfLawmatics::$prefix . 'integration', plugins_url('../assets/js/integration.js', __FILE__), array(), WccGfLawmatics::$wcc_gf_lawmatics_version);
        }
        if(!empty($_GET['tab']) && sanitize_text_field(wp_unslash($_GET['tab'])) == "configuration"){
            wp_enqueue_script(WccGfLawmatics::$prefix . 'configuration', plugins_url('../assets/js/configuration.js', __FILE__), array(), WccGfLawmatics::$wcc_gf_lawmatics_version);
        }
    }


    function wcc_gf_lawmatics_status(){
        $json = array();

        if (!empty($_POST['nonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), WccGfLawmatics::$prefix.'ajax-nonce' ) && !empty($_POST['id']) && isset($_POST['status'])){
            global $wpdb;
            $table_note = $wpdb->prefix . 'wcc_gf_lawmatics_feeds';
            $wpdb->update($table_note,array("status"=>sanitize_text_field(wp_unslash($_POST['status']))),array("id"=>sanitize_text_field(wp_unslash($_POST['id']))));
            $json['success'] = 1;
        }else{

            $json['error'] = 1;
        }
        echo wp_json_encode($json);wp_die();
    }



    public function wcc_gf_lawmatics_get_module_fields(){
        $fields = array();
        if(!empty($_POST['nonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), WccGfLawmatics::$prefix.'ajax-nonce' )){
            if(!empty($_POST['wcc_gf_lawmatics_module']) && !empty($_POST['accounts'])){
                global $wpdb;
                $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_accounts';
                $info = $wpdb->get_row($wpdb->prepare("SELECT * FROM `$table_name` WHERE id = %s ",sanitize_text_field(wp_unslash($_POST['accounts']))),ARRAY_A);
                $forms = new WccGfLawmatics_Forms($info);
                $fields = $forms->getModuleFields(sanitize_text_field(wp_unslash($_POST['wcc_gf_lawmatics_module'])));
            }
            require_once plugin_dir_path( __FILE__ ) . '../templates/integration_field_info.php';
        }else{
            echo "";
        }
        wp_die();
    }

    public function wcc_gf_lawmatics_get_module_fields_and_form_field(){
        $json = array();
        if(!empty($_POST['nonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), WccGfLawmatics::$prefix.'ajax-nonce' )){
            if(!empty($_POST['accounts']) && !empty($_POST['forms']) && !empty($_POST['wcc_gf_lawmatics_module'])){
                global $wpdb;
                $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_accounts';
                $info = $wpdb->get_row($wpdb->prepare("SELECT * FROM `$table_name` WHERE id = %s ",sanitize_text_field(wp_unslash($_POST['accounts']))),ARRAY_A);
                $forms = new WccGfLawmatics_Forms($info);
                $json['connector_fields'] = $forms->getModuleFields(sanitize_text_field(wp_unslash($_POST['wcc_gf_lawmatics_module'])));


                $json['form_fields'] = array();

                $form = array();

                if(class_exists("GFAPI")){
                    $form = GFAPI::get_form( sanitize_text_field(wp_unslash($_POST['forms']) ));
                }

                if($form){
                    foreach ($form['fields'] as $key => $value) {
                      if(!$value['label']) continue;
                      $json['form_fields'][$value['id']] = $value['label'];
                    }
                }

            }
        }
        echo wp_json_encode($json);wp_die();
    }
    function escape_post_array( $data, $escape_function = 'esc_html' ) {
        $escaped_data = array();

        foreach ( $data as $key => $value ) {
            if ( is_array( $value ) ) {
                // Recursively escape nested arrays
                $escaped_data[$key] = $this->escape_post_array( $value, $escape_function );
            } else {
                // Apply the chosen escape function to scalar values
                $escaped_data[$key] = call_user_func( $escape_function, $value );
            }
        }

        return $escaped_data;
    }
    public function checkActions(){
        if(isset($_REQUEST['_wpnonce'])){
            $nonce = sanitize_text_field( wp_unslash($_REQUEST['_wpnonce']) );
            if(!empty($_REQUEST['wcc_gf_lawmatics_feeds_delete_record']) && wp_verify_nonce( $nonce, 'wcc_gf_lawmatics_feeds_delete_record' )){
                global $wpdb;
                $id = sanitize_text_field( absint($_REQUEST['wcc_gf_lawmatics_feeds_delete_record']) );
                $wpdb->delete(
                    "{$wpdb->prefix}wcc_gf_lawmatics_feeds",
                    [ 'id' => $id ]
                );

                $tab = (isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : "dashboard");
                
                $paged = (isset($_GET['paged']) ? sanitize_text_field(wp_unslash($_GET['paged'])) : "1");
                
                $_SESSION['success_msg'] = "Record Delete Sucessfully";

                wp_redirect( admin_url('admin.php?page=wcc-gf-to-lawmatics&tab='.$tab.'&paged='.$paged) );
                exit();
            }
            if(!empty($_REQUEST['wcc_gf_lawmatics_feeds_to_history_delete_gravityforms']) && wp_verify_nonce( $nonce, 'wcc_gf_lawmatics_feeds_to_history_delete_gravityforms' )){
                global $wpdb;
                $id = sanitize_text_field( absint($_REQUEST['wcc_gf_lawmatics_feeds_to_history_delete_gravityforms']) );
                $wpdb->delete(
                    "{$wpdb->prefix}wcc_gf_lawmatics_feeds",
                    [ 'id' => $id ]
                );

                $tab = (isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : "dashboard");
                
                $paged = (isset($_GET['paged']) ? sanitize_text_field(wp_unslash($_GET['paged'])) : "1");
                
                $_SESSION['success_msg'] = "Record Delete Sucessfully";

                wp_redirect( admin_url('admin.php?page=wcc-gf-to-lawmatics&tab='.$tab.'&paged='.$paged) );
                exit();
            }
            // If the delete bulk action is triggered
            if ( wp_verify_nonce( $nonce, 'bulk-toplevel_page_wcc_gf_lawmatics' ) && isset($_GET['page']) && $_GET['page'] == "wcc_gf_lawmatics"  && (( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' ) || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' ) && !empty($_POST['bulk-delete']))
            ) {
                global $wpdb;
                $delete_ids = array_map( 'absint', (array) $_POST['bulk-delete'] );
                // loop over the array of record IDs and delete them
                foreach ( $delete_ids as $id ) {
                    $id = sanitize_text_field($id);
                    if((isset($_GET['tab']) && $_GET['tab'] == "log")){
                        $wpdb->delete(
                            "{$wpdb->prefix}wcc_gf_lawmatics_feeds_to_history",
                            [ 'id' => $id ]
                        );
                        $wpdb->delete(
                            "{$wpdb->prefix}wcc_gf_lawmatics_feeds_to_history_data",
                            [ 'form_entries_id' => $id ]
                        );
                    }else if(((isset($_GET['tab']) && $_GET['tab'] == "integration") || empty($_GET['tab']))){
                        $table = "{$wpdb->prefix}wcc_gf_lawmatics_feeds";
                        $wpdb->delete(
                            $table,
                            [ 'id' => $id ]
                        );
                    }
                }
                $_SESSION['success_msg'] = "Record Delete Sucessfully";
                
                $tab = (isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : "dashboard");
                
                $paged = (isset($_GET['paged']) ? sanitize_text_field(wp_unslash($_GET['paged'])) : "1");
                
                wp_redirect( admin_url('admin.php?page=wcc-gf-to-lawmatics&tab='.$tab.'&paged='.$paged) );
                exit;
            }


            
            if(wp_verify_nonce( $nonce, 'wcc_gf_lawmatics_send_manual' ) && !empty($_REQUEST['wcc_gf_lawmatics_send_manual'])){
                global $wpdb;
                global $wpdb;
                $table_name = $wpdb->prefix . 'wcc_entries';  
                
                $info = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `$table_name` WHERE id = %s",sanitize_text_field(wp_unslash($_REQUEST['wcc_gf_lawmatics_send_manual'])))  ,ARRAY_A);

                if($info){
                    $table_name = $wpdb->prefix . 'wcc_entries_data';  
                    
                    $data = $wpdb->get_results( $wpdb->prepare("SELECT * FROM `$table_name` WHERE form_entries_id = %s  ORDER BY id ASC",$info['id'])  ,ARRAY_A);

                    $data_values = array();
                    foreach ($data as $key => $value) {
                        $data_values[$value['key']] = $value['value'];
                    }
                    $form = array();
                    if(class_exists("GFAPI")){
                        $form = GFAPI::get_form( $info['form_id'] );
                    }
                    $entry = array();
                    if($form){
                        foreach ($form['fields'] as $key => $value) {
                            if($value['label'] && isset($data_values[$value['label']])){
                                $entry[$value['id']] = $data_values[$value['label']];
                            }
                        }
                    }
                    
                    
                    $this->wcc_gf_lawmatics_integration($entry,$form,$info['id']);
                }

                wp_redirect(admin_url('admin.php?page=wcc-gf-to-lawmatics&tab=log&entry_id='.sanitize_text_field(wp_unslash($_REQUEST['wcc_gf_lawmatics_send_manual']))));exit;
            }

            
            if(wp_verify_nonce( $nonce, 'wcc_gf_lawmatics_filter' ) && !empty($_REQUEST['wcc_gf_lawmatics_log_export_data'])){
                $this->wcc_gf_lawmatics_log_export_data();
            }


            if(wp_verify_nonce( $nonce, 'wcc_gf_lawmatics_integration' ) && isset($_POST['wcc_gf_lawmatics_integration_submit'])){
                global $wpdb;
                
                if(!empty($_POST['edit_id'])){

                    $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds';
                    $wpdb->update( $table_name ,array(
                        "account_id" => isset($_POST['accounts']) ? sanitize_text_field(wp_unslash($_POST['accounts'])) : "",
                        "form_id" => isset($_POST['forms']) ? sanitize_text_field(wp_unslash($_POST['forms'])) : "",
                        "name" => isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : "",
                        "action" => isset($_POST['wcc_gf_lawmatics_action']) ? sanitize_text_field(wp_unslash($_POST['wcc_gf_lawmatics_action'])) : "",
                        "module" => isset($_POST['wcc_gf_lawmatics_module']) ? sanitize_text_field(wp_unslash($_POST['wcc_gf_lawmatics_module'])) : "",
                        "status" => isset($_POST['gf_lawmatics']) ? sanitize_text_field(wp_unslash($_POST['gf_lawmatics'])) : "",
                        "date_added" => gmdate("Y-m-d H:i:s"),
                        "date_updated" => gmdate("Y-m-d H:i:s"),
                    ),array(
                        "id" => sanitize_text_field(wp_unslash($_POST['edit_id']))
                    ));

                    $lastid = sanitize_text_field(wp_unslash($_POST['edit_id']));

                    $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds_to_field';
                    $wpdb->delete( $table_name ,array("feed_id" => $lastid));
                    if(!empty($_POST['maping'])){
                        $escaped_values = $this->escape_post_array($_POST['maping']);
                        foreach ($escaped_values as $key => $value) {
                            $wpdb->insert( $table_name ,array(
                                "feed_id" => $lastid,
                                "crm_field" => sanitize_text_field(wp_unslash($key)),
                                "fields_type" => sanitize_text_field(wp_unslash($value['type'])),
                                "value" => ($value['type'] == "standard" ? sanitize_text_field(wp_unslash($value['value'])) : sanitize_text_field(wp_unslash($value['custom_value']))),
                            ));
                        }
                    }
                    $_SESSION['wcc_success_message'] = "Integration Update Successfully";
                }else{
                    //echo "<pre>"; print_r($_POST); echo "</pre>";die; 
                    $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds';

                    $wpdb->insert( $table_name ,array(
                        "account_id" => sanitize_text_field(wp_unslash($_POST['accounts'])),
                        "form_id" => sanitize_text_field(wp_unslash($_POST['forms'])),
                        "name" => sanitize_text_field(wp_unslash($_POST['name'])),
                        "action" => sanitize_text_field(wp_unslash($_POST['wcc_gf_lawmatics_action'])),
                        "module" => sanitize_text_field(wp_unslash($_POST['wcc_gf_lawmatics_module'])),
                        "status" => isset($_POST['gf_lawmatics']) ? sanitize_text_field(wp_unslash($_POST['gf_lawmatics'])) : "",
                        "date_added" => gmdate("Y-m-d H:i:s"),
                        "date_updated" => gmdate("Y-m-d H:i:s"),
                    ));

                    $lastid = $wpdb->insert_id;
                    $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds_to_field';
                    if($lastid && !empty($_POST['maping'])){
                        $escaped_values = $this->escape_post_array($_POST['maping']);
                        foreach ($escaped_values as $key => $value) {
                            $wpdb->insert( $table_name ,array(
                                "feed_id" => $lastid,
                                "crm_field" => sanitize_text_field(wp_unslash($key)),
                                "fields_type" => sanitize_text_field(wp_unslash($value['type'])),
                                "value" => ($value['type'] == "standard" ? sanitize_text_field(wp_unslash($value['value'])) : sanitize_text_field(wp_unslash($value['custom_value']))),
                            ));
                        }
                    }
                    $_SESSION['wcc_success_message'] = "Integration Add Successfully";
                }

                wp_redirect(admin_url( 'admin.php?page=wcc-gf-to-lawmatics&tab=integration'));exit;
            }
            if(wp_verify_nonce( $nonce, 'wcc_gf_lawmatics_configuration_delete' ) && isset($_GET['wcc_gf_lawmatics_configuration_delete'])){
                global $wpdb;
                $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_accounts';  
                $wpdb->delete($table_name,array(
                    "id" => sanitize_text_field(wp_unslash($_GET['wcc_gf_lawmatics_configuration_delete']))
                ));
                $_SESSION['wcc_success_message'] = "Account Delete Successfully";
                wp_redirect(admin_url( 'admin.php?page=wcc-gf-to-lawmatics&tab=configuration'));exit;
            }

                
            if(wp_verify_nonce( $nonce, 'wcc_gf_lawmatics_configuration' ) && isset($_POST['wcc_gf_lawmatics_config_btn'])){
                global $wpdb;
                if(!empty($_POST['edit_id'])){
                    $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_accounts';  
                    $wpdb->update($table_name,array(
                        "name" => isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : "",
                        "client_id" => isset($_POST['wcc_gf_lawmatics_client_id']) ? sanitize_text_field(wp_unslash($_POST['wcc_gf_lawmatics_client_id'])) : "",
                        "client_secret" => isset($_POST['wcc_gf_lawmatics_client_secret']) ? sanitize_text_field(wp_unslash($_POST['wcc_gf_lawmatics_client_secret'])) : "",
                        "authorization_code" => isset($_POST['wcc_gf_lawmatics_authorization_code']) ? sanitize_text_field(wp_unslash($_POST['wcc_gf_lawmatics_authorization_code'])) : "",
                        "date_updated" => gmdate("Y-m-d H:i:s"),
                    ),array(
                        "id" => isset($_POST['edit_id']) ? sanitize_text_field(wp_unslash($_POST['edit_id'])) : ""
                    ));
                    $_SESSION['wcc_success_message'] = "Account Update Successfully";
                }else{
                    $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_accounts';  
                    $wpdb->insert($table_name,array(
                        "name" => isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : "",
                        "client_id" => isset($_POST['wcc_gf_lawmatics_client_id']) ? sanitize_text_field(wp_unslash($_POST['wcc_gf_lawmatics_client_id'])) : "",
                        "client_secret" => isset($_POST['wcc_gf_lawmatics_client_secret']) ? sanitize_text_field(wp_unslash($_POST['wcc_gf_lawmatics_client_secret'])) : "",
                        "authorization_code" => isset($_POST['wcc_gf_lawmatics_authorization_code']) ? sanitize_text_field(wp_unslash($_POST['wcc_gf_lawmatics_authorization_code'])) : "",
                        "date_added" => gmdate("Y-m-d H:i:s"),
                        "date_updated" => gmdate("Y-m-d H:i:s"),
                    ));
                    $_SESSION['wcc_success_message'] = "Account Insert Successfully";
                }
                wp_redirect(admin_url( 'admin.php?page=wcc-gf-to-lawmatics&tab=configuration'));exit;
            }
        }

    
        if(!empty($_REQUEST['wcc_gf_lawmatics_redirect']) && !empty($_REQUEST['code']) && !empty($_REQUEST['state'])){
            $state = sanitize_text_field(wp_unslash($_REQUEST['state']));

            $state = explode("___", $state);
            if(count($state) == 2 && wp_verify_nonce( $state[0], 'wcc_gf_lawmatics_configuration_redirect' )){
                $id = base64_decode($state[1]);
                 
                global $wpdb;
                $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_accounts';  
                
                $info = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `$table_name` WHERE id = %s",$id)  ,ARRAY_A);


                if($info){
                    $post = array(
                        "client_id" => $info['client_id'],
                        "client_secret" => $info['client_secret'],
                        "grant_type" => "authorization_code",
                        "code" => sanitize_text_field(wp_unslash($_REQUEST['code'])),
                        "redirect_uri" => admin_url( 'admin.php?page=wcc-gf-to-lawmatics' ).'&wcc_gf_lawmatics_redirect=1',
                    );
                    $reposnse = wp_remote_post(WccGfLawmatics::$authTokenUrl,array(
                            "body"  => $post
                        )
                    );
                    $reposnse     = json_decode( wp_remote_retrieve_body( $reposnse ),1);
                    if(!empty($reposnse['access_token'])){
                        $wpdb->update($table_name,array("authorization_code" => $reposnse['access_token']),array("id" => $id));
                        $_SESSION['wcc_success_message'] = "Token Update Successfully";
                    }else{
                        $_SESSION['wcc_error_message'] = "Token Update fail";
                    }
                    
                }else{
                    $_SESSION['wcc_error_message'] = "Configuration Account Not Found";
                }
            }else{
                $_SESSION['wcc_error_message'] = "Token Update fail";
            }
            wp_redirect(admin_url('admin.php?page=wcc-gf-to-lawmatics&tab=configuration'));exit;
        }
    }
    public function wcc_gf_lawmatics_log_export_data(){
        if( ! class_exists( 'WP_List_Table' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
        }
        require_once plugin_dir_path( __FILE__ ) . '../templates/log_tbl.php';
        $feed_id = "";

        if (empty($_GET['_wpnonce']) || !wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'wcc_gf_lawmatics_filter' )){
            exit();
        }

        if(!empty($_GET['feed_id'])){
          $feed_id = sanitize_text_field(wp_unslash($_GET['feed_id']));
        }
        $data = array();

        global $wpdb;

        $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds_to_history';
        $where = "";


        $filter_search = (isset($_GET['filter_search']) ? sanitize_text_field(wp_unslash($_GET['filter_search'])) : "");
        $filter_object = (isset($_GET['filter_object']) ? sanitize_text_field(wp_unslash($_GET['filter_object'])) : "");
        $filter_status = (isset($_GET['filter_status']) ? sanitize_text_field(wp_unslash($_GET['filter_status'])) : "");
        $filter_time = (isset($_GET['filter_time']) ? sanitize_text_field(wp_unslash($_GET['filter_time'])) : "");
        $entry_id = (isset($_GET['entry_id']) ? sanitize_text_field(wp_unslash($_GET['entry_id'])) : "");

        $filter_start_date = (isset($_GET['filter_start_date']) ? sanitize_text_field(wp_unslash($_GET['filter_start_date'])) : "");
        $filter_end_date = (isset($_GET['filter_end_date']) ? sanitize_text_field(wp_unslash($_GET['filter_end_date'])) : "");

        $filter_date = (isset($_GET['filter_date']) ? sanitize_text_field(wp_unslash($_GET['filter_date'])) : "");
        $where = "";
        $where_vals = array();
        if($filter_object){
            $where .=  " AND `$table_name`.object = %s";
            $where_vals[] = $filter_object;
        }
        if($filter_status != ""){
            $where .= " AND `$table_name`.status = %s";
            $where_vals[] = $filter_status;
        }
        if($filter_search){
            $where .= " AND `$table_name`.data_sent LIKE %s";
            $where_vals[] = '%'.$filter_search.'%';
        }
        if($entry_id){
            $where .= " AND `$table_name`.entry_id LIKE %s";
            $where_vals[] = '%'.$entry_id.'%';
        }
        if($filter_time == "today"){
            $where .= " AND date(`$table_name`.date_added) = %s";
            $where_vals[] = gmdate("Y-m-d");
        }else if($filter_time == "yesterday"){
              $where .= " AND date(`$table_name`.date_added) = %s";
              $where_vals[] = gmdate("Y-m-d",strtotime("-1 days"));
        }else if($filter_time == "this_week"){
            $day = gmdate('w');
            $week_start = gmdate('Y-m-d', strtotime('-'.$day.' days'));
            $where .= " AND date(`$table_name`.date_added) >= %s";
            $where_vals[] = gmdate("Y-m-d",strtotime($week_start));
            $where .= " AND date(`$table_name`.date_added) <= %s";
            $where_vals[] = gmdate("Y-m-d");
        }else if($filter_time == "last_7"){
              $where .= " AND date(`$table_name`.date_added) >= %s";
              $where_vals[] = gmdate("Y-m-d",strtotime("-7 days"));
              $where .= " AND date(`$table_name`.date_added) <= %s";
              $where_vals[] = gmdate("Y-m-d");
        }else if($filter_time == "last_30"){
              $where .= " AND date(`$table_name`.date_added) >= %s";
              $where_vals[] = gmdate("Y-m-d",strtotime("-30 days"));
              $where .= " AND date(`$table_name`.date_added) <= %s";
              $where_vals[] = gmdate("Y-m-d");
        }else if($filter_time == "this_month"){
              $where .= " AND date(`$table_name`.date_added) >= %s";
              $where_vals[] = gmdate("Y-m-1");
              $where .= " AND date(`$table_name`.date_added) <= %s";
              $where_vals[] = gmdate("Y-m-d");
        }else if($filter_time == "last_month"){
              $where .= " AND date(`$table_name`.date_added) >= %s";
              $where_vals[] = gmdate("Y-m-1",strtotime("-1 months"));
              $where .= " AND date(`$table_name`.date_added) <= %s";
              $where_vals[] = gmdate("Y-m-t",strtotime("-1 months"));
        }else if($filter_time == "custom"){
            if(($filter_start_date)){
              $where .= " AND date(`$table_name`.date_added) >= %s";
              $where_vals[] = $filter_start_date;
            }
            if(($filter_end_date)){
              $where .= " AND date(`$table_name`.date_added) <= %s";
              $where_vals[] = $filter_end_date;
            }
        }

        if($feed_id){
            $where .= " AND feed_id = %s";
            $where_vals[] = $feed_id;
        }



        
        $order_by = "";
        $order = "DESC";
        if(!empty($_GET['orderby'])){
            if($_GET['orderby'] == "date_added"){
                $order_by .= " date_added ";
            }else{
                $order_by = " date_added";
            }
        }else{
            $order_by = " date_added";
        }
        if(!empty($_GET['order'])){
            $order = sanitize_text_field(wp_unslash($_GET['order']));
        }
        $order_by .= " ".$order;
        
        $query             = "SELECT `$table_name`.* FROM `$table_name` WHERE 1 ".$where;

        $sql = $query . " GROUP BY `$table_name`.id ORDER BY ${order_by}";
        

        $data         = $wpdb->get_results( $wpdb->prepare($sql,$where_vals) ,ARRAY_A);

         //echo "<pre>"; print_r($data); echo "</pre>";die; 
        header('Content-disposition: attachment; filename='.gmdate("Y-m-d",current_time('timestamp')).'.csv');
        header("Content-Transfer-Encoding: binary");
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2000 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");
        header('Content-Type: text/html; charset=UTF-8');
        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        $leads=$data;
        $sep=',';



        $field_titles=array();
        $field_titles[] = 'Status';
        $field_titles[] = 'Lawmatics Id';
        $field_titles[] = 'Entry ID';
        $field_titles[] = 'Feed ID';
        $field_titles[] = 'Description';
        $field_titles[] = 'Time';
        

        $fp = fopen('php://output', 'w');
        fputcsv($fp, $field_titles,$sep);
        $sno=0;
        foreach($leads as $lead_row){
          $sno++;
          $_row=array();
          if($lead_row['status'] == 0){
            $_row[] = "Fail";
          }else if($lead_row['status'] == 1){
            $_row[] = "Created";
          }else if($lead_row['status'] == 2){
            $_row[] = "Updated";
          }
        $_row[] = $lead_row['connector_entry_id'];
        $_row[] = $lead_row['entry_id'];
        $_row[] = $lead_row['feed_id'];
        $_row[] = $lead_row['note'];
        $_row[] = $lead_row['date_added'];

          
          fputcsv($fp, $_row,$sep);    
        }
        fclose($fp);
        exit();
    }
    public function browser_info($u_agent=""){ 
        $bname = '';
        $platform = '';
        $version= ""; $ub='';
        if($u_agent == "" && !empty($_SERVER['HTTP_USER_AGENT'])){
            $u_agent=sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']));
        }
        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        }
        elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'Mac';
        }
        elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'Windows';
        }
        ////further refine platform
         if (preg_match('/iphone/i', $u_agent)) {
                    $platform    =   "iPhone";
                } else if (preg_match('/android/i', $u_agent)) {
                    $platform    =   "Android";
                } else if (preg_match('/blackberry/i', $u_agent)) {
                    $platform    =   "BlackBerry";
                } else if (preg_match('/webos/i', $u_agent)) {
                    $platform    =   "Mobile";
                } else if (preg_match('/ipod/i', $u_agent)) {
                    $platform    =   "iPod";
                } else if (preg_match('/ipad/i', $u_agent)) {
                    $platform    =   "iPad";
                }
        // Next get the name of the useragent yes seperately and for good reason
        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
        { 
            $bname = 'Internet Explorer'; 
            $ub = "MSIE"; 
        } 
        elseif(preg_match('/Firefox/i',$u_agent)) 
        { 
            $bname = 'Mozilla Firefox'; 
            $ub = "Firefox"; 
        } 
          elseif(preg_match('/OPR/i',$u_agent)) 
        { 
            $bname = 'Opera'; 
            $ub = "Opera"; 
        }
        elseif(preg_match('/Chrome/i',$u_agent)) 
        { 
            $bname = 'Google Chrome'; 
            $ub = "Chrome"; 
        } 
        elseif(preg_match('/Safari/i',$u_agent)) 
        { 
            $bname = 'Apple Safari'; 
            $ub = "Safari"; 
        }  
        elseif(preg_match('/Netscape/i',$u_agent)) 
        { 
            $bname = 'Netscape'; 
            $ub = "Netscape"; 
        } 
        
        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
        ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }  
        // see how many we have
        $i = count($matches['browser']);
        if ($i > 1) {
            //see if version is before or after the name
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
                $version= $matches['version'][0];
            }
            else {
                $version= $matches['version'][1];
            }
        }
        else if ($i > 0){
            $version= $matches['version'][0];
        }  
        // check if we have a number
        if ($version==null || $version=="") {$version="?";}  
        return array(
            'userAgent' => $u_agent,
            'full_name'      => $bname,
            'name'      => $ub,
            'version'   => $version,
            'platform'  => $platform,
            'pattern'    => $pattern
        );
    }


    public function get_ip(){
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
           $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    /**
     * Ajax Actions.
     *
     * @since  1.0.0
     * @access protected
     */
    function wcc_gf_lawmatics_integration( $entry, $gf ,$insert_id = "") {
         
        $form_id = 0;
        if ( isset( $gf['id'] ) ) {
            $form_id = intval( $gf['id'] );
        }
        
        $posted_data = $entry;
        $not_ignore_spam_entry = 1;
        $ignore_spam_entry = get_option( 'wcc_gf_lawmatics_ignore_spam_entry' );
        if ( $ignore_spam_entry ) {
            if ( $posted_data['status'] == 'spam' ) {
                $not_ignore_spam_entry = 0;
            }
        }
        $trigger_type = "Form Submit";
        
        if ( $form_id && $not_ignore_spam_entry ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds';          
            $list_actions = $wpdb->get_results($wpdb->prepare("SELECT * FROM `$table_name` WHERE form_id = %s AND status=%d ",$form_id,1),ARRAY_A);



            if ( $list_actions ) {
                foreach ($list_actions as $key => $value) {
                    $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds_to_field';  
                    $wcc_gf_lawmatics_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM `$table_name` WHERE feed_id = %s ORDER BY ID ASC",$value['id']),ARRAY_A);
                     
                    $connector_fields = array();
                    $account_info = array();
                    if($value['account_id'] && $value['module']){
                        $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_accounts';
                        $account_info = $wpdb->get_row($wpdb->prepare("SELECT * FROM `$table_name` WHERE id = %s",$value['account_id']),ARRAY_A);
                        $forms = new WccGfLawmatics_Forms($account_info);
                        $connector_fields = $forms->getModuleFields($value['module']);
                    }
                    if($wcc_gf_lawmatics_fields){
                        $data = array();
                        $attachment_fields = array();

                        foreach ( $wcc_gf_lawmatics_fields as $wcc_gf_lawmatics_field_key => $wcc_gf_lawmatics_field ) {
                            if ( isset( $wcc_gf_lawmatics_field['crm_field'] ) && $wcc_gf_lawmatics_field['crm_field'] ) {
                                if ( isset( $wcc_gf_lawmatics_field['crm_field'] ) && $wcc_gf_lawmatics_field['crm_field'] == 'checkbox' ) {
                                    for( $i = 1; $i <= 20; $i++ ) {
                                        if ( isset( $entry[$wcc_gf_lawmatics_field['value'].'.'.$i] ) ) {
                                            if ( $entry[$wcc_gf_lawmatics_field['value'].'.'.$i] ) {
                                                $entry[$wcc_gf_lawmatics_field['value']][] = $entry[$wcc_gf_lawmatics_field['value'].'.'.$i];
                                            }
                                        }
                                    }
                                } else if ( isset( $wcc_gf_lawmatics_field['crm_field'] ) && $wcc_gf_lawmatics_field['crm_field'] == 'multiselect' ) {
                                    $entry[$wcc_gf_lawmatics_field['value']] = json_decode( $entry[$wcc_gf_lawmatics_field['value']] );
                                }
                                
                                if ( is_array( $entry[$wcc_gf_lawmatics_field['value']] ) ) {
                                    $entry[$wcc_gf_lawmatics_field['value']] = implode( ';', $entry[$wcc_gf_lawmatics_field['value']] );
                                }
                                
                                if ( $wcc_gf_lawmatics_field['crm_field'] == 'attachment_field' ) {
                                    $attachment_fields[] = $wcc_gf_lawmatics_field['value'];
                                } else {
                                    if ( strpos( $wcc_gf_lawmatics_field['crm_field'], 'cf_' ) !== false ) {
                                    $data['custom_field'][$wcc_gf_lawmatics_field['crm_field']] = wp_strip_all_tags( $entry[$wcc_gf_lawmatics_field['value']] );
                                    } else if ( strpos( $wcc_gf_lawmatics_field['crm_field'], '###' ) !== false ) {
                                        $wcc_gf_lawmatics_field_data = explode( '###', $wcc_gf_lawmatics_field['crm_field'] );
                                        $data[$wcc_gf_lawmatics_field_data[0]][$wcc_gf_lawmatics_field_data[1]] = wp_strip_all_tags( $entry[$wcc_gf_lawmatics_field['value']] );
                                    } else {

                                        if($wcc_gf_lawmatics_field['fields_type'] == "custom"){
                                            preg_match_all('/{(.*?)}/', $wcc_gf_lawmatics_field['value'], $matches);
                                            $find = isset($matches[0]) ? $matches[0] : array();
                                            $replace = array();
                                            if(isset($matches[1])){
                                                foreach ($matches[1] as $matchesvalue) {
                                                    $replace = isset($entry[$matchesvalue]) ? $entry[$matchesvalue] : "";
                                                }
                                            }
                                            $data[$wcc_gf_lawmatics_field['crm_field']] = str_replace($find, $replace, $wcc_gf_lawmatics_field['value']);
                                        }else{
                                            $data[$wcc_gf_lawmatics_field['crm_field']] = wp_strip_all_tags( $entry[$wcc_gf_lawmatics_field['value']] );
                                        }
                                        
                                        
                                    }
                                }
                            }
                        }
                  
                         
                        if ( $data != null && $value['account_id'] && $value['module']) {
                            
                            $module = $value['module'];
                            $lawmaticss = new WccGfLawmatics_Forms($account_info);
                            $action = $value['action'];
                            if ( ! $action ) {
                                $action = 'create_or_update';
                            }
                            
                            $recent_note = '';
                            if ( isset( $data['recent_note'] ) ) {
                                $recent_note = $data['recent_note'];
                                unset( $data['recent_note'] );
                            }
                            
                            $ids = array();
                            $record_insert = array();
                            $record_update = array();
                            $fail_record = array();
                            if ( $action == 'create' ) {
                                $record = $lawmaticss->addRecord( $module, $data, $form_id );
                                if ( $module == 'leads' ) {
                                    if ( isset( $record['data']['id'] ) ) {
                                        $record_insert[$record['data']['id']] = $record;
                                        $record_insert[$record['data']['id']]['wcc_note'] = "Added To Lead";
                                        $ids[] = $record['data']['id'];
                                    }else{
                                        $fail_data = $record;
                                        $fail_data['wcc_note'] = "Failed To Lead";
                                        $fail_record[] = $fail_data;
                                    }
                                } else {
                                    if ( isset( $record['data']['id'] ) ) {
                                        $record_insert[$record['data']['id']] = $record;
                                        $record_insert[$record['data']['id']]['wcc_note'] = "Added To Contact";
                                        $ids[] = $record['data']['id'];
                                    }else{
                                        $fail_data = $record;
                                        $fail_data['wcc_note'] = "Failed To Contact";
                                        $fail_record[] = $fail_data;
                                    }
                                }
                            } else if ( $action == 'create_or_update' ) {
                                $email = ( isset( $data['emails'] ) ? $data['emails'] : '' );
                                if ( $email ) {
                                    $records = $lawmaticss->getRecords( $module, $email );
                                    if ( $records ) {
                                        
                                        if ( isset( $records['data']['id'] ) ) {
                                            $record_id = $records['data']['id'];
                                            $ids[] = $record_id;
                                            $response = $lawmaticss->updateRecord( $module, $data, $record_id, $form_id );
                                            $record_update[$record_id] = $response;
                                            $record_update[$record_id]['wcc_note'] = "Update Record";
                                        }
                                    
                                    } else {
                                        $record = $lawmaticss->addRecord( $module, $data, $form_id );
                                        if ( $module == 'leads' ) {
                                            if ( isset( $record['data']['id'] ) ) {
                                                $record_insert[$record['data']['id']] = $record;
                                                $record_insert[$record['data']['id']]['wcc_note'] = "Added To Lead";
                                                $ids[] = $record['data']['id'];
                                            }else{
                                                $fail_data = $record;
                                                $fail_data['wcc_note'] = "Failed To Lead";
                                                $fail_record[] = $fail_data;
                                            }
                                        } else {
                                            if ( isset( $record['data']['id'] ) ) {
                                                $record_insert[$record['data']['id']] = $record;
                                                $record_insert[$record['data']['id']]['wcc_note'] = "Added To Contact";
                                                $ids[] = $record['data']['id'];
                                            }else{
                                                $fail_data = $record;
                                                $fail_data['wcc_note'] = "Failed To Contact";
                                                $fail_record[] = $fail_data;
                                            }
                                        }
                                    }
                                } else {
                                    $record = $lawmaticss->addRecord( $module, $data, $form_id );
                                    if ( $module == 'leads' ) {
                                        if ( isset( $record['data']['id'] ) ) {
                                            $record_insert[$record['data']['id']] = $record;
                                            $record_insert[$record['data']['id']]['wcc_note'] = "Added To Lead";
                                            $ids[] = $record['data']['id'];
                                        }else{
                                            $fail_data = $record;
                                            $fail_data['wcc_note'] = "Failed To Lead";
                                            $fail_record[] = $fail_data;
                                        }
                                    } else {
                                        if ( isset( $record['data']['id'] ) ) {
                                            $record_insert[$record['data']['id']] = $record;
                                            $record_insert[$record['data']['id']]['wcc_note'] = "Added To Contact";
                                            $ids[] = $record['data']['id'];
                                        }else{
                                            $fail_data = $record;
                                            $fail_data['wcc_note'] = "Failed To Contact";
                                            $fail_record[] = $fail_data;
                                        }
                                    }
                                }
                            }
                            foreach ($fail_record as $fail_record_key => $fail_record_value) {
                                $note = "";
                                if(isset($fail_data['wcc_note'])){
                                    $note = $fail_data['wcc_note'];
                                    unset($fail_data['wcc_note']);
                                }
                                $insert_data = array(
                                    "feed_id" => $value['id'],
                                    "entry_id" => $insert_id,
                                    "data_sent" => wp_json_encode($data),
                                    "response" => wp_json_encode($fail_record_value),
                                    "trigger_type" => $trigger_type,
                                    "object" => $value['module'],
                                    "note" => $note,
                                    "status" => 0,
                                    "date_added" => gmdate("Y-m-d H:i:s"),
                                    "date_updated" => gmdate("Y-m-d H:i:s"),
                                );
                                $wpdb->insert($wpdb->prefix."wcc_gf_lawmatics_feeds_to_history",$insert_data);
                            }
                            foreach ($record_insert as $record_insert_key => $record_insert_value) {
                                $note = "";
                                if(isset($record_insert_value['wcc_note'])){
                                    $note = $record_insert_value['wcc_note'];
                                    unset($record_insert_value['wcc_note']);
                                }
                                $insert_data = array(
                                    "feed_id" => $value['id'],
                                    "entry_id" => $insert_id,
                                    "connector_entry_id" => $record_insert_key,
                                    "data_sent" => wp_json_encode($data),
                                    "response" => wp_json_encode($record_insert_value),
                                    "trigger_type" => $trigger_type,
                                    "object" => $value['module'],
                                    "note" => $note,
                                    "status" => 1,
                                    "date_added" => gmdate("Y-m-d H:i:s"),
                                    "date_updated" => gmdate("Y-m-d H:i:s"),
                                );
                                $wpdb->insert($wpdb->prefix."wcc_gf_lawmatics_feeds_to_history",$insert_data);
                            }

                            foreach ($record_update as $record_update_key => $record_update_value) {

                                $note = "";
                                if(isset($record_update_value['wcc_note'])){
                                    $note = $record_update_value['wcc_note'];
                                    unset($record_update_value['wcc_note']);
                                }
                                $insert_data = array(
                                    "data_sent" => wp_json_encode($data),
                                    "response" => wp_json_encode($record_update_value),
                                    "note" => $note,
                                    "status" => 2,
                                    "date_updated" => gmdate("Y-m-d H:i:s"),
                                );
                                $wpdb->update($wpdb->prefix."wcc_gf_lawmatics_feeds_to_history",$insert_data,array("connector_entry_id" => $record_update_key));
                            }

                            
                            if ( $recent_note != '' && $ids != null ) {
                                foreach ( $ids as $id ) {
                                    $note_data = array(
                                        'description'       => $recent_note,
                                        'targetable_id'     => $id,
                                        'targetable_type'   => 'Contact',
                                    );

                                    $lawmaticss->addRecord( 'notes', $note_data, $form_id );
                                }
                            }


                        }
                    }
                }
            }
        }
    }


    /**
     * Enqueue Styles and Scripts Actions and Filters.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function enqueue() {

    }


    /**
     * Wrapper for is_plugin_active.
     *
     * @access protected
     * @since 1.0.0
     *
     * @param string $plugin Plugin to check.
     * @return boolean
     */
    protected static function is_plugin_active( $plugin ) {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return is_plugin_active( $plugin );
    }

    public static function wcc_entries_details($info){
        global $wpdb;
        if($info && $info['id'] && $info['form_type'] == "gravity"){
            $insert_id = $info['id'];
            $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds_to_history';
            $feeds         = $wpdb->get_row( $wpdb->prepare("SELECT `$table_name`.* FROM `$table_name` WHERE 1 AND entry_id = %s GROUP BY `$table_name`.id ORDER BY id DESC",$insert_id) ,ARRAY_A);
            require_once plugin_dir_path( __FILE__ ) . '../templates/wcc_entries_details.php';
        }
    }
}