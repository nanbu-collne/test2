<?php

/* Tables */
const TABLE_ITEM = 'wc2_item';
const TABLE_ITEM_META = 'wc2_itemmeta';
const TABLE_ITEM_SKU = 'wc2_itemsku';
const TABLE_ITEM_SKU_META = 'wc2_itemskumeta';

/* Columns */
const ITEM_POST_TYPE = 'item';
const ITEM_ID = 'item_id';
const ITEM_POST_ID = 'item_post_id';
const ITEM_CODE = 'item_code';//商品コード
const ITEM_NAME = 'item_name';//商品名
const ITEM_PRODUCT_TYPE = 'item_product_type';//商品区分(0:物販、1:ダウンロードコンテンツ、2:サービス)
const ITEM_CHARGES_TYPE = 'item_charges_type';//課金タイプ(0:通常課金、1:継続課金、2:定期購入)
const ITEM_OPTION = 'item_option';//商品オプション
const ITEM_PURCHASE_LIMIT = 'item_purchase_limit';//購入制限数
const ITEM_PURCHASE_LIMIT_LOWEST = 'item_purchase_limit_lowest';//購入制限数(最低)
const ITEM_PURCHASE_LIMIT_HIGHEST = 'item_purchase_limit_highest';//購入制限数(最大)
const ITEM_POINT_RATE = 'item_point_rate';//ポイント率
const ITEM_QUANTITY_DISCOUNT = 'item_quantity_discount';//大口割引
const ITEM_QUANTITY_DISCOUNT_NUM1 = 'item_quantity_discount_num1';//大口割引1(数)
const ITEM_QUANTITY_DISCOUNT_RATE1 = 'item_quantity_discount_rate1';//大口割引1(割引率)
const ITEM_QUANTITY_DISCOUNT_NUM2 = 'item_quantity_discount_num2';//大口割引2(数)
const ITEM_QUANTITY_DISCOUNT_RATE2 = 'item_quantity_discount_rate2';//大口割引2(割引率)
const ITEM_QUANTITY_DISCOUNT_NUM3 = 'item_quantity_discount_num3';//大口割引3(数)
const ITEM_QUANTITY_DISCOUNT_RATE3 = 'item_quantity_discount_rate3';//大口割引3(割引率)
const ITEM_PREPARATIONS_SHIPMENT = 'item_preparations_shipment';//発送日目安
const ITEM_DELIVERY_METHOD = 'item_delivery_method';//配送方法
const ITEM_SHIPPING_CHARGE = 'item_shipping_charge';//送料
const ITEM_INDIVIDUAL_SHIPPING_CHARGES = 'item_individual_shipping_charges';//送料個別課金
const ITEM_SKU = 'item_sku';
const ITEM_SKU_ITEM_ID = 'sku_item_id';
const ITEM_SKU_ID = 'sku_id';
const ITEM_SKU_CODE = 'sku_code';//SKUコード
const ITEM_SKU_NAME = 'sku_name';//SKU名
const ITEM_SKU_UNIT = 'sku_unit';//単位
const ITEM_SKU_STOCK = 'sku_stock';//在庫数
const ITEM_SKU_STATUS = 'sku_status';//在庫ステータス
const ITEM_SKU_PRICE = 'sku_price';//売価
const ITEM_SKU_COSTPRICE = 'sku_costprice';//原価
const ITEM_SKU_LISTPRICE = 'sku_listprice';//定価
const ITEM_SKU_SET_QUANTITY_DISCOUNT = 'sku_set_quantity_discount';//大口割引適用
const ITEM_SKU_SORT = 'sku_sort';//ソート順

/* Types */
const TYPE_NONE = '#NONE#';
const TYPE_TEXT = 'x';//ALL
const TYPE_TEXT_Z = 'kan';//全角
const TYPE_TEXT_ZK = 'kna';//全角カナ
const TYPE_TEXT_A = 'aln';//半角英数
const TYPE_TEXT_I = 'int';//整数
const TYPE_TEXT_F = 'float';//浮動小数点数
const TYPE_TEXT_P = 'price';//金額(浮動小数点数)
const TYPE_TEXT_D = 'date';//日付(半角整数or'/'or'-'or' ')
const TYPE_CHECK = 'check';//チェックボックス
const TYPE_RADIO = 'radio';//ラジオボタン
const TYPE_SELECT = 'select';//セレクトボックス
const TYPE_SELECT_MULTIPLE = 'selectmultiple';//セレクトボックス(複数選択)
const TYPE_TEXTAREA = 'textarea';
const TYPE_PARENT = 'parent';

class WC2_DB_Item
{
	/* Public variables */

	public $item_table = '';//Item table name
	public $item_meta_table = '';//Item meta table name
	public $item_sku_table = '';//Item SKU table name
	public $item_sku_meta_table = '';//Item SKU meta table name

	/* Private variables */

	protected $post_id = '';//Item post->ID
	protected $item_id = '';//Item internal ID
	protected $item_data = array();//Item data
	protected $item_list = array();//Item list data
	protected $item_code = '';//Item code

	protected $sku_id = '';//Current SKU id
	protected $sku_data = array();//Current SKU data

	protected $item_sku_data = array();//Current Item SKU data

	protected $item_base_column = array();//Item base box
	protected $item_meta_column = array();//Item meta box
	protected $item_sku_column = array();//Item SKU box
	protected $item_sku_meta_column = array();//Item SKU meta box
	protected $item_sku_format = array();//Item SKU box format

	protected $item_table_column = array();//Item table column
	protected $item_sku_table_column = array();//Item SKU table column

	protected $item_label = array();//Labels

	protected $query = '';//Query
	protected $select = '';//Selects
	protected $join = '';//Join tables
	protected $table = '';//Main table
	protected $where = '';//Conditions
	protected $order = '';//Sort by

	protected static $instance = null;

	/* Constructor */

	private function __construct() {
		$plugin = Welcart2::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		//Initial processing.
		$this->_set_table();
		$this->_set_item_label_init();
		$this->_set_item_column_init();

		add_action( 'the_post', array( $this, 'item_post_action' ) );
	}

	public function item_post_action() {
		global $post, $post_type;

		if( ITEM_POST_TYPE == $post_type ) {
			$this->set_the_post_id( $post->ID );
			$this->get_item_data();
		}
	}

	/* Public functions */

