<?php
/* Tables */
const TABLE_OPTIONS = 'wc2_options';

class WC2_Options
{
	protected $options = array();
	protected $options_table = '';//Options table name

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	public function __construct() {
		//Initial processing.
		$this->_set_table();
		$this->load_options();
	}

	/***********************************
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 ***********************************/
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	//Initial table name
	private function _set_table() {
		global $wpdb;
		$this->options_table = $wpdb->prefix.TABLE_OPTIONS;
	}

	/***********************************
	 * Get option value.
	 *
	 * @since     1.0.0
	 *
	 * @return    value
	 *            when $key is empty, return all option values.
	 ***********************************/
	public function get_option( $key = '' ) {
		global $wpdb;

		if( $key == '' ) {
			return $this->options;
		}
		if( is_array($this->options) && array_key_exists( $key, $this->options ) ) {
			return $this->options[$key];
		}

		$query = $wpdb->prepare( "SELECT option_value FROM $this->options_table WHERE option_name = %s", $key );
		$res = $wpdb->get_var( $query );
		if( empty($res) ) {
			$value = $res;
		} else {
			$value = maybe_unserialize($res);
			$this->options[$key] = $value;
		}
		return $value;
	}

	/***********************************
	 * Update option value.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function update_option( $key, $value ) {
		global $wpdb;

		if( is_array($key) || is_object($key) || empty($key) )
			return;

		if( is_array($this->options) && array_key_exists( $key, $this->options ) ) {
			//unset($this->options[$key]);
			$this->options[$key] = '';
		}

		$this->options[$key] = $value;
		$value_str = ( is_array($value) || is_object($value) ) ? serialize($value) : $value;

		$query = $wpdb->prepare( "SELECT option_id FROM $this->options_table WHERE option_name = %s", $key );
		$option_id = $wpdb->get_var( $query );
		if( !$option_id ) {
			$query = $wpdb->prepare( "INSERT INTO $this->options_table (option_name, option_value) values (%s, %s)", $key, $value_str );
			$res = $wpdb->query( $query );
		} else {
			$query = $wpdb->prepare( "UPDATE $this->options_table SET option_value = %s WHERE option_name = %s", $value_str, $key );
			$res = $wpdb->query( $query );
		}
		return $res;
	}

	/***********************************
	 * Delete option value.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function remove_option( $key ){
		global $wpdb;

		if( is_array($key) || is_object($key) || empty($key) )
			return;

		if( is_array($this->options) && array_key_exists( $key, $this->options ) ) {
			unset($this->options[$key]);
		}

		$query = $wpdb->prepare( "DELETE FROM $this->options_table WHERE option_name = %s", $key );
		$res = $wpdb->query( $query );

		return $res;
	}

	/***********************************
	 * Load all options.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function load_options() {
		global $wpdb;

		$this->options = array();
		$query = $wpdb->prepare( "SELECT option_name, option_value FROM $this->options_table WHERE autoload = %s", 'yes' );
		$res = $wpdb->get_results( $query );
		foreach( (array)$res as $row ) {
			$key = $row->option_name;
			if( NULL == $key )
				continue;

			if( empty($row->option_value) ) {
				$value = $row->option_value;
			} else {
				$value = maybe_unserialize($row->option_value);
			}
			$this->options[$key] = $value;
		}

		return $res;
	}

	/***********************************
	 * Get payment info.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function get_payment_info( $payment_method ) {
		$payment = array();
		$payment_option = self::get_payment_option( 'id' );
		if( array_key_exists( $payment_method, $payment_option ) )
			$payment = $payment_option[$payment_method];

		return $payment;
	}

	/***********************************
	 * Get payment options.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function get_payment_option( $keyflag ) {
		$payment = array();
		$payment_method = self::get_option( 'payment_method' );

		foreach( $payment_method as $id => $value ) {
			$key = ( isset($value[$keyflag]) ) ? $value[$keyflag] : $value['sort'];
			$payment[$key] = array(
				'id' => $id,
				'name' => $value['name'],
				'explanation' => $value['explanation'],
				'settlement' => $value['settlement'],
				'sort' => $value['sort'],
				'use' => isset( $value['use'] ) ? $value['use'] : 'activate'
			);
		}
		ksort($payment);
		return $payment;
	}
}

function wc2_get_option( $key = '' ) {
	$wc2_options = WC2_Options::get_instance();
	return $wc2_options->get_option( $key );
}

function wc2_update_option( $key, $value ) {
	$wc2_options = WC2_Options::get_instance();
	return $wc2_options->update_option( $key, $value );
}

function wc2_delete_option( $key ) {
	$wc2_options = WC2_Options::get_instance();
	return $wc2_options->remove_option( $key );
}

function wc2_get_payment( $payment_method ) {
	$wc2_options = WC2_Options::get_instance();
	return $wc2_options->get_payment_info( $payment_method );
}

function wc2_get_payment_option( $keyflag = 'sort' ) {
	$wc2_options = WC2_Options::get_instance();
	return $wc2_options->get_payment_option( $keyflag );
}
