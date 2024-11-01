<?php
if(!defined('ABSPATH')) exit;
/**
 * Create a new table class that will extend the WP_List_Table
 */
class WCC_FORM_ENTRIES_GFLAWMATICS_LIST_TABLE extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public $columns_data = array();
    public $form_id = array();
    public function prepare_items()
    {
        $this->process_bulk_action();

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        //usort( $data, array( &$this, 'sort_data' ) );


        $perPage = 20;
        $currentPage = $this->get_pagenum();
        $totalItems = $this->get_totalitem();

        $data = $this->table_data($currentPage,$perPage);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns['cb'] = '<input type="checkbox" />';
        $columns['starred'] = '';
        $columns['name'] = 'Name';
        $columns['module'] = 'Module';
        $columns['form'] = 'Form';
        $columns['date_added'] = 'Date Added';
        
        $this->columns_data = $columns;
        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        $sort = array(
            "date_added" => array("date_added",true),
            "name" => array("name",true),
        );
        return $sort;
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data($currentPage,$perPage)
    {
        $data = array();

        global $wpdb;

        $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds';
        
         
        $where = "";
        $order_by = "";
        $order = "DESC";
        if(!empty($_GET['orderby'])){
            if($_GET['orderby'] == "name"){
                $order_by .= " name ";
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
        
        $query             = "SELECT {$table_name}.* FROM {$table_name} WHERE 1 ";

        
        $items_per_page = $perPage;

        $page             = $currentPage;

        $offset         = ( $page * $items_per_page ) - $items_per_page;
        $sql = ($query . " GROUP BY ".$table_name.".id ORDER BY ".$order_by." LIMIT ${offset}, ${items_per_page}");
        $data         = $wpdb->get_results( $sql ,ARRAY_A);

        return $data;
    }
    private function get_totalitem()
    {
        $data = array();

        global $wpdb;

        $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds';
        $where = "";


         
        $query             = "SELECT * FROM ".$table_name."  WHERE 1  GROUP BY id";

        $total_query     = "SELECT COUNT(1) FROM (${query}) AS {$table_name}";

        $total             = $wpdb->get_var( $total_query );
         
        return $total;
    }

    public function process_bulk_action() {
         
    }
    public function no_items() {
      esc_html_e( 'No Record Avaliable.', 'wcc-gf-lawmatics' );
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        if(array_key_exists($column_name, $this->columns_data)){
            return !empty($item[ $column_name ]) ? $item[ $column_name ] : "N/A";
        }else{
                return print_r( $item, true ) ;
        }
    }
    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
        );
    }

    public function column_starred( $item ) {
         
        $html = '<div class="switch-wrapper switch-display-options">';
            $html .= '<div  class="wcc-switch-control">';
                $html .= '<input data-id="'.$item['id'].'" id="wcc-switch'.$item['id'].'" value="1"  type="checkbox" name="gf_lawmatics" class="wcc-switch wcc-switch-status" '.( (isset($item['status']) && $item['status']) ? ' checked' : '' ).'>';
                $html .= '<label  for="wcc-switch'.$item['id'].'" class="green">';
                $html .= '</label>';
            $html .= '</div>';
        $html .= '</div>';
        return sprintf(
            $html
        );
    }
    public function column_name( $item ) {
        $paged = 1;
        if(!empty($_GET['paged'])){
            $paged = sanitize_text_field(wp_unslash($_GET['paged']));
        }
        $delete_nonce = wp_create_nonce( 'wcc_gf_lawmatics_feeds_delete_record' );
        $actions = array(
            'view'          => '<a title="Edit" href="'.admin_url('admin.php?page=wcc-gf-to-lawmatics&tab=integration&add=1&edit_id='.$item['id']).'">Edit</a>',
            'trash'          => '<a title="Delete" onclick="if(!confirm(\'Sure You Want To Delete Record?\')){return false;}" href="'.admin_url('admin.php?page=wcc-gf-to-lawmatics&tab=integration&action=delete&wcc_gf_lawmatics_feeds_delete_record='.$item['id']).'&paged='.$paged.'&_wpnonce='.$delete_nonce.'">Delete</a>',
        );


        return sprintf(
            '%1$s %2$s',
            $item['name'],
            $this->row_actions($actions)
        );
    }
    public function column_module( $item ) {
        return $item['module'];
    }
    public function column_form( $item ) {
        $name = '';
        if(class_exists("GFAPI")){
            $form = GFAPI::get_form( $item['form_id'] );
            if($form){
                $name = $form['title'];
            }
        }
        return $name ? $name : "N/A";
    }
    public function bulk_actions($which = '') {
        if ( is_null( $this->_actions ) ) {
            $this->_actions = $this->get_bulk_actions();

            /**
             * Filters the items in the bulk actions menu of the list table.
             *
             * The dynamic portion of the hook name, `$this->screen->id`, refers
             * to the ID of the current screen.
             *
             * @since 3.1.0
             * @since 5.6.0 A bulk action can now contain an array of options in order to create an optgroup.
             *
             * @param array $actions An array of the available bulk actions.
             */
            $this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

            $two = '';
        } else {
            $two = '2';
        }

        if ( empty( $this->_actions ) ) {
            return;
        }

        ?>
        <label for="bulk-action-selector- <?php echo esc_attr( $which ) ?>" class="screen-reader-text"><?php echo esc_html__('Select bulk action', 'wcc-gf-lawmatics'); ?></label>
        <select name='action<?php echo esc_attr( $two ) ?>' id='bulk-action-selector-<?php echo esc_attr( $which ) ?>'>
            <option value='-1'><?php echo esc_html__('Bulk actions', 'wcc-gf-lawmatics'); ?></option>
        <?php

        foreach ( $this->_actions as $key => $value ) {
            if ( is_array( $value ) ) {
                ?>
                <optgroup label='<?php echo esc_attr( $key ) ?>'>
                );
                <?php
                foreach ( $value as $name => $title ) {
                    $class = ( 'edit' === $name ) ? ' class="hide-if-no-js"' : '';
                    ?>
                        <option value='<?php echo esc_attr( $name ) ?>' <?php echo esc_attr( $class ) ?>><?php echo esc_attr( $title ) ?></option>
                    <?php
                }
                ?>
                </optgroup>
                <?php
            } else {
                $class = ( 'edit' === $key ) ? ' class="hide-if-no-js"' : '';
                ?>
                <option value='<?php echo esc_attr( $key ) ?>' <?php echo esc_attr( $class ) ?>><?php echo esc_attr( $value ) ?></option>
                <?php
            }
        }
        ?>
        </select>
        <?php


        submit_button( esc_html__( 'Apply', 'wcc-gf-lawmatics' ), 'action', '', false, array( 'title' => 'Apply', 'id' => "doaction$two" ) );
    }
    public function get_bulk_actions() {
        $actions = [
            'bulk-delete' => 'Delete'
        ];

        return $actions;
    }


    public function column_date_added($item)
    {
        return $item['date_added'];
        
    }


    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'date_added';
        $order = 'desc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = sanitize_text_field(wp_unslash($_GET['orderby']));
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = sanitize_text_field(wp_unslash($_GET['order']));
        }


        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }


    public static function delete_record( $id ) {
        global $wpdb;

        $wpdb->delete(
            "{$wpdb->prefix}wcc_gf_lawmatics_feeds",
            [ 'id' => $id ]
        );
        
    }
}