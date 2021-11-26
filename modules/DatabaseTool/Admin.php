<?php

namespace Shapla\Devtools\Modules\DatabaseTool;

class Admin {

	private static $instance;

	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
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
				$template = SHAPLA_DEVTOOLS_VIEWS . '/export.php';
				break;

			case 'import':
				$template = SHAPLA_DEVTOOLS_VIEWS . '/import.php';
				break;

			case 'table-data':
				$template = SHAPLA_DEVTOOLS_VIEWS . '/table-data.php';
				break;

			case 'tables-list':
				$template = SHAPLA_DEVTOOLS_VIEWS . '/tables-list.php';
				break;

			default:
				$template = SHAPLA_DEVTOOLS_VIEWS . '/warning.php';
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

		wp_enqueue_style( SHAPLA_DEVTOOLS . '-admin' );
		wp_enqueue_script( SHAPLA_DEVTOOLS . '-admin' );
	}

	function export_table_data() {
		if ( ! isset( $_GET['page'], $_GET['tab'], $_GET['table'] ) ) {
			return;
		}

		if ( $_GET['page'] !== 'shapla-import-export' ) {
			return;
		}

		if ( $_GET['tab'] !== 'export' ) {
			return;
		}

		Helper::csv_export( esc_attr( $_GET['table'] ) );
		die();
	}

	function import_table_data() {
		if ( ! isset( $_POST['page'], $_POST['tab'], $_POST['table'] ) ) {
			return;
		}

		if ( $_POST['page'] !== 'shapla-import-export' ) {
			return;
		}

		if ( $_POST['tab'] !== 'import' ) {
			return;
		}

		$file  = isset( $_FILES['file_csv'] ) ? $_FILES['file_csv'] : null;
		$table = isset( $_POST['table'] ) ? $_POST['table'] : null;

		$tmp_name = isset( $file['tmp_name'] ) ? $file['tmp_name'] : null;

		$ids = Helper::csv_import( $table, $tmp_name );

		foreach ( $ids as $value ) {
			echo $value . '<br>';
		}
	}
}