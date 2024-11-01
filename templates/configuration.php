<?php if(!defined('ABSPATH')) exit; ?>
<div class="wrap">
    <?php require "header.php" ?>
    <h1 class="wp-heading-inline">Lawmatics Accounts</h1>
    <a title="Add New Account" class="page-title-action" href="<?php echo esc_url(menu_page_url( 'wcc-gf-to-lawmatics', 0 )); ?>&tab=configuration&add=1">Add New Account</a>
    <div class="wcc-mt-15 wcc-p-0">
        <table class="wcc-table-js widefat wcc_data_table mt-0 wcc-table">
            <thead>
                <tr>
                    <td >Name</td>
                    <td style="width:35%">Credentials</td>
                    <td>Date Added</td>
                    <td>Date Updated</td>
                    <td class="wcc-text-right">Action</td>
                </tr>
            </thead>
            <tbody>
                <?php if($accounts) { ?>
                    <?php foreach ($accounts as $key => $value) { ?>
                        <tr>
                            <td>
                                <?php echo esc_html($value['name']) ?>
                            </td>
                            <td>
                                Client ID : <?php echo ($value['client_id'] ? esc_html($value['client_id']) : "N/A") ?><br/>
                                Client Secret : <?php echo ($value['client_secret'] ? esc_html($value['client_secret']) : "N/A") ?><br/>
                                Authorization Code : <?php echo ($value['authorization_code'] ? esc_html($value['authorization_code']) : "N/A") ?>
                            </td>
                            <td><?php echo esc_html($value['date_added']) ?></td>
                            <td><?php echo esc_html($value['date_updated']) ?></td>
                            <td class="wcc-text-right">
                                <?php if($value['client_id'] && $value['client_secret']){ ?>
                                    <a title="Genarate Authorization Code" href="<?php echo esc_url(WccGfLawmatics::$authurl) ?>?client_id=<?php echo esc_html($value['client_id']) ?>&response_type=code&state=<?php echo esc_html($nonce_redirect) ?>___<?php echo esc_html(base64_encode($value['id'])) ?>&redirect_uri=<?php echo esc_html(urlencode($redirect_url)) ?>">Genarate Authorization Code</a> | 
                                <?php } ?>
                                <a href="<?php echo esc_url(menu_page_url( 'wcc-gf-to-lawmatics', 0 )); ?>&tab=configuration&add=1&id=<?php echo esc_html($value['id']) ?>" title="Edit">Edit</a> | 
                                <a class="wcc-text-danger" onclick="if(!confirm('Sure you want to delete account?')){return false;}" href="<?php echo esc_url(menu_page_url( 'wcc-gf-to-lawmatics', 0 )); ?>&tab=configuration&_wpnonce=<?php echo esc_html($nonce) ?>&wcc_gf_lawmatics_configuration_delete=<?php echo esc_html($value['id']) ?>" title="Delete">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php }else{ ?>
                    <tr>
                        <td colspan="5" class="wcc-text-center">Record not found</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php require "message.php" ?>
</div>