<?php

/* Tables */
const TABLE_ORDER = 'wc2_order';
const TABLE_ORDER_META = 'wc2_ordermeta';
const TABLE_ORDER_CART = 'wc2_ordercart';
const TABLE_ORDER_CART_META = 'wc2_ordercartmeta';
const TABLE_ORDER_DELIVERY = 'wc2_orderdelivery';
const TABLE_ORDER_DELIVERY_META = 'wc2_orderdeliverymeta';

/* Columns */
const ORDER_ID = 'ID';
const ORDER_DEC_ID = 'dec_order_id';//注文番号（表示用）
const ORDER_MEMBER_ID = 'member_id';//会員番号
const ORDER_EMAIL = 'email';//メールアドレス
const ORDER_NAME1 = 'name1';//姓
const ORDER_NAME2 = 'name2';//名
const ORDER_NAME3 = 'name3';//姓（フリガナ）
const ORDER_NAME4 = 'name4';//名（フリガナ）
const ORDER_COUNTRY = 'country';//国
const ORDER_ZIPCODE = 'zipcode';//郵便番号
const ORDER_PREF = 'pref';//都道府県
const ORDER_ADDRESS1 = 'address1';//住所１（市区町村番地）
const ORDER_ADDRESS2 = 'address2';//住所２
const ORDER_TEL = 'tel';//電話番号
const ORDER_FAX = 'fax';//FAX番号
const ORDER_NOTE = 'note';//備考
const ORDER_DELIVERY_METHOD = 'delivery_method';//配送方法
const ORDER_DELIVERY_NAME = 'delivery_name';//配送方法名
const ORDER_DELIVERY_DATE = 'delivery_date';//配送日
const ORDER_DELIVERY_TIME = 'delivery_time';//配送時間
const ORDER_DELIDUE_DATE = 'delidue_date';//発送予定日
const ORDER_PAYMENT_METHOD = 'payment_method';//支払方法
const ORDER_PAYMENT_NAME = 'payment_name';//支払方法名
const ORDER_CONDITION = 'order_condition';//状態
const ORDER_ITEM_TOTAL_PRICE = 'item_total_price';//商品合計額
const ORDER_GETPOINT = 'getpoint';//取得ポイント
const ORDER_USEDPOINT = 'usedpoint';//使用ポイント
const ORDER_DISCOUNT = 'discount';//値引
const ORDER_SHIPPING_CHARGE = 'shipping_charge';//送料
const ORDER_COD_FEE = 'cod_fee';//代引手数料
const ORDER_TAX = 'tax';//消費税
const ORDER_DATE = 'order_date';//受注日
const ORDER_MODIFIED = 'order_modified';//更新日
const ORDER_STATUS = 'order_status';//受注ステータス
const RECEIPT_STATUS = 'receipt_status';//入金ステータス
const RECEIPTED_DATE = 'receipted_date';//入金日
const ORDER_TYPE = 'order_type';//受注区分
const ORDER_CHECK = 'order_check';//出力済チェック
const ORDER_MEMO = 'order_memo';//メモ
const ORDER_META_TYPE = 'meta_type';
const ORDER_META_KEY = 'meta_key';

const ORDER_CART = 'cart';
const ORDER_CART_ID = 'cart_id';
const ORDER_CART_ORDER_ID = 'order_id';
const ORDER_CART_GROUP_ID = 'group_id';
const ORDER_CART_ROW_INDEX = 'row_index';
const ORDER_CART_POST_ID = 'post_id';
const ORDER_CART_ITEM_ID = 'item_id';
const ORDER_CART_ITEM_CODE = 'item_code';
const ORDER_CART_ITEM_NAME = 'item_name';
const ORDER_CART_SKU_ID = 'sku_id';
const ORDER_CART_SKU_CODE = 'sku_code';
const ORDER_CART_SKU_NAME = 'sku_name';
const ORDER_CART_PRICE = 'price';
const ORDER_CART_CPRICE = 'cprice';
const ORDER_CART_QUANTITY = 'quantity';
const ORDER_CART_UNIT = 'unit';
const ORDER_CART_TAX = 'tax';
const ORDER_CART_DESTINATION_ID = 'destination_id';
const ORDER_CART_META_TYPE = 'meta_type';
const ORDER_CART_META_KEY = 'meta_key';

const ORDER_DELIVERY = 'delivery';
const ORDER_DELIVERY_ID = 'deli_id';
const ORDER_DELIVERY_ORDER_ID = 'order_id';
const ORDER_DELIVERY_ROW_INDEX = 'row_index';
const ORDER_DELIVERY_NAME1 = 'name1';
const ORDER_DELIVERY_NAME2 = 'name2';
const ORDER_DELIVERY_NAME3 = 'name3';
const ORDER_DELIVERY_NAME4 = 'name4';
const ORDER_DELIVERY_COUNTRY = 'country';
const ORDER_DELIVERY_ZIPCODE = 'zipcode';
const ORDER_DELIVERY_PREF = 'pref';
const ORDER_DELIVERY_ADDRESS1 = 'address1';
const ORDER_DELIVERY_ADDRESS2 = 'address2';
const ORDER_DELIVERY_TEL = 'tel';
const ORDER_DELIVERY_FAX = 'fax';
const ORDER_DELIVERY_META_TYPE = 'meta_type';
const ORDER_DELIVERY_META_KEY = 'meta_key';

class WC2_DB_Order
{
	/* Public variables */

	public $order_table = '';//Order table name
	public $order_meta_table = '';//Order meta table name
	public $order_cart_table = '';//Order cart table name
	public $order_cart_meta_table = '';//Order cart meta table name
	public $order_delivery_table = '';//Order delivery table name
	public $order_delivery_meta_table = '';//Order delivery meta table name
	public $member_table = '';//Member table name

	/* Private variables */

	protected $order_id = '';
	protected $order_data = array();//Order data
	protected $order_list = array();//Order list data

	protected $order_table_column = array();//Order table column
	protected $order_cart_table_column = array();//Order cart table column
	protected $order_delivery_table_column = array();//Order delivery table column

	protected static $instance = null;

	/* Constructor */

	private function __construct() {
		$plugin = Welcart2::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		//Initial processing.
		$this->_set_table();
		$this->_set_item_column_init();
	}

	/* Public functions */

