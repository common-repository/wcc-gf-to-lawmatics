<?php
  if(!defined('ABSPATH')) exit;
  if( ! class_exists( 'WP_List_Table' ) ) {
      require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
  }
  require_once "integration_tbl.php";
  $tbl = new WCC_FORM_ENTRIES_GFLAWMATICS_LIST_TABLE();
  $tbl->prepare_items();
?>
<div class="wrap">
    <?php require "header.php" ?>
    <h1 class="wp-heading-inline">Lawmatics Integrations</h1>
    <a title="Add New Integration" class="page-title-action" href="<?php echo esc_url(menu_page_url( 'wcc-gf-to-lawmatics', 0 )); ?>&tab=integration&add=1">Add New Integration</a>
    <form method="post">
        <?php $tbl->display(); ?>
    </form>
    <?php require "message.php" ?>
</div>