<?php

namespace BrianHenryIE\WP_Account_And_Login_UX\API;

class Settings implements Settings_Interface {

	/**
	 *
	 * @return string
	 */
	public function get_plugin_slug(): string {
		return 'bh-wp-account-and-login-ux';
	}

	/**
	 *
	 * @used-by Checkout::enqueue_scripts()
	 *
	 * @return string
	 */
	public function get_plugin_version(): string {
		return '0.2.2';
	}
}