	public static function get_instance() {
		if( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	// Get list values
	public function get_order_list_data( $args ) {
		global $wpdb;

		$select = "SELECT ";
		$column = "ID, dec_order_id, order_date, ";
		if( wc2_is_membersystem_state() ) $column .= "member_id, ";
		$column .= "CONCAT( name1, ' ', name2 ) AS name, pref, delivery_name, ( item_total_price - usedpoint + discount + shipping_charge + cod_fee + {$this->order_table}.tax ) AS total_price, payment_name, receipt_status, order_status, order_modified ";
		$from = "FROM {$this->order_table} ";
		$join = ( isset($args['join']) ) ? $args['join'] : "";
		$group = "";
		$having = "";
		$where = "";
		//if( array_key_exists( 'search_in', $_REQUEST ) && array_key_exists( 'search_column', $_REQUEST ) && array_key_exists( 'search_word', $_REQUEST ) ) {
			if( array_key_exists( 'search_column', $_REQUEST ) && 'none' != $_REQUEST['search_column'] && !empty($_REQUEST['search_word']) ) {
				switch( $_REQUEST['search_column'] ) {
				case 'name':
					$where = "name1 LIKE '%".esc_sql($_REQUEST['search_word']['keyword'])."%' OR name2 LIKE '%".esc_sql($_REQUEST['search_word']['keyword'])."%' ";
					break;
				case 'order_status':
					$where = "order_status = '".esc_sql($_REQUEST['search_word']['order_status'])."' ";
					break;
				case 'receipt_status':
					$where = "receipt_status = '".esc_sql($_REQUEST['search_word']['receipt_status'])."' ";
					break;
				case 'order_type':
					$where = "order_type = '".esc_sql($_REQUEST['search_word']['order_type'])."' ";
					break;
				case 'item_code':
					$where = "{$this->order_cart_table}.item_code LIKE '%".esc_sql($_REQUEST['search_word']['keyword'])."%' ";
					$join .= "INNER JOIN {$this->order_cart_table} ON {$this->order_table}.ID = {$this->order_cart_table}.order_id ";
					break;
				case 'item_name':
					$where = "{$this->order_cart_table}.item_name LIKE '%".esc_sql($_REQUEST['search_word']['keyword'])."%' ";
					$join .= "INNER JOIN {$this->order_cart_table} ON {$this->order_table}.ID = {$this->order_cart_table}.order_id ";
					break;
				case 'order_date':
					if( preg_match("/^[0-9-:]+$/", $_REQUEST['search_word']['keyword']) ) {
						$where = $_REQUEST['search_column']." LIKE '%".esc_sql($_REQUEST['search_word']['keyword'])."%' ";
					} else {
						$where = $_REQUEST['search_column']." LIKE '%x%' ";
					}
					break;
				default:
					$where = $_REQUEST['search_column']." LIKE '%".esc_sql($_REQUEST['search_word']['keyword'])."%' ";
				}
			}
		//}
		if( !empty($args['where']) ) $where = ( $where != "" ) ? $where."AND ".$args['where'] : $args['where'];
		if( $where != "" ) $where = "WHERE ".$where;
		$order = "ORDER BY ".$args['orderby']." ".$args['order'];
		$limit = "";
		$query = $select . $column . $from . $join . $group . $having . $where . $order . $limit;
//wc2_log($query,"test.log");
		$results = $wpdb->get_results( $query, ARRAY_A );
		return $results;
	}

	// Get database values
	public function get_order_data( $order_id = '' ) {
		global $wpdb;

		if( !empty($order_id) ) $this->order_id = $order_id;
		if( empty($this->order_id) ) return array();

		self::clear_column();

		//Order table
		$query = $wpdb->prepare( "SELECT * FROM {$this->order_table} WHERE ID = %d", $this->order_id );
		$data = $wpdb->get_row( $query, ARRAY_A );
		if( $data ) {
			$this->order_data[ORDER_ID] = $data[ORDER_ID];
			$this->order_data[ORDER_DEC_ID] = $data[ORDER_DEC_ID];
			$this->order_data[ORDER_MEMBER_ID] = $data[ORDER_MEMBER_ID];
			$this->order_data[ORDER_EMAIL] = $data[ORDER_EMAIL];
			$this->order_data[ORDER_NAME1] = $data[ORDER_NAME1];
			$this->order_data[ORDER_NAME2] = $data[ORDER_NAME2];
			$this->order_data[ORDER_NAME3] = $data[ORDER_NAME3];
			$this->order_data[ORDER_NAME4] = $data[ORDER_NAME4];
			$this->order_data[ORDER_COUNTRY] = $data[ORDER_COUNTRY];
			$this->order_data[ORDER_ZIPCODE] = $data[ORDER_ZIPCODE];
			$this->order_data[ORDER_PREF] = $data[ORDER_PREF];
			$this->order_data[ORDER_ADDRESS1] = $data[ORDER_ADDRESS1];
			$this->order_data[ORDER_ADDRESS2] = $data[ORDER_ADDRESS2];
			$this->order_data[ORDER_TEL] = $data[ORDER_TEL];
			$this->order_data[ORDER_FAX] = $data[ORDER_FAX];
			$this->order_data[ORDER_NOTE] = $data[ORDER_NOTE];
			$this->order_data[ORDER_DELIVERY_METHOD] = $data[ORDER_DELIVERY_METHOD];
			$this->order_data[ORDER_DELIVERY_NAME] = $data[ORDER_DELIVERY_NAME];
			$this->order_data[ORDER_DELIVERY_DATE] = $data[ORDER_DELIVERY_DATE];
			$this->order_data[ORDER_DELIVERY_TIME] = $data[ORDER_DELIVERY_TIME];
			$this->order_data[ORDER_DELIDUE_DATE] = $data[ORDER_DELIDUE_DATE];
			$this->order_data[ORDER_PAYMENT_METHOD] = $data[ORDER_PAYMENT_METHOD];
			$this->order_data[ORDER_PAYMENT_NAME] = $data[ORDER_PAYMENT_NAME];
			$this->order_data[ORDER_CONDITION] = maybe_unserialize($data[ORDER_CONDITION]);
			$this->order_data[ORDER_ITEM_TOTAL_PRICE] = $data[ORDER_ITEM_TOTAL_PRICE];
			$this->order_data[ORDER_GETPOINT] = $data[ORDER_GETPOINT];
			$this->order_data[ORDER_USEDPOINT] = $data[ORDER_USEDPOINT];
			$this->order_data[ORDER_DISCOUNT] = $data[ORDER_DISCOUNT];
			$this->order_data[ORDER_SHIPPING_CHARGE] = $data[ORDER_SHIPPING_CHARGE];
			$this->order_data[ORDER_COD_FEE] = $data[ORDER_COD_FEE];
			$this->order_data[ORDER_TAX] = $data[ORDER_TAX];
			$this->order_data[ORDER_DATE] = $data[ORDER_DATE];
			$this->order_data[ORDER_MODIFIED] = $data[ORDER_MODIFIED];
			$this->order_data[ORDER_STATUS] = $data[ORDER_STATUS];
			$this->order_data[RECEIPT_STATUS] = $data[RECEIPT_STATUS];
			$this->order_data[RECEIPTED_DATE] = $data[RECEIPTED_DATE];
			$this->order_data[ORDER_TYPE] = $data[ORDER_TYPE];
			$this->order_data[ORDER_CHECK] = maybe_unserialize($data[ORDER_CHECK]);

			//Order meta table
			$query_meta = $wpdb->prepare( "SELECT * FROM {$this->order_meta_table} WHERE order_id = %d", $this->order_id );
			$data_meta = $wpdb->get_results( $query_meta, ARRAY_A );
			foreach( (array)$data_meta as $meta ) {
				if( !empty($meta['meta_type']) ) {
					if( $meta['meta_type'] == WC2_CUSTOM_ORDER or $meta['meta_type'] == WC2_CUSTOM_CUSTOMER ) {
						$this->order_data[$meta['meta_type']][$meta['meta_key']] = maybe_unserialize( $meta['meta_value'] );
					} else {
						$this->order_data['meta_type'][$meta['meta_type']][$meta['meta_key']] = maybe_unserialize( $meta['meta_value'] );
					}
				} else {
					$this->order_data['meta_key'][$meta['meta_key']] = maybe_unserialize( $meta['meta_value'] );
				}
			}

			//Order cart table
			$query_cart = $wpdb->prepare( "SELECT * FROM {$this->order_cart_table} WHERE order_id = %d ORDER BY row_index", $this->order_id );
			$data_cart = $wpdb->get_results( $query_cart, ARRAY_A );
			foreach( (array)$data_cart as $cart ) {
				$idx = $cart[ORDER_CART_ROW_INDEX];
				$this->order_data[ORDER_CART][$idx][ORDER_CART_ID] = $cart[ORDER_CART_ID];
				$this->order_data[ORDER_CART][$idx][ORDER_CART_GROUP_ID] = $cart[ORDER_CART_GROUP_ID];
				$this->order_data[ORDER_CART][$idx][ORDER_CART_ROW_INDEX] = $cart[ORDER_CART_ROW_INDEX];
				$this->order_data[ORDER_CART][$idx][ORDER_CART_POST_ID] = $cart[ORDER_CART_POST_ID];
				$this->order_data[ORDER_CART][$idx][ORDER_CART_ITEM_ID] = $cart[ORDER_CART_ITEM_ID];
				$this->order_data[ORDER_CART][$idx][ORDER_CART_ITEM_CODE] = $cart[ORDER_CART_ITEM_CODE];
				$this->order_data[ORDER_CART][$idx][ORDER_CART_ITEM_NAME] = $cart[ORDER_CART_ITEM_NAME];
				$this->order_data[ORDER_CART][$idx][ORDER_CART_SKU_ID] = $cart[ORDER_CART_SKU_ID];
				$this->order_data[ORDER_CART][$idx][ORDER_CART_SKU_CODE] = $cart[ORDER_CART_SKU_CODE];
				$this->order_data[ORDER_CART][$idx][ORDER_CART_SKU_NAME] = $cart[ORDER_CART_SKU_NAME];
				$this->order_data[ORDER_CART][$idx][ORDER_CART_PRICE] = $cart[ORDER_CART_PRICE];
				$this->order_data[ORDER_CART][$idx][ORDER_CART_CPRICE] = $cart[ORDER_CART_CPRICE];
				$this->order_data[ORDER_CART][$idx][ORDER_CART_QUANTITY] = $cart[ORDER_CART_QUANTITY];
				$this->order_data[ORDER_CART][$idx][ORDER_CART_UNIT] = $cart[ORDER_CART_UNIT];
				$this->order_data[ORDER_CART][$idx][ORDER_CART_TAX] = $cart[ORDER_CART_TAX];
				$this->order_data[ORDER_CART][$idx][ORDER_CART_DESTINATION_ID] = $cart[ORDER_CART_DESTINATION_ID];

				//Order cart meta table
				$query_cart_meta = $wpdb->prepare( "SELECT * FROM {$this->order_cart_meta_table} WHERE cart_id = %d ORDER BY cartmeta_id", $cart[ORDER_CART_ID] );
				$cart_meta = $wpdb->get_results( $query_cart_meta, ARRAY_A );
				foreach( (array)$cart_meta as $meta ) {
					if( !empty($meta['meta_type']) ) {
						$this->order_data[ORDER_CART][$idx]['meta_type'][$meta['meta_type']][$meta['meta_key']] = maybe_unserialize( $meta['meta_value'] );
					} else {
						$this->order_data[ORDER_CART][$idx]['meta_key'][$meta['meta_key']] = maybe_unserialize( $meta['meta_value'] );
					}
				}
			}

			//Order delivery table
			$query_delivery = $wpdb->prepare( "SELECT * FROM {$this->order_delivery_table} WHERE order_id = %d ORDER BY row_index", $this->order_id );
			$data_delivery = $wpdb->get_results( $query_delivery, ARRAY_A );
			foreach( (array)$data_delivery as $delivery ) {
				$idx = $delivery[ORDER_DELIVERY_ROW_INDEX];
				$this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_ID] = $delivery[ORDER_DELIVERY_ID];
				$this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_ROW_INDEX] = $delivery[ORDER_DELIVERY_ROW_INDEX];
				$this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_NAME1] = $delivery[ORDER_DELIVERY_NAME1];
				$this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_NAME2] = $delivery[ORDER_DELIVERY_NAME2];
				$this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_NAME3] = $delivery[ORDER_DELIVERY_NAME3];
				$this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_NAME4] = $delivery[ORDER_DELIVERY_NAME4];
				$this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_COUNTRY] = $delivery[ORDER_DELIVERY_COUNTRY];
				$this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_ZIPCODE] = $delivery[ORDER_DELIVERY_ZIPCODE];
				$this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_PREF] = $delivery[ORDER_DELIVERY_PREF];
				$this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_ADDRESS1] = $delivery[ORDER_DELIVERY_ADDRESS1];
				$this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_ADDRESS2] = $delivery[ORDER_DELIVERY_ADDRESS2];
				$this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_TEL] = $delivery[ORDER_DELIVERY_TEL];
				$this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_FAX] = $delivery[ORDER_DELIVERY_FAX];

				//Order delivery meta table
				$query_delivery_meta = $wpdb->prepare( "SELECT * FROM {$this->order_delivery_meta_table} WHERE deli_id = %d ORDER BY delimeta_id", $delivery[ORDER_DELIVERY_ID] );
				$delivery_meta = $wpdb->get_results( $query_delivery_meta, ARRAY_A );
				foreach( (array)$delivery_meta as $meta ) {
					if( !empty($meta['meta_type']) ) {
						if( $meta['meta_type'] == WC2_CUSTOM_DELIVERY ) {
							$this->order_data[ORDER_DELIVERY][$idx][$meta['meta_type']][$meta['meta_key']] = maybe_unserialize( $meta['meta_value'] );
						} else {
							$this->order_data[ORDER_DELIVERY][$idx]['meta_type'][$meta['meta_type']][$meta['meta_key']] = maybe_unserialize( $meta['meta_value'] );
						}
					} else {
						$this->order_data[ORDER_DELIVERY][$idx]['meta_key'][$meta['meta_key']] = maybe_unserialize( $meta['meta_value'] );
					}
				}
			}
		}

		return $this->order_data;
	}

	// Register database
	public function add_order_data() {
		global $wpdb;

		do_action( 'wc2_action_add_order_data_pre' );

		$order_table_column = self::get_order_table_column();
		array_shift( $order_table_column );
		$order_column = implode( ',', $order_table_column );

		$order_cart_table_column = self::get_order_cart_table_column();
		array_shift( $order_cart_table_column );
		$order_cart_column = implode( ',', $order_cart_table_column );

		$order_delivery_table_column = self::get_order_delivery_table_column();
		array_shift( $order_delivery_table_column );
		$order_delivery_column = implode( ',', $order_delivery_table_column );

		$res = array();
		$i = 0;

		//Order table
		$query = $wpdb->prepare(
			"INSERT INTO {$this->order_table}
				( {$order_column} )
			VALUES 
				( %s, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s, %s, %s, %s, %d, %s, %s, %f, %d, %d, %f, %f, %f, %f, %s, %s, %s, %s, %s, %s, %s )",
				$this->get_the_dec_order_id(), 
				$this->get_the_member_id(), 
				$this->get_the_order_email(), 
				$this->get_the_order_name1(), 
				$this->get_the_order_name2(), 
				$this->get_the_order_name3(), 
				$this->get_the_order_name4(), 
				$this->get_the_order_country(), 
				$this->get_the_order_zipcode(), 
				$this->get_the_order_pref(), 
				$this->get_the_order_address1(), 
				$this->get_the_order_address2(), 
				$this->get_the_order_tel(), 
				$this->get_the_order_fax(), 
				$this->get_the_order_note(), 
				$this->get_the_order_delivery_method(), 
				$this->get_the_order_delivery_name(), 
				$this->get_the_order_delivery_date(), 
				$this->get_the_order_delivery_time(), 
				$this->get_the_order_delidue_date(), 
				$this->get_the_order_payment_method(), 
				$this->get_the_order_payment_name(), 
				$this->get_the_order_condition(), 
				$this->get_the_order_item_total_price(), 
				$this->get_the_order_getpoint(), 
				$this->get_the_order_usedpoint(), 
				$this->get_the_order_discount(), 
				$this->get_the_order_shipping_charge(), 
				$this->get_the_order_cod_fee(), 
				$this->get_the_order_tax(), 
				$this->get_the_order_date(), 
				$this->get_the_order_modified(), 
				$this->get_the_order_status(), 
				$this->get_the_receipt_status(), 
				$this->get_the_receipted_date(), 
				$this->get_the_order_type(), 
				$this->get_the_order_check() 
		);
		$res[$i] = $wpdb->query( $query );
//wc2_log($query,"test.log");

		if( false !== $res[$i] ) {

			//Get internal order_id.
			$this->order_id = $wpdb->insert_id;

			//Order meta table
			if( array_key_exists( WC2_CUSTOM_CUSTOMER, $this->order_data ) ) {
				foreach( (array)$this->order_data[WC2_CUSTOM_CUSTOMER] as $key => $value ) {
					$i++;
					$res[$i] = self::update_order_meta_data( $this->order_id, $key, $value, WC2_CUSTOM_CUSTOMER );
					if( false === $res[$i] ) break;
				}
			}
			if( array_key_exists( WC2_CUSTOM_ORDER, $this->order_data ) ) {
				foreach( (array)$this->order_data[WC2_CUSTOM_ORDER] as $key => $value ) {
					$i++;
					$res[$i] = self::update_order_meta_data( $this->order_id, $key, $value, WC2_CUSTOM_ORDER );
					if( false === $res[$i] ) break;
				}
			}
			if( array_key_exists( ORDER_META_TYPE, $this->order_data ) ) {
				foreach( (array)$this->order_data[ORDER_META_TYPE] as $type => $data ) {
					foreach( (array)$data as $key => $value ) {
						$i++;
						$res[$i] = self::update_order_meta_data( $this->order_id, $key, $value, $type );
						if( false === $res[$i] ) break 2;
					}
				}
			}
			if( array_key_exists( ORDER_META_KEY, $this->order_data ) ) {
				foreach( (array)$this->order_data[ORDER_META_KEY] as $key => $value ) {
					$i++;
					$res[$i] = self::update_order_meta_data( $this->order_id, $key, $value );
					if( false === $res[$i] ) break;
				}
			}

			if( false !== $res[$i] ) {
				foreach( (array)$this->order_data[ORDER_CART] as $idx => $data_cart ) {
					//Order cart table
					$query_cart = $wpdb->prepare(
						"INSERT INTO {$this->order_cart_table} 
							( {$order_cart_column} ) 
						VALUES
							( %d, %d, %d, %d, %d, %s, %s, %d, %s, %s, %f, %f, %f, %s, %f, %d )",
						$this->order_id, 
						$this->get_the_cart_group_id( $idx ), 
						$this->get_the_cart_row_index( $idx ), 
						$this->get_the_cart_post_id( $idx ), 
						$this->get_the_cart_item_id( $idx ), 
						$this->get_the_cart_item_code( $idx ), 
						$this->get_the_cart_item_name( $idx ), 
						$this->get_the_cart_sku_id( $idx ), 
						$this->get_the_cart_sku_code( $idx ), 
						$this->get_the_cart_sku_name( $idx ), 
						$this->get_the_cart_price( $idx ), 
						$this->get_the_cart_cprice( $idx ), 
						$this->get_the_cart_quantity( $idx ), 
						$this->get_the_cart_unit( $idx ), 
						$this->get_the_cart_tax( $idx ), 
						$this->get_the_cart_destination_id( $idx )
					);
					$i++;
					$res[$i] = $wpdb->query( $query_cart );
					if( false !== $res[$i] ) {

						//Get internal cart_id.
						$cart_id = $wpdb->insert_id;

						if( array_key_exists( ORDER_CART_META_TYPE, $data_cart ) ) {
							foreach( (array)$data_cart[ORDER_CART_META_TYPE] as $type => $meta ) {
								foreach( (array)$meta as $key => $value ) {
									$i++;
									$res[$i] = self::update_order_cart_meta_data( $cart_id, $key, $value, $type );
									if( false === $res[$i] ) break 2;
								}
							}
						}
						if( array_key_exists( ORDER_CART_META_KEY, $data_cart ) ) {
							foreach( (array)$data_cart[ORDER_CART_META_KEY] as $key => $value ) {
								$i++;
								$res[$i] = self::update_order_cart_meta_data( $cart_id, $key, $value );
								if( false === $res[$i] ) break;
							}
						}
					}
				}
			}

			if( false !== $res[$i] ) {
				foreach( (array)$this->order_data[ORDER_DELIVERY] as $idx => $data_delivery ) {
					//Order delivery table
					$query_delivery = $wpdb->prepare(
						"INSERT INTO {$this->order_delivery_table} 
							( {$order_delivery_column} ) 
						VALUES
							( %d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s )",
						$this->order_id, 
						$this->get_the_delivery_row_index( $idx ), 
						$this->get_the_delivery_name1( $idx ), 
						$this->get_the_delivery_name2( $idx ), 
						$this->get_the_delivery_name3( $idx ), 
						$this->get_the_delivery_name4( $idx ), 
						$this->get_the_delivery_country( $idx ), 
						$this->get_the_delivery_zipcode( $idx ), 
						$this->get_the_delivery_pref( $idx ), 
						$this->get_the_delivery_address1( $idx ), 
						$this->get_the_delivery_address2( $idx ), 
						$this->get_the_delivery_tel( $idx ), 
						$this->get_the_delivery_fax( $idx )
					);
					$i++;
					$res[$i] = $wpdb->query( $query_delivery );
					if( false !== $res[$i] ) {

						//Get internal delivery_id.
						$delivery_id = $wpdb->insert_id;

						if( array_key_exists( WC2_CUSTOM_DELIVERY, $data_delivery ) ) {
							foreach( (array)$data_delivery[WC2_CUSTOM_DELIVERY] as $key => $value ) {
								$i++;
								$res[$i] = self::update_order_delivery_meta_data( $delivery_id, $key, $value, WC2_CUSTOM_DELIVERY );
								if( false === $res[$i] ) break;
							}
						}
						if( array_key_exists( ORDER_DELIVERY_META_TYPE, $data_delivery ) ) {
							foreach( (array)$data_delivery[ORDER_DELIVERY_META_TYPE] as $type => $meta ) {
								foreach( (array)$meta as $key => $value ) {
									$i++;
									$res[$i] = self::update_order_delivery_meta_data( $delivery_id, $key, $value, $type );
									if( false === $res[$i] ) break 2;
								}
							}
						}
						if( array_key_exists( ORDER_DELIVERY_META_KEY, $data_delivery ) ) {
							foreach( (array)$data_delivery[ORDER_DELIVERY_META_KEY] as $key => $value ) {
								$i++;
								$res[$i] = self::update_order_delivery_meta_data( $delivery_id, $key, $value );
								if( false === $res[$i] ) break;
							}
						}
					}
				}
			}
		}

		do_action( 'wc2_action_add_order_data', $this->order_id, $res );

		if( in_array( false, $res, true ) ) {
			$result = -1;
		} else {
			$result = ( 0 < array_sum($res) ) ? 1 : 0;
		}
		return $result;
	}

	// Update database
	public function update_order_data() {
		global $wpdb;

		if( empty( $this->order_id ) ) return false;

		$res = array();
		$i = 0;

		$query_select = $wpdb->prepare( "SELECT COUNT(*) FROM {$this->order_table} WHERE ID = %d ", $this->order_id );
		if( 1 == $wpdb->get_var( $query_select ) ) {

			do_action( 'wc2_action_update_order_data_pre' );

			//Order table
			$query = $wpdb->prepare(
				"UPDATE {$this->order_table} SET 
					email = %s, name1 = %s, name2 = %s, name3 = %s, name4 = %s, country = %s, zipcode = %s, pref = %s, address1 = %s, address2 = %s, tel = %s, fax = %s, note = %s, 
					delivery_method = %d, delivery_name = %s, delivery_date = %s, delivery_time = %s, delidue_date = %s, payment_method = %d, payment_name = %s, order_condition = %s, 
					item_total_price = %f, getpoint = %d, usedpoint = %d, discount = %f, shipping_charge = %f, cod_fee = %f, tax = %f, order_modified = %s, order_status = %s, receipt_status = %s, receipted_date = %s, order_type = %s, order_check = %s 
				WHERE ID = %d ", 
				$this->get_the_order_email(), 
				$this->get_the_order_name1(), 
				$this->get_the_order_name2(), 
				$this->get_the_order_name3(), 
				$this->get_the_order_name4(), 
				$this->get_the_order_country(), 
				$this->get_the_order_zipcode(), 
				$this->get_the_order_pref(), 
				$this->get_the_order_address1(), 
				$this->get_the_order_address2(), 
				$this->get_the_order_tel(), 
				$this->get_the_order_fax(), 
				$this->get_the_order_note(), 
				$this->get_the_order_delivery_method(), 
				$this->get_the_order_delivery_name(), 
				$this->get_the_order_delivery_date(), 
				$this->get_the_order_delivery_time(), 
				$this->get_the_order_delidue_date(), 
				$this->get_the_order_payment_method(), 
				$this->get_the_order_payment_name(), 
				$this->get_the_order_condition(), 
				$this->get_the_order_item_total_price(), 
				$this->get_the_order_getpoint(), 
				$this->get_the_order_usedpoint(), 
				$this->get_the_order_discount(), 
				$this->get_the_order_shipping_charge(), 
				$this->get_the_order_cod_fee(), 
				$this->get_the_order_tax(), 
				$this->get_the_order_modified(), 
				$this->get_the_order_status(), 
				$this->get_the_receipt_status(), 
				$this->get_the_receipted_date(), 
				$this->get_the_order_type(), 
				$this->get_the_order_check(), 
				$this->order_id 
			);
			$res[$i] = $wpdb->query( $query );
//wc2_log($query,"test.log");

			if( false !== $res[$i] ) {

				//Order meta table
				if( array_key_exists( WC2_CUSTOM_CUSTOMER, $this->order_data ) ) {
					foreach( (array)$this->order_data[WC2_CUSTOM_CUSTOMER] as $key => $value ) {
						$i++;
						$res[$i] = self::update_order_meta_data( $this->order_id, $key, $value, WC2_CUSTOM_CUSTOMER );
						if( false === $res[$i] ) break;
					}
				}
				if( array_key_exists( WC2_CUSTOM_ORDER, $this->order_data ) ) {
					foreach( (array)$this->order_data[WC2_CUSTOM_ORDER] as $key => $value ) {
						$i++;
						$res[$i] = self::update_order_meta_data( $this->order_id, $key, $value, WC2_CUSTOM_ORDER );
						if( false === $res[$i] ) break;
					}
				}
				if( array_key_exists( ORDER_META_TYPE, $this->order_data ) ) {
					foreach( (array)$this->order_data[ORDER_META_TYPE] as $type => $data ) {
						foreach( (array)$data as $key => $value ) {
							$i++;
							$res[$i] = self::update_order_meta_data( $this->order_id, $key, $value, $type );
							if( false === $res[$i] ) break 2;
						}
					}
				}
				if( array_key_exists( ORDER_META_KEY, $this->order_data ) ) {
					foreach( (array)$this->order_data[ORDER_META_KEY] as $key => $value ) {
						$i++;
						$res[$i] = self::update_order_meta_data( $this->order_id, $key, $value );
						if( false === $res[$i] ) break;
					}
				}

				if( false !== $res[$i] ) {
					foreach( (array)$this->order_data[ORDER_CART] as $idx => $data ) {
						$cart_id = $data[ORDER_CART_ID];

						$query_cart_select = $wpdb->prepare( "SELECT COUNT(*) FROM {$this->order_cart_table} WHERE cart_id = %d AND order_id = %d", $cart_id, $this->order_id );
						if( 1 == $wpdb->get_var( $query_cart_select ) ) {

							//Order cart table
							$query_cart = $wpdb->prepare(
								"UPDATE {$this->order_cart_table} SET 
									group_id = %d, row_index = %d, post_id = %d, item_id = %d, item_code = %s, item_name = %s, sku_id = %d, sku_code = %s, sku_name = %s, price = %f, cprice = %f, quantity = %f, unit = %s, tax = %f, destination_id = %d 
								WHERE cart_id = %d AND order_id = %d", 
								$this->get_the_cart_group_id( $idx ), 
								$this->get_the_cart_row_index( $idx ), 
								$this->get_the_cart_post_id( $idx ), 
								$this->get_the_cart_item_id( $idx ), 
								$this->get_the_cart_item_code( $idx ), 
								$this->get_the_cart_item_name( $idx ), 
								$this->get_the_cart_sku_id( $idx ), 
								$this->get_the_cart_sku_code( $idx ), 
								$this->get_the_cart_sku_name( $idx ), 
								$this->get_the_cart_price( $idx ), 
								$this->get_the_cart_cprice( $idx ), 
								$this->get_the_cart_quantity( $idx ), 
								$this->get_the_cart_unit( $idx ), 
								$this->get_the_cart_tax( $idx ), 
								$this->get_the_cart_destination_id( $idx ), 
								$cart_id, 
								$this->order_id 
							);
							$i++;
							$res[$i] = $wpdb->query( $query_cart );
//wc2_log($query_cart,"test.log");
							if( false !== $res[$i] ) {
								if( array_key_exists( ORDER_CART_META_TYPE, $data ) ) {
									foreach( (array)$data[ORDER_CART_META_TYPE] as $type => $meta ) {
										foreach( (array)$meta as $key => $value ) {
											$i++;
											$res[$i] = self::update_order_cart_meta_data( $cart_id, $key, $value, $type );
											if( false === $res[$i] ) break 2;
										}
									}
								}
								if( array_key_exists( ORDER_CART_META_KEY, $data ) ) {
									foreach( (array)$data[ORDER_CART_META_KEY] as $key => $value ) {
										$i++;
										$res[$i] = self::update_order_cart_meta_data( $cart_id, $key, $value );
										if( false === $res[$i] ) break;
									}
								}
							}
						}
					}
				}

				if( false !== $res[$i] ) {
					foreach( (array)$this->order_data[ORDER_DELIVERY] as $idx => $data ) {
						$delivery_id = $data[ORDER_DELIVERY_ID];

						$query_delivery_select = $wpdb->prepare( "SELECT COUNT(*) FROM {$this->order_delivery_table} WHERE deli_id = %d AND order_id = %d", $delivery_id, $this->order_id );
						if( 1 == $wpdb->get_var( $query_delivery_select ) ) {

							//Order delivery table
							$query_delivery = $wpdb->prepare(
								"UPDATE {$this->order_delivery_table} SET 
									row_index = %d, name1 = %s, name2 = %s, name3 = %s, name4 = %s, country = %s, zipcode = %s, pref = %s, address1 = %s, address2 = %s, tel = %s, fax = %s 
								WHERE deli_id = %d AND order_id = %d", 
								$this->get_the_delivery_row_index( $idx ), 
								$this->get_the_delivery_name1( $idx ), 
								$this->get_the_delivery_name2( $idx ), 
								$this->get_the_delivery_name3( $idx ), 
								$this->get_the_delivery_name4( $idx ), 
								$this->get_the_delivery_country( $idx ), 
								$this->get_the_delivery_zipcode( $idx ), 
								$this->get_the_delivery_pref( $idx ), 
								$this->get_the_delivery_address1( $idx ), 
								$this->get_the_delivery_address2( $idx ), 
								$this->get_the_delivery_tel( $idx ), 
								$this->get_the_delivery_fax( $idx ), 
								$delivery_id, 
								$this->order_id 
							);
							$i++;
							$res[$i] = $wpdb->query( $query_delivery );
//wc2_log($query_delivery,"test.log");
							if( false !== $res[$i] ) {
								if( array_key_exists( WC2_CUSTOM_DELIVERY, $data ) ) {
									foreach( (array)$data[WC2_CUSTOM_DELIVERY] as $key => $value ) {
										$i++;
										$res[$i] = self::update_order_delivery_meta_data( $delivery_id, $key, $value, WC2_CUSTOM_DELIVERY );
										if( false === $res[$i] ) break;
									}
								}
								if( array_key_exists( ORDER_DELIVERY_META_TYPE, $data ) ) {
									foreach( (array)$data[ORDER_DELIVERY_META_TYPE] as $type => $meta ) {
										foreach( (array)$meta as $key => $value ) {
											$i++;
											$res[$i] = self::update_order_delivery_meta_data( $delivery_id, $key, $value, $type );
											if( false === $res[$i] ) break 2;
										}
									}
								}
								if( array_key_exists( ORDER_DELIVERY_META_KEY, $data ) ) {
									foreach( (array)$data[ORDER_DELIVERY_META_KEY] as $key => $value ) {
										$i++;
										$res[$i] = self::update_order_delivery_meta_data( $delivery_id, $key, $value );
										if( false === $res[$i] ) break;
									}
								}
							}
						}
					}
				}
			}

			do_action( 'wc2_action_update_order_data', $this->order_id, $res );
		}

		if( in_array( false, $res, true ) ) {
			$result = -1;
		} else {
			$result = ( 0 < array_sum($res) ) ? 1 : 0;
		}
		return $result;
	}

	public function update_order_data_value( $order_id, $update_query ) {
		global $wpdb;
		$query_select = $wpdb->prepare( "SELECT COUNT(*) FROM {$this->order_table} WHERE ID = %d ", $order_id );
		if( 1 == $wpdb->get_var( $query_select ) ) {
			$query = $wpdb->prepare( "UPDATE {$this->order_table} SET {$update_query} WHERE ID = %d ", $order_id );
//wc2_log($query,"test.log");
			$res = $wpdb->query( $query );
		}
		return $res;
	}

	// Register database
	public function add_order_meta_data( $order_id, $key, $value, $type = '' ) {
		global $wpdb;

		$query = $wpdb->prepare( "INSERT INTO {$this->order_meta_table} ( order_id, meta_type, meta_key, meta_value ) VALUES ( %d, %s, %s, %s )",
			$order_id, $type, $key, $value
		);
//wc2_log($query,"test.log");
		$res = $wpdb->query( $query );
		return $res;
	}

	// Update database
	public function update_order_meta_data( $order_id, $key, $value, $type = '' ) {
		global $wpdb;

		if( '' == $type ) {
			$query_select = $wpdb->prepare( "SELECT count( meta_id ) FROM {$this->order_meta_table} WHERE order_id = %d AND meta_key = %s ",
				$order_id, $key
			);
		} else {
			$query_select = $wpdb->prepare( "SELECT count( meta_id ) FROM {$this->order_meta_table} WHERE order_id = %d AND meta_type = %s AND meta_key = %s ",
				$order_id, $type, $key
			);
		}
		if( 0 == $wpdb->get_var( $query_select ) ) {
			$query = $wpdb->prepare( "INSERT INTO {$this->order_meta_table} ( order_id, meta_type, meta_key, meta_value ) VALUES ( %d, %s, %s, %s )",
				$order_id, $type, $key, $value
			);
		} else {
			if( '' == $type ) {
				$query = $wpdb->prepare( "UPDATE {$this->order_meta_table} SET meta_value = %s WHERE order_id = %d AND meta_key = %s ",
					$value, $order_id, $key
				);
			} else {
				$query = $wpdb->prepare( "UPDATE {$this->order_meta_table} SET meta_value = %s WHERE order_id = %d AND meta_type = %s AND meta_key = %s ",
					$value, $order_id, $type, $key
				);
			}
		}
//wc2_log($query,"test.log");

		$res = $wpdb->query( $query );

		return $res;
	}

	public function update_order_cart_meta_data( $cart_id, $key, $value, $type = '' ) {
		global $wpdb;

		if( '' == $type ) {
			$query_select = $wpdb->prepare( "SELECT count( cartmeta_id ) FROM {$this->order_cart_meta_table} WHERE cart_id = %d AND meta_key = %s ",
				$cart_id, $key
			);
		} else {
			$query_select = $wpdb->prepare( "SELECT count( cartmeta_id ) FROM {$this->order_cart_meta_table} WHERE cart_id = %d AND meta_type = %s AND meta_key = %s ",
				$cart_id, $type, $key
			);
		}
		if( 0 == $wpdb->get_var( $query_select ) ) {
			$query = $wpdb->prepare( "INSERT INTO {$this->order_cart_meta_table} ( cart_id, meta_type, meta_key, meta_value ) VALUES ( %d, %s, %s, %s )",
				$cart_id, $type, $key, $value
			);
		} else {
			if( '' == $type ) {
				$query = $wpdb->prepare( "UPDATE {$this->order_cart_meta_table} SET meta_value = %s WHERE cart_id = %d AND meta_key = %s ",
					$value, $cart_id, $key
				);
			} else {
				$query = $wpdb->prepare( "UPDATE {$this->order_cart_meta_table} SET meta_value = %s WHERE cart_id = %d AND meta_type = %s AND meta_key = %s ",
					$value, $cart_id, $type, $key
				);
			}
		}
//wc2_log($query,"test.log");
		$res = $wpdb->query( $query );

		return $res;
	}

	public function update_order_delivery_meta_data( $delivery_id, $key, $value, $type = '' ) {
		global $wpdb;

		if( '' == $type ) {
			$query_select = $wpdb->prepare( "SELECT count( delimeta_id ) FROM {$this->order_delivery_meta_table} WHERE deli_id = %d AND meta_key = %s ",
				$delivery_id, $key
			);
		} else {
			$query_select = $wpdb->prepare( "SELECT count( delimeta_id ) FROM {$this->order_delivery_meta_table} WHERE deli_id = %d AND meta_type = %s AND meta_key = %s ",
				$delivery_id, $type, $key
			);
		}
		if( 0 == $wpdb->get_var( $query_select ) ) {
			$query = $wpdb->prepare( "INSERT INTO {$this->order_delivery_meta_table} ( deli_id, meta_type, meta_key, meta_value ) VALUES ( %d, %s, %s, %s )",
				$delivery_id, $type, $key, $value
			);
		} else {
			if( '' == $type ) {
				$query = $wpdb->prepare( "UPDATE {$this->order_delivery_meta_table} SET meta_value = %s WHERE deli_id = %d AND meta_key = %s ",
					$value, $delivery_id, $key
				);
			} else {
				$query = $wpdb->prepare( "UPDATE {$this->order_delivery_meta_table} SET meta_value = %s WHERE deli_id = %d AND meta_type = %s AND meta_key = %s ",
					$value, $delivery_id, $type, $key
				);
			}
		}
//wc2_log($query,"test.log");
		$res = $wpdb->query( $query );

		return $res;
	}

	// Delete database
	public function delete_order_data( $order_id ) {
		global $wpdb;
		$res = array();
		$i = 0;

		if( empty( $order_id ) ) return false;

		do_action( 'wc2_action_delete_order_data_pre', $order_id );

		$query = $wpdb->prepare( "DELETE FROM {$this->order_table} WHERE ID = %d", $order_id );
		$res[$i] = $wpdb->query( $query );
		if( false !== $res[$i] ) {
			$i++;
			$query_meta = $wpdb->prepare( "DELETE FROM {$this->order_meta_table} WHERE order_id = %d", $order_id );
			$res[$i] = $wpdb->query( $query_meta );
			if( false !== $res[$i] ) {
				$query_cart_select = $wpdb->prepare( "SELECT cart_id FROM {$this->order_cart_table} WHERE order_id = %d", $order_id );
				$cart_data = $wpdb->get_results( $query_cart_select, ARRAY_A );
				foreach( (array)$cart_data as $data ) {
					$i++;
					$query_cart_delete = $wpdb->prepare( "DELETE FROM {$this->order_cart_table} WHERE cart_id = %d", $data['cart_id'] );
					$res[$i] = $wpdb->query( $query_cart_delete );
					if( false !== $res[$i] ) {
						$i++;
						$query_cart_meta_delete = $wpdb->prepare( "DELETE FROM {$this->order_cart_meta_table} WHERE cart_id = %d", $data['cart_id'] );
						$res[$i] = $wpdb->query( $query_cart_meta_delete );
						if( false === $res[$i] ) break;
					}
				}
			}
			if( false !== $res[$i] ) {
				$i++;
				$query_delivery_select = $wpdb->prepare( "SELECT deli_id FROM {$this->order_delivery_table} WHERE order_id = %d", $order_id );
				$delivery_data = $wpdb->get_results( $query_delivery_select, ARRAY_A );
				foreach( (array)$delivery_data as $data ) {
					$i++;
					$query_delivery_delete = $wpdb->prepare( "DELETE FROM {$this->order_delivery_table} WHERE deli_id = %d", $data['deli_id'] );
					$res[$i] = $wpdb->query( $query_delivery_delete );
					if( false !== $res[$i] ) {
						$i++;
						$query_delivery_meta_delete = $wpdb->prepare( "DELETE FROM {$this->order_delivery_meta_table} WHERE deli_id = %d", $data['deli_id'] );
						$res[$i] = $wpdb->query( $query_delivery_meta_delete );
						if( false === $res[$i] ) break;
					}
				}
			}
		}

		do_action( 'wc2_action_delete_order_data', $order_id, $res );

		if( in_array( false, $res, true ) ) {
			$result = -1;
		} else {
			$result = ( 0 < array_sum($res) ) ? array_sum($res) : 0;
		}
		return $result;
	}

	public function point_processing( $order_id, $member_id, $payment_method, $getpoint, $usedpoint, $receipt_status ) {
		global $wpdb;

		$query = '';
		if( wc2_is_complete_settlement( $payment_method, $receipt_status ) ) {
			if( apply_filters( 'wc2_action_acting_getpoint_switch', true, $order_id, true ) ) {
				$query = $wpdb->prepare( "UPDATE {$this->member_table} SET mem_point = ( mem_point + %d - %d ) WHERE ID = %d", 
					$getpoint, $usedpoint, $member_id );
			} else {
				$query = $wpdb->prepare( "UPDATE {$this->member_table} SET mem_point = ( mem_point - %d ) WHERE ID = %d", 
					$usedpoint, $member_id );
			}
		} elseif( 0 < $usedpoint ) {
			$query = $wpdb->prepare( "UPDATE {$this->member_table} SET mem_point = ( mem_point - %d ) WHERE ID = %d", 
				$usedpoint, $member_id );
		}
		if( '' != $query ) {
			$wpdb->query( $query );

			if( array_key_exists( 'point', $_SESSION[WC2]['member'] ) ) {
				$query = $wpdb->prepare( "SELECT mem_point FROM {$this->member_table} WHERE ID = %d", $member_id );
				$point = $wpdb->get_var( $query );
				$_SESSION[WC2]['member']['point'] = $point;
			}
		}
	}

	function acting_get_point( $order_id, $add = true ) {
		global $wpdb;

		if( !apply_filters( 'wc2_action_acting_getpoint_switch', true, $order_id, $add ) )
			return;

		$general = wc2_get_option( 'general' );
		if( $general['point_assign'] != 0 ) {
			if( wc2_is_membersystem_state() && wc2_is_membersystem_point() ) {

				$query = $wpdb->prepare( "SELECT member_id, getpoint FROM {$this->order_table} WHERE ID = %d", $order_id );
				$row = $wpdb->get_row( $query, ARRAY_A );
				$member_id = $row[ORDER_MEMBER_ID];
				$getpoint = $row[ORDER_GETPOINT];

				if( !empty($member_id) && 0 < $getpoint ) {
					$calc = ( $add ) ? '+' : '-';
					$query = $wpdb->prepare( "UPDATE {$this->member_table} SET mem_point = ( mem_point ".$calc." %d ) WHERE ID = %d", $getpoint, $member_id );
					$wpdb->query( $query );

					if( array_key_exists( 'point', $_SESSION[WC2]['member'] ) ) {
						$query = $wpdb->prepare( "SELECT mem_point FROM {$this->member_table} WHERE ID = %d", $member_id );
						$point = $wpdb->get_var( $query );
						$_SESSION[WC2]['member']['point'] = $point;
					}
				}
			}
		}
		do_action( 'wc2_action_acting_getpoint', $order_id, $add );
	}

	function restore_point( $member_id, $point ) {
		global $wpdb;

		if( !apply_filters( 'wc2_action_restore_point_switch', true, $member_id, $point ) )
			return;

		$query = $wpdb->prepare( "UPDATE {$this->member_table} SET mem_point = ( mem_point - %d ) WHERE ID = %d", $point, $member_id );
		$wpdb->query( $query );
	}

	public function get_order_id_by_member_id( $member_id ) {
		global $wpdb;
		$query = $wpdb->prepare( "SELECT ID FROM {$this->order_table} WHERE member_id = %d ORDER BY order_date DESC", $member_id );
		$order_id = $wpdb->get_var( $query );
		return $order_id;
	}

	public function get_order_id_results_by_member_id( $member_id ){
		global $wpdb;
		$query = $wpdb->prepare( "SELECT ID FROM {$this->order_table} WHERE member_id = %d ORDER BY order_date DESC", $member_id );
		$order_ids = $wpdb->get_results( $query, ARRAY_A );
		return $order_ids;
	}

	public function get_order_data_value( $order_id, $key ) {
		global $wpdb;
		$query = $wpdb->prepare( "SELECT {$key} FROM {$this->order_table} WHERE ID = %d LIMIT 1", $order_id );
		$value = $wpdb->get_var( $query );
		return $value;
	}

	public function get_order_meta_value( $order_id, $key, $type = '' ) {
		global $wpdb;
		if( '' == $type ) {
			$query = $wpdb->prepare( "SELECT meta_value FROM {$this->order_meta_table} WHERE order_id = %d AND meta_key = %s",
				$order_id, $key
			);
		} else {
			$query = $wpdb->prepare( "SELECT meta_value FROM {$this->order_meta_table} WHERE order_id = %d AND meta_type = %s AND meta_key = %s",
				$order_id, $type, $key
			);
		}
		$value = $wpdb->get_results( $query, ARRAY_A );
		return $value;
	}

	public function get_order_meta_type( $order_id, $type ) {
		global $wpdb;
		$query = $wpdb->prepare( "SELECT meta_key, meta_value FROM {$this->order_meta_table} WHERE order_id = %d AND meta_type = %s",
			$order_id, $type
		);
		$res = $wpdb->get_results( $query, ARRAY_A );
		return $res;
	}

	public function set_dec_order_id( $order_id, $dec_order_id ) {
		global $wpdb;
		$query = $wpdb->prepare( "UPDATE {$this->order_table} SET dec_order_id = %s WHERE ID = %d", $dec_order_id, $order_id );
		$res = $wpdb->query( $query );
	}

	public function get_order_cart_data( $order_id, $cart_id = 0 ) {
		global $wpdb;
		$data = array();

		//Order cart table
		if( $cart_id != 0 ) {
			$query_cart = $wpdb->prepare( "SELECT * FROM {$this->order_cart_table} WHERE cart_id = %d", $cart_id );
		} else {
			$query_cart = $wpdb->prepare( "SELECT * FROM {$this->order_cart_table} WHERE order_id = %d ORDER BY row_index", $order_id );
		}
		$data_cart = $wpdb->get_results( $query_cart, ARRAY_A );
		foreach( (array)$data_cart as $cart ) {
			$idx = $cart[ORDER_CART_ROW_INDEX];
			$data[$idx][ORDER_CART_ID] = $cart[ORDER_CART_ID];
			$data[$idx][ORDER_CART_GROUP_ID] = $cart[ORDER_CART_GROUP_ID];
			$data[$idx][ORDER_CART_ROW_INDEX] = $cart[ORDER_CART_ROW_INDEX];
			$data[$idx][ORDER_CART_POST_ID] = $cart[ORDER_CART_POST_ID];
			$data[$idx][ORDER_CART_ITEM_ID] = $cart[ORDER_CART_ITEM_ID];
			$data[$idx][ORDER_CART_ITEM_CODE] = $cart[ORDER_CART_ITEM_CODE];
			$data[$idx][ORDER_CART_ITEM_NAME] = $cart[ORDER_CART_ITEM_NAME];
			$data[$idx][ORDER_CART_SKU_ID] = $cart[ORDER_CART_SKU_ID];
			$data[$idx][ORDER_CART_SKU_CODE] = $cart[ORDER_CART_SKU_CODE];
			$data[$idx][ORDER_CART_SKU_NAME] = $cart[ORDER_CART_SKU_NAME];
			$data[$idx][ORDER_CART_PRICE] = $cart[ORDER_CART_PRICE];
			$data[$idx][ORDER_CART_CPRICE] = $cart[ORDER_CART_CPRICE];
			$data[$idx][ORDER_CART_QUANTITY] = $cart[ORDER_CART_QUANTITY];
			$data[$idx][ORDER_CART_UNIT] = $cart[ORDER_CART_UNIT];
			$data[$idx][ORDER_CART_TAX] = $cart[ORDER_CART_TAX];
			$data[$idx][ORDER_CART_DESTINATION_ID] = $cart[ORDER_CART_DESTINATION_ID];

			//Order cart meta table
			$query_cart_meta = $wpdb->prepare( "SELECT * FROM {$this->order_cart_meta_table} WHERE cart_id = %d ORDER BY cartmeta_id", $cart[ORDER_CART_ID] );
			$cart_meta = $wpdb->get_results( $query_cart_meta, ARRAY_A );
			foreach( (array)$cart_meta as $meta ) {
				if( !empty($meta[ORDER_CART_META_TYPE]) ) {
					$data[$idx][ORDER_CART_META_TYPE][$meta[ORDER_CART_META_TYPE]][$meta[ORDER_CART_META_KEY]] = maybe_unserialize( $meta['meta_value'] );
				} else {
					$data[$idx][ORDER_CART_META_KEY][$meta[ORDER_CART_META_KEY]] = maybe_unserialize( $meta['meta_value'] );
				}
			}
		}
		return $data;
	}

	public function add_order_cart_data( $order_id, $add_cart ) {
		global $wpdb;

		$order_cart_table_column = self::get_order_cart_table_column();
		array_shift( $order_cart_table_column );
		$order_cart_column = implode( ',', $order_cart_table_column );

		$res = array();
		$i = 0;

		do_action( 'wc2_action_add_order_cart_data_pre', $order_id, $add_cart );

		//Order cart table
		$query_cart = $wpdb->prepare(
			"INSERT INTO {$this->order_cart_table} 
				( {$order_cart_column} ) 
			VALUES
				( %d, %d, %d, %d, %d, %s, %s, %d, %s, %s, %f, %f, %f, %s, %f, %d )",
			$order_id, 
			$add_cart[ORDER_CART_GROUP_ID], 
			$add_cart[ORDER_CART_ROW_INDEX], 
			$add_cart[ORDER_CART_POST_ID], 
			$add_cart[ORDER_CART_ITEM_ID], 
			$add_cart[ORDER_CART_ITEM_CODE], 
			$add_cart[ORDER_CART_ITEM_NAME], 
			$add_cart[ORDER_CART_SKU_ID], 
			$add_cart[ORDER_CART_SKU_CODE], 
			$add_cart[ORDER_CART_SKU_NAME], 
			$add_cart[ORDER_CART_PRICE], 
			$add_cart[ORDER_CART_CPRICE], 
			$add_cart[ORDER_CART_QUANTITY], 
			$add_cart[ORDER_CART_UNIT], 
			$add_cart[ORDER_CART_TAX], 
			$add_cart[ORDER_CART_DESTINATION_ID] 
		);
//wc2_log($query_cart,"test.log");
		$res[$i] = $wpdb->query( $query_cart );
		if( !$res[$i] )
			return -1;

		//Get internal cart_id.
		$cart_id = $wpdb->insert_id;

		if( array_key_exists( ORDER_CART_META_TYPE, $add_cart ) ) {
			foreach( (array)$add_cart[ORDER_CART_META_TYPE] as $type => $meta ) {
				foreach( (array)$meta as $key => $value ) {
					$i++;
					$res[$i] = self::update_order_cart_meta_data( $cart_id, $key, $value, $type );
					if( false === $res[$i] ) break 2;
				}
			}
		}
		if( array_key_exists( ORDER_CART_META_KEY, $add_cart ) ) {
			foreach( (array)$add_cart[ORDER_CART_META_KEY] as $key => $value ) {
				$i++;
				$res[$i] = self::update_order_cart_meta_data( $cart_id, $key, $value );
				if( false === $res[$i] ) break;
			}
		}

		do_action( 'wc2_action_add_order_cart_data', $order_id, $cart_id, $add_cart, $res );

		if( in_array( false, $res, true ) ) {
			$result = -1;
		} else {
			$result = ( 0 < array_sum($res) ) ? $cart_id : 0;
		}
		return $result;
	}

	public function remove_order_cart_data( $order_id, $cart_id ) {
		global $wpdb;
		$res = array();
		$i = 0;

		do_action( 'wc2_action_remove_order_cart_data_pre', $order_id, $cart_id );

		$query_select = $wpdb->prepare( "SELECT * FROM {$this->order_cart_table} WHERE order_id = %d AND cart_id = %d", $order_id, $cart_id );
		$cart_row = $wpdb->get_row( $query_select, ARRAY_A );
		if( $cart_row ) {
			$query = $wpdb->prepare( "DELETE FROM {$this->order_cart_table} WHERE order_id = %d AND cart_id = %d", $order_id, $cart_id );
			$res[$i] = $wpdb->query( $query );
			if( false !== $res[$i] ) {
				$i++;
				$query_meta = $wpdb->prepare( "DELETE FROM {$this->order_cart_meta_table} WHERE cart_id = %d", $cart_id );
				$res[$i] = $wpdb->query( $query_meta );
			}
		}

		do_action( 'wc2_action_remove_order_cart_data', $order_id, $cart_id, $res );

		if( in_array( false, $res, true ) ) {
			$result = -1;
		} else {
			$result = ( 0 < array_sum($res) ) ? array_sum($res) : 0;
		}
		return $result;
	}

	public function get_member_history( $member_id ) {
		global $wpdb;
		$history = array();

		$query = $wpdb->prepare( "SELECT ID FROM {$this->order_table} WHERE member_id = %d ORDER BY order_date DESC", $member_id );
		$data_order = $wpdb->get_results( $query, ARRAY_A );
		foreach( (array)$data_order as $order_id ) {
			$history[] = self::get_order_data( $order_id );
		}
		return $history;
	}

	public function update_order_check( $order_id, $checked ) {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT order_check FROM {$this->order_table} WHERE ID = %d", $order_id );
		$res = $wpdb->get_var( $query );
		$checkfield = maybe_unserialize( $res );

		if( !in_array( $checked, $checkfield ) ) {
			$checkfield[] = $checked;
			$query = $wpdb->prepare( "UPDATE {$this->order_table} SET order_check = %s WHERE ID = %d", serialize($checkfield), $order_id );
			$res = $wpdb->query( $query );

			if( $res )
				return $checked;
			else
				return false;
		} else {
			return $checked;
		}
	}

	// Create values
	public function clear_column() {
		$this->order_data = array();
	}

	// Set values
	public function set_order_data( $data ) {
		if( is_array($data) ) $this->order_data = $data;
	}
	public function set_order_id( $value ) {
		$this->order_id = $value;
	}
	public function set_value( $key, $value ) {
		if( is_array($value) ) $value = serialize($value);
		$this->order_data[$key] = $value;
	}
	public function set_meta_value( $key, $value, $type = '' ) {
		if( is_array($value) ) $value = serialize($value);
		if( !empty($key) ) {
			if( !empty($type) ) {
				$this->order_data[ORDER_META_TYPE][$type][$key] = $value;
			} else {
				$this->order_data[ORDER_META_KEY][$key] = $value;
			}
		}
	}
	public function set_custom_meta_value( $key, $value, $type ) {
		if( is_array($value) ) $value = serialize($value);
		if( !empty($key) ) {
			$this->order_data[$type][$key] = $value;
		}
	}
	public function set_cart_value( $key, $value, $idx ) {
		if( is_array($value) ) $value = serialize($value);
		if( !empty($key) ) $this->order_data[ORDER_CART][$idx][$key] = $value;
	}
	public function set_cart_meta_value( $key, $value, $idx, $type = '' ) {
		if( is_array($value) ) $value = serialize($value);
		if( !empty($key) ) {
			if( !empty($type) ) {
				$this->order_data[ORDER_CART][$idx][ORDER_CART_META_TYPE][$type][$key] = $value;
			} else {
				$this->order_data[ORDER_CART][$idx][ORDER_CART_META_KEY][$key] = $value;
			}
		}
	}
	public function set_delivery_value( $key, $value, $idx ) {
		if( is_array($value) ) $value = serialize($value);
		if( !empty($key) ) $this->order_data[ORDER_DELIVERY][$idx][$key] = $value;
	}
	public function set_delivery_meta_value( $key, $value, $idx, $type = '' ) {
		if( is_array($value) ) $value = serialize($value);
		if( !empty($key) ) {
			if( !empty($type) ) {
				$this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_META_TYPE][$type][$key] = $value;
			} else {
				$this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_META_KEY][$key] = $value;
			}
		}
	}
	public function set_delivery_custom_meta_value( $key, $value, $idx, $type ) {
		if( is_array($value) ) $value = serialize($value);
		if( !empty($key) ) {
			$this->order_data[ORDER_DELIVERY][$idx][$type][$key] = $value;
		}
	}

	// Get values
	public function get_value( $key ) {
		$value = ( is_array($this->order_data) and array_key_exists( $key, $this->order_data ) ) ? $this->order_data[$key] : '';
		$value = maybe_unserialize($value);
		return $value;
	}
	public function get_cart_value( $key, $idx ) {
		$value = ( is_array($this->order_data[ORDER_CART][$idx]) and array_key_exists( $key, $this->order_data[ORDER_CART][$idx] ) ) ? $this->order_data[ORDER_CART][$idx][$key] : '';
		$value = maybe_unserialize($value);
		return $value;
	}
	public function get_delivery_value( $key, $idx ) {
		$value = ( is_array($this->order_data[ORDER_DELIVERY][$idx]) and array_key_exists( $key, $this->order_data[ORDER_DELIVERY][$idx] ) ) ? $this->order_data[ORDER_DELIVERY][$idx][$key] : '';
		$value = maybe_unserialize($value);
		return $value;
	}
	//Get order value
	public function get_the_order_data() {
		return $this->order_data;
	}
	public function get_the_order_id() {
		return $this->order_id;
	}
	public function get_the_dec_order_id() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_DEC_ID, $this->order_data ) ) ? $this->order_data[ORDER_DEC_ID] : '';
		return $value;
	}
	public function get_the_member_id() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_MEMBER_ID, $this->order_data ) ) ? $this->order_data[ORDER_MEMBER_ID] : '';
		return $value;
	}
	public function get_the_order_email() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_EMAIL, $this->order_data ) ) ? $this->order_data[ORDER_EMAIL] : '';
		return $value;
	}
	public function get_the_order_name1() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_NAME1, $this->order_data ) ) ? $this->order_data[ORDER_NAME1] : '';
		return $value;
	}
	public function get_the_order_name2() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_NAME2, $this->order_data ) ) ? $this->order_data[ORDER_NAME2] : '';
		return $value;
	}
	public function get_the_order_name3() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_NAME3, $this->order_data ) ) ? $this->order_data[ORDER_NAME3] : '';
		return $value;
	}
	public function get_the_order_name4() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_NAME4, $this->order_data ) ) ? $this->order_data[ORDER_NAME4] : '';
		return $value;
	}
	public function get_the_order_country() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_COUNTRY, $this->order_data ) ) ? $this->order_data[ORDER_COUNTRY] : '';
		return $value;
	}
	public function get_the_order_zipcode() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_ZIPCODE, $this->order_data ) ) ? $this->order_data[ORDER_ZIPCODE] : '';
		return $value;
	}
	public function get_the_order_pref() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_PREF, $this->order_data ) ) ? $this->order_data[ORDER_PREF] : '';
		return $value;
	}
	public function get_the_order_address1() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_ADDRESS1, $this->order_data ) ) ? $this->order_data[ORDER_ADDRESS1] : '';
		return $value;
	}
	public function get_the_order_address2() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_ADDRESS2, $this->order_data ) ) ? $this->order_data[ORDER_ADDRESS2] : '';
		return $value;
	}
	public function get_the_order_tel() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_TEL, $this->order_data ) ) ? $this->order_data[ORDER_TEL] : '';
		return $value;
	}
	public function get_the_order_fax() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_FAX, $this->order_data ) ) ? $this->order_data[ORDER_FAX] : '';
		return $value;
	}
	public function get_the_order_note() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_NOTE, $this->order_data ) ) ? $this->order_data[ORDER_NOTE] : '';
		return $value;
	}
	public function get_the_order_delivery_method() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_DELIVERY_METHOD, $this->order_data ) ) ? $this->order_data[ORDER_DELIVERY_METHOD] : -1;
		return $value;
	}
	public function get_the_order_delivery_name() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_DELIVERY_NAME, $this->order_data ) ) ? $this->order_data[ORDER_DELIVERY_NAME] : '';
		return $value;
	}
	public function get_the_order_delivery_date() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_DELIVERY_DATE, $this->order_data ) ) ? $this->order_data[ORDER_DELIVERY_DATE] : '';
		return $value;
	}
	public function get_the_order_delivery_time() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_DELIVERY_TIME, $this->order_data ) ) ? $this->order_data[ORDER_DELIVERY_TIME] : '';
		return $value;
	}
	public function get_the_order_delidue_date() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_DELIDUE_DATE, $this->order_data ) ) ? $this->order_data[ORDER_DELIDUE_DATE] : '';
		return $value;
	}
	public function get_the_order_payment_method() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_PAYMENT_METHOD, $this->order_data ) ) ? $this->order_data[ORDER_PAYMENT_METHOD] : -1;
		return $value;
	}
	public function get_the_order_payment_name() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_PAYMENT_NAME, $this->order_data ) ) ? $this->order_data[ORDER_PAYMENT_NAME] : '';
		return $value;
	}
	public function get_the_order_condition() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_CONDITION, $this->order_data ) ) ? $this->order_data[ORDER_CONDITION] : array();
		return $value;
	}
	public function get_the_order_item_total_price() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_ITEM_TOTAL_PRICE, $this->order_data ) ) ? $this->order_data[ORDER_ITEM_TOTAL_PRICE] : 0;
		return $value;
	}
	public function get_the_order_getpoint() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_GETPOINT, $this->order_data ) ) ? $this->order_data[ORDER_GETPOINT] : 0;
		return $value;
	}
	public function get_the_order_usedpoint() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_USEDPOINT, $this->order_data ) ) ? $this->order_data[ORDER_USEDPOINT] : 0;
		return $value;
	}
	public function get_the_order_discount() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_DISCOUNT, $this->order_data ) ) ? $this->order_data[ORDER_DISCOUNT] : 0;
		return $value;
	}
	public function get_the_order_shipping_charge() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_SHIPPING_CHARGE, $this->order_data ) ) ? $this->order_data[ORDER_SHIPPING_CHARGE] : 0;
		return $value;
	}
	public function get_the_order_cod_fee() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_COD_FEE, $this->order_data ) ) ? $this->order_data[ORDER_COD_FEE] : 0;
		return $value;
	}
	public function get_the_order_tax() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_TAX, $this->order_data ) ) ? $this->order_data[ORDER_TAX] : 0;
		return $value;
	}
	public function get_the_order_date() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_DATE, $this->order_data ) ) ? $this->order_data[ORDER_DATE] : '0000-00-00 00:00:00';
		return $value;
	}
	public function get_the_order_modified() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_MODIFIED, $this->order_data ) ) ? $this->order_data[ORDER_MODIFIED] : '';
		return $value;
	}
	public function get_the_order_status() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_STATUS, $this->order_data ) ) ? $this->order_data[ORDER_STATUS] : '';
		return $value;
	}
	public function get_the_receipt_status() {
		$value = ( is_array($this->order_data) and array_key_exists( RECEIPT_STATUS, $this->order_data ) ) ? $this->order_data[RECEIPT_STATUS] : '';
		return $value;
	}
	public function get_the_receipted_date() {
		$value = ( is_array($this->order_data) and array_key_exists( RECEIPTED_DATE, $this->order_data ) ) ? $this->order_data[RECEIPTED_DATE] : '';
		return $value;
	}
	public function get_the_order_type() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_TYPE, $this->order_data ) ) ? $this->order_data[ORDER_TYPE] : '';
		return $value;
	}
	public function get_the_order_check() {
		$value = ( is_array($this->order_data) and array_key_exists( ORDER_CHECK, $this->order_data ) ) ? $this->order_data[ORDER_CHECK] : array();
		return $value;
	}
	//Get order cart value
	public function get_the_cart_group_id( $idx ) {
		$value = ( is_array($this->order_data[ORDER_CART][$idx]) and array_key_exists( ORDER_CART_GROUP_ID, $this->order_data[ORDER_CART][$idx] ) ) ? $this->order_data[ORDER_CART][$idx][ORDER_CART_GROUP_ID] : 0;
		return $value;
	}
	public function get_the_cart_row_index( $idx ) {
		$value = ( is_array($this->order_data[ORDER_CART][$idx]) and array_key_exists( ORDER_CART_ROW_INDEX, $this->order_data[ORDER_CART][$idx] ) ) ? $this->order_data[ORDER_CART][$idx][ORDER_CART_ROW_INDEX] : 0;
		return $value;
	}
	public function get_the_cart_post_id( $idx ) {
		$value = ( is_array($this->order_data[ORDER_CART][$idx]) and array_key_exists( ORDER_CART_POST_ID, $this->order_data[ORDER_CART][$idx] ) ) ? $this->order_data[ORDER_CART][$idx][ORDER_CART_POST_ID] : 0;
		return $value;
	}
	public function get_the_cart_item_id( $idx ) {
		$value = ( is_array($this->order_data[ORDER_CART][$idx]) and array_key_exists( ORDER_CART_ITEM_ID, $this->order_data[ORDER_CART][$idx] ) ) ? $this->order_data[ORDER_CART][$idx][ORDER_CART_ITEM_ID] : 0;
		return $value;
	}
	public function get_the_cart_item_code( $idx ) {
		$value = ( is_array($this->order_data[ORDER_CART][$idx]) and array_key_exists( ORDER_CART_ITEM_CODE, $this->order_data[ORDER_CART][$idx] ) ) ? $this->order_data[ORDER_CART][$idx][ORDER_CART_ITEM_CODE] : '';
		return $value;
	}
	public function get_the_cart_item_name( $idx ) {
		$value = ( is_array($this->order_data[ORDER_CART][$idx]) and array_key_exists( ORDER_CART_ITEM_NAME, $this->order_data[ORDER_CART][$idx] ) ) ? $this->order_data[ORDER_CART][$idx][ORDER_CART_ITEM_NAME] : '';
		return $value;
	}
	public function get_the_cart_sku_id( $idx ) {
		$value = ( is_array($this->order_data[ORDER_CART][$idx]) and array_key_exists( ORDER_CART_SKU_ID, $this->order_data[ORDER_CART][$idx] ) ) ? $this->order_data[ORDER_CART][$idx][ORDER_CART_SKU_ID] : 0;
		return $value;
	}
	public function get_the_cart_sku_code( $idx ) {
		$value = ( is_array($this->order_data[ORDER_CART][$idx]) and array_key_exists( ORDER_CART_SKU_CODE, $this->order_data[ORDER_CART][$idx] ) ) ? $this->order_data[ORDER_CART][$idx][ORDER_CART_SKU_CODE] : '';
		return $value;
	}
	public function get_the_cart_sku_name( $idx ) {
		$value = ( is_array($this->order_data[ORDER_CART][$idx]) and array_key_exists( ORDER_CART_SKU_NAME, $this->order_data[ORDER_CART][$idx] ) ) ? $this->order_data[ORDER_CART][$idx][ORDER_CART_SKU_NAME] : '';
		return $value;
	}
	public function get_the_cart_price( $idx ) {
		$value = ( is_array($this->order_data[ORDER_CART][$idx]) and array_key_exists( ORDER_CART_PRICE, $this->order_data[ORDER_CART][$idx] ) ) ? $this->order_data[ORDER_CART][$idx][ORDER_CART_PRICE] : 0;
		return $value;
	}
	public function get_the_cart_cprice( $idx ) {
		$value = ( is_array($this->order_data[ORDER_CART][$idx]) and array_key_exists( ORDER_CART_CPRICE, $this->order_data[ORDER_CART][$idx] ) ) ? $this->order_data[ORDER_CART][$idx][ORDER_CART_CPRICE] : 0;
		return $value;
	}
	public function get_the_cart_quantity( $idx ) {
		$value = ( is_array($this->order_data[ORDER_CART][$idx]) and array_key_exists( ORDER_CART_QUANTITY, $this->order_data[ORDER_CART][$idx] ) ) ? $this->order_data[ORDER_CART][$idx][ORDER_CART_QUANTITY] : 0;
		return $value;
	}
	public function get_the_cart_unit( $idx ) {
		$value = ( is_array($this->order_data[ORDER_CART][$idx]) and array_key_exists( ORDER_CART_UNIT, $this->order_data[ORDER_CART][$idx] ) ) ? $this->order_data[ORDER_CART][$idx][ORDER_CART_UNIT] : '';
		return $value;
	}
	public function get_the_cart_tax( $idx ) {
		$value = ( is_array($this->order_data[ORDER_CART][$idx]) and array_key_exists( ORDER_CART_TAX, $this->order_data[ORDER_CART][$idx] ) ) ? $this->order_data[ORDER_CART][$idx][ORDER_CART_TAX] : 0;
		return $value;
	}
	public function get_the_cart_destination_id( $idx ) {
		$value = ( is_array($this->order_data[ORDER_CART][$idx]) and array_key_exists( ORDER_CART_DESTINATION_ID, $this->order_data[ORDER_CART][$idx] ) ) ? $this->order_data[ORDER_CART][$idx][ORDER_CART_DESTINATION_ID] : 0;
		return $value;
	}
	// Get delivery value
	public function get_the_delivery_row_index( $idx ) {
		$value = ( is_array($this->order_data[ORDER_DELIVERY][$idx]) and array_key_exists( ORDER_DELIVERY_ROW_INDEX, $this->order_data[ORDER_DELIVERY][$idx] ) ) ? $this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_ROW_INDEX] : 0;
		return $value;
	}
	public function get_the_delivery_name1( $idx ) {
		$value = ( is_array($this->order_data[ORDER_DELIVERY][$idx]) and array_key_exists( ORDER_DELIVERY_NAME1, $this->order_data[ORDER_DELIVERY][$idx] ) ) ? $this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_NAME1] : '';
		return $value;
	}
	public function get_the_delivery_name2( $idx ) {
		$value = ( is_array($this->order_data[ORDER_DELIVERY][$idx]) and array_key_exists( ORDER_DELIVERY_NAME2, $this->order_data[ORDER_DELIVERY][$idx] ) ) ? $this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_NAME2] : '';
		return $value;
	}
	public function get_the_delivery_name3( $idx ) {
		$value = ( is_array($this->order_data[ORDER_DELIVERY][$idx]) and array_key_exists( ORDER_DELIVERY_NAME3, $this->order_data[ORDER_DELIVERY][$idx] ) ) ? $this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_NAME3] : '';
		return $value;
	}
	public function get_the_delivery_name4( $idx ) {
		$value = ( is_array($this->order_data[ORDER_DELIVERY][$idx]) and array_key_exists( ORDER_DELIVERY_NAME4, $this->order_data[ORDER_DELIVERY][$idx] ) ) ? $this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_NAME4] : '';
		return $value;
	}
	public function get_the_delivery_country( $idx ) {
		$value = ( is_array($this->order_data[ORDER_DELIVERY][$idx]) and array_key_exists( ORDER_DELIVERY_COUNTRY, $this->order_data[ORDER_DELIVERY][$idx] ) ) ? $this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_COUNTRY] : '';
		return $value;
	}
	public function get_the_delivery_zipcode( $idx ) {
		$value = ( is_array($this->order_data[ORDER_DELIVERY][$idx]) and array_key_exists( ORDER_DELIVERY_ZIPCODE, $this->order_data[ORDER_DELIVERY][$idx] ) ) ? $this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_ZIPCODE] : '';
		return $value;
	}
	public function get_the_delivery_pref( $idx ) {
		$value = ( is_array($this->order_data[ORDER_DELIVERY][$idx]) and array_key_exists( ORDER_DELIVERY_PREF, $this->order_data[ORDER_DELIVERY][$idx] ) ) ? $this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_PREF] : '';
		return $value;
	}
	public function get_the_delivery_address1( $idx ) {
		$value = ( is_array($this->order_data[ORDER_DELIVERY][$idx]) and array_key_exists( ORDER_DELIVERY_ADDRESS1, $this->order_data[ORDER_DELIVERY][$idx] ) ) ? $this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_ADDRESS1] : '';
		return $value;
	}
	public function get_the_delivery_address2( $idx ) {
		$value = ( is_array($this->order_data[ORDER_DELIVERY][$idx]) and array_key_exists( ORDER_DELIVERY_ADDRESS2, $this->order_data[ORDER_DELIVERY][$idx] ) ) ? $this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_ADDRESS2] : '';
		return $value;
	}
	public function get_the_delivery_tel( $idx ) {
		$value = ( is_array($this->order_data[ORDER_DELIVERY][$idx]) and array_key_exists( ORDER_DELIVERY_TEL, $this->order_data[ORDER_DELIVERY][$idx] ) ) ? $this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_TEL] : '';
		return $value;
	}
	public function get_the_delivery_fax( $idx ) {
		$value = ( is_array($this->order_data[ORDER_DELIVERY][$idx]) and array_key_exists( ORDER_DELIVERY_FAX, $this->order_data[ORDER_DELIVERY][$idx] ) ) ? $this->order_data[ORDER_DELIVERY][$idx][ORDER_DELIVERY_FAX] : '';
		return $value;
	}

	public function get_order_table_column() {
		return $this->order_table_column;
	}

	public function get_order_cart_table_column() {
		return $this->order_cart_table_column;
	}

	public function get_order_delivery_table_column() {
		return $this->order_delivery_table_column;
	}

	public function is_order_table_column( $column ) {
		return in_array( $column, $this->get_order_table_column() );
	}

	public function is_order_cart_table_column( $column ) {
		return in_array( $column, $this->get_order_table_column() );
	}

	public function is_order_delivery_table_column( $column ) {
		return in_array( $column, $this->get_order_delivery_table_column() );
	}

	/* Private functions */

	//Initial table name
	private function _set_table() {
		global $wpdb;
		$this->order_table = $wpdb->prefix.TABLE_ORDER;
		$this->order_meta_table = $wpdb->prefix.TABLE_ORDER_META;
		$this->order_cart_table = $wpdb->prefix.TABLE_ORDER_CART;
		$this->order_cart_meta_table = $wpdb->prefix.TABLE_ORDER_CART_META;
		$this->order_delivery_table = $wpdb->prefix.TABLE_ORDER_DELIVERY;
		$this->order_delivery_meta_table = $wpdb->prefix.TABLE_ORDER_DELIVERY_META;
		$this->member_table = $wpdb->prefix.TABLE_MEMBER;
	}

	//Initial column
	private function _set_item_column_init() {

		//*** Not be changed.
		$this->order_table_column = array( ORDER_ID, ORDER_DEC_ID, ORDER_MEMBER_ID, ORDER_EMAIL, ORDER_NAME1, ORDER_NAME2, ORDER_NAME3, ORDER_NAME4, ORDER_COUNTRY, ORDER_ZIPCODE, ORDER_PREF, ORDER_ADDRESS1, ORDER_ADDRESS2, ORDER_TEL, ORDER_FAX, ORDER_NOTE, ORDER_DELIVERY_METHOD, ORDER_DELIVERY_NAME, ORDER_DELIVERY_DATE, ORDER_DELIVERY_TIME, ORDER_DELIDUE_DATE, ORDER_PAYMENT_METHOD, ORDER_PAYMENT_NAME, ORDER_CONDITION, ORDER_ITEM_TOTAL_PRICE, ORDER_GETPOINT, ORDER_USEDPOINT, ORDER_DISCOUNT, ORDER_SHIPPING_CHARGE, ORDER_COD_FEE, ORDER_TAX, ORDER_DATE, ORDER_MODIFIED, ORDER_STATUS, RECEIPT_STATUS, RECEIPTED_DATE, ORDER_TYPE, ORDER_CHECK );
		$this->order_cart_table_column = array( ORDER_CART_ID, ORDER_CART_ORDER_ID, ORDER_CART_GROUP_ID, ORDER_CART_ROW_INDEX, ORDER_CART_POST_ID, ORDER_CART_ITEM_ID, ORDER_CART_ITEM_CODE, ORDER_CART_ITEM_NAME, ORDER_CART_SKU_ID, ORDER_CART_SKU_CODE, ORDER_CART_SKU_NAME, ORDER_CART_PRICE, ORDER_CART_CPRICE, ORDER_CART_QUANTITY, ORDER_CART_UNIT, ORDER_CART_TAX, ORDER_CART_DESTINATION_ID );
		$this->order_delivery_table_column = array( ORDER_DELIVERY_ID, ORDER_DELIVERY_ORDER_ID, ORDER_DELIVERY_ROW_INDEX, ORDER_DELIVERY_NAME1, ORDER_DELIVERY_NAME2, ORDER_DELIVERY_NAME3, ORDER_DELIVERY_NAME4, ORDER_DELIVERY_COUNTRY, ORDER_DELIVERY_ZIPCODE, ORDER_DELIVERY_PREF, ORDER_DELIVERY_ADDRESS1, ORDER_DELIVERY_ADDRESS2, ORDER_DELIVERY_TEL, ORDER_DELIVERY_FAX );
	}
}

