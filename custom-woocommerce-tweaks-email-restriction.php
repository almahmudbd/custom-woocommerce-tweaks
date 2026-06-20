<?php
/**
 * Restrict user registration to specific email domains.
 *
 * Covers WordPress backend/frontend registration, WooCommerce frontend
 * registration, and the WP Admin "Add New User" screen. Gated by the
 * `restrict_email_domains_setting` option.
 */

/**
 * Whether the given email address uses an allowed domain.
 */
function custom_woocommerce_tweaks_is_allowed_email_domain($email)
{
    $allowed_domains = array('gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com');
    $email_domain = substr(strrchr((string) $email, '@'), 1);

    if (!$email_domain) {
        return true; // Let core handle empty/invalid emails.
    }

    return in_array(strtolower($email_domain), $allowed_domains, true);
}

/**
 * Restrict user registration to specific email domains (WP Backend/Frontend).
 */
add_filter('registration_errors', 'custom_restrict_email_domains_wp', 10, 3);
function custom_restrict_email_domains_wp($errors, $sanitized_user_login, $user_email)
{
    if (get_option('restrict_email_domains_setting', 'no') === 'yes') {
        if (!custom_woocommerce_tweaks_is_allowed_email_domain($user_email)) {
            $errors->add('email_domain_error', 'Sorry, registration is not allowed with this email.');
        }
    }
    return $errors;
}

/**
 * Restrict user registration to specific email domains (WooCommerce Frontend).
 */
add_action('woocommerce_register_post', 'custom_restrict_email_domains_wc', 10, 3);
function custom_restrict_email_domains_wc($username, $email, $validation_errors)
{
    if (get_option('restrict_email_domains_setting', 'no') === 'yes') {
        if (!custom_woocommerce_tweaks_is_allowed_email_domain($email)) {
            $validation_errors->add('email_domain_error', 'Sorry, registration is not allowed with this email.');
        }
    }
}

/**
 * Restrict user registration to specific email domains (WP Admin / user-new.php).
 */
add_action('user_profile_update_errors', 'custom_restrict_email_domains_admin', 10, 3);
function custom_restrict_email_domains_admin($errors, $update, $user)
{
    // Apply only when creating a new user, not updating
    if ($update) {
        return;
    }

    if (get_option('restrict_email_domains_setting', 'no') === 'yes') {
        if (!custom_woocommerce_tweaks_is_allowed_email_domain($user->user_email)) {
            $errors->add('email_domain_error', 'Sorry, registration is not allowed with this email.');
        }
    }
}
