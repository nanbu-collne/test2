<?php
/**
 * Welcart2.
 *
 * @package   Welcart2
 * @author    Collne Inc. <author@collne.com>
 * @license   GPL-2.0+
 * @link      http://www.welcart2.com
 * @copyright 2014 Collne Inc.
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-plugin-name-admin.php`
 *
 * @TODO: Rename this class to a proper name for your plugin.
 *
 * @package Plugin_Name
 * @author  Your Name <email@example.com>
 */
class Welcart2 {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0.1506081';

	/**
	 * @TODO - Rename "plugin-name" to the name of your plugin
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'wc2';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * post type slug.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	//protected $item_name, $category_name, $related_name, $tag_name;
	protected static $item_name = 'item';
	protected static $category_name = 'item';
	protected static $related_name = 'item-related';
	protected static $tag_name = 'item-tag';
	public $options;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		global $wpdb;

		do_action( 'wc2_construct' );

		add_action( 'after_setup_theme', array( &$this, 'wc2_session_start' ) );

		$this->load_options();
		$this->init_options();

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Make url
		//add_action( 'init', array($this, 'make_url') );
		
		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load admin style sheet and JavaScript.
		add_action( 'init', array( $this, 'enqueue_common_styles' ) );

		//  The slug for the product and taxonomy
		//self::$item_name = 'item';
		//self::$category_name = 'item';
		//self::$related_name = 'item-related';
		//self::$tag_name = 'item-tag';

		// Action-Hook to set product post-type
		add_action( 'init', array( $this, 'set_post_type' ) );

		// Action-Hook to set taxonomies of product
		add_action( 'init', array( $this, 'set_taxonomies' ) );

		// Action-Hook to set custom rules for the permalink
		add_action( 'init', array( $this, 'set_custom_rules' ) );

		// Filter-Hook to add custom rules for the permalink
		add_filter( 'rewrite_rules_array', array( $this, 'add_custom_rules' ) );

		// Filter-Hook to set contextual help for product admin pages
		add_filter( 'contextual_help', array( $this, 'custom_post_help' ), 10, 3 );

		//define( 'WC2_CART_NUMBER', wc2_get_option('wc2_cart_number') );
		//define( 'WC2_ITEM_CAT_PARENT_ID', wc2_get_option('wc2_item_cat_parent_id') );

		define( 'WC2_MYSQL_VERSION', $wpdb->db_version() );
		define( 'WC2_JP', ('ja' === get_locale() ? true : false) );
	}

	function wc2_session_start() {
		$options = wc2_get_option('wc2');
		$general_op = wc2_get_option('general');
		if( !isset($options['wc2_key']) || empty($options['wc2_key']) ){
			$options['wc2_key'] =  uniqid('uk');
			wc2_update_option( 'wc2', $options );
		}

		if(defined( 'WC2_KEY' )){
			if( is_admin() || preg_match('/\/wp-login\.php/', $_SERVER['REQUEST_URI']) ){
				session_name( 'adm'.WC2_KEY );
			}else{
				session_name( WC2_KEY );
			}
		}else{
			if( is_admin() || preg_match('/\/wp-login\.php/', $_SERVER['REQUEST_URI']) ){
				session_name( 'adm'.$options['wc2_key'] );
			}else{
				session_name( $options['wc2_key'] );
			}
		}

		@session_start();

		if ( !isset($_SESSION[WC2]['member']) || $general_op['membersystem_state'] != 'activate' ){
			$_SESSION[WC2]['member'] = array();
		}

		if( !isset($_SESSION[WC2]['checked_business_days']) )
			$this->update_business_days();
	}

	//営業日
	function update_business_days() {
		$general = wc2_get_option('general');
		$datetimestr = get_date_from_gmt(gmdate('Y-m-d H:i:s', time()));
		$dhour = (int)substr($datetimestr, 11, 2);
		$dminute = (int)substr($datetimestr, 14, 2);
		$dsecond = (int)substr($datetimestr, 17, 2);
		$dmonth = (int)substr($datetimestr, 5, 2);
		$dday = (int)substr($datetimestr, 8, 2);
		$dyear = (int)substr($datetimestr, 0, 4);
		$dtimestamp = mktime($dhour, $dminute, $dsecond, $dmonth, $dday, $dyear);
		$datenow = getdate($dtimestamp);
		//list($year, $mon, $mday) = wc2_get_beforemonth($datenow['year'], $datenow['mon'], $datenow['mday'], 1);
		list($year, $mon, $mday) = wc2_get_beforemonth($datenow['year'], $datenow['mon'], 1, 1);
		
		if(isset($general['business_days'][$year][$mon][1]))
			unset($general['business_days'][$year][$mon]);
		
		for($i=0; $i<12; $i++){
			//list($year, $mon, $mday) = wc2_get_aftermonth($datenow['year'], $datenow['mon'], $datenow['mday'], $i);
			list($year, $mon, $mday) = wc2_get_aftermonth($datenow['year'], $datenow['mon'], 1, $i);
			$last = wc2_get_lastday($year, $mon);
			for($j=1; $j<=$last; $j++){
				if(!isset($general['business_days'][$year][$mon][$j]))
					$general['business_days'][$year][$mon][$j] = 1;
			}
		}
		wc2_update_option('general', $general);

		$_SESSION[WC2]['checked_business_days'] = '';
	}

	public function load_options() {
		//WC2_Funcs::load_options();
		$this->options = WC2_Options::get_instance();
	}

	public function init_options() {

		$session_name = wc2_get_option( 'session_name' );
		if( empty($session_name) ) {
			$session_name = $this->plugin_slug . mt_rand(1000, 9999);
			wc2_update_option( 'session_name', $session_name );
		}

		/*-------------------------------
			基本設定オプション初期値
		---------------------------------*/
		$general = wc2_get_option('general');
		if(!isset($general['campaign_category'])) $general['campaign_category'] = 0;
		if(!isset($general['campaign_privilege'])) $general['campaign_privilege'] = '';
		if(!isset($general['privilege_point'])) $general['privilege_point'] = '';
		if(!isset($general['privilege_discount'])) $general['privilege_discount'] = '';