function wc2_remove_order( $order_id ) {
	$wc2_order = WC2_DB_Order::get_instance();
	$res = $wc2_order->delete_order_data($order_id);
	return $res;
}

function wc2_get_order_data( $order_id ) {
	$wc2_order = WC2_DB_Order::get_instance();
	return $wc2_order->get_order_data( $order_id );
}

function wc2_get_the_order_data() {
	$wc2_order = WC2_DB_Order::get_instance();
	return $wc2_order->get_the_order_data();
}

function wc2_get_order_data_value( $order_id, $key ) {
	$wc2_order = WC2_DB_Order::get_instance();
	return $wc2_order->get_order_data_value( $order_id, $key );
}

function wc2_get_order_meta_value( $order_id, $key, $type = '' ) {
	$wc2_order = WC2_DB_Order::get_instance();
	return $wc2_order->get_order_meta_value( $order_id, $key, $type );
}

function wc2_get_order_meta_type( $order_id, $type ) {
	$wc2_order = WC2_DB_Order::get_instance();
	return $wc2_order->get_order_meta_type( $order_id, $type );
}

function wc2_get_order_cart_data( $order_id, $cart_id = 0 ) {
	$wc2_order = WC2_DB_Order::get_instance();
	$cart = $wc2_order->get_order_cart_data( $order_id, $cart_id );
	return $cart;
}

