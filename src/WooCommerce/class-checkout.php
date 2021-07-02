<?php
/**
 * Moves the customer "billing_email" field to tbe beginning of the checkout, then prompts the
 * user to log in if an account exists for that email address. Adds a password reset email button
 * which acts with a single click using the billing_email.
 *
 * @link       http://BrianHenry.ie
 * @since      1.0.0
 *
 * @package    BH_WP_Account_And_Login_UX
 * @subpackage BH_WP_Account_And_Login_UX/woocommerce
 */

namespace BrianHenryIE\WP_Account_And_Login_UX\WooCommerce;

use BrianHenryIE\WP_Account_And_Login_UX\API\Settings_Interface;
use WC_Checkout;
use WC_Shortcode_My_Account;

/**
 * Class Checkout
 *
 * @package BrianHenryIE\WP_Account_And_Login_UX\WooCommerce
 */
class Checkout {

	/** @var Settings_Interface Plugin settings. */
	protected $settings;

	/**
	 * Checkout constructor.
	 *
	 * Makes settings available to the class.
	 *
	 * @param Settings_Interface $settings
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Print some JSON to indicate if the user is already logged in.
	 *
	 * We'll use this to avoid making pointless AJAX calls.
	 *
	 * TODO: use auth cookie.
	 *
	 * @hooked wp_footer
	 */
	public function add_is_user_logged_in_json(): void {

		if ( ! is_checkout() ) {
			return;
		}

		global $current_user;

		if ( ! empty( $current_user ) && is_object( $current_user ) && isset( $current_user->user_email ) ) {
			echo "\n\n<script id=\"bh-wp-account-and-login-ux\">\n";
			echo 'bh_wp_account_and_login_ux=' . wp_json_encode( array( 'logged_in_user' => $current_user->user_email ) );
			echo "\n</script>\n\n";

		}
	}

	/**
	 * Register the JavaScript files used for WooCommerce.
	 *
	 * This JS fires the `update_checkout` trigger when an email address is entered.
	 * Handles the Login and Send Password Reset buttons.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts(): void {

		if ( 'production' === wp_get_environment_type() ) {
			$version = $this->settings->get_plugin_version();
		} else {
			$time    = time();
			$version = "{$time}";
		}

		wp_enqueue_script( 'bh-wp-account-and-login-ux-woocommerce-checkout', plugin_dir_url( __FILE__ ) . 'js/checkout.js', array( 'jquery' ), $version, false );

	}

	/**
	 * Hide the native login prompt/form which appears above the checkout.
	 *
	 * This is just the "Allow customers to log into an existing account during checkout" option.
	 *
	 * TODO: only return 'no' on the actual checkout, not the settings page.
	 *
	 * @see /wp-admin/admin.php?page=wc-settings&tab=account
	 *
	 * @hooked pre_option_woocommerce_enable_checkout_login_reminder
	 *
	 * @see templates/checkout/form-login.php
	 * @see get_option()
	 *
	 * @param mixed  $_pre_option The value to return instead of the option value. This differs
	 *                           from `$default`, which is used as the fallback value in the event
	 *                           the option doesn't exist elsewhere in get_option().
	 *                           Default false (to skip past the short-circuit).
	 * @param string $_option     Option name.
	 * @param mixed  $_default    The fallback value to return if the option does not exist.
	 *                           Default false.
	 *
	 * @return string
	 */
	public function woocommerce_disable_checkout_login_reminder( $_pre_option, $_option, $_default ) {
		return 'no';
	}

	/**
	 * Since users may already have an account, move the email address input field so it is the first input,
	 * then we'll check does the user already have an account, and if so display the login form.
	 *
	 * Change the billing_email priority value.
	 *
	 * @see WC_Checkout::get_checkout_fields()
	 * @see https://rudrastyh.com/woocommerce/reorder-checkout-fields.html
	 * @see woocommerce_form_field()
	 *
	 * @hooked woocommerce_checkout_fields
	 *
	 * @param array $checkout_fields Array of checkout fields which will later be rendered with `woocommerce_form_field()`.
	 *
	 * @return array
	 */
	public function move_email_input_first( array $checkout_fields ): array {

		$checkout_fields['billing']['billing_email']['priority'] = 4;

		return $checkout_fields;
	}

