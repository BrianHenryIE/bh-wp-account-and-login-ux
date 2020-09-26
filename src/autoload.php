<?php
/**
 * Loads all required classes
 *
 * Uses classmap, PSR4 & wp-namespace-autoloader.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           BH_WP_Account_And_Login_UX
 *
 * @see https://github.com/pablo-sg-pacheco/wp-namespace-autoloader/
 */

namespace BH_WP_Account_And_Login_UX;

use BH_WP_Account_And_Login_UX\Pablo_Pacheco\WP_Namespace_Autoloader\WP_Namespace_Autoloader;


$class_map_file = __DIR__ . '/autoload_classmap.php';
if ( file_exists( $class_map_file ) ) {

	$class_map = include $class_map_file;

	if ( is_array( $class_map ) ) {
		spl_autoload_register(
			function ( $classname ) use ( $class_map ) {

				if ( array_key_exists( $classname, $class_map ) && file_exists( $class_map[ $classname ] ) ) {
					require_once $class_map[ $classname ];
				}
			}
		);
	}
}

// The plugin-scoped namespace for composer required libraries, as specified in composer.json Mozart config.
$dep_namespace = 'BH_WP_Account_And_Login_UX';
// The Mozart config `dep_directory` adjusted for relative path.
$dep_directory = '/vendor/';

spl_autoload_register(
	function ( $namespaced_class_name ) use ( $dep_namespace, $dep_directory ) {

		$autoload_directory = __DIR__ . $dep_directory . '/';

		// The class name with its true namespace.
		$bare_namespaced_class_name = preg_replace( "#$dep_namespace\\\*#", '', $namespaced_class_name );

		$file_path = $autoload_directory . str_replace( '\\', '/', $bare_namespaced_class_name ) . '.php';

		if ( file_exists( $file_path ) ) {
			require_once $file_path;
		}
	}
);

$wpcs_autoloader = new WP_Namespace_Autoloader();
$wpcs_autoloader->init();
