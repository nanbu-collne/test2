<?php

/* Tables */
const TABLE_MEMBER = 'wc2_member';
const TABLE_MEMBER_META = 'wc2_membermeta';

/* Columns */
const MEMBER_ID = 'ID';
const MEMBER_ACCOUNT = 'mem_account';//ログインアカウント名
const MEMBER_EMAIL = 'mem_email';//メールアドレス
const MEMBER_PASSWD = 'mem_passwd';//パスワード
const MEMBER_RANK = 'mem_rank';//会員ランク
const MEMBER_POINT = 'mem_point';//ポイント
const MEMBER_NAME1 = 'mem_name1';//姓
const MEMBER_NAME2 = 'mem_name2';//名
const MEMBER_NAME3 = 'mem_name3';//姓（フリガナ）
const MEMBER_NAME4 = 'mem_name4';//名（フリガナ）
const MEMBER_COUNTRY = 'mem_country';//国
const MEMBER_ZIPCODE = 'mem_zipcode';//郵便番号
const MEMBER_PREF = 'mem_pref';//都道府県
const MEMBER_ADDRESS1 = 'mem_address1';//住所１（市区町村番地）
const MEMBER_ADDRESS2 = 'mem_address2';//住所２
const MEMBER_TEL = 'mem_tel';//電話番号
const MEMBER_FAX = 'mem_fax';//FAX番号
const MEMBER_REGISTERED = 'mem_registered';//会員登録日

class WC2_DB_Member
{
	/* Public variables */

	public $member_table = '';//Member table name
	public $member_meta_table = '';//Member meta table name

	/* Private variables */

	protected $member_id = '';
	protected $member_data = array();//Member data
	protected $member_list = array();//Member list data

	protected $member_table_column = array();//Member table column

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
	public function get_member_list_data( $args ) {
		global $wpdb;

		$select = "SELECT ";
		$column = $wpdb->prepare( "mem.ID, mem.mem_account, mem.mem_email, mem.mem_rank, mem.mem_point, CONCAT(mem.mem_name1, %s, mem.mem_name2) AS mem_name, CONCAT(mem.mem_name3, %s, mem.mem_name4) AS mem_name_furigana, mem.mem_country, mem.mem_zipcode, CONCAT(mem.mem_pref, mem.mem_address1, %s, mem.mem_address2) AS mem_address, mem.mem_tel, mem.mem_fax, mem.mem_registered ", " ", " ", "<br>" );
		$from = "FROM ". $this->member_table ." AS mem ";
		$join = "";
		$group = "";
		$having = "";
		if( array_key_exists('search_in', $_REQUEST) && array_key_exists('search_column', $_REQUEST) && array_key_exists('search_word', $_REQUEST) ){
			if( 'none' != $_REQUEST['search_column'] && !empty($_REQUEST['search_word']) ){
				switch( $_REQUEST['search_column'] ) {
				case 'mem_name':
					$search_word = preg_replace('/(\s|　)/', '', $_REQUEST['search_word']['keyword']);
					//$where = "WHERE mem.mem_name1 LIKE '%". esc_sql($search_word) ."%' OR mem.mem_name2 LIKE '%". esc_sql($search_word) ."%' ";
					$where = "WHERE CONCAT (mem.mem_name1, mem.mem_name2) LIKE '%".esc_sql($search_word) . "%' ";
					break;
				case 'mem_address':
					$search_word = preg_replace('/(\s|　)/', '', $_REQUEST['search_word']['keyword']);
					//$where = "WHERE CONCAT (mem.mem_address1, mem.mem_address2) LIKE '%". esc_sql($search_word) ."%' ";
					$where = "WHERE CONCAT (mem.mem_pref, mem.mem_address1, mem.mem_address2) LIKE '%".esc_sql($search_word)."%' ";
					break;
				case 'mem_rank':
					$where = "WHERE mem.mem_rank = '".esc_sql($_REQUEST['search_word']['mem_rank'])."' ";
					break;
				case 'mem_registered':
					if( preg_match("/^[0-9-:]+$/", $_REQUEST['search_word']['keyword']) ) {
						$where = "WHERE mem.mem_registered LIKE '%".esc_sql($_REQUEST['search_word']['keyword'])."%' ";
					} else {
						$where = "WHERE mem.mem_registered LIKE '%x%' ";
					}
					break;
				default:
					$search_word = preg_replace('/(\s|　)/', '', $_REQUEST['search_word']['keyword']);
					$where = "WHERE mem.".esc_sql($_REQUEST['search_column'])." LIKE '%".esc_sql($search_word)."%' ";
				}
			}else{
				$where = $wpdb->prepare( "WHERE %d ", 1 );
			}
		}else{
			$where = $wpdb->prepare( "WHERE %d ", 1 );
		}
		$order = "ORDER BY ". esc_sql($args['orderby']) ." ". esc_sql($args['order']);
		$limit = "";
		$query = $select . $column . $from . $join . $group . $having . $where . $order . $limit;

		$results = $wpdb->get_results( $query, ARRAY_A );

		return $results;
	}