	/**
	 * Parse the $_POST 'post_data' string into the checkout object.
	 *
	 * Without this, the checkout was re-rendering during `woocommerce_update_order_review_fragments` using
	 * the saved data rather than the posted data.
	 *
	 * @see WC_Ajax::update_order_review()
	 * @see assets/js/frontend/checkout.js
	 *
	 * @hooked woocommerce_checkout_update_order_review
	 *
	 * @param string $posted_data `posted_data` key of array posted by checkout.js.
	 */
	public function parse_post_on_update_order_review( string $posted_data ): void {

		$post_array = array();
		parse_str( $posted_data, $post_array );

		// TODO: does $post_array have any empty strings that need to be filtered/deleted?
		$clean = function( $post_element ) {
			return wp_unslash( $post_element );
		};

		// Nonce was already checked in WC_Ajax::update_order_review().
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$post = array_map( $clean, $_POST );

		$_POST = array_merge( $post, $post_array );

		$checkout = WC()->checkout();

		$checkout->get_posted_data();

	}


	/**
	 * If the user is not logged in and has entered the email address of an existing account,
	 * add a password field and login buttons to the billing input form (.woocommerce-billing-fields).
	 *
	 * `.woocommerce-billing-fields` is not re-rendered by default.
	 *
	 * @see WC_Ajax::update_order_review()
	 *
	 * @hooked woocommerce_update_order_review_fragments
	 *
	 * @param array $fragments Associative array of DOM selectors => HTML to be replaced.
	 *
	 * @return array
	 */
	public function rerender_billing_fields_fragment( array $fragments ): array {

		$checkout = WC()->checkout();

		ob_start();

		wc_get_template( 'checkout/form-billing.php', array( 'checkout' => $checkout ) );

		$woocommerce_billing_fields = ob_get_clean();

		$fragments['.woocommerce-billing-fields'] = $woocommerce_billing_fields;

		return $fragments;

	}

	/**
	 * Register an inline login form below the email field.
	 *
	 * Check the user is not logged in and that the email address matches an account we can log in to.
	 *
	 * The field will later be rendered by woocommerce_form_field().
	 *
	 * @see woocommerce_form_field()
	 *
	 * @hooked woocommerce_checkout_fields
	 *
	 * @param array $checkout_fields Array of checkout fields which will later be rendered with `woocommerce_form_field()`.
	 *
	 * @return array
	 */
	public function add_password_field_login_button_to_billing( array $checkout_fields ): array {

		if ( is_user_logged_in() ) {
			return $checkout_fields;
		}

		$checkout = WC()->checkout();

		$billing_email = $checkout->get_value( 'billing_email' );

		if ( ! is_email( $billing_email ) ) {
			return $checkout_fields;
		}

		// TODO: the option should/should the option exist to always display it and act also as a registration form?
		// (for people who think they already have an account?) / instances where force-account isn't active?
		if ( false === get_user_by( 'email', $billing_email ) ) {
			return $checkout_fields;
		}

		$checkout_inline_login_field = array(
			'type'     => 'checkout_inline_login_field',
			'priority' => 5,
		);

		$checkout_fields['billing']['inline_login'] = $checkout_inline_login_field;

		return $checkout_fields;
	}

	/**
	 * Custom field renderer for `checkout_inline_login_field`, password + login button on checkout.
	 *
	 * @hooked woocommerce_form_field_checkout_inline_login_field
	 *
	 * @see woocommerce_form_field()
	 *
	 * @param string $field HTML to output.
	 * @param string $key
	 * @param mixed  $args
	 * @param string $value
	 *
	 * @return string HTML to output.
	 */
	public function woocommerce_form_field_checkout_inline_login_field( $field, $key, $args, $value ) {

		if ( is_user_logged_in() ) {
			return '';
		}

		$field  = '<p class="form-row form-row-first" id="inline_login_password_field" data-priority="5">';
		$field .= '<label for="inline_login_password" class="">Please log in to your account:</label>';
		$field .= '<span class="woocommerce-input-wrapper">';
		$field .= '<input type="password" placeholder="password" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="inline_login_password" id="inline_login_password" />';
		$field .= '</span>';
		$field .= '</p>';

		$field .= '<p style="margin-top:23px" class="form-row form-row-last" id="inline_login_buttons_field" data-priority="6">';
		$field .= '<button id="inline_login_button" type="button" style="margin: 0 10px 0 0;" class="button alt">Log in</button>';
		$field .= '<button id="inline_password_reset_button" type="button">Reset Password</button>';
		$field .= '</p>';

		return $field;
	}

