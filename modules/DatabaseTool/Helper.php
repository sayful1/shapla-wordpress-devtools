<?php

namespace Shapla\Devtools\Modules\DatabaseTool;

class Helper {
	/**
	 * List database tables
	 * @return array
	 */
	public static function get_database_tables_list(): array {
		global $wpdb;
		$query = sprintf(
			"SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE='BASE TABLE' AND TABLE_SCHEMA = '%s'",
			DB_NAME
		);

		return $wpdb->get_results( $query, ARRAY_A );
	}

	public static function truncate_table( $tables ) {
		global $wpdb;
		if ( is_string( $tables ) ) {
			$table = esc_sql( $tables );

			return $wpdb->query( "TRUNCATE TABLE $table" );
		}

		if ( is_array( $tables ) ) {
			foreach ( $tables as $table ) {
				$table = esc_sql( $table );
				$wpdb->query( "TRUNCATE TABLE $table" );
			}

			return true;
		}

		return false;
	}

	public static function drop_table( $tables ) {
		global $wpdb;
		if ( is_string( $tables ) ) {
			$table = esc_sql( $tables );

			return $wpdb->query( "DROP TABLE IF EXISTS $table" );
		}
		if ( is_array( $tables ) ) {
			foreach ( $tables as $table ) {
				$table = esc_sql( $table );
				$wpdb->query( "DROP TABLE IF EXISTS $table" );
			}

			return true;
		}

		return false;
	}

	public static function csv_export( $table ) {
		global $wpdb;
		$table_header = $wpdb->get_results( sprintf( "SHOW COLUMNS FROM $table FROM %s;", DB_NAME ) );
		$_header      = [];
		foreach ( $table_header as $_table_header ) {
			$_header[] = $_table_header->Field;
		}

		$items = $wpdb->get_results( "SELECT * FROM $table", ARRAY_N );

		// output headers so that the file is downloaded rather than displayed
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $table . '.csv' );
		// create a file pointer connected to the output stream
		$output = fopen( 'php://output', 'w' );

		// output the column headings
		fputcsv( $output, $_header );

		foreach ( $items as $item ) {
			fputcsv( $output, $item );
		}

		fclose( $output );
	}

	public static function csv_import( $table, $csv_filename ) {
		global $wpdb;
		$csv_results   = array_map( 'str_getcsv', file( $csv_filename ) );
		$table_header  = $wpdb->get_results( sprintf( "SHOW COLUMNS FROM $table FROM %s;", DB_NAME ) );
		$_header_count = count( $table_header );
		$new_data      = [];
		foreach ( $csv_results as $data ) {

			$_data_count = count( $data );

			if ( $_header_count !== $_data_count ) {
				continue;
			}

			$_header = [];
			foreach ( $table_header as $header ) {
				$_header[] = $header->Field;
			}
			for ( $i = 0; $i < $_data_count; $i ++ ) {
				$new_data[ $_header[ $i ] ] = $data[ $i ];
			}

		}

		$ids = [];
		foreach ( $new_data as $value ) {
			$wpdb->insert( $table, $value );
			$ids[] = $wpdb->insert_id;
		}

		return $ids;
	}

	/**
	 *  A method for inserting multiple rows into the specified table
	 *  Updated to include the ability to Update existing rows by primary key
	 *
	 *  Usage Example for insert:
	 *
	 *  $insert_arrays = array();
	 *  foreach($assets as $asset) {
	 *  $time = current_time( 'mysql' );
	 *  $insert_arrays[] = array(
	 *  'type' => "multiple_row_insert",
	 *  'status' => 1,
	 *  'name'=>$asset,
	 *  'added_date' => $time,
	 *  'last_update' => $time);
	 *
	 *  }
	 *
	 *
	 *  wp_insert_rows($insert_arrays, $wpdb->tablename);
	 *
	 *  Usage Example for update:
	 *
	 *  wp_insert_rows($insert_arrays, $wpdb->tablename, true, "primary_column");
	 *
	 *
	 * @param string $wp_table_name
	 * @param array $row_arrays
	 * @param boolean $update
	 * @param string $primary_key
	 *
	 * @return false|int
	 *
	 * @author    Ugur Mirza ZEYREK
	 * @contributor Travis Grenell
	 * @source http://stackoverflow.com/a/12374838/1194797
	 */
	public static function wp_insert_rows( $wp_table_name, $row_arrays = array(), $update = false, $primary_key = null ) {
		global $wpdb;
		$wp_table_name = esc_sql( $wp_table_name );
		// Setup arrays for Actual Values, and Placeholders
		$values        = array();
		$place_holders = array();
		$query         = "";
		$query_columns = "";

		$query .= "INSERT INTO `{$wp_table_name}` (";
		foreach ( $row_arrays as $count => $row_array ) {
			foreach ( $row_array as $key => $value ) {
				if ( $count == 0 ) {
					if ( $query_columns ) {
						$query_columns .= ", " . $key . "";
					} else {
						$query_columns .= "" . $key . "";
					}
				}

				$values[] = $value;

				$symbol = "%s";
				if ( is_numeric( $value ) ) {
					if ( is_float( $value ) ) {
						$symbol = "%f";
					} else {
						$symbol = "%d";
					}
				}
				if ( isset( $place_holders[ $count ] ) ) {
					$place_holders[ $count ] .= ", '$symbol'";
				} else {
					$place_holders[ $count ] = "( '$symbol'";
				}
			}
			// mind closing the GAP
			$place_holders[ $count ] .= ")";
		}

		$query .= " $query_columns ) VALUES ";

		$query .= implode( ', ', $place_holders );

		if ( $update ) {
			$update = " ON DUPLICATE KEY UPDATE $primary_key=VALUES( $primary_key ),";
			$cnt    = 0;
			foreach ( $row_arrays[0] as $key => $value ) {
				if ( $cnt == 0 ) {
					$update .= "$key=VALUES($key)";
					$cnt    = 1;
				} else {
					$update .= ", $key=VALUES($key)";
				}
			}
			$query .= $update;
		}

		$sql = $wpdb->prepare( $query, $values );
		if ( $wpdb->query( $sql ) ) {
			return true;
		} else {
			return false;
		}
	}
}