<?php
/**
 * Checkout Fields Customizer Tweak
 * Allows enabling/disabling, editing labels, placeholders, notes (descriptions),
 * and required states for WooCommerce checkout billing, shipping, and order fields.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Returns list of standard core WooCommerce checkout fields.
 *
 * @return array
 */
function cwt_get_checkout_field_definitions() {
    return array(
        'billing' => array(
            'title'  => __( 'Billing Fields', 'custom-woocommerce-tweaks' ),
            'fields' => array(
                'billing_first_name' => array(
                    'label'            => __( 'First Name', 'woocommerce' ),
                    'default_required' => true,
                ),
                'billing_last_name'  => array(
                    'label'            => __( 'Last Name', 'woocommerce' ),
                    'default_required' => true,
                ),
                'billing_company'    => array(
                    'label'            => __( 'Company Name', 'woocommerce' ),
                    'default_required' => false,
                ),
                'billing_country'    => array(
                    'label'            => __( 'Country / Region', 'woocommerce' ),
                    'default_required' => true,
                ),
                'billing_address_1'  => array(
                    'label'            => __( 'Street Address (Line 1)', 'woocommerce' ),
                    'default_required' => true,
                ),
                'billing_address_2'  => array(
                    'label'            => __( 'Apartment, suite, unit (Line 2)', 'woocommerce' ),
                    'default_required' => false,
                ),
                'billing_city'       => array(
                    'label'            => __( 'Town / City', 'woocommerce' ),
                    'default_required' => true,
                ),
                'billing_state'      => array(
                    'label'            => __( 'State / District / County', 'woocommerce' ),
                    'default_required' => true,
                ),
                'billing_postcode'   => array(
                    'label'            => __( 'Postcode / ZIP', 'woocommerce' ),
                    'default_required' => true,
                ),
                'billing_phone'      => array(
                    'label'            => __( 'Phone', 'woocommerce' ),
                    'default_required' => true,
                ),
                'billing_email'      => array(
                    'label'            => __( 'Email Address', 'woocommerce' ),
                    'default_required' => true,
                ),
            ),
        ),
        'shipping' => array(
            'title'  => __( 'Shipping Fields', 'custom-woocommerce-tweaks' ),
            'fields' => array(
                'shipping_first_name' => array(
                    'label'            => __( 'First Name', 'woocommerce' ),
                    'default_required' => true,
                ),
                'shipping_last_name'  => array(
                    'label'            => __( 'Last Name', 'woocommerce' ),
                    'default_required' => true,
                ),
                'shipping_company'    => array(
                    'label'            => __( 'Company Name', 'woocommerce' ),
                    'default_required' => false,
                ),
                'shipping_country'    => array(
                    'label'            => __( 'Country / Region', 'woocommerce' ),
                    'default_required' => true,
                ),
                'shipping_address_1'  => array(
                    'label'            => __( 'Street Address (Line 1)', 'woocommerce' ),
                    'default_required' => true,
                ),
                'shipping_address_2'  => array(
                    'label'            => __( 'Apartment, suite, unit (Line 2)', 'woocommerce' ),
                    'default_required' => false,
                ),
                'shipping_city'       => array(
                    'label'            => __( 'Town / City', 'woocommerce' ),
                    'default_required' => true,
                ),
                'shipping_state'      => array(
                    'label'            => __( 'State / District / County', 'woocommerce' ),
                    'default_required' => true,
                ),
                'shipping_postcode'   => array(
                    'label'            => __( 'Postcode / ZIP', 'woocommerce' ),
                    'default_required' => true,
                ),
                'shipping_phone'      => array(
                    'label'            => __( 'Phone', 'woocommerce' ),
                    'default_required' => false,
                ),
            ),
        ),
        'order' => array(
            'title'  => __( 'Order Notes / Additional Fields', 'custom-woocommerce-tweaks' ),
            'fields' => array(
                'order_comments' => array(
                    'label'            => __( 'Order Notes', 'woocommerce' ),
                    'default_required' => false,
                ),
            ),
        ),
    );
}

/**
 * Get current saved settings for checkout fields.
 *
 * @return array
 */
function cwt_get_checkout_fields_settings() {
    $saved = get_option( 'cwt_checkout_fields_settings', array() );
    return is_array( $saved ) ? $saved : array();
}

/**
 * Check if checkout fields customizer is active.
 *
 * @return bool
 */
function cwt_is_customizer_active() {
    return get_option( 'cwt_enable_checkout_fields_customizer', 'yes' ) === 'yes';
}