	//And determine whether the member is present in the email that was put in argument
	public function is_member($email){
		global $wpdb;

		$query = $wpdb->prepare("SELECT * FROM $this->member_table WHERE mem_email = %s", $email);
		$member = $wpdb->get_row( $query, ARRAY_A );
		if( empty($member) ){
			return false;
		}else{
			return true;
		}
	}

	//Get member_id by email
	public function get_member_id_by_email( $email = '' ){
		global $wpdb;

		if( empty($email) ){
			return false;
		}
		$query = $wpdb->prepare( "SELECT ID FROM {$this->member_table} WHERE mem_email = %s LIMIT 1", $email );
		$id = $wpdb->get_var( $query);
		return $id;
	}

	//Get member id by account
	public function get_member_id_by_account( $account = '' ){
		global $wpdb;

		if( empty($account) ){
			return false;
		}
		$query = $wpdb->prepare( "SELECT ID FROM {$this->member_table} WHERE mem_account = %s LIMIT 1", $account );

		$id = $wpdb->get_var( $query);
		return $id;
	}

	//password check by account
	public function login_check_by_account( $account, $pass ){
		global $wpdb;

		if( empty($account) || empty($pass) ){
			return false;
		}
		$query = $wpdb->prepare("SELECT ID FROM {$this->member_table} WHERE mem_account = %s AND mem_passwd = %s LIMIT 1", $account, $pass);
		$id = $wpdb->get_var( $query );

		return $id;
	}

	//password check by email
	public function login_check_by_email( $email, $pass ){
		global $wpdb;

		if( empty($email) || empty($pass) ){
			return false;
		}
		$query = $wpdb->prepare("SELECT ID FROM {$this->member_table} WHERE mem_email = %s AND mem_passwd = %s LIMIT 1", $email, $pass);
		$id = $wpdb->get_var( $query );

		return $id;
	}

