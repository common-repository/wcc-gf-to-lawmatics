<?php if(!defined('ABSPATH')) exit; ?>
<div class="box box-primary box-solid wcc-mt-10">
  <div class="box-header">
    <i class="dashicons dashicons-admin-plugins"></i> Lawmatics
    <div class="box-tools">
      <button class="box-close-open btn btn-default" type="button"><i class="dashicons dashicons-arrow-up"></i></button>
    </div>
  </div>
  <div class="box-body">

  	<?php if($feeds){ ?>
  		<?php if($feeds['status'] == 0){ $class= "wcc-text-danger"; }else{ $class= "wcc-text-success"; } ?>
  		<div class="<?php echo esc_attr($class) ?> wcc-mb-10"><?php if($feeds['status'] == 0){ echo esc_attr("Failed to send ".$feeds['connector_entry_id']); }else if($feeds['status'] == 1){ echo esc_attr("Added to send ".$feeds['connector_entry_id']); }else if($feeds['status'] == 2){ echo esc_attr("Updated to send ".$feeds['connector_entry_id']); } ?></div>
  	<?php } ?>

  	<a href="<?php echo esc_url(admin_url("admin.php?page=wcc-gf-to-lawmatics&tab=log&wcc_gf_lawmatics_send_manual=".$info['id']."&_wpnonce=".wp_create_nonce( 'wcc_gf_lawmatics_send_manual' ))) ?>" class="button-primary wcc-my-btn" title="Send to Lawmatics">Send to Lawmatics</a>
  	<?php if($feeds){ ?>
  		<a href="<?php echo esc_url(admin_url("admin.php?page=wcc-gf-to-lawmatics&tab=log&entry_id=".$feeds['entry_id'])) ?>" class="button-primary wcc-my-btn" title='Go to Logs'>Go to Logs</a>
  	<?php } ?>

  </div>
</div>