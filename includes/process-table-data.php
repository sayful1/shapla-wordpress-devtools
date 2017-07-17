<?php

if( ! class_exists('Shapla_IE_Process_Table_Data') ):

class Shapla_IE_Process_Table_Data
{
	/**
	 * @var Shapla_IE_Process_Table_Data The single instance of the class
	 */
	private static $_instance = null;

    /**
     * Ensures only one instance of Shapla_IE_Process_Table_Data is loaded or can be loaded.
     * @return Shapla_IE_Process_Table_Data - Main instance
     */
	public static function init()
	{
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * __construct 
	 * class constructor will set the needed filter and action hooks
	 * 
	 * @param array $args 
	 */
	public function __construct(){
		add_action( 'wp_ajax_get_cell_data', array( $this, 'get_cell_data') );
		add_action( 'wp_ajax_update_cell_data', array( $this, 'update_cell_data') );
	}

	public function get_cell_data()
	{
		$_table 	= isset($_POST['table']) ? esc_attr($_POST['table']) : null;
		$_column 	= isset($_POST['column']) ? esc_attr($_POST['column']) : null;
		$_key 		= isset($_POST['primary_key']) ? esc_attr($_POST['primary_key']) : null;
		$_value 	= isset($_POST['primary_value']) ? intval($_POST['primary_value']) : 0;

		if ( $_table && $_column && $_key && $_value ) {

			global $wpdb;
			$sql = "SELECT * FROM $_table WHERE $_key = $_value";
			$data = $wpdb->get_row( $sql, OBJECT );

			if ( null !== $data ) {

				$response = array(
					'table' 		=> $_table,
					'column' 		=> $_column,
					'primary_key' 	=> $_key,
					'primary_value' => $_value,
					'column_value' 	=> $data->{$_column},
				);
				wp_send_json_success( $response );
			} else {
				wp_send_json_error( __('Something went wrong.', 'textdomain'), 401 );
			}
		}

		wp_send_json_error( __('Required fields are not set properly.', 'textdomain'), 401 );
	}

	public function update_cell_data()
	{
		$_table 	= isset($_POST['table']) ? esc_attr($_POST['table']) : null;
		$_key 		= isset($_POST['primary_key']) ? esc_attr($_POST['primary_key']) : null;
		$_value 	= isset($_POST['primary_value']) ? intval($_POST['primary_value']) : 0;
		$_column 	= isset($_POST['column']) ? esc_attr($_POST['column']) : null;
		$_column_v 	= isset($_POST['column_value']) ? $_POST['column_value'] : 0;

		global $wpdb;
		$result = $wpdb->update(
			$_table,
			array( $_column => $_column_v ),
			array( $_key => $_value )
		);

		if ( false === $result ) {
			wp_send_json_error( __('Something went wrong.', 'textdomain'), 401 );
		}

		$response = array(
			'success_msg' 	=> "$result row has been updated.",
			'primary_value' => $_value,
			'column' 		=> $_column,
			'column_value' 	=> mb_strimwidth( esc_html($_column_v), 0, 50, ' ...', 'UTF-8' ),
		);

		wp_send_json_success( $response );
	}
}

endif;

Shapla_IE_Process_Table_Data::init();