	public static function get_instance() {
		if( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	// Is wc2item by $post->ID
	// ( argument or global. )
	public function is_wc2item( $post_id = '' ) {
		global $wpdb, $post;
		if( empty($post_id) ) {
			if( empty($post) ) return false;
			$post_id = $post->ID;
		}

		$query = $wpdb->prepare( "SELECT post_type FROM {$wpdb->posts} WHERE ID = %d", $post_id );
		$post_type = $wpdb->get_var( $query );
		if( ITEM_POST_TYPE == $post_type ) {
			return true;
		} else {
			return false;
		}
	}

	// Make select query
	public function get_select_query() {
		$this->query = "SELECT ".$this->select." FROM ".$this->table;
		if( !empty($this->join) ) $this->query .= $this->join;
		$this->query .= " WHERE ".$this->where;
		if( !empty($this->order) ) $this->query .= $this->order;
		return $this->query;
	}

	// Get item list
	public function get_item_list( $post_id, $column ) {
		global $wpdb;
		switch( $column ) {
		case ITEM_CODE:
		case ITEM_NAME:
			$this->select = ITEM_CODE.", ".ITEM_NAME;
			$this->table = $this->item_table;
			$this->join = "";
			$this->where = ITEM_POST_ID." = ".$post_id;
			break;
		case ITEM_SKU_CODE:
		case ITEM_SKU_PRICE:
		case ITEM_SKU_STOCK:
		case ITEM_SKU_STATUS:
			$this->select = ITEM_SKU_CODE.", ".ITEM_SKU_PRICE.", ".ITEM_SKU_STOCK.", ".ITEM_SKU_STATUS;
			$this->table = $this->item_sku_table;
			$this->join = " INNER JOIN ".$this->item_table." AS item ON sku_item_id = item.ID ";
			$this->where = ITEM_POST_ID." = ".$post_id;
			break;
		}
		do_action( 'wc2_action_get_item_list_pre' );

		$query = apply_filters( 'wc2_filter_get_item_list_select_query', $this->get_select_query() );
		$this->item_list = $wpdb->get_results( $query, ARRAY_A );
		do_action( 'wc2_action_get_item_list' );

		return $this->item_list;
	}

	// Get item_code for list display
	// ( item_code & item_name )
	public function get_list_item_code( $post_id ) {
		$item_list = $this->get_item_list( $post_id, ITEM_CODE );
		$item_code = ( !empty($item_list[0][ITEM_CODE]) ) ? esc_attr($item_list[0][ITEM_CODE]) : '';
		$item_name = ( !empty($item_list[0][ITEM_NAME]) ) ? esc_attr($item_list[0][ITEM_NAME]) : '';
		return ( $item_code.'　'.$item_name );
	}

	// Get sku_code for list display
	public function get_list_item_sku_code( $post_id ) {
		$item_list = $this->get_item_list( $post_id, ITEM_SKU_CODE );
		$sku_code = '';
		foreach( $item_list as $row ) {
			$sku_code .= $row[ITEM_SKU_CODE].'<br />';
		}
		return $sku_code;
	}

	// Get sku_price for list display
	public function get_list_item_sku_price( $post_id ) {
		$item_list = $this->get_item_list( $post_id, ITEM_SKU_PRICE );
		$sku_price = '';
		foreach( $item_list as $row ) {
			$sku_price .= wc2_crform( $row[ITEM_SKU_PRICE], true, false ).'<br />';
		}
		return $sku_price;
	}

	// Get sku_stock for list display
	public function get_list_item_sku_stock( $post_id ) {
		$item_list = $this->get_item_list( $post_id, ITEM_SKU_STOCK );
		$sku_stock = '';
		foreach( $item_list as $row ) {
			$sku_stock .= $row[ITEM_SKU_STOCK].'<br />';
		}
		return ( $sku_stock );
	}

	// Get sku_status for list display
	// ( stock status name. )
	public function get_list_item_sku_status( $post_id ) {
		$stock_status = wc2_get_option( 'stock_status' );
		$item_list = $this->get_item_list( $post_id, ITEM_SKU_STATUS );
		$sku_stock_status = '';
		foreach( $item_list as $row ) {
			$sku_stock_status .= $stock_status[$row[ITEM_SKU_STATUS]].'<br />';
		}
		return ( $sku_stock_status );
	}

	// Get database values
	// ( from internal post_id. )
	public function get_item_data( $key = 'item_post_id' ) {
		global $wpdb;

		$this->clear_column();

		if( $key == 'item_post_id' ) {
			if( empty( $this->post_id ) ) return array();
			if( !$this->is_wc2item( $this->post_id ) ) return array();
			$key_value = $this->post_id;
		} elseif( $key == 'ID' ) {
			if( empty( $this->item_id ) ) return array();
			$key_value = $this->item_id;
		} elseif( $key == 'item_code' ) {
			if( empty( $this->item_code ) ) return array();
			$key_value = $this->item_code;
		} else {
			return array();
		}

		//Item table
		$query = $wpdb->prepare( "SELECT * FROM {$this->item_table} WHERE {$key} = %d", $key_value );
		$data = $wpdb->get_row( $query, ARRAY_A );
		if( $data ) {
			$this->item_id = $data['ID'];
			$this->post_id = $data[ITEM_POST_ID];

			$this->item_data[ITEM_ID] = $data['ID'];
			$this->item_data[ITEM_POST_ID] = $data[ITEM_POST_ID];
			$this->item_data[ITEM_CODE] = $data[ITEM_CODE];
			$this->item_data[ITEM_NAME] = $data[ITEM_NAME];
			$this->item_data[ITEM_PRODUCT_TYPE] = $data[ITEM_PRODUCT_TYPE];
			$this->item_data[ITEM_CHARGES_TYPE] = $data[ITEM_CHARGES_TYPE];

			//Item meta table
			$query_meta = $wpdb->prepare( "SELECT * FROM {$this->item_meta_table} WHERE meta_item_id = %d", $this->item_id );
			$data_meta = $wpdb->get_results( $query_meta, ARRAY_A );
			foreach( (array)$data_meta as $meta ) {
				$this->item_data[$meta['meta_key']] = $meta['meta_value'];
			}

			//Item SKU table
			$query_sku = $wpdb->prepare( "SELECT * FROM {$this->item_sku_table} WHERE sku_item_id = %d ORDER BY sku_sort", $this->item_id );
			$data_sku = $wpdb->get_results( $query_sku, ARRAY_A );
			if( 0 < count($data_sku) ) {
				foreach( (array)$data_sku as $sku ) {
					$id = $sku[ITEM_SKU_ID];
					$this->item_data[ITEM_SKU][$id][ITEM_SKU_ID] = $sku[ITEM_SKU_ID];
					$this->item_data[ITEM_SKU][$id][ITEM_SKU_CODE] = $sku[ITEM_SKU_CODE];
					$this->item_data[ITEM_SKU][$id][ITEM_SKU_NAME] = $sku[ITEM_SKU_NAME];
					$this->item_data[ITEM_SKU][$id][ITEM_SKU_PRICE] = $sku[ITEM_SKU_PRICE];
					$this->item_data[ITEM_SKU][$id][ITEM_SKU_COSTPRICE] = $sku[ITEM_SKU_COSTPRICE];
					$this->item_data[ITEM_SKU][$id][ITEM_SKU_LISTPRICE] = $sku[ITEM_SKU_LISTPRICE];
					$this->item_data[ITEM_SKU][$id][ITEM_SKU_UNIT] = $sku[ITEM_SKU_UNIT];
					$this->item_data[ITEM_SKU][$id][ITEM_SKU_STOCK] = $sku[ITEM_SKU_STOCK];
					$this->item_data[ITEM_SKU][$id][ITEM_SKU_STATUS] = $sku[ITEM_SKU_STATUS];
					$this->item_data[ITEM_SKU][$id][ITEM_SKU_SORT] = $sku[ITEM_SKU_SORT];

					//Item SKU meta table
					$query_sku_meta = $wpdb->prepare( "SELECT * FROM {$this->item_sku_meta_table} WHERE meta_item_id = %d AND meta_sku_id = %d", $this->item_id, $sku[ITEM_SKU_ID] );
					$data_sku_meta = $wpdb->get_results( $query_sku_meta, ARRAY_A );
					foreach( (array)$data_sku_meta as $sku_meta ) {
						$this->item_data[ITEM_SKU][$id][$sku_meta['meta_key']] = $sku_meta['meta_value'];
					}
				}
			}
		}

		return $this->item_data;
	}

	// Get database values
	// ( from internal item_id and sku_id. )
	public function get_item_sku_data( $item_id, $sku_id ) {
		global $wpdb;

		if( empty($item_id) or empty($sku_id) ) return array();

		$this->item_sku_data = array();
		$stock_status = wc2_get_option( 'stock_status' );

		//Item table & Item SKU table
		$query = $wpdb->prepare( "SELECT ITEM.ID AS item_id, item_post_id, item_code, item_name, item_product_type, item_charges_type, 
				sku_id, sku_code, sku_name, sku_price, sku_costprice, sku_listprice, sku_unit, sku_stock, sku_status, sku_sort 
			FROM {$this->item_table} AS ITEM 
			LEFT JOIN {$this->item_sku_table} AS SKU ON ITEM.ID = sku_item_id AND sku_id = %d 
			WHERE ITEM.ID = %d", 
			$sku_id, $item_id
		);
		$data = $wpdb->get_row( $query, ARRAY_A );
		if( $data ) {
			$this->item_sku_data[ITEM_ID] = $data[ITEM_ID];
			$this->item_sku_data[ITEM_POST_ID] = $data[ITEM_POST_ID];
			$this->item_sku_data[ITEM_CODE] = $data[ITEM_CODE];
			$this->item_sku_data[ITEM_NAME] = $data[ITEM_NAME];
			$this->item_sku_data[ITEM_PRODUCT_TYPE] = $data[ITEM_PRODUCT_TYPE];
			$this->item_sku_data[ITEM_CHARGES_TYPE] = $data[ITEM_CHARGES_TYPE];
			$this->item_sku_data[ITEM_SKU_ID] = $data[ITEM_SKU_ID];
			$this->item_sku_data[ITEM_SKU_CODE] = $data[ITEM_SKU_CODE];
			$this->item_sku_data[ITEM_SKU_NAME] = $data[ITEM_SKU_NAME];
			$this->item_sku_data[ITEM_SKU_PRICE] = $data[ITEM_SKU_PRICE];
			$this->item_sku_data[ITEM_SKU_COSTPRICE] = $data[ITEM_SKU_COSTPRICE];
			$this->item_sku_data[ITEM_SKU_LISTPRICE] = $data[ITEM_SKU_LISTPRICE];
			$this->item_sku_data[ITEM_SKU_UNIT] = $data[ITEM_SKU_UNIT];
			$this->item_sku_data[ITEM_SKU_STOCK] = $data[ITEM_SKU_STOCK];
			$this->item_sku_data[ITEM_SKU_STATUS] = $data[ITEM_SKU_STATUS];
			if( array_key_exists( $data[ITEM_SKU_STOCK], $stock_status ) ) {
				$this->item_sku_data['stock_status'] = $stock_status[$data[ITEM_SKU_STOCK]];
			} else {
				$this->item_sku_data['stock_status'] = '';
			}
			$this->item_sku_data[ITEM_SKU_SORT] = $data[ITEM_SKU_SORT];

			//Item meta table
			$query_meta = $wpdb->prepare( "SELECT * FROM {$this->item_meta_table} WHERE meta_item_id = %d", $item_id );
			$data_meta = $wpdb->get_results( $query_meta, ARRAY_A );
			foreach( (array)$data_meta as $meta ) {
				$this->item_sku_data[$meta['meta_key']] = $meta['meta_value'];
			}

			//Item SKU meta table
			$query_sku_meta = $wpdb->prepare( "SELECT * FROM {$this->item_sku_meta_table} WHERE meta_item_id = %d AND meta_sku_id = %d", $item_id, $sku_id );
			$data_sku_meta = $wpdb->get_results( $query_sku_meta, ARRAY_A );
			foreach( (array)$data_sku_meta as $sku_meta ) {
				$this->item_sku_data[$sku_meta['meta_key']] = $sku_meta['meta_value'];
			}
		}

		return $this->item_sku_data;
	}

	public function get_item_sku_data_by_code( $item_code, $sku_code ) {
		global $wpdb;

		if( empty($item_code) or empty($sku_code) ) return array();

		$query = $wpdb->prepare( "SELECT ITEM.ID AS item_id, item_code, sku_id, sku_code 
			FROM {$this->item_table} AS ITEM 
			LEFT JOIN {$this->item_sku_table} AS SKU ON ITEM.ID = sku_item_id AND sku_code = %s 
			WHERE item_code = %s", 
			$sku_code, $item_code
		);
		$data = $wpdb->get_row( $query, ARRAY_A );
		if( $data ) {
			$this->item_sku_data = $this->get_item_sku_data( $data[ITEM_ID], $data[ITEM_SKU_ID] );
		}

		return $this->item_sku_data;
	}

	/*public function get_sku_name_by_sku_code( $sku_code ){
		global $wpdb;

		if( empty($sku_code) ) return array();

		$query = $wpdb->prepare( "SELECT sku_name
			FROM {$this->item_sku_table}
			WHERE sku_code = %s",
			$sku_code
		);

		$sku_name = $wpdb->get_var( $query );

		return $sku_name;
	}*/


	public function get_sku_id_by_sku_code( $sku_code ){
		global $wpdb;

		if( empty($sku_code) ) return array();

		$query = $wpdb->prepare( "SELECT sku_id
			FROM {$this->item_sku_table}
			WHERE sku_code = %s",
			$sku_code
		);
		$sku_name = $wpdb->get_var( $query );

		return $sku_name;
	}

	// Register database
	public function add_item_data() {
		global $wpdb;

		//if( !$this->is_wc2item( $this->post_id ) ) return false;

		do_action( 'wc2_action_add_item_data_pre' );

		//Item table
		$query = $wpdb->prepare(
			"INSERT INTO {$this->item_table} ( item_post_id, item_code, item_name, item_product_type, item_charges_type ) 
				VALUES ( %d, %s, %s, %d, %d )", 
			$this->get_the_post_id(), 
			$this->get_the_item_code(), 
			$this->get_the_item_name(), 
			$this->get_the_item_product_type(), 
			$this->get_the_item_charges_type() 
		);
		$res = $wpdb->query( $query );
		if( false !== $res ) {

			//Get internal item_id.
			$this->item_id = $wpdb->insert_id;

			//Item meta table ( item_base_column not in item_table_column. )
			$item_base_column = $this->get_item_base_column();
			foreach( (array)$item_base_column as $key => $column ) {
				if( !$this->is_item_table_column( $key ) and $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
					$res = $this->update_item_meta_data( $this->item_id, $key, $this->get_the_item_value( $key ) );
					if( false === $res ) break;
				}
			}

			if( false !== $res ) {
				//Item meta table ( item_meta_column. )
				$item_meta_column = $this->get_item_meta_column();
				foreach( (array)$item_meta_column as $key => $column ) {
					if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
						$res = $this->update_item_meta_data( $this->item_id, $key, $this->get_the_item_value( $key ) );
						if( false === $res ) break;
					}
				}

				if( false !== $res ) {
					//Item SKU table & Item SKU meta table
					$item_sku = ( is_array($this->item_data) and array_key_exists( ITEM_SKU, $this->item_data ) ) ? $this->item_data[ITEM_SKU] : array();
					foreach( (array)$item_sku as $id => $sku ) {
						if( 0 < $id and '' != $this->get_the_item_sku_code( $id ) ) {
							$res = $this->add_item_sku_data( $id, $sku );
							if( false === $res ) break;
						}
					}
				}
			}
		}
		do_action( 'wc2_action_add_item_data', $this->item_id, $res );

		return $res;
	}

	// Register database
	// ( SKU & SKU meta )
	public function add_item_sku_data( $id, $sku ) {
		global $wpdb;

		//Item SKU table
		$query = $wpdb->prepare(
			"INSERT INTO {$this->item_sku_table} ( sku_item_id, sku_id, sku_code, sku_name, sku_price, sku_costprice, sku_listprice, sku_unit, sku_stock, sku_status, sku_sort ) 
				VALUES ( %d, %d, %s, %s, %f, %f, %f, %s, %s, %d, %d )", 
			$this->item_id, 
			$id, 
			$this->get_the_item_sku_code( $id ), 
			$this->get_the_item_sku_name( $id ), 
			$this->get_the_item_sku_price( $id ), 
			$this->get_the_item_sku_costprice( $id ), 
			$this->get_the_item_sku_listprice( $id ), 
			$this->get_the_item_sku_unit( $id ), 
			$this->get_the_item_sku_stock( $id ), 
			$this->get_the_item_sku_status( $id ), 
			$this->get_the_item_sku_sort( $id ) 
		);

		$res = $wpdb->query( $query );
		if( false !== $res ) {
			//Item SKU meta table ( item_sku_column not in item_sku_table_column. )
			$item_sku_column = $this->get_item_sku_column();
			foreach( (array)$item_sku_column as $key => $column ) {
				if( !$this->is_item_sku_table_column( $key ) and $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
					$res = $this->update_item_sku_meta_data( $this->item_id, $id, $key, $sku[$key] );
					if( false === $res ) break;
				}
			}

			if( false !== $res ) {
				//Item SKU meta table ( item_sku_meta_column. )
				$item_sku_meta_column = $this->get_item_sku_meta_column();
				foreach( (array)$item_sku_meta_column as $key => $column ) {
					if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
						$res = $this->update_item_sku_meta_data( $this->item_id, $id, $key, $sku[$key] );
						if( false === $res ) break;
					}
				}
			}
		}
		return $res;
	}