function wc2_add_order_cart_data( $order_id, $cart ) {
	$wc2_order = WC2_DB_Order::get_instance();
	$cart = $wc2_order->add_order_cart_data( $order_id, $cart );
	return $cart;
}

function wc2_remove_order_cart_data( $order_id, $cart_id ) {
	$wc2_order = WC2_DB_Order::get_instance();
	$res = $wc2_order->remove_order_cart_data( $order_id, $cart_id  );
	return $res;
}

function wc2_get_member_history( $member_id ) {
	$wc2_order = WC2_DB_Order::get_instance();
	$history = $wc2_order->get_member_history( $member_id );
	return $history;
}

function wc2_update_order_data_value( $order_id, $update_query ) {
	$wc2_order = WC2_DB_Order::get_instance();
	$res = $wc2_order->update_order_data_value( $order_id, $update_query );
	return $res;
}

function wc2_add_order_meta_data( $order_id, $key, $value, $type = '' ) {
	$wc2_order = WC2_DB_Order::get_instance();
	$res = $wc2_order->add_order_meta_data( $order_id, $key, $value, $type );
	return $res;
}

function wc2_update_order_meta_data( $order_id, $key, $value, $type = '' ) {
	$wc2_order = WC2_DB_Order::get_instance();
	$res = $wc2_order->update_order_meta_data( $order_id, $key, $value, $type );
	return $res;
}

