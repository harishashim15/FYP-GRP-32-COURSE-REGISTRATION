(function ($) {
    "use strict";

    var input = $('.validate-input .input100');

    $('.validate-form').on('submit', function (e) {
        e.preventDefault();

        var check = true;

        for (var i = 0; i < input.length; i++) {
            if (validate(input[i]) == false) {
                showValidate(input[i]);
                check = false;
            }
        }

        if (!check) return;

        // GET LOGIN_CRED + PASSWORD
        var loginCred = $('input[name="id"]').val().trim();
        var password = $('input[name="pass"]').val();

        $.ajax({
            url: 'login.php',
            type: 'POST',
            data: {
                username: loginCred,
                password: password
            },
            success: function (response) {
                response = response.trim();

                if (response === 'admin') {
                    window.location.href = 'admin/admin_dashboard.php';
                } else if (response === 'advisor') {
                    window.location.href = 'advisor_dashboard.html';
                } else if (response === 'student') {
                    window.location.href = 'student_dashboard.html';
                } else if (response === 'invalid') {
                    showErrorMessage('Invalid login credentials or password. Please try again.');
                } else {
                    showErrorMessage('Unexpected response: [' + response + ']');
                }
            },
            error: function () {
                showErrorMessage('Could not connect to the server. Please try again later.');
            }
        });
    });

    $('.validate-form .input100').each(function () {
        $(this).focus(function () {
            hideValidate(this);
            hideErrorMessage();
        });
    });

    // SIMPLE VALIDATION
    function validate(input) {
        if ($(input).val().trim() == '') {
            return false;
        }
        return true;  // FIXED: was missing return true
    }

    function showValidate(input) {
        var thisAlert = $(input).parent();
        $(thisAlert).addClass('alert-validate');
    }

    function hideValidate(input) {
        var thisAlert = $(input).parent();
        $(thisAlert).removeClass('alert-validate');
    }

    function showErrorMessage(msg) {
        var $err = $('#login-error-msg');
        if ($err.length === 0) {
            $err = $('<p id="login-error-msg" style="color:#e74c3c; text-align:center; margin-top:12px; font-size:14px;"></p>');
            $('.container-login100-form-btn').after($err);
        }
        $err.text(msg).show();
    }

    function hideErrorMessage() {
        $('#login-error-msg').hide();
    }

})(jQuery);