	// Update database
	public function update_item_data( $sku_delete = false ) {
		global $wpdb;

		if( empty( $this->post_id ) ) return false;
		if( !$this->is_wc2item( $this->post_id ) ) return false;

		$query = $wpdb->prepare( "SELECT ID FROM {$this->item_table} WHERE item_post_id = %d ", $this->post_id );
		$item_id = $wpdb->get_var( $query );

		if( !$item_id ) {
			$res = $this->add_item_data();

		} else {
			do_action( 'wc2_action_update_item_data_pre' );

			$this->item_id = $item_id;
			//Item table
			$query = $wpdb->prepare(
				"UPDATE {$this->item_table} SET item_code = %s, item_name = %s, item_product_type = %d, item_charges_type = %d WHERE item_post_id = %d ", 
				$this->get_the_item_code(), 
				$this->get_the_item_name(), 
				$this->get_the_item_product_type(), 
				$this->get_the_item_charges_type(),
				$this->get_the_post_id()
			);

			$res = $wpdb->query( $query );
			if( false !== $res ) {
				//Item meta table ( item_base_column not in item_table_column. )
				$item_base_column = $this->get_item_base_column();
				foreach( (array)$item_base_column as $key => $column ) {
					if( !$this->is_item_table_column( $key ) and $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
						$res = $this->update_item_meta_data( $this->item_id, $key, $this->get_the_item_value( $key ) );
						if( false === $res ) break;
					}
				}

				if( false !== $res ) {
					//Item meta table ( item_meta_column. )
					$item_meta_column = $this->get_item_meta_column();
					foreach( (array)$item_meta_column as $key => $column ) {
						if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
							$res = $this->update_item_meta_data( $this->item_id, $key, $this->get_the_item_value( $key ) );
							if( false === $res ) break;
						}
					}

					if( true == $sku_delete ) {
						$this->delete_item_sku_data( $this->item_id );
					}

					//Item SKU table & Item SKU meta table
					$item_sku = ( is_array($this->item_data) and array_key_exists( ITEM_SKU, $this->item_data ) ) ? $this->item_data[ITEM_SKU] : array();

					foreach( (array)$item_sku as $id => $sku ) {
						//$query_select = $wpdb->prepare( "SELECT COUNT(*) FROM {$this->item_sku_table} WHERE sku_item_id = %d AND sku_id = %d ", $this->item_id, $id );
						if( 0 == $this->count_sku_data($id) ) {
							if( 0 < $id and '' != $this->get_the_item_sku_code( $id ) ) {
								$res = $this->add_item_sku_data( $id, $sku );
								if( false === $res ) break;
							}
						} else {
							$res = $this->update_item_sku_data( $id, $sku );
							if( false === $res ) break;
						}
					}
				}
			}
			do_action( 'wc2_action_update_item_data', $this->item_id, $res );
		}

