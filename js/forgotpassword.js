(function ($) {
    "use strict";

    // ─── STEP MANAGEMENT ─────────────────────────────────────────────────────────

    var STEPS = {
        EMAIL:   'step-email',
        SUCCESS: 'step-success'
    };

    function showStep(step) {
        $('.reset-step').hide();
        $('#' + step).show();
    }

    // ─── INIT ─────────────────────────────────────────────────────────────────────

    $(document).ready(function () {
        showStep(STEPS.EMAIL);
    });

    // ─── FORM SUBMIT ──────────────────────────────────────────────────────────────

    $('#reset-password-form').on('submit', function (e) {
        e.preventDefault();

        var $emailInput = $('input[name="reset_email"]');
        var email       = $emailInput.val().trim();

        // --- CLIENT-SIDE VALIDATION ---
        hideErrorMessage();
        hideFieldError($emailInput);

        if (email === '') {
            showFieldError($emailInput, 'Please enter your email address.');
            return;
        }

        if (!isValidEmail(email)) {
            showFieldError($emailInput, 'Please enter a valid email address.');
            return;
        }

        // --- SERVER-SIDE CHECK ---
        var $btn = $('#reset-submit-btn');
        setLoading($btn, true);

        $.ajax({
            url:  'resetPassword.php',   // matches your actual PHP filename
            type: 'POST',
            data: {
                action: 'check_email',
                email:  email
            },
            success: function (response) {
                response = response.trim();

                switch (response) {
                    case 'found':
                        showStep(STEPS.SUCCESS);
                        break;

                    case 'not_found':
                        showFieldError(
                            $emailInput,
                            'No account found with that email address.'
                        );
                        break;

                    case 'invalid_email':
                        showFieldError(
                            $emailInput,
                            'The email address you entered is not valid.'
                        );
                        break;

                    case 'rate_limited':
                        showErrorMessage(
                            'Too many reset attempts. Please wait a few minutes and try again.'
                        );
                        break;

                    case 'mail_error':
                        showErrorMessage(
                            'Account found but email could not be sent. Please try again.'
                        );
                        break;

                    default:
                        showErrorMessage(
                            'Unexpected server response. Please try again later.'
                        );
                        break;
                }
            },
            error: function () {
                showErrorMessage(
                    'Could not connect to the server. Please check your connection and try again.'
                );
            },
            complete: function () {
                setLoading($btn, false);
            }
        });
    });

    // Clear errors on focus
    $('input[name="reset_email"]').on('focus', function () {
        hideFieldError($(this));
        hideErrorMessage();
    });

    // ─── HELPERS ──────────────────────────────────────────────────────────────────

    function isValidEmail(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
        return re.test(email);
    }

    function showFieldError($input, msg) {
        $input.addClass('input-error');
        var $wrap = $input.closest('.wrap-input100');
        var $err  = $wrap.find('.field-error-msg');
        if ($err.length === 0) {
            $err = $('<span class="field-error-msg"></span>');
            $wrap.append($err);
        }
        $err.text(msg).show();
    }

    function hideFieldError($input) {
        $input.removeClass('input-error');
        $input.closest('.wrap-input100').find('.field-error-msg').hide();
    }

    function showErrorMessage(msg) {
        $('#reset-error-msg').text(msg).show();
    }

    function hideErrorMessage() {
        $('#reset-error-msg').hide();
    }

    function setLoading($btn, isLoading) {
        if (isLoading) {
            $btn.prop('disabled', true).data('original-text', $btn.text()).text('Sending…');
        } else {
            $btn.prop('disabled', false).text($btn.data('original-text') || 'Send Reset Link');
        }
    }

})(jQuery);