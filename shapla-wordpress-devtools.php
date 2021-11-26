<?php
/*!
 * Plugin Name: 	Shapla WordPress Devtools
 * Description: 	Manage your WordPress database from WordPress Admin Panel.
 * Plugin URI: 		https://sayfulislam.com/
 * Version: 		1.0.0
 * Author: 			Sayful Islam
 * Author URI: 		https://sayfulislam.com
 * License: 		GPLv2 or later
 * License URI:		http://www.gnu.org/licenses/gpl-2.0.txt
 */

defined( 'ABSPATH' ) || die;

class ShaplaWordPressDevtools {

	/**
	 * Current plugin slug
	 *
	 * @var string
	 */
	private $plugin_name = 'shapla-wordpress-devtools';

	/**
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Main WP_DB_Admin Instance
	 * Ensures only one instance of WP_DB_Admin is loaded or can be loaded.
	 *
	 * @return ShaplaWordPressDevtools - Main instance
	 * @since 1.0.0
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			// Define constants
			self::$instance->define_constants();

			if ( file_exists( SHAPLA_DEVTOOLS_PATH . '/vendor/autoload.php' ) ) {
				include_once SHAPLA_DEVTOOLS_PATH . '/vendor/autoload.php';
			}

			// Include plugin files
			self::$instance->include_files();
		}

		return self::$instance;
	}

	/**
	 * Define plugin constants
	 */
	private function define_constants() {
		define( 'SHAPLA_DEVTOOLS', $this->plugin_name );
		define( 'SHAPLA_DEVTOOLS_VERSION', '1.0.0' );
		define( 'SHAPLA_DEVTOOLS_FILE', __FILE__ );
		define( 'SHAPLA_DEVTOOLS_PATH', dirname( SHAPLA_DEVTOOLS_FILE ) );
		define( 'SHAPLA_DEVTOOLS_INCLUDES', SHAPLA_DEVTOOLS_PATH . '/includes' );
		define( 'SHAPLA_DEVTOOLS_VIEWS', SHAPLA_DEVTOOLS_PATH . '/views' );
		define( 'SHAPLA_DEVTOOLS_URL', plugins_url( '', SHAPLA_DEVTOOLS_FILE ) );
		define( 'SHAPLA_DEVTOOLS_ASSETS', SHAPLA_DEVTOOLS_URL . '/assets' );
	}

	/**
	 * Include plugin files
	 */
	private function include_files() {
		Shapla\Devtools\Plugin::init();
	}
}

ShaplaWordPressDevtools::init();