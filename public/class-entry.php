<?php
/**
 *  class ENTRY.
 *
 * @package   Welcart2
 * @author    Collne Inc. <author@collne.com>
 * @license   GPL-2.0+
 * @link      http://www.welcart2.com
 * @copyright 2014 Collne Inc.
 */

class WC2_Entry {

	/**
	 * Instance of this class.
	 */
	protected static $instance = null;

	/**
	 * Constructor
	 *
	 */
	function __construct() {
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	public function get_entry() {
		$entry = array();

		$entry['member_id'] = ( isset($_SESSION[WC2]['member']['ID']) ) ? $_SESSION[WC2]['member']['ID'] : '';
		$entry['member_regmode'] = ( isset($_SESSION[WC2]['entry']['member_regmode']) ) ? $_SESSION[WC2]['entry']['member_regmode'] : 'none';

		$entry['customer'] = array(
			'account' => '',
			'email' => '',
			'email2' => '',
			'passwd' => '',
			'passwd2' => '',
			'name1' => '',
			'name2' => '',
			'name3' => '',
			'name4' => '',
			'zipcode' => '',
			'country' => '',
			'pref' => '',
			'address1' => '',
			'address2' => '',
			'tel' => '',
			'fax' => ''
		);
		if( isset($_SESSION[WC2]['entry']['customer']) ) {
			foreach( (array)$_SESSION[WC2]['entry']['customer'] as $key => $value ) {
				$entry['customer'][$key] = $value;
			}
		}
		if( isset($_SESSION[WC2]['entry'][WC2_CUSTOM_CUSTOMER]) ) {
			$entry['customer'][WC2_CUSTOM_CUSTOMER] = $_SESSION[WC2]['entry'][WC2_CUSTOM_CUSTOMER];
		}

		$entry['delivery'] = array(
			'name1' => '',
			'name2' => '',
			'name3' => '',
			'name4' => '',
			'zipcode' => '',
			'country' => '',
			'pref' => '',
			'address1' => '',
			'address2' => '',
			'tel' => '',
			'fax' => '',
			'delivery_flag' => ''
		);
		if( isset($_SESSION[WC2]['entry']['delivery']) ) {
			foreach( (array)$_SESSION[WC2]['entry']['delivery'] as $key => $value ) {
				$entry['delivery'][$key] = $value;
			}
		}

		if( isset($_SESSION[WC2]['entry'][WC2_CUSTOM_DELIVERY]) ) {
			$entry['delivery'][WC2_CUSTOM_DELIVERY] = $_SESSION[WC2]['entry'][WC2_CUSTOM_DELIVERY];
		}

		$entry['order'] = array(
			'note' => '',
			'delivery_method' => '',
			'delivery_name' => '',
			'delivery_date' => '',
			'delivery_time' => '',
			'delidue_date' => '',
			'payment_method' => '',
			'payment_name' => '',
			'condition' => array(),
			'shipping_charge' => '',
			'item_total_price' => '',
			'getpoint' => '',
			'usedpoint' => '',
			'discount' => '',
			'shipping_charge' => '',
			'cod_fee' => '',
			'tax' => '',
			'total_price' => ''
		);
		if( isset($_SESSION[WC2]['entry']['order']) ) {
			foreach( (array)$_SESSION[WC2]['entry']['order'] as $key => $value ) {
				$entry['order'][$key] = $value;
			}
		}

		if( isset($_SESSION[WC2]['entry'][WC2_CUSTOM_ORDER]) ) {
			$entry['order'][WC2_CUSTOM_ORDER] = $_SESSION[WC2]['entry'][WC2_CUSTOM_ORDER];
		}

		$entry = apply_filters( 'wc2_filter_get_entry', $entry );

		return $entry;
	}

	public function set_entry() {

		$_POST = wc2_stripslashes_deep_post( $_POST );

		if( isset($_SESSION[WC2]['member']['ID']) && !empty($_SESSION[WC2]['member']['ID']) ) {
			if( $_REQUEST['cart'] != 'confirm' ) {
				foreach( $_SESSION[WC2]['member'] as $key => $value ) {
					switch( $key ) {
					case 'ID':
					case 'passwd':
					case 'rank':
					case 'point':
					case 'registered':
						break;

					case WC2_CUSTOM_MEMBER:
						foreach( $value as $mbkey => $mbvalue ) {
							if( empty($_SESSION[WC2]['entry'][WC2_CUSTOM_CUSTOMER][$mbkey]) ) {
								if( is_array($mbvalue) ) {
									foreach( $mbvalue as $k => $v ) {
										$_SESSION[WC2]['entry'][WC2_CUSTOM_CUSTOMER][$mbkey][$v] = $v;
									}
								} else {
									$_SESSION[WC2]['entry'][WC2_CUSTOM_CUSTOMER][$mbkey] = $mbvalue;
								}
							}
						}
						break;

					case 'country':
						$_SESSION[WC2]['entry']['customer'][$key] = ( empty($value) ) ? wc2_get_base_country() : $value;
						break;

					default:
						if( is_array($value) ) {
							foreach( $value as $k => $v ) {
								$_SESSION[WC2]['entry']['customer'][$k] = $v;
							}
						} else {
							$_SESSION[WC2]['entry']['customer'][$key] = $value;
						}
					}
				}
			}
		}

		if( isset($_POST['customer']) ) {
			foreach( $_POST['customer'] as $key => $value ) {
				if( 'passwd' == $key || 'passwd2' == $key ){
					continue;
				}
				if( 'country' == $key && empty($value) ) {
					$_SESSION[WC2]['entry']['customer'][$key] = wc2_get_base_country();
				} else {
					$_SESSION[WC2]['entry']['customer'][$key] = $value;
				}
			}
		}

		if( isset($_POST['delivery']) ) {
			foreach( $_POST['delivery'] as $key => $value ) {
				if( 'country' == $key && empty($value) ){
					$_SESSION[WC2]['entry']['delivery'][$key] = wc2_get_base_country();
				} else{
					$_SESSION[WC2]['entry']['delivery'][$key] = $value;
				}
			}
		}

		if( isset($_POST['delivery']['delivery_flag']) && $_POST['delivery']['delivery_flag'] == 0 ) {
			foreach( $_SESSION[WC2]['entry']['customer'] as $key => $value ) {
				if( 'country' == $key && empty($value) ) {
					$_SESSION[WC2]['entry']['delivery'][$key] = wc2_get_base_country();
				} else {
					$_SESSION[WC2]['entry']['delivery'][$key] = $value;
				}
			}
		}

		if( isset($_POST['offer']) ) {
			foreach( $_POST['offer'] as $key => $value ) {
				$_SESSION[WC2]['entry']['order'][$key] = $value;
			}
		}

		if( isset($_SESSION[WC2]['entry']['delivery']['delivery_flag']) && $_SESSION[WC2]['entry']['delivery']['delivery_flag'] == 0 ) {
			self::set_custom_customer_delivery();
		}

/*
		if( isset($_POST[WC2_CUSTOM_ORDER]) ) {
			unset($_SESSION[WC2]['entry'][WC2_CUSTOM_ORDER]);
			foreach( $_POST[WC2_CUSTOM_ORDER] as $key => $value ) {
				if( is_array($value) ) {
					foreach( $value as $k => $v ) {
						$_SESSION[WC2]['entry'][WC2_CUSTOM_ORDER][$key][$v] = $v;
					}
				} else {
					$_SESSION[WC2]['entry'][WC2_CUSTOM_ORDER][$key] = $value;
				}
			}
		}

		if( isset($_POST[WC2_CUSTOM_CUSTOMER]) ) {
			unset($_SESSION[WC2]['entry'][WC2_CUSTOM_CUSTOMER]);
			foreach( $_POST[WC2_CUSTOM_CUSTOMER] as $key => $value ) {
				if( is_array($value) ) {
					foreach( $value as $k => $v ) {
						$_SESSION[WC2]['entry'][WC2_CUSTOM_CUSTOMER][$key][$v] = $v;
					}
				} else {
					$_SESSION[WC2]['entry'][WC2_CUSTOM_CUSTOMER][$key] = $value;
				}
			}
		}

		if( isset($_POST[WC2_CUSTOM_DELIVERY]) ) {
			unset($_SESSION[WC2]['entry'][WC2_CUSTOM_DELIVERY]);
			foreach( $_POST[WC2_CUSTOM_DELIVERY] as $key => $value ) {
				if( is_array($value) ) {
					foreach( $value as $k => $v ) {
						$_SESSION[WC2]['entry'][WC2_CUSTOM_DELIVERY][$key][$v] = $v;
					}
				} else {
					$_SESSION[WC2]['entry'][WC2_CUSTOM_DELIVERY][$key] = $value;
				}
			}
		}
*/

		if( isset($_POST['wcaction']) && 'delivery_process' == $_POST['wcaction'] ){
			//csod
			if( isset( $_SESSION[WC2]['entry'][WC2_CUSTOM_ORDER] ) ){
				unset($_SESSION[WC2]['entry'][WC2_CUSTOM_ORDER]);
			}
			$csod_keys = wc2_get_custom_field_keys(WC2_CSOD);

			if( !empty($csod_keys) && is_array($csod_keys) ){
				foreach($csod_keys as $key){
					list( $pfx, $csod_key ) = explode('_', $key, 2);
					$csod_val = ( isset( $_POST[WC2_CUSTOM_ORDER][$csod_key] ) ) ? $_POST[WC2_CUSTOM_ORDER][$csod_key]: '';
					$_SESSION[WC2]['entry'][WC2_CUSTOM_ORDER][$csod_key] = $csod_val;

				}
			}

			//csde
			if( isset( $_SESSION[WC2]['entry'][WC2_CUSTOM_DELIVERY] ) ){
				unset($_SESSION[WC2]['entry'][WC2_CUSTOM_DELIVERY]);
			}
			$csde_keys = wc2_get_custom_field_keys(WC2_CSDE);
			if( !empty($csde_keys) && is_array($csde_keys) ){
				foreach($csde_keys as $key){
					list( $pfx, $csde_key ) = explode('_', $key, 2);
					$csde_val = ( isset( $_POST[WC2_CUSTOM_DELIVERY][$csde_key] ) ) ? $_POST[WC2_CUSTOM_DELIVERY][$csde_key]: '';
					$_SESSION[WC2]['entry'][WC2_CUSTOM_DELIVERY][$csde_key] = $csde_val;
				}
			}
		}elseif( isset($_POST['wcaction']) && 'customer_process' == $_POST['wcaction'] ) {
			//cscs
			if( isset( $_SESSION[WC2]['entry'][WC2_CUSTOM_CUSTOMER] ) ){
				unset($_SESSION[WC2]['entry'][WC2_CUSTOM_CUSTOMER]);
			}
			$cscs_keys = wc2_get_custom_field_keys(WC2_CSCS);
			if( !empty($cscs_keys) && is_array($cscs_keys) ){
				foreach($cscs_keys as $key){
					list( $pfx, $cscs_key ) = explode('_', $key, 2);
					$cscs_val = ( isset( $_POST[WC2_CUSTOM_CUSTOMER][$cscs_key] ) ) ? $_POST[WC2_CUSTOM_CUSTOMER][$cscs_key]: '';
					$_SESSION[WC2]['entry'][WC2_CUSTOM_CUSTOMER][$cscs_key] = $cscs_val;
				}
			}
		}

		if( ( isset($_SESSION[WC2]['entry']['delivery']['delivery_flag']) && $_SESSION[WC2]['entry']['delivery']['delivery_flag'] == 0 ) || 
			( isset($_POST['delivery']['delivery_flag']) && $_POST['delivery']['delivery_flag'] == 0 ) ) {
			self::set_custom_customer_delivery();
		}

		do_action( 'wc2_action_set_entry' );
	}

	function set_entry_member_regmode($value){
//		$entry['member_regmode'] = ( isset($_SESSION[WC2]['entry']['member_regmode']) ) ? $_SESSION[WC2]['entry']['member_regmode'] : 'none';
		$_SESSION[WC2]['entry']['member_regmode'] = $value;
	}

	function set_custom_customer_delivery() {
		if( isset($_SESSION[WC2]['entry'][WC2_CUSTOM_CUSTOMER]) ) {
			$delivery = array();
			$csde_data = wc2_get_has_custom_field_key( 'delivery' );
			if( !empty($csde_data) and is_array($csde_data) ) {
				foreach( $csde_data as $key ) {
					list( $pfx, $cskey ) = explode( '_', $key, 2 );
					array_push( $delivery, $cskey );
				}
			}
			foreach( $_SESSION[WC2]['entry'][WC2_CUSTOM_CUSTOMER] as $key => $value ) {
				if( in_array($key, $delivery) ) {
					if( empty($_SESSION[WC2]['entry'][WC2_CUSTOM_DELIVERY][$key]) ) {
						if( is_array($value) ) {
							foreach( $value as $k => $v ) {
								$_SESSION[WC2]['entry'][WC2_CUSTOM_DELIVERY][$key][$v] = $v;
							}
						} else {
							$_SESSION[WC2]['entry'][WC2_CUSTOM_DELIVERY][$key] = $value;
						}
					}
				}
			}
		}
	}

	public function get_entry_order_value( $key ) {
		if( array_key_exists( 'entry', $_SESSION[WC2] ) and array_key_exists( 'order', $_SESSION[WC2]['entry'] ) and array_key_exists( $key, $_SESSION[WC2]['entry']['order'] ) ) {
			$value = maybe_unserialize( $_SESSION[WC2]['entry']['order'][$key] );
		} else {
			$value = '';
		}
		return $value;
	}

	public function set_entry_order_value( $key, $value ) {
	//	if( array_key_exists( 'order', $_SESSION[WC2]['entry'] ) ) {
			$_SESSION[WC2]['entry']['order'][$key] = $value;
	//	}
	}

	public function clear_entry($key = '') {
		if( array_key_exists( 'entry', $_SESSION[WC2] ) ) {
			if( empty($key) ){
				unset( $_SESSION[WC2]['entry'] );
			}else{
				if( array_key_exists( $key, $_SESSION[WC2]['entry'] ) ) {
					unset( $_SESSION[WC2]['entry'][$key] );
				}
			}
		}
	}
}

function wc2_set_entry() {
	$wc2_entry = WC2_Entry::get_instance();
	$wc2_entry->set_entry();
}

function wc2_get_entry() {
	$wc2_entry = WC2_Entry::get_instance();
	$entry = $wc2_entry->get_entry();
	return $entry;
}

function wc2_set_entry_member_regmode($value){
	$wc2_entry = WC2_Entry::get_instance();
	$entry = $wc2_entry->set_entry_member_regmode($value);
	return $entry;
}

function wc2_set_entry_order_value( $key, $value ) {
	$wc2_entry = WC2_Entry::get_instance();
	$wc2_entry->set_entry_order_value( $key, $value );
}

function wc2_get_entry_order_value( $key ) {
	$wc2_entry = WC2_Entry::get_instance();
	return $wc2_entry->get_entry_order_value( $key );
}

function wc2_clear_entry($key ='') {
	$wc2_entry = WC2_Entry::get_instance();
	$wc2_entry->clear_entry($key);
}

