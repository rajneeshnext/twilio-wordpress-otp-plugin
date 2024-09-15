jQuery(document).ready(function ($) {
    var timerInterval;

    // Initialize intl-tel-input
    function initializeIntlTelInput(selector) {
        var input = document.querySelector(selector);
        return window.intlTelInput(input, {
            initialCountry: "auto",
            geoIpLookup: function(callback) {
                $.get('https://ipinfo.io', function() {}, "jsonp").always(function(resp) {
                    var countryCode = (resp && resp.country) ? resp.country : "us";
                    callback(countryCode);
                });
            },
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
        });
    }

    var itiModal = initializeIntlTelInput("#phone");
    var itiDirect = initializeIntlTelInput("#phoneDirect");

    $('#openModalBtn').on('click', function () {
        $('#otpModal').show();
    });

    $('.close').on('click', function () {
        $('#otpModal').hide();
    });

    // Phone form submission for modal
    $('#phoneForm').on('submit', function (e) {
        e.preventDefault();
        var phoneNumber = itiModal.getNumber();
        $.ajax({
            url: phoneOtpAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'send_otp',
                phone_number: phoneNumber,
                security: phoneOtpAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('OTP has been sent to: ' + phoneNumber);
                    $('#phoneForm').hide();
                    $('#otpForm').show();
                    $('#otp1').focus();
                    startTimer('#timer', '#resendOTP');
                } else {
                    alert('Failed to send OTP. Please try again.');
                }
            }
        });
    });

    // Phone form submission for direct display
    $('#phoneFormDirect').on('submit', function (e) {
        e.preventDefault();
        var phoneNumber = itiDirect.getNumber();
        $.ajax({
            url: phoneOtpAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'send_otp',
                phone_number: phoneNumber,
                security: phoneOtpAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('OTP has been sent to: ' + phoneNumber);
                    $('#phoneFormDirect').hide();
                    $('#otpFormDirect').show();
                    $('#otp1Direct').focus();
                    startTimer('#timerDirect', '#resendOTPD');
                } else {
                    alert('Failed to send OTP. Please try again.');
                }
            }
        });
    });

    // OTP verification
    function verifyOtp(formId, otpFields) {
        $(formId).on('submit', function (e) {
            e.preventDefault();
            var otp = otpFields.map(function(field) { return $(field).val(); }).join('');
            $.ajax({
                url: phoneOtpAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'verify_otp',
                    otp: otp,
                    security: phoneOtpAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('OTP verified successfully. You are logged in!');
                    } else {
                        alert('Invalid OTP. Please try again.');
                    }
                }
            });
        });
    }

    verifyOtp('#otpForm', ['#otp1', '#otp2', '#otp3', '#otp4', '#otp5', '#otp6']);
    verifyOtp('#otpFormDirect', ['#otp1Direct', '#otp2Direct', '#otp3Direct', '#otp4Direct', '#otp5Direct', '#otp6Direct']);

    // Handle Resend OTP
    function handleResendOtp(buttonId, timerId) {
        $(buttonId).on('click', function () {
            $.ajax({
                url: phoneOtpAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'resend_otp',
                    security: phoneOtpAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('OTP has been resent.');
                        startTimer(timerId, buttonId);  // Restart timer
                    } else {
                        alert('Failed to resend OTP. Please try again.');
                    }
                }
            });
        });
    }

    handleResendOtp('#resendOTP', '#timer');
    handleResendOtp('#resendOTPD', '#timerDirect');

    // Timer function
    function startTimer(timerId, buttonId) {
        var timer = 10;
        $(timerId).text(timer);
        $(buttonId).prop('disabled', true);

        clearInterval(timerInterval);
        timerInterval = setInterval(function () {
            timer--;
            $(timerId).text(timer);
            if (timer <= 0) {
                clearInterval(timerInterval);
                $(buttonId).prop('disabled', false);
                $(timerId).text('0');
            }
        }, 1000);
    }

    // Move focus between OTP fields
    function handleOtpFocus(fields) {
        fields.forEach(function(field, index) {
            $(field).on('input', function () {
                var currentInput = $(this);
                if (currentInput.val().length === 1 && index < fields.length - 1) {
                    $(fields[index + 1]).focus();  // Move to next input field
                }
            });
        });
    }

    handleOtpFocus(['#otp1', '#otp2', '#otp3', '#otp4', '#otp5', '#otp6']);
    handleOtpFocus(['#otp1Direct', '#otp2Direct', '#otp3Direct', '#otp4Direct', '#otp5Direct', '#otp6Direct']);
});