	// Get database values
	public function get_member_data( $member_id = '' ) {
		global $wpdb;

		if( !empty($member_id) ) $this->member_id = $member_id;
		if( empty($this->member_id) ) return array();

		$this->clear_column();

		//Member table
		$query = $wpdb->prepare( "SELECT * FROM {$this->member_table} WHERE ID = %d", $this->member_id );
		$data = $wpdb->get_row( $query, ARRAY_A );
		if( $data ) {
			$this->member_data['ID'] = $data['ID'];
			$this->member_data['account'] = $data['mem_account'];
			$this->member_data['email'] = $data['mem_email'];
			$this->member_data['passwd'] = $data['mem_passwd'];
			$this->member_data['rank'] = $data['mem_rank'];
			$this->member_data['point'] = $data['mem_point'];
			$this->member_data['name1'] = $data['mem_name1'];
			$this->member_data['name2'] = $data['mem_name2'];
			$this->member_data['name3'] = $data['mem_name3'];
			$this->member_data['name4'] = $data['mem_name4'];
			$this->member_data['country'] = $data['mem_country'];
			$this->member_data['zipcode'] = $data['mem_zipcode'];
			$this->member_data['pref'] = $data['mem_pref'];
			$this->member_data['address1'] = $data['mem_address1'];
			$this->member_data['address2'] = $data['mem_address2'];
			$this->member_data['tel'] = $data['mem_tel'];
			$this->member_data['fax'] = $data['mem_fax'];
			$this->member_data['registered'] = $data['mem_registered'];

			//Member meta table
			$query_meta = $wpdb->prepare( "SELECT * FROM {$this->member_meta_table} WHERE member_id = %d", $this->member_id );
			$data_meta = $wpdb->get_results( $query_meta, ARRAY_A );

			//meta
			foreach( (array)$data_meta as $meta ) {
				if( !empty($meta['meta_type']) ){
					if( $meta['meta_type'] == WC2_CUSTOM_MEMBER ) {
						$this->member_data[$meta['meta_type']][$meta['meta_key']] = maybe_unserialize( $meta['meta_value'] );
					} else {
						$this->member_data['meta_type'][$meta['meta_type']][$meta['meta_key']] = maybe_unserialize( $meta['meta_value'] );
					}
				}else{
					$this->member_data['meta_key'][$meta['meta_key']] = maybe_unserialize( $meta['meta_value'] );
				}
			}
		}
		return $this->member_data;
	}

	// Register database
	public function add_member_data() {
		global $wpdb;

		$member_table_column = $this->get_member_table_column();
		array_shift( $member_table_column );
		$member_column = implode( ',', $member_table_column );
		$res =array();
		$i = 0;

		do_action( 'wc2_action_add_member_data_pre', $member_column );

		$query = $wpdb->prepare( "INSERT INTO {$this->member_table}
				( {$member_column} ) 
			VALUES
				( %s, %s, %s, %d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s )",
			esc_sql( trim($this->member_data['account']) ),
			esc_sql( trim($this->member_data['email']) ),
			esc_sql( trim($this->member_data['passwd']) ),
			esc_sql( trim($this->member_data['rank']) ),
			esc_sql( trim($this->member_data['point']) ),
			esc_sql( trim($this->member_data['name1']) ),
			esc_sql( trim($this->member_data['name2']) ),
			esc_sql( trim($this->member_data['name3']) ),
			esc_sql( trim($this->member_data['name4']) ),
			esc_sql( trim($this->member_data['country']) ),
			esc_sql( trim($this->member_data['zipcode']) ),
			esc_sql( trim($this->member_data['pref']) ),
			esc_sql( trim($this->member_data['address1']) ),
			esc_sql( trim($this->member_data['address2']) ),
			esc_sql( trim($this->member_data['tel']) ),
			esc_sql( trim($this->member_data['fax']) ),
			get_date_from_gmt(gmdate('Y-m-d H:i:s', time()))
		);
		$res[$i] = $wpdb->query( $query );

		if( false !== $res[$i] ) {
			$this->member_id = $wpdb->insert_id;
			$this->member_data['ID'] = $this->member_id;

			if( array_key_exists( WC2_CUSTOM_MEMBER, $this->member_data ) ){
				foreach( $this->member_data[WC2_CUSTOM_MEMBER] as $key => $value ){
					$i++;
					$res[$i] = $this->reg_member_meta_data( $this->member_id, $key, $value, WC2_CUSTOM_MEMBER );
					if( false === $res[$i] ) break;
				}
			}
			if( array_key_exists( 'meta_type', $this->member_data ) ) {
				foreach( (array)$this->member_data['meta_type'] as $type => $data ) {
					foreach( (array)$data as $key => $value ) {
						$i++;
						$res[$i] = $this->reg_member_meta_data( $this->member_id, $key, $value, $type );
						if( false === $res[$i] ) break;
					}
				}
			}
			if( array_key_exists( 'meta_key', $this->member_data ) ) {
				foreach( (array)$this->member_data['meta_key'] as $key => $value ) {
					$i++;
					$res[$i] = $this->reg_member_meta_data( $this->member_id, $key, $value );
					if( false === $res[$i] ) break;
				}
			}
		}
		do_action( 'wc2_action_add_member_data', $this->member_id, $res, $i );

		if( in_array( false, $res, true ) ) {
			$result = -1;
		} else {
			$result = ( 0 < array_sum($res) ) ? 1 : 0;
		}

		return $result;
	}

