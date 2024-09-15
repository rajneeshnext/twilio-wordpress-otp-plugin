<?php
/*
Plugin Name: Twilio WordPress Phone OTP Plugin
Description: Used by thousands, A plugin to display a phone OTP form with options for a modal button or direct page display, using Twilio API.
Version: 1.0
Author: Rajneesh Saini
Plugin URI: https://www.boldertechnologies.net/twilio-wp-otp
Requires PHP: 5.6.20
Author URI: https://www.upwork.com/freelancers/rajneeshkumarsaini
*/

// Enqueue necessary scripts and styles
function phone_otp_enqueue_scripts() {
    wp_enqueue_style('intl-tel-input-css', 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css');
    wp_enqueue_script('intl-tel-input-js', 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js', array('jquery'), null, true);
    wp_enqueue_script('phone-otp-js', plugin_dir_url(__FILE__) . 'phone-otp.js', array('jquery'), null, true);
    wp_enqueue_style('phone-otp-css', plugin_dir_url(__FILE__) . 'phone-otp.css');
}
add_action('wp_enqueue_scripts', 'phone_otp_enqueue_scripts');

// Add admin menu
function phone_otp_admin_menu() {
    add_options_page(
        'Phone OTP Settings',
        'Phone OTP Settings',
        'manage_options',
        'phone-otp-settings',
        'phone_otp_settings_page'
    );
}
add_action('admin_menu', 'phone_otp_admin_menu');
function phone_otp_plugin_action_links($links) {
    $settings_link = '<a href="options-general.php?page=phone-otp-settings">' . __('Settings') . '</a>';
    array_unshift($links, $settings_link); // Add the settings link
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'phone_otp_plugin_action_links');
// Settings page content
function phone_otp_settings_page() {
    ?>
   <div class="wrap">
        <h1>Phone OTP Plugin Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('phone_otp_settings_group');
            do_settings_sections('phone-otp-settings');
            submit_button();
            ?>
        </form>

        <h2>Usage and Shortcode Info</h2>
        <p>To use the Phone OTP Plugin, you can add the following shortcodes to your pages or posts:</p>
        <ul>
            <li><strong>For the modal (popup):</strong> Use the shortcode <code>[phone_otp_modal]</code>.</li>
            <li><strong>For direct display:</strong> Use the shortcode <code>[phone_otp_direct]</code>.</li>
        </ul>
        <p>Ensure you have configured your Twilio API settings under this settings page.</p>
    </div>
    <?php
}

// Register settings
function phone_otp_register_settings() {
    register_setting('phone_otp_settings_group', 'twilio_sid');
    register_setting('phone_otp_settings_group', 'twilio_auth_token');
    register_setting('phone_otp_settings_group', 'twilio_phone_number');

    add_settings_section(
        'phone_otp_settings_section',
        'Twilio API Settings',
        null,
        'phone-otp-settings'
    );

    add_settings_field(
        'twilio_sid',
        'Twilio SID',
        'phone_otp_text_field_callback',
        'phone-otp-settings',
        'phone_otp_settings_section',
        array('label_for' => 'twilio_sid')
    );

    add_settings_field(
        'twilio_auth_token',
        'Twilio Auth Token',
        'phone_otp_text_field_callback',
        'phone-otp-settings',
        'phone_otp_settings_section',
        array('label_for' => 'twilio_auth_token')
    );

    add_settings_field(
        'twilio_phone_number',
        'Twilio Phone Number',
        'phone_otp_text_field_callback',
        'phone-otp-settings',
        'phone_otp_settings_section',
        array('label_for' => 'twilio_phone_number')
    );
}
add_action('admin_init', 'phone_otp_register_settings');

// Text field callback
function phone_otp_text_field_callback($args) {
    $option = get_option($args['label_for']);
    ?>
    <input type="text" id="<?php echo esc_attr($args['label_for']); ?>" name="<?php echo esc_attr($args['label_for']); ?>" value="<?php echo esc_attr($option); ?>" class="regular-text">
    <?php
}

// Shortcode for phone OTP form with modal
function phone_otp_modal_shortcode() {
    ob_start(); // Start output buffering to return the HTML content
    ?>
    <button id="openModalBtn">Open Phone OTP Form</button>

    <!-- Modal (Popup) -->
    <div id="otpModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Login with Phone and OTP</h2>

            <!-- Phone Input Form -->
            <form id="phoneForm">
                <input type="tel" id="phone" placeholder="Enter phone number" required>
                <button type="submit">Send OTP</button>
            </form>

            <!-- OTP Input Form -->
            <form id="otpForm" style="display: none;">
                <div class="otp-container">
                    <input type="text" class="otp-input" maxlength="1" id="otp1" required>
                    <input type="text" class="otp-input" maxlength="1" id="otp2" required>
                    <input type="text" class="otp-input" maxlength="1" id="otp3" required>
                    <input type="text" class="otp-input" maxlength="1" id="otp4" required>
                    <input type="text" class="otp-input" maxlength="1" id="otp5" required>
                    <input type="text" class="otp-input" maxlength="1" id="otp6" required>
                </div>
                <button type="submit">Verify OTP</button>

                <div class="timer">Resend OTP in <span id="timer">10</span> seconds</div>
                <button id="resendOTP" disabled>Resend OTP</button>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean(); // Return the buffered content
}
add_shortcode('phone_otp_modal', 'phone_otp_modal_shortcode');

// Shortcode for phone OTP form without modal
function phone_otp_direct_shortcode() {
    ob_start(); // Start output buffering to return the HTML content
    ?>
    <div class="phone-otp-container">
        <h2>Login with Phone and OTP</h2>

        <!-- Phone Input Form -->
        <form id="phoneFormDirect">
            <input type="tel" id="phoneDirect" placeholder="Enter phone number" required>
            <button type="submit">Send OTP</button>
        </form>

        <!-- OTP Input Form -->
        <form id="otpFormDirect" style="display: none;">
            <div class="otp-container">
                <input type="text" class="otp-input" maxlength="1" id="otp1Direct" required>
                <input type="text" class="otp-input" maxlength="1" id="otp2Direct" required>
                <input type="text" class="otp-input" maxlength="1" id="otp3Direct" required>
                <input type="text" class="otp-input" maxlength="1" id="otp4Direct" required>
                <input type="text" class="otp-input" maxlength="1" id="otp5Direct" required>
                <input type="text" class="otp-input" maxlength="1" id="otp6Direct" required>
            </div>
            <button type="submit">Verify OTP</button>

            <div class="timer">Resend OTP in <span id="timerDirect">10</span> seconds</div>
            <button id="resendOTPD" disabled>Resend OTP</button>
        </form>
    </div>
    <?php
    return ob_get_clean(); // Return the buffered content
}
add_shortcode('phone_otp_direct', 'phone_otp_direct_shortcode');

// Function to get Twilio API credentials
function phone_otp_get_twilio_credentials() {
    return array(
        'sid' => get_option('twilio_sid'),
        'auth_token' => get_option('twilio_auth_token'),
        'phone_number' => get_option('twilio_phone_number')
    );
}

// Display plugin activation notice
function phone_otp_activation_notice() {
    if (is_admin()) {
        $screen = get_current_screen();
        if ($screen->id === 'plugins') {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('Phone OTP Plugin activated. Please configure the settings <a href="' . admin_url('options-general.php?page=phone-otp-settings') . '">here</a>.', 'phone-otp-plugin'); ?></p>
            </div>
            <?php
        }
    }
}
add_action('admin_notices', 'phone_otp_activation_notice');

// Handle AJAX requests for sending OTP
function phone_otp_send_otp() {
    check_ajax_referer('phone_otp_nonce', 'security');

    $phone_number = sanitize_text_field($_POST['phone_number']);

    // Use Twilio API to send OTP here
    // Example Twilio code (replace with your own implementation)
    $twilio = new \Twilio\Rest\Client(get_option('twilio_sid'), get_option('twilio_auth_token'));
    try {
        $twilio->messages->create($phone_number, array(
            'from' => get_option('twilio_phone_number'),
            'body' => 'Your OTP is 123456'
        ));
        wp_send_json_success();
    } catch (Exception $e) {
        wp_send_json_error(array('message' => $e->getMessage()));
    }
}
add_action('wp_ajax_send_otp', 'phone_otp_send_otp');
add_action('wp_ajax_nopriv_send_otp', 'phone_otp_send_otp');

// Handle AJAX requests for verifying OTP
function phone_otp_verify_otp() {
    check_ajax_referer('phone_otp_nonce', 'security');

    $otp = sanitize_text_field($_POST['otp']);

    // Verify OTP here
    if ($otp === '123456') {
        wp_send_json_success();
    } else {
        wp_send_json_error(array('message' => 'Invalid OTP.'));
    }
}
add_action('wp_ajax_verify_otp', 'phone_otp_verify_otp');
add_action('wp_ajax_nopriv_verify_otp', 'phone_otp_verify_otp');

// Handle AJAX requests for resending OTP
function phone_otp_resend_otp() {
    check_ajax_referer('phone_otp_nonce', 'security');

    // Resend OTP logic here
    wp_send_json_success();
}
add_action('wp_ajax_resend_otp', 'phone_otp_resend_otp');
add_action('wp_ajax_nopriv_resend_otp', 'phone_otp_resend_otp');

// Localize script for AJAX
function phone_otp_localize_script() {
    wp_localize_script('phone-otp-js', 'phoneOtpAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('phone_otp_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'phone_otp_localize_script');