		return $res;
	}

	// Update database
	// ( Update Item meta or register. )
	public function update_item_meta_data( $item_id, $key, $value, $type = '' ) {
		global $wpdb;

		if( '' == $type ) {
			$query_select = $wpdb->prepare( "SELECT COUNT( meta_id ) FROM {$this->item_meta_table} WHERE meta_item_id = %d AND meta_key = %s ",
				$item_id, $key
			);
		} else {
			$query_select = $wpdb->prepare( "SELECT COUNT( meta_id ) FROM {$this->item_meta_table} WHERE meta_item_id = %d AND meta_type = %s AND meta_key = %s ",
				$item_id, $type, $key
			);
		}
		if( 0 == $wpdb->get_var( $query_select ) ) {
			$query = $wpdb->prepare( "INSERT INTO {$this->item_meta_table} ( meta_item_id, meta_type, meta_key, meta_value ) VALUES ( %d, %s, %s, %s )",
				$item_id, $type, $key, $value
			);
		} else {
			if( '' == $type ) {
				$query = $wpdb->prepare( "UPDATE {$this->item_meta_table} SET meta_value = %s WHERE meta_item_id = %d AND meta_key = %s ",
					$value, $item_id, $key
				);
			} else {
				$query = $wpdb->prepare( "UPDATE {$this->item_meta_table} SET meta_value = %s WHERE meta_item_id = %d AND meta_type = %s AND meta_key = %s ",
					$value, $item_id, $type, $key
				);
			}
		}
		$res = $wpdb->query( $query );

		return $res;
	}

	// Update database
	// ( SKU & SKU meta )
	public function update_item_sku_data( $id, $sku ) {
		global $wpdb;

		//Item SKU table
		$query = $wpdb->prepare(
			"UPDATE {$this->item_sku_table} SET 
			sku_code = %s, sku_name = %s, sku_price = %f, sku_costprice = %f, sku_listprice = %f, sku_unit = %s, sku_stock = %s, sku_status = %d, sku_sort = %d 
			WHERE sku_item_id = %d AND sku_id = %d ", 
			$this->get_the_item_sku_code( $id ), 
			$this->get_the_item_sku_name( $id ), 
			$this->get_the_item_sku_price( $id ), 
			$this->get_the_item_sku_costprice( $id ), 
			$this->get_the_item_sku_listprice( $id ), 
			$this->get_the_item_sku_unit( $id ), 
			$this->get_the_item_sku_stock( $id ), 
			$this->get_the_item_sku_status( $id ), 
			$this->get_the_item_sku_sort( $id ), 
			$this->item_id,
			$id
		);

		$res = $wpdb->query( $query );
		if( false !== $res ) {
			//Item SKU meta table ( item_sku_column not in item_sku_table_column. )
			$item_sku_column = $this->get_item_sku_column();

			foreach( (array)$item_sku_column as $key => $column ) {
				if( !$this->is_item_sku_table_column( $key ) and $column['display'] != 'none' and $column['type'] != TYPE_PARENT  ) {
					$res = $this->update_item_sku_meta_data( $this->item_id, $id, $key, $sku[$key] );
					if( false === $res ) break;
				}
			}

			if( false !== $res ) {
				//Item SKU meta table ( item_sku_meta_column. )
				$item_sku_meta_column = $this->get_item_sku_meta_column();
				foreach( (array)$item_sku_meta_column as $key => $column ) {
					if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
						$res = $this->update_item_sku_meta_data( $this->item_id, $id, $key, $sku[$key] );
						if( false === $res ) break;
					}
				}
			}
		}

		return $res;
	}

	// Update database
	// ( Update SKU meta or register. )
	public function update_item_sku_meta_data( $item_id, $sku_id, $key, $value, $type = '' ) {
		global $wpdb;

		if( empty($item_id) ) return false;

		if( is_array($value) ) $value = serialize($value);

		if( '' == $type ) {
			$query_select = $wpdb->prepare( "SELECT COUNT( meta_id ) FROM {$this->item_sku_meta_table} WHERE meta_item_id = %d AND meta_sku_id = %d AND meta_key = %s ",
				$item_id, $sku_id, $key
			);
		} else {
			$query_select = $wpdb->prepare( "SELECT COUNT( meta_id ) FROM {$this->item_sku_meta_table} WHERE meta_item_id = %d AND meta_sku_id = %d AND meta_type = %s AND meta_key = %s ",
				$item_id, $sku_id, $type, $key
			);
		}
		if( 0 == $wpdb->get_var( $query_select ) ) {
			$query = $wpdb->prepare( "INSERT INTO {$this->item_sku_meta_table} ( meta_item_id, meta_sku_id, meta_type, meta_key, meta_value ) VALUES ( %d, %d, %s, %s, %s )",
				$item_id, $sku_id, $type, $key, $value
			);
		} else {
			if( '' == $type ) {
				$query = $wpdb->prepare( "UPDATE {$this->item_sku_meta_table} SET meta_value = %s WHERE meta_item_id = %d AND meta_sku_id = %d AND meta_key = %s ",
					$value, $item_id, $sku_id, $key
				);
			} else {
				$query = $wpdb->prepare( "UPDATE {$this->item_sku_meta_table} SET meta_value = %s WHERE meta_item_id = %d AND meta_sku_id = %d AND meta_type = %s AND meta_key = %s ",
					$value, $item_id, $sku_id, $type, $key
				);
			}
		}
		$res = $wpdb->query( $query );
		return $res;
	}

	// Delete database
	public function delete_item_data() {
		global $wpdb;

		if( empty( $this->post_id ) ) return false;
		if( !$this->is_wc2item( $this->post_id ) ) return false;

		do_action( 'wc2_action_delete_item_data_pre', $this->post_id );

		$query_select = $wpdb->prepare( "SELECT ID FROM {$this->item_table} WHERE item_post_id = %d", $this->post_id );
		$item_id = $wpdb->get_var( $query_select );
		$res = '';
		if( $item_id ) {
			$query = $wpdb->prepare( "DELETE FROM {$this->item_table} WHERE item_post_id = %d AND ID = %d", $this->post_id, $item_id );
			$res = $wpdb->query( $query );
			if( false !== $res ) {
				$query_meta = $wpdb->prepare( "DELETE FROM {$this->item_meta_table} WHERE meta_item_id = %d", $item_id );
				$res = $wpdb->query( $query_meta );
				if( false !== $res ) {
					$query_sku = $wpdb->prepare( "DELETE FROM {$this->item_sku_table} WHERE sku_item_id = %d", $item_id );
					$res = $wpdb->query( $query_sku );
					if( false !== $res ) {
						$query_sku_meta = $wpdb->prepare( "DELETE FROM {$this->item_sku_meta_table} WHERE meta_item_id = %d", $item_id );
						$res = $wpdb->query( $query_sku_meta );
					}
				}
			}
		}

		do_action( 'wc2_action_delete_item_data', $this->post_id, $item_id, $res );

		return $res;
	}

	public function delete_item_sku_data( $item_id, $sku_id = '' ) {
		global $wpdb;

		if( empty( $item_id ) ) return false;

		do_action( 'wc2_action_delete_item_sku_data_pre', $this->post_id, $item_id, $sku_id );

		if( !empty( $sku_id ) ) {
			$query_sku = $wpdb->prepare( "DELETE FROM {$this->item_sku_table} WHERE sku_item_id = %d AND sku_id = %d", $item_id, $sku_id );
			$res = $wpdb->query( $query_sku );
			if( false !== $res ) {
				$query_sku_meta = $wpdb->prepare( "DELETE FROM {$this->item_sku_meta_table} WHERE meta_item_id = %d AND meta_sku_id = %d", $item_id, $sku_id );
				$res = $wpdb->query( $query_sku_meta );
			}
		} else {
			$query_sku = $wpdb->prepare( "DELETE FROM {$this->item_sku_table} WHERE sku_item_id = %d", $item_id );
			$res = $wpdb->query( $query_sku );
			if( false !== $res ) {
				$query_sku_meta = $wpdb->prepare( "DELETE FROM {$this->item_sku_meta_table} WHERE meta_item_id = %d", $item_id );
				$res = $wpdb->query( $query_sku_meta );
			}
		}

		do_action( 'wc2_action_delete_item_sku_data', $this->post_id, $item_id, $sku_id, $res );

		return $res;
	}

	public function delete_all_item_data(){
		global $wpdb;
		$table = $wpdb->prefix.'wc2_item';
		$query = "SELECT * FROM {$table}";
		$all_item = $wpdb->get_results($query, ARRAY_A);
		$wc2_item = WC2_DB_Item::get_instance();
		foreach( $all_item as $key => $val ){
			$wc2_item->set_the_post_id($val['item_post_id']);
			$wc2_item->delete_item_data();
		}
		$args = array(
					'post_type' => 'item',
					'numberposts' => -1 ,
					'post_status' => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' )
					);
		$item_posts = get_posts($args);

		foreach($item_posts as $key => $val){
			wp_delete_post($val->ID);
		}

	}

	public function delete_item_revision(){
		global $wpdb;

		$query = $wpdb->prepare( "DELETE FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = %s", $this->post_id, 'revision' );
		$res = $wpdb->query($query);

		do_action( 'wc2_action_delete_item_revision', $this->post_id);

		return $res;
	}

	public function delete_term_relationship(){
		global $wpdb;

		$query = $wpdb->prepare( "DELETE FROM {$wpdb->term_relationships} WHERE object_id = %d", $this->post_id );
		$res = $wpdb->query($query);

		do_action('wc2_action_delete_term_relationship', $this->post_id);

		return $res;
	}

	public function delete_custome_field_key($meta_key_table){
		global $wpdb;
		
		$query = $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ( %s ) AND post_id = %d", implode( "','", $meta_key_table ), $this->post_id );
		$query = stripslashes( $query );
		$res = $wpdb->query( $query );

		do_action('wc2_action_delete_custome_field_key', $this->post_id);

		return $res;
	}

	//public function delete
	public function get_count_term_taxonomy(){
		global $wpdb;

		$query = "SELECT term_taxonomy_id, COUNT(*) AS ct FROM {$wpdb->term_relationships} GROUP BY term_taxonomy_id";
		$res = $wpdb->get_results( $query, ARRAY_A );

		do_action('wc2_action_get_count_term_taxonomy');

		return $res;
	}

	public function term_taxonomy_count_post($term_taxonomy_id){
		global $wpdb;
		
		$query = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->term_relationships} WHERE term_taxonomy_id = %d", $term_taxonomy_id );
		$tct = $wpdb->get_var( $query );
		$query = $wpdb->prepare( "UPDATE {$wpdb->term_taxonomy} SET count = %d WHERE term_taxonomy_id = %d", $tct, $term_taxonomy_id );
		$res = $wpdb->query($query);

		return $res;
	}

	public function count_sku_data($sku_id){
		global $wpdb;

		$query = $wpdb->prepare( "SELECT COUNT( sku_id ) FROM {$this->item_sku_table} WHERE sku_item_id = %d AND sku_id = %d ", $this->item_id, $sku_id );
		$res = $wpdb->get_var($query);

		return $res;
	}

	// Create values
	public function clear_column() {
		$this->item_data = array();
		$this->sku_id = '';
		$this->sku_data = array();
	}

	// Column
	public function set_item_base_column( $column ) {
		$this->item_base_column = $column;
	}
	public function set_item_meta_column( $column ) {
		$this->item_meta_column = $column;
	}
	public function set_item_sku_column( $column ) {
		$this->item_sku_column = $column;
	}
	public function set_item_sku_meta_column( $column ) {
		$this->item_sku_meta_column = $column;
	}
	public function set_item_sku_format( $format ) {
		$this->item_sku_format = $format;
	}
	public function get_item_base_column() {
		return $this->item_base_column;
	}
	public function get_item_meta_column() {
		$item_meta_column = apply_filters( 'wc2_filter_get_item_meta_column', $this->item_meta_column );
		return $item_meta_column;
	}
	public function get_item_meta_info_box_column() {
		return $this->item_meta_column;
	}
	public function get_item_sku_column() {
		return $this->item_sku_column;
	}
	public function get_item_sku_meta_column() {
		$item_sku_meta_column = apply_filters( 'wc2_filter_get_item_sku_meta_column', $this->item_sku_meta_column );
		return $item_sku_meta_column;
	}
	public function get_item_sku_format() {
		return $this->item_sku_format;
	}
	public function get_item_table_column() {
		return $this->item_table_column;
	}
	public function get_item_sku_table_column() {
		return $this->item_sku_table_column;
	}
	public function get_item_column_all() {
		$column = array_merge( $this->item_base_column, $this->item_meta_column, $this->item_sku_column, $this->item_sku_meta_column );
		$column = apply_filters( 'wc2_filter_get_item_column_all', $column );
		return $column;
	}

	// Label
	public function get_item_label() {
		return $this->item_label;
	}
	public function set_item_label( $label ) {
		$this->item_label = $label;
	}

	public function get_the_item_label( $column ) {
		$label = ( array_key_exists( $column, $this->item_label ) ) ? $this->item_label[$column] : '';
		return $label;
	}

	// SKU
	public function count_item_sku() {
		$count = ( is_array($this->item_data) and array_key_exists( ITEM_SKU, $this->item_data ) ) ? count( array_keys( $this->item_data[ITEM_SKU] ) ) : 0;
		return $count;
	}
	public function get_item_sku() {
		$item_sku = ( is_array($this->item_data) and array_key_exists( ITEM_SKU, $this->item_data ) ) ? $this->item_data[ITEM_SKU] : array();
		return $item_sku;
	}
	public function get_item_sku_id_max() {
		if( is_array($this->item_data) and array_key_exists( ITEM_SKU, $this->item_data ) ) {
			$sku_id = array_keys( $this->item_data[ITEM_SKU] );
			$max = max( $sku_id );
		} else {
			$max = 0;
		}
		return $max;
	}
	public function have_item_sku_data() {
		if( empty($this->sku_data) ) {
			reset( $this->item_data[ITEM_SKU] );
		}
		list( $this->sku_id, $this->sku_data ) = each( $this->item_data[ITEM_SKU] );
		if( $this->sku_data ) {
			return true;
		} else {
			return false;
		}
	}
	public function get_the_sku_id() {
		$id = ( !empty($this->sku_id) ) ? $this->sku_id : false;
		return $id;
	}
	public function set_the_sku_id( $id ) {
		$this->sku_id = $id;
	}
	public function get_the_sku_data() {
		$data = ( !empty($this->sku_data) ) ? $this->sku_data : array();
		return $data;
	}

	// Set values
	public function set_the_item_value( $key, $value ) {
		if( '' != $key ) $this->item_data[$key] = $value;
	}
	public function set_the_item_id( $value ) {
		$this->item_id = $value;
	}
	public function set_the_post_id( $value ) {
		$this->post_id = $value;
	}
	public function set_the_code( $value ) {
		$this->item_code = $value;
	}
	public function set_the_item_post_id( $value ) {
		$this->item_data[ITEM_POST_ID] = $value;
	}
	public function set_the_item_code( $value ) {
		$this->item_data[ITEM_CODE] = $value;
	}
	public function set_the_item_name( $value ) {
		$this->item_data[ITEM_NAME] = $value;
	}
	public function set_the_item_product_type( $value ) {
		$this->item_data[ITEM_PRODUCT_TYPE] = $value;
	}
	public function set_the_item_charges_type( $value ) {
		$this->item_data[ITEM_CHARGES_TYPE] = $value;
	}
	public function set_the_item_options( $options ) {
		foreach( (array)$options as $option ) {
			$this->item_data[ITEM_OPTION][$option['key']] = $option['value'];
		}
	}
	public function set_the_item_option( $key, $value ) {
		if( !empty($key) ) $this->item_data[ITEM_OPTION][$key] = $value;
	}
	public function set_the_item_sku_value( $key, $id, $value ) {
		if( '' != $id and '' != $key ) $this->item_data[ITEM_SKU][$id][$key] = $value;

	}

	// Get values
	public function get_the_item_value( $key ) {
		if( '' == $key ) return '';
		$value = ( is_array($this->item_data) and array_key_exists( $key, $this->item_data ) ) ? $this->item_data[$key] : '';
		return $value;
	}
	public function get_the_post_id() {
		return $this->post_id;
	}
	public function get_the_item_id() {
		return $this->item_id;
	}
	public function get_the_item_post_id() {
		$value = ( is_array($this->item_data) and array_key_exists( ITEM_POST_ID, $this->item_data ) ) ? $this->item_data[ITEM_POST_ID] : '';
		return $value;
	}
	public function get_the_item_code() {
		$value = ( is_array($this->item_data) and array_key_exists( ITEM_CODE, $this->item_data ) ) ? $this->item_data[ITEM_CODE] : '';
		return $value;
	}
	public function get_the_item_name() {
		$value = ( is_array($this->item_data) and array_key_exists( ITEM_NAME, $this->item_data ) ) ? $this->item_data[ITEM_NAME] : '';
		return $value;
	}
	public function get_the_item_product_type() {
		$value = ( is_array($this->item_data) and array_key_exists( ITEM_PRODUCT_TYPE, $this->item_data ) ) ? $this->item_data[ITEM_PRODUCT_TYPE] : 0;
		return $value;
	}
	public function get_the_item_charges_type() {
		$value = ( is_array($this->item_data) and array_key_exists( ITEM_CHARGES_TYPE, $this->item_data ) ) ? $this->item_data[ITEM_CHARGES_TYPE] : 0;
		return $value;
	}
	public function get_the_item_options() {
		$value = ( is_array($this->item_data) and array_key_exists( ITEM_OPTION, $this->item_data ) ) ? $this->item_data[ITEM_OPTION] : '';
		return $value;
	}
	public function get_the_item_option( $key ) {
		$value = ( is_array($this->item_data[ITEM_OPTION]) and isset($this->item_data[ITEM_OPTION]) and array_key_exists( $key, $this->item_data[ITEM_OPTION] ) ) ? $this->item_data[ITEM_OPTION][$key] : '';
		return $value;
	}
	public function get_the_item_sku_value( $key, $id = '' ) {
		if( empty($id) ) $id = $this->get_the_sku_id();
		$value = ( is_array($this->item_data) and isset($this->item_data[ITEM_SKU][$id]) and array_key_exists( $key, $this->item_data[ITEM_SKU][$id] ) ) ? $this->item_data[ITEM_SKU][$id][$key] : '';
		return $value;
	}
	public function get_the_item_sku_code( $id = '' ) {
		if( empty($id) ) $id = $this->get_the_sku_id();
		$value = ( is_array($this->item_data) and isset($this->item_data[ITEM_SKU][$id]) and array_key_exists( ITEM_SKU_CODE, $this->item_data[ITEM_SKU][$id] ) ) ? $this->item_data[ITEM_SKU][$id][ITEM_SKU_CODE] : '';
		return $value;
	}
	public function get_the_item_sku_name( $id = '' ) {
		if( empty($id) ) $id = $this->get_the_sku_id();
		$value = ( is_array($this->item_data) and isset($this->item_data[ITEM_SKU][$id]) and array_key_exists( ITEM_SKU_NAME, $this->item_data[ITEM_SKU][$id] ) ) ? $this->item_data[ITEM_SKU][$id][ITEM_SKU_NAME] : '';
		return $value;
	}
	public function get_the_item_sku_price( $id = '' ) {
		if( empty($id) ) $id = $this->get_the_sku_id();
		$value = ( is_array($this->item_data) and isset($this->item_data[ITEM_SKU][$id]) and array_key_exists( ITEM_SKU_PRICE, $this->item_data[ITEM_SKU][$id] ) ) ? $this->item_data[ITEM_SKU][$id][ITEM_SKU_PRICE] : 0;
		return $value;
	}
	public function get_the_item_sku_costprice( $id = '' ) {
		if( empty($id) ) $id = $this->get_the_sku_id();
		$value = ( is_array($this->item_data) and isset($this->item_data[ITEM_SKU][$id]) and array_key_exists( ITEM_SKU_COSTPRICE, $this->item_data[ITEM_SKU][$id] ) ) ? $this->item_data[ITEM_SKU][$id][ITEM_SKU_COSTPRICE] : 0;
		return $value;
	}
	public function get_the_item_sku_listprice( $id = '' ) {
		if( empty($id) ) $id = $this->get_the_sku_id();
		$value = ( is_array($this->item_data) and isset($this->item_data[ITEM_SKU][$id]) and array_key_exists( ITEM_SKU_LISTPRICE, $this->item_data[ITEM_SKU][$id] ) ) ? $this->item_data[ITEM_SKU][$id][ITEM_SKU_LISTPRICE] : 0;
		return $value;
	}
	public function get_the_item_sku_unit( $id = '' ) {
		if( empty($id) ) $id = $this->get_the_sku_id();
		$value = ( is_array($this->item_data) and isset($this->item_data[ITEM_SKU][$id]) and array_key_exists( ITEM_SKU_UNIT, $this->item_data[ITEM_SKU][$id] ) ) ? $this->item_data[ITEM_SKU][$id][ITEM_SKU_UNIT] : '';
		return $value;
	}
	public function get_the_item_sku_stock( $id = '' ) {
		if( empty($id) ) $id = $this->get_the_sku_id();
		$value = ( is_array($this->item_data) and isset($this->item_data[ITEM_SKU][$id]) and array_key_exists( ITEM_SKU_STOCK, $this->item_data[ITEM_SKU][$id] ) ) ? $this->item_data[ITEM_SKU][$id][ITEM_SKU_STOCK] : '';
		return $value;
	}
	public function get_the_item_sku_status( $id = '' ) {
		if( empty($id) ) $id = $this->get_the_sku_id();
		$value = ( is_array($this->item_data) and isset($this->item_data[ITEM_SKU][$id]) and array_key_exists( ITEM_SKU_STATUS, $this->item_data[ITEM_SKU][$id] ) ) ? $this->item_data[ITEM_SKU][$id][ITEM_SKU_STATUS] : 0;
		return $value;
	}
	public function get_the_item_sku_sort( $id = '' ) {
		if( empty($id) ) $id = $this->get_the_sku_id();
		$value = ( is_array($this->item_data) and isset($this->item_data[ITEM_SKU][$id]) and array_key_exists( ITEM_SKU_SORT, $this->item_data[ITEM_SKU][$id] ) ) ? $this->item_data[ITEM_SKU][$id][ITEM_SKU_SORT] : 0;
		return $value;
	}

	public function get_item_name_by_item_code( $item_code ) {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT item_name FROM {$this->item_table} WHERE item_code = %d", $item_code );
		$item_name = $wpdb->get_var( $query );
		return $item_name;
	}

	public function get_post_id_by_item_code( $item_code ) {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT item_post_id FROM {$this->item_table} WHERE item_code = %s", $item_code );
		$post_id = $wpdb->get_var( $query );
		return $post_id;
	}

	public function get_post_id_by_item_name( $item_name ) {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT item_post_id FROM {$this->item_table} WHERE item_name = %s", $item_name );
		$post_id = $wpdb->get_var( $query );
		return $post_id;
	}

	public function get_some_post_ids_by_item_code( $item_code ){
		global $wpdb;

		$query = $wpdb->prepare( "SELECT item_post_id FROM {$this->item_table} WHERE item_code = %s", $item_code );

		$post_ids = $wpdb->get_results( $query, ARRAY_A );
		return $post_ids;
	}

	public function get_item_id_by_post_id($post_id = ''){
		global $wpdb;

		if( empty($post_id) ) {
			if(empty($this->$post_id) ){
				return;
			}
			$post_id = $this->$post_id;
		}
		$query = $wpdb->prepare( "SELECT ID FROM {$this->item_table} WHERE item_post_id = %d", $post_id );
		$item_id = $wpdb->get_var( $query );

		return $item_id;
	}

	public function get_item_value_by_post_id( $post_id = '', $column ) {
		global $wpdb, $post;
		if( empty($post_id) ) {
			if( empty($post) ) return '';
			$post_id = $post->ID;
		}
		if( empty($column) ) {
			$column = 'item_code';
		}

		if( $this->is_item_table_column( $column ) ) {
			$query = $wpdb->prepare( "SELECT {$column} FROM {$this->item_table} WHERE item_post_id = %d", $post_id );
		} else {
			$query = $wpdb->prepare( "SELECT meta_value FROM {$this->item_meta_table} AS META 
				INNER JOIN {$this->item_table} AS ITEM ON ITEM.ID = META.meta_item_id 
				WHERE item_post_id = %d AND meta_key = %s", 
				$post_id, $column
			);
		}
		$item_value = $wpdb->get_var( $query );
		return $item_value;
	}

	public function get_item_value_by_item_id( $item_id = '', $column ) {
		global $wpdb;
		if( empty($item_id) ) {
			$item_id = $this->get_the_item_id();
		}
		if( empty($column) ) {
			$column = 'item_code';
		}

		if( $this->is_item_table_column( $column ) ) {
			$query = $wpdb->prepare( "SELECT {$column} FROM {$this->item_table} WHERE ID = %d", $item_id );
		} else {
			$query = $wpdb->prepare( "SELECT meta_value FROM {$this->item_meta_table} WHERE meta_item_id = %d AND meta_key = %s", $item_id, $column );
		}
		$item_value = $wpdb->get_var( $query );
		return $item_value;
	}

	public function get_item_value_by_item_code( $item_code = '', $column ) {
		global $wpdb;
		if( empty($item_id) ) {
			$item_id = $this->get_the_item_id();
		}
		if( empty($column) ) {
			$column = 'ID';
		}

		if( $this->is_item_table_column( $column ) ) {
			$query = $wpdb->prepare( "SELECT {$column} FROM {$this->item_table} WHERE item_code = %s", $item_code );
		} else {
			$query = $wpdb->prepare( "SELECT meta_value FROM {$this->item_meta_table} AS META 
				INNER JOIN {$this->item_table} AS ITEM ON ITEM.ID = META.meta_item_id 
				WHERE item_code = %s AND meta_key = %s", 
				$item_code, $column
			);
		}
		$item_value = $wpdb->get_var( $query );
		return $item_value;
	}

	public function get_item_sku_price( $item_id, $sku_id, $price ) {
		global $wpdb;
		if( empty($item_id) ) {
			$item_id = $this->get_the_item_id();
		}
		if( empty($sku_id) ) {
			$sku_id = $this->get_the_sku_id();
		}
		if( empty($price) ) {
			$price = 'sku_price';
		}

		$query = $wpdb->prepare( "SELECT {$price} FROM {$this->item_sku_table} WHERE sku_item_id = %d AND sku_id = %d", $item_id, $sku_id );
		$sku_price = $wpdb->get_var( $query );
		return $sku_price;
	}

	public function get_item_sku_value( $item_id, $sku_id, $column ) {
		global $wpdb;
		if( empty($column) ) {
			$column = 'sku_code';
		}

		if( $this->is_item_sku_table_column( $column ) ) {
			$query = $wpdb->prepare( "SELECT {$column} 
				FROM {$this->item_sku_table} 
				WHERE sku_item_id = %d AND sku_id = %d",
				$item_code, $sku_code
			);
		} else {
			$query = $wpdb->prepare( "SELECT meta_value 
				FROM {$this->item_sku_table} AS SKU 
				WHERE meta_item_id = %d AND meta_sku_id = %d AND meta_key = %s",
				$item_id, $sku_id, $column
			);
		}
		$sku_value = $wpdb->get_var( $query );
		return $sku_value;
	}

	public function get_item_sku_value_by_code( $item_code, $sku_code, $column ) {
		global $wpdb;
		if( empty($column) ) {
			$column = 'sku_id';
		}

		if( $this->is_item_sku_table_column( $column ) ) {
			$query = $wpdb->prepare( "SELECT {$column} 
				FROM {$this->item_sku_table} AS SKU 
				INNER JOIN {$this->item_table} AS ITEM ON ITEM.ID = SKU.sku_item_id 
				WHERE ITEM.item_code = %s AND SKU.sku_code = %s",
				$item_code, $sku_code
			);
		} else {
			$query = $wpdb->prepare( "SELECT meta_value 
				FROM {$this->item_sku_meta_table} AS SKU_META 
				INNER JOIN {$this->item_sku_table} AS SKU ON SKU.sku_id = SKU_META.meta_sku_id 
				INNER JOIN {$this->item_table} AS ITEM ON ITEM.ID = SKU.sku_item_id AND ITEM.ID = SKU_META.meta_item_id 
				WHERE ITEM.item_code = %s AND SKU.sku_code = %s AND SKU_META.meta_key = %s",
				$item_code, $sku_code, $column
			);
		}
		$sku_value = $wpdb->get_var( $query );
		return $sku_value;
	}

	public function get_item_stock( $item_id, $sku_id ) {
		if( ( isset($this->item_sku_data[ITEM_ID]) and $item_id == $this->item_sku_data[ITEM_ID] ) and 
			( isset($this->item_sku_data[ITEM_SKU_ID]) and $sku_id == $this->item_sku_data[ITEM_SKU_ID] ) ) {
			$sku_stock = ( isset($this->item_sku_data[ITEM_SKU_STOCK]) ) ? $this->item_sku_data[ITEM_SKU_STOCK] : false;
		} else {
			global $wpdb;
			$query = $wpdb->prepare( "SELECT sku_stock FROM {$this->item_sku_table} WHERE sku_item_id = %d AND sku_id = %d", $item_id, $sku_id );
			$sku_stock = $wpdb->get_var( $query );
		}
		return $sku_stock;
	}

	public function get_item_stock_status( $item_id, $sku_id ) {
		$status = '';
		$stock_status = wc2_get_option( 'stock_status' );
		if( ( isset($this->item_sku_data[ITEM_ID]) and $item_id == $this->item_sku_data[ITEM_ID] ) and 
			( isset($this->item_sku_data[ITEM_SKU_ID]) and $sku_id == $this->item_sku_data[ITEM_SKU_ID] ) ) {
			$sku_status = ( isset($this->item_sku_data[ITEM_SKU_STATUS]) ) ? $this->item_sku_data[ITEM_SKU_STATUS] : false;
		} else {
			global $wpdb;
			$query = $wpdb->prepare( "SELECT sku_status FROM {$this->item_sku_table} WHERE sku_item_id = %d AND sku_id = %d", $item_id, $sku_id );
			$sku_status = $wpdb->get_var( $query );
		}
		if( array_key_exists( $sku_status, $stock_status ) ) {
			$status = $stock_status[$sku_status];
		}
		return $status;
	}

	public function get_pictids( $item_code ) {
		global $wpdb;

		if( empty($item_code) )
			return false;

		$system_options = wc2_get_option( 'system' );
		if( !$system_options['subimage_rule'] ) {
			$codestr = $item_code.'%';
			$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title LIKE %s AND post_title <> %s AND post_type = 'attachment' ORDER BY post_title", $codestr, $item_code );
		} else {
			$codestr = $item_code.'--%';
			$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title LIKE %s AND post_type = 'attachment' ORDER BY post_title", $codestr );
		}
		$res = $wpdb->get_col( $query );
		return $res;
	}

	public function get_mainpictid( $item_code ) {
		global $wpdb;
		$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'attachment' LIMIT 1", $item_code );
		$id = $wpdb->get_var( $query );
		$id = apply_filters( 'wc2_filter_get_mainpictid', $id, $item_code );
		return $id;
	}

	public function get_post_term_ids( $post_id, $taxonomy ) {
		global $wpdb;
		$query = $wpdb->prepare( "SELECT tt.term_id FROM {$wpdb->term_relationships} AS tr 
			INNER JOIN {$wpdb->term_taxonomy} AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id 
			WHERE tt.taxonomy = %s AND tr.object_id = %d", 
			$taxonomy, $post_id
		);
		$ids = $wpdb->get_col( $query );
		return $ids;
	}

	public function get_item_tags( $post_id ) {
		global $wpdb;
		$tag = 'item-tag';
		$query = $wpdb->prepare( "SELECT t.name FROM {$wpdb->term_relationships} AS tr 
			INNER JOIN {$wpdb->term_taxonomy} AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id 
			INNER JOIN {$wpdb->terms} AS t ON t.term_id = tt.term_id 
			WHERE tt.taxonomy = %s AND tr.object_id = %d", 
			$tag, $post_id
		);
		$item_tags = $wpdb->get_col( $query );
		return apply_filters( 'wc2_filter_get_get_item_tags', $item_tags, $post_id );
	}

	public function is_item_table_column( $column ) {
		return in_array( $column, $this->get_item_table_column() );
	}

	public function is_item_sku_table_column( $column ) {
		return in_array( $column, $this->get_item_sku_table_column() );
	}

	/* Private functions */

	//Initial table name
	private function _set_table() {
		global $wpdb;
		$this->item_table = $wpdb->prefix.TABLE_ITEM;
		$this->item_meta_table = $wpdb->prefix.TABLE_ITEM_META;
		$this->item_sku_table = $wpdb->prefix.TABLE_ITEM_SKU;
		$this->item_sku_meta_table = $wpdb->prefix.TABLE_ITEM_SKU_META;
	}

	//Initial label
	private function _set_item_label_init() {
		$this->item_label = array(
			ITEM_CODE => __( '商品コード', 'wc2' ),
			ITEM_NAME => __( '商品名', 'wc2' ),
			ITEM_PRODUCT_TYPE => __( '商品区分', 'wc2' ),
			ITEM_CHARGES_TYPE => __( '課金タイプ', 'wc2' ),
			ITEM_PURCHASE_LIMIT => __( '購入制限数', 'wc2' ),
			ITEM_POINT_RATE => __( 'ポイント率', 'wc2' ),
			ITEM_QUANTITY_DISCOUNT => __( '大口割引', 'wc2' ),

			ITEM_OPTION => __( '商品オプション', 'wc2' ),
			ITEM_PREPARATIONS_SHIPMENT => __( '発送日目安', 'wc2' ),
			ITEM_DELIVERY_METHOD => __( '配送方法', 'wc2' ),
			ITEM_SHIPPING_CHARGE => __( 'Shipping charges', 'wc2' ),
			ITEM_INDIVIDUAL_SHIPPING_CHARGES => __( '送料個別課金', 'wc2' ),

			ITEM_SKU => __( 'SKU情報', 'wc2' ),
			ITEM_SKU_CODE => __( 'SKUコード', 'wc2' ),
			ITEM_SKU_NAME => __( 'SKU表示名', 'wc2' ),
			ITEM_SKU_PRICE => __( '売価(円)', 'wc2' ),
			ITEM_SKU_COSTPRICE => __( '原価(円)', 'wc2' ),
			ITEM_SKU_LISTPRICE => __( '定価(円)', 'wc2' ),
			ITEM_SKU_UNIT => __( '単位', 'wc2' ),
			ITEM_SKU_STOCK => __( '在庫数', 'wc2' ),
			ITEM_SKU_STATUS => __( '在庫状態', 'wc2' ),
			ITEM_SKU_SET_QUANTITY_DISCOUNT => __( '大口割引適用', 'wc2' ),
		);
	}

	//Initial column
	private function _set_item_column_init() {

		//*** Not be changed.
		$this->item_table_column = array( ITEM_CODE, ITEM_NAME, ITEM_PRODUCT_TYPE, ITEM_CHARGES_TYPE );
		$this->item_sku_table_column = array( ITEM_SKU_CODE, ITEM_SKU_NAME, ITEM_SKU_PRICE, ITEM_SKU_COSTPRICE, ITEM_SKU_LISTPRICE, ITEM_SKU_UNIT, ITEM_SKU_STOCK, ITEM_SKU_STATUS, ITEM_SKU_SET_QUANTITY_DISCOUNT, ITEM_SKU_SORT );

		//Item base box column
		$this->item_base_column = array(
			ITEM_CODE => array( 'display'=>'', 'type'=>TYPE_TEXT_A, 'value'=>'', 'essential'=>1, 'default'=>'', 'parent'=>'', 'label_pre'=>'', 'label_post'=>'' ),
			ITEM_NAME => array( 'display'=>'', 'type'=>TYPE_TEXT, 'value'=>'', 'essential'=>1, 'default'=>'', 'parent'=>'', 'label_pre'=>'', 'label_post'=>'' ),
			ITEM_PRODUCT_TYPE => array( 'display'=>'', 'type'=>TYPE_RADIO, 'value'=>'0:物販;1:コンテンツファイル;2:サービス', 'essential'=>1, 'default'=>'', 'parent'=>'', 'label_pre'=>'', 'label_post'=>'' ),
			ITEM_CHARGES_TYPE => array( 'display'=>'', 'type'=>TYPE_SELECT, 'value'=>'0:通常課金;1:継続課金;2:定期購入', 'essential'=>1, 'default'=>'', 'parent'=>'', 'label_pre'=>'', 'label_post'=>'' ),
			ITEM_PURCHASE_LIMIT => array( 'display'=>'', 'type'=>TYPE_PARENT, 'value'=>'', 'essential'=>0, 'default'=>'', 'parent'=>'', 'label_pre'=>'', 'label_post'=>'' ),
			ITEM_PURCHASE_LIMIT_LOWEST => array( 'display'=>'', 'type'=>TYPE_TEXT_I, 'value'=>'', 'essential'=>0, 'default'=>'', 'parent'=>ITEM_PURCHASE_LIMIT, 'label_pre'=>'', 'label_post'=>'枚以上' ),
			ITEM_PURCHASE_LIMIT_HIGHEST => array( 'display'=>'', 'type'=>TYPE_TEXT_I, 'value'=>'', 'essential'=>0, 'default'=>'', 'parent'=>ITEM_PURCHASE_LIMIT, 'label_pre'=>'', 'label_post'=>'枚まで' ),
			ITEM_POINT_RATE => array( 'display'=>'', 'type'=>TYPE_TEXT_I, 'value'=>'', 'essential'=>0, 'default'=>'', 'parent'=>'', 'label_pre'=>'', 'label_post'=>'％（整数）' ),
			ITEM_QUANTITY_DISCOUNT => array( 'display'=>'', 'type'=>TYPE_PARENT, 'value'=>'', 'essential'=>0, 'default'=>'', 'parent'=>'', 'label_pre'=>'', 'label_post'=>'' ),
			ITEM_QUANTITY_DISCOUNT_NUM1 => array( 'display'=>'', 'type'=>TYPE_TEXT_I, 'value'=>'', 'essential'=>0, 'default'=>'', 'parent'=>ITEM_QUANTITY_DISCOUNT, 'label_pre'=>'１．', 'label_post'=>'枚以上で' ),
			ITEM_QUANTITY_DISCOUNT_RATE1 => array( 'display'=>'', 'type'=>TYPE_TEXT_I, 'value'=>'', 'essential'=>0, 'default'=>'', 'parent'=>ITEM_QUANTITY_DISCOUNT, 'label_pre'=>'', 'label_post'=>'円引き（単価）<br />' ),
			ITEM_QUANTITY_DISCOUNT_NUM2 => array( 'display'=>'', 'type'=>TYPE_TEXT_I, 'value'=>'', 'essential'=>0, 'default'=>'', 'parent'=>ITEM_QUANTITY_DISCOUNT, 'label_pre'=>'２．', 'label_post'=>'枚以上で' ),
			ITEM_QUANTITY_DISCOUNT_RATE2 => array( 'display'=>'', 'type'=>TYPE_TEXT_I, 'value'=>'', 'essential'=>0, 'default'=>'', 'parent'=>ITEM_QUANTITY_DISCOUNT, 'label_pre'=>'', 'label_post'=>'円引き（単価）<br />' ),
			ITEM_QUANTITY_DISCOUNT_NUM3 => array( 'display'=>'', 'type'=>TYPE_TEXT_I, 'value'=>'', 'essential'=>0, 'default'=>'', 'parent'=>ITEM_QUANTITY_DISCOUNT, 'label_pre'=>'３．', 'label_post'=>'枚以上で' ),
			ITEM_QUANTITY_DISCOUNT_RATE3 => array( 'display'=>'', 'type'=>TYPE_TEXT_I, 'value'=>'', 'essential'=>0, 'default'=>'', 'parent'=>ITEM_QUANTITY_DISCOUNT, 'label_pre'=>'', 'label_post'=>'円引き（単価）' ),
		);

		//Item meta box column
		$this->item_meta_column = array(
		);

		//Item SKU box column
		$stock_status = wc2_get_option( 'stock_status' );
		$stock_status_value = '';
		foreach( (array)$stock_status as $key => $value ) {
			$stock_status_value .= $key.':'.$value.';';
		}
		$stock_status_value = rtrim( $stock_status_value, ';' );

		$this->item_sku_column = array(
			ITEM_SKU_CODE => array( 'display'=>'', 'type'=>TYPE_TEXT_A, 'value'=>'', 'essential'=>1, 'default'=>'', 'parent'=>'', 'label_pre'=>'', 'label_post'=>'' ),
			ITEM_SKU_NAME => array( 'display'=>'', 'type'=>TYPE_TEXT, 'value'=>'', 'essential'=>0, 'default'=>'', 'parent'=>'', 'label_pre'=>'', 'label_post'=>'' ),
			ITEM_SKU_UNIT => array( 'display'=>'', 'type'=>TYPE_TEXT, 'value'=>'', 'essential'=>0, 'default'=>'', 'parent'=>'', 'label_pre'=>'', 'label_post'=>'' ),
			ITEM_SKU_STOCK => array( 'display'=>'', 'type'=>TYPE_TEXT_I, 'value'=>'', 'essential'=>0, 'default'=>'', 'parent'=>'', 'label_pre'=>'', 'label_post'=>'' ),
			ITEM_SKU_STATUS => array( 'display'=>'', 'type'=>TYPE_SELECT, 'value'=>$stock_status_value, 'essential'=>0, 'default'=>'', 'parent'=>'', 'label_pre'=>'', 'label_post'=>'' ),
			ITEM_SKU_PRICE => array( 'display'=>'', 'type'=>TYPE_TEXT_P, 'value'=>'', 'essential'=>1, 'default'=>'', 'parent'=>'', 'label_pre'=>'', 'label_post'=>'' ),
			ITEM_SKU_COSTPRICE => array( 'display'=>'', 'type'=>TYPE_TEXT_P, 'value'=>'', 'essential'=>0, 'default'=>'', 'parent'=>'', 'label_pre'=>'', 'label_post'=>'' ),
			ITEM_SKU_LISTPRICE => array( 'display'=>'', 'type'=>TYPE_TEXT_P, 'value'=>'', 'essential'=>0, 'default'=>'', 'parent'=>'', 'label_pre'=>'', 'label_post'=>'' ),
			ITEM_SKU_SET_QUANTITY_DISCOUNT => array( 'display'=>'', 'type'=>TYPE_SELECT, 'value'=>'0:適用しない;1:適用する', 'essential'=>0, 'default'=>'', 'parent'=>'', 'label_pre'=>'', 'label_post'=>'' ),
		);

		//Item SKU meta box column
		$this->item_sku_meta_column = array(
		);

		// SKU table format ( row * column )
		$this->item_sku_format = array(
			0 => array( ITEM_SKU_CODE, ITEM_SKU_NAME, ITEM_SKU_UNIT, ITEM_SKU_STOCK, ITEM_SKU_STATUS ),
			1 => array( ITEM_SKU_PRICE, ITEM_SKU_LISTPRICE, ITEM_SKU_COSTPRICE, '', ITEM_SKU_SET_QUANTITY_DISCOUNT ),
		);
	}
}