	/**
	 * Log the user in!
	 *
	 * @see parse_post_on_update_order_review()
	 *
	 * @hooked woocommerce_checkout_update_order_review
	 *
	 * @param string $_posted_data The `posted_data` key of the $_POST array send by checkout.js, as parsed earlier.
	 */
	public function inline_log_user_in( $_posted_data ): void {

		if ( is_user_logged_in() ) {
			return;
		}

		$checkout = WC_Checkout::instance();

		if ( empty( $checkout->get_value( 'login_button_clicked' ) ) ) {
			return;
		}

		$email = $checkout->get_value( 'billing_email' );

		if ( empty( $email ) ) {
			return;
		}

		$password = $checkout->get_value( 'inline_login_password' );

		if ( empty( $password ) ) {
			$this->inline_login_response = array(
				'message'  => '<strong>Password field empty</strong>.',
				'severity' => 'info',
			);
			return;
		}

		if ( empty( $email ) ) {

			$this->inline_login_response = array(
				'message'  => '<strong>Not a valid email address</strong>.',
				'severity' => 'info',
			);

			return;
		}

		if ( ! is_email( $email ) ) {
			$this->inline_login_response = array(
				'message'  => '<strong>Not a valid email address</strong>.',
				'severity' => 'info',
			);
			return;
		}

		$user = get_user_by( 'email', $email );

		if ( false === $user ) {

			$this->inline_login_response = array(
				'message'  => '<strong>Account does not exist</strong>.',
				'severity' => 'error',
			);
			return;
		}

		if ( ! wp_check_password( $password, $user->data->user_pass, $user->ID ) ) {

			$this->inline_login_response = array(
				'message'  => '<strong>Incorrect password</strong>.',
				'severity' => 'error',
			);
			return;
		}

		wp_set_current_user( $user->ID, $user->user_login );

		/**
		 * We need the cookie to be accessible via $_COOKIES for the new nonce to be correctly created, otherwise
		 * a nonce is created for a logged in user with no cookie, and fails to verify when their next request does
		 * have a cookie.
		 *
		 * PHP's setcookie() does not seem to fill $_COOKIES[] so we do it manually.
		 *
		 * @see wp_verify_nonce()
		 * @see wp_get_session_token()
		 * @see wp_parse_auth_cookie()
		 * @see wp_set_auth_cookie()
		 */
		add_action(
			'set_logged_in_cookie',
			function( $auth_cookie, $expire, $expiration, $user_id, $scheme, $token ) {
				$_COOKIE[ LOGGED_IN_COOKIE ] = implode( '|', array( 'username', 'expiration', $token, 'hmac' ) );
			},
			10,
			6
		);

		wp_set_auth_cookie( $user->ID );
		do_action( 'wp_login', $user->user_login, $user );

		$this->inline_login_response = array(
			'message'  => '<strong>Thank you for logging in</strong>.',
			'severity' => 'message',
		);

		/**
		 * After logging in, replace the `wc_checkout_params` JSON in the HTML document with updated nonces.
		 *
		 * @see \WC_Frontend_Scripts::get_script_data('wc-checkout'); (private access).
		 */
		add_filter(
			'woocommerce_update_order_review_fragments',
			function( $fragments ) {
				global $wp;
				$params = array(
					'ajax_url'                  => WC()->ajax_url(),
					'wc_ajax_url'               => \WC_AJAX::get_endpoint( '%%endpoint%%' ),
					'update_order_review_nonce' => wp_create_nonce( 'update-order-review' ),
					'apply_coupon_nonce'        => wp_create_nonce( 'apply-coupon' ),
					'remove_coupon_nonce'       => wp_create_nonce( 'remove-coupon' ),
					'option_guest_checkout'     => get_option( 'woocommerce_enable_guest_checkout' ),
					'checkout_url'              => \WC_AJAX::get_endpoint( 'checkout' ),
					'is_checkout'               => is_checkout() && empty( $wp->query_vars['order-pay'] ) && ! isset( $wp->query_vars['order-received'] ) ? 1 : 0,
					'i18n_checkout_error'       => esc_attr__( 'Error processing checkout. Please try again.', 'woocommerce' ),
				);

				$params_var = 'var wc_checkout_params = ' . wp_json_encode( $params ) . ';';

				$fragments['#wc-checkout-js-extra'] = "<script id=\"wc-checkout-js-extra\">{$params_var}</script>";

				return $fragments;

			}
		);

	}

