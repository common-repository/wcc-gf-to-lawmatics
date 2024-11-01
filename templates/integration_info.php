<?php if(!defined('ABSPATH')) exit; ?>
<div class="wrap">
    <?php require "header.php" ?>
    <form method="post">
        <div class="wcc-card wcc-mt-15">
            <h2 class="wcc-card-title"><?php echo esc_html(empty($info['id']) ? "Add" : "Edit") ?> Integration</h2>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label title="<?php esc_html_e( 'Name', 'wcc-gf-lawmatics' ); ?>" for='accounts'><?php esc_html_e( 'Name', 'wcc-gf-lawmatics' ); ?></label></th>
                        <td>
                            <input type="text" name="name" class="wcc-input" id="name" required="required" value="<?php echo (isset($info['name']) ? esc_html($info['name']) : "") ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label title="<?php esc_html_e( 'Lawmatics Account', 'wcc-gf-lawmatics' ); ?>" for='accounts'><?php esc_html_e( 'Lawmatics Account', 'wcc-gf-lawmatics' ); ?></label></th>
                        <td>
                            <select name="accounts" class="wcc-input" id="accounts" required="required">
                                <option value=""><?php esc_html_e( 'Select a account', 'wcc-gf-lawmatics' ); ?></option>
                                <?php
                                    foreach ( $accounts as $key => $value ) {
                                        $selected = '';
                                        if ( isset($info['account_id']) && $value['id'] == $info['account_id'] ) {
                                            $selected = ' selected="selected"';
                                        }
                                        ?>
                                            <option value="<?php echo esc_attr($value['id']); ?>"<?php echo esc_attr($selected); ?>><?php echo esc_attr($value['name']); ?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label title="<?php esc_html_e( 'Module', 'wcc-gf-lawmatics' ); ?>" for='wcc_gf_lawmatics_module'><?php esc_html_e( 'Module', 'wcc-gf-lawmatics' ); ?></label></th>
                        <td>
                            <select name="wcc_gf_lawmatics_module" class="wcc-input" id="wcc_gf_lawmatics_module" required="required">
                                <option value=""><?php esc_html_e( 'Select a module', 'wcc-gf-lawmatics' ); ?></option>
                                <?php
                                    $modules = WccGfLawmatics::$modules;
                                    foreach ( $modules as $key => $value ) {
                                        $selected = '';
                                        if ( isset($info['module']) && $key == $info['module'] ) {
                                            $selected = ' selected="selected"';
                                        }
                                        ?>
                                            <option value="<?php echo esc_attr($key); ?>"<?php echo esc_attr($selected); ?>><?php echo esc_attr($value); ?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label title="<?php esc_html_e( 'Form', 'wcc-gf-lawmatics' ); ?>" for='forms'><?php esc_html_e( 'Form', 'wcc-gf-lawmatics' ); ?></label></th>
                        <td>
                            <select name="forms" class="wcc-input" id="forms" required="required">
                                <option value=""><?php esc_html_e( 'Select a form', 'wcc-gf-lawmatics' ); ?></option>
                                <?php
                                    foreach ( $forms as $key => $value ) {
                                        $selected = '';
                                        if ( isset($info['form_id']) && $value['id'] == $info['form_id'] ) {
                                            $selected = ' selected="selected"';
                                        }
                                        ?>
                                            <option value="<?php echo esc_attr($value['id']); ?>"<?php echo esc_attr($selected); ?>><?php echo esc_attr($value['name']); ?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label title="<?php esc_html_e( 'Lawmatics CRM Integration?', 'wcc-gf-lawmatics' ); ?>" for="wcc-switch"><?php esc_html_e( 'Lawmatics CRM Integration?', 'wcc-gf-lawmatics' ); ?></label></th>
                        <td>
                            <input type="hidden" name="gf_lawmatics" value="0" />
                            <div class="switch-wrapper switch-display-options">
                                <div  class="wcc-switch-control">
                                    <input  id="wcc-switch" value="1"  type="checkbox" name="gf_lawmatics" class="wcc-switch" <?php echo esc_attr( (isset($info['status']) && $info['status']) ? esc_attr(' checked') : '' ); ?>>
                                    <label  for="wcc-switch" class="blue">
                                    </label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label title="<?php esc_html_e( 'Action Event', 'wcc-gf-lawmatics' ); ?>" for="wcc_gf_lawmatics_action"><?php esc_html_e( 'Action Event', 'wcc-gf-lawmatics' ); ?></label></th>
                        <td>
                            <fieldset>
                                <label title="<?php esc_html_e( 'Create Module Record', 'wcc-gf-lawmatics' ); ?>">
                                    <input type="radio" name="wcc_gf_lawmatics_action" id="wcc_gf_lawmatics_action" value="create"<?php echo esc_attr( (!isset($info['action']) || $info['action'] == 'create') ? esc_attr(' checked="checked"') : '' ); ?> /> <?php esc_html_e( 'Create Module Record', 'wcc-gf-lawmatics' ); ?></label>&nbsp;&nbsp;
                                <label title="<?php esc_html_e( 'Create/Update Module Record', 'wcc-gf-lawmatics' ); ?>"><input type="radio" name="wcc_gf_lawmatics_action" value="create_or_update"<?php echo esc_attr( (isset($info['action']) && $info['action'] == 'create_or_update') ? esc_attr(' checked="checked"') : '' ); ?> /> <?php esc_html_e( 'Create/Update Module Record', 'wcc-gf-lawmatics' ); ?></label>
                            </fieldset>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="maping_fields" >
            <?php if(!empty($info['field'])){ ?>
                <?php foreach ($info['field'] as $key => $value) { ?>
                    <div class="wcc-card wcc-mt-15 connector_setting_block" data-maping_key="<?php echo esc_attr($value['crm_field']) ?>">
                        <table class="widefat wcc_data_table wcc-mt-0 wcc-table form-table">
                            <thead>
                                <tr>
                                    <th class="connector_field_name" >
                                        <?php echo (isset($connector_fields[$value['crm_field']]) ? esc_html($connector_fields[$value['crm_field']]['label']) : "N/A") ?><?php if(!empty($connector_fields[$value['crm_field']]['required'])){ ?> <span class='wcc-text-danger'>(Required)</span><?php } ?>
                                    </th>
                                    <th class="wcc-text-right"><?php if(empty($connector_fields[$value['crm_field']]['required'])){ ?><i title="Remove" class="dashicons dashicons-trash removeMaping"></i><?php } ?></th>
                                </tr>
                                <tr>
                                    <th class="connector_field_name_info" colspan="2">
                                        Name: <?php echo esc_html($value['crm_field']) ?> , Type: <?php echo (isset($connector_fields[$value['crm_field']]) ? esc_html($connector_fields[$value['crm_field']]['type']) : "N/A") ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Field Type</td>
                                    <td>
                                        <select name="maping[<?php echo esc_attr($value['crm_field']) ?>][type]" class="wcc-input maping_field_type">
                                            <option <?php echo ($value['fields_type'] == "standard" ? esc_attr("selected") : "") ?> value="standard"><?php esc_html_e( 'Standard Field', 'wcc-gf-lawmatics' ); ?></option>
                                            <option <?php echo ($value['fields_type'] == "custom" ? esc_attr("selected") : "") ?> value="custom"><?php esc_html_e( 'Custom Value', 'wcc-gf-lawmatics' ); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Value</td>
                                    <td>
                                        <div class="wcc_custom_value"  style="display: <?php echo ($value['fields_type'] == "standard" ? esc_attr("none") : esc_attr("block")) ?>;">
                                            <textarea class="wcc-input" name="maping[<?php echo esc_attr($value['crm_field']) ?>][custom_value]"><?php echo ($value['fields_type'] == "custom" ? esc_attr($value['value']) : "") ?></textarea>
                                        </div>
                                        <select name="maping[<?php echo esc_attr($value['crm_field']) ?>][value]" class="wcc-input form_value_selector">
                                            <option value=''>Select Field</option>
                                            <?php if(isset($form_fields)){ ?>
                                                <?php foreach ($form_fields as $kkk => $vvv) { ?>
                                                    <option <?php echo ($value['fields_type'] == "standard" && $kkk == $value['value'] ? esc_attr("selected") : "") ?> value="<?php echo esc_attr($kkk) ?>"><?php echo esc_attr($vvv) ?></option>
                                                <?php } ?>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
        <div class="wcc-card wcc-p-0 wcc-mt-15 connector_map_fields" <?php if(empty($info['account_id']) || empty($info['account_id']) || empty($info['account_id'])){ ?> style="display:none;" <?php } ?>>
            <table class="form-table widefat wcc_data_table wcc-mt-0 wcc-table">
                <thead>
                    <tr>
                        <th colspan="2">Add New Field</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="connector_fields" class="wcc-input" id="connector_fields">
                                <option value="">Select Field</option>
                                <?php if(!empty($connector_fields)){ ?>                                    
                                    <?php foreach ($connector_fields as $key => $value) { ?>
                                        <option data-type='<?php echo esc_attr($value['type']) ?>' <?php if(isset($info['field_keys']) && in_array($key, $info['field_keys'])){?>disabled="disabled"<?php } ?> value='<?php echo esc_attr($key) ?>'><?php echo esc_attr($value['label']) ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </td>
                        <td>
                            <button title="Add Field" type="button" class="button button-default" id="wcc_add_maping_val"><i class="dashicons dashicons-plus"></i> Add Field</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p>
            <input title="Save Changes" type='submit' class='button-primary button' name="wcc_gf_lawmatics_integration_submit" value="<?php esc_html_e( 'Save Changes', 'wcc-gf-lawmatics' ); ?>" />
            <input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce( 'wcc_gf_lawmatics_integration' )) ?>">
        </p>
        <input type="hidden" name="edit_id" value="<?php echo (isset($info['id']) ? esc_attr($info['id']) : "") ?>">
    </form>
    <div class="field_info">
        <?php 
        $fields = isset($connector_fields) ? $connector_fields : array();
        require_once plugin_dir_path( __FILE__ ) . 'integration_field_info.php';
        ?>
    </div>
    <?php require "message.php" ?>
</div>
<div class="hidden_connector_setting" style="display:none;">
    <div class="wcc-card wcc-mt-15 connector_setting_block">
        <table class="widefat wcc_data_table wcc-mt-0 wcc-table form-table">
            <thead>
                <tr>
                    <th class="connector_field_name" ></th>
                    <th class="wcc-text-right"><i title="Remove" class="dashicons dashicons-trash removeMaping"></i></th>
                </tr>
                <tr>
                    <th class="connector_field_name_info" colspan="2"></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Field Type</td>
                    <td>
                        <select name="maping[{{connector_field}}][type]" class="wcc-input maping_field_type">
                            <option value="standard"><?php esc_html_e( 'Standard Field', 'wcc-gf-lawmatics' ); ?></option>
                            <option value="custom"><?php esc_html_e( 'Custom Value', 'wcc-gf-lawmatics' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Value</td>
                    <td>
                        <div class="wcc_custom_value" style="display: none;">
                            <textarea class="wcc-input" name="maping[{{connector_field}}][custom_value]"></textarea>
                        </div>
                        <select name="maping[{{connector_field}}][value]" class="wcc-input form_value_selector">
                            <option value=''>Select Field</option>
                            <?php if(isset($form_fields)){ ?>
                                <?php foreach ($form_fields as $key => $value) { ?>
                                    <option value="<?php echo esc_attr($key) ?>"><?php echo esc_attr($value) ?></option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>