	public function update_member_data( $member_id = '' ) {
		global $wpdb;

		if( !empty($member_id) ) $this->member_id = $member_id;
		if( empty($this->member_id) ) return false;
		$res =array();
		$i = 0;

		do_action( 'wc2_action_update_member_data_pre', $this->member_id );

		$query = $wpdb->prepare( "UPDATE {$this->member_table}
			SET mem_account = %s,
				mem_email = %s,
				mem_passwd = %s,
				mem_rank = %d,
				mem_point = %d,
				mem_name1 = %s,
				mem_name2 = %s,
				mem_name3 = %s,
				mem_name4 = %s,
				mem_country = %s,
				mem_zipcode = %s,
				mem_pref = %s,
				mem_address1 = %s,
				mem_address2 = %s,
				mem_tel = %s,
				mem_fax = %s
			WHERE ID = %d",
				esc_sql( trim($this->member_data['account']) ),
				esc_sql( trim($this->member_data['email']) ),
				esc_sql( trim($this->member_data['passwd']) ),
				esc_sql( trim($this->member_data['rank']) ),
				esc_sql( trim($this->member_data['point']) ),
				esc_sql( trim($this->member_data['name1']) ),
				esc_sql( trim($this->member_data['name2']) ),
				esc_sql( trim($this->member_data['name3']) ),
				esc_sql( trim($this->member_data['name4']) ),
				esc_sql( trim($this->member_data['country']) ),
				esc_sql( trim($this->member_data['zipcode']) ),
				esc_sql( trim($this->member_data['pref']) ),
				esc_sql( trim($this->member_data['address1']) ),
				esc_sql( trim($this->member_data['address2']) ),
				esc_sql( trim($this->member_data['tel']) ),
				esc_sql( trim($this->member_data['fax']) ),
				$this->member_id
		);
		$res[$i] = $wpdb->query( $query );
		if( false !== $res[$i] ) {
			if( array_key_exists( WC2_CUSTOM_MEMBER, $this->member_data ) ){
				foreach( $this->member_data[WC2_CUSTOM_MEMBER] as $key => $value ){
					$i++;
					$res[$i] = $this->reg_member_meta_data( $this->member_id, $key, $value, WC2_CUSTOM_MEMBER );
					if( false === $res[$i] ) break;
				}
			}
			if( array_key_exists( 'meta_type', $this->member_data ) ) {
				foreach( (array)$this->member_data['meta_type'] as $type => $data ) {
					foreach( (array)$data as $key => $value ) {
						$i++;
						$res[$i] = $this->reg_member_meta_data( $this->member_id, $key, $value, $type );
						if( false === $res[$i] ) break;
					}
				}
			}
			if( array_key_exists( 'meta_key', $this->member_data ) ) {
				foreach( (array)$this->member_data['meta_key'] as $key => $value ) {
					$i++;
					$res[$i] = $this->reg_member_meta_data( $this->member_id, $key, $value );
					if( false === $res[$i] ) break;
				}
			}
		}
		do_action( 'wc2_action_update_member_data', $this->member_id, $res, $i );

		if( in_array( false, $res, true ) ) {
			$result = -1;
		} else {
			$result = ( 0 < array_sum($res) ) ? 1 : 0;
		}
		return $result;
	}