function wc2_register_order_data() {
	$wc2_order = WC2_DB_Order::get_instance();
	$cart = wc2_get_cart();
	$entry_data = wc2_get_entry();

	if( empty($cart) ) {
		wc2_set_log( 'Session is empty.', 'register_orderdata' );
		return false;
	}
	if( empty($entry_data['customer']['name1']) || empty($entry_data['customer']['email']) || empty($entry_data) || empty($cart) ) {
		wc2_set_log( 'Customer data is empty.', 'register_orderdata' );
		return false;
	}

	$general_options = wc2_get_option( 'general' );
	$payment = wc2_get_payment( $entry_data['order']['payment_method'] );
	foreach( $cart as $row ) {
		$charges_type = wc2_get_item_charges_type( $row['item_id'] );
		if( !empty($charges_type) ) break;
	}

	$args = array(
		'cart' => $cart,
		'entry_data' => $entry_data,
		'member_id' => $entry_data['member_id'],
		'payment' => $payment,
		'charges_type' => $charges_type
	);

	$order_date = wc2_get_today_datetime_format();
	$order_status = 'new';
	$receipt_status = '';
	$receipted_date = '';
	if( 'continue' == $charges_type ) {
		$order_modified = $order_date;
	} else {
		$noreceipt_status_table = wc2_get_option( 'noreceipt_status' );
		if( in_array( $payment['settlement'], $noreceipt_status_table ) ) $receipt_status = 'unpaid';
		$order_modified = '';
	}
	$order_status = apply_filters( 'wc2_filter_register_orderdata_status', $order_status, $args );
	$receipt_status = apply_filters( 'wc2_filter_register_orderdata_receipt_status', $receipt_status, $args );
	if( !preg_match( '/pending|unpaid/', $receipt_status ) ) {
		$receipted_date = $order_date;
	}
	$order_type = '';
	$order_check = array();

	do_action( 'wc2_action_register_order_data_pre', $args );

	$data = array();

	$data[ORDER_MEMBER_ID] = ( isset($entry_data['member_id']) ) ? $entry_data['member_id'] : '';

	$data[ORDER_EMAIL] = ( isset($entry_data['customer']['email']) ) ? $entry_data['customer']['email'] : '';
	$data[ORDER_NAME1] = ( isset($entry_data['customer']['name1']) ) ? $entry_data['customer']['name1'] : '';
	$data[ORDER_NAME2] = ( isset($entry_data['customer']['name2']) ) ? $entry_data['customer']['name2'] : '';
	$data[ORDER_NAME3] = ( isset($entry_data['customer']['name3']) ) ? $entry_data['customer']['name3'] : '';
	$data[ORDER_NAME4] = ( isset($entry_data['customer']['name4']) ) ? $entry_data['customer']['name4'] : '';
	$data[ORDER_COUNTRY] = ( isset($entry_data['customer']['country']) ) ? $entry_data['customer']['country'] : '';
	$data[ORDER_ZIPCODE] = ( isset($entry_data['customer']['zipcode']) ) ? $entry_data['customer']['zipcode'] : '';
	$data[ORDER_PREF] = ( isset($entry_data['customer']['pref']) ) ? $entry_data['customer']['pref'] : '';
	$data[ORDER_ADDRESS1] = ( isset($entry_data['customer']['address1']) ) ? $entry_data['customer']['address1'] : '';
	$data[ORDER_ADDRESS2] = ( isset($entry_data['customer']['address2']) ) ? $entry_data['customer']['address2'] : '';
	$data[ORDER_TEL] = ( isset($entry_data['customer']['tel']) ) ? $entry_data['customer']['tel'] : '';
	$data[ORDER_FAX] = ( isset($entry_data['customer']['fax']) ) ? $entry_data['customer']['fax'] : '';

	$data[ORDER_NOTE] = ( isset($entry_data['order']['note']) ) ? $entry_data['order']['note'] : '';
	$data[ORDER_DELIVERY_METHOD] = ( isset($entry_data['order']['delivery_method']) ) ? $entry_data['order']['delivery_method'] : -1;
	$data[ORDER_DELIVERY_NAME] = ( isset($entry_data['order']['delivery_name']) ) ? $entry_data['order']['delivery_name'] : '';
	$data[ORDER_DELIVERY_DATE] = ( isset($entry_data['order']['delivery_date']) ) ? $entry_data['order']['delivery_date'] : '';
	$data[ORDER_DELIVERY_TIME] = ( isset($entry_data['order']['delivery_time']) ) ? $entry_data['order']['delivery_time'] : '';
	$data[ORDER_DELIDUE_DATE] = ( isset($entry_data['order']['delidue_date']) ) ? $entry_data['order']['delidue_date'] : '';

	$data[ORDER_PAYMENT_METHOD] = ( isset($entry_data['order']['payment_method']) ) ? $entry_data['order']['payment_method'] : -1;
	$data[ORDER_PAYMENT_NAME] = ( isset($entry_data['order']['payment_name']) ) ? $entry_data['order']['payment_name'] : '';

	$data[ORDER_CONDITION] = serialize(wc2_get_condition());

	$data[ORDER_ITEM_TOTAL_PRICE] = ( isset($entry_data['order']['item_total_price']) ) ? $entry_data['order']['item_total_price'] : 0;
	$data[ORDER_GETPOINT] = ( isset($entry_data['order']['getpoint']) ) ? $entry_data['order']['getpoint'] : 0;
	$data[ORDER_USEDPOINT] = ( isset($entry_data['order']['usedpoint']) ) ? $entry_data['order']['usedpoint'] : 0;
	$data[ORDER_DISCOUNT] = ( isset($entry_data['order']['discount']) ) ? $entry_data['order']['discount'] : 0;
	$data[ORDER_SHIPPING_CHARGE] = ( isset($entry_data['order']['shipping_charge']) ) ? $entry_data['order']['shipping_charge'] : 0;
	$data[ORDER_COD_FEE] = ( isset($entry_data['order']['cod_fee']) ) ? $entry_data['order']['cod_fee'] : 0;
	$data[ORDER_TAX] = ( isset($entry_data['order']['tax']) ) ? $entry_data['order']['tax'] : 0;

	$data[ORDER_DATE] = $order_date;
	$data[ORDER_MODIFIED] = $order_modified;
	$data[ORDER_STATUS] = $order_status;
	$data[RECEIPT_STATUS] = $receipt_status;
	$data[RECEIPTED_DATE] = $receipted_date;
	$data[ORDER_TYPE] = $order_type;
	$data[ORDER_CHECK] = serialize($order_check);

	$cscs_keys = wc2_get_custom_field_keys(WC2_CSCS);
	if( !empty($cscs_keys) && is_array($cscs_keys) ) {
		foreach( $cscs_keys as $key ) {
			list( $pfx, $cscs_key ) = explode('_', $key, 2);
			if( array_key_exists(WC2_CUSTOM_CUSTOMER, $entry_data['customer']) and array_key_exists($cscs_key, $entry_data['customer'][WC2_CUSTOM_CUSTOMER]) ) {
				if( is_array($entry_data['customer'][WC2_CUSTOM_CUSTOMER][$cscs_key]) ) {
					$data[WC2_CUSTOM_CUSTOMER][$cscs_key] = serialize($entry_data['customer'][WC2_CUSTOM_CUSTOMER][$cscs_key]);
				} else {
					$data[WC2_CUSTOM_CUSTOMER][$cscs_key] = $entry_data['customer'][WC2_CUSTOM_CUSTOMER][$cscs_key];
				}
			} else {
				$data[WC2_CUSTOM_CUSTOMER][$cscs_key] = '';
			}
		}
	}

	$csod_keys = wc2_get_custom_field_keys(WC2_CSOD);
	if( !empty($csod_keys) && is_array($csod_keys) ) {
		foreach( $csod_keys as $key ) {
			list( $pfx, $csod_key ) = explode('_', $key, 2);
			if( array_key_exists(WC2_CUSTOM_ORDER, $entry_data['order']) and array_key_exists($csod_key, $entry_data['order'][WC2_CUSTOM_ORDER]) ) {
				if( is_array($entry_data['order'][WC2_CUSTOM_ORDER][$csod_key]) ) {
					$data[WC2_CUSTOM_ORDER][$csod_key] = serialize($entry_data['order'][WC2_CUSTOM_ORDER][$csod_key]);
				} else {
					$data[WC2_CUSTOM_ORDER][$csod_key] = $entry_data['order'][WC2_CUSTOM_ORDER][$csod_key];
				}
			} else {
				$data[WC2_CUSTOM_ORDER][$csod_key] = '';
			}
		}
	}

	$data[ORDER_CART] = array();
	ksort( $cart );
	foreach( $cart as $idx => $row ) {
		//$data[ORDER_CART][$row_index][ORDER_CART_GROUP_ID] = 0;
		$data[ORDER_CART][$idx][ORDER_CART_ROW_INDEX] = $idx;
		$data[ORDER_CART][$idx][ORDER_CART_POST_ID] = $row['post_id'];
		$data[ORDER_CART][$idx][ORDER_CART_ITEM_ID] = $row['item_id'];
		$data[ORDER_CART][$idx][ORDER_CART_ITEM_CODE] = $row['item_code'];
		$data[ORDER_CART][$idx][ORDER_CART_ITEM_NAME] = $row['item_name'];
		$data[ORDER_CART][$idx][ORDER_CART_SKU_ID] = $row['sku_id'];
		$data[ORDER_CART][$idx][ORDER_CART_SKU_CODE] = $row['sku_code'];
		$data[ORDER_CART][$idx][ORDER_CART_SKU_NAME] = $row['sku_name'];
		$data[ORDER_CART][$idx][ORDER_CART_PRICE] = $row['price'];
		$data[ORDER_CART][$idx][ORDER_CART_CPRICE] = $row['cprice'];
		$data[ORDER_CART][$idx][ORDER_CART_QUANTITY] = $row['quantity'];
		$data[ORDER_CART][$idx][ORDER_CART_UNIT] = $row['unit'];
		$data[ORDER_CART][$idx][ORDER_CART_TAX] = $row['tax'];
		//$data[ORDER_CART][$idx][ORDER_CART_DESTINATION_ID] = 0;
		$data[ORDER_CART][$idx][ORDER_CART_META_TYPE] = maybe_unserialize( $row['meta_type'] );
		$data[ORDER_CART][$idx][ORDER_CART_META_KEY] = maybe_unserialize( $row['meta_key'] );
	}

	$data[ORDER_DELIVERY][0][ORDER_DELIVERY_ROW_INDEX] = 0;
	$data[ORDER_DELIVERY][0][ORDER_DELIVERY_NAME1] = ( isset($entry_data['delivery']['name1']) ) ? $entry_data['delivery']['name1'] : '';
	$data[ORDER_DELIVERY][0][ORDER_DELIVERY_NAME2] = ( isset($entry_data['delivery']['name2']) ) ? $entry_data['delivery']['name2'] : '';
	$data[ORDER_DELIVERY][0][ORDER_DELIVERY_NAME3] = ( isset($entry_data['delivery']['name3']) ) ? $entry_data['delivery']['name3'] : '';
	$data[ORDER_DELIVERY][0][ORDER_DELIVERY_NAME4] = ( isset($entry_data['delivery']['name4']) ) ? $entry_data['delivery']['name4'] : '';
	$data[ORDER_DELIVERY][0][ORDER_DELIVERY_COUNTRY] = ( isset($entry_data['delivery']['country']) ) ? $entry_data['delivery']['country'] : '';
	$data[ORDER_DELIVERY][0][ORDER_DELIVERY_ZIPCODE] = ( isset($entry_data['delivery']['zipcode']) ) ? $entry_data['delivery']['zipcode'] : '';
	$data[ORDER_DELIVERY][0][ORDER_DELIVERY_PREF] = ( isset($entry_data['delivery']['pref']) ) ? $entry_data['delivery']['pref'] : '';
	$data[ORDER_DELIVERY][0][ORDER_DELIVERY_ADDRESS1] = ( isset($entry_data['delivery']['address1']) ) ? $entry_data['delivery']['address1'] : '';
	$data[ORDER_DELIVERY][0][ORDER_DELIVERY_ADDRESS2] = ( isset($entry_data['delivery']['address2']) ) ? $entry_data['delivery']['address2'] : '';
	$data[ORDER_DELIVERY][0][ORDER_DELIVERY_TEL] = ( isset($entry_data['delivery']['tel']) ) ? $entry_data['delivery']['tel'] : '';
	$data[ORDER_DELIVERY][0][ORDER_DELIVERY_FAX] = ( isset($entry_data['delivery']['fax']) ) ? $entry_data['delivery']['fax'] : '';

	$csde_keys = wc2_get_custom_field_keys(WC2_CSDE);
	if( !empty($csde_keys) && is_array($csde_keys) ) {
		foreach( $csde_keys as $key ) {
			list( $pfx, $csde_key ) = explode('_', $key, 2);
			if( array_key_exists(WC2_CUSTOM_DELIVERY, $entry_data['delivery']) and array_key_exists($csde_key, $entry_data['delivery'][WC2_CUSTOM_DELIVERY]) ) {
				if( is_array($entry_data['delivery'][WC2_CUSTOM_DELIVERY][$csde_key]) ) {
					$data[ORDER_DELIVERY][0][WC2_CUSTOM_DELIVERY][$csde_key] = serialize($entry_data['delivery'][WC2_CUSTOM_DELIVERY][$csde_key]);
				} else {
					$data[ORDER_DELIVERY][0][WC2_CUSTOM_DELIVERY][$csde_key] = $entry_data['delivery'][WC2_CUSTOM_DELIVERY][$csde_key];
				}
			} else {
				$data[ORDER_DELIVERY][0][WC2_CUSTOM_DELIVERY][$csde_key] = '';
			}
		}
	}

//wc2_log(print_r($data,true),"test.log");

	$data = apply_filters( 'wc2_filter_register_order_entry_data', $data, $args );
	$wc2_order->set_order_data( $data );

	$res = $wc2_order->add_order_data();
	if( !$res ) {
		wc2_set_log( 'Order data registration error.', 'register_orderdata' );
		return false;
	}

	$order_id = $wc2_order->get_the_order_id();

	if( $entry_data['member_id'] && wc2_is_membersystem_state() && wc2_is_membersystem_point() ) {
		$wc2_order->point_processing( $order_id, $entry_data['member_id'], $entry_data['order']['payment_method'], $entry_data['order']['getpoint'], $entry_data['order']['usedpoint'], $receipt_status );
	}

	$args['order_id'] = $order_id;
	wc2_set_dec_order_id( $args );

	do_action( 'wc2_action_register_order_data', $args, $data );

	return $order_id;
}

