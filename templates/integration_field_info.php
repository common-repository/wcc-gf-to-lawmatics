<?php if(!defined('ABSPATH')) exit; ?>
<?php
    if ( !empty($fields) ) {
        $exclude_fields = array();
        ?>
        <h3><?php esc_html_e( 'Dropdown Fields', 'wcc-gf-lawmatics' ); ?></h3>
        <?php foreach ( $fields as $field_key => $field ) {
            if ( $field['choices'] != null && ! in_array( $field_key, $exclude_fields ) ) {
                ?>
                <div class="wcc-card wcc-mt-15">
                    <h4 class="wcc-card-title"><?php echo esc_html($field['label']); ?></h4>
                    <table class="wcc-table">
                        <thead>
                            <tr>
                                <th style="width: 30%"><?php esc_html_e( 'Option Value', 'wcc-gf-lawmatics' ); ?></th>
                                <th><?php esc_html_e( 'Option Label', 'wcc-gf-lawmatics' ); ?></th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th style="width: 30%"><?php esc_html_e( 'Option Value', 'wcc-gf-lawmatics' ); ?></th>
                                <th><?php esc_html_e( 'Option Label', 'wcc-gf-lawmatics' ); ?></th>
                            </tr>
                        </tfoot>
                        <?php
                            foreach ( $field['choices'] as $choice ) {
                                ?>
                                    <tr>
                                        <td style="width: 30%"><?php echo esc_html($choice['id']); ?></td>
                                        <td><?php echo esc_html($choice['name']); ?></td>
                                    </tr>
                                <?php
                            }
                        ?>
                    </table>
                </div>
                <?php
            }
        }
        ?>
<?php } ?>