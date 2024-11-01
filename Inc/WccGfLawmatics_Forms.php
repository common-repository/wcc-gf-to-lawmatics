<?php
if (!defined('ABSPATH')) exit;
/**
 * @package WccGfLawmatics
 */

/**
 * Class WccGfLawmatics_Forms.
 *
 * @since 1.0.0
 */
class WccGfLawmatics_Forms {


    var $authtoken;

    var $authorization_code;
    
    function __construct($array = array()) {
        
        if(!empty($array['authorization_code'])){
            $this->authorization_code = $array['authorization_code'];
        }
    }

    /**
     * Forms.
     *
     * @since 1.0.0
     * @access public
     */
    public function list() {
        global $wpdb;
        $forms = array();

        $gf_db_version = get_option( 'gf_db_version' );


        if ( $gf_db_version && version_compare( $gf_db_version, '2.3', '>=' ) ) {
            $table_name = $wpdb->prefix."gf_form";
            $forms = $wpdb->get_results( $wpdb->prepare('SELECT * FROM {$table_name} WHERE is_trash=%d',0) );
        } else {
            $table_name = $wpdb->prefix."rg_form";
            $forms = $wpdb->get_results( $wpdb->prepare('SELECT * FROM {$table_name} WHERE is_trash=%d',0) );
        }
        return $forms;
    }

    public function info( $id ) {
        global $wpdb;

        $gf_db_version = get_option( 'gf_db_version' );
        if ( $gf_db_version && version_compare( $gf_db_version, '2.3', '>=' ) ) {
            $table_name = $wpdb->prefix.'gf_form_meta';
            $form_meta = $wpdb->get_row( $wpdb->prepare('SELECT * FROM {$table_name} WHERE form_id=%d LIMIT 1',$id) );
        } else {
            $table_name = $wpdb->prefix.'rg_form_meta';
            $form_meta = $wpdb->get_row( $wpdb->prepare('SELECT * FROM {$table_name} WHERE form_id=%d LIMIT 1',$id) );
        }
        return $form_meta;
    }
    public function getModuleFields( $module = "" ) {
        if(!$module){
            $module = WccGfLawmatics::$module;
        }
        $fields = array();
        if($this->authorization_code){
            
            $fields = array();

            if($module == "contacts"){
                $fields["first_name"] = array(
                    'label'     => "First name",
                    'type'      => "String",  
                    'required'  => 1,
                    'choices'   => "",
                );
                $fields["last_name"] = array(
                    'label'     => "Last name",
                    'type'      => "String",  
                    'required'  => 1,
                    'choices'   => "",
                );
                $fields["email"] = array(
                    'label'     => "Email",
                    'type'      => "String",  
                    'required'  => 1,
                    'choices'   => "",
                );
            }

        }
        
        return $fields;
    }
    