		if(!isset($general['company_name'])) $general['company_name'] = '';
		if(!isset($general['zip_code'])) $general['zip_code'] = '';
		if(!isset($general['address1'])) $general['address1'] = '';
		if(!isset($general['address2'])) $general['address2'] = '';
		if(!isset($general['tel_number'])) $general['tel_number'] = '';
		if(!isset($general['fax_number'])) $general['fax_number'] = '';
		if(!isset($general['order_mail'])) $general['order_mail'] = '';
		if(!isset($general['inquiry_mail'])) $general['inquiry_mail'] = '';
		if(!isset($general['sender_mail'])) $general['sender_mail'] = '';
		if(!isset($general['error_mail'])) $general['error_mail'] = '';
		if(!isset($general['copyright'])) $general['copyright'] = '';
		if(!isset($general['postage_privilege'])) $general['postage_privilege'] = '';
		if(!isset($general['purchase_limit'])) $general['purchase_limit'] = '';
		if(!isset($general['shipping_rule'])) $general['shipping_rule'] = '';
		if(!isset($general['tax_rate'])){
			$general['tax_rate'] = '';
			$general['tax_method'] = 'cutting';
			$general['tax_mode'] = 'include';
			$general['tax_target'] = 'products';
		}else{
			if(!isset($general['tax_mode'])) $general['tax_mode'] = empty($general['tax_rate']) ? 'include' : 'exclude';
			if(!isset($general['tax_target'])) $general['tax_target'] = 'all';
		}
		if(!isset($general['add2cart'])) $general['add2cart'] = '0';

		if(!isset($general['membersystem_state'])) $general['membersystem_state'] = 'activate';
		if(!isset($general['membersystem_point'])) $general['membersystem_point'] = 'activate';
		if(!isset($general['point_rate'])) $general['point_rate'] = '';
		if(!isset($general['start_point'])) $general['start_point'] = '';
		if(!isset($general['point_coverage'])) $general['point_coverage'] = 1;
		if(!isset($general['point_assign'])) $general['point_assign'] = 1;
		if(!isset($general['member_pass_rule_min']) || empty($general['member_pass_rule_min']) ) $general['member_pass_rule_min'] = 6;
		if(!isset($general['member_pass_rule_max']) || empty($general['member_pass_rule_max']) ) $general['member_pass_rule_max'] = '';