function wc2_set_dec_order_id( $args ) {
	$wc2_order = WC2_DB_Order::get_instance();
	$system_options = wc2_get_option( 'system' );
	extract( $args );

	$olimit = 0;
	if( !$system_options['dec_orderID_flag'] ) {
		$dec_order_id = str_pad( $order_id, $system_options['dec_orderID_digit'], "0", STR_PAD_LEFT );
	} else {
		while( $ukey = wc2_get_key( $system_options['dec_orderID_digit'] ) ) {
			$ores = $wc2_order->get_order_data_value( $order_id, ORDER_DEC_ID );
			if( !$ores || 100 < $olimit )
				break;
			$olimit++;
		}
		$dec_order_id = $ukey;
	}
	$dec_order_id = apply_filters( 'wc2_filter_dec_order_id_prefix', $system_options['dec_orderID_prefix'], $args ) . apply_filters( 'wc2_filter_dec_order_id', $dec_order_id, $args );

	if( 100 < $olimit ) {
		$wc2_order->set_dec_order_id( $order_id, uniqid() );
	} else {
		$wc2_order->set_dec_order_id( $order_id, $dec_order_id );
	}
}

function wc2_is_complete_settlement( $payment_method, $receipt_status = '' ) {
	$complete = false;

	$general = wc2_get_option( 'general' );
	if( $general['point_assign'] == 0 ) {
		$complete = true;
	} else {
		$payments = wc2_get_payment_option( 'id' );
		if( isset($payments[$payment_method]['settlement']) ) {
			switch( $payments[$payment_method]['settlement'] ) {
			case 'acting':
				if( false !== strpos( $receipt_status, 'pending' ) ) break;
			case 'acting_zeus_card':
			case 'acting_remise_card':
			case 'acting_jpayment_card':
			case 'acting_paypal_ec':
			case 'acting_sbps_card':
			case 'acting_telecom_card':
			case 'acting_digitalcheck_card':
			case 'acting_mizuho_card':
			case 'acting_anotherlane_card':
			case 'acting_veritrans_card':
			case 'COD':
				$complete = true;
			}
		}
	}
	$complete = apply_filters( 'wc2_filter_is_complete_settlement', $complete, $payment_method, $receipt_status );
	return $complete;
}