/* Template functions */

function is_wc2item( $post_id = '' ) {
	global $post;
	if( empty($post_id) ) {
		if( empty($post) ) return false;
		$post_id = $post->ID;
	}
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->is_wc2item( $post_id );
}

function wc2_get_item_data( $key = 'item_post_id' ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_data( $key );
}

function wc2_item_clear_column() {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->clear_column();
}

function wc2_set_item_base_column( $column ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_item_base_column( $column );
}

function wc2_set_item_meta_column( $column ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_item_meta_column( $column );
}

function wc2_set_item_sku_column( $column ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_item_sku_column( $column );
}

function wc2_set_item_sku_meta_column( $column ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_item_sku_meta_column( $column );
}

function wc2_set_item_sku_format( $format ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_item_sku_format( $format );
}

function wc2_get_item_base_column() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_base_column();
}

function wc2_get_item_meta_column() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_meta_column();
}

function wc2_get_item_sku_column() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_sku_column();
}

function wc2_get_item_sku_meta_column() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_sku_meta_column();
}

function wc2_get_item_sku_format() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_sku_format();
}

function wc2_get_item_table_column() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_table_column();
}

function wc2_get_item_sku_table_column() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_sku_table_column();
}

function wc2_get_item_column_all() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_column_all();
}

function wc2_get_item_label() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_label();
}