	public function update_member_data_value( $member_id, $update_query ) {
		global $wpdb;
		$query_select = $wpdb->prepare( "SELECT COUNT(*) FROM {$this->member_table} WHERE ID = %d ", $member_id );
		if( 1 == $wpdb->get_var( $query_select ) ) {
			$query = $wpdb->prepare( "UPDATE {$this->member_table} SET {$update_query} WHERE ID = %d ", $member_id );
			$res = $wpdb->query( $query );
		}
		return $res;
	}

	// Update database
	// ( Update Member meta or register. )
	public function reg_member_meta_data( $member_id, $key, $value, $type = '' ) {
		global $wpdb;

		if( empty($member_id) ) return false;

		if( is_array($value) ) $value = serialize($value);

		if( '' == $type ) {
			$query_select = $wpdb->prepare( "SELECT count( meta_id ) FROM {$this->member_meta_table} WHERE member_id = %d AND meta_key = %s ",
				$member_id, $key
			);
		} else {
			$query_select = $wpdb->prepare( "SELECT count( meta_id ) FROM {$this->member_meta_table} WHERE member_id = %d AND meta_type = %s AND meta_key = %s ",
				$member_id, $type, $key
			);
		}
		if( 0 == $wpdb->get_var( $query_select ) ) {
			$query = $wpdb->prepare( "INSERT INTO {$this->member_meta_table} ( member_id, meta_type, meta_key, meta_value ) VALUES ( %d, %s, %s, %s )",
				$member_id, $type, $key, $value
			);
		} else {
			if( '' ==  $type ) {
				$query = $wpdb->prepare( "UPDATE {$this->member_meta_table} SET meta_value = %s WHERE member_id = %d AND meta_key = %s ",
					$value, $member_id, $key
				);
			} else {
				$query = $wpdb->prepare( "UPDATE {$this->member_meta_table} SET meta_value = %s WHERE member_id = %d AND meta_type = %s AND meta_key = %s ",
					$value, $member_id, $type, $key
				);
			}
		}
		$res = $wpdb->query( $query );

		return $res;
	}

	// Delete database
	public function delete_member_data( $member_id = '' ) {
		global $wpdb;

		if( !empty($member_id) ) $this->member_id = $member_id;
		if( empty($this->member_id) ) return false;
		$res = array();
		$i = 0;

		do_action( 'wc2_action_delete_member_data_pre', $this->member_id );

		$query = $wpdb->prepare( "DELETE FROM {$this->member_table} WHERE ID = %d", $this->member_id );
		$res[$i] = $wpdb->query( $query );
		if( false !== $res[$i] ) {
			$i++;
			$query_meta = $wpdb->prepare( "DELETE FROM {$this->member_meta_table} WHERE member_id = %d", $this->member_id );
			$res[$i] = $wpdb->query( $query_meta );
		}

		do_action( 'wc2_action_delete_member_data', $this->member_id, $res );

		$this->clear_column();

		if( in_array( false, $res, true ) ) {
			$result = -1;
		} else {
			$result = ( 0 < array_sum($res) ) ? 1 : 0;
		}
		return $result;
	}

	public function delete_member_meta_data($member_id, $key = '', $type = ''){
		global $wpdb;

		if( !empty($member_id) ) $this->member_id = $member_id;
		if( empty($this->member_id) ) return false;
		$res = array();

		do_action( 'wc2_action_delete_member_meta_data_pre', $this->member_id );

		$where = "member_id = '{$this->member_id}' ";
		if( !empty($key) ){
			$where .= "AND meta_key = '{$key}' ";
		}
		if( !empty($type) ){
			$where .= "AND meta_type = '{$type}' ";
		}
		$query_meta = "DELETE FROM {$this->member_meta_table} WHERE {$where}";
		$res = $wpdb->query( $query_meta );

		do_action( 'wc2_action_delete_member_meta_data', $this->member_id, $res );

		return $res;
	}

