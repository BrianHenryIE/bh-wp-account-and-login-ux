
(function( $ ) {
    'use strict';

    $( document ).ready(function() {

        // If the user is logged in, we don't care.
        // TODO: Use auth cookie.
        if (typeof bh_wp_account_and_login_ux !== 'undefined' && bh_wp_account_and_login_ux.hasOwnProperty("logged_in_user")) {
            return;
        }

        $('body').on('blur change', '#billing_email', function (e) {

            // TODO: Loading indicator.
            // TODO: It fires even if the value hasn't changed since the last time it fired.

            // if it's a valid email (taken from checkout.js)
            /* https://stackoverflow.com/questions/2855865/jquery-validate-e-mail-address-regex */
            var pattern = new RegExp(/^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[0-9a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i); // eslint-disable-line max-len

            if (pattern.test($('#billing_email').val())) {

                // Fire the ajax for update_order_review
                // (which will check for a user account).
                $(document.body).trigger('update_checkout');
            }

        });

    });


    $( document ).ready(function() {

        $( document.body ).bind( 'updated_checkout', function( data ) {

            $('#login_button_clicked').remove();
            $('#password_reset_button_clicked').remove();

            $('#inline_login_password').focus();

            // When ('#inline_login_password').is focused, enter means log in.
            $('#inline_login_password').on("keypress", function(e) {
                /* ENTER PRESSED*/
                if (e.keyCode == 13) {

                    $('#place_order').prepend('<input type="hidden" name="login_button_clicked" id="login_button_clicked" value="clicked" />');

                    $(document.body).trigger('update_checkout');

                    return false;
                }
            });


            // TODO: check we're not adding these repeatedly.

            $('#inline_login_button').on('click', function (e) {

                // TODO: validate not empty (and maybe WP minimum complexity).

                $('#place_order').prepend('<input type="hidden" name="login_button_clicked" id="login_button_clicked" value="clicked" />');

                $(document.body).trigger('update_checkout');

            });

            $('#inline_password_reset_button').on('click', function (e) {

                $('#place_order').prepend('<input type="hidden" name="password_reset_button_clicked" id="password_reset_button_clicked" value="clicked" />');

                $(document.body).trigger('update_checkout');

            });

        });

    });

})( jQuery );
