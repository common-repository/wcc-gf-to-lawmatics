<?php if(!defined('ABSPATH')) exit; ?>
<?php 

  if( ! class_exists( 'WP_List_Table' ) ) {
      require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
  }
  require_once "log_tbl.php";

  $array = array(
    "feed_id" => $feed_id,
  );
  $tbl = new WCC_GF_LAWMATICS_LOG_TABLE();
  $tbl->prepare_items($array);
  
  $filter_status = "";
  $orderby = "";
  $order = "";
  $filter_search = "";
  $filter_object = "";
  $filter_status = "";
  $filter_time = "";
  $filter_start_date = "";
  $filter_end_date = "";

  if (!empty($_GET['_wpnonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'wcc_gf_lawmatics_filter' )){
    $filter_status = (isset($_GET['filter_status']) ? sanitize_text_field(wp_unslash($_GET['filter_status'])) : "");
    $orderby = (isset($_GET['orderby']) ? sanitize_text_field(wp_unslash($_GET['orderby'])) : "");
    $order = (isset($_GET['order']) ? sanitize_text_field(wp_unslash($_GET['order'])) : "");
    $filter_search = (isset($_GET['filter_search']) ? sanitize_text_field(wp_unslash($_GET['filter_search'])) : "");
    $filter_object = (isset($_GET['filter_object']) ? sanitize_text_field(wp_unslash($_GET['filter_object'])) : "");
    $filter_status = (isset($_GET['filter_status']) ? sanitize_text_field(wp_unslash($_GET['filter_status'])) : "");
    $filter_time = (isset($_GET['filter_time']) ? sanitize_text_field(wp_unslash($_GET['filter_time'])) : "");
    $filter_start_date = (isset($_GET['filter_start_date']) ? sanitize_text_field(wp_unslash($_GET['filter_start_date'])) : "");
    $filter_end_date = (isset($_GET['filter_end_date']) ? sanitize_text_field(wp_unslash($_GET['filter_end_date'])) : "");
  }



  $url = "";
  $data = array();
  if($feed_id){
    $data['feed_id'] = $feed_id;
  }

  if($filter_search){
    $data['filter_search'] = $filter_search;
  }
  
  if($filter_object){
    $data['filter_object'] = $filter_object;
  }
  
  if($filter_status){
    $data['filter_status'] = $filter_status;
  }
  
  if($filter_time){
    $data['filter_time'] = $filter_time;
  }
  
  if($filter_start_date){
    $data['filter_start_date'] = $filter_start_date;
  }
  
  if($filter_end_date){
    $data['filter_end_date'] = $filter_end_date;
  }

  if($data){
    $url = "&".http_build_query($data);
  }


  $times=array("today"=>"Today","yesterday"=>"Yesterday","this_week"=>"This Week","last_7"=>"Last 7 Days","last_30"=>"Last 30 Days","this_month"=>"This Month","last_month"=>"Last Month","custom"=>"Select Range"); 
?>
<div class="wrap">
  <?php require "header.php" ?>
  <?php if(!empty($_SESSION['success_msg'])){ ?>
  <div id="message" class="updated notice is-dismissible"><p><?php echo esc_html(sanitize_text_field($_SESSION['success_msg'])) ; ?>.</p></div>
  <?php unset($_SESSION['success_msg']); } ?>
  <div>
    <h1 class="wp-heading-inline"><label for="wcc_feed_id"><?php esc_html_e( 'Lawmatics Log', 'wcc-gf-lawmatics' ); ?></label></h1>
    <select name="feed_id" onchange="location='<?php echo esc_url(menu_page_url( 'wcc-gf-to-lawmatics', 0 )); ?>&tab=log&feed_id='+this.value">
      <option value="">All Feeds</option>
      <?php if($feeds){ ?>
        <?php foreach ($feeds as $key => $value) { ?>
          <option <?php echo $feed_id == $value['id'] ? esc_attr("selected='selected'") : "" ?> value="<?php echo esc_attr( $value['id'], 'wcc-gf-lawmatics' ); ?>"><?php echo esc_attr( $value['name'], 'wcc-gf-lawmatics' ); ?></option>
        <?php } ?>
        </optgroup>
      <?php } ?>
    </select>
  </div>
  <div class="wcc-d-flex wcc-justify-end">
    <div>
      <form action="" id="search_form">
        <input type="hidden" name="feed_id" value="<?php echo esc_attr($feed_id) ?>">
        <input type="hidden" name="page" value="wcc-gf-to-lawmatics">
        <input type="hidden" name="tab" value="log">
        <input type="hidden" name="orderby" value="<?php echo esc_attr($orderby) ?>">
        <input type="hidden" name="order" value="<?php echo esc_attr($order) ?>">
        <input type="text" placeholder="<?php esc_attr_e('Search','wcc-gf-lawmatics') ?>" value="<?php echo esc_html($filter_search)  ?>" name="filter_search" class="wcc-input-inline">

        <select name="filter_object" class="wcc-input-inline">
          <option value=""><?php esc_html_e('All Object','wcc-gf-lawmatics') ?></option>
          <?php
          foreach(WccGfLawmatics::$modules as $val){
              $sel="";

              if( $filter_object == $val)
                $sel="selected='selected'";
                ?>
                <option value='<?php echo esc_attr($val) ?>' <?php echo esc_attr($sel) ?>><?php echo esc_attr($val) ?></option>
                <?php
          }
          ?>
        </select>

        <select name="filter_status" class="wcc-input-inline">
          <option value=""><?php esc_html_e('All Status','wcc-gf-lawmatics') ?></option>
          <option <?php echo ($filter_status == "0" ? esc_attr("selected='selected'") : "") ?> value="0"><?php esc_html_e('Fail','wcc-gf-lawmatics') ?></option>
          <option <?php echo ($filter_status == "1" ? esc_attr("selected='selected'") : "") ?> value="1"><?php esc_html_e('Created','wcc-gf-lawmatics') ?></option>
          <option <?php echo ($filter_status == "2" ? esc_attr("selected='selected'") : "") ?> value="2"><?php esc_html_e('Updated','wcc-gf-lawmatics') ?></option>
        </select>

        <select name="filter_time" class="wcc-input-inline">
          <option value=""><?php esc_html_e('All Times','wcc-gf-lawmatics') ?></option>
          <?php
          foreach($times as $key => $val){
            $sel="";

            if($filter_time == $key)
              $sel="selected='selected'";
            ?>
            <option value='<?php echo esc_attr($key) ?>' <?php echo esc_attr($sel) ?>><?php echo esc_attr($val) ?></option>
            <?php 
          }
          ?>
        </select>

        <div  class="custom_range_fields <?php if($filter_time == "custom"){ echo esc_attr("wcc-inline-block"); } ?> wcc-float-left"> 
          <input type="text" name="filter_start_date" placeholder="<?php esc_attr_e('From Date','wcc-gf-lawmatics') ?>" value="<?php echo esc_attr($filter_start_date); ?>" class="wcc-date-picker wcc-input-inline" style="width: 100px">
          <input type="text" class="wcc-date-picker wcc-input-inline" value="<?php echo esc_attr($filter_end_date); ?>" placeholder="<?php esc_attr_e('To Date','wcc-gf-lawmatics') ?>" name="filter_end_date"  style="width: 100px">
        </div>
       
        <input type="hidden" name="wcc_gf_lawmatics_log_export_data" id="wcc_gf_lawmatics_log_export_data" value="">
        <input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce( 'wcc_gf_lawmatics_filter' )) ?>">

        <button style="display: flex;align-items: center;" type="submit" title="<?php esc_attr_e('Search','wcc-gf-lawmatics') ?>" class="button-secondary button wcc-input-inline"><i class="dashicons dashicons-search"></i> <?php esc_html_e('Search','wcc-gf-lawmatics') ?></button>
 
      </form>
    </div>
  </div>

  <form method="post">
    <?php $tbl->display(); ?>
  </form>

  <?php require "message.php" ?>
</div>

<div class="wcc-modal fade" id="viewDataModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close close_model"  title="Close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h2 class="modal-title">Data</h2>
      </div>
      <div class="modal-body">


        <div class="wcc-row">
          <div class="wcc-col-12">
            <div class="wcc-card wcc-mt-0">
              <h2 class="wcc-card-title"><?php esc_html_e( 'Data Sent', 'wcc-gf-lawmatics' ); ?></h2>
              <div class="wcc-card-body wcc-data-sent-data">

              </div>
            </div>
          </div>
        </div>
        <div class="wcc-row">
          <div class="wcc-col-12">
            <div class="wcc-card wcc-mt-0">
              <h2 class="wcc-card-title"><?php esc_html_e( 'Response', 'wcc-gf-lawmatics' ); ?></h2>
              <div class="wcc-card-body ">
                <pre class="wcc-data-response-data"></pre>
              </div>
            </div>
          </div>

        </div>
        <div class="wcc-row">

          <div class="wcc-col-12">
            <div class="wcc-card wcc-mt-0">
              <h2 class="wcc-card-title"><?php esc_html_e( 'More Details', 'wcc-gf-lawmatics' ); ?></h2>
              <div class="wcc-card-body wcc-data-more-data">

              </div>
            </div>
          </div>

        </div>

      </div>
    </div>
  </div>
</div>