<?php
if(!defined('ABSPATH')) exit;
/**
 * Create a new table class that will extend the WP_List_Table
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class WCC_GF_LAWMATICS_LOG_TABLE extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public $columns_data = array();
    public $feed_id = array();
    
    


    public function prepare_items($args = array())
    {
        if(!empty($args['feed_id'])){
            $this->feed_id = $args['feed_id'];
        }
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
    public function single_row( $item ) {
        //class="'.($item["status"] == 0 ? "unread_data" : "").'"
        ?>
        <tr>
        <?php
        $this->single_row_columns( $item );
        ?>
        </tr>
        <?php
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns['cb'] = '<input type="checkbox" />';
        $columns['status'] = '';
        $columns['connector_entry_id'] = 'Lawmatics Id';
        $columns['entry_id'] = 'Entry ID';
        $columns['feed_id'] = 'Feed ID';
        $columns['note'] = 'Description';
        $columns['date_added'] = 'Time';
        $columns['action'] = 'Detail';

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
            "connector_entry_id" => array("connector_entry_id",true),
            "entry_id" => array("entry_id",true),
            "date_added" => array("date_added",true),
        );
        return $sort;
    }

    public function get_data($args = array())
    {
        if(!empty($args['feed_id'])){
            $this->feed_id = $args['feed_id'];
        }
        return $this->table_data("","",1);
    }
    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data($currentPage,$perPage,$all = "")
    {
        $data = array();

        global $wpdb;

        $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds_to_history';
        $where = "";
        $filter_search = "";
        $filter_object = "";
        $filter_status = "";
        $filter_time = "";
        $entry_id = "";
        $filter_start_date = "";
        $filter_end_date = "";
        $filter_date = "";
        if (!empty($_GET['_wpnonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'wcc_gf_lawmatics_filter' )){
            $filter_search = (isset($_GET['filter_search']) ? sanitize_text_field(wp_unslash($_GET['filter_search'])) : "");
            $filter_object = (isset($_GET['filter_object']) ? sanitize_text_field(wp_unslash($_GET['filter_object'])) : "");
            $filter_status = (isset($_GET['filter_status']) ? sanitize_text_field(wp_unslash($_GET['filter_status'])) : "");
            $filter_time = (isset($_GET['filter_time']) ? sanitize_text_field(wp_unslash($_GET['filter_time'])) : "");
            $entry_id = (isset($_GET['entry_id']) ? sanitize_text_field(wp_unslash($_GET['entry_id'])) : "");
            $filter_start_date = (isset($_GET['filter_start_date']) ? sanitize_text_field(wp_unslash($_GET['filter_start_date'])) : "");
            $filter_end_date = (isset($_GET['filter_end_date']) ? sanitize_text_field(wp_unslash($_GET['filter_end_date'])) : "");
            $filter_date = (isset($_GET['filter_date']) ? sanitize_text_field(wp_unslash($_GET['filter_date'])) : "");
        }
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

        if($this->feed_id){
            $where .= " AND feed_id = %s";
            $where_vals[] = $this->feed_id;
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

        if(!$all){
            $items_per_page = $perPage;

            $page             = $currentPage;

            $offset         = ( $page * $items_per_page ) - $items_per_page;
            $sql = $query . " GROUP BY `$table_name`.id ORDER BY ${order_by} LIMIT ${offset}, ${items_per_page}";
            $sql = $wpdb->prepare($sql,$where_vals);
            $data         = $wpdb->get_results( $sql ,ARRAY_A);
        }else{
            $sql = $query . " GROUP BY `$table_name`.id ORDER BY ${order_by}";
            $sql = $wpdb->prepare($sql,$where_vals);
            $data         = $wpdb->get_results( $sql ,ARRAY_A);
        }
        
        return $data;
    }
    private function get_totalitem()
    {
        $data = array();

        global $wpdb;

        $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds_to_history';
        $filter_search = "";
        $filter_object = "";
        $filter_status = "";
        $filter_time = "";
        $entry_id = "";
        $filter_start_date = "";
        $filter_end_date = "";
        $filter_date = "";
        if (!empty($_GET['_wpnonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'wcc_gf_lawmatics_filter' )){
            
            $filter_search = (isset($_GET['filter_search']) ? sanitize_text_field(wp_unslash($_GET['filter_search'])) : "");
            $filter_object = (isset($_GET['filter_object']) ? sanitize_text_field(wp_unslash($_GET['filter_object'])) : "");
            $filter_status = (isset($_GET['filter_status']) ? sanitize_text_field(wp_unslash($_GET['filter_status'])) : "");
            $filter_time = (isset($_GET['filter_time']) ? sanitize_text_field(wp_unslash($_GET['filter_time'])) : "");
            $entry_id = (isset($_GET['entry_id']) ? sanitize_text_field(wp_unslash($_GET['entry_id'])) : "");
            $filter_start_date = (isset($_GET['filter_start_date']) ? sanitize_text_field(wp_unslash($_GET['filter_start_date'])) : "");
            $filter_end_date = (isset($_GET['filter_end_date']) ? sanitize_text_field(wp_unslash($_GET['filter_end_date'])) : "");
            $filter_date = (isset($_GET['filter_date']) ? sanitize_text_field(wp_unslash($_GET['filter_date'])) : "");
        }
        
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

        if($this->feed_id){
            $where .= " AND feed_id = %s";
            $where_vals[] = $this->feed_id;
        }


         
        $query             = "SELECT `$table_name`.* FROM `$table_name` WHERE 1 ".$where."  GROUP BY `$table_name`.id";

        $total_query     = "SELECT COUNT(1) FROM (${query}) AS ".$table_name;

        
        $total_query = $wpdb->prepare($total_query,$where_vals);

        $total             = $wpdb->get_var( $total_query );
         
        return $total;
    }
    public function get_totalitem_by_status($filter = array())
    {
        $data = array();

        global $wpdb;

        $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds_to_history';


        $filter_search = "";
        $filter_object = "";
        $filter_status = "";
        $filter_time = "";
        $entry_id = "";
        $filter_start_date = "";
        $filter_end_date = "";
        $filter_date = "";
        if (!empty($_GET['_wpnonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'wcc_gf_lawmatics_filter' )){
            
            $filter_search = (isset($_GET['filter_search']) ? sanitize_text_field(wp_unslash($_GET['filter_search'])) : "");
            $filter_object = (isset($_GET['filter_object']) ? sanitize_text_field(wp_unslash($_GET['filter_object'])) : "");
            $filter_status = (isset($_GET['filter_status']) ? sanitize_text_field(wp_unslash($_GET['filter_status'])) : "");
            $filter_time = (isset($_GET['filter_time']) ? sanitize_text_field(wp_unslash($_GET['filter_time'])) : "");
            $entry_id = (isset($_GET['entry_id']) ? sanitize_text_field(wp_unslash($_GET['entry_id'])) : "");
            $filter_start_date = (isset($_GET['filter_start_date']) ? sanitize_text_field(wp_unslash($_GET['filter_start_date'])) : "");
            $filter_end_date = (isset($_GET['filter_end_date']) ? sanitize_text_field(wp_unslash($_GET['filter_end_date'])) : "");
            $filter_date = (isset($_GET['filter_date']) ? sanitize_text_field(wp_unslash($_GET['filter_date'])) : "");
        }
        
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

        if($this->feed_id){
            $where .= " AND feed_id = %s";
            $where_vals[] = $this->feed_id;
        }



         
        $query             = "SELECT `$table_name`.* FROM `$table_name` WHERE 1 ".$where."  GROUP BY `$table_name`.id";

        $total_query     = "SELECT COUNT(1) FROM (${query}) AS ".$table_name;

        $total_query = $wpdb->prepare($total_query,$where_vals);

        $total             = $wpdb->get_var( $total_query );
         
        return $total;
    }

    public function process_bulk_action() {


        //Detect when a bulk action is being triggered...
        if (!empty($_REQUEST['_wpnonce']) && 'restore' === $this->current_action() && !empty($_GET['wcc_gf_lawmatics_feeds_to_history_restore_gravityforms'] ) ) {

            // In our file that handles the request, verify the nonce.
            $nonce = sanitize_text_field( wp_unslash($_REQUEST['_wpnonce']) );

            if ( ! wp_verify_nonce( $nonce, 'wcc_gf_lawmatics_feeds_to_history_restore_gravityforms' ) ) {
            die( 'Go get a life script kiddies' );
            }
            else {
            self::restore_gravityforms( sanitize_text_field(absint( $_GET['wcc_gf_lawmatics_feeds_to_history_restore_gravityforms'] ) ) );
            $_SESSION['success_msg'] = "Record Restore Sucessfully";
            wp_redirect(admin_url('admin.php?page=wcc_gf_lawmatics_feeds_to_history&paged'.(isset($_GET['paged']) ? sanitize_text_field(wp_unslash($_GET['paged'])) : "1").'&filter_status='.(isset($_GET['filter_status']) ? sanitize_text_field(wp_unslash($_GET['filter_status'])) : "")));
            exit;
            }

        }

        //Detect when a bulk action is being triggered...
        if (!empty($_REQUEST['_wpnonce']) &&  'trash' === $this->current_action() && !empty($_GET['wcc_gf_lawmatics_feeds_to_history_trash_gravityforms']) ) {

            // In our file that handles the request, verify the nonce.
            $nonce = sanitize_text_field( wp_unslash($_REQUEST['_wpnonce']) );

            if ( ! wp_verify_nonce( $nonce, 'wcc_gf_lawmatics_feeds_to_history_trash_gravityforms' ) ) {
            die( 'Go get a life script kiddies' );
            }
            else {
            self::trash_gravityforms( sanitize_text_field(absint( $_GET['wcc_gf_lawmatics_feeds_to_history_trash_gravityforms'] ) ) );
            $_SESSION['success_msg'] = "Record Trash Sucessfully";
            wp_redirect( admin_url('admin.php?page=wcc_gf_lawmatics_feeds_to_history&paged'.(isset($_GET['paged']) ? sanitize_text_field(wp_unslash($_GET['paged'])) : "1").'&filter_status='.(isset($_GET['filter_status']) ? sanitize_text_field(wp_unslash($_GET['filter_status'])) : "")) );
            exit;
            }

        }
         
        //Detect when a bulk action is being triggered...
        if (!empty($_REQUEST['_wpnonce']) && 'delete' === $this->current_action() && !empty($_GET['wcc_gf_lawmatics_feeds_to_history_delete_gravityforms']) ) {

            // In our file that handles the request, verify the nonce.
            $nonce = sanitize_text_field( wp_unslash($_REQUEST['_wpnonce']) );

            if ( ! wp_verify_nonce( $nonce, 'wcc_gf_lawmatics_feeds_to_history_delete_gravityforms' ) ) {
            die( 'Go get a life script kiddies' );
            }
            else {
            self::delete_gravityforms( sanitize_text_field(absint( $_GET['wcc_gf_lawmatics_feeds_to_history_delete_gravityforms'] ) ) );
            $_SESSION['success_msg'] = "Record Delete Sucessfully";
            wp_redirect( admin_url('admin.php?page=wcc_gf_lawmatics_feeds_to_history&paged'.(isset($_GET['paged']) ? sanitize_text_field(wp_unslash($_GET['paged'])) : "1").'&filter_status='.(isset($_GET['filter_status']) ? sanitize_text_field(wp_unslash($_GET['filter_status'])) : "")));
            exit;
            }

        }

        // If the delete bulk action is triggered
        if ( !empty($_POST['bulk-delete']) && ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
        || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
        ) {
            $delete_ids = array_map( 'absint', (array) $_POST['bulk-delete'] );

            // loop over the array of record IDs and delete them
            foreach ( $delete_ids as $id ) {
                self::delete_gravityforms( $id );
            }
            $_SESSION['success_msg'] = "Record Delete Sucessfully";
            wp_redirect( admin_url('admin.php?page=wcc_gf_lawmatics_feeds_to_history&paged'.(isset($_GET['paged']) ? sanitize_text_field(wp_unslash($_GET['paged'])) : "1").'&filter_status='.(isset($_GET['filter_status']) ? sanitize_text_field(wp_unslash($_GET['filter_status'])) : "")));
            exit;
        }
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

        submit_button( esc_html__( 'Apply', 'wcc-gf-lawmatics' ), 'action', '', false, array( 'id' => "doaction$two" , "title" => "Apply" ) );
        submit_button( esc_html__( 'Export as CSV', 'wcc-gf-lawmatics' ), 'export', '', false, array( 'id' => "doexport$two" , "title" => "Export as CSV" ) );
    }
    public function get_bulk_actions() {
        $actions = [
            'bulk-delete' => 'Delete',
        ];

        return $actions;
    }

    public function column_status($item){
        $return = "";
        if($item['status'] == "0"){
            $return = '<i title="Fail" class="dashicons-no-alt dashicons wcc-text-danger"></i>';
        }else if($item['status'] == "1"){
            $return = '<i title="Created" class="dashicons-yes-alt dashicons wcc-text-success"></i>';
        }else if($item['status'] == "2"){
            $return = '<i title="Updated" class="dashicons-yes-alt dashicons wcc-text-success"></i>';
        }
        return $return;
    }
    public function column_connector_entry_id($item){
        return $item['connector_entry_id'] ? $item['connector_entry_id'] : "N/A";
    }
    public function column_entry_id($item){
        $html = "<a href='".admin_url('admin.php?page=wcc_entries&view='.$item['entry_id'])."'>".$item['entry_id']."</a>";
        return $html;
    }
    public function column_feed_id($item){
        return "<a href='".admin_url('admin.php?page=wcc-gf-to-lawmatics&tab=integration&add=1&edit_id='.$item['feed_id'])."'>#".$item['feed_id']."</a>";
    }
    public function column_note($item){
        return $item['note'];
    }
    public function column_action($item){
        $html = "<div class='wcc_response_data' style='display:none'>";
            $html .= $item['response'];
        $html .= "</div>";
        $sent_data = array();
        $data_sent = $item['data_sent'] ? json_decode($item['data_sent']) : array();
        $html .= "<div class='wcc_sent_data' style='display:none'>";
            $html .= "<table class='table widefat striped'>";
            if($data_sent && is_array($data_sent)){
                foreach ($data_sent as $key => $value) {
                    $html .= "<tr>";
                        $html .= "<th>";
                            $html .= esc_html($key);
                        $html .= "</th>";
                        $html .= "<td>";
                            $html .= esc_html($value);
                        $html .= "</td>";
                    $html .= "</tr>";
                }
            }
            $html .= "</table>";
        $html .= "</div>";
        $html .= "<div class='wcc_more_data' style='display:none'>";
            $html .= "<table class='table widefat striped'>";
                $html .= "<tr>";
                    $html .= "<th>";
                        $html .= "Trigger";
                    $html .= "</th>";
                    $html .= "<td>";
                        $html .= esc_html($item['trigger_type']);
                    $html .= "</td>";
                $html .= "</tr>";
                $html .= "<tr>";
                    $html .= "<th>";
                        $html .= "Object";
                    $html .= "</th>";
                    $html .= "<td>";
                        $html .= esc_html($item['object']);
                    $html .= "</td>";
                $html .= "</tr>";
            $html .= "</table>";
        $html .= "</div>";
        return $html.'<i class="wcc_more_detail dashicons dashicons-editor-expand" title="Expand Detail"></i>';
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


    public static function delete_gravityforms( $id ) {
        global $wpdb;

        $wpdb->delete(
            "{$wpdb->prefix}wcc_gf_lawmatics_feeds_to_history",
            [ 'id' => $id ]
        );
        $wpdb->delete(
            "{$wpdb->prefix}wcc_gf_lawmatics_feeds_to_history_data",
            [ 'form_entries_id' => $id ]
        );
    }
    public static function trash_gravityforms( $id ) {
        global $wpdb;

        $wpdb->update(
            "{$wpdb->prefix}wcc_gf_lawmatics_feeds_to_history",
            array("status" => 2),
            [ 'id' => $id ]
        );
    }
    public static function restore_gravityforms( $id ) {
        global $wpdb;

        $wpdb->update(
            "{$wpdb->prefix}wcc_gf_lawmatics_feeds_to_history",
            array("status" => 1),
            [ 'id' => $id ]
        );
    }
}