function wc2_set_item_label( $label ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_item_label( $label );
}

function wc2_get_the_item_label( $column ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_the_item_label( $column );
}

function wc2_the_item_label_e( $column ) {
	$wc2_item = WC2_DB_Item::get_instance();
	esc_attr_e( $wc2_item->get_the_item_label( $column ) );
}

function wc2_count_item_sku() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->count_item_sku();
}

function wc2_have_item_sku_data() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->have_item_sku_data();
}

function wc2_get_item_sku() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_sku();
}

function wc2_get_item_sku_id_max() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_sku_id_max();
}

function wc2_set_the_sku_id( $id ){
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->set_the_sku_id( $id );
}

function wc2_set_the_item_value( $key, $value ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_the_item_value( $key, $value );
}

function wc2_set_the_item_id( $value ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_the_item_id( $value );
}

function wc2_set_the_post_id( $value ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_the_post_id( $value );
}

function wc2_set_the_code( $value ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_the_code( $value );
}
function wc2_set_the_item_post_id( $value ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_the_item_post_id( $value );
}

function wc2_set_the_item_code( $value ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_the_item_code( $value );
}

function wc2_set_the_item_name( $value ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_the_item_name( $value );
}

function wc2_set_the_item_product_type( $value ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_the_item_product_type( $value );
}