    function addRecord( $module, $data, $form_id ) {
        
        if ( $module == 'leads' ) {
            $data = array(
                'lead'  => $data,
            );
        } else if ( $module == 'notes' ) {
            $data = array(
                'note'  => $data,
            );
        } else {
            $field = array();
            $data_manage = $data;
            $data["first_name"] = isset($data_manage['first_name']) ? $data_manage['first_name'] : "";
            $data["last_name"] = isset($data_manage['last_name']) ? $data_manage['last_name'] : "";
            $data["email"] = isset($data_manage['email']) ? $data_manage['email'] : "";
            $data["phone"] = isset($data_manage['phone']) ? $data_manage['phone'] : "";

            $field[] = "first_name";
            $field[] = "last_name";
            $field[] = "email";
            $field[] = "phone";



            if(isset($data_manage['first_name'])){
                unset($data_manage['first_name']);
            }
            if(isset($data_manage['last_name'])){
                unset($data_manage['last_name']);
            }
            if(isset($data_manage['email'])){
                unset($data_manage['email']);
            }
            if(isset($data_manage['phone'])){
                unset($data_manage['phone']);
            }


            if(isset($data_manage['notes'])){
                $field[] = "notes";
                $data['notes'][] = array(
                    "name" => "Notes",
                    "body" => $data_manage['notes'],
                );
                unset($data_manage['notes']);
            }
            if($data_manage){
                $data['custom_fields'] = array();
                $field[] = "custom_fields";
                foreach ($data_manage as $key => $value) {
                    $data['custom_fields'][] = array(
                        "id" => $key,
                        "value" => $value,
                    );
                }
            }
        }

        $header = array(
            'Authorization' => 'Bearer '.$this->authorization_code,
            'Content-Type' => 'application/json',
        );
        
        $url = WccGfLawmatics::$apiUrl."contacts?fields=".implode(",",$field);

        $data = wp_json_encode($data);

        $main_response = wp_remote_post($url,
            array(
                'headers' => $header,
                "body"  => $data
            )
        );

        $json_response = wp_remote_retrieve_body($main_response);
        $response = json_decode($json_response,1);

         
        if ( is_wp_error( $main_response ) ) {
            $log = "Form ID: ".$form_id."\n";
            $log .= "Error: ".wp_json_encode( $main_response )."\n";
            $log .= "Date: ".gmdate( 'Y-m-d H:i:s' )."\n\n";
            
            $send_to = get_option( 'wcc_gf_lawmatics_notification_send_to' );
            if ( $send_to ) {
                $to = $send_to;
                $subject = get_option( 'wcc_gf_lawmatics_notification_subject' );
                $body = "Form ID: ".$form_id."<br>";
                $body .= "Error: ".wp_json_encode( $main_response )."<br>";
                $body .= "Date: ".gmdate( 'Y-m-d H:i:s' );
                $headers = array(
                    'Content-Type: text/html; charset=UTF-8',
                );
                wp_mail( $to, $subject, $body, $headers );
            }
            
            $wcc_gf_lawmatics_debug = get_option("wcc_gf_lawmatics_debug");
            $wcc_gf_lawmatics_debug .= $log."\n\n";
            update_option("wcc_gf_lawmatics_debug",$wcc_gf_lawmatics_debug);
        }
        
        return $response;
    }
    
    function getRecords( $module, $email ) {
        
       
        $header = array(
            'Authorization' => 'Bearer '.$this->authorization_code,
            'Content-Type' => 'application/json',
        );
        
        $url = WccGfLawmatics::$apiUrl."contacts/find_by_email/".$email;

        $main_response = wp_remote_get($url,
            array(
                'headers' => $header,
            )
        );

        $json_response = wp_remote_retrieve_body($main_response);
        $response = json_decode($json_response,1);
        
        return isset($response['data']) ? $response : array();
    }
    
