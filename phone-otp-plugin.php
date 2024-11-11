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
// Autoload dependencies (for Twilio SDK)
if ( file_exists( __DIR__ . '/Twilio/autoload.php' ) ) {
    require_once __DIR__ . '/Twilio/autoload.php';
}
use Twilio\Rest\Client;
include_once(ABSPATH . 'wp-includes/pluggable.php');
// Enqueue necessary scripts and styles
function phone_otp_enqueue_scripts() {
    wp_enqueue_style('intl-tel-input-css', 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css');
    wp_enqueue_script('intl-tel-input-js', 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js', array('jquery'), null, true);
    wp_enqueue_script('phone-otp-js', plugin_dir_url(__FILE__) . 'phone-otp.js', array('jquery'), null, true);
    wp_enqueue_style('phone-otp-css', plugin_dir_url(__FILE__) . 'phone-otp.css');
}
if( !is_user_logged_in()) {
	add_action('wp_enqueue_scripts', 'phone_otp_enqueue_scripts');
}

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
    register_setting('phone_otp_settings_group', 'phone_otp_redirect_page');
	// Register new settings
    register_setting('phone_otp_settings_group', 'phone_otp_button_label');
    register_setting('phone_otp_settings_group', 'phone_otp_button_color');
    register_setting('phone_otp_settings_group', 'phone_otp_sms_text');
    register_setting('phone_otp_settings_group', 'phone_otp_success_text');
    register_setting('phone_otp_settings_group', 'phone_otp_form_title'); // New field for form title
    register_setting('phone_otp_settings_group', 'phone_otp_form_title2'); // New field for form title
    register_setting('phone_otp_settings_group', 'phone_otp_submit_button_label');
    register_setting('phone_otp_settings_group', 'phone_otp_submit_button_label2');
    register_setting('phone_otp_settings_group', 'phone_first_time_login_sms_text');
    register_setting('phone_otp_settings_group', 'phone_otp_popup_bg_image');
    register_setting('phone_otp_settings_group', 'phone_otp_custom_css');

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
	
    
    // Add Modal/Button related settings
    add_settings_field('phone_otp_button_label', 'Login/SignUp Button Label', 'phone_otp_text_field_callback', 'phone-otp-settings', 'phone_otp_settings_section', array('label_for' => 'phone_otp_button_label'));
    add_settings_field('phone_otp_button_color', 'Login/SignUp Button Color', 'phone_otp_color_picker_callback', 'phone-otp-settings', 'phone_otp_settings_section', array('label_for' => 'phone_otp_button_color','label_description' => 'Button BG,Color'));

    // Form and Submit button settings
    add_settings_field('phone_otp_form_title', 'Form Title (Step1)', 'phone_otp_text_field_callback', 'phone-otp-settings', 'phone_otp_settings_section', array('label_for' => 'phone_otp_form_title'));
    add_settings_field('phone_otp_submit_button_label', 'Submit Button Label', 'phone_otp_text_field_callback', 'phone-otp-settings', 'phone_otp_settings_section', array('label_for' => 'phone_otp_submit_button_label'));

    // Form and Submit button settings
    add_settings_field('phone_otp_form_title2', 'Form Title (Step2)', 'phone_otp_text_field_callback', 'phone-otp-settings', 'phone_otp_settings_section', array('label_for' => 'phone_otp_form_title2'));
    add_settings_field('phone_otp_submit_button_label2', 'Submit Button Label (Step2)', 'phone_otp_text_field_callback', 'phone-otp-settings', 'phone_otp_settings_section', array('label_for' => 'phone_otp_submit_button_label2'));

    // OTP-related settings
    add_settings_field('phone_otp_sms_text', 'OTP SMS Text', 'phone_otp_textarea_callback', 'phone-otp-settings', 'phone_otp_settings_section', array('label_for' => 'phone_otp_sms_text'));
    add_settings_field('phone_otp_success_text', 'OTP Success Message', 'phone_otp_text_field_callback', 'phone-otp-settings', 'phone_otp_settings_section', array('label_for' => 'phone_otp_success_text'));
    add_settings_field('phone_first_time_login_sms_text', 'First time login SMS Text', 'phone_otp_textarea_callback', 'phone-otp-settings', 'phone_otp_settings_section', array('label_for' => 'phone_first_time_login_sms_text'));

    add_settings_field('phone_otp_redirect_page', 'Redirect after login', 'phone_otp_page_dropdown', 'phone-otp-settings', 'phone_otp_settings_section', array('label_for' => 'phone_otp_redirect_page'));

    add_settings_field(
        'phone_otp_custom_css',
        'Custom CSS',
        'phone_otp_custom_css_callback',
        'phone-otp-settings',
        'phone_otp_settings_section',
        array('label_for' => 'phone_otp_custom_css')
    );
    add_settings_field(
        'phone_otp_popup_bg_image',
        'Popup Background Image',
        'phone_otp_popup_bg_image_callback',
        'phone-otp-settings',
        'phone_otp_settings_section',
        array('label_for' => 'phone_otp_popup_bg_image')
    );
}
add_action('admin_init', 'phone_otp_register_settings');

function phone_otp_custom_css_callback($args) {
    $option = get_option($args['label_for']);
    ?>
    <textarea id="<?php echo esc_attr($args['label_for']); ?>" name="<?php echo esc_attr($args['label_for']); ?>" rows="5" cols="50" class="large-text"><?php echo esc_textarea($option); ?></textarea>
    <p class="description">Add your custom CSS for the OTP popup or any other elements here.</p>
    <?php
}
function phone_otp_enqueue_custom_css() {
    $custom_css = get_option('phone_otp_custom_css');
    if ($custom_css) {
        // Output the custom CSS wrapped in a style tag
        echo '<style type="text/css">' . wp_kses($custom_css, array( 'style' => array() )) . '</style>';
    }
}
add_action('wp_head', 'phone_otp_enqueue_custom_css');
function phone_otp_popup_bg_image_callback($args) {
    $option = get_option($args['label_for']);
    ?>
    <input type="text" id="<?php echo esc_attr($args['label_for']); ?>" name="<?php echo esc_attr($args['label_for']); ?>" value="<?php echo esc_attr($option); ?>" class="regular-text">
    <button class="upload_image_button button">Upload Image</button>
    <div class="image-preview" style="margin-top: 10px;">
        <?php if ($option) : ?>
            <img src="<?php echo esc_url($option); ?>" style="max-width: 100%; height: auto;" />
        <?php endif; ?>
    </div>
    <script>
        jQuery(document).ready(function($){
            var mediaUploader;
            $('.upload_image_button').click(function(e) {
                e.preventDefault();
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                mediaUploader = wp.media.frames.file_frame = wp.media({
                    title: 'Select a Background Image',
                    button: {
                        text: 'Choose Image'
                    },
                    multiple: false
                });
                mediaUploader.on('select', function() {
                    attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#<?php echo esc_attr($args['label_for']); ?>').val(attachment.url);
                    $('.image-preview').html('<img src="'+attachment.url+'" style="max-width: 100%; height: auto;" />');
                });
                mediaUploader.open();
            });
        });
    </script>
    <?php
}

// Text field callback
function phone_otp_text_field_callback($args) {
    $option = get_option($args['label_for']);
    ?>
    <input type="text" id="<?php echo esc_attr($args['label_for']); ?>" name="<?php echo esc_attr($args['label_for']); ?>" value="<?php echo esc_attr($option); ?>" class="regular-text">
    <?php
}

// Textarea callback for SMS text field
function phone_otp_textarea_callback($args) {
    $option = get_option($args['label_for']);
    ?>
    <textarea id="<?php echo esc_attr($args['label_for']); ?>" name="<?php echo esc_attr($args['label_for']); ?>" rows="5" cols="50"><?php echo esc_attr($option); ?></textarea>
    <?php
}

// Color picker callback for button color
function phone_otp_color_picker_callback($args) {
    $option = get_option($args['label_for']);
    ?>
    <input type="text" id="<?php echo esc_attr($args['label_for']); ?>" name="<?php echo esc_attr($args['label_for']); ?>" value="<?php echo esc_attr($option); ?>" class="color-picker" data-alpha="true">&nbsp;<?php echo esc_attr($args['label_description']); ?>
    <script>
        jQuery(document).ready(function($){
            $('.color-picker').wpColorPicker();
        });
    </script>
    <?php
}

// Dropdown for redirect page
function phone_otp_page_dropdown() {
    $pages = get_pages();
    $selected_page = get_option('phone_otp_redirect_page');
    ?>
    <select name="phone_otp_redirect_page" id="phone_otp_redirect_page">
        <option value="">Select a page...</option>
        <?php foreach ($pages as $page) { ?>
            <option value="<?php echo $page->ID; ?>" <?php selected($selected_page, $page->ID); ?>><?php echo $page->post_title; ?></option>
        <?php } ?>
    </select>
    <?php
}

// Shortcode for phone OTP form with modal
function phone_otp_modal_shortcode() {
    ob_start(); // Start output buffering to return the HTML content
    if ( is_user_logged_in() ) {
	   return;
    }
    if(get_option('phone_otp_form_title')!=""){
		$title = get_option('phone_otp_form_title');
	}else{
		$title = "Login with OTP ";
	}
    if(get_option('phone_otp_submit_button_label2')!=""){
        $title2 = get_option('phone_otp_submit_button_label2');
    }else{
        $title2 = "Send OTP";
    }
    if(get_option('phone_otp_submit_button_label')!=""){
		$button_label = get_option('phone_otp_submit_button_label');
	}else{
		$button_label = "Send OTP";
	}
    if(get_option('phone_otp_submit_button_label2')!=""){
        $button_label2 = get_option('phone_otp_submit_button_label2');
    }else{
        $button_label2 = "Send OTP";
    }
    if(get_option('phone_otp_button_label')!=""){
        $modal_button_label = get_option('phone_otp_button_label');
    }else{
        $modal_button_label = "Login/SignUp";
    }
    if(get_option('phone_otp_button_color')!=""){
        $phone_otp_button_color = get_option('phone_otp_button_color');
        $phone_otp_button_color = explode(',', $phone_otp_button_color);
        if(is_array($phone_otp_button_color)){
            $phone_otp_button_color = "style='background-color: ".$phone_otp_button_color[0].";color: ".$phone_otp_button_color[1]."'";        
        }else{
            $phone_otp_button_color = "style='background-color: #f00;color: white;'";    
        }
    }else{
        $phone_otp_button_color = "style='background-color: #f00;color: white;'";
    }
    $bg_image = get_option('phone_otp_popup_bg_image');
    if ($bg_image) {
        $bg_style = 'style="background-image: url(' . esc_url($bg_image) . ');"';
    } else {
        $bg_style = ''; // Fallback if no image is set
    }
    ?>
    <button class="openModalBtn" id="openModalBtn" <?php echo $phone_otp_button_color;?>><?php echo $modal_button_label;?></button>
    <!-- Modal (Popup) -->
    <div id="otpModal" class="modal">
        <div class="modal-content" <?php echo $bg_style;?>>
            <span class="close">&times;</span>
            <!-- Phone Input Form -->
            <form id="phoneForm">
                <?php echo $title;?>                
                <input type="tel" id="phone" placeholder="Enter Your Mobile Number" required>
                <button type="submit"><?php echo $button_label;?></button>
            </form>
			
			<?php do_action( 'twilio_otp_plugin_insert_html');?>

            <!-- OTP Input Form -->
            <form id="otpForm" style="display: none;">
                <?php echo $title2;?>
                <div class="otp-container">
                    <input type="text" class="otp-input" maxlength="1" id="otp1" required>
                    <input type="text" class="otp-input" maxlength="1" id="otp2" required>
                    <input type="text" class="otp-input" maxlength="1" id="otp3" required>
                    <input type="text" class="otp-input" maxlength="1" id="otp4" required>
                    <input type="text" class="otp-input" maxlength="1" id="otp5" required>
                    <input type="text" class="otp-input" maxlength="1" id="otp6" required>
                </div>
                <button type="submit"><?php echo $button_label;?></button>
                <div class="timer">Didn't received it? Resend Passcode in <span id="timer">10</span> seconds</div>
                <button id="resendOTP" disabled>Resend Passcode</button>
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
    if ( is_user_logged_in() ) {
	   return;
    }
	if(get_option('phone_otp_form_title')!=""){
        $title = get_option('phone_otp_form_title');
    }else{
        $title = "Login with OTP ";
    }
    if(get_option('phone_otp_submit_button_label2')!=""){
        $title2 = get_option('phone_otp_submit_button_label2');
    }else{
        $title2 = "Send OTP";
    }
    if(get_option('phone_otp_submit_button_label')!=""){
        $button_label = get_option('phone_otp_submit_button_label');
    }else{
        $button_label = "Send OTP";
    }
    if(get_option('phone_otp_submit_button_label2')!=""){
        $button_label2 = get_option('phone_otp_submit_button_label2');
    }else{
        $button_label2 = "Send OTP";
    }
    ?>
    <div class="phone-otp-container">
        <?php echo $title;?>
        <!-- Phone Input Form -->
        <form id="phoneFormDirect">
            <input type="tel" id="phoneDirect" placeholder="Enter Your Mobile Number" required>
            <button type="submit"><?php echo $button_label;?></button>
        </form>

        <!-- OTP Input Form -->
        <form id="otpFormDirect" style="display: none;">
            <?php echo $title2;?>
            <div class="otp-container">
                <input type="text" class="otp-input" maxlength="1" id="otp1Direct" required>
                <input type="text" class="otp-input" maxlength="1" id="otp2Direct" required>
                <input type="text" class="otp-input" maxlength="1" id="otp3Direct" required>
                <input type="text" class="otp-input" maxlength="1" id="otp4Direct" required>
                <input type="text" class="otp-input" maxlength="1" id="otp5Direct" required>
                <input type="text" class="otp-input" maxlength="1" id="otp6Direct" required>
            </div>
            <button type="submit"><?php echo $button_label2;?></button>

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
    //check_ajax_referer('phone_otp_nonce', 'security');
	session_start();
    $phone_number = sanitize_text_field($_POST['phone_number']);
	if(get_option('phone_otp_sms_text')!=""){
		$otp_sms = get_option('phone_otp_sms_text');
	}else{
		$otp_sms = "Your OTP is ";
	}    
	if(get_option('phone_otp_success_text')!=""){
		$otp_sms_success = get_option('phone_otp_success_text');
	}else{
		$otp_sms_success = 'OTP sent successfully';
	}
	
	$otp = rand(100000, 999999); // Generate OTP
    $otp_sms = $otp_sms." ".$otp;
    //echo $otp_sms;exit();
	//$_SESSION['phone_otp'] = $otp;
	//wp_send_json_success(array('otp' => $otp));
    // Use Twilio API to send OTP here
    // Example Twilio code (replace with your own implementation)
    $twilio = new \Twilio\Rest\Client(get_option('twilio_sid'), get_option('twilio_auth_token'));
    try {
        $twilio->messages->create($phone_number, array(
            'from' => get_option('twilio_phone_number'),
            'body' => $otp_sms
        ));
        
        $_SESSION['phone_otp'] = $otp;
        $_SESSION['phone_number'] = $phone_number;
        wp_send_json_success("$otp_sms_success");
    } catch (Exception $e) {
        wp_send_json_error(array('message' => $e->getMessage()));
    }
}
add_action('wp_ajax_send_otp', 'phone_otp_send_otp');
add_action('wp_ajax_nopriv_send_otp', 'phone_otp_send_otp');

// Handle AJAX requests for verifying OTP
function phone_otp_verify_otp() {
    //check_ajax_referer('phone_otp_nonce', 'security');
    session_start();
    $entered_otp = sanitize_text_field($_POST['otp']);
    $session_otp = $_SESSION['phone_otp'];
    $phone_number = $_SESSION['phone_number'];

    if ($entered_otp == $session_otp) {
        // Check if user exists with this phone number
        $user = get_users(array(
            'meta_key' => 'phone_number',
            'meta_value' => $phone_number,
            'number' => 1
        ));
        $success_sms = "";
        if ($user) {
            // Log in the user
            wp_set_auth_cookie($user[0]->ID, true);
        } else {
            // Register the user if not found
            $username = 'user_' . substr(md5($phone_number), 0, 5);
            $password = wp_generate_password();
            $user_id = wp_create_user($username, $password);
            update_user_meta($user_id, 'phone_number', $phone_number);

            // Log in the newly created user
            wp_set_auth_cookie($user_id, true);
            if(get_option('phone_first_time_login_sms_text')!=""){
                $success_sms = get_option('phone_first_time_login_sms_text');
            }
        }

        // Redirect to specified page
        $redirect_page_id = get_option('phone_otp_redirect_page');
        $redirect_url = $redirect_page_id ? get_permalink($redirect_page_id) : home_url();

        if($success_sms!=""){
            $twilio = new \Twilio\Rest\Client(get_option('twilio_sid'), get_option('twilio_auth_token'));
            try {
                $twilio->messages->create($phone_number, array(
                    'from' => get_option('twilio_phone_number'),
                    'body' => $success_sms
                ));
            } catch (Exception $e) {
                wp_send_json_error(array('message' => $e->getMessage()));
            } 
        }
        wp_send_json_success(array('redirect_url' => $redirect_url));
    } else {
        wp_send_json_error('Invalid OTP');
    }
}
add_action('wp_ajax_verify_otp', 'phone_otp_verify_otp');
add_action('wp_ajax_nopriv_verify_otp', 'phone_otp_verify_otp');

// Handle AJAX requests for resending OTP
function phone_otp_resend_otp() {
    check_ajax_referer('phone_otp_nonce', 'security');

    // Resend OTP logic here
    phone_otp_send_otp();
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