		if(!isset($general['indi_item_name'])){
			$general['indi_item_name']['item_name'] = 1;
			$general['indi_item_name']['item_code'] = 1;
			$general['indi_item_name']['sku_name'] = 1;
			$general['indi_item_name']['sku_code'] = 1;
			$general['pos_item_name']['item_name'] = 1;
			$general['pos_item_name']['item_code'] = 2;
			$general['pos_item_name']['sku_name'] = 3;
			$general['pos_item_name']['sku_code'] = 4;
		}

		wc2_update_option('general', $general);

		$cart_description = wc2_get_option( 'cart_description' );
		$member_description = wc2_get_option( 'member_description' );
		if(!isset($cart_description['cart_header'])) $cart_description['cart_header'] = array('top'=>'','customer'=>'','delivery'=>'','confirm'=>'','complete'=>'');
		if(!isset($cart_description['cart_footer'])) $cart_description['cart_footer'] = array('top'=>'','customer'=>'','delivery'=>'','confirm'=>'','complete'=>'');
		if(!isset($member_description['member_header'])) $member_description['member_header'] = array('login'=>'','newmemberform'=>'','lostpassword'=>'','changepassword'=>'','memberform'=>'','complete'=>'');
		if(!isset($member_description['member_footer'])) $member_description['member_footer'] = array('login'=>'','newmemberform'=>'','lostpassword'=>'','changepassword'=>'','memberform'=>'','complete'=>'');
		wc2_update_option( 'cart_description', $cart_description );
		wc2_update_option( 'member_description', $member_description );

		$this->check_display_mode();

		/*---------------------------------
			メール設定オプション初期値
		----------------------------------*/
		$phrase = wc2_get_option( 'phrase' );
		$phrase_default = wc2_get_option( 'phrase_default' );

		if( !isset($phrase['title']) ) {
			foreach ( (array)$phrase_default['title'] as $key => $value ) {
				$phrase['title'][$key] = $value;
			}
		}
		if( !isset($phrase['header']) ) {
			foreach ( (array)$phrase_default['header'] as $key => $value ) {
				$phrase['header'][$key] = $value;
			}
		}
		if( !isset($phrase['footer']) ) {
			foreach ( (array)$phrase_default['footer'] as $key => $value ) {
				$phrase['footer'][$key] = $value;
			}
		}
		wc2_update_option( 'phrase', $phrase );

		/*---------------------------------
			配送設定オプション初期値
		-----------------------------------/


		/*---------------------------------
			システム設定オプション初期値
		 ----------------------------------*/
		$system_options = wc2_get_option( 'system' );

		if( !isset($system_options['addressform']) ) $system_options['addressform'] = wc2_get_local_addressform(); //住所様式
		if( !isset($system_options['target_market']) ) $system_options['target_market'] = wc2_get_local_target_market(); //販売対象国
		if( !isset($system_options['mem_option_digit']) ) $system_options['mem_option_digit'] = 5; //会員コード桁数
		if( !isset($system_options['currency']) ) $system_options['currency'] = wc2_get_base_country();
		if( !isset($system_options['base_country']) ) $system_options['base_country'] = wc2_get_base_country();
		if( !isset($system_options['divide_item']) ) $system_options['divide_item'] = 0; //表示モード
		if( !isset($system_options['itemimg_anchor_rel']) ) $system_options['itemimg_anchor_rel'] = ''; //rel属性
		if( !isset($system_options['composite_category_orderby']) ) $system_options['composite_category_orderby'] = 'ID'; //複合カテゴリーソート項目
		if( !isset($system_options['composite_category_order']) ) $system_options['composite_category_order'] = 'ASC'; //複合カテゴリーソート順
		if( !isset($system_options['use_ssl']) ) $system_options['use_ssl'] = 0; //SSLを使用する
		if( !isset($system_options['ssl_url_admin']) ) $system_options['ssl_url_admin'] = ''; //WordPress のアドレス (SSL)
		if( !isset($system_options['ssl_url']) ) $system_options['ssl_url'] = ''; //ブログのアドレス (SSL)
		if( !isset($system_options['inquiry_id']) ) $system_options['inquiry_id'] = '';
		if( !isset($system_options['no_cart_css']) ) $system_options['no_cart_css'] = 0;
		if( !isset($system_options['dec_orderID_flag']) ) $system_options['dec_orderID_flag'] = 0;
		if( !isset($system_options['dec_orderID_prefix']) ) $system_options['dec_orderID_prefix'] = '';
		if( !isset($system_options['dec_orderID_digit']) ) $system_options['dec_orderID_digit'] = 8;
		if( !isset($system_options['subimage_rule']) ) $system_options['subimage_rule'] = 1;
		if( !isset($system_options['pdf_delivery']) ) $system_options['system']['pdf_delivery'] = 0;
		if( !isset($system_options['csv_encode_type']) ) $system_options['csv_encode_type'] = 0;

