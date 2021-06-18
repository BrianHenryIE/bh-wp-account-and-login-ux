<?php
/**
 * Class Plugin_Test. Tests the root plugin setup.
 *
 * @package BH_WP_Account_And_Login_UX
 * @author     Brian Henry <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WP_Account_And_Login_UX;

use BrianHenryIE\WP_Account_And_Login_UX\Includes\BH_WP_Account_And_Login_UX;

/**
 * Verifies the plugin has been instantiated and added to PHP's $GLOBALS variable.
 */
class BH_WP_Account_And_Login_Integration_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * Test the main plugin object is added to PHP's GLOBALS and that it is the correct class.
	 */
	public function test_plugin_instantiated() {

		$this->markTestSkipped( 'No API needed, no point adding a global class reference.' );

		$this->assertArrayHasKey( 'bh_wp_account_and_login_ux', $GLOBALS );

		$this->assertInstanceOf( BH_WP_Account_And_Login_UX::class, $GLOBALS['bh_wp_account_and_login_ux'] );
	}

}