/**
 * Shared helper: apply saved overrides (label, placeholder, description, required)
 * to a single field's args array. Returns modified args.
 *
 * @param array  $args  Field arguments (label, placeholder, required, etc.).
 * @param array  $conf  Saved config for this field from cwt_checkout_fields_settings.
 * @return array Modified field arguments.
 */
function cwt_apply_field_overrides( $args, $conf ) {
    if ( isset( $conf['label'] ) && trim( $conf['label'] ) !== '' ) {
        $args['label'] = sanitize_text_field( $conf['label'] );
    }
    if ( isset( $conf['placeholder'] ) && trim( $conf['placeholder'] ) !== '' ) {
        $args['placeholder'] = sanitize_text_field( $conf['placeholder'] );
    }
    if ( isset( $conf['description'] ) && trim( $conf['description'] ) !== '' ) {
        $args['description'] = sanitize_text_field( $conf['description'] );
    }
    if ( isset( $conf['required'] ) && $conf['required'] !== 'default' ) {
        $args['required'] = ( $conf['required'] === 'yes' );
    }
    return $args;
}

// ─── Hook 1: Main checkout fields override ─────────────────────────────────────
add_filter( 'woocommerce_checkout_fields', 'cwt_custom_override_checkout_fields', 9999 );
function cwt_custom_override_checkout_fields( $fields ) {
    if ( ! cwt_is_customizer_active() ) {
        return $fields;
    }

    $definitions = cwt_get_checkout_field_definitions();
    $settings    = cwt_get_checkout_fields_settings();

    foreach ( $definitions as $section => $section_info ) {
        if ( ! isset( $fields[ $section ] ) || ! is_array( $fields[ $section ] ) ) {
            continue;
        }
        foreach ( $section_info['fields'] as $field_key => $default_info ) {
            $conf = isset( $settings[ $field_key ] ) ? $settings[ $field_key ] : array();

            // Disabled → remove field entirely
            if ( isset( $conf['enabled'] ) && ! (int) $conf['enabled'] ) {
                unset( $fields[ $section ][ $field_key ] );
                continue;
            }
            if ( ! isset( $fields[ $section ][ $field_key ] ) ) {
                continue;
            }

            $fields[ $section ][ $field_key ] = cwt_apply_field_overrides(
                $fields[ $section ][ $field_key ],
                $conf
            );
        }
    }

    return $fields;
}