	/**
	 * Instance variable to hold the success/failure message after login.
	 *
	 * Severity: one of info|message|error standard WooCommerce CSS.
	 *
	 * @var array{'message': string, 'severity': string}
	 */
	protected array $inline_login_response = array();

	/**
	 * After login and password reset actions, display feedback to the user.
	 *
	 * @see WC_Checkout::get_checkout_fields()
	 * @see https://rudrastyh.com/woocommerce/reorder-checkout-fields.html
	 *
	 * @hooked woocommerce_checkout_fields
	 *
	 * @param array $checkout_fields List of checkout fields to later render.
	 *
	 * @return array
	 */
	public function add_login_response_notice( array $checkout_fields ): array {

		$checkout_inline_login_response_field = array(
			'type'     => 'checkout_inline_login_response',
			'priority' => 6, // Immediately after the login form.
		);

		$checkout_fields['billing']['inline_login_response'] = $checkout_inline_login_response_field;

		return $checkout_fields;
	}

	/**
	 * Custom field renderer for success/failure messages for login.
	 *
	 * Options for severity (css): message|info|error.
	 *
	 * @hooked woocommerce_form_field_checkout_inline_login_response
	 *
	 * @see woocommerce_form_field()
	 *
	 * @param string $_field HTML to output.
	 * @param string $_key
	 * @param mixed  $_args
	 * @param string $_value
	 *
	 * @return string HTML to output.
	 */
	public function woocommerce_form_field_checkout_inline_login_response( $_field, $_key, $_args, $_value ) {

		if ( empty( $this->inline_login_response ) ) {
			return '';
		}

		$field = '<p>';

		$field .= '<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">';
		$field .= '<ul class="woocommerce-' . $this->inline_login_response['severity'] . '">';
		$field .= '<li>';
		$field .= $this->inline_login_response['message'];
		$field .= '</li>';
		$field .= '</ul>';
		$field .= '</div>';

		$field .= '</p>';

		return $field;
	}

	/**
	 * Handle the passwrod reset button.
	 *
	 * Send the user a password reset link to the billing_email.
	 *
	 * @see WC_Shortcode_My_Account::retrieve_password();
	 *
	 * @hooked woocommerce_checkout_update_order_review
	 *
	 * @param string $_posted_data The `posted_data` key of the $_POST array send by checkout.js, as parsed earlier.
	 */
	public function send_password_reset_email( $_posted_data ): void {

		if ( is_user_logged_in() ) {
			// TODO: Add a notice?
			return;
		}

		$checkout = WC_Checkout::instance();

		if ( empty( $checkout->get_value( 'password_reset_button_clicked' ) ) ) {
			return;
		}

		$email = $checkout->get_value( 'billing_email' );

		if ( empty( $email ) ) {
			return;
		}

		$user = get_user_by( 'email', $email );

		if ( false === $user ) {
			// TODO: Add a message ~"no user found with that email"... although that shouldn't really happen.
			return;
		}

		$key = get_password_reset_key( $user );

		// Send email notification.
		WC()->mailer(); // Load email classes.
		do_action( 'woocommerce_reset_password_notification', $user->user_login, $key );

		$this->inline_login_response = array(
			'message'  => '<strong>Password reset email sent</strong>.',
			'severity' => 'message',
		);
	}
}
