<?php
/**
 * Add a settings page under WooCommerce menu
 */
add_action('admin_menu', 'custom_woocommerce_tweaks_add_settings_page');
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
    // Save settings when the form is submitted.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        update_option('disable_cod_for_courier', isset($_POST['disable_cod_for_courier']) ? 'yes' : 'no');
        update_option('disable_password_email', isset($_POST['disable_password_email']) ? 'yes' : 'no');
        update_option('enable_clickable_phone', isset($_POST['enable_clickable_phone']) ? 'yes' : 'no');
        echo '<div class="updated"><p>Settings saved successfully!</p></div>';
    }

    // Retrieve current settings.
    $disable_cod_for_courier = get_option('disable_cod_for_courier', 'yes');
    $disable_password_email = get_option('disable_password_email', 'yes');
    $enable_clickable_phone = get_option('enable_clickable_phone', 'yes');
    ?>
    <div class="wrap">
        <h1>Custom WooCommerce Tweaks Settings</h1>
        <form method="post" action="">
            <table class="form-table">
                <!-- Option to disable COD for courier -->
                <tr valign="top">
                    <th scope="row">Disable COD for Courier</th>
                    <td>
                        <input type="checkbox" name="disable_cod_for_courier" <?php checked($disable_cod_for_courier, 'yes'); ?> />
                        <label for="disable_cod_for_courier">Hide COD when Courier shipping is selected</label>
                    </td>
                </tr>
                <!-- Option to disable password change email notifications -->
                <tr valign="top">
                    <th scope="row">Disable Password Change Email</th>
                    <td>
                        <input type="checkbox" name="disable_password_email" <?php checked($disable_password_email, 'yes'); ?> />
                        <label for="disable_password_email">Disable email notification for password changes</label>
                    </td>
                </tr>
                <!-- Option to enable clickable phone numbers for WhatsApp -->
                <tr valign="top">
                    <th scope="row">Enable Clickable Phone for WhatsApp</th>
                    <td>
                        <input type="checkbox" name="enable_clickable_phone" <?php checked($enable_clickable_phone, 'yes'); ?> />
                        <label for="enable_clickable_phone">Make phone numbers clickable for WhatsApp</label>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
