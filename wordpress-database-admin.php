<?php
/*!
 * Plugin Name: 	WordPress Database Admin
 * Description: 	Manage your WordPress database from WordPress Admin Panel.
 * Plugin URI: 		https://sayfulislam.com/
 * Version: 		1.0.0
 * Author: 			Sayful Islam
 * Author URI: 		https://sayfulislam.com
 * License: 		GPLv2 or later
 * License URI:		http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'WordPress_Database_Admin' ) ) {

	class WordPress_Database_Admin {

		/**
		 * Current plugin slug
		 *
		 * @var string
		 */
		private $plugin_name = 'wordpress-database-admin';

		/**
		 * @var null
		 */
		private static $instance = null;

		/**
		 * Main WP_DB_Admin Instance
		 * Ensures only one instance of WP_DB_Admin is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @return WordPress_Database_Admin - Main instance
		 */
		public static function init() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * WordPress_Database_Admin constructor.
		 */
		public function __construct() {
			// Define constants
			$this->define_constants();

			// Include plugin files
			$this->include_files();

			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		}

		/**
		 * Define plugin constants
		 */
		private function define_constants() {
			define( 'WORDPRESS_DATABASE_ADMIN_VERSION', '1.0.0' );
			define( 'WORDPRESS_DATABASE_ADMIN_FILE', __FILE__ );
			define( 'WORDPRESS_DATABASE_ADMIN_PATH', dirname( WORDPRESS_DATABASE_ADMIN_FILE ) );
			define( 'WORDPRESS_DATABASE_ADMIN_INCLUDES', WORDPRESS_DATABASE_ADMIN_PATH . '/includes' );
			define( 'WORDPRESS_DATABASE_ADMIN_VIEWS', WORDPRESS_DATABASE_ADMIN_PATH . '/views' );
			define( 'WORDPRESS_DATABASE_ADMIN_URL', plugins_url( '', WORDPRESS_DATABASE_ADMIN_FILE ) );
			define( 'WORDPRESS_DATABASE_ADMIN_ASSETS', WORDPRESS_DATABASE_ADMIN_URL . '/assets' );
		}

		/**
		 * Include plugin files
		 */
		private function include_files() {
			include_once WORDPRESS_DATABASE_ADMIN_INCLUDES . '/functions.php';
			include_once WORDPRESS_DATABASE_ADMIN_INCLUDES . '/process-table-data.php';
			include_once WORDPRESS_DATABASE_ADMIN_INCLUDES . '/export-table-data.php';
		}

		/**
		 * Add tools page
		 */
		public function admin_menu() {
			$hook = add_management_page(
				__( 'Database Admin', 'wordpress-database-admin' ),
				__( 'Database Admin', 'wordpress-database-admin' ),
				'manage_options',
				'shapla-import-export',
				array( $this, 'management_page' )
			);

			add_action( "load-$hook", array( $this, 'screen_option' ) );
		}

		/**
		 * Load page content
		 */
		public function management_page() {
			$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'warning';

			switch ( $tab ) {
				case 'export':
					$template = WORDPRESS_DATABASE_ADMIN_VIEWS . '/export.php';
					break;

				case 'import':
					$template = WORDPRESS_DATABASE_ADMIN_VIEWS . '/import.php';
					break;

				case 'table-data':
					$template = WORDPRESS_DATABASE_ADMIN_VIEWS . '/table-data.php';
					break;

				case 'tables-list':
					$template = WORDPRESS_DATABASE_ADMIN_VIEWS . '/tables-list.php';
					break;

				default:
					$template = WORDPRESS_DATABASE_ADMIN_VIEWS . '/warning.php';
					break;
			}

			if ( file_exists( $template ) ) {
				include $template;
			}
		}

		/**
		 * Add screen option for admin page
		 */
		public function screen_option() {
			$option = 'per_page';
			$args   = array(
				'label'   => __( 'Number of items per page:', 'wordpress-database-admin' ),
				'default' => 100,
				'option'  => 'wordpress_database_admin_per_page'
			);

			add_screen_option( $option, $args );
		}

		/**
		 * Load admin scripts
		 *
		 * @param $hook
		 */
		public function admin_scripts( $hook ) {
			if ( $hook != 'tools_page_shapla-import-export' ) {
				return;
			}

			wp_enqueue_style( $this->plugin_name . '-admin', WORDPRESS_DATABASE_ADMIN_ASSETS . '/css/admin.css', array(), WORDPRESS_DATABASE_ADMIN_VERSION, 'all' );
			wp_enqueue_script( $this->plugin_name . '-admin', WORDPRESS_DATABASE_ADMIN_ASSETS . '/js/scripts.js', array( 'jquery' ), WORDPRESS_DATABASE_ADMIN_VERSION, true );
		}

		/**
		 * Get all tables name from MySQL database
		 * @return array
		 */
		public function get_tables() {
			global $wpdb;
			$tables_list = $wpdb->get_results( "SHOW TABLES" );

			$tables = array();

			foreach ( $tables_list as $_tables_list ) {
				foreach ( $_tables_list as $key => $value ) {
					$tables[] = $value;
				}
			}

			return $tables;
		}

		/**
		 * Get table header for particular table
		 *
		 * @param  string $table_name MySQL table name
		 *
		 * @return array
		 */
		public function get_table_headers( $table_name ) {
			global $wpdb;
			$table_name   = esc_sql( $table_name );
			$table_header = $wpdb->get_results( "SHOW COLUMNS FROM $table_name FROM $wpdb->dbname;" );

			$tables = array();
			foreach ( $table_header as $_table_header ) {
				$tables[] = $_table_header->Field;
			}

			return $tables;
		}
	}
}


WordPress_Database_Admin::init();