function wc2_get_item_total_price( $cart = array() ) {
	if( empty($cart) and !is_admin() )
		$cart = wc2_get_cart();

	$total_price = 0;
	foreach( $cart as $row ) {
		$total_price += ( $row['price'] * $row['quantity'] );
	}
	return apply_filters( 'wc2_filter_get_item_total_price', $total_price, $cart );
}

function wc2_get_item_total_quantity( $cart = array() ) {
	if( empty($cart) and !is_admin() )
		$cart = wc2_get_cart();

	$total_quantity = 0;
	foreach( $cart as $row ) {
		$total_quantity += $row['quantity'];
	}
	return $total_quantity;
}

function wc2_get_order_point( $member_id = '', $usedpoint = 0, $display_mode = '', $cart = array() ) {
	if( $member_id == '' || !wc2_is_membersystem_state() || !wc2_is_membersystem_point() ) return 0;

	$general = wc2_get_option( 'general' );
	if( empty($cart) and !is_admin() )
		$cart = wc2_get_cart();
	if( empty($display_mode) )
		$display_mode = $general['display_mode'];

	$point = 0;
	if( $display_mode == 'Promotionsale' ) {
		if( $general['campaign_privilege'] == 'discount' ) {
			foreach( $cart as $row ) {
				$cats = wc2_get_post_term_ids( $row['post_id'], 'category' );
				if( !in_array($general['campaign_category'], $cats) ) {
					$rate = wc2_get_item_value_by_item_id( $row['item_id'], ITEM_POINT_RATE );
					$price = $row['price'] * $row['quantity'];
					$point += $price * $rate / 100;
				}
			}
		} elseif( $general['campaign_privilege'] == 'point' ) {
			foreach( $cart as $row ) {
				$rate = wc2_get_item_value_by_item_id( $row['item_id'], ITEM_POINT_RATE );
				$price = $row['price'] * $row['quantity'];
				$cats = wc2_get_post_term_ids( $row['post_id'], 'category' );
				if( in_array($general['campaign_category'], $cats) ) {
					$point += $price * $rate / 100 * $general['privilege_point'];
				} else {
					$point += $price * $rate / 100;
				}
			}
		}
	} else {
		foreach( $cart as $row ) {
			$rate = wc2_get_item_value_by_item_id( $row['item_id'], ITEM_POINT_RATE );
			$price = $row['price'] * $row['quantity'];
			$point += $price * $rate / 100;
		}
	}

	if( 0 < $point ) $point = ceil( $point );

	if( 0 < $usedpoint ) {
		$item_total_price = wc2_get_item_total_price( $cart );
		$point = ceil( $point - ( $point * $usedpoint / $item_total_price ) );
		if( 0 > $point )
			$point = 0;
	}

	return apply_filters( 'wc2_filter_get_order_point', $point, $member_id, $display_mode, $cart );
}

