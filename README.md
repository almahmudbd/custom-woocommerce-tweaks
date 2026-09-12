# Custom WooCommerce Tweaks
A simple Wordpress plugin with few custom WooCommerce tweaks, i have used before.
This plugin adds useful customizations to WooCommerce for better functionality and user experience.

## Current Features
All features are individually toggleable from **WooCommerce > Tweaks Settings**.

1. **Disable COD for Courier & Post Office**  
   Hides the Cash on Delivery (COD) payment option when the Courier or Post Office shipping method is selected. Shipping method IDs are set in `custom-woocommerce-tweaks-core.php`.

2. **Disable Password Change Email**  
   Disables the email notification sent when a user changes their account password.

3. **Clickable WhatsApp Link for Phone Numbers**  
   Adds a clickable WhatsApp link below the billing phone number on the admin order page (Bangladeshi numbers only, `01XXXXXXXXX` / `8801XXXXXXXXX`).

4. **Remove UpdraftPlus from Admin Bar**  
   Hides the UpdraftPlus menu item from the WordPress admin bar.

5. **Phone Number Validation (11 Digits)**  
   Blocks checkout unless the billing phone contains exactly 11 digits.

6. **Bank Payment Discount (0.5%)**  
   Applies a 0.5% cart discount when the Bank Payment gateway is selected, and refreshes the checkout totals on payment method change.

7. **Disable Cart Page**  
   Redirects the standalone cart page to checkout.

8. **Show Full Cart on Checkout**  
   Displays the full editable cart above the checkout form.

9. **Restrict Registration Email Domains**  
   Only allows registration with gmail, yahoo, hotmail or outlook addresses. Applies to WordPress registration, WooCommerce registration, and the admin "Add New User" screen.

10. **Checkout Fields Customizer (Billing, Shipping & Order Notes)**  
    Full control over WooCommerce checkout form fields from a dedicated tab under **WooCommerce > Tweaks Settings**:
    - Enable or disable (show/hide) core billing, shipping, and order notes fields.
    - Toggle field required state (Default, Required, or Optional).
    - Customize field labels, placeholder texts, and helper notes/descriptions.
    - Smooth integration with WooCommerce dynamic address localization and validation.

11. **WhatsApp Click-to-Chat Buttons**  
    Adds a WhatsApp button to single product pages (below Add to Cart) and to the checkout page (above the order review). Both are toggleable from the General Tweaks tab. Phone number, button labels, and message templates are configured at the top of `custom-woocommerce-tweaks-whatsapp.php`.

> Note: payment gateway and shipping method IDs are hardcoded to this site's setup — edit them in `custom-woocommerce-tweaks-core.php` before using elsewhere.

## Installation
1. Download the plugin.
2. Upload the plugin to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.

## Settings
1. Go to **WooCommerce > Tweaks Settings** in the WordPress admin panel.
2. Customize the options across two organized tabs:
   - **General Tweaks:** Toggle individual general tweaks (COD rules, discounts, phone validation, WhatsApp click-to-chat, etc.).
   - **Checkout Fields Customizer:** Master switch and organized tables to customize billing, shipping, and order notes fields.

> Phone number, button labels, and message templates for the WhatsApp buttons live at the top of `custom-woocommerce-tweaks-whatsapp.php` (defined as constants).

## Support
Fix it yourself please :) 
hehe.

## License
This plugin is licensed under the GPL-3.0+.
