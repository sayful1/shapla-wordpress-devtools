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

if( ! class_exists('WP_DB_Admin') ):

class WP_DB_Admin {

	private $plugin_name = 'wp-db-admin';
	private static $instance = null;

	/**
	 * Main WP_DB_Admin Instance
	 * Ensures only one instance of WP_DB_Admin is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @return WP_DB_Admin - Main instance
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct()
	{
		// Define constants
        $this->define_constants();

		add_action( 'init', array( $this, 'include_files' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	private function define_constants()
	{
		define('SHAPLA_IE_VERSION', '1.0.0' );
		define('SHAPLA_IE_FILE', __FILE__ );
		define('SHAPLA_IE_PATH', dirname( SHAPLA_IE_FILE ) );
		define('SHAPLA_IE_INCLUDES', SHAPLA_IE_PATH . '/includes' );
		define('SHAPLA_IE_VIEWS', SHAPLA_IE_PATH . '/views' );
		define('SHAPLA_IE_URL', plugins_url( '', SHAPLA_IE_FILE ) );
		define('SHAPLA_IE_ASSETS', SHAPLA_IE_URL . '/assets' );
	}

	public function include_files()
	{
		include_once SHAPLA_IE_INCLUDES . '/functions.php';
		include_once SHAPLA_IE_INCLUDES . '/process-table-data.php';
		include_once SHAPLA_IE_INCLUDES . '/export-table-data.php';
	}

	public function admin_menu()
	{
		$hook = add_management_page(
			__('Database Admin'),
			__('Database Admin'),
			'manage_options',
			'shapla-import-export',
			array( $this, 'management_page' )
		);

		add_action( "load-$hook", array( $this, 'screen_option' ) );
	}

	public function management_page()
	{
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'warning';

        switch ($tab) {
            case 'export':
                $template = SHAPLA_IE_VIEWS . '/export.php';
                break;

            case 'import':
                $template = SHAPLA_IE_VIEWS . '/import.php';
                break;

            case 'table-data':
                $template = SHAPLA_IE_VIEWS . '/table-data.php';
                break;

            case 'tables-list':
                $template = SHAPLA_IE_VIEWS . '/tables-list.php';
                break;

            default:
                $template = SHAPLA_IE_VIEWS . '/warning.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        }
	}

    public function screen_option()
    {
        $option = 'per_page';
        $args   = [
            'label'   => __( 'Number of items per page:' ),
            'default' => 300,
            'option'  => 'shapla_import_export_per_page'
        ];

        add_screen_option( $option, $args );
    }

    public function admin_scripts( $hook )
    {
    	if ( $hook != 'tools_page_shapla-import-export' ) {
    		return;
    	}

    	wp_enqueue_style( $this->plugin_name . '-admin', SHAPLA_IE_ASSETS . '/css/admin.css', array(), SHAPLA_IE_VERSION, 'all' );
    	wp_enqueue_script( $this->plugin_name . '-admin', SHAPLA_IE_ASSETS . '/js/scripts.js', array( 'jquery' ), SHAPLA_IE_VERSION, true );
    }

	/**
	 * Get all tables name from MySQL database
	 * @return array
	 */
	public function get_tables()
	{
		global $wpdb;
		$tables_list = $wpdb->get_results("show tables");

		$tables = array();

		foreach ($tables_list as $_tables_list) {
			foreach ($_tables_list as $key => $value) {
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
	public function get_table_headers( $table_name )
	{
		global $wpdb;
		$table_header = $wpdb->get_results("SHOW COLUMNS FROM $table_name FROM $wpdb->dbname;");

		$tables = array();
		foreach ($table_header as $_table_header) {
			$tables[] = $_table_header->Field;
		}

		return $tables;
	}
}

endif;

WP_DB_Admin::init();