function wc2_get_order_discount( $display_mode = '', $cart = array() ) {
	$general = wc2_get_option( 'general' );
	if( empty($display_mode) )
		$display_mode = $general['display_mode'];
	if( empty($cart) and !is_admin() )
		$cart = wc2_get_cart();

	$discount = 0;
	if( $display_mode == 'Promotionsale' ) {
		if( $general['campaign_privilege'] == 'discount' ) {
			if( 0 === (int)$general['campaign_category'] ) {
				$item_total_price = wc2_get_item_total_price( $cart );
				$discount = $item_total_price * $general['privilege_discount'] / 100;
			} else {
				foreach( $cart as $row ) {
					if( in_category((int)$general['campaign_category'], $row['post_id']) ) {
						$discount += $row['price'] * $row['quantity'] * $general['privilege_discount'] / 100;
					}
				}
			}
		} elseif( $general['campaign_privilege'] == 'point' ) {
			$discount = 0;
		}
	}

	$discount = ceil( $discount * -1 );
	$discount = apply_filters( 'wc2_filter_get_order_discount', $discount, $cart );
	return $discount;
}

function wc2_get_shipping_charge( $d_method_id, $pref, $country = '', $cart = array() ) {

	if( empty($country) )
		$country = wc2_get_base_country();
	if( empty($cart) and !is_admin() )
		$cart = wc2_get_cart();
	//if( empty($entry_data) )
	//	$entry_data = wc2_get_entry();
/*	if( function_exists( 'dlseller_have_shipped' ) && !dlseller_have_shipped() ) {
		$charge = 0;
		$charge = apply_filters( 'wc2_filter_get_shipping_charge', $charge, $cart, $entry_data );
		return $charge;
	}
*/
	$delivery = wc2_get_option( 'delivery' );
	$delivery_method = ( isset($delivery['delivery_method']) ) ? $delivery['delivery_method'] : array();
	$shipping_charge = wc2_get_option( 'shipping_charge' );

	//配送方法ID
	//$d_method_id = $entry_data['order']['delivery_method'];
	//配送方法index
	$d_method_index = wc2_get_delivery_method_index($d_method_id);
	//送料ID
	$fixed_charge_id = ( isset($delivery_method[$d_method_index]['charge']) ) ? $delivery_method[$d_method_index]['charge'] : -1;
	$individual_quant = 0;
	$charges = array();
	$individual_charges = array();

	foreach( $cart as $row ) {
		if( -1 == $fixed_charge_id ) {
			//商品送料ID
			$s_charge_id = wc2_get_item_value_by_item_id( $row['item_id'], ITEM_SHIPPING_CHARGE );
			//商品送料index
			$s_charge_index = wc2_get_shipping_charge_index( $s_charge_id );
			$charge = ( isset($shipping_charge[$s_charge_index][$country][$pref]) ) ? $shipping_charge[$s_charge_index][$country][$pref] : 0;
		} else {
			$s_charge_index = wc2_get_shipping_charge_index( $fixed_charge_id );
			$charge = ( isset($shipping_charge[$s_charge_index][$country][$pref]) ) ? $shipping_charge[$s_charge_index][$country][$pref] : 0;
		}
		$item_individual_shipping_charges = wc2_get_item_value_by_item_id( $row['item_id'], ITEM_INDIVIDUAL_SHIPPING_CHARGES );
		if( $item_individual_shipping_charges ) {
			$individual_quant += $row['quantity'];
			$individual_charges[] = $row['quantity'] * $charge;
		} else {
			$charges[] = $charge;
		}
	}

	if( 0 < count($charges) ) {
		rsort($charges);
		$max_charge = $charges[0];
		$charge = $max_charge + array_sum($individual_charges);
	} else {
		$charge = array_sum($individual_charges);
	}

	$charge = apply_filters( 'wc2_filter_get_shipping_charge', $charge, $cart );
	return $charge;
}

function wc2_get_cod_fee( $payment_method, $amount_by_cod, $item_total_price, $discount, $shipping_charge ) {
	$payment_info = wc2_get_option( 'payment_info' );
	$payment = wc2_get_payment( $payment_method );

	if( 'COD' != $payment['settlement'] ) {
		$fee = 0;

	} elseif( 'change' != $payment_info['cod_type'] ) {
		$fee = ( isset($payment_info['cod_fee']) ) ? $payment_info['cod_fee'] : 0;

	} else {
		$materials = array(
			'total_price' => $item_total_price,
			'discount' => $discount,
			'shipping_charge' => $shipping_charge,
			'cod_fee' => 0
		);
		$price = $amount_by_cod + wc2_get_tax( $materials );
		if( $price <= $payment_info['cod_first_amount'] ) {
			$fee = $payment_info['cod_first_fee'];

		} elseif( isset($payment_info['cod_amounts']) ) {
			$last = count( $payment_info['cod_amounts'] ) - 1;
			if( $price > $payment_info['cod_amounts'][$last] ) {
				$fee = $payment_info['cod_end_fee'];

			} else {
				$fee = 0;
				foreach( $payment_info['cod_amounts'] as $key => $value ) {
					if( $price <= $value ) {
						$fee = $payment_info['cod_fees'][$key];
						break;
					}
				}
			}
		} else {
			$fee = $payment_info['cod_end_fee'];
		}
	}
	$fee = apply_filters( 'wc2_filter_get_cod_fee', $fee, $payment_method, $amount_by_cod );
	return $fee;
}

function wc2_set_order_price( $cart = array(), $entry_data = array() ) {
	$general = wc2_get_option( 'general' );
	if( empty($cart) and !is_admin() )
		$cart = wc2_get_cart();
	if( empty($entry_data) and !is_admin() )
		$entry_data = wc2_get_entry();

	//*** Delivery method name
	$delivery_name = wc2_get_delivery_method_name( $entry_data['order']['delivery_method'] );
	wc2_set_entry_order_value( 'delivery_name', $delivery_name );
	//--------------------------------------------------------------------------

	//*** Payment method name
	$payment = wc2_get_payment( $entry_data['order']['payment_method'] );
	$payment_name = $payment['name'];
	wc2_set_entry_order_value( 'payment_name', $payment_name );
	//--------------------------------------------------------------------------

	//*** Item total price
	$item_total_price = wc2_get_item_total_price( $cart );
	wc2_set_entry_order_value( 'item_total_price', $item_total_price );
	//--------------------------------------------------------------------------

	//*** Discount price
	$discount = wc2_get_order_discount( $general['display_mode'], $cart );
	wc2_set_entry_order_value( 'discount', $discount );
	//--------------------------------------------------------------------------

	//*** Shipping charge
	if( empty($general['postage_privilege']) || ( $item_total_price + $discount ) < $general['postage_privilege'] ) {
		$country = ( isset($entry_data['delivery']['country']) && !empty($entry_data['delivery']['country']) ) ? $entry_data['delivery']['country'] : $entry_data['customer']['country'];
		$shipping_charge = wc2_get_shipping_charge( $entry_data['order']['delivery_method'], $entry_data['delivery']['pref'], $country, $cart );
	} else {
		$shipping_charge = 0;
	}
	$shipping_charge = apply_filters( 'wc2_filter_set_shipping_charge', $shipping_charge, $cart, $entry_data );
	wc2_set_entry_order_value( 'shipping_charge', $shipping_charge );
	//--------------------------------------------------------------------------

	//*** COD fee
	$usedpoint = ( isset( $entry_data['order']['usedpoint'] ) ) ? (int)$entry_data['order']['usedpoint'] : 0;
	$amount_by_cod = $item_total_price + $discount + $shipping_charge - $usedpoint;
	$amount_by_cod = apply_filters( 'wc2_filter_set_amount_by_cod', $amount_by_cod, $entry_data, $item_total_price, $discount, $shipping_charge, $usedpoint );
	$cod_fee = wc2_get_cod_fee( $entry_data['order']['payment_method'], $amount_by_cod, $item_total_price, $discount, $shipping_charge );
	$cod_fee = apply_filters( 'wc2_filter_set_cod_fee', $cod_fee, $entry_data, $item_total_price, $discount, $shipping_charge, $usedpoint );
	wc2_set_entry_order_value( 'cod_fee', $cod_fee );
	//--------------------------------------------------------------------------

	//*** Set materials
	$materials = array(
		'entry_data' => $entry_data,
		'cart' => $cart,
		'total_price' => $item_total_price,
		'discount' => $discount,
		'shipping_charge' => $shipping_charge,
		'usedpoint' => $usedpoint,
		'cod_fee' => $cod_fee,
		'payment' => $payment,
	);
	//--------------------------------------------------------------------------

	//*** Tax price
	$tax = wc2_get_tax( $materials );
	wc2_set_entry_order_value( 'tax', $tax );
	//--------------------------------------------------------------------------

	//*** Total price
	$total_price = $item_total_price + $discount + $shipping_charge - $usedpoint + $cod_fee + ( 'exclude' == $general['tax_mode'] ? $tax : 0 );
	$total_price = apply_filters( 'wc2_filter_set_total_order_price', $total_price, $item_total_price, $discount, $shipping_charge, $usedpoint, $cod_fee );
	wc2_set_entry_order_value( 'total_price', $total_price );
	//--------------------------------------------------------------------------

	//*** Get point
	$member = wc2_get_member();
	$getpoint = wc2_get_order_point( $member['ID'], $usedpoint );
	wc2_set_entry_order_value( 'getpoint', $getpoint );
	//--------------------------------------------------------------------------
}

function wc2_update_order_check( $order_id, $checked ){
	$wc2_order = WC2_DB_Order::get_instance();
	return $wc2_order->update_order_check( $order_id, $checked );
}

