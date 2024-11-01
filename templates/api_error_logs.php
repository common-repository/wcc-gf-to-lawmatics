<?php if(!defined('ABSPATH')) exit; ?>
<div class="wrap">
    <?php require "header.php" ?>
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Lawmatics API Error Logs', 'wcc-gf-lawmatics' ); ?></h1>
    <div class="wcc-card wcc-mt-15">
        <?php
            if ( $file_data ) {
                ?>
                <pre style="overflow: scroll;">
                    <?php
                        print_r( esc_html($file_data) );
                    ?>
                </pre>
                <form method="post">
                    <p>
                        <input type='submit' class='button-primary wcc-my-btn' name="submit" value="<?php esc_html_e( 'Clear API Error Logs', 'wcc-gf-lawmatics' ); ?>" title="<?php esc_html_e( 'Clear API Error Logs', 'wcc-gf-lawmatics' ); ?>" />
                    </p>
                </form>
            <?php
            } else {
                ?><p><?php esc_html_e( 'No API error logs found.', 'wcc-gf-lawmatics' ); ?></p><?php
            }
        ?>
    </div>
    <?php require "message.php" ?>
</div>