<?php
/**
 * Tests for the root plugin file.
 *
 * @package BH_WP_Account_And_Login_UX
 * @author  Brian Henry <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WP_Account_And_Login_UX;

use BrianHenryIE\WP_Account_And_Login_UX\Includes\BH_WP_Account_And_Login_UX;

/**
 * Class Plugin_WP_Mock_Test
 */
class BH_WP_Account_And_Login_UX_Test extends \Codeception\Test\Unit {

	protected function _before() {
		\WP_Mock::setUp();
	}

	/**
	 * Verifies the plugin initialization.
	 */
	public function test_plugin_include() {

		$this->markTestSkipped( 'No API needed, no point adding a global class reference.' );

		$plugin_root_dir = dirname( __DIR__, 2 ) . '/src';

		\WP_Mock::userFunction(
			'plugin_dir_path',
			array(
				'args'   => array( \WP_Mock\Functions::type( 'string' ) ),
				'return' => $plugin_root_dir . '/',
			)
		);

		\WP_Mock::userFunction(
			'register_activation_hook'
		);

		\WP_Mock::userFunction(
			'register_deactivation_hook'
		);

		require_once $plugin_root_dir . '/bh-wp-account-and-login-ux.php';

		$this->assertArrayHasKey( 'bh_wp_account_and_login_ux', $GLOBALS );

		$this->assertInstanceOf( BH_WP_Account_And_Login_UX::class, $GLOBALS['bh_wp_account_and_login_ux'] );

	}


	/**
	 * Verifies the plugin does not output anything to screen.
	 */
	public function test_plugin_include_no_output() {

		$plugin_root_dir = dirname( __DIR__, 2 ) . '/src';

		\WP_Mock::userFunction(
			'plugin_dir_path',
			array(
				'args'   => array( \WP_Mock\Functions::type( 'string' ) ),
				'return' => $plugin_root_dir . '/',
			)
		);

		\WP_Mock::userFunction(
			'register_activation_hook'
		);

		\WP_Mock::userFunction(
			'register_deactivation_hook'
		);

		ob_start();

		require_once $plugin_root_dir . '/bh-wp-account-and-login-ux.php';

		$printed_output = ob_get_contents();

		ob_end_clean();

		$this->assertEmpty( $printed_output );

	}

}
