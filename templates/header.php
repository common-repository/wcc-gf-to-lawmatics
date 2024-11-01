<?php if(!defined('ABSPATH')) exit; ?>
<h2 class="nav-tab-wrapper">
    <a href="<?php echo esc_url(menu_page_url( 'wcc-gf-to-lawmatics', 0 )); ?>&tab=integration" class="nav-tab <?php echo (empty($tab) || $tab == "wcc_gf_lawmatics" || $tab == "integration") ? esc_attr("nav-tab-active") : ""; ?>">Integration</a>
    <a href="<?php echo esc_url(menu_page_url( 'wcc-gf-to-lawmatics', 0 )); ?>&tab=configuration" class="nav-tab <?php echo (isset($tab) && $tab == "configuration") ? esc_attr("nav-tab-active") : ""; ?>">Configuration</a>
    <a href="<?php echo esc_url(menu_page_url( 'wcc-gf-to-lawmatics', 0 )); ?>&tab=log" class="nav-tab <?php echo (isset($tab) && $tab == "log") ? esc_attr("nav-tab-active") : ""; ?>">Lawmatics Log</a>
    <a href="<?php echo esc_url(menu_page_url( 'wcc-gf-to-lawmatics', 0 )); ?>&tab=api_error_logs" class="nav-tab <?php echo (isset($tab) && $tab == "api_error_logs") ? esc_attr("nav-tab-active") : ""; ?>">Lawmatics API Error Logs</a>
    <a href="<?php echo esc_url(menu_page_url( 'wcc-gf-to-lawmatics', 0 )); ?>&tab=settings" class="nav-tab <?php echo (isset($tab) && $tab == "settings") ? esc_attr("nav-tab-active") : ""; ?>">Settings</a>    
</h2>
<?php if(isset($_SESSION['wcc_success_message'])) { ?>
    <div class="wcc_success_message"><?php echo esc_html(sanitize_text_field($_SESSION['wcc_success_message'])) ?></div>
<?php unset($_SESSION['wcc_success_message']); } ?>
<?php if(isset($_SESSION['wcc_error_message'])) { ?>
    <div class="wcc_error_message"><?php echo esc_html(sanitize_text_field($_SESSION['wcc_error_message'])) ?></div>
<?php unset($_SESSION['wcc_error_message']); } ?>