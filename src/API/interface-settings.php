<?php

namespace BrianHenryIE\WP_Account_And_Login_UX\API;

interface Settings_Interface {

	public function get_plugin_slug(): string;

	public function get_plugin_version(): string;
}
