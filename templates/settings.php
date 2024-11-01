<?php if(!defined('ABSPATH')) exit; ?>
<div class="wrap">
    <?php require "header.php" ?>
    <?php if (!empty($_POST['nonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), WccGfLawmatics::$prefix.'setting' ) && isset( $_POST['submit'] ) ) { ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e( 'Settings saved.', 'wcc-gf-lawmatics' ); ?></p>
        </div>
    <?php } ?>
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Settings', 'wcc-gf-lawmatics' ); ?></h1>
    <div class="wcc-card wcc-mt-15">
        <form method="post">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="wcc_gf_lawmatics_notification_subject" title="<?php esc_html_e( 'API Error Notification', 'wcc-gf-lawmatics' ); ?>" ><?php esc_html_e( 'API Error Notification', 'wcc-gf-lawmatics' ); ?></label></th>
                        <td>
                            <label for="wcc_gf_lawmatics_notification_subject" title="<?php esc_html_e( 'Subject', 'wcc-gf-lawmatics' ); ?>"><?php esc_html_e( 'Subject', 'wcc-gf-lawmatics' ); ?></label><br>
                            <input class="wcc-input" type="text" name="wcc_gf_lawmatics_notification_subject" id="wcc_gf_lawmatics_notification_subject" value="<?php echo esc_attr($notification_subject); ?>" />
                            <p class="description"><?php esc_html_e( 'Enter The Subject.', 'wcc-gf-lawmatics' ); ?></p><br><br>
                            <label for="wcc_gf_lawmatics_notification_send_to" title="<?php esc_html_e( 'Send To', 'wcc-gf-lawmatics' ); ?>"><?php esc_html_e( 'Send To', 'wcc-gf-lawmatics' ); ?></label><br>
                            <input class="wcc-input" type="text" name="wcc_gf_lawmatics_notification_send_to" id="wcc_gf_lawmatics_notification_send_to" value="<?php echo esc_attr($notification_send_to); ?>" />
                            <p class="description"><?php esc_html_e( 'Enter the email address. For multiple email addresses, you can add email address by comma separated.', 'wcc-gf-lawmatics' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wcc-switch" title="<?php esc_html_e( 'Ignore Spam Entry?', 'wcc-gf-lawmatics' ); ?>"><?php esc_html_e( 'Ignore Spam Entry?', 'wcc-gf-lawmatics' ); ?></label></th>
                        <td>
                            <input type="hidden" name="wcc_gf_lawmatics_ignore_spam_entry" value="0" />
                            <div class="switch-wrapper switch-display-options">
                                <div  class="wcc-switch-control">
                                    <input  id="wcc-switch" value="1"  type="checkbox" name="wcc_gf_lawmatics_ignore_spam_entry" class="wcc-switch" <?php echo ( $ignore_spam_entry ? esc_attr(' checked') : '' ); ?>>
                                    <label  for="wcc-switch" class="blue">
                                    </label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wcc-uninstall" title="<?php esc_html_e( 'Delete Data On Uninstall?', 'wcc-gf-lawmatics' ); ?>"><?php esc_html_e( 'Delete Data On Uninstall?', 'wcc-gf-lawmatics' ); ?></label></th>
                        <td>
                            <input type="hidden" name="wcc_gf_lawmatics_uninstall"  value="0" />

                            <div class="switch-wrapper switch-display-options">
                                <div  class="wcc-switch-control">
                                    <input  id="wcc-uninstall" value="1"  type="checkbox" name="wcc_gf_lawmatics_uninstall" class="wcc-switch" <?php echo ( $uninstall ? esc_attr(' checked') : '' ); ?>>
                                    <label  for="wcc-uninstall" class="blue">
                                    </label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label title="<?php esc_html_e( 'Redirect URL', 'wcc-gf-lawmatics' ); ?>"><?php esc_html_e( 'Redirect URL', 'wcc-gf-lawmatics' ); ?></label></th>
                        <td>
                            <?php echo esc_url($redirect_url) ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p>
                <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce(WccGfLawmatics::$prefix.'setting')); ?>">
                <input type='submit' class='button button-primary' name="submit" value="<?php esc_html_e( 'Save Changes', 'wcc-gf-lawmatics' ); ?>" title="<?php esc_html_e( 'Save Changes', 'wcc-gf-lawmatics' ); ?>" />
            </p>
        </form>
    </div>
    <?php require "message.php" ?>
</div>