    function updateRecord( $module, $data, $record_id, $form_id ) {
        
        if ( $module == 'leads' ) {
            $data = array(
                'lead'  => $data,
            );
        } else {
            
            $field = array();
            $data_manage = $data;
            $data["first_name"] = isset($data_manage['first_name']) ? $data_manage['first_name'] : "";
            $data["last_name"] = isset($data_manage['last_name']) ? $data_manage['last_name'] : "";
            $data["email"] = isset($data_manage['email']) ? $data_manage['email'] : "";
            $data["phone"] = isset($data_manage['phone']) ? $data_manage['phone'] : "";

            $field[] = "first_name";
            $field[] = "last_name";
            $field[] = "email";
            $field[] = "phone";



            if(isset($data_manage['first_name'])){
                unset($data_manage['first_name']);
            }
            if(isset($data_manage['last_name'])){
                unset($data_manage['last_name']);
            }
            if(isset($data_manage['email'])){
                unset($data_manage['email']);
            }
            if(isset($data_manage['phone'])){
                unset($data_manage['phone']);
            }


            if(isset($data_manage['notes'])){
                $field[] = "notes";
                $data['notes'][] = array(
                    "name" => "Notes",
                    "body" => $data_manage['notes'],
                );
                unset($data_manage['notes']);
            }
            if($data_manage){
                $data['custom_fields'] = array();
                $field[] = "custom_fields";
                foreach ($data_manage as $key => $value) {
                    $data['custom_fields'][] = array(
                        "id" => $key,
                        "value" => $value,
                    );
                }
            }
        }
       
        $header = array(
            'Authorization' => 'Bearer '.$this->authorization_code,
            'Content-Type' => 'application/json',
        );
        
        $url = WccGfLawmatics::$apiUrl."contacts/".$record_id."?fields=".implode(",",$field);

        $data = wp_json_encode($data);

        $main_response = wp_remote_post($url,
            array(
                'headers' => $header,
                "body"  => $data
            )
        );

        $json_response = wp_remote_retrieve_body($main_response);
        $response = json_decode($json_response,1);
        
        if ( is_wp_error( $main_response ) ) {
            $log = "Form ID: ".$form_id."\n";
            $log .= "Error: ".wp_json_encode( $main_response )."\n";
            $log .= "Date: ".gmdate( 'Y-m-d H:i:s' )."\n\n";
            
            $send_to = get_option( 'wcc_gf_lawmatics_notification_send_to' );
            if ( $send_to ) {
                $to = $send_to;
                $subject = get_option( 'wcc_gf_lawmatics_notification_subject' );
                $body = "Form ID: ".$form_id."<br>";
                $body .= "Error: ".wp_json_encode( $main_response )."<br>";
                $body .= "Date: ".gmdate( 'Y-m-d H:i:s' );
                $headers = array(
                    'Content-Type: text/html; charset=UTF-8',
                );
                wp_mail( $to, $subject, $body, $headers );
            }
            
            $wcc_gf_lawmatics_debug = get_option("wcc_gf_lawmatics_debug");
            $wcc_gf_lawmatics_debug .= $log."\n\n";
            update_option("wcc_gf_lawmatics_debug",$wcc_gf_lawmatics_debug);
        }
        
        return $response;
    }
    
    function addFile( $data, $form_id ) {
     
        $header = array(
            'Authorization' => 'Token token='.$this->key,
            'Content-Type' => 'multipart/form-data',
            'content-type' => 'multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW',
        );


        
        if ( strpos( $this->url, 'freshsales.io' ) !== false ) {
            $url = $this->url.'/api/documents';
        } else {
            $url = $this->url.'/crm/sales/api/documents';
        }
       
        $data = wp_json_encode($data);

        $main_response = wp_remote_post($url,
            array(
                'headers' => $header,
                "body"  => $data
            )
        );


        $json_response = wp_remote_retrieve_body($main_response);
        $response = json_decode($json_response,1);

         
        if ( is_wp_error( $main_response ) ) {
            $log = "Form ID: ".$form_id."\n";
            $log .= "Error: ".wp_json_encode( $main_response )."\n";
            $log .= "Date: ".gmdate( 'Y-m-d H:i:s' )."\n\n";
            
            $send_to = get_option( 'wcc_gf_lawmatics_notification_send_to' );
            if ( $send_to ) {
                $to = $send_to;
                $subject = get_option( 'wcc_gf_lawmatics_notification_subject' );
                $body = "Form ID: ".$form_id."<br>";
                $body .= "Error: ".wp_json_encode( $main_response )."<br>";
                $body .= "Date: ".gmdate( 'Y-m-d H:i:s' );
                $headers = array(
                    'Content-Type: text/html; charset=UTF-8',
                );
                wp_mail( $to, $subject, $body, $headers );
            }
            
            $wcc_gf_lawmatics_debug = get_option("wcc_gf_lawmatics_debug");
            $wcc_gf_lawmatics_debug .= $log."\n\n";
            update_option("wcc_gf_lawmatics_debug",$wcc_gf_lawmatics_debug);
        }
        
        
        return $response;
    }
}