	public function get_member_data_value( $member_id, $key ) {
		global $wpdb;
		$query = $wpdb->prepare( "SELECT {$key} FROM {$this->member_table} WHERE ID = %d LIMIT 1", $member_id );
		$value = $wpdb->get_var( $query );

		return $value;
	}

	public function get_member_meta_value_results( $member_id, $key, $type = '' ) {
		global $wpdb;
		if( '' == $type ) {
			$query = $wpdb->prepare( "SELECT meta_type, meta_key, meta_value FROM {$this->member_meta_table} WHERE member_id = %d AND meta_key = %s",
				$member_id, $key
			);
		} elseif( '' == $key ) {
			$query = $wpdb->prepare( "SELECT meta_type, meta_key, meta_value FROM {$this->member_meta_table} WHERE member_id = %d AND meta_type = %s",
				$member_id, $type, $key
			);
		} else {
			$query = $wpdb->prepare( "SELECT meta_type, meta_key, meta_value FROM {$this->member_meta_table} WHERE member_id = %d AND meta_type = %s AND meta_key = %s",
				$member_id, $type, $key
			);
		}
		$value = $wpdb->get_results( $query, ARRAY_A );
		return $value;
	}

	public function get_member_meta_value( $member_id, $key, $type = '' ) {
		global $wpdb;
		if( '' == $type ) {
			$query = $wpdb->prepare( "SELECT meta_value FROM {$this->member_meta_table} WHERE member_id = %d AND meta_key = %s",
				$member_id, $key
			);
		} else {
			$query = $wpdb->prepare( "SELECT meta_value FROM {$this->member_meta_table} WHERE member_id = %d AND meta_type = %s AND meta_key = %s",
				$member_id, $type, $key
			);
		}
		$value = $wpdb->get_var($query);
		return $value;
	}


	//Change password
	public function changepassword($lostmail, $newpass){
		global $wpdb;

		if( empty($lostmail) || empty($newpass) ){
			return false;
		}
		$query = $wpdb->prepare("UPDATE {$this->member_table} SET mem_passwd = %s WHERE mem_email = %s", 
						md5($newpass), $lostmail);
		$res = $wpdb->query( $query );
		return $res;
	}

	// Create values
	public function clear_column() {
		$this->member_data = array();
	}

	// Set values
	public function set_member_id( $value ) {
		$this->member_id = $value;
	}
	public function set_value( $key, $value ) {
		$this->member_data[$key] = $value;
	}
	public function set_meta_value( $key, $value, $type = '' ) {
		if( is_array($value) ) $value = serialize($value);
		if( !empty($key) ) {
			if( !empty($type) ) {
				$this->member_data['meta_type'][$type][$key] = $value;
			} else {
				$this->member_data['meta_key'][$key] = $value;
			}
		}
	}

	// Get values
	public function get_member_id() {
		return $this->member_id;
	}
	public function get_value( $key ) {
		$value = ( is_array($this->member_data) and array_key_exists( $key, $this->member_data ) ) ? $this->member_data[$key] : '';
		$value = maybe_unserialize($value);
		return $value;
	}
	public function get_meta_value( $key, $type = '' ) {
		if( $type ){
			$value = ( isset($this->member_data['meta_type'][$type]) && array_key_exists( $key, $this->member_data['meta_type'][$type] ) ) ? $this->member_data['meta_type'][$type][$key] : '';
		}else{
			$value = ( isset($this->member_data['meta_key']) && array_key_exists( $key, $this->member_data['meta_key'] ) ) ? $this->member_data['meta_key'][$key] : '';
		}
		$value = maybe_unserialize($value);
		return $value;
	}

	public function get_member_table_column() {
		return $this->member_table_column;
	}

	public function is_member_table_column( $column ) {
		return in_array( $column, $this->get_member_table_column() );
	}

