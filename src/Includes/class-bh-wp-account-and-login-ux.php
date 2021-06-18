<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * frontend-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    BH_WP_Account_And_Login_UX
 * @subpackage BH_WP_Account_And_Login_UX/includes
 */

namespace BrianHenryIE\WP_Account_And_Login_UX\Includes;

use BrianHenryIE\WP_Account_And_Login_UX\Admin\Admin;
use BrianHenryIE\WP_Account_And_Login_UX\API\Settings_Interface;
use BrianHenryIE\WP_Account_And_Login_UX\Frontend\Frontend;
use BrianHenryIE\WP_Account_And_Login_UX\WooCommerce\Checkout;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * frontend-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    BH_WP_Account_And_Login_UX
 * @subpackage BH_WP_Account_And_Login_UX/includes
 * @author     Brian Henry <BrianHenryIE@gmail.com>
 */
class BH_WP_Account_And_Login_UX {

	/** @var Settings_Interface */
	protected Settings_Interface $settings;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the frontend-facing side of the site.
	 *
	 * @since    1.0.0
	 *
	 * @param Settings_Interface $settings Facade for wp_options.
	 */
	public function __construct( Settings_Interface $settings ) {

		$this->settings = $settings;

		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_frontend_hooks();
		$this->define_woocommerce_hooks();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	protected function set_locale(): void {

		$plugin_i18n = new I18n();

		add_action( 'plugins_loaded', array( $plugin_i18n, 'load_plugin_textdomain' ) );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	protected function define_admin_hooks(): void {

		$plugin_admin = new Admin( $this->settings );

		add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_scripts' ) );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	protected function define_frontend_hooks(): void {

		$plugin_frontend = new Frontend( $this->settings );

		add_action( 'wp_enqueue_scripts', array( $plugin_frontend, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $plugin_frontend, 'enqueue_scripts' ) );

	}

	/**
	 * Register all of the hooks related to WooCommerce.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	protected function define_woocommerce_hooks(): void {

		$checkout = new Checkout( $this->settings );

		add_action( 'wp_footer', array( $checkout, 'add_is_user_logged_in_json' ) );

		add_action( 'wp_enqueue_scripts', array( $checkout, 'enqueue_scripts' ) );

		add_filter( 'pre_option_woocommerce_enable_checkout_login_reminder', array( $checkout, 'woocommerce_disable_checkout_login_reminder' ) );

		add_filter( 'woocommerce_checkout_fields', array( $checkout, 'move_email_input_first' ) );

		// Parse $_POST for common use later.
		add_action( 'woocommerce_checkout_update_order_review', array( $checkout, 'parse_post_on_update_order_review' ), 1, 1 );

		// Runs during woocommerce_checkout_update_order_review.
		add_filter( 'woocommerce_update_order_review_fragments', array( $checkout, 'rerender_billing_fields_fragment' ) );

		add_filter( 'woocommerce_checkout_fields', array( $checkout, 'add_password_field_login_button_to_billing' ) );

		add_filter( 'woocommerce_checkout_fields', array( $checkout, 'add_login_response_notice' ) );

		add_filter( 'woocommerce_form_field_checkout_inline_login_field', array( $checkout, 'woocommerce_form_field_checkout_inline_login_field' ), 10, 4 );

		add_filter( 'woocommerce_form_field_checkout_inline_login_response', array( $checkout, 'woocommerce_form_field_checkout_inline_login_response' ), 10, 4 );

		add_action( 'woocommerce_checkout_update_order_review', array( $checkout, 'inline_log_user_in' ), 2, 1 );

		add_action( 'woocommerce_checkout_update_order_review', array( $checkout, 'send_password_reset_email' ), 2, 1 );

	}

}
