<?php

/* Tables */
const TABLE_LOG = 'wc2_log';

class WC2_DB_Log
{
	/* Public variables */

	public $log_table = '';//Log table name

	/* Private variables */
	protected $log_type = '';
	protected $log_key = '';
	protected $log = '';
	protected $log_data = array();

	protected $query = '';

	protected static $instance = null;

	/* Constructor */

	private function __construct() {
		$plugin = Welcart2::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		//Initial processing.
		$this->_set_table();
	}

	/* Public functions */

	public static function get_instance() {
		if( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function set_select_query( $key, $keytype = 'key', $type = '' ) {
		global $wpdb;
		switch( $keytype ) {
			case 'datetime':
				$this->query = $wpdb->prepare( "SELECT * FROM {$this->log_table} WHERE log_datetime LIKE %s", $key."%" );
				break;
			case 'type':
				$this->query = $wpdb->prepare( "SELECT * FROM {$this->log_table} WHERE log_type = %s", $key );
				break;
			case 'key':
				$this->query = $wpdb->prepare( "SELECT * FROM {$this->log_table} WHERE log_key = %s", $key );
				break;
			default:
				$this->query = $wpdb->prepare( "SELECT * FROM {$this->log_table} WHERE log_type = %s AND log_key = %s", $type, $key );
		}
	}

	public function set_delete_query( $key, $keytype = 'key', $type = '' ) {
		global $wpdb;
		switch( $keytype ) {
			case 'datetime':
				$this->query = $wpdb->prepare( "DELETE FROM {$this->log_table} WHERE log_datetime LIKE %s", $key."%" );
				break;
			case 'type':
				$this->query = $wpdb->prepare( "DELETE FROM {$this->log_table} WHERE log_type = %s", $key );
				break;
			case 'key':
				$this->query = $wpdb->prepare( "DELETE FROM {$this->log_table} WHERE log_key = %s", $key );
				break;
			default:
				$this->query = $wpdb->prepare( "DELETE FROM {$this->log_table} WHERE log_type = %s AND log_key = %s", $type, $key );
		}
	}

	// Get database values
	public function get_log_data() {
		global $wpdb;

		$query = $this->get_query();
		if( empty($query) ) return array();

		$this->clear_column();

		$data = $wpdb->get_results( $query, ARRAY_A );
		$i = 0;
		foreach( (array)$data as $row ) {
			$this->log_data[$i]['datetime'] = $row['log_datetime'];
			$this->log_data[$i]['type'] = $row['log_type'];
			$this->log_data[$i]['key'] = $row['log_key'];
			$this->log_data[$i]['log'] = $row['log'];
			$i++;
		}

		return $this->log_data;
	}

	// Register database
	public function add_log_data() {
		global $wpdb;

		$query = $wpdb->prepare( "INSERT INTO {$this->log_table} 
				( log_datetime, log_type, log_key, log ) 
			VALUES 
				( %s, %s, %s, %s )",
			get_date_from_gmt(gmdate('Y-m-d H:i:s', time())),
			$this->log_type,
			$this->log_key,
			$this->log
		);

		$res = $wpdb->query( $query );
		return $res;
	}

	// Delete database
	public function delete_log_data() {
		global $wpdb;

		$query = $this->get_query();
		if( empty($query) ) return false;

		$res = $wpdb->query( $query );
		return $res;
	}

	// Set query
	public function set_query( $query ) {
		$this->query = $query;
	}

	// Get query
	public function get_query() {
		return $this->query;
	}

	// Create values
	public function clear_column() {
		$this->log_data = array();
	}

	// Set values
	public function set_log_type( $value ) {
		$this->log_type = $value;
	}
	public function set_log_key( $value ) {
		$this->log_key = $value;
	}
	public function set_log( $value ) {
		$this->log = $value;
	}

	/* Private functions */

	//Initial table name
	private function _set_table() {
		global $wpdb;
		$this->log_table = $wpdb->prefix.TABLE_LOG;
	}
}

function wc2_set_log( $log, $type = 'message', $key = '' ) {
	$wc2_log = WC2_DB_Log::get_instance();
	$wc2_log->set_log_type( $type );
	$wc2_log->set_log_key( $key );
	$wc2_log->set_log( $log );
	$wc2_log->add_log_data();
}

function wc2_get_log_data( $type, $key ) {
	$wc2_log = WC2_DB_Log::get_instance();
	$wc2_log->set_select_query( $key, 'typekey', $type );
	return $wc2_log->get_log_data();
}

function wc2_get_log_data_by_type( $type ) {
	$wc2_log = WC2_DB_Log::get_instance();
	$wc2_log->set_select_query( $type, 'type' );
	return $wc2_log->get_log_data();
}

function wc2_get_log_data_by_key( $key ) {
	$wc2_log = WC2_DB_Log::get_instance();
	$wc2_log->set_select_query( $key, 'key' );
	return $wc2_log->get_log_data();
}

function wc2_get_log_data_by_date( $date ) {
	$wc2_log = WC2_DB_Log::get_instance();
	$wc2_log->set_select_query( $date, 'datetime' );
	return $wc2_log->get_log_data();
}

function wc2_delete_log_data( $type, $key ) {
	$wc2_log = WC2_DB_Log::get_instance();
	$wc2_log->set_delete_query( $key, 'typekey', $type );
	return $wc2_log->delete_log_data();
}

function wc2_delete_log_data_by_type( $type ) {
	$wc2_log = WC2_DB_Log::get_instance();
	$wc2_log->set_delete_query( $type, 'type' );
	return $wc2_log->delete_log_data();
}

function wc2_delete_log_data_by_key( $key ) {
	$wc2_log = WC2_DB_Log::get_instance();
	$wc2_log->set_delete_query( $key, 'key' );
	return $wc2_log->delete_log_data();
}

function wc2_delete_log_data_by_date( $date ) {
	$wc2_log = WC2_DB_Log::get_instance();
	$wc2_log->set_delete_query( $date, 'datetime' );
	return $wc2_log->delete_log_data();
}
