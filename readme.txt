# Twilio WordPress Phone OTP Plugin

A WordPress plugin that provides a phone OTP (One Time Password) authentication system. This plugin supports two modes: displaying the OTP form inside a modal (popup) or directly on the page. It integrates with Twilio to send and verify OTPs.

## Features

- OTP form displayed in a modal or directly on the page.
- International phone number input with country flags.
- OTP input fields with automatic focus navigation.
- 10-second timer for resending OTP.
- Admin settings to configure Twilio API credentials.

## Installation

1. **Upload Plugin:**
   - Download the plugin zip file.
   - Go to your WordPress admin dashboard.
   - Navigate to **Plugins** > **Add New**.
   - Click **Upload Plugin** and choose the zip file.
   - Click **Install Now** and then **Activate**.

2. **Configure Twilio Settings:**
   - Go to **Settings** > **Phone OTP Settings** in the WordPress admin dashboard.
   - Enter your Twilio SID, Auth Token, and Phone Number.
   - Click **Save Changes**.

## Shortcodes

- **Modal OTP Form:**
  - Use the shortcode `[phone_otp_modal]` to display the OTP form inside a modal popup.
  
- **Direct OTP Form:**
  - Use the shortcode `[phone_otp_direct]` to display the OTP form directly on the page.

## Usage

1. **Add the Shortcodes to Your Content:**
   - Edit the page or post where you want to display the OTP form.
   - Add `[phone_otp_modal]` or `[phone_otp_direct]` to the content.

2. **Test the OTP Functionality:**
   - Visit the page where you added the shortcode.
   - Follow the prompts to enter your phone number and receive an OTP.
   - Verify the OTP by entering it into the provided fields.

## Admin Settings

1. **Navigate to Settings:**
   - Go to **Settings** > **Phone OTP Settings** in the WordPress admin dashboard.

2. **Enter Twilio Credentials:**
   - Fill in your Twilio SID, Auth Token, and Phone Number.
   - Save the changes.

## Development and Customization

- **JavaScript**: The plugin uses jQuery and the `intl-tel-input` library for phone number input. The script for handling OTP sending, verification, and timer is located in `phone-otp.js`.
- **CSS**: The styling for the OTP form and modal is handled in `phone-otp.css`.
- **PHP**: The plugin's core functionality and settings page are defined in `phone-otp-plugin.php`.

## Support

For support and bug reports, please contact [Your Support Email] or open an issue on the plugin's repository.

## License

This plugin is licensed under the [GPLv2 License](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).

---

*This README file is a template. Please modify it according to your specific needs and details.* 
