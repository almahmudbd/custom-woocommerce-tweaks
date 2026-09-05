<?php
/**
 * Add a settings page under WooCommerce menu
 */
add_action( 'admin_menu', 'custom_woocommerce_tweaks_add_settings_page' );
function custom_woocommerce_tweaks_add_settings_page() {
    add_submenu_page(
        'woocommerce', // Parent menu slug to add the submenu under WooCommerce.
        'Custom Tweaks Settings', // The page title shown on the settings page.
        'Tweaks Settings', // The title in the WooCommerce menu.
        'manage_options', // Required capability to access this page.
        'custom-woocommerce-tweaks-settings', // Unique slug for the settings page.
        'custom_woocommerce_tweaks_settings_page' // Callback function to render the page.
    );
}

/**
 * Render the settings page
 */
function custom_woocommerce_tweaks_settings_page() {
    $current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';

    // Save settings when the form is submitted.
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        // Verify intent/nonce if available or handle tab-specific saves
        if ( isset( $_POST['cwt_settings_tab'] ) && $_POST['cwt_settings_tab'] === 'checkout_fields' ) {
            // Save Checkout Fields Customizer settings
            $enable_customizer = isset( $_POST['cwt_enable_checkout_fields_customizer'] ) ? 'yes' : 'no';
            update_option( 'cwt_enable_checkout_fields_customizer', $enable_customizer );

            $posted_fields = isset( $_POST['cwt_fields'] ) && is_array( $_POST['cwt_fields'] ) ? $_POST['cwt_fields'] : array();
            $definitions   = function_exists( 'cwt_get_checkout_field_definitions' ) ? cwt_get_checkout_field_definitions() : array();
            $clean_settings = array();

            foreach ( $definitions as $sec_key => $sec_data ) {
                foreach ( $sec_data['fields'] as $field_key => $default_info ) {
                    $field_post = isset( $posted_fields[ $field_key ] ) ? $posted_fields[ $field_key ] : array();

                    $clean_settings[ $field_key ] = array(
                        'enabled'     => isset( $field_post['enabled'] ) ? 1 : 0,
                        'required'    => isset( $field_post['required'] ) && in_array( $field_post['required'], array( 'default', 'yes', 'no' ), true ) ? $field_post['required'] : 'default',
                        'label'       => isset( $field_post['label'] ) ? sanitize_text_field( wp_unslash( $field_post['label'] ) ) : '',
                        'placeholder' => isset( $field_post['placeholder'] ) ? sanitize_text_field( wp_unslash( $field_post['placeholder'] ) ) : '',
                        'description' => isset( $field_post['description'] ) ? sanitize_text_field( wp_unslash( $field_post['description'] ) ) : '',
                    );
                }
            }

            update_option( 'cwt_checkout_fields_settings', $clean_settings );
            echo '<div class="updated"><p>Checkout fields settings saved successfully!</p></div>';
        } else {
            // Save General Tweaks settings
            update_option( 'disable_cod_for_courier', isset( $_POST['disable_cod_for_courier'] ) ? 'yes' : 'no' );
            update_option( 'disable_password_email', isset( $_POST['disable_password_email'] ) ? 'yes' : 'no' );
            update_option( 'enable_clickable_phone', isset( $_POST['enable_clickable_phone'] ) ? 'yes' : 'no' );
            update_option( 'remove_updraft_admin_bar_setting', isset( $_POST['remove_updraft_admin_bar_setting'] ) ? 'yes' : 'no' );
            update_option( 'validate_mobile_number_setting', isset( $_POST['validate_mobile_number_setting'] ) ? 'yes' : 'no' );
            update_option( 'enable_bank_payment_discount', isset( $_POST['enable_bank_payment_discount'] ) ? 'yes' : 'no' );
            update_option( 'disable_cart_page', isset( $_POST['disable_cart_page'] ) ? 'yes' : 'no' );
            update_option( 'show_full_cart_on_checkout', isset( $_POST['show_full_cart_on_checkout'] ) ? 'yes' : 'no' );
            update_option( 'restrict_email_domains_setting', isset( $_POST['restrict_email_domains_setting'] ) ? 'yes' : 'no' );
            echo '<div class="updated"><p>Settings saved successfully!</p></div>';
        }
    }

    // Retrieve current general settings.
    $disable_cod_for_courier          = get_option( 'disable_cod_for_courier', 'yes' );
    $disable_password_email           = get_option( 'disable_password_email', 'yes' );
    $enable_clickable_phone           = get_option( 'enable_clickable_phone', 'yes' );
    $remove_updraft_admin_bar_setting = get_option( 'remove_updraft_admin_bar_setting', 'no' );
    $validate_mobile_number_setting   = get_option( 'validate_mobile_number_setting', 'no' );
    $enable_bank_payment_discount     = get_option( 'enable_bank_payment_discount', 'no' );
    $disable_cart_page                = get_option( 'disable_cart_page', 'no' );
    $show_full_cart_on_checkout       = get_option( 'show_full_cart_on_checkout', 'no' );
    $restrict_email_domains_setting   = get_option( 'restrict_email_domains_setting', 'no' );

    // Retrieve checkout fields settings.
    $enable_checkout_fields_customizer = get_option( 'cwt_enable_checkout_fields_customizer', 'no' );
    $checkout_field_definitions        = function_exists( 'cwt_get_checkout_field_definitions' ) ? cwt_get_checkout_field_definitions() : array();
    $checkout_field_settings           = function_exists( 'cwt_get_checkout_fields_settings' ) ? cwt_get_checkout_fields_settings() : array();