function wc2_set_the_item_charges_type( $value ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_the_item_charges_type( $value );
}

function wc2_set_the_item_options( $options ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_the_item_options( $options );
}

function wc2_set_the_item_option( $key, $value ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_the_item_option( $key, $value );
}

function wc2_set_the_item_sku_value( $key, $id, $value ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_the_item_sku_value( $key, $id, $value );
}

function wc2_get_the_item_value( $key ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_the_item_value( $key );
}

function wc2_get_the_item_value_e( $key ) {
	esc_attr_e( wc2_get_the_item_value( $key ) );
}

function wc2_get_the_post_id() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_the_post_id();
}

function wc2_get_the_item_id() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_the_item_id();
}

function wc2_get_the_item_post_id() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_the_item_post_id();
}

function wc2_get_the_item_code() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_the_item_code();
}

function wc2_get_the_item_name() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_the_item_name();
}

function wc2_get_the_item_product_type() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_the_item_product_type();
}

function wc2_get_the_item_charges_type() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_the_item_charges_type();
}

function wc2_get_the_item_options() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_the_item_options();
}

function wc2_get_the_item_option( $key ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_the_item_option( $key );
}

function wc2_get_the_item_sku_value( $key ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$id = $wc2_item->get_the_sku_id();
	return $wc2_item->get_the_item_sku_value( $key, $id );
}

function wc2_get_the_item_sku_id() {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_the_sku_id();
}

function wc2_get_the_item_sku_code() {
	$wc2_item = WC2_DB_Item::get_instance();
	$id = $wc2_item->get_the_sku_id();
	return $wc2_item->get_the_item_sku_code( $id );
}

function wc2_get_the_item_sku_name() {
	$wc2_item = WC2_DB_Item::get_instance();
	$id = $wc2_item->get_the_sku_id();
	return $wc2_item->get_the_item_sku_name( $id );
}

function wc2_get_the_item_sku_price() {
	$wc2_item = WC2_DB_Item::get_instance();
	$id = $wc2_item->get_the_sku_id();
	return $wc2_item->get_the_item_sku_price( $id );
}

function wc2_get_the_item_sku_costprice() {
	$wc2_item = WC2_DB_Item::get_instance();
	$id = $wc2_item->get_the_sku_id();
	return $wc2_item->get_the_item_sku_costprice();
}

function wc2_get_the_item_sku_listprice() {
	$wc2_item = WC2_DB_Item::get_instance();
	$id = $wc2_item->get_the_sku_id();

	return $wc2_item->get_the_item_sku_listprice( $id );
}

function wc2_get_the_item_sku_unit() {
	$wc2_item = WC2_DB_Item::get_instance();
	$id = $wc2_item->get_the_sku_id();
	return $wc2_item->get_the_item_sku_unit( $id );
}

function wc2_get_the_item_sku_stock() {
	$wc2_item = WC2_DB_Item::get_instance();
	$id = $wc2_item->get_the_sku_id();
	return $wc2_item->get_the_item_sku_stock( $id );
}

function wc2_get_the_item_sku_status() {
	$wc2_item = WC2_DB_Item::get_instance();
	$id = $wc2_item->get_the_sku_id();
	return $wc2_item->get_the_item_sku_status( $id );
}

function wc2_get_the_item_sku_sort() {
	$wc2_item = WC2_DB_Item::get_instance();
	$id = $wc2_item->get_the_sku_id();
	return $wc2_item->get_the_item_sku_sort( $id );
}

function wc2_get_item_value_by_post_id( $post_id, $column){
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_value_by_post_id( $post_id, $column );
}

function wc2_get_item_code_by_post_id( $post_id = '' ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_value_by_post_id( $post_id, 'item_code' );
}

function wc2_get_item_name_by_post_id( $post_id = '' ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_value_by_post_id( $post_id, 'item_name' );
}

function wc2_get_item_code_by_item_id( $item_id = '' ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_value_by_item_id( $item_id, 'item_code' );
}

function wc2_get_item_name_by_item_id( $item_id = '' ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_value_by_item_id( $item_id, 'item_name' );
}

function wc2_get_item_value_by_item_id( $item_id, $column ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_value_by_item_id( $item_id, $column );
}

function wc2_get_item_id_by_item_code( $item_code ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_value_by_item_code( $item_code, 'ID' );
}

function wc2_get_item_value_by_item_code( $item_code, $column ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_value_by_item_code( $item_code, $column );
}

function wc2_the_item_code_e() {
	$wc2_item = WC2_DB_Item::get_instance();
	esc_attr_e( $wc2_item->get_the_item_code() );
}

function wc2_the_item_name_e() {
	$wc2_item = WC2_DB_Item::get_instance();
	esc_attr_e( $wc2_item->get_the_item_name() );
}

function wc2_the_item_product_type_e() {
	$wc2_item = WC2_DB_Item::get_instance();
	esc_attr_e( $wc2_item->get_the_item_product_type() );
}

function wc2_the_item_charges_type_e() {
	$wc2_item = WC2_DB_Item::get_instance();
	esc_attr_e( $wc2_item->get_the_item_charges_type() );
}

function wc2_the_item_sku_value_e( $key ) {
	$value = wc2_get_the_item_sku_value( $key );
	esc_attr_e( $value );
}

function wc2_the_item_sku_price_e() {
	$price = wc2_get_the_item_sku_price();
	echo wc2_crform( $price, true, false );
}

function wc2_the_item_sku_costprice_e() {
	$price = wc2_get_the_item_sku_costprice();
	echo wc2_crform( $price, true, false );
}

