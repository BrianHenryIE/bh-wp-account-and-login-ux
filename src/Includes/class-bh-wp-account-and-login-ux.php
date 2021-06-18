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
use BrianHenryIE\WP_Account_And_Login_UX\BrianHenryIE\WPPB\WPPB_Loader_Interface;
use BrianHenryIE\WP_Account_And_Login_UX\BrianHenryIE\WPPB\WPPB_Plugin_Abstract;
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
class BH_WP_Account_And_Login_UX extends WPPB_Plugin_Abstract {

	/** @var Settings_Interface */
	protected $settings;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the frontend-facing side of the site.
	 *
	 * @since    1.0.0
	 *
	 * @param WPPB_Loader_Interface $loader The WPPB class which adds the hooks and filters to WordPress.
	 * @param Settings_Interface    $settings Facade for wp_options.
	 */
	public function __construct( $loader, $settings ) {
		if ( defined( 'BH_WP_ACCOUNT_AND_LOGIN_UX_VERSION' ) ) {
			$version = BH_WP_ACCOUNT_AND_LOGIN_UX_VERSION;
		} else {
			$version = '1.0.0';
		}
		$plugin_name = 'bh-wp-account-and-login-ux';

		parent::__construct( $loader, $plugin_name, $version );

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
	protected function set_locale() {

		$plugin_i18n = new I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	protected function define_admin_hooks() {

		$plugin_admin = new Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	protected function define_frontend_hooks() {

		$plugin_frontend = new Frontend( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_frontend, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_frontend, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to WooCommerce.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	protected function define_woocommerce_hooks() {

		$checkout = new Checkout( $this->settings );

		$this->loader->add_action( 'wp_footer', $checkout, 'add_is_user_logged_in_json' );

		$this->loader->add_action( 'wp_enqueue_scripts', $checkout, 'enqueue_scripts' );

		$this->loader->add_filter( 'pre_option_woocommerce_enable_checkout_login_reminder', $checkout, 'woocommerce_disable_checkout_login_reminder' );

		$this->loader->add_filter( 'woocommerce_checkout_fields', $checkout, 'move_email_input_first' );

		// Parse $_POST for common use later.
		$this->loader->add_action( 'woocommerce_checkout_update_order_review', $checkout, 'parse_post_on_update_order_review', 1, 1 );

		// Runs during woocommerce_checkout_update_order_review.
		$this->loader->add_filter( 'woocommerce_update_order_review_fragments', $checkout, 'rerender_billing_fields_fragment' );

		$this->loader->add_filter( 'woocommerce_checkout_fields', $checkout, 'add_password_field_login_button_to_billing' );

		$this->loader->add_filter( 'woocommerce_checkout_fields', $checkout, 'add_login_response_notice' );

		$this->loader->add_filter( 'woocommerce_form_field_checkout_inline_login_field', $checkout, 'woocommerce_form_field_checkout_inline_login_field', 10, 4 );

		$this->loader->add_filter( 'woocommerce_form_field_checkout_inline_login_response', $checkout, 'woocommerce_form_field_checkout_inline_login_response', 10, 4 );

		$this->loader->add_action( 'woocommerce_checkout_update_order_review', $checkout, 'inline_log_user_in', 2, 1 );

		$this->loader->add_action( 'woocommerce_checkout_update_order_review', $checkout, 'send_password_reset_email', 2, 1 );

	}

}