		//$system['currency'] = wc2_get_base_country();
		//$system['base_country'] = wc2_get_base_country();
		wc2_update_option( 'system', $system_options );

		/*------------------------------
			支払設定オプション初期値
		 -------------------------------*/
		//$payment_method = wc2_get_option( 'payment_method' );
		//if( !$payment_method ) $payment_method = array();
		$settlement_types = wc2_get_option( 'settlement_types' );
		if( !$settlement_types ) {
			$settlement_types = array(
				'BT' => __('Bank transfer', 'wc2'),
				'COD' => __('COD', 'wc2'),
			);
		}
		wc2_update_option( 'settlement_types', $settlement_types );

		$payment_info = wc2_get_option( 'payment_info' );
		if(!isset($payment_info['cod_type'])) $payment_info['cod_type'] = 'fix';
		wc2_update_option( 'payment_info', $payment_info );

		do_action( 'wc2_init_option' );
	}

	/**
	 * Register and enqueue common-specific style sheet.
	 *
	 * @TODO:
	 *
	 * - Rename "Plugin_Name" to the name your plugin
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_common_styles() {

		wp_enqueue_style( 'enqueue_common_styles', plugins_url( 'assets/css/common.css', __FILE__ ), array(), Welcart2::VERSION );

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
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

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();

					restore_current_blog();
				}

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

					restore_current_blog();

				}

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Action-Hook callback function to set product post-type
	 *
	 * @since    1.0.0
	 */
	public static function set_post_type() {
		$labels = array(
			'name' => __( 'Welcart Items', 'wc2' ),
			'singular_name' => __( 'Items List', 'wc2' ),
			'add_new' => '商品の新規追加',
			'add_new_item' => '商品の新規追加',
			'edit_item' => '商品の編集',
			'new_item' => __( 'Items', 'wc2' ),
			'view_item' => '商品を見る',
			'search_items' => 'タイトル・解説を検索',
			'not_found' =>  '商品がありません',
			'not_found_in_trash' => 'ゴミ箱に商品はありません', 
			'parent_item_colon' => '',
			'menu_name' => __( 'Welcart Items', 'wc2' )
		);
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true, 
			'show_in_menu' => true, 
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => true, 
			'hierarchical' => false,
			'menu_position' => null,
			//'supports' => array('title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes'), 
			'supports' => array( 'author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes'), 
			//'register_meta_box_cb' => array($this,'wc_add_meta_box_item')
		);
		register_post_type( self::$item_name, $args );
	}

	public static function set_taxonomies() {
		$labels = array(
			'name' => __( 'Items Category', 'wc2' ),
			'singular_name' => _x( 'Item', 'taxonomy singular name' ),
			'search_items' =>  '商品カテゴリーを検索',
			'all_items' => '全ての商品カテゴリー',
			'parent_item' => __( 'Parent Item' ),
			'parent_item_colon' => __( 'Parent Item:' ),
			'edit_item' => __( 'Edit Item' ), 
			'update_item' => __( 'Update Item' ),
			'add_new_item' => '商品カテゴリーを追加',
			'new_item_name' => __( 'New Item Name' ),
		);
		register_taxonomy( self::$category_name, array(self::$item_name), array(
			'hierarchical' => true,
			'labels' => $labels,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => self::$category_name ),
		));
		$labels = array(
			'name' => __( 'Related Item', 'wc2' ),
			'singular_name' => __( 'Related Item', 'wc2' ),
			'search_items' =>  __( 'Search Related' ),
			'popular_items' => __( 'Popular Related' ),
			'all_items' => __( 'All Related' ),
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __( 'Edit Related' ), 
			'update_item' => __( 'Update Related' ),
			'add_new_item' => __( 'Add New Related' ),
			'new_item_name' => __( 'New Related Name' ),
			'separate_items_with_commas' => __( 'Separate Related with commas' ),
			'add_or_remove_items' => __( 'Add or remove Related' ),
			'choose_from_most_used' => __( 'Choose from the most used Related' )
		);
		register_taxonomy( self::$related_name, self::$item_name, array(
			'hierarchical' => false,
			'labels' => $labels,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => self::$related_name ),
		));
		$labels = array(
			'name' => __( 'Tags' ),
			'singular_name' => __( 'Tags' ),
			'search_items' =>  __( 'Search Tags' ),
			'popular_items' => __( 'Popular Tags' ),
			'all_items' => __( 'All Tags' ),
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __( 'Edit Tag' ), 
			'update_item' => __( 'Update Tag' ),
			'add_new_item' => __( 'Add New Tag' ),
			'new_item_name' => __( 'New Tag Name' ),
			'separate_items_with_commas' => __( 'Separate writers with commas' ),
			'add_or_remove_items' => __( 'Add or remove tags' ),
			'choose_from_most_used' => __( 'Choose from the most used tags' )
		);
		register_taxonomy( self::$tag_name, self::$item_name, array(
			'hierarchical' => false,
			'labels' => $labels,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => self::$tag_name ),
		));
	}

	public function set_custom_rules() {
		global $wp_rewrite, $add_cutom_post_rules;

		if ( ! $wp_rewrite->using_permalinks() ) { return; }
		$structure = get_option('permalink_structure');
		preg_match('/\/[^%]*\//', $structure, $matches);
		$pre = empty($matches[0]) ? '/' . self::$category_name .'/' : $matches[0];
		$add_cutom_post_rules = array();
		$rule_templates = array(
			'/'                                                         => '',
			'/([0-9]{1,})/'                                             => '&p=$matches[1]',
			'/page/([0-9]{1,})/'                                        => '&paged=$matches[1]',
			'/date/([0-9]{4})/'                                         => '&year=$matches[1]',
			'/date/([0-9]{4})/page/([0-9]{1,})/'                        => '&year=$matches[1]&paged=$matches[2]',
			'/date/([0-9]{4})/([0-9]{2})/'                              => '&year=$matches[1]&monthnum=$matches[2]',
			'/date/([0-9]{4})/([0-9]{2})/page/([0-9]{1,})/'             => '&year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]',
			'/date/([0-9]{4})/([0-9]{2})/([0-9]{2})/'                   => '&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]',
			'/date/([0-9]{4})/([0-9]{2})/([0-9]{2})/page/([0-9]{1,})/'  => '&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]',
			$pre.'(\S*)'                                                => '&' . self::$category_name . '=$matches[1]',
		);
		$post_types = get_post_types( array( 'public' => true, 'show_ui' => true ), false );

		if ( $post_types ) {
			foreach ( $post_types as $post_type_slug => $post_type ) {
				if ( ! isset( $post_type->_builtin ) || ! $post_type->_builtin ) {
					foreach ( $rule_templates as $regex => $rule ) {
						$add_cutom_post_rules[ $post_type_slug . $regex . '?$' ] = $wp_rewrite->index . '?post_type=' . $post_type_slug . $rule;
					}
				}
			}
		}

		if ( $add_cutom_post_rules ) {
			$rules = $wp_rewrite->wp_rewrite_rules();
			foreach ( $add_cutom_post_rules as $regex => $rule ) {
				if ( ! isset( $rules[$regex] ) ) {
					$wp_rewrite->flush_rules();
					break;
				}
			}
		}
	}

	public function add_custom_rules( $rules ) {
		global $add_cutom_post_rules;
		if ( $add_cutom_post_rules && is_array( $add_cutom_post_rules ) ) {
			$rules = array_merge( $add_cutom_post_rules, $rules );
		}
		return $rules;
	}

	public function wc_add_meta_box_item( $post ) {
		if( !is_admin() )
			return;

		add_meta_box( 'meta_box_product_first_box', '商品情報', 'add_field_product_first_box', self::$item_name, 'normal', 'high' );
		add_meta_box( 'meta_box_product_second_box', '配送関連情報', 'add_field_product_second_box', self::$item_name, 'normal', 'high' );
		add_meta_box( 'itemsku', 'SKU情報', 'add_field_product_sku_box', self::$item_name, 'normal', 'high' );
		add_meta_box( 'itemoption', '商品オプション情報', 'add_field_product_option_box', self::$item_name, 'normal', 'high' );
		add_meta_box( 'pagecontent_box', '商品詳細ページ', 'add_field_product_pagecontent_box', self::$item_name, 'normal', 'high' );
		add_meta_box( 'meta_box_product_pict_box', '商品画像', 'add_field_product_pict_box', self::$item_name, 'side', 'high' );
	}

	/**
	 * Contextual help for custom post
	 *
	 * @since    1.0.0
	 */
	public function custom_post_help( $contextual_help, $screen_id, $screen ) {
		switch( $screen_id ){
			case 'edit-' . self::$item_name:
				$add_help1 = '<p><a href="http://youtu.be/wZzfM63-43k" target="_blank">http://youtu.be/wZzfM63-43k</a></p>';
				$add_help1 .= '<iframe width="560" height="315" src="//www.youtube.com/embed/wZzfM63-43k" frameborder="0" allowfullscreen></iframe>';
				$help_args1 = array(
					'title' => '動画マニュアル1',
					'id' => 'custom-help-movie1',
					'content' => $add_help1,
					'callback' => false,
				);
				$add_help2 = '<p><a href="http://youtu.be/YClsze3jTDQ" target="_blank">http://youtu.be/YClsze3jTDQ</a></p>';
				$add_help2 .= '<iframe width="560" height="315" src="//www.youtube.com/embed/YClsze3jTDQ" frameborder="0" allowfullscreen></iframe>';
				$help_args2 = array(
					'title' => '動画マニュアル2',
					'id' => 'custom-help-movie2',
					'content' => $add_help2,
					'callback' => false,
				);

				// タブの削除・追加
				$screen->remove_help_tabs();
				$screen->add_help_tab($help_args1);
				$screen->add_help_tab($help_args2);

				// サイドバーへの付け足し
				$old_sidebar = $screen->get_help_sidebar();
				$new_sidebar = $old_sidebar . '<p>サイドバーを書き換えた</p>';
				$screen->set_help_sidebar($new_sidebar);
			break;

			case self::$item_name:
				$add_help1 = '<p><a href="http://youtu.be/wZzfM63-43k" target="_blank">http://youtu.be/wZzfM63-43k</a></p>';
				$add_help1 .= '<iframe width="560" height="315" src="//www.youtube.com/embed/wZzfM63-43k" frameborder="0" allowfullscreen></iframe>';
				$help_args1 = array(
					'title' => '動画マニュアル1',
					'id' => 'custom-help-movie1',
					'content' => $add_help1,
					'callback' => false,
				);
				$add_help2 = '<p><a href="http://youtu.be/YClsze3jTDQ" target="_blank">http://youtu.be/YClsze3jTDQ</a></p>';
				$add_help2 .= '<iframe width="560" height="315" src="//www.youtube.com/embed/YClsze3jTDQ" frameborder="0" allowfullscreen></iframe>';
				$help_args2 = array(
					'title' => '動画マニュアル2',
					'id' => 'custom-help-movie2',
					'content' => $add_help2,
					'callback' => false,
				);

				// タブの削除・追加
				$screen->remove_help_tabs();
				$screen->add_help_tab($help_args1);
				$screen->add_help_tab($help_args2);

				// サイドバーへの付け足し
				$old_sidebar = $screen->get_help_sidebar();
				$new_sidebar = $old_sidebar . '<p>サイドバーを書き換えた</p>';
				$screen->set_help_sidebar($new_sidebar);
			break;

			case 'edit-' . self::$item_name:
			?>
			
			<?php
			break;

			default:
				echo $contextual_help;
		}
	}

	//active plugin
	public static function set_initial() {
		self::set_post_type();
		self::set_taxonomies();
//		$this->set_default_theme();
//		self::set_default_page();
		self::set_default_categories();
//		$this->create_table();
//		$this->update_table();
//		$rets07 = usces_upgrade_07();
//		$rets11 = usces_upgrade_11();
//		$rets14 = usces_upgrade_14();
//		$rets141 = usces_upgrade_141();
//		$rets143 = usces_upgrade_143();
//		$this->update_options();
	}

	public static function set_default_categories() {
/*		global $wpdb;

		$idObj = get_category_by_slug('item');

		if( empty($idObj) ) {
			$item_cat = array('cat_name' => __('商品', 'wc2'), 'category_description' => '', 'category_nicename' => 'item', 'category_parent' => 0);
			$item_cat_id = wp_insert_category($item_cat);
			wc2_update_option('wc2_item_cat_parent_id', $item_cat_id);
		}

		$idObj = get_category_by_slug('itemreco'); 
		if( empty($idObj) && isset($item_cat_id) ) {
			$itemreco_cat = array('cat_name' => __('お勧め商品', 'wc2'), 'category_description' => '', 'category_nicename' => 'itemreco', 'category_parent' => $item_cat_id);
			$itemreco_cat_id = wp_insert_category($itemreco_cat);	
		}

		$idObj = get_category_by_slug('itemnew'); 
		if( empty($idObj) && isset($item_cat_id) ) {
			$itemnew_cat = array('cat_name' => __('新商品', 'wc2'), 'category_description' => '', 'category_nicename' => 'itemnew', 'category_parent' => $item_cat_id);
			$itemnew_cat_id = wp_insert_category($itemnew_cat);	
		}

		$idObj = get_category_by_slug('itemgenre'); 
		if( empty($idObj) && isset($item_cat_id) ) {
			$itemgenre_cat = array('cat_name' => __('商品ジャンル', 'wc2'), 'category_description' => '', 'category_nicename' => 'itemgenre', 'category_parent' => $item_cat_id);
			$itemgenre_cat_id = wp_insert_category($itemgenre_cat);	
		}
*/

		if( !term_exists( 'item-reco', 'item' ) ) {
			wp_insert_term( __('お勧め商品', 'wc2'), 'item', array( 'slug' => 'item-reco', 'parent' => 0 ) );
			delete_option( 'item_children' );
		}
		if( !term_exists( 'item-new', 'item' ) ) {
			wp_insert_term( __('新商品', 'wc2'), 'item', array( 'slug' => 'item-new', 'parent' => 0 ) );
			delete_option( 'item_children' );
		}
		if( !term_exists( 'item-genre', 'item' ) ) {
			wp_insert_term( __('商品ジャンル', 'wc2'), 'item', array( 'slug' => 'item-genre', 'parent' => 0 ) );
			delete_option( 'item_children' );
		}
	}

	private function check_display_mode() {
		$general_options = wc2_get_option('general');
		if( isset($general_options['display_mode']) && $general_options['display_mode'] == 'Maintenancemode' ) return;

		$start['hour'] = empty($general_options['campaign_schedule']['start']['hour']) ? 0 : $general_options['campaign_schedule']['start']['hour'];
		$start['min'] = empty($general_options['campaign_schedule']['start']['min']) ? 0 : $general_options['campaign_schedule']['start']['min'];
		$start['month'] = empty($general_options['campaign_schedule']['start']['month']) ? 0 : $general_options['campaign_schedule']['start']['month'];
		$start['day'] = empty($general_options['campaign_schedule']['start']['day']) ? 0 : $general_options['campaign_schedule']['start']['day'];
		$start['year'] = empty($general_options['campaign_schedule']['start']['year']) ? 0 : $general_options['campaign_schedule']['start']['year'];
		$end['hour'] = empty($general_options['campaign_schedule']['end']['hour']) ? 0 : $general_options['campaign_schedule']['end']['hour'];
		$end['min'] = empty($general_options['campaign_schedule']['end']['min']) ? 0 : $general_options['campaign_schedule']['end']['min'];
		$end['month'] = empty($general_options['campaign_schedule']['end']['month']) ? 0 : $general_options['campaign_schedule']['end']['month'];
		$end['day'] = empty($general_options['campaign_schedule']['end']['day']) ? 0 : $general_options['campaign_schedule']['end']['day'];
		$end['year'] = empty($general_options['campaign_schedule']['end']['year']) ? 0 : $general_options['campaign_schedule']['end']['year'];
		$starttime = mktime($start['hour'], $start['min'], 0, $start['month'], $start['day'], $start['year']);
		$endtime = mktime($end['hour'], $end['min'], 0, $end['month'], $end['day'], $end['year']);
		$current_time = current_time('timestamp');

		if( ($current_time >= $starttime) && ($current_time <= $endtime) ){
			$general_options['display_mode'] = 'Promotionsale';
		}else{
			$general_options['display_mode'] = 'Usualsale';
		}

		wc2_update_option('general', $general_options);
	}

	//cookie
	static function set_cookie($values){
		$value = serialize($values);
		$timeout = time()+7*86400;
		$domain = $_SERVER['SERVER_NAME'];
		$res = setcookie('wc2_cookie', $value, $timeout, COOKIEPATH, $domain);
	}

	static function get_cookie() {
		$values = isset($_COOKIE['wc2_cookie']) ? unserialize(stripslashes($_COOKIE['wc2_cookie'])) : NULL;
		return $values;
	}

