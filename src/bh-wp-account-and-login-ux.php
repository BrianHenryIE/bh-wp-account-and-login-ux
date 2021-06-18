<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           BH_WP_Account_And_Login_UX
 *
 * @wordpress-plugin
 * Plugin Name:       Account & Login UX
 * Plugin URI:        http://github.com/BrianHenryIE/bh-wp-account-and-login-ux/
 * Description:
 * Version:           0.1.1
 * Author:            Brian Henry
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bh-wp-account-and-login-ux
 * Domain Path:       /languages
 */

namespace BH_WP_Account_And_Login_UX;

use BrianHenryIE\WP_Account_And_Login_UX\API\Settings;
use BrianHenryIE\WP_Account_And_Login_UX\Includes\Activator;
use BrianHenryIE\WP_Account_And_Login_UX\Includes\Deactivator;
use BrianHenryIE\WP_Account_And_Login_UX\Includes\BH_WP_Account_And_Login_UX;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'autoload.php';

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BH_WP_ACCOUNT_AND_LOGIN_UX_VERSION', '0.1.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-activator.php
 */
function activate_bh_wp_account_and_login_ux(): void {

	Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-deactivator.php
 */
function deactivate_bh_wp_account_and_login_ux(): void {

	Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'BH_WP_Account_And_Login_UX\activate_bh_wp_account_and_login_ux' );
register_deactivation_hook( __FILE__, 'BH_WP_Account_And_Login_UX\deactivate_bh_wp_account_and_login_ux' );


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function instantiate_bh_wp_account_and_login_ux(): void {

	$settings = new Settings();

	new BH_WP_Account_And_Login_UX( $settings );

}

instantiate_bh_wp_account_and_login_ux();
