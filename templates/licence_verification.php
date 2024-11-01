<?php if(!defined('ABSPATH')) exit; ?>
<?php require "header.php" ?>
<div class="wcc-card wcc-mt-15">
    <h2 class="wcc-card-title"><?php esc_html_e( 'Licence Verification', 'wcc-gf-lawmatics' ); ?></h2>
    <?php
        if ( isset( $success ) ) {
            if ( $success ) {                            
                 ?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php echo esc_html($message); ?></p>
                    </div>
                <?php
            } else {
                update_site_option( 'wcc_gf_lawmatics_licence', 0 );
                ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php echo esc_html($message); ?></p>
                    </div>
                <?php
            }
        }
    ?>
    <form method="post">
        <table class="form-table">                    
            <tbody>
                <tr>
                    <th scope="row"><label title="<?php esc_html_e( 'Purchase Code', 'wcc-gf-lawmatics' ); ?>" for="wcc_gf_lawmatics_purchase_code"><?php esc_html_e( 'Purchase Code', 'wcc-gf-lawmatics' ); ?></label></th>
                    <td>
                        <input name="wcc_gf_lawmatics_purchase_code" id="wcc_gf_lawmatics_purchase_code" type="text" class="wcc-input" value="<?php echo esc_attr($wcc_gf_lawmatics_purchase_code); ?>" />
                    </td>
                </tr>
            </tbody>
        </table>
        <p>
            <input type='submit' class='wcc-my-btn button-primary' name="verify" value="<?php esc_html_e( 'Verify', 'wcc-gf-lawmatics' ); ?>" title="<?php esc_html_e( 'Verify', 'wcc-gf-lawmatics' ); ?>" />
            <input type='submit' class='wcc-my-btn button-primary' name="unverify" value="<?php esc_html_e( 'Unverify', 'wcc-gf-lawmatics' ); ?>" title="<?php esc_html_e( 'Unverify', 'wcc-gf-lawmatics' ); ?>" />
        </p>
    </form>   
</div>

<?php require "message.php" ?>