// ─── Hook 2: Default address fields (prevents country-change validation reset) ─
add_filter( 'woocommerce_default_address_fields', 'cwt_custom_override_default_address_fields', 9999 );
function cwt_custom_override_default_address_fields( $fields ) {
    if ( ! cwt_is_customizer_active() ) {
        return $fields;
    }

    $settings     = cwt_get_checkout_fields_settings();
    $address_keys = array( 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country' );

    foreach ( $address_keys as $key ) {
        $billing_key = 'billing_' . $key;
        if ( ! isset( $settings[ $billing_key ] ) || ! isset( $fields[ $key ] ) ) {
            continue;
        }
        $conf = $settings[ $billing_key ];

        if ( isset( $conf['enabled'] ) && ! (int) $conf['enabled'] ) {
            $fields[ $key ]['required'] = false;
        }
        $fields[ $key ] = cwt_apply_field_overrides( $fields[ $key ], $conf );
    }

    return $fields;
}

// ─── Hook 3: Country locale data (prevents JS from overwriting labels) ──────────
add_filter( 'woocommerce_get_country_locale', 'cwt_override_country_locale', 9999 );
function cwt_override_country_locale( $locales ) {
    if ( ! cwt_is_customizer_active() ) {
        return $locales;
    }

    $settings         = cwt_get_checkout_fields_settings();
    $locale_field_map = array(
        'billing_first_name' => 'first_name', 'billing_last_name' => 'last_name',
        'billing_company'    => 'company',    'billing_address_1' => 'address_1',
        'billing_address_2'  => 'address_2',  'billing_city'      => 'city',
        'billing_state'      => 'state',      'billing_postcode'  => 'postcode',
    );

    $overrides = array();
    foreach ( $locale_field_map as $billing_key => $locale_key ) {
        if ( ! isset( $settings[ $billing_key ] ) ) {
            continue;
        }
        $conf  = $settings[ $billing_key ];
        $entry = array();

        if ( isset( $conf['label'] ) && trim( $conf['label'] ) !== '' ) {
            $entry['label'] = sanitize_text_field( $conf['label'] );
        }
        if ( isset( $conf['placeholder'] ) && trim( $conf['placeholder'] ) !== '' ) {
            $entry['placeholder'] = sanitize_text_field( $conf['placeholder'] );
        }
        if ( isset( $conf['required'] ) && $conf['required'] !== 'default' ) {
            $entry['required'] = ( $conf['required'] === 'yes' );
        }
        if ( isset( $conf['enabled'] ) && ! (int) $conf['enabled'] ) {
            $entry['hidden']   = true;
            $entry['required'] = false;
        }
        if ( ! empty( $entry ) ) {
            $overrides[ $locale_key ] = $entry;
        }
    }

    if ( empty( $overrides ) ) {
        return $locales;
    }

    // Inject into every country's locale
    foreach ( $locales as $cc => &$country_locale ) {
        foreach ( $overrides as $lk => $od ) {
            $country_locale[ $lk ] = array_merge(
                isset( $country_locale[ $lk ] ) ? $country_locale[ $lk ] : array(),
                $od
            );
        }
    }

    return $locales;
}

// ─── Hook 4: Order notes toggle ─────────────────────────────────────────────────
add_filter( 'woocommerce_enable_order_notes_field', 'cwt_conditionally_disable_order_notes_section', 9999 );
function cwt_conditionally_disable_order_notes_section( $enabled ) {
    if ( ! cwt_is_customizer_active() ) {
        return $enabled;
    }
    $settings = cwt_get_checkout_fields_settings();
    if ( isset( $settings['order_comments']['enabled'] ) && ! (int) $settings['order_comments']['enabled'] ) {
        return false;
    }
    return $enabled;
}

// ─── Hook 5: Frontend JS (re-applies labels after AJAX for phone, email, etc.) ─
add_action( 'wp_footer', 'cwt_checkout_fields_frontend_js', 9999 );
function cwt_checkout_fields_frontend_js() {
    if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_wc_endpoint_url() ) {
        return;
    }
    if ( ! cwt_is_customizer_active() ) {
        return;
    }

    $settings     = cwt_get_checkout_fields_settings();
    $js_overrides = array();

    foreach ( $settings as $field_key => $conf ) {
        $o = array();
        if ( isset( $conf['label'] ) && trim( $conf['label'] ) !== '' ) {
            $o['label'] = sanitize_text_field( $conf['label'] );
        }
        if ( isset( $conf['placeholder'] ) && trim( $conf['placeholder'] ) !== '' ) {
            $o['placeholder'] = sanitize_text_field( $conf['placeholder'] );
        }
        if ( ! empty( $o ) ) {
            $js_overrides[ $field_key ] = $o;
        }
    }

    if ( empty( $js_overrides ) ) {
        return;
    }
    ?>
    <script type="text/javascript">
    (function(){
        var O = <?php echo wp_json_encode( $js_overrides ); ?>;

        function cwtApply() {
            for (var k in O) {
                if (!O.hasOwnProperty(k)) continue;
                var f = document.getElementById(k + '_field');
                if (!f) continue;

                if (O[k].label) {
                    var l = f.querySelector('label');
                    if (l) {
                        var abbr = l.querySelector('abbr');
                        l.textContent = O[k].label;
                        if (abbr) {
                            l.appendChild(document.createTextNode(' '));
                            l.appendChild(abbr);
                        }
                    }
                }

                if (O[k].placeholder) {
                    var inp = f.querySelector('input, textarea');
                    if (inp && (inp.tagName === 'INPUT' || inp.tagName === 'TEXTAREA')) {
                        inp.setAttribute('placeholder', O[k].placeholder);
                    }
                }
            }
        }

        /* Deferred apply — runs after all synchronous handlers on the same event */
        function cwtDeferApply() {
            setTimeout(cwtApply, 50);
        }

        /* Initial apply on page load */
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', cwtDeferApply);
        } else {
            cwtDeferApply();
        }

        /* Re-apply after WooCommerce AJAX events (deferred to run AFTER WC's own handlers) */
        if (typeof jQuery !== 'undefined') {
            jQuery(document.body).on('updated_checkout', cwtDeferApply);
            jQuery(document.body).on('country_to_state_changed', cwtDeferApply);
        }

        /* MutationObserver: catch any DOM replacement WooCommerce does on the checkout form */
        var checkoutForm = document.querySelector('form.checkout, form.woocommerce-checkout');
        if (checkoutForm && typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function(mutations) {
                var dominated = false;
                for (var i = 0; i < mutations.length; i++) {
                    if (mutations[i].addedNodes.length > 0) {
                        dominated = true;
                        break;
                    }
                }
                if (dominated) {
                    cwtDeferApply();
                }
            });
            observer.observe(checkoutForm, { childList: true, subtree: true });
        }
    })();
    </script>
    <?php
}