/*
	public static function set_default_page(){
		global $wpdb;
		$datetime = get_date_from_gmt(gmdate('Y-m-d H:i:s', time()));
		$datetime_gmt = gmdate('Y-m-d H:i:s', time());

		//cart_page
		$query = $wpdb->prepare("SELECT ID from $wpdb->posts where post_name = %s", WC2_CART_FOLDER);
		$cart_number = $wpdb->get_var( $query );
		if( $cart_number === NULL ) {
			$query = $wpdb->prepare("INSERT INTO $wpdb->posts 
				(post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, 
				comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, 
				post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count)
				VALUES (%d, %s, %s, %s, %s, %s, %s, 
				%s, %s, %s, %s, %s, %s, %s, %s, 
				%s, %d, %s, %d, %s, %s, %d)", 
				1, $datetime, $datetime_gmt, '', __('Cart', 'usces'), '', 'publish', 
				'closed', 'closed', '', WC2_CART_FOLDER, '', '', $datetime, $datetime_gmt, 
				'', 0, '', 0, 'page', '', 0);
			$wpdb->query($query);
			$cart_number = $wpdb->insert_id;
			if( $cart_number !== NULL ) {
				$query = $wpdb->prepare("INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES (%d, %s, %s)", 
					$cart_number, '_wp_page_template', 'wc2cart.php');
				$wpdb->query($query);
			}
		}
		update_option('wc2_cart_number', $cart_number);
		
		//member_page
		$query = $wpdb->prepare("SELECT ID from $wpdb->posts where post_name = %s", WC2_MEMBER_FOLDER);
		$member_number = $wpdb->get_var( $query );
		if( $member_number === NULL ) {
			$query = $wpdb->prepare("INSERT INTO $wpdb->posts 
				(post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, 
				comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, 
				post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count)
				VALUES (%d, %s, %s, %s, %s, %s, %s, 
				%s, %s, %s, %s, %s, %s, %s, %s, 
				%s, %d, %s, %d, %s, %s, %d)", 
				1, $datetime, $datetime_gmt, '', __('メンバー', 'wc2'), '', 'publish', 
				'closed', 'closed', '', WC2_MEMBER_FOLDER, '', '', $datetime, $datetime_gmt, 
				'', 0, '', 0, 'page', '', 0);
			$wpdb->query($query);
			$member_number = $wpdb->insert_id;
			if( $member_number !== NULL ) {
				$query = $wpdb->prepare("INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES (%d, %s, %s)", 
					$member_number, '_wp_page_template', 'wc2member.php');
				$wpdb->query($query);
			}
		}
		update_option('wc2_member_number', $member_number);
	}
*/

}

function wc2_set_cookie($values){
	$res = Welcart2::set_cookie($values);
	return $res;
}

function wc2_get_cookie(){
	$res = Welcart2::get_cookie();
	return $res;
}

?>
