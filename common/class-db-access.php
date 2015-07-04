<?php

/* Tables */
const TABLE_ACCESS = 'wc2_access';

class WC2_DB_Access
{
	/* Public variables */

	public $access_table = '';//Access table name

	/* Private variables */
	protected $access_data = array();

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

	// Get database values
	public function get_access_data() {
		global $wpdb;

		$query = $this->get_query();
		if( empty($query) ) return array();

		$this->clear_column();

		$data = $wpdb->get_results( $query, ARRAY_A );
		$i = 0;
		foreach( (array)$data as $row ) {
			$this->access_data[$i]['key'] = $row['acc_key'];
			$this->access_data[$i]['type'] = $row['acc_type'];
			$this->access_data[$i]['value'] = $row['acc_value'];
			$this->access_data[$i]['date'] = $row['acc_date'];
			$this->access_data[$i]['num1'] = $row['acc_num1'];
			$this->access_data[$i]['num2'] = $row['acc_num2'];
			$this->access_data[$i]['str1'] = $row['acc_str1'];
			$this->access_data[$i]['str2'] = $row['acc_str2'];
			$i++;
		}

		return $this->access_data;
	}

	// Delete database
	public function delete_access_data() {
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
		$this->access_data = array();
	}

	//insert lostdata
	public function store_lost_mail_key( $lostmail, $lostkey ) {
		global $wpdb;
		$date = current_time('mysql');
		$query = $wpdb->prepare( "INSERT INTO {$this->access_table} ( acc_key, acc_type, acc_value, acc_date )  VALUES ( %s, %s, %s, %s )", $lostmail, 'lostkey', $lostkey, $date );
		$res = $wpdb->query($query);
		return $res;
	}

	//delete lostdata
	public function remove_lost_mail_key( $lostmail, $lostkey ) {
		global $wpdb;
		$query = $wpdb->prepare( "DELETE FROM {$this->access_table} WHERE acc_key = %s AND acc_value = %s", $lostmail, $lostkey );
		$res = $wpdb->query($query);
		return $res;
	}

	//ロストキーチェック
	public function check_lostkey( $lostmail, $lostkey ) {
		global $wpdb;
		$query = $wpdb->prepare( "SELECT ID FROM {$this->access_table} WHERE acc_key = %s AND acc_type = %s AND acc_value = %s", $lostmail, 'lostkey', $lostkey );
		$res = $wpdb->get_var($query);
		return $res;
	}

	//制限時間チェック
	public function check_lostlimit( $lostmail, $lostkey ) {
		global $wpdb;
		$query = $wpdb->prepare( "SELECT acc_date FROM {$this->access_table} WHERE acc_key = %s AND acc_type = %s AND acc_value = %s", $lostmail, 'lostkey', $lostkey );
		$losttime = $wpdb->get_col($query);
		$limittime = date("Y-m-d H:i:s", strtotime("$losttime[0] +1 day"));
		$current = current_time('mysql');
		if( $current <= $limittime ){
			return true;
		}else{
			return false;
		}
		//return $res;
	}

	public function get_access_date( $key ) {
		global $wpdb;
		$query = $wpdb->prepare( "SELECT acc_date FROM {$this->access_table} WHERE acc_key = %s LIMIT 1", $key );
		$acc_date = $wpdb->get_var( $query );
		return $acc_date;
	}

	public function add_access_date( $date, $key ) {
		global $wpdb;
		$query = $wpdb->prepare( "INSERT INTO {$this->access_table} ( acc_date, acc_key ) VALUES ( %s, %s )", $date, $key );
		$res = $wpdb->query( $query );
		return $res;
	}

	public function update_access_date( $date, $key ) {
		global $wpdb;
		$query = $wpdb->prepare( "UPDATE {$this->access_table} SET acc_date = %s WHERE acc_key = %s LIMIT 1", $date, $key );
		$res = $wpdb->query( $query );
		return $res;
	}

	/* Private functions */

	//Initial table name
	private function _set_table() {
		global $wpdb;
		$this->access_table = $wpdb->prefix.TABLE_ACCESS;
	}
}

function wc2_store_lost_mail_key( $lostmail, $lostkey ) {
	$wc2_access = WC2_DB_Access::get_instance();
	return $wc2_access->store_lost_mail_key( $lostmail, $lostkey );
}

function wc2_remove_lost_mail_key( $lostmail, $lostkey ) {
	$wc2_access = WC2_DB_Access::get_instance();
	return $wc2_access->remove_lost_mail_key( $lostmail, $lostkey );
}

function wc2_check_lostkey( $lostmail, $lostkey ) {
	$wc2_access = WC2_DB_Access::get_instance();
	return $wc2_access->check_lostkey( $lostmail, $lostkey );
}

function wc2_check_lostlimit( $lostmail, $lostkey ) {
	$wc2_access = WC2_DB_Access::get_instance();
	return $wc2_access->check_lostlimit( $lostmail, $lostkey );
}

function wc2_get_access_date( $key ) {
	$wc2_access = WC2_DB_Access::get_instance();
	return $wc2_access->get_access_date( $key );
}

function wc2_add_access_date( $date, $key ) {
	$wc2_access = WC2_DB_Access::get_instance();
	return $wc2_access->add_access_date( $date, $key );
}

function wc2_update_access_date( $date, $key ) {
	$wc2_access = WC2_DB_Access::get_instance();
	return $wc2_access->update_access_date( $date, $key );
}
