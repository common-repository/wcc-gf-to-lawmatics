<?php if(!defined('ABSPATH')) exit; ?>
<div class="wrap">
    <?php require "header.php" ?>
    <div class="wcc-card wcc-mt-15">
        <h2 class="wcc-card-title"><?php esc_html_e( 'Lawmatics CRM Configuration', 'wcc-gf-lawmatics' ); ?></h2>
        <form method="post">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="name" title="<?php esc_html_e( 'Name', 'wcc-gf-lawmatics' ); ?>" ><?php esc_html_e( 'Name (required)', 'wcc-gf-lawmatics' ); ?></label></th>
                        <td>
                            <input class="wcc-input" type="text" name="name" placeholder="Name" id="name" required="required" value="<?php echo (isset($info['name']) ? esc_attr($info['name']) : ""); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wcc_gf_lawmatics_client_id" title="<?php esc_html_e( 'Client ID (required)', 'wcc-gf-lawmatics' ); ?>" ><?php esc_html_e( 'Client ID (required)', 'wcc-gf-lawmatics' ); ?></label></th>
                        <td>
                            <input class="wcc-input" type="text" name="wcc_gf_lawmatics_client_id" placeholder="Client ID" id="wcc_gf_lawmatics_client_id" required="required" value="<?php echo (isset($info['client_id']) ? esc_attr($info['client_id']) : ""); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wcc_gf_lawmatics_client_secret" title="<?php esc_html_e( 'Client Secret (required)	', 'wcc-gf-lawmatics' ); ?>" ><?php esc_html_e( 'Client Secret (required)', 'wcc-gf-lawmatics' ); ?></label></th>
                        <td>
                            <input placeholder="Client Secret" class="wcc-input" type="text" name="wcc_gf_lawmatics_client_secret" id="wcc_gf_lawmatics_client_secret" required="required" value="<?php echo (isset($info['client_secret']) ? esc_attr($info['client_secret']) : ""); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wcc_gf_lawmatics_authorization_code" title="<?php esc_html_e( 'Authorization Code', 'wcc-gf-lawmatics' ); ?>" ><?php esc_html_e( 'Authorization Code ', 'wcc-gf-lawmatics' ); ?></label></th>
                        <td>
                            <input placeholder="Authorization Code" class="wcc-input" type="text" name="wcc_gf_lawmatics_authorization_code" id="wcc_gf_lawmatics_authorization_code" value="<?php echo (isset($info['authorization_code']) ? esc_attr($info['authorization_code']) : ""); ?>" />
                            <?php if(!empty($info['client_secret']) && !empty($info['client_id'])){ ?>
                                <a class="wcc-a-tag" title="Click Here to Genarate Authorization Code" href="<?php echo esc_url(WccGfLawmatics::$authurl) ?>?client_id=<?php echo esc_attr($info['client_id']) ?>&response_type=code&state=<?php echo esc_attr($nonce_redirect) ?>___<?php echo esc_attr(base64_encode($info['id'])) ?>&redirect_uri=<?php echo esc_attr(urlencode($redirect_url)) ?>">Click Here to Genarate Authorization Code</a>
                            <?php } ?>
                            <div>
                                <small>Please add 'Client ID' and 'Client Secret' and hit the Add button to Generate the 'Authorization Code'</small>
                            </div>
                            <div>
                                <small>After Successfully save the keys, from the Account list page, Click on the 'Generate Authorization Code' link and Login to your Lawmatics Account to Generate the code.</small>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p>
                <input type="hidden" name="edit_id" id="edit_id" value="<?php echo (isset($info['id']) ? esc_attr($info['id']) : ""); ?>" />
                <input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce( 'wcc_gf_lawmatics_configuration' )) ?>">
                <input type='submit' class='button button-primary' name="wcc_gf_lawmatics_config_btn" value="<?php if(isset($info['id'])){ echo esc_attr__("Update", 'wcc-gf-lawmatics'); } else { echo esc_attr__("Add", 'wcc-gf-lawmatics'); } ?>"  title="<?php if(isset($info['id'])){ echo esc_attr__("Update", 'wcc-gf-lawmatics'); } else { echo esc_attr__("Add", 'wcc-gf-lawmatics'); } ?>" />
                <a href="<?php echo esc_url(menu_page_url( 'wcc-gf-to-lawmatics', 0 )); ?>&tab=configuration" class="button wcc-my-btn-danger" title="Cancel">Cancel</a>
            </p>
        </form>
    </div>
    <?php require "message.php" ?>
</div>