	/* Private functions */

	//Initial table name
	private function _set_table() {
		global $wpdb;
		$this->member_table = $wpdb->prefix.TABLE_MEMBER;
		$this->member_meta_table = $wpdb->prefix.TABLE_MEMBER_META;
	}

	//Initial column
	private function _set_item_column_init() {

		//*** Not be changed.
		$this->member_table_column = array( MEMBER_ID, MEMBER_ACCOUNT, MEMBER_EMAIL, MEMBER_PASSWD, MEMBER_RANK, MEMBER_POINT, MEMBER_NAME1, MEMBER_NAME2, MEMBER_NAME3, MEMBER_NAME4, MEMBER_COUNTRY, MEMBER_ZIPCODE, MEMBER_PREF, MEMBER_ADDRESS1, MEMBER_ADDRESS2, MEMBER_TEL, MEMBER_FAX, MEMBER_REGISTERED );
	}
}

function wc2_delete_member_data($member_id){
	$wc2_db_member = WC2_DB_Member::get_instance();
	return $wc2_db_member->delete_member_data($member_id);
}

function wc2_delete_member_meta_data($member_id, $key = '', $type = ''){
	$wc2_db_member = WC2_DB_Member::get_instance();
	return $wc2_db_member->delete_member_meta_data($member_id, $key, $type);
}

function wc2_get_member_id(){
	$wc2_db_member = WC2_DB_Member::get_instance();
	return $wc2_db_member->get_member_id();
}

function wc2_get_member_data($member_id){
	$wc2_db_member = WC2_DB_Member::get_instance();
	return $wc2_db_member->get_member_data($member_id);
}

function wc2_get_member_data_value( $member_id, $key ) {
	$wc2_member = WC2_DB_Member::get_instance();
	return $wc2_member->get_member_data_value( $member_id, $key );
}

function wc2_get_member_meta_value_results( $member_id, $key, $type = '' ) {
	$wc2_member = WC2_DB_Member::get_instance();
	return $wc2_member->get_member_meta_value_results( $member_id, $key, $type );
}

function wc2_get_member_meta_value( $member_id, $key, $type = '' ) {
	$wc2_member = WC2_DB_Member::get_instance();
	return $wc2_member->get_member_meta_value( $member_id, $key, $type );
}

function wc2_update_member_data($member_id){
	$wc2_db_member = WC2_DB_Member::get_instance();
	return $wc2_db_member->update_member_data($member_id);
}

function wc2_update_member_data_value($member_id, $update_query){
	$wc2_db_member = WC2_DB_Member::get_instance();
	return $wc2_db_member->update_member_data_value($member_id, $update_query);
}

function wc2_reg_member_meta_data($member_id, $key, $value, $type = ''){
	$wc2_db_member = WC2_DB_Member::get_instance();
	return $wc2_db_member->reg_member_meta_data($member_id, $key, $value, $type);
}

function wc2_set_member_id($value){
	$wc2_db_member = WC2_DB_Member::get_instance();
	return $wc2_db_member->set_member_id($value);
}

function wc2_db_member_set_value($key, $value){
	$wc2_db_member = WC2_DB_Member::get_instance();
	return $wc2_db_member->set_value($key, $value);
}

function wc2_db_member_set_meta_value($key, $value, $type = ''){
	$wc2_db_member = WC2_DB_Member::get_instance();
	return $wc2_db_member->set_meta_value($key, $value, $type);
}

function wc2_is_member($email){
	$wc2_db_member = WC2_DB_Member::get_instance();
	return $wc2_db_member->is_member($email);
}

function wc2_get_member_data_by_email( $email ) {
	$wc2_db_member = WC2_DB_Member::get_instance();
	$member_id = $wc2_db_member->get_member_id_by_email( $email );
	$member_data = ( $member_id ) ? $wc2_db_member->get_member_data( $member_id ) : array();
	return $member_data;
}