function wc2_the_item_sku_listprice_e() {
	$price = wc2_get_the_item_sku_listprice();
	echo wc2_crform( $price, true, false );
}

function wc2_update_item_meta_data( $item_id, $key, $value, $type = '' ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->update_item_meta_data( $item_id, $key, $value, $type );
}

function wc2_update_item_sku_meta_data( $item_id, $sku_id, $key, $value, $type = '' ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->update_item_sku_meta_data( $item_id, $sku_id, $key, $value, $type );
}

function wc2_get_item_sku_price( $item_id, $sku_id, $price = 'sku_price' ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_sku_price( $item_id, $sku_id, $price );
}

function wc2_get_item_stock( $item_id, $sku_id ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_stock( $item_id, $sku_id );
}

function wc2_get_item_stock_status( $item_id, $sku_id ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_stock_status( $item_id, $sku_id );
}

function wc2_get_item_sku_data( $item_id, $sku_id ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_sku_data( $item_id, $sku_id );
}

function wc2_get_item_sku_data_by_code( $item_code, $sku_code ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_sku_data_by_code( $item_code, $sku_code );
}

function wc2_get_mainpictid( $item_code ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_mainpictid( $item_code );
}

function wc2_get_post_term_ids( $post_id, $taxonomy ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_post_term_ids( $post_id, $taxonomy );
}

function wc2_get_item_tags( $post_id ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_tags( $post_id );
}

function wc2_get_item_charges_type( $item_id ) {
	$type = NULL;
	$charges = wc2_get_item_value_by_item_id( $item_id, 'item_charges_type' );
	switch( $charges ) {
		case 0:
			$type = 'once';
			break;
		case 1:
			$type = 'continue';
			break;
		case 2:
			$type = 'regular';
/*
			if( !empty($cart) ) {
				if( empty($cart['advance']) ) {
					$type = 'once';
				} else {
					if( is_array($cart['advance']) and array_key_exists( 'regular', $cart['advance'] ) ) {
						$regular = maybe_unserialize( $cart['advance']['regular'] );
					} else {
						$advance = $this->cart->wc_unserialize( $cart['advance'] );
						$sku = urldecode( $cart['sku'] );
						$sku_encoded = $cart['sku'];
						$regular = $advance[$post_id][$sku_encoded]['regular'];
					}
					$unit = isset( $regular['unit'] ) ? $regular['unit'] : '';
					$interval = isset( $regular['interval'] ) ? (int)$regular['interval'] : 0;
					if( empty($unit) or 1 > $interval )
						$type = 'once';
				}
			}
*/
			break;
		default:
			$type = 'once';
	}
	return $type;
}

function wc2_get_item_id_by_post_id( $post_id = '' ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_id_by_post_id( $post_id );
}

function wc2_get_post_id_by_item_code( $item_code ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_post_id_by_item_code( $item_code );
}

function wc2_get_item_name_by_item_code( $item_code ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_name_by_item_code( $item_code );
}

//function wc2_get_sku_name_by_sku_code( $sku_code ){
//	$wc2_item = WC2_DB_Item::get_instance();
//	return $wc2_item->get_sku_name_by_sku_code( $sku_code );
//}

function wc2_get_sku_id_by_sku_code($sku_code){
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_sku_id_by_sku_code( $sku_code );
}

function wc2_get_item_sku_value( $item_id, $sku_id, $column ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_sku_value( $item_id, $sku_id, $column );
}

function wc2_get_item_sku_value_by_code( $item_code, $sku_code, $column ) {
	$wc2_item = WC2_DB_Item::get_instance();
	return $wc2_item->get_item_sku_value_by_code( $item_code, $sku_code, $column );
}

function wc2_the_item_image( $number = 0, $width = 60, $height = 60, $post_id = '' ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$ptitle = $number;
	if( empty($post_id) ){
		$post_id = $wc2_item->get_the_post_id();
		if( empty($post_id) ){
			return false;
		}
	}

	if( $ptitle && 0 == (int)$number ) {
		$picposts = query_posts(array('post_type'=>'attachment','name'=>$ptitle));
		$pictid = empty($picposts) ? 0 : $picposts[0]->ID;
		$html = wp_get_attachment_image( $pictid, array($width, $height), false );
		$alt = 'alt="'.esc_attr($code[0]).'"';
		$alt = apply_filters('wc2_filter_img_alt', $alt, $post_id, $pictid, $width, $height);
		$html = preg_replace('/alt=\"[^\"]*\"/', $alt, $html);
		$title = 'title="'.esc_attr($name[0]).'"';
		$title = apply_filters('wc2_filter_img_title', $title, $post_id, $pictid, $width, $height);
		$html = preg_replace('/title=\"[^\"]+\"/', $title, $html);
		$html = apply_filters( 'wc2_filter_main_img', $html, $post_id, $pictid, $width, $height);
	} else {
		$wc2_item = WC2_DB_Item::get_instance();
		$item_code = $wc2_item->get_item_value_by_post_id( $post_id, 'item_code' );
		if( !$item_code ) return false;

		$item_name = $wc2_item->get_item_value_by_post_id( $post_id, 'item_name' );
		if( 0 == $number ) {
			$pictid = (int)$wc2_item->get_mainpictid( $item_code );
			$pictid = (!empty( $pictid ) ) ? $pictid: 0;
			$html = wp_get_attachment_image( $pictid, array($width, $height), true );
			$alt = 'alt="'.esc_attr($item_code).'"';
			$alt = apply_filters( 'wc2_filter_img_alt', $alt, $post_id, $pictid, $width, $height );
			$html = preg_replace('/alt=\"[^\"]*\"/', $alt, $html);
			$title = 'title="'.esc_attr( $item_name ).'"';
			$title = apply_filters( 'wc2_filter_img_title', $title, $post_id, $pictid, $width, $height );
			$html = preg_replace( '/title=\"[^\"]+\"/', $title, $html );
			$html = apply_filters( '_filter_main_img', $html, $post_id, $pictid, $width, $height );
		} else {
			$pictids = $wc2_item->get_pictids( $item_code );
			$ind = $number - 1;
			$pictid = ( isset($pictids[$ind]) && (int)$pictids[$ind] ) ? $pictids[$ind] : 0;
			$html = wp_get_attachment_image( $pictid, array($width, $height), false );
			$alt = 'alt="'.esc_attr($item_code).'"';
			$alt = apply_filters('wc2_filter_img_alt', $alt, $post_id, $pictid, $width, $height);
			$html = preg_replace('/alt=\"[^\"]*\"/', $alt, $html);
			$title = 'title="'.esc_attr($item_name).'"';
			$title = apply_filters('wc2_filter_img_title', $title, $post_id, $pictid, $width, $height);
			$html = preg_replace('/title=\"[^\"]+\"/', $title, $html);
			$html = apply_filters( 'wc2_filter_sub_img', $html, $post_id, $pictid, $width, $height);
		}
	}
	return $html;
}

function wc2_the_item_image_e($number = 0, $width = 60, $height = 60, $post_id = ''){
	echo wc2_the_item_image($number, $width, $height, $post_id);
}

function wc2_the_item_image_url( $number = 0, $post_id = '' ) {
	$ptitle = $number;

	if( $ptitle && is_string($number) ) {
		$picposts = query_posts( array( 'post_type'=>'attachment', 'name'=>$ptitle ) );
		$pictid = empty($picposts) ? 0 : $picposts[0]->ID;
		$pictid = $picposts[0]->ID;
		$url = wp_get_attachment_url( $pictid );

	} else {
		if( empty($post_id) ) global $post;
		$post_id = $post->ID;

		$wc2_item = WC2_DB_Item::get_instance();
		$item_code = $wc2_item->get_item_value_by_post_id( $post_id, 'item_code' );
		if( !$item_code ) return false;

		if( 0 == $number ) {
			$pictid = (int)$wc2_item->get_mainpictid( $item_code );
			$url = wp_get_attachment_url( $pictid );
		} else {
			$pictids = $wc2_item->get_pictids( $item_code );
			$id = $number - 1;
			$pictid = ( isset($pictids[$id]) && (int)$pictids[$id] ) ? $pictids[$id] : 0;
			$url = wp_get_attachment_url( $pictid );
		}
	}
	return $url;
}

function wc2_the_item_image_url_e( $number = 0, $post = '' ) {
	$url = wc2_the_item_image_url( $number, $post );
	echo $url;
}

function wc2_the_item_image_caption( $number = 0, $post = '' ) {
	$ptitle = $number;

	if( $ptitle && 0 == (int)$number ) {
		$picposts = query_posts( array( 'post_type'=>'attachment', 'name'=>$ptitle ) );
		$excerpt = ( empty($picposts) ) ? '' : $picposts[0]->post_excerpt;

	} else {
		if( empty($post) ) global $post;
		$post_id = $post->ID;

		$wc2_item = WC2_DB_Item::get_instance();
		$item_code = $wc2_item->get_item_value_by_post_id( $post_id, 'item_code' );
		if( !$item_code ) return false;

		if( 0 == $number ) {
			$pictid = (int)$wc2_item->get_mainpictid( $item_code );
			$attach_ob = get_post( $pictid );
		} else {
			$pictids = $wc2_item->get_pictids( $item_code );
			$ind = $number - 1;
			$attach_ob = get_post( $pictids[$ind] );
		}
		$excerpt = $attach_ob->post_excerpt;
	}

	return $excerpt;
}

function wc2_the_item_image_caption_e( $number = 0, $post = '' ) {
	$caption = wc2_the_item_image_caption( $number, $post );
	echo esc_html( $caption );
}

function wc2_the_item_image_description( $number = 0, $post = '' ) {
	$ptitle = $number;

	if( $ptitle && 0 == (int)$number ) {
		$picposts = query_posts( array( 'post_type'=>'attachment', 'name'=>$ptitle ) );
		$excerpt = ( empty($picposts) ) ? '' : $picposts[0]->post_content;

	} else {
		if( empty($post) ) global $post;
		$post_id = $post->ID;

		$wc2_item = WC2_DB_Item::get_instance();
		$item_code = $wc2_item->get_item_value_by_post_id( $post_id, 'item_code' );
		if( !$item_code ) return false;

		if( 0 == $number ) {
			$pictid = (int)$wc2_item->get_mainpictid( $item_code );
			$attach_ob = get_post( $pictid );
		} else {
			$pictids = $wc2_item->get_pictids( $item_code );
			$ind = $number - 1;
			$attach_ob = get_post( $pictids[$ind] );
		}
		$excerpt = $attach_ob->post_content;
	}
	return $excerpt;
}

function wc2_the_item_image_description_e( $number = 0, $post = '' ) {
	$description = wc2_the_item_image_description( $number, $post );
	echo esc_html( $description );
}

function wc2_get_item_subimage_count() {
	global $post;
	$post_id = $post->ID;

	$wc2_item = WC2_DB_Item::get_instance();
	$item_code = $wc2_item->get_item_value_by_post_id( $post_id, 'item_code' );
	if( !$item_code ) return false;

	$res = array();
	$pictids = $wc2_item->get_pictids( $item_code );
	for( $i=1; $i<=count($pictids); $i++ ) {
		$res[] = $i;
	}
	return $res;
}