?>
    <div class="wrap cwt-settings-wrap">
        <h1>Custom WooCommerce Tweaks</h1>

        <nav class="nav-tab-wrapper" style="margin-bottom: 20px;">
            <a href="?page=custom-woocommerce-tweaks-settings&tab=general" class="nav-tab <?php echo $current_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'General Tweaks', 'custom-woocommerce-tweaks' ); ?>
            </a>
            <a href="?page=custom-woocommerce-tweaks-settings&tab=checkout_fields" class="nav-tab <?php echo $current_tab === 'checkout_fields' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Checkout Fields Customizer', 'custom-woocommerce-tweaks' ); ?>
            </a>
        </nav>

        <?php if ( $current_tab === 'general' ) : ?>
            <form method="post" action="?page=custom-woocommerce-tweaks-settings&tab=general">
                <input type="hidden" name="cwt_settings_tab" value="general" />
                <table class="form-table">
                    <!-- Option to disable COD for courier / post office -->
                    <tr valign="top">
                        <th scope="row">Disable COD for Courier &amp; Post Office</th>
                        <td>
                            <input type="checkbox" name="disable_cod_for_courier" <?php checked( $disable_cod_for_courier, 'yes' ); ?> />
                            <label for="disable_cod_for_courier">Hide COD when Courier or Post Office shipping is selected</label>
                        </td>
                    </tr>
                    <!-- Option to disable password change email notifications -->
                    <tr valign="top">
                        <th scope="row">Disable Password Change Email</th>
                        <td>
                            <input type="checkbox" name="disable_password_email" <?php checked( $disable_password_email, 'yes' ); ?> />
                            <label for="disable_password_email">Disable email notification for password changes</label>
                        </td>
                    </tr>
                    <!-- Option to enable clickable phone numbers for WhatsApp -->
                    <tr valign="top">
                        <th scope="row">Enable Clickable Phone for WhatsApp</th>
                        <td>
                            <input type="checkbox" name="enable_clickable_phone" <?php checked( $enable_clickable_phone, 'yes' ); ?> />
                            <label for="enable_clickable_phone">Make phone numbers clickable for WhatsApp</label>
                        </td>
                    </tr>
                    <!-- Option to remove Updraft from admin bar -->
                    <tr valign="top">
                        <th scope="row">Remove Updraft Admin Bar</th>
                        <td>
                            <input type="checkbox" name="remove_updraft_admin_bar_setting" <?php checked( $remove_updraft_admin_bar_setting, 'yes' ); ?> />
                            <label for="remove_updraft_admin_bar_setting">Hide UpdraftPlus menu from the admin bar</label>
                        </td>
                    </tr>
                    <!-- Option to validate mobile number length -->
                    <tr valign="top">
                        <th scope="row">Validate Mobile Number (11 Digits)</th>
                        <td>
                            <input type="checkbox" name="validate_mobile_number_setting" <?php checked( $validate_mobile_number_setting, 'yes' ); ?> />
                            <label for="validate_mobile_number_setting">Ensure billing phone is exactly 11 digits during checkout</label>
                        </td>
                    </tr>
                    <!-- Option to enable bank payment discount -->
                    <tr valign="top">
                        <th scope="row">Enable Bank Payment Discount (0.5%)</th>
                        <td>
                            <input type="checkbox" name="enable_bank_payment_discount" <?php checked( $enable_bank_payment_discount, 'yes' ); ?> />
                            <label for="enable_bank_payment_discount">Add 0.5% discount when Bank Payment selected</label>
                        </td>
                    </tr>
                    <!-- Option to disable the cart page -->
                    <tr valign="top">
                        <th scope="row">Disable Cart Page</th>
                        <td>
                            <input type="checkbox" name="disable_cart_page" <?php checked( $disable_cart_page, 'yes' ); ?> />
                            <label for="disable_cart_page">Redirect the cart page to checkout</label>
                        </td>
                    </tr>
                    <!-- Option to show the full cart on checkout -->
                    <tr valign="top">
                        <th scope="row">Show Full Cart on Checkout</th>
                        <td>
                            <input type="checkbox" name="show_full_cart_on_checkout" <?php checked( $show_full_cart_on_checkout, 'yes' ); ?> />
                            <label for="show_full_cart_on_checkout">Display the full editable cart above the checkout form</label>
                        </td>
                    </tr>
                    <!-- Option to restrict registration to specific email domains -->
                    <tr valign="top">
                        <th scope="row">Restrict Registration Email Domains</th>
                        <td>
                            <input type="checkbox" name="restrict_email_domains_setting" <?php checked( $restrict_email_domains_setting, 'yes' ); ?> />
                            <label for="restrict_email_domains_setting">Only allow registration with gmail, yahoo, hotmail or outlook addresses</label>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

        <?php elseif ( $current_tab === 'checkout_fields' ) : ?>
            <style>
                .cwt-fields-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 25px;
                    background: #fff;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                }
                .cwt-fields-table th, .cwt-fields-table td {
                    padding: 10px 12px;
                    border: 1px solid #ccd0d4;
                    vertical-align: middle;
                    text-align: left;
                }
                .cwt-fields-table th {
                    background-color: #f6f7f7;
                    font-weight: 600;
                    color: #1d2327;
                }
                .cwt-field-code {
                    display: inline-block;
                    background: #f0f0f1;
                    padding: 2px 6px;
                    border-radius: 4px;
                    font-size: 11px;
                    color: #50575e;
                    margin-top: 3px;
                }
                .cwt-row-disabled {
                    background-color: #fafafa;
                    opacity: 0.65;
                }
                .cwt-section-title {
                    font-size: 16px;
                    margin: 25px 0 10px;
                    padding-bottom: 6px;
                    border-bottom: 2px solid #2271b1;
                    color: #1d2327;
                }
                .cwt-fields-table input[type="text"] {
                    width: 100%;
                    box-sizing: border-box;
                }
            </style>

            <form method="post" action="?page=custom-woocommerce-tweaks-settings&tab=checkout_fields">
                <input type="hidden" name="cwt_settings_tab" value="checkout_fields" />

                <div style="background: #fff; padding: 15px 20px; border-left: 4px solid #2271b1; box-shadow: 0 1px 2px rgba(0,0,0,0.05); margin-bottom: 20px;">
                    <label style="font-weight: 600; font-size: 14px; cursor: pointer;">
                        <input type="checkbox" name="cwt_enable_checkout_fields_customizer" value="yes" <?php checked( $enable_checkout_fields_customizer, 'yes' ); ?> />
                        <?php esc_html_e( 'Enable Checkout Fields Customizer', 'custom-woocommerce-tweaks' ); ?>
                    </label>
                    <p class="description" style="margin-top: 5px;">
                        <?php esc_html_e( 'Turn this tweak on to customize labels, placeholders, notes (descriptions), and enable or disable fields in the checkout form.', 'custom-woocommerce-tweaks' ); ?>
                    </p>
                </div>

                <?php foreach ( $checkout_field_definitions as $section_key => $section_data ) : ?>
                    <h3 class="cwt-section-title"><?php echo esc_html( $section_data['title'] ); ?></h3>
                    <table class="cwt-fields-table">
                        <thead>
                            <tr>
                                <th style="width: 20%;"><?php esc_html_e( 'Field', 'custom-woocommerce-tweaks' ); ?></th>
                                <th style="width: 8%; text-align: center;"><?php esc_html_e( 'Enabled', 'custom-woocommerce-tweaks' ); ?></th>
                                <th style="width: 12%;"><?php esc_html_e( 'Required', 'custom-woocommerce-tweaks' ); ?></th>
                                <th style="width: 20%;"><?php esc_html_e( 'Custom Label', 'custom-woocommerce-tweaks' ); ?></th>
                                <th style="width: 20%;"><?php esc_html_e( 'Custom Placeholder', 'custom-woocommerce-tweaks' ); ?></th>
                                <th style="width: 20%;"><?php esc_html_e( 'Custom Note / Description', 'custom-woocommerce-tweaks' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $section_data['fields'] as $field_key => $default_info ) :
                                $field_conf = isset( $checkout_field_settings[ $field_key ] ) ? $checkout_field_settings[ $field_key ] : array();
                                $is_enabled  = isset( $field_conf['enabled'] ) ? (int) $field_conf['enabled'] : 1;
                                $required    = isset( $field_conf['required'] ) ? $field_conf['required'] : 'default';
                                $label       = isset( $field_conf['label'] ) ? $field_conf['label'] : '';
                                $placeholder = isset( $field_conf['placeholder'] ) ? $field_conf['placeholder'] : '';
                                $description = isset( $field_conf['description'] ) ? $field_conf['description'] : '';
                            ?>
                                <tr class="<?php echo ! $is_enabled ? 'cwt-row-disabled' : ''; ?>">
                                    <td>
                                        <strong><?php echo esc_html( $default_info['label'] ); ?></strong>
                                        <br/>
                                        <span class="cwt-field-code"><?php echo esc_html( $field_key ); ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="checkbox"
                                               name="cwt_fields[<?php echo esc_attr( $field_key ); ?>][enabled]"
                                               value="1"
                                               class="cwt-field-toggle"
                                               <?php checked( $is_enabled, 1 ); ?> />
                                    </td>
                                    <td>
                                        <select name="cwt_fields[<?php echo esc_attr( $field_key ); ?>][required]" style="width: 100%;">
                                            <option value="default" <?php selected( $required, 'default' ); ?>>
                                                <?php echo esc_html( sprintf( __( 'Default (%s)', 'custom-woocommerce-tweaks' ), ! empty( $default_info['default_required'] ) ? __( 'Required', 'custom-woocommerce-tweaks' ) : __( 'Optional', 'custom-woocommerce-tweaks' ) ) ); ?>
                                            </option>
                                            <option value="yes" <?php selected( $required, 'yes' ); ?>><?php esc_html_e( 'Required', 'custom-woocommerce-tweaks' ); ?></option>
                                            <option value="no" <?php selected( $required, 'no' ); ?>><?php esc_html_e( 'Optional', 'custom-woocommerce-tweaks' ); ?></option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text"
                                               name="cwt_fields[<?php echo esc_attr( $field_key ); ?>][label]"
                                               value="<?php echo esc_attr( $label ); ?>"
                                               placeholder="<?php echo esc_attr( $default_info['label'] ); ?>" />
                                    </td>
                                    <td>
                                        <input type="text"
                                               name="cwt_fields[<?php echo esc_attr( $field_key ); ?>][placeholder]"
                                               value="<?php echo esc_attr( $placeholder ); ?>"
                                               placeholder="<?php esc_attr_e( 'Placeholder text...', 'custom-woocommerce-tweaks' ); ?>" />
                                    </td>
                                    <td>
                                        <input type="text"
                                               name="cwt_fields[<?php echo esc_attr( $field_key ); ?>][description]"
                                               value="<?php echo esc_attr( $description ); ?>"
                                               placeholder="<?php esc_attr_e( 'Help note / description...', 'custom-woocommerce-tweaks' ); ?>" />
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endforeach; ?>

                <?php submit_button( __( 'Save Checkout Fields Settings', 'custom-woocommerce-tweaks' ) ); ?>
            </form>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var toggles = document.querySelectorAll('.cwt-field-toggle');
                    toggles.forEach(function(toggle) {
                        toggle.addEventListener('change', function() {
                            var tr = this.closest('tr');
                            if (this.checked) {
                                tr.classList.remove('cwt-row-disabled');
                            } else {
                                tr.classList.add('cwt-row-disabled');
                            }
                        });
                    });
                });
            </script>
        <?php endif; ?>
    </div>
    <?php
}
