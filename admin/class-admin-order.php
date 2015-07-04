<?php
/**
 * Welcart2.
 *
 * @package   WC2 WC2_Order
 * @author    Collne Inc. <author@welcart.com>
 * @license   GPL-2.0+
 * @link      http://www.welcart2.com
 * @copyright 2014 Collne Inc.
 */

if( !class_exists('WC2_Order_List_Table') )
	require_once( WC2_PLUGIN_DIR.'/admin/includes/class-order-list-table.php' );

if( !class_exists('WC2_PRINT') )
	require_once( WC2_PLUGIN_DIR.'/common/class-print.php' );

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-plugin-name.php`
 *
 * @TODO: Rename this class to a proper name for your plugin.
 *
 * @package WC2_Order
 * @author  Collne Inc. <author@welcart.com>
 */
class WC2_Order extends WC2_Admin_Page {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	//screen_optionに設定
	public static $per_page_slug = 'order_list_page';

	protected $mode = '';
	protected $page = '';
	protected $title = '';

	/***********************************
	 * Constructor
	 *
	 * @since     1.0.0
	 ***********************************/
	public function __construct() {
		parent::__construct();

		add_action( 'init', array( $this, 'pdfout_order' ) );
		add_action( 'wp_ajax_order_edit_ajax', array( $this, 'order_edit_ajax' ) );
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

	/***********************************
	 * Initial setting.
	 *
	 * @since     1.0.0
	 ***********************************/
	protected function init() {
		$admin_screen_label = wc2_get_option( 'admin_screen_label' );
		$this->title = $admin_screen_label['order'];

		do_action( 'wc2_action_admin_order_init' );
	}

	/***********************************
	 * Add a tab to the Contextual Help menu in an admin page.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function admin_help_setting( $help, $screen_id, $screen ) {
		if( !isset( $this->plugin_screen_hook_suffix ) or $this->plugin_screen_hook_suffix != $screen->id ) return;

		$tabs = array(
			array(
				'title' => $this->title.'一覧',
				'id' => 'order-list',
				'callback' => array( $this, 'get_help_order_list' )
			),
			array(
				'title' => $this->title.'データ編集',
				'id' => 'order-edit',
				'callback' => array( $this, 'get_help_order_edit' )
			),
		);

		foreach( $tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}
	}

	function get_help_order_list() {
		echo "<dl>
				<dt>受注一覧の詳細な説明</dt>
					<dd></dd>
				<dt></dt>
					<dd></dd>
			</dl>";
	}

	function get_help_order_edit() {
		echo "<dl>
				<dt>受注データ編集の詳細な説明</dt>
					<dd></dd>
				<dt></dt>
					<dd></dd>
			</dl>";
	}

	/***********************************
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function enqueue_admin_styles() {
		if( !isset( $this->plugin_screen_hook_suffix ) )
			return;

		parent::enqueue_admin_styles();
		$screen = get_current_screen();
		if( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'_admin_order_styles', plugins_url( 'assets/css/admin_order.css', __FILE__ ), array(), Welcart2::VERSION );
		}
	}

	/***********************************
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function enqueue_admin_scripts() {
		if( !isset( $this->plugin_screen_hook_suffix ) )
			return;

		parent::enqueue_admin_scripts();
		$screen = get_current_screen();
		if( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( 'ajaxzip3', 'http://ajaxzip3.googlecode.com/svn/trunk/ajaxzip3/ajaxzip3.js' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'post', admin_url().'js/post.js' );
			wp_enqueue_script( 'postbox', admin_url().'js/postbox.js' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
//*** LI CUSTOMIZE >>>
			wp_enqueue_script( 'li-scripts', get_template_directory_uri().'/js/admin.js' );
//*** LI CUSTOMIZE <<<
		}
	}

	/***********************************
	 * 表示オプションの表示制御
	 * @since    1.0.0
	 *
	 * NOTE:  $show_screen = 1は表示オプションを表示、0は非表示
	 ***********************************/
	public function admin_show_screen( $show_screen, $screen ) {
		if( !isset( $screen->id ) || false === strpos( $screen->id, 'toplevel_page_wc2_order' ) )
			return $show_screen;

		$action = ( isset($_REQUEST['action']) ) ? $_REQUEST['action'] : 'list';
		switch( $action ) {
			case 'new' :
			case 'edit':
				$show_screen = 0;
				break;
			case 'list':
			case 'delete':
			case 'delete_batch':
			default :
				$show_screen = 1;
				break;
		}
		$show_screen = apply_filters( 'wc2_filter_admin_order_show_screen', $show_screen );

		return $show_screen;
	}

	/***********************************
	 * リストの表示件数取得
	 * @since    1.0.0
	 *
	 * NOTE:  screen_options_show_screen にフックして、保存されたリストの表示件数を適用
	 ***********************************/
	public function admin_set_screen_options( $result, $option, $value ) {

		$order_list_screens = array( self::$per_page_slug );

		if( in_array( $option, $order_list_screens ) )
			$result = $value;

		return $result;
	}

	/***********************************
	 * Add a sub menu page.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function add_admin_menu() {
		$this->plugin_screen_hook_suffix = add_submenu_page( $this->menu_slug['management'], $this->title.'一覧', $this->title.'一覧', 'edit_pages', 'wc2_order', array( $this, 'admin_order_page' ) );
		add_action( 'load-' . $this->plugin_screen_hook_suffix, array( $this, 'load_order_action' ) );
	}

	/***********************************
	 * Runs when an administration menu page is loaded.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function load_order_action() {
		$current_screen = get_current_screen();

		//searchout
		if( array_key_exists( 'search_out', $_REQUEST ) && array_key_exists( 'search_column', $_REQUEST ) && array_key_exists( 'search_word', $_REQUEST ) ) {
			$referer = remove_query_arg( array( 'search_in', 'search_column', 'search_word' ), wp_unslash(wp_get_referer()) );
			wp_redirect( $referer );
			die();
		}

		//リストのカスタム・カラム（Order_List_Table::define_columns）をフック
		add_filter( 'manage_' . $current_screen->id . '_columns', array( 'WC2_Order_List_Table', 'define_columns' ) );

		//リストの表示件数設定（表示オプション内の件数フィールド）
		add_screen_option( 'per_page', array( 'label' => __( '件', 'wc2' ), 'default' => 10, 'option' => self::$per_page_slug ) );
	}

	/***********************************
	* PDF OUT
	************************************/
	public function pdfout_order() {

		if( isset($_REQUEST['action']) && 'pdfout_order' == $_REQUEST['action'] ) {
			$this->page = '';
			$wc2_print = WC2_PRINT::get_instance();
			$wc2_print->print_process();
			die();
		}
		do_action( 'wc2_action_admin_order_pdfout' );
	}

	/***********************************
	 * The function to be called to output the content for this page.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function admin_order_page() {
		if( !isset( $this->plugin_screen_hook_suffix ) )
			return;

		$screen = get_current_screen();
		if( $this->plugin_screen_hook_suffix != $screen->id )
			return;

		if( isset($_REQUEST['action']) ) {
			$this->mode = $_REQUEST['action'];
		} else {
			$this->mode = 'list';
		}

		$wc2_order = WC2_DB_Order::get_instance();

		switch( $this->mode ) {
			case 'new':
				$title = '新規'.$this->title.'登録';

				$order_id = '';
				$dec_order_id = '';
				$data = $this->get_post_data();
				$data['ID'] = '';
				$data['dec_order_id'] = '--------------';
				$cart = array();
				$cart_row = 1;
				$item_total_price = 0;

				$order_date = wc2_get_today();

				$order_action = 'register';
				$this->page = 'order-post';
				break;

			case 'edit':
				$title = $this->title.'編集';

				if( isset( $_REQUEST['order_action'] ) and 'register' == $_REQUEST['order_action'] ) {
					check_admin_referer( 'wc2_order_post', 'wc2_nonce' );

					$data = $this->get_post_data();

					if( $this->check_order_data( $data ) ) {

						$data = apply_filters( 'wc2_filter_admin_order_register_order_data', $data );
						do_action( 'wc2_action_admin_order_register_pre' );

						$wc2_order->set_order_data( $data );
						$res = $wc2_order->add_order_data();
						if( 1 == $res ) {
							$order_id = $wc2_order->get_the_order_id();
							$args = array( 'data' => $data, 'order_id' => $order_id );
							wc2_set_dec_order_id( $args );
							$this->action_status = 'success';
							$this->action_message = __('登録しました','wc2');
						} else {
							$this->action_status = 'error';
							$this->action_message = __('登録に失敗しました','wc2');
						}
					} else {
						$this->action_status = 'error';
						$this->action_message = __('データに不備があります','wc2');
					}

				} elseif( isset( $_REQUEST['order_action'] ) and 'update' == $_REQUEST['order_action'] ) {
					check_admin_referer( 'wc2_order_post', 'wc2_nonce' );

					$order_id = ( isset($_REQUEST['order_id']) ) ? $_REQUEST['order_id'] : '';
					$data = $this->get_post_data();
					if( $this->check_order_data( $data ) ) {

						$data = apply_filters( 'wc2_filter_admin_order_update_order_data', $data );
						do_action( 'wc2_action_admin_order_update_pre' );

						$wc2_order->set_order_id( $order_id );
						$wc2_order->set_order_data( $data );
						$res = $wc2_order->update_order_data();
						if( 1 == $res ) {
							$this->action_status = 'success';
							$this->action_message = __( 'Updated!' );
						} elseif( 0 === $res ) {
							$this->action_status = 'none';
							$this->action_message = '';
						} else {
							$this->action_status = 'error';
							$this->action_message = __( 'Update Failed' );
						}
					} else {
						$this->action_status = 'error';
						$this->action_message = __('データに不備があります','wc2');
					}

				} else {
					$order_id = ( isset($_REQUEST['target']) ) ? $_REQUEST['target'] : '';
				}

				$data = wc2_get_order_data( $order_id );
				$dec_order_id = ( isset($data['dec_order_id']) ) ? $data['dec_order_id'] : '';
				$cart = ( !empty($data['cart']) ) ? $data['cart'] : array();
				$cart_row = ( 0 < count($cart) ) ? max( array_keys( $cart ) ) + 1 : 1;
				$item_total_price = wc2_get_item_total_price( $cart );
				$order_date_time = explode(" ", $data[ORDER_DATE]);
				$order_date = explode("-", $order_date_time[0]);

				$order_action = 'update';
				$this->page = 'order-post';
				break;

//*** LI CUSTOMIZE >>>
			case 'edit-mode':
				$title = $this->title.'編集';

				$order_id = ( isset($_REQUEST['order_id']) ) ? $_REQUEST['order_id'] : '';
				$dec_order_id = ( isset($_REQUEST['dec_order_id']) ) ? $_REQUEST['dec_order_id'] : '';
				$data = $this->get_post_data();
				$cart = ( !empty($data['cart']) ) ? $data['cart'] : array();
				$cart_row = ( 0 < count($cart) ) ? max( array_keys( $cart ) ) + 1 : 1;
				$item_total_price = wc2_get_item_total_price( $cart );
				$order_date_time = explode(" ", $data[ORDER_DATE]);
				$order_date = explode("-", $order_date_time[0]);

				$order_action = 'update';
				$this->page = 'order-post';
				$this->mode = 'edit';
				break;
//*** LI CUSTOMIZE <<<

			case 'delete':
				check_admin_referer( 'wc2_order_list', 'wc2_nonce' );
				if( isset( $_REQUEST['target'] ) && !empty( $_REQUEST['target'] ) ) {
					$res = self::delete_order_data( $_REQUEST['target'] );
				}
				$this->page = 'order-list';
				break;

			case 'delete_batch':
				check_admin_referer( 'wc2_order_list', 'wc2_nonce' );
				if( isset( $_REQUEST['order_tag'] ) && !empty( $_REQUEST['order_tag'] ) ) {
					$res = self::delete_batch_order_data( $_REQUEST['order_tag'] );
				}
				$this->page = 'order-list';
				break;

			case 'dl_orderdetail_list':
				check_admin_referer( 'wc2_dl_orderdetail_list', 'wc2_nonce' );
				$this->download_order_detail_list();
				$this->page = '';
				break;

			case 'dl_order_list':
				check_admin_referer( 'wc2_dl_order_list', 'wc2_nonce' );
				$this->download_order_list();
				$this->page = '';
				break;

			case 'list';
			default:
				$this->page = 'order-list';
				break;
		}

		do_action( 'wc2_action_admin_order_page', array( $this ) );

		$order_status = wc2_get_option( 'management_status' );
		$receipt_status = wc2_get_option( 'receipt_status' );
		$order_type = wc2_get_option( 'order_type' );

		//受注リスト
		if( $this->page == 'order-list' ) {

			$order_list = new WC2_Order_List_Table();
			$order_list->prepare_items();

			$order_refine_period = wc2_get_option( 'order_refine_period' );
			$search_period = ( isset($_SESSION[WC2][$this->page]['search_period']) ) ? $_SESSION[WC2][$this->page]['search_period'] : 3;
			$startdate = ( isset($_SESSION[WC2][$this->page]['startdate']) ) ? $_SESSION[WC2][$this->page]['startdate'] : '';
			$enddate = ( isset($_SESSION[WC2][$this->page]['enddate']) ) ? $_SESSION[WC2][$this->page]['enddate'] : '';

			$search_column_key = ( isset($_REQUEST['search_column']) ) ? $_REQUEST['search_column'] : '';
			$search_word = '';
			$search_word_key = '';
			switch( $search_column_key ) {
			case 'none':
				break;
			case 'order_status':
				$search_word_key = ( isset($_REQUEST['search_word']['order_status']) ) ? $_REQUEST['search_word']['order_status'] : '';
				if( array_key_exists( $search_word_key, $order_status ) ) $search_word = $order_status[$search_word_key];
				break;
			case 'receipt_status':
				$search_word_key = ( isset($_REQUEST['search_word']['receipt_status']) ) ? $_REQUEST['search_word']['receipt_status'] : '';
				if( array_key_exists( $search_word_key, $receipt_status ) ) $search_word = $receipt_status[$search_word_key];
				break;
			case 'order_type':
				$search_word_key = ( isset($_REQUEST['search_word']['order_type']) ) ? $_REQUEST['search_word']['order_type'] : '';
				if( array_key_exists( $search_word_key, $order_type ) ) $search_word = $order_type[$search_word_key];
				break;
			default:
				if( isset($_REQUEST['search_word']['keyword']) ) $search_word = $_REQUEST['search_word']['keyword'];
			}

			$search_columns = $order_list->define_columns();
			unset( $search_columns['cb'] );
			unset( $search_columns['total_price'] );
			$search_columns['item_code'] = __('Item code', 'wc2');
			$search_columns['item_name'] = __('Item name', 'wc2');
			$search_columns = apply_filters( 'wc2_filter_admin_order_list_search_columns', $search_columns );

			$opt_order = wc2_get_option( 'opt_order' );
			$chk_order = ( !empty($opt_order['chk_order']) ) ? $opt_order['chk_order'] : array();
			$chk_detail = ( !empty($opt_order['chk_detail']) ) ? $opt_order['chk_detail'] : array();

			$system_options = wc2_get_option( 'system' );
			$applyform = wc2_get_apply_addressform( $system_options['addressform'] );

		//受注編集画面
		} elseif( $this->page == 'order-post' ) {

			$status = $this->action_status;
			$message = $this->action_message;

			$general_options = wc2_get_option( 'general' );
			$payment_method = wc2_get_option( 'payment_method' );
			$delivery_options = wc2_get_option( 'delivery' );
			$delivery_method = ( isset($delivery_options['delivery_method']) ) ? $delivery_options['delivery_method'] : array();
			$delivery_after_days = apply_filters( 'wc2_filter_delivery_after_days', ( !empty($delivery_options['delivery_after_days']) ? (int)$delivery_options['delivery_after_days'] : 100 ) );

			$order_condition = maybe_unserialize( $data[ORDER_CONDITION] );
			$order_check = maybe_unserialize( $data[ORDER_CHECK] );
		}

		$order_page = apply_filters( 'wc2_filter_admin_order_page', WC2_PLUGIN_DIR.'/admin/views/'.$this->page.'.php' );
		require_once( $order_page );
	}

	/***********************************
	 * The function to be called to output the script source for this page.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function admin_page_scripts() {
		if( !isset( $this->plugin_screen_hook_suffix ) )
			return;

		$screen = get_current_screen();
		if( $this->plugin_screen_hook_suffix != $screen->id )
			return;

		$delivery_options = wc2_get_option( 'delivery' );
		$delivery_method = ( isset($delivery_options['delivery_method']) ) ? $delivery_options['delivery_method'] : array();
		$phrase = wc2_get_option( 'phrase' );
?>
<script type="text/javascript">
jQuery(function($) {
<?php //受注リスト画面
	if( $this->page == 'order-list' ) : 
		$startdate = ( !empty($_SESSION[WC2][$this->page]['startdate']) ) ? ', setDate: "'.$_SESSION[WC2][$this->page]['startdate'].'", defaultDate: "'.$_SESSION[WC2][$this->page]['startdate'].'"' : '';
		$enddate = ( !empty($_SESSION[WC2][$this->page]['enddate']) ) ? ', setDate: "'.$_SESSION[WC2][$this->page]['enddate'].'", defaultDate: "'.$_SESSION[WC2][$this->page]['enddate'].'"' : '';
?>
	//$("#navi-box").css("display", "none");
	//$("#navi-box-link").click(function() {
	//	$("#navi-box").toggle();
	//});

	//batch
	$(".action").click( function() {
		var idname = $(this).attr("id");
		var pos = ( idname.substr(-1,1) == "2" ) ? "bottom" : "top";
		if( "-1" == $("#bulk-action-selector-"+pos+" option:selected").val() ){
			alert("操作を選択してください。");
			return false;
		}
		if( "delete_batch" == $("#bulk-action-selector-"+pos+" option:selected").val() ) {
			if( $("input[name='order_tag[]']:checked").length > 0 ) {
				if( !confirm("チェックしたデータを削除します。よろしいですか？") ) {
					return false;
				}
			} else {
				alert("削除するデータをチェックしてください。");
				return false;
			}
		}
	});

	//delete
	$(".delete-order").click( function() {
		var delete_id = $(this).attr("id").replace("delete-", "");
		if( !confirm("注文番号 " + delete_id + " を削除します。よろしいですか？") ) {
			return false;
		}
	});

	//search
	$("#search-in").click( function() {
		var search_column = $("#search-column option:selected").val();
		switch( search_column ) {
		case "none":
			alert("検索項目を選択してください。");
			return false;
			break;
		case "order_status":
		case "receipt_status":
		case "order_type":
			break;
		default:
			if( "" == $("#search-word-keyword").val() ) {
				alert("検索するキーワードを入力してください。");
				return false;
			}
		}
	});

	$("#search-column").change( function() {
		var search_column = $("#search-column option:selected").val();
		orderList.selectSearchColumn(search_column);
	});

	//受注明細データ出力
	$("#dlOrderDetailListDialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 400,
		width: 700,
		resizable: true,
		modal: true,
		buttons: {
			"<?php _e('Close'); ?>": function() {
				$(this).dialog("close");
			}
		},
		close: function() {
		}
	});

	$("#dl-detail").click(function() {
<?php
	ob_start();
?>
		var search_column = $("#search-column option:selected").val();
		var search_word = "";
		switch( search_column ) {
		case "order_status":
			search_word = $("#search-word-order_status option:selected").val();
			break;
		case "receipt_status":
			search_word = $("#search-word-receipt_status option:selected").val();
			break;
		case "order_type":
			search_word = $("#search-word-order_type option:selected").val();
			break;
		default:
			search_word = $("#search-word-keyword").val();
		}
		var args = "&search[column]="+search_column
			+"&search[word]="+search_word
			+"&search[period]="+$("#search-period option:selected").val()
			+"&search[startdate]="+$("#startdate").val()
			+"&search[enddate]="+$("#enddate").val();
<?php
	$admin_order_detail_list_args_script = ob_get_contents();
	ob_end_clean();
	echo apply_filters( 'wc2_filter_admin_order_detail_list_args_script', $admin_order_detail_list_args_script );
?>
		$(".check-detail").each(function(i) {
			if( $(this).attr("checked") ) {
				args += "&check["+$(this).val()+"]=on";
			}
		});
		location.href = "<?php echo WC2_ADMIN_URL; ?>?page=wc2_order&action=dl_orderdetail_list&noheader=true"+args+"&wc2_nonce=<?php echo wp_create_nonce( 'wc2_dl_orderdetail_list' ); ?>";
	});

	$("#dl-orderdetail-list").click(function() {
		$("#dlOrderDetailListDialog").dialog("open");
	});

	//受注データ出力
	$("#dlOrderListDialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 600,
		width: 700,
		resizable: true,
		modal: true,
		buttons: {
			"<?php _e('Close'); ?>": function() {
				$(this).dialog("close");
			}
		},
		close: function() {
		}
	});

	$("#dl-order").click(function() {
<?php
	ob_start();
?>
		var search_column = $("#search-column option:selected").val();
		var search_word = "";
		switch( search_column ) {
		case "order_status":
			search_word = $("#search-word-order_status option:selected").val();
			break;
		case "receipt_status":
			search_word = $("#search-word-receipt_status option:selected").val();
			break;
		case "order_type":
			search_word = $("#search-word-order_type option:selected").val();
			break;
		default:
			search_word = $("#search-word-keyword").val();
		}
		var args = "&search[column]="+search_column
			+"&search[word]="+search_word
			+"&search[period]="+$("#search-period option:selected").val()
			+"&search[startdate]="+$("#startdate").val()
			+"&search[enddate]="+$("#enddate").val();
<?php
	$admin_order_list_args_script = ob_get_contents();
	ob_end_clean();
	echo apply_filters( 'wc2_filter_admin_order_list_args_script', $admin_order_list_args_script );
?>
		$(".check-order").each(function(i) {
			if( $(this).attr("checked") ) {
				args += "&check["+$(this).val()+"]=on";
			}
		});
		location.href = "<?php echo WC2_ADMIN_URL; ?>?page=wc2_order&action=dl_order_list&noheader=true"+args+"&wc2_nonce=<?php echo wp_create_nonce( 'wc2_dl_order_list' ); ?>";
	});

	$("#dl-order-list").click(function() {
		$("#dlOrderListDialog").dialog("open");
	});

	$("#startdate").datepicker({
		dateFormat: "yy-mm-dd"<?php echo $startdate; ?>
	});

	$("#enddate").datepicker({
		dateFormat: "yy-mm-dd"<?php echo $enddate; ?>
	});

	$("#search-period").change(function() {
		var period = $("#search-period option:selected").val();
		if( period == 5 ) {
			$("#period-specified").css("display", "inline-block");
		} else {
			$("#period-specified").css("display", "none");
		}
	});
	//$("#search-period").triggerHandler("change");

	orderList = {
		selectSearchColumn : function( search_column ) {
			switch( search_column ) {
			case "order_status":
				$("#search-label").css("display", "none");
				$("#search-word-keyword").css("display", "none");
				$("#search-word-order_status").css("display", "inline-block");
				$("#search-word-receipt_status").css("display", "none");
				$("#search-word-order_type").css("display", "none");
				break;
			case "receipt_status":
				$("#search-label").css("display", "none");
				$("#search-word-keyword").css("display", "none");
				$("#search-word-order_status").css("display", "none");
				$("#search-word-receipt_status").css("display", "inline-block");
				$("#search-word-order_type").css("display", "none");
				break;
			case "order_type":
				$("#search-label").css("display", "none");
				$("#search-word-keyword").css("display", "none");
				$("#search-word-order_status").css("display", "none");
				$("#search-word-receipt_status").css("display", "none");
				$("#search-word-order_type").css("display", "inline-block");
				break;
			default:
				$("#search-label").css("display", "inline-block");
				$("#search-word-keyword").css("display", "inline-block");
				$("#search-word-order_status").css("display", "none");
				$("#search-word-receipt_status").css("display", "none");
				$("#search-word-order_type").css("display", "none");
			}
		}
	};
<?php do_action( 'wc2_action_admin_order_list_scripts' ); ?>
<?php //受注データ編集画面
	elseif( $this->page == 'order-post' ): 
		$data = wc2_get_the_order_data();
//*** LI CUSTOMIZE >>>
		if( empty($data) ) $data = $this->get_post_data();
//*** LI CUSTOMIZE <<<
?>
	var selected_delivery_time = "<?php esc_html_e( isset($data['delivery_time']) ? $data['delivery_time'] : '' ); ?>";
	var delivery_time = [];
<?php
	foreach( (array)$delivery_method as $dmid => $dm ) :
		$lines = explode("\n", $dm['time']);
?>
	delivery_time[<?php echo $dm['id']; ?>] = [];
<?php
		foreach( (array)$lines as $line ) :
			if( trim($line) != '' ) :
?>
	delivery_time[<?php echo $dm['id']; ?>].push("<?php echo trim($line); ?>");
<?php		endif;
		endforeach;
	endforeach;
?>
	$("#addItemDialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 500,
		width: 700,
		resizable: true,
		modal: true,
		appendTo:"#dialog-parent",
		close: function() {
			$("#additem-category").val("-1");
			$("#additem-form").html("");
			$("#additem-select").val("");
		}
	});

	$("#additem").click(function() {
		if( $("#order_id").val() == "" ) {
			alert("<?php _e('「設定を更新」を押して注文Noを確定してください。', 'wc2'); ?>");
			return;
		}
		$("#addItemDialog").dialog("open");
	});

	$(document).on( "change", "#additem-category", function() {
		orderItem.getSelectItem( $(this).val() );
	});

	$(document).on( "change", "#additem-select", function() {
		orderItem.getItem( $(this).val() );
	});

	$("#getitem").click(function() {
		if( $("#additem-code").val() == "" ) return;
		orderItem.getItem( encodeURIComponent($("#additem-code").val()) );
	});

	$(document).on( "change", "#additem-code", function() {
		orderItem.getItem( $(this).val() );
	});

//*** LI CUSTOMIZE >>>
	//$("#delivery_method_select").change(function() {
	$(document).on( "change", "#delivery_method_select", function() {
//*** LI CUSTOMIZE <<<
		$("#delivery_name").val($("#delivery_method_select option:selected").text());
		orderFunc.makeDeliveryTime($("#delivery_method_select option:selected").val());
//*** LI CUSTOMIZE >>>
		var p = $("input[name*='sku_price']");
		var q = $("input[name*='quantity']");
		var ci = $("input[name*='cart_id']");
		var cart_ids = "";
		var prices = "";
		var quantities = "";
		for( var i = 0; i < p.length; i++ ) {
			cart_ids += $(ci[i]).val()+"<?php echo WC2_SPLIT; ?>";
			prices += parseFloat($(p[i]).val())+"<?php echo WC2_SPLIT; ?>";
			quantities += $(q[i]).val()+"<?php echo WC2_SPLIT; ?>";
		}

		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: {
				action: "order_edit_ajax",
				mode: "get-shipping-charge",
				delivery_method: $("#delivery_method_select option:selected").val(),
				delivery_pref: $("#delivery_pref option:selected").val(),
				order_id: $("#order_id").val(),
				cart_ids: cart_ids,
				prices: prices,
				quantities: quantities
			}
		}).done(function( retVal, dataType ) {
			var data = retVal.split("<?php echo WC2_SPLIT; ?>");
			if( data[0] == "OK" ) {
				$("#shipping_charge").val(data[1]);
				orderFunc.sumPrice(null);
			}
		}).fail(function( retVal ) {
		});
		return false;
//*** LI CUSTOMIZE <<<
	});

	$("#payment_method_select").change(function() {
		$("#payment_name").val($("#payment_method_select option:selected").text());
	});

	$(document).on("click", ".additem-sku", function() {
		var idname = $(this).attr("id");
		var ids = idname.split("-");
		var id = ids[2];
		var additem = $("#additem-item_id-"+id).val();
		var addsku = $("#additem-sku_id-"+id).val();
		var addquantity = $("#additem-quantity-"+id).val();
		orderItem.add2cart( additem, addsku, addquantity );
	});

	$("#sendMailDialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 650,
		width: 700,
		resizable: true,
		modal: true,
		buttons: {
			"<?php _e('Close'); ?>": function() {
				$(this).dialog("close");
			}
		},
		appendTo:"#dialog-parent",
		close: function() {
			$("#sendmail-message").html("");
			$("#sendmail-address").val("");
		}
	});

	$("#sendMailAlert").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 200,
		width: 200,
		resizable: false,
		appendTo: "#sendMailDialog",
		modal: false
	});

	$("#sendmail").click(function() {
		orderMail.sendMail();
	});

	$("#mail-completion").click(function() {
		orderMail.getMailMessage("mail_completion");
		$("#sendmail-address").val($("input[name='customer[email]']").val());
		$("#sendmail-name").val($("input[name='customer[name1]']").val()+$("input[name='customer[name2]']").val());
		$("#sendmail-subject").val("<?php echo esc_js(apply_filters('wc2_filter_admin_order_mail_subject', $phrase['title']['completionmail'], $data, 'mail_completion')); ?>");
		$("#mail-checked").val("mail_completion");
		$("#sendMailDialog").dialog("option", "title", "<?php _e('発送完了メール', 'wc2'); ?>");
		$("#sendMailDialog").dialog("open");
	});

	$("#mail-order").click(function() {
		orderMail.getMailMessage("mail_order");
		$("#sendmail-address").val($("input[name='customer[email]']").val());
		$("#sendmail-name").val($("input[name='customer[name1]']").val()+$("input[name='customer[name2]']").val());
		$("#sendmail-subject").val("<?php echo esc_js(apply_filters('wc2_filter_admin_order_mail_subject', $phrase['title']['ordermail'], $data, 'mail_order')); ?>");
		$("#mail-checked").val("mail_order");
		$("#sendMailDialog").dialog("option", "title", "<?php _e('注文確認メール', 'wc2'); ?>");
		$("#sendMailDialog").dialog("open");
	});

	$("#mail-change").click(function() {
		orderMail.getMailMessage("mail_change");
		$("#sendmail-address").val($("input[name='customer[email]']").val());
		$("#sendmail-name").val($("input[name='customer[name1]']").val()+$("input[name='customer[name2]']").val());
		$("#sendmail-subject").val("<?php echo esc_js(apply_filters('wc2_filter_admin_order_mail_subject', $phrase['title']['changemail'], $data, 'mail_change')); ?>");
		$("#mail-checked").val("mail_change");
		$("#sendMailDialog").dialog("option", "title", "<?php _e('変更確認メール', 'wc2'); ?>");
		$("#sendMailDialog").dialog("open");
	});

	$("#mail-receipt").click(function() {
		orderMail.getMailMessage("mail_receipt");
		$("#sendmail-address").val($("input[name='customer[email]']").val());
		$("#sendmail-name").val($("input[name='customer[name1]']").val()+$("input[name='customer[name2]']").val());
		$("#sendmail-subject").val("<?php echo esc_js(apply_filters('wc2_filter_admin_order_mail_subject', $phrase['title']['receiptmail'], $data, 'mail_receipt')); ?>");
		$("#mail-checked").val("mail_receipt");
		$("#sendMailDialog").dialog("option", "title", "<?php _e('入金確認メール', 'wc2'); ?>");
		$("#sendMailDialog").dialog("open");
	});

	$("#mail-estimate").click(function() {
		orderMail.getMailMessage("mail_estimate");
		$("#sendmail-address").val($("input[name='customer[email]']").val());
		$("#sendmail-name").val($("input[name='customer[name1]']").val()+$("input[name='customer[name2]']").val());
		$("#sendmail-subject").val("<?php echo esc_js(apply_filters('wc2_filter_admin_order_mail_subject', $phrase['title']['estimatemail'], $data, 'mail_estimate')); ?>");
		$("#mail-checked").val("mail_estimate");
		$("#sendMailDialog").dialog("option", "title", "<?php _e('見積メール', 'wc2'); ?>");
		$("#sendMailDialog").dialog("open");
	});

	$("#mail-cancel").click(function() {
		orderMail.getMailMessage("mail_cancel");
		$("#sendmail-address").val($("input[name='customer[email]']").val());
		$("#sendmail-name").val($("input[name='customer[name1]']").val()+$("input[name='customer[name2]']").val());
		$("#sendmail-subject").val("<?php echo esc_js(apply_filters('wc2_filter_admin_order_mail_subject', $phrase['title']['cancelmail'], $data, 'mail_cancel')); ?>");
		$("#mail-checked").val("mail_cancel");
		$("#sendMailDialog").dialog("option", "title", "<?php _e('キャンセルメール', 'wc2'); ?>");
		$("#sendMailDialog").dialog("open");
	});

	$("#mail-other").click(function() {
		orderMail.getMailMessage("mail_other");
		$("#sendmail-address").val($("input[name='customer[email]']").val());
		$("#sendmail-name").val($("input[name='customer[name1]']").val()+$("input[name='customer[name2]']").val());
		$("#sendmail-subject").val("<?php echo esc_js(apply_filters('wc2_filter_admin_order_mail_subject', $phrase['title']['othermail'], $data, 'mail_other')); ?>");
		$("#mail-checked").val("mail_other");
		$("#sendMailDialog").dialog("option", "title", "<?php _e('その他のメール', 'wc2'); ?>");
		$("#sendMailDialog").dialog("open");
	});

	$("#PDFDialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 820,
		width: 700,
		resizable: true,
		modal: true,
		appendTo: "#dialog-parent",
		buttons: {
			"<?php _e('Close'); ?>": function() {
				$(this).dialog('close');
			}
		},
		close: function() {
			$("#new-pdf").html("");
		}
	});

	$("#print-estimate").click(function() {
		window.open('<?php echo WC2_ADMIN_URL; ?>?page=wc2_order&action=pdfout_order&order_id='+$("#order_id").val()+'&type=estimate');
/*
		$("#new-pdf").html('<iframe src="<?php echo WC2_ADMIN_URL; ?>?page=wc2_order&action=pdfout_order&order_id='+$("#order_id").val()+'&type=estimate" align="center" width="660" height=670" border="1" marginheight="0" marginwidth="0"></iframe>');
		$("#PDFDialog").dialog("option", "title", "<?php _e('見積書印刷', 'wc2'); ?>");
		$("#PDFDialog").dialog("open");
*/
		orderMail.checkPost("print_estimate");
	});

	$("#print-deliveryslip").click(function() {
		window.open('<?php echo WC2_ADMIN_URL; ?>?page=wc2_order&action=pdfout_order&order_id='+$("#order_id").val()+'&type=deliveryslip');
/*
		$("#new-pdf").html('<iframe src="<?php echo WC2_ADMIN_URL; ?>?page=wc2_order&action=pdfout_order&order_id='+$("#order_id").val()+'&type=deliveryslip" align="center" width="660" height=670" border="1" marginheight="0" marginwidth="0"></iframe>');
		$("#PDFDialog").dialog("option", "title", "<?php _e('納品書印刷', 'wc2'); ?>");
		$("#PDFDialog").dialog("open");
*/
		orderMail.checkPost("print_deliveryslip");
	});

	$("#print-invoice").click(function() {
		window.open('<?php echo WC2_ADMIN_URL; ?>?page=wc2_order&action=pdfout_order&order_id='+$("#order_id").val()+'&type=invoice');
/*
		$("#new-pdf").html('<iframe src="<?php echo WC2_ADMIN_URL; ?>?page=wc2_order&action=pdfout_order&order_id='+$("#order_id").val()+'&type=invoice" align="center" width="660" height=670" border="1" marginheight="0" marginwidth="0"></iframe>');
		$("#PDFDialog").dialog("option", "title", "<?php _e('請求書印刷', 'wc2'); ?>");
		$("#PDFDialog").dialog("open");
*/
		orderMail.checkPost("print_invoice");
	});

	$("#print-receipt").click(function() {
		window.open('<?php echo WC2_ADMIN_URL; ?>?page=wc2_order&action=pdfout_order&order_id='+$("#order_id").val()+'&type=receipt');
/*
		$("#new-pdf").html('<iframe src="<?php echo WC2_ADMIN_URL; ?>?page=wc2_order&action=pdfout_order&order_id='+$("#order_id").val()+'&type=receipt" align="center" width="660" height=670" border="1" marginheight="0" marginwidth="0"></iframe>');
		$("#PDFDialog").dialog("option", "title", "<?php _e('領収書印刷', 'wc2'); ?>");
		$("#PDFDialog").dialog("open");
*/
		orderMail.checkPost("print_receipt");
	});

	orderItem = {
		add2cart : function( additem, addsku, addquantity ) {
			//var newoptob = $("input[name*='optNEWCode[" + additem + "][" + addsku + "]']");
			//var newoptvalue = "";
			var mes = "";
/*
			for( var n = 0; n < newoptob.length; n++ ) {
				newoptvalue = $(":input[name='itemNEWOption[" + additem + "][" + addsku + "][" + $(newoptob[n]).val() + "]']").val();
				var newoptclass = $(":input[name='itemNEWOption[" + additem + "][" + addsku + "][" + $(newoptob[n]).val() + "]']").attr("class");
				var essential = $(":input[name='optNEWEssential[" + additem + "][" + addsku + "][" + $(newoptob[n]).val() + "]']").val();
				switch(newoptclass) {
				case "iopt_select_multiple":
					var sel = 0;
					if( essential == 1 ) {
						$(":input[name='itemNEWOption[" + additem + "][" + addsku + "][" + $(newoptob[n]).val() + "]'] option:selected").each(function(idx, obj) {
							if( "<?php echo WC2_UNSELECTED; ?>" != $(this).val() ) {
								sel++;
							}
						});
						if( sel == 0 ) {
							mes += decodeURIComponent($(newoptob[n]).val())+"を選択してください\n";
						}
					}
					$(":input[name='itemNEWOption[" + additem + "][" + addsku + "][" + $(newoptob[n]).val() + "]'] option:selected").each(function(idx, obj) {
						if( "<?php echo WC2_UNSELECTED; ?>" != $(this).val() ) {
							query += "&itemOption[" + $(newoptob[n]).val() + "][" + encodeURIComponent($(this).val()) + "]="+encodeURIComponent($(this).val());
						}
					});
					break;
				case "iopt_select":
					if( essential == 1 && newoptvalue == "<?php echo WC2_UNSELECTED; ?>" ) {
						mes += decodeURIComponent($(newoptob[n]).val())+"を選択してください\n";
					} else {
						query += "&itemOption[" + $(newoptob[n]).val() + "]="+encodeURIComponent(newoptvalue);
					}
					break;
				case "iopt_text":
				case "iopt_textarea":
					if( essential == 1 && newoptvalue == "" ) {
						mes += decodeURIComponent($(newoptob[n]).val())+"を入力してください\n";
					} else {
						query += "&itemOption[" + $(newoptob[n]).val() + "]="+encodeURIComponent(newoptvalue);
					}
					break;
				}
			}
*/
			if( mes != '' ) {
				alert(mes);
				return;
			}

			var cart_row = $("#cart_row").val();
			$.ajax({
				url: ajaxurl,
				type: "POST",
				data: {
					action: "order_edit_ajax",
					mode: "add2cart",
					order_id: $("#order_id").val(),
					item_id: additem,
					sku_id: addsku,
					quantity: addquantity,
//*** LI CUSTOMIZE >>>
			<?php
				$edit_mode = ( isset($_REQUEST['edit_mode']) ) ? $_REQUEST['edit_mode'] : 'display';
				if( $edit_mode == 'update' ) : ?>
					delivery_method: $("#delivery_method_select option:selected").val(),
					delivery_pref: $("#delivery_pref option:selected").val(),
			<?php else: ?>
					delivery_method: $("#delivery_method_select").val(),
					delivery_pref: $('input[name="delivery[pref]"]').val(),
			<?php endif; ?>
//*** LI CUSTOMIZE <<<
					cart_row: cart_row
				}
			}).done(function( retVal, dataType ) {
//*** LI CUSTOMIZE >>>
				//$("#order-cart-items").html("");
				//$("#order-cart-items").html( retVal );
				//orderFunc.sumPrice(null);
				//$("#cart_row").val(parseInt(cart_row)+1);
				var data = retVal.split("<?php echo WC2_SPLIT; ?>");
				if( data[0] == "OK" ) {
					$("#order-cart-items").html("");
					$("#order-cart-items").html(data[1]);
					$("#status-history tbody").html("");
					$("#status-history tbody").html(data[2]);
					orderFunc.sumPrice(null);
					$("#cart_row").val(parseInt(cart_row)+1);
				}
//*** LI CUSTOMIZE <<<
			}).fail(function( retVal ) {
			});
			return false;
		},

		getSelectItem : function( cat_id ) {
			if( cat_id == "-1" ) {
				$("#additem-select").html("");
				return false;
			}
			$("#additem-loading").html('<img src="'+WC2L10n.loading_gif+'" />');

			$.ajax({
				url: ajaxurl,
				type: "POST",
				data: {
					action: "order_edit_ajax",
					mode: "get_select_item",
					cat_id: cat_id
				}
			}).done(function( retVal, dataType ) {
				$("#additem-loading").html("");
				$("#additem-select").html( retVal );
			}).fail(function( retVal ) {
			});
			return false;
		},

		getItem : function( item_code ) {
			if( item_code == "-1" ) {
				$("#additem-form").html("");
				return false;
			}
			$("#additem-loading").html('<img src="'+WC2L10n.loading_gif+'" />');

			$.ajax({
				url: ajaxurl,
				type: "POST",
				data: {
					action: "order_edit_ajax",
					mode: "get_add_item",
					item_code: item_code
				}
			}).done(function( retVal, dataType ) {
				$("#additem-loading").html("");
				$("#additem-form").html( retVal );
			}).fail(function( retVal ) {
			});
			return false;
		}
	};

	orderFunc = {
		sumPrice : function( obj ) {
			if( obj != null ) {
				if( !WC2Util.checkNumMinus(obj.val()) ) {
					alert("<?php _e('数値で入力してください', 'wc2'); ?>");
					obj.focus();
					return false;
				}
			}
			var total = 0;
			var itemtotal = <?php echo apply_filters( 'wc2_filter_admin_order_item_total_price', 0, $data ); ?>;
			$("input[name*='sku_price']").each(function() {
				var idname = $(this).attr("id");
				var ids = idname.split("-");
				var id = ids[1];
//*** LI CUSTOMIZE >>>
				var round = $('input[name="meta_key['+id+'][round]"]').val();
				//var subtotal = parseFloat($(this).val()) * $("#quantity-"+id).val();
				var subtotal = LiUtil.toFixed( parseFloat($(this).val()) * $("#quantity-"+id).val(), round, 0 );
//*** LI CUSTOMIZE <<<
				$("#subtotal-"+id).html(WC2Util.addComma(subtotal+''));
				itemtotal += subtotal;
			});
			$("#item_total_price").val(itemtotal);
			$("#item-total").html(WC2Util.addComma(itemtotal+''));
			var usedpoint = $("#usedpoint").val()*1;
			var discount = parseFloat($("#discount").val());
			var shipping_charge = parseFloat($("#shipping_charge").val());
			var cod_fee = parseFloat($("#cod_fee").val());
//*** LI CUSTOMIZE >>>
			//var tax = parseFloat($("#tax").val());
			//total = itemtotal - usedpoint + discount + shipping_charge + cod_fee + tax;
			total = itemtotal - usedpoint + discount + shipping_charge + cod_fee;
//*** LI CUSTOMIZE <<<
			$("#total").html(WC2Util.addComma(total+''));
			$("#total-top").html(WC2Util.addComma(total+''));
			<?php do_action( 'wc2_action_admin_order_total_price', $data ); ?>
		},

		makeDeliveryTime : function(selected) {
			var option = '';
			if( selected == -1 || delivery_time[selected] == undefined || 0 == delivery_time[selected].length ) {
				option += '<option value=""><?php _e('指定しない', 'wc2'); ?></option>';
			} else {
				for( var i=0; i<delivery_time[selected].length; i++ ) {
					if( delivery_time[selected][i] == selected_delivery_time ) {
						option += '<option value="' + delivery_time[selected][i] + '" selected="selected">' + delivery_time[selected][i] + '</option>';
					} else {
						option += '<option value="' + delivery_time[selected][i] + '">' + delivery_time[selected][i] + '</option>';
					}
				}
			}
			$("#delivery_time_select").html(option);
		},

		getMember : function( email ) {
			$.ajax({
				url: ajaxurl,
				type: "POST",
				data: {
					action: "order_edit_ajax",
					mode: "get_member",
					email: encodeURIComponent(email)
				}
			}).done(function( retVal, dataType ) {
				var data = retVal.split("<?php echo WC2_SPLIT; ?>");
				if( data[0] == "OK" ) {
					for( var i = 1; i < data.length; i++ ) {
						var value = data[i].split("=");
						if( value[0] == "member_id" ) {
							$(".member-id-value").html(value[1]);
							$("#member_id").val(value[1]);
						} else {
							$(":input[name='"+value[0]+"']").val(value[1]);
						}
					}
				} else if( data[0] == "NG" ) {
					alert("<?php _e('該当する会員情報は存在しません。', 'wc2'); ?>");
				} else {
					alert("ERROR");
				}
			}).fail(function( retVal ) {
			});
			return false;
		},

		recalculation : function() {
<?php ob_start(); ?>
			var p = $("input[name*='sku_price']");
			var q = $("input[name*='quantity']");
			var pi = $("input[name*='postId']");
			var item_ids = "";
			var skus = "";
			var prices = "";
			var quantities = "";
			for( var i = 0; i < p.length; i++) {
				item_ids += $(pi[i]).val()+"<?php echo WC2_SPLIT; ?>";
				prices += parseFloat($(p[i]).val())+"<?php echo WC2_SPLIT; ?>";
				quantities += $(q[i]).val()+"<?php echo WC2_SPLIT; ?>";
			}
			var usedpoint = $("#usedpoint").val()*1;
			var shipping_charge = parseFloat($("#shipping_charge").val());
			var cod_fee = parseFloat($("#cod_fee").val());

			$.ajax({
				url: ajaxurl,
				type: "POST",
				data: {
					action: "order_edit_ajax",
					mode: "recalculation",
					order_id: $("#order_id").val(),
					member_id: $("#member_id").val(),
					item_ids: item_ids,
					skus: skus,
					prices: prices,
					quantities: quantities,
					usedpoint: usedpoint,
					shipping_charge: shipping_charge,
					cod_fee: cod_fee
				}
			}).done(function( retVal, dataType ) {
				var data = retVal.split("<?php echo WC2_SPLIT; ?>");
				if( data[0] == "OK" ) {
					$("#discount").val(data[1]);
//*** LI CUSTOMIZE >>>
					//$("#tax").val(data[2]);
//*** LI CUSTOMIZE <<<
					$("#getpoint").val(data[3]);
					$("#total").html(WC2Util.addComma(data[4]+""));
					$("#total-top").html(WC2Util.addComma(data[4]+""));
				}
			}).fail(function( retVal ) {
			});
			return false;
<?php
	$script_recalculation = ob_get_contents();
	ob_end_clean();
	echo apply_filters( 'wc2_filter_admin_order_recalculation', $script_recalculation, $data );
?>
		}
	};

	orderMail = {
		sendMail : function() {
			if($("#sendmail-address").val() == "") return;

			$.ajax({
				url: ajaxurl,
				type: "POST",
				data: {
					action: "order_edit_ajax",
					mode: "sendmail",
					mailaddress: encodeURIComponent($("#sendmail-address").val()),
					message: encodeURIComponent($("#sendmail-message").val()),
					name: encodeURIComponent($("#sendmail-name").val()),
					subject: encodeURIComponent($("#sendmail-subject").val()),
					order_id: $("#order_id").val(),
					checked: $("#mail-checked").val()
				}
			}).done(function( retVal, dataType ) {
				if( retVal == "OK" ) {
					checked = $("#mail-checked").val().replace("_", "-");
					$("li."+checked).removeClass("status-no");
					$("li."+checked).addClass("status-yes dashicons-before dashicons-yes");
					<?php do_action( 'wc2_action_admin_order_check_mail_scripts' ); ?>

					$("#sendMailAlert").dialog("option", "buttons", {
						"OK": function() {
							$(this).dialog("close");
							$('#sendMailDialog').dialog("close");
						}
					});
					$("#sendMailAlert").dialog("option", "title", "SUCCESS");
					$("#sendMailAlert fieldset").html("<p><?php _e('メールを送信しました。', 'wc2'); ?></p>");
					$("#sendMailAlert").dialog("open");

				} else if( retVal == "NG" ) {
					$("#sendMailAlert").dialog("option", "buttons", {
						"OK": function() {
							$(this).dialog("close");
						}
					});
					$("#sendMailAlert").dialog("option", "title", "ERROR");
					$("#sendMailAlert fieldset").html("<p><?php _e('メールを送信できませんでした。', 'wc2'); ?></p>");
					$("#sendMailAlert").dialog("open");
				}
			}).fail(function( retVal ) {
				$("#sendMailAlert").dialog("option", "buttons", {
					"OK": function() {
						$(this).dialog("close");
					}
				});
				$("#sendMailAlert").dialog("option", "title", "ERROR");
				$("#sendMailAlert fieldset").html("<p><?php _e('メールを送信できませんでした。', 'wc2'); ?></p>");
				$("#sendMailAlert").dialog("open");
			});
			return false;
		},

		checkPost : function( checked ) {
			$.ajax({
				url: ajaxurl,
				type: "POST",
				data: {
					action: "order_edit_ajax",
					mode: "checkpost",
					order_id: $("#order_id").val(),
					checked: checked
				}
			}).done(function( retVal, dataType ) {
				if( retVal == checked ) {
					checked = checked.replace("_", "-");
					$("li."+checked).removeClass("status-no");
					$("li."+checked).addClass("status-yes dashicons-before dashicons-yes");
				}
			}).fail(function( retVal ) {
			});
			return false;
		},

		getMailMessage : function( checked ) {
			$("#sendmail").attr("disabled", "disabled");
			$("#sendmail-message").val( WC2L10n.now_loading );
			$.ajax({
				url: ajaxurl,
				type: "POST",
				data: {
					action: "order_edit_ajax",
					mode: checked,
					order_id: $("#order_id").val()
				}
			}).done(function( retVal, dataType ) {
				$("#sendmail").removeAttr("disabled");
				$("#sendmail-message").val( retVal );
			}).fail(function( retVal ) {
			});
			return false;
		}
	};

	$(".update").click(function() {
		var error = 0;

		<?php if( wc2_is_membersystem_state() && wc2_is_membersystem_point() ) : ?>
		if( !WC2Util.checkNum( $("#usedpoint").val() ) ) {
			error++;
			$("#usedpoint").css({"background-color": "#FFA"}).click(function() {
				$(this).css({"background-color": "#FFF"});
			});
		}
		if( !WC2Util.checkNum( $("#getdpoint").val() ) ) {
			error++;
			$("#getdpoint").css({"background-color": "#FFA"}).click(function() {
				$(this).css({"background-color": "#FFF"});
			});
		}
		<?php endif; ?>
		if( !WC2Util.checkPrice( $("#discount").val() ) ) {
			error++;
			$("#discount").css({"background-color": "#FFA"}).click(function() {
				$(this).css({"background-color": "#FFF"});
			});
		}
		if( !WC2Util.checkPrice( $("#shipping_charge").val() ) ) {
			error++;
			$("#shipping_charge").css({"background-color": "#FFA"}).click(function() {
				$(this).css({"background-color": "#FFF"});
			});
		}
		if( !WC2Util.checkPrice( $("#cod_fee").val() ) ) {
			error++;
			$("#cod_fee").css({"background-color": "#FFA"}).click(function() {
				$(this).css({"background-color": "#FFF"});
			});
		}
//*** LI CUSTOMIZE >>>
		//if( !WC2Util.checkPrice( $("#tax").val() ) ) {
		//	error++;
		//	$("#tax").css({"background-color": "#FFA"}).click(function() {
		//		$(this).css({"background-color": "#FFF"});
		//	});
		//}
//*** LI CUSTOMIZE <<<
		<?php do_action( 'wc2_action_admin_order_check_update_scripts', $data, $this->mode ); ?>
		if( 0 < error ) {
			$("#aniboxStatus").attr("class", "error");
			$("#info_image").attr("src", WC2L10n.error_info);
			$("#info_message").html("データに不備があります");
			$("#anibox").animate({ backgroundColor: "#FFE6E6" }, 2000);
			return false;
		}

		if( ("completion" == $("#order_status option:selected").val() || "continuation" == $("#order_status option:selected").val()) && "<?php echo wc2_get_today_format(); ?>" != $("#modified").val() ) {
			if( confirm("<?php _e('更新日を今日の日付に変更しますか？', 'wc2'); ?>\n<?php _e('更新日を変更せずに更新する場合はキャンセルを押してください。', 'wc2'); ?>") ) {
				$("#up_modified").val("update");
			} else {
				$("#up_modified").val("");
			}
		}

		$("#order-edit-form").submit();
	});
<?php do_action( 'wc2_action_admin_order_post_scripts', $data, $this->mode ); ?>
});
jQuery(document).ready(function($) {
	$("#navi-box").css("display", "none");
	$("#navi-box-link").click(function() {
		$("#navi-box").toggle();
	});

	orderFunc.sumPrice(null);

	$(document).on( "change", "input[name*='sku_price']", function() {orderFunc.sumPrice($(this));});
//*** LI CUSTOMIZE >>>
	//$(document).on( "change", "input[name*='quantity']", function() {orderFunc.sumPrice($(this));});
	$(document).on( "change", "input[name*='quantity']", function() {
		var p = $("input[name*='sku_price']");
		var q = $("input[name*='quantity']");
		var ci = $("input[name*='cart_id']");
		var cart_ids = "";
		var prices = "";
		var quantities = "";
		for( var i = 0; i < p.length; i++ ) {
			cart_ids += $(ci[i]).val()+"<?php echo WC2_SPLIT; ?>";
			prices += parseFloat($(p[i]).val())+"<?php echo WC2_SPLIT; ?>";
			quantities += $(q[i]).val()+"<?php echo WC2_SPLIT; ?>";
		}

		var idname = $(this).attr("id");
		var ids = idname.split("-");
		var id = ids[1];

		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: {
				action: "order_edit_ajax",
				mode: "get-shipping-charge",
		<?php
			$edit_mode = ( isset($_REQUEST['edit_mode']) ) ? $_REQUEST['edit_mode'] : 'display';
			if( $edit_mode == 'update' ) : ?>
				delivery_method: $("#delivery_method_select option:selected").val(),
				delivery_pref: $("#delivery_pref option:selected").val(),
		<?php else: ?>
				delivery_method: $("#delivery_method_select").val(),
				delivery_pref: $('input[name="delivery[pref]"]').val(),
		<?php endif; ?>
				order_id: $("#order_id").val(),
				cart_ids: cart_ids,
				prices: prices,
				quantities: quantities,
				id: id
			}
		}).done(function( retVal, dataType ) {
			var data = retVal.split("<?php echo WC2_SPLIT; ?>");
			if( data[0] == "OK" ) {
				$("#shipping_charge").val(data[1]);
				if( data[2] != "0" ) $("#sku_price-"+id).val(data[2]);
				orderFunc.sumPrice(null);
			}
		}).fail(function( retVal ) {
		});
		return false;
	});
//*** LI CUSTOMIZE <<<
	$(document).on( "change", "#usedpoint", function() {orderFunc.sumPrice($("#usedpoint"));});
	$(document).on( "change", "#discount", function() {orderFunc.sumPrice($("#discount"));});
	$(document).on( "change", "#shipping_charge", function() {orderFunc.sumPrice($("#shipping_charge"));});
	$(document).on( "change", "#cod_fee", function() {orderFunc.sumPrice($("#cod_fee"));});
//*** LI CUSTOMIZE >>>
	//$(document).on( "change", "#tax", function() {orderFunc.sumPrice($("#tax"));});
//*** LI CUSTOMIZE <<<

	$(document).on( "click", ".cart-remove", function() {
		if( !confirm("<?php _e('明細から商品を削除します。よろしいですか？', 'wc2'); ?>") ) {
			return false;
		}
		var idname = $(this).attr("id");
		var ids = idname.split("-");
		var cart_id = ids[2];

		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: {
				action: "order_edit_ajax",
				mode: "cart_remove",
				order_id: $("#order_id").val(),
//*** LI CUSTOMIZE >>>
		<?php
			$edit_mode = ( isset($_REQUEST['edit_mode']) ) ? $_REQUEST['edit_mode'] : 'display';
			if( $edit_mode == 'update' ) : ?>
				delivery_method: $("#delivery_method_select option:selected").val(),
				delivery_pref: $("#delivery_pref option:selected").val(),
		<?php else: ?>
				delivery_method: $("#delivery_method_select").val(),
				delivery_pref: $('input[name="delivery[pref]"]').val(),
		<?php endif; ?>
//*** LI CUSTOMIZE <<<
				cart_id: cart_id
			}
		}).done(function( retVal, dataType ) {
//*** LI CUSTOMIZE >>>
			//$("#order-cart-items").html("");
			//$("#order-cart-items").html( retVal );
			//orderFunc.sumPrice(null);
			var data = retVal.split("<?php echo WC2_SPLIT; ?>");
			if( data[0] == "OK" ) {
				$("#order-cart-items").html("");
				$("#order-cart-items").html(data[1]);
				$("#status-history tbody").html("");
				$("#status-history tbody").html(data[2]);
				orderFunc.sumPrice(null);
			}
//*** LI CUSTOMIZE <<<
		}).fail(function( retVal ) {
		});
		return false;
	});

<?php $order_delivery_method = ( isset($data['delivery_method']) ) ? $data['delivery_method'] : -1; ?>
	orderFunc.makeDeliveryTime(<?php echo $order_delivery_method; ?>);

	$("#get-member").click(function() {
		if( "" == $("input[name='customer[email]']").val() ) {
			alert("<?php _e('メールアドレスを入力してください。','wc2'); ?>");
			return;
		}
		if( $("input[name='customer[name1]']").val() || "" != $("input[name='delivery[name1]']").val() ) {
			if( !confirm("<?php _e('注文者住所を配送先住所に上書きします。よろしいですか？', 'wc2'); ?>") ) {
				return false;
			}
		}
		orderFunc.getMember($("input[name='customer[email]']").val());
	});

	<?php if( $this->mode == 'new' ) : ?>
	$("#costomer-copy").click(function() {
		if( "" != $("input[name='delivery[name1]']").val() || 
			"" != $("input[name='delivery[name2]']").val() || 
			"" != $("input[name='delivery[name3]']").val() || 
			"" != $("input[name='delivery[name4]']").val() || 
			"" != $("input[name='delivery[zipcode]']").val() || 
			"" != $("input[name='delivery[address1]']").val() || 
			"" != $("input[name='delivery[address2]']").val() || 
			"" != $("input[name='delivery[tel]']").val() || 
			"" != $("input[name='delivery[fax]']").val() ) {
			if( !confirm("<?php _e('注文者住所を配送先住所に上書きします。よろしいですか？', 'wc2'); ?>") )
				return;
		}
		$("input[name='delivery[name1]']").val($("input[name='customer[name1]']").val());
		$("input[name='delivery[name2]']").val($("input[name='customer[name2]']").val());
		$("input[name='delivery[name3]']").val($("input[name='customer[name3]']").val());
		$("input[name='delivery[name4]']").val($("input[name='customer[name4]']").val());
		$("input[name='delivery[zipcode]']").val($("input[name='customer[zipcode]']").val());
		$("#delivery_country").val($("#customer_country option:selected").val());
		$("#delivery_pref").val($("#customer_pref option:selected").val());
		$("input[name='delivery[address1]']").val($("input[name='customer[address1]']").val());
		$("input[name='delivery[address2]']").val($("input[name='customer[address2]']").val());
		$("input[name='delivery[tel]']").val($("input[name='customer[tel]']").val());
		$("input[name='delivery[fax]']").val($("input[name='customer[fax]']").val());
	});
	<?php endif; ?>

	$("#recalc").click(function() {orderFunc.recalculation();});
<?php endif; ?>
});
</script>
<?php
	}

	/***********************************
	 * AJAX request's action.
	 *
	 * @since    1.0.0
	 ***********************************/
	function order_edit_ajax() {
//wc2_log(print_r($_POST,true),"test.log");
		if( $_POST['action'] != 'order_edit_ajax' ) die(0);

		$res = false;
		$_POST = WC2_Utils::stripslashes_deep_post($_POST);

		switch( $_POST['mode'] ) {
		case 'add2cart':
			$slug = apply_filters( 'wc2_filter_cart_slug', 'cart' );
			$general_options = wc2_get_option( 'general' );
			$add_cart = array();
			$item_id = ( isset($_POST['item_id']) ) ? $_POST['item_id'] : 0;
			$sku_id = ( isset($_POST['sku_id']) ) ? $_POST['sku_id'] : 0;
			$quantity = ( isset($_POST['quantity']) ) ? $_POST['quantity'] : 1;
			$cart_row = ( isset($_POST['cart_row']) ) ? $_POST['cart_row'] : 1;
			$item_sku_data = wc2_get_item_sku_data( $item_id, $sku_id );
			$price = $item_sku_data['sku_price'];
			$price = apply_filters( 'wc2_filter_admin_order_add2cart_price', $price, $quantity, $item_id, $sku_id, $slug );
			if( empty($general_options['tax_rate']) ) {
				$tax = 0;
			} else {
				$materials = array(
					'total_price' => $price * $quantity,
					'discount' => 0,
					'shipping_charge' => 0,
					'cod_fee' => 0,
				);
				$tax = wc2_internal_tax( $materials );
			}

			$add_cart['group_id'] = 0;
			$add_cart['row_index'] = $cart_row;
			$add_cart['post_id'] = $item_sku_data['item_post_id'];
			$add_cart['item_id'] = $item_id;
			$add_cart['item_code'] = $item_sku_data['item_code'];
			$add_cart['item_name'] = $item_sku_data['item_name'];
			$add_cart['sku_id'] = $sku_id;
			$add_cart['sku_code'] = $item_sku_data['sku_code'];
			$add_cart['sku_name'] = $item_sku_data['sku_name'];
			$add_cart['price'] = $price;
			$add_cart['cprice'] = $item_sku_data['sku_costprice'];
			$add_cart['quantity'] = $quantity;
			$add_cart['unit'] = $item_sku_data['sku_unit'];
			$add_cart['tax'] = $tax;
			$add_cart['destination_id'] = 0;
			$add_cart['meta_type'] = apply_filters( 'wc2_filter_admin_order_add2cart_meta_type', array(), $quantity, $item_id, $sku_id, $slug );
			$add_cart['meta_key'] = apply_filters( 'wc2_filter_admin_order_add2cart_meta_key', array(), $quantity, $item_id, $sku_id, $slug );
			$add_cart = apply_filters( 'wc2_filter_admin_order_add2cart', $add_cart, $slug );

			$res = wc2_add_order_cart_data( $_POST['order_id'], $add_cart );
			if( !$res )
				die( $res );

			$cart = wc2_get_order_cart_data( $_POST['order_id'] );
//*** LI CUSTOMIZE >>>
			//$res = wc2_get_admin_order_cart_row( $_POST['order_id'], $cart );
			$cart_row = wc2_get_admin_order_cart_row( $_POST['order_id'], $cart );
			$order_history_form = li_get_order_history_form( $_POST['order_id'] );
			$res = 'OK'.WC2_SPLIT.$cart_row.WC2_SPLIT.$order_history_form;

			$shipping_charge = li_get_delivery_shipping_charge( $_POST['delivery_method'], $_POST['delivery_pref'], $cart );
			$order_modified = wc2_get_today_datetime_format();
			$update_query = " shipping_charge = ".$shipping_charge.", order_modified = '".$order_modified."'";
			wc2_update_order_data_value( $_POST['order_id'], $update_query );
//*** LI CUSTOMIZE <<<
			break;

		case 'cart_remove':
			$res = wc2_remove_order_cart_data( $_POST['order_id'], $_POST['cart_id'] );
			if( !$res )
				die( $res );

			$cart = wc2_get_order_cart_data( $_POST['order_id'] );
//*** LI CUSTOMIZE >>>
			//$res = wc2_get_admin_order_cart_row( $_POST['order_id'], $cart );
			$cart_row = wc2_get_admin_order_cart_row( $_POST['order_id'], $cart );
			$order_history_form = li_get_order_history_form( $_POST['order_id'] );
			$res = 'OK'.WC2_SPLIT.$cart_row.WC2_SPLIT.$order_history_form;

			$shipping_charge = li_get_delivery_shipping_charge( $_POST['delivery_method'], $_POST['delivery_pref'], $cart );
			$order_modified = wc2_get_today_datetime_format();
			$update_query = " shipping_charge = ".$shipping_charge.", order_modified = '".$order_modified."'";
			wc2_update_order_data_value( $_POST['order_id'], $update_query );
//*** LI CUSTOMIZE <<<
			break;

		case 'mail_completion':
		case 'mail_order':
		case 'mail_change':
		case 'mail_receipt':
		case 'mail_estimate':
		case 'mail_cancel':
		case 'mail_other':
			$res = wc2_ordermail_admin( $_POST['order_id'] );
			break;

		case 'sendmail':
			$res = wc2_send_ordermail_admin();
			break;

		case 'get_add_item':
			$res = wc2_get_add_item( $_POST['item_code'] );
			break;

		case 'get_select_item':
			$res = wc2_get_select_item( $_POST['cat_id'] );
			break;

		case 'checkpost':
			$res = wc2_update_order_check( $_POST['order_id'], $_POST['checked'] );
			break;

		case 'get_member':
			$res = wc2_get_member_neworder( $_POST['email'] );
			break;

		case 'recalculation':
			$res = wc2_order_recalculation( $_POST['order_id'], $_POST['member_id'], $_POST['item_ids'], $_POST['skus'], $_POST['prices'], $_POST['quantities'], $_POST['usedpoint'], $_POST['shipping_charge'], $_POST['cod_fee'] );
			break;
		}
		$res = apply_filters( 'wc2_filter_admin_order_edit_ajax', $res );
//wc2_log($res,"test.log");
		die( $res );
	}

	/***********************************
	 * Register order data.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function get_post_data() {
		$data = array();

		$data[ORDER_MEMBER_ID] = ( isset($_POST['member_id']) ) ? $_POST['member_id'] : '';

		$data[ORDER_EMAIL] = ( isset($_POST['customer']['email']) ) ? $_POST['customer']['email'] : '';
		$data[ORDER_NAME1] = ( isset($_POST['customer']['name1']) ) ? $_POST['customer']['name1'] : '';
		$data[ORDER_NAME2] = ( isset($_POST['customer']['name2']) ) ? $_POST['customer']['name2'] : '';
		$data[ORDER_NAME3] = ( isset($_POST['customer']['name3']) ) ? $_POST['customer']['name3'] : '';
		$data[ORDER_NAME4] = ( isset($_POST['customer']['name4']) ) ? $_POST['customer']['name4'] : '';
		$data[ORDER_COUNTRY] = ( isset($_POST['customer']['country']) ) ? $_POST['customer']['country'] : '';
		$data[ORDER_ZIPCODE] = ( isset($_POST['customer']['zipcode']) ) ? $_POST['customer']['zipcode'] : '';
		$data[ORDER_PREF] = ( isset($_POST['customer']['pref']) ) ? $_POST['customer']['pref'] : '';
		$data[ORDER_ADDRESS1] = ( isset($_POST['customer']['address1']) ) ? $_POST['customer']['address1'] : '';
		$data[ORDER_ADDRESS2] = ( isset($_POST['customer']['address2']) ) ? $_POST['customer']['address2'] : '';
		$data[ORDER_TEL] = ( isset($_POST['customer']['tel']) ) ? $_POST['customer']['tel'] : '';
		$data[ORDER_FAX] = ( isset($_POST['customer']['fax']) ) ? $_POST['customer']['fax'] : '';

		$data[ORDER_NOTE] = ( isset($_POST['offer']['note']) ) ? $_POST['offer']['note'] : '';
		$data[ORDER_DELIVERY_METHOD] = ( isset($_POST['offer']['delivery_method']) ) ? $_POST['offer']['delivery_method'] : -1;
		$data[ORDER_DELIVERY_NAME] = ( isset($_POST['offer']['delivery_name']) ) ? $_POST['offer']['delivery_name'] : '';
		$data[ORDER_DELIVERY_DATE] = ( isset($_POST['offer']['delivery_date']) ) ? $_POST['offer']['delivery_date'] : '';
		$data[ORDER_DELIVERY_TIME] = ( isset($_POST['offer']['delivery_time']) ) ? $_POST['offer']['delivery_time'] : '';
		$data[ORDER_DELIDUE_DATE] = ( isset($_POST['offer']['delidue_date']) ) ? $_POST['offer']['delidue_date'] : '';

		$data[ORDER_PAYMENT_METHOD] = ( isset($_POST['offer']['payment_method']) ) ? $_POST['offer']['payment_method'] : -1;
		$data[ORDER_PAYMENT_NAME] = ( isset($_POST['offer']['payment_name']) ) ? $_POST['offer']['payment_name'] : '';

		$data[ORDER_CONDITION] = ( isset($_POST['order_id']) ) ? wc2_get_order_data_value( $_POST['order_id'], 'order_condition' ) : serialize( wc2_get_condition() );

		$data[ORDER_ITEM_TOTAL_PRICE] = ( isset($_POST['offer']['item_total_price']) ) ? $_POST['offer']['item_total_price'] : 0;
		$data[ORDER_GETPOINT] = ( isset($_POST['offer']['getpoint']) ) ? $_POST['offer']['getpoint'] : 0;
		$data[ORDER_USEDPOINT] = ( isset($_POST['offer']['usedpoint']) ) ? $_POST['offer']['usedpoint'] : 0;
		$data[ORDER_DISCOUNT] = ( isset($_POST['offer']['discount']) ) ? $_POST['offer']['discount'] : 0;
		$data[ORDER_SHIPPING_CHARGE] = ( isset($_POST['offer']['shipping_charge']) ) ? $_POST['offer']['shipping_charge'] : 0;
		$data[ORDER_COD_FEE] = ( isset($_POST['offer']['cod_fee']) ) ? $_POST['offer']['cod_fee'] : 0;
		$data[ORDER_TAX] = ( isset($_POST['offer']['tax']) ) ? $_POST['offer']['tax'] : 0;

		$data[ORDER_DATE] = ( isset($_POST['order_date']) ) ? $_POST['order_date'] : '';
		$data[ORDER_MODIFIED] = ( isset($_POST['modified']) ) ? $_POST['modified'] : '';
		$data[ORDER_STATUS] = ( isset($_POST['offer']['order_status']) ) ? $_POST['offer']['order_status'] : '';
		$data[RECEIPT_STATUS] = ( isset($_POST['offer']['receipt_status']) ) ? $_POST['offer']['receipt_status'] : '';
		$data[RECEIPTED_DATE] = ( isset($_POST['offer']['receipted_date']) ) ? $_POST['offer']['receipted_date'] : '';
		$data[ORDER_TYPE] = ( isset($_POST['offer']['order_type']) ) ? $_POST['offer']['order_type'] : 'adminorder';
		$data[ORDER_CHECK] = ( isset($_POST['order_id']) ) ? wc2_get_order_data_value( $_POST['order_id'], 'order_check' ) : serialize(array());

		$data['meta_key'][ORDER_MEMO] = ( isset($_POST['order_memo']) ) ? $_POST['order_memo'] : '';

		//Custom Customer
		$cscs_keys = wc2_get_custom_field_keys(WC2_CSCS);
		if( !empty($cscs_keys) && is_array($cscs_keys) ){
			foreach($cscs_keys as $key){
				list( $pfx, $cscs_key ) = explode('_', $key, 2);
				if( array_key_exists(WC2_CUSTOM_CUSTOMER, $_POST) and array_key_exists($cscs_key, $_POST[WC2_CUSTOM_CUSTOMER]) ) {
					if( is_array($_POST[WC2_CUSTOM_CUSTOMER][$cscs_key]) ) {
						$data[WC2_CUSTOM_CUSTOMER][$cscs_key] = serialize($_POST[WC2_CUSTOM_CUSTOMER][$cscs_key]);
					} else {
						$data[WC2_CUSTOM_CUSTOMER][$cscs_key] = $_POST[WC2_CUSTOM_CUSTOMER][$cscs_key];
					}
				} else {
					$data[WC2_CUSTOM_CUSTOMER][$cscs_key] = '';
				}
			}
		}

		//Custom Order
		$csod_keys = wc2_get_custom_field_keys(WC2_CSOD);
		if( !empty($csod_keys) && is_array($csod_keys) ){
			foreach($csod_keys as $key){
				list( $pfx, $csod_key ) = explode('_', $key, 2);
				if( array_key_exists(WC2_CUSTOM_ORDER, $_POST) and array_key_exists($csod_key, $_POST[WC2_CUSTOM_ORDER]) ) {
					if( is_array($_POST[WC2_CUSTOM_ORDER][$csod_key]) ) {
						$data[WC2_CUSTOM_ORDER][$csod_key] = serialize($_POST[WC2_CUSTOM_ORDER][$csod_key]);
					} else {
						$data[WC2_CUSTOM_ORDER][$csod_key] = $_POST[WC2_CUSTOM_ORDER][$csod_key];
					}
				} else {
					$data[WC2_CUSTOM_ORDER][$csod_key] = '';
				}
			}
		}

		//Cart
		$data[ORDER_CART] = array();
		if( array_key_exists( 'cart_id', $_POST ) ) {
			$general_options = wc2_get_option( 'general' );
			foreach( (array)$_POST['cart_id'] as $cart_id ) {
				$post_id = ( isset($_POST['cart_post_id'][$cart_id]) ) ? $_POST['cart_post_id'][$cart_id] : 0;
				$item_id = ( isset($_POST['item_id'][$cart_id]) ) ? $_POST['item_id'][$cart_id] : 0;
				$sku_id = ( isset($_POST['sku_id'][$cart_id]) ) ? $_POST['sku_id'][$cart_id] : 0;
				$quantity = ( isset($_POST['quantity'][$cart_id]) ) ? $_POST['quantity'][$cart_id] : 1;
				$price = ( isset($_POST['sku_price'][$cart_id]) ) ? $_POST['sku_price'][$cart_id] : 0;
				$row_index = ( isset($_POST['row_index'][$cart_id]) ) ? $_POST['row_index'][$cart_id] : 1;
				$meta_type = ( isset($_POST['meta_type'][$cart_id]) ) ? $_POST['meta_type'][$cart_id] : array();
				$meta_key = ( isset($_POST['meta_key'][$cart_id]) ) ? $_POST['meta_key'][$cart_id] : array();

				if( empty($general_options['tax_rate']) ) {
					$tax = 0;
				} else {
					$materials = array(
						'total_price' => $price * $quantity,
						'discount' => 0,
						'shipping_charge' => 0,
						'cod_fee' => 0,
					);
					$tax = wc2_internal_tax( $materials );
				}
				$order_cart_data = wc2_get_order_cart_data( $_POST['order_id'], $cart_id );
				$cart_data = array_shift($order_cart_data);

				$data[ORDER_CART][$row_index][ORDER_CART_ID] = $cart_id;
				$data[ORDER_CART][$row_index][ORDER_CART_GROUP_ID] = 0;
				$data[ORDER_CART][$row_index][ORDER_CART_ROW_INDEX] = $row_index;
				$data[ORDER_CART][$row_index][ORDER_CART_POST_ID] = $post_id;
				$data[ORDER_CART][$row_index][ORDER_CART_ITEM_ID] = $item_id;
				$data[ORDER_CART][$row_index][ORDER_CART_ITEM_CODE] = $cart_data[ORDER_CART_ITEM_CODE];
				$data[ORDER_CART][$row_index][ORDER_CART_ITEM_NAME] = $cart_data[ORDER_CART_ITEM_NAME];
				$data[ORDER_CART][$row_index][ORDER_CART_SKU_ID] = $sku_id;
				$data[ORDER_CART][$row_index][ORDER_CART_SKU_CODE] = $cart_data[ORDER_CART_SKU_CODE];
				$data[ORDER_CART][$row_index][ORDER_CART_SKU_NAME] = $cart_data[ORDER_CART_SKU_NAME];
				$data[ORDER_CART][$row_index][ORDER_CART_PRICE] = $price;
				$data[ORDER_CART][$row_index][ORDER_CART_CPRICE] = $cart_data[ORDER_CART_CPRICE];
				$data[ORDER_CART][$row_index][ORDER_CART_QUANTITY] = $quantity;
				$data[ORDER_CART][$row_index][ORDER_CART_UNIT] = $cart_data[ORDER_CART_UNIT];
				$data[ORDER_CART][$row_index][ORDER_CART_TAX] = $tax;
				//$data[ORDER_CART][$row_index][ORDER_CART_DESTINATION_ID] = 0;
				$data[ORDER_CART][$row_index][ORDER_CART_META_TYPE] = maybe_unserialize( $meta_type );
				$data[ORDER_CART][$row_index][ORDER_CART_META_KEY] = maybe_unserialize( $meta_key );
			}
		}

		//Delivery
		$data[ORDER_DELIVERY] = array();
		$data[ORDER_DELIVERY][0][ORDER_DELIVERY_ID] = ( isset($_POST['delivery']['deli_id']) ) ? $_POST['delivery']['deli_id'] : 0;
		$data[ORDER_DELIVERY][0][ORDER_DELIVERY_ROW_INDEX] = 0;
		$data[ORDER_DELIVERY][0][ORDER_DELIVERY_NAME1] = ( isset($_POST['delivery']['name1']) ) ? $_POST['delivery']['name1'] : '';
		$data[ORDER_DELIVERY][0][ORDER_DELIVERY_NAME2] = ( isset($_POST['delivery']['name2']) ) ? $_POST['delivery']['name2'] : '';
		$data[ORDER_DELIVERY][0][ORDER_DELIVERY_NAME3] = ( isset($_POST['delivery']['name3']) ) ? $_POST['delivery']['name3'] : '';
		$data[ORDER_DELIVERY][0][ORDER_DELIVERY_NAME4] = ( isset($_POST['delivery']['name4']) ) ? $_POST['delivery']['name4'] : '';
		$data[ORDER_DELIVERY][0][ORDER_DELIVERY_COUNTRY] = ( isset($_POST['delivery']['country']) ) ? $_POST['delivery']['country'] : '';
		$data[ORDER_DELIVERY][0][ORDER_DELIVERY_ZIPCODE] = ( isset($_POST['delivery']['zipcode']) ) ? $_POST['delivery']['zipcode'] : '';
		$data[ORDER_DELIVERY][0][ORDER_DELIVERY_PREF] = ( isset($_POST['delivery']['pref']) ) ? $_POST['delivery']['pref'] : '';
		$data[ORDER_DELIVERY][0][ORDER_DELIVERY_ADDRESS1] = ( isset($_POST['delivery']['address1']) ) ? $_POST['delivery']['address1'] : '';
		$data[ORDER_DELIVERY][0][ORDER_DELIVERY_ADDRESS2] = ( isset($_POST['delivery']['address2']) ) ? $_POST['delivery']['address2'] : '';
		$data[ORDER_DELIVERY][0][ORDER_DELIVERY_TEL] = ( isset($_POST['delivery']['tel']) ) ? $_POST['delivery']['tel'] : '';
		$data[ORDER_DELIVERY][0][ORDER_DELIVERY_FAX] = ( isset($_POST['delivery']['fax']) ) ? $_POST['delivery']['fax'] : '';
		$data[ORDER_DELIVERY][0][ORDER_DELIVERY_META_TYPE] = ( isset($_POST['delivery']['meta_type']) ) ? $_POST['delivery']['meta_type'] : array();
		$data[ORDER_DELIVERY][0][ORDER_DELIVERY_META_KEY] = ( isset($_POST['delivery']['meta_type']) ) ? $_POST['delivery']['meta_type'] : array();

		//Custom Delivery
		$csde_keys = wc2_get_custom_field_keys(WC2_CSDE);
		if( !empty($csde_keys) && is_array($csde_keys) ){
			foreach($csde_keys as $key){
				list( $pfx, $csde_key ) = explode('_', $key, 2);
				if( array_key_exists(WC2_CUSTOM_DELIVERY, $_POST) and array_key_exists($csde_key, $_POST[WC2_CUSTOM_DELIVERY]) ) {
					if( is_array($_POST[WC2_CUSTOM_DELIVERY][$csde_key]) ) {
						$data[ORDER_DELIVERY][0][WC2_CUSTOM_DELIVERY][$csde_key] = serialize($_POST[WC2_CUSTOM_DELIVERY][$csde_key]);
					} else {
						$data[ORDER_DELIVERY][0][WC2_CUSTOM_DELIVERY][$csde_key] = $_POST[WC2_CUSTOM_DELIVERY][$csde_key];
					}
				} else {
					$data[ORDER_DELIVERY][0][WC2_CUSTOM_DELIVERY][$csde_key] = '';
				}
			}
		}

		$data = apply_filters( 'wc2_filter_admin_order_get_post_data', $data );
//wc2_log(print_r($data,true),"test.log");
		return $data;
	}

	/***********************************
	 * Check order data.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function check_order_data( $data ) {



		return true;
	}

	/***********************************
	 * Delete order data.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function delete_order_data( $order_id ) {
		$wc2_order = WC2_DB_Order::get_instance();
		$res = $wc2_order->delete_order_data( $order_id );
	}

	/***********************************
	 * Delete order data.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function delete_batch_order_data( $order ) {
		$wc2_order = WC2_DB_Order::get_instance();
		foreach( (array)$order as $idx => $order_id ) {
			$res = $wc2_order->delete_order_data( $order_id );
		}
	}

	/***********************************
	 * Order detail list download.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function download_order_detail_list() {
		$order_list = new WC2_Order_List_Table();
		//1ページあたりのテーブルの行数
		$per_page = $order_list->get_items_per_page( self::$per_page_slug );
		//ソート
		$args = $order_list->sort_culum_order_by($per_page);
		//データ
		$list_data = $order_list->get_list_data($args);

		$list = '';
		$opt_order = wc2_get_option( 'opt_order' );
		$chk_detail = ( !empty($opt_order['chk_detail']) ) ? $opt_order['chk_detail'] : array();

		//--------------------- checkbox Check  ---------------------//
		$chk_detail['ID'] = 1;
		$chk_detail['deco_id'] = 1;
		$chk_detail['date'] = isset($_REQUEST['check']['date']) ? 1: 0;
		$chk_detail['member_id'] = isset($_REQUEST['check']['member_id']) ? 1: 0;
		$chk_detail['name'] = isset($_REQUEST['check']['name']) ? 1: 0;
		$chk_detail['delivery_method'] = isset($_REQUEST['check']['delivery_method']) ? 1: 0;
		$chk_detail['shipping_date'] = isset($_REQUEST['check']['shipping_date']) ? 1: 0;
		$chk_detail['item_code'] = 1;
		$chk_detail['sku_code'] = isset($_REQUEST['check']['sku_code']) ? 1: 0;
		$chk_detail['item_name'] = isset($_REQUEST['check']['item_name']) ? 1: 0;
		$chk_detail['sku_name'] = isset($_REQUEST['check']['sku_name']) ? 1: 0;
		$chk_detail['options'] = isset($_REQUEST['check']['options']) ? 1: 0;
		$chk_detail['quantity'] = 1;
		$chk_detail['price'] = 1;
		$chk_detail['unit'] = isset($_REQUEST['check']['unit']) ? 1: 0;

		$opt_order['chk_detail'] = apply_filters( 'wc2_filter_admin_order_list_chk_detail', $chk_detail );
		wc2_update_option( 'opt_order', $opt_order );

		//---------------------------- TITLE -----------------------------//
		$title = '';

		$title .= '"'. __('ID', 'wc2') .'"';
		if( 1 == $chk_detail['deco_id'] ) $title .= ',"'. __('Order number', 'wc2') .'"';
		if( 1 == $chk_detail['date'] ) $title .= ',"'. __('Order date', 'wc2') .'"';
		if( 1 == $chk_detail['member_id'] ) $title .= ',"'. __('Membership ID', 'wc2') .'"';
		if( 1 == $chk_detail['name'] ) $title .= ',"'. __('Name', 'wc2') .'"';
		if( 1 == $chk_detail['delivery_method'] ) $title .= ',"'. __('Delivery method', 'wc2') .'"';
		if( 1 == $chk_detail['shipping_date'] ) $title .= ',"'. __('Shipping date', 'wc2') .'"';
		if( 1 == $chk_detail['item_code'] ) $title .= ',"'. __('Item code', 'wc2') .'"';
		if( 1 == $chk_detail['sku_code'] ) $title .= ',"'. __('SKU code', 'wc2') .'"';
		if( 1 == $chk_detail['item_name'] ) $title .= ',"'. __('Item name', 'wc2') .'"';
		if( 1 == $chk_detail['sku_name'] ) $title .= ',"'. __('SKU display name', 'wc2') .'"';
		if( 1 == $chk_detail['options'] ) $title .= ',"'. __('Options for items', 'wc2') .'"';
		if( 1 == $chk_detail['quantity'] ) $title .= ',"'. __('Quantity', 'wc2') .'"';
		if( 1 == $chk_detail['price'] ) $title .= ',"'. __('Unit price', 'wc2') .'"';
		if( 1 == $chk_detail['unit'] ) $title .= ',"'. __('Unit', 'wc2') .'"';

		$list .= apply_filters( 'wc2_filter_admin_order_list_dl_detail_title', $title, $chk_detail );
		$list .= "\n";

		//----------------------------- DATA -----------------------------//
		foreach( (array)$list_data as $data ){
			$order_id = $data['ID'];
			$cart = wc2_get_order_cart_data($order_id);

			foreach( $cart as $cart_row ){
				$line = '"'. $order_id .'"';
				if( 1 == $chk_detail['deco_id'] ) $line .= ',"'.$data['dec_order_id'].'"';
				if( 1 == $chk_detail['date'] ) $line .= ',"'.$data['order_date'].'"';
				if( 1 == $chk_detail['member_id'] ) $line .= ',"'.$data['member_id'].'"';
				if( 1 == $chk_detail['name'] ) $line .= ',"'.wc2_entity_decode($data['name']).'"';
				if( 1 == $chk_detail['delivery_method'] ) $line .= ',"'.wc2_entity_decode($data['delivery_name']).'"';
				if( 1 == $chk_detail['shipping_date'] ) $line .= ',"'.$data['order_modified'].'"';
				if( 1 == $chk_detail['item_code'] ) $line .= ',"'.$cart_row['item_code'].'"';
				if( 1 == $chk_detail['sku_code'] ) $line .= ',"'.$cart_row['sku_code'].'"';
				if( 1 == $chk_detail['item_name'] ) $line .= ',"'.wc2_entity_decode($cart_row['item_name']).'"';
				if( 1 == $chk_detail['sku_name'] ) $line .= ',"'.wc2_entity_decode($cart_row['sku_name']).'"';
				if( 1 == $chk_detail['options'] ) {
					$optstr = '';
					$options = isset( $cart_row['meta_type']['option']) ? $cart_row['meta_type']['option']: '';
					if(is_array($options) && count($options) > 0) {
						foreach((array)$options as $key => $value) {
							$meta_value = maybe_unserialize($value);
							if( is_array($meta_value) ){
								$meta_vals = '';
								foreach($meta_value as $array_val){
									$meta_vals .= ' '.urldecode($array_val);
								}
								$optstr .= wc2_entity_decode(urldecode($key).':'.$meta_vals).' ';
							}else{
								$optstr .= wc2_entity_decode(urldecode($key).':'.urldecode($meta_value)).' ';
							}
						}
					}
					$line .= ',"'.$optstr.'"';
				}
				if( 1 == $chk_detail['quantity'] ) $line .= ',"'.$cart_row['quantity'].'"';
				if( 1 == $chk_detail['price'] ) $line .= ',"'.wc2_crform($cart_row['price'], false, false, false).'"';
				if( 1 == $chk_detail['unit'] ) $line .= ',"'.wc2_entity_decode($cart_row['unit']).'"';

				$list .= apply_filters( 'wc2_filter_admin_order_list_dl_detail', $line, $chk_detail, $data, $cart_row );
				$list .= "\n";
			}
		}

		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=wc2_order_detail_list.csv");
		mb_http_output("pass");
		print(mb_convert_encoding($list, "SJIS-win", "UTF-8"));
		exit();
	}

	/***********************************
	 * Order list download.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function download_order_list() {
		$order_list = new WC2_Order_List_Table();
		//1ページあたりのテーブルの行数
		$per_page = $order_list->get_items_per_page( self::$per_page_slug );
		//ソート
		$args = $order_list->sort_culum_order_by($per_page);
		//データ
		$list_data = $order_list->get_list_data($args);

		$wc2_order = WC2_DB_Order::get_instance();
		$system_options = wc2_get_option( 'system' );
		$applyform = wc2_get_apply_addressform( $system_options['addressform'] );
		$management_status = wc2_get_option( 'management_status' );
		$receipt_status = wc2_get_option( 'receipt_status' );
		$order_type = wc2_get_option( 'order_type' );
		$locale_options = wc2_get_option( 'locale_options' );

		$list = '';
		$opt_order = wc2_get_option( 'opt_order' );
		$chk_order = ( !empty($opt_order['chk_order']) ) ? $opt_order['chk_order'] : array();

		//--------------------- checkbox Check ---------------------//
		//-------- Customer -------//
		$chk_order['ID'] = 1;
		$chk_order['deco_id'] = 1;
		$chk_order['order_date'] = 1;
		$chk_order['member_id'] = isset($_REQUEST['check']['member_id']) ? 1 : 0;

		$cscs_head = wc2_get_custom_field_keys(WC2_CSCS, 'head');
		if( !empty($cscs_head) ){
			foreach( $cscs_head as $val ){
				$chk_order[$val] = isset($_REQUEST['check'][$val]) ? 1 : 0;
			}
		}
		$chk_order['email'] = isset($_REQUEST['check']['email']) ? 1 : 0;

		$cscs_beforename = wc2_get_custom_field_keys(WC2_CSCS, 'beforename');
		if( !empty($cscs_beforename) ){
			foreach( $cscs_beforename as $val ){
				$chk_order[$val] = isset($_REQUEST['check'][$val]) ? 1 : 0;
			}
		}
		$chk_order['name'] = 1;
		$chk_order['kana'] = isset($_REQUEST['check']['kana']) ? 1 : 0;

		$cscs_aftername = wc2_get_custom_field_keys(WC2_CSCS, 'aftername');
		if( !empty($cscs_aftername) ){
			foreach( $cscs_aftername as $val ){
				$chk_order[$val] = isset($_REQUEST['check'][$val]) ? 1 : 0;
			}
		}
		$chk_order['country'] = isset($_REQUEST['check']['country']) ? 1 : 0;
		$chk_order['zipcode'] = isset($_REQUEST['check']['zipcode']) ? 1 : 0;
		$chk_order['pref'] = 1;
		$chk_order['address1'] = 1;
		$chk_order['address2'] = 1;
		$chk_order['tel'] = isset($_REQUEST['check']['tel']) ? 1 : 0;
		$chk_order['fax'] = isset($_REQUEST['check']['fax']) ? 1 : 0;

		$cscs_bottom = wc2_get_custom_field_keys(WC2_CSCS, 'bottom');
		if( !empty($cscs_bottom) ){
			foreach( $cscs_bottom as $val ){
				$chk_order[$val] = isset($_REQUEST['check'][$val]) ? 1 : 0;
			}
		}
		$cscs_other = wc2_get_custom_field_keys(WC2_CSCS, 'other');
		if( !empty($cscs_other) ){
			foreach( $cscs_other as $val ){
				$chk_order[$val] = isset($_REQUEST['check'][$val]) ? 1 : 0;
			}
		}

		//-------- Delivery -------//
		$csde_head = wc2_get_custom_field_keys(WC2_CSDE, 'head');
		if( !empty($csde_head) ){
			foreach( $csde_head as $val ){
				$chk_order[$val] = isset($_REQUEST['check'][$val]) ? 1 : 0;
			}
		}

		$csde_beforename = wc2_get_custom_field_keys(WC2_CSDE, 'beforename');
		if( !empty($csde_beforename) ){
			foreach( $csde_beforename as $val ){
				$chk_order[$val] = isset($_REQUEST['check'][$val]) ? 1 : 0;
			}
		}
		$chk_order['delivery_name'] = 1;
		$chk_order['delivery_kana'] = isset($_REQUEST['check']['delivery_kana']) ? 1 : 0;

		$csde_aftername = wc2_get_custom_field_keys(WC2_CSDE, 'aftername');
		if( !empty($csde_aftername) ){
			foreach( $csde_aftername as $val ){
				$chk_order[$val] = isset($_REQUEST['check'][$val]) ? 1 : 0;
			}
		}
		$chk_order['delivery_country'] = isset($_REQUEST['check']['delivery_country']) ? 1 : 0;
		$chk_order['delivery_zipcode'] = isset($_REQUEST['check']['delivery_zipcode']) ? 1 : 0;
		$chk_order['delivery_pref'] = 1;
		$chk_order['delivery_address1'] = 1;
		$chk_order['delivery_address2'] = 1;
		$chk_order['delivery_tel'] = isset($_REQUEST['check']['delivery_tel']) ? 1 : 0;
		$chk_order['delivery_fax'] = isset($_REQUEST['check']['delivery_fax']) ? 1 : 0;

		$csde_bottom = wc2_get_custom_field_keys(WC2_CSDE, 'bottom');
		if( !empty($csde_bottom) ){
			foreach( $csde_bottom as $val ){
				$chk_order[$val] = isset($_REQUEST['check'][$val]) ? 1 : 0;
			}
		}

		$csde_other = wc2_get_custom_field_keys(WC2_CSDE, 'other');
		if( !empty($csde_other) ){
			foreach( $csde_other as $val ){
				$chk_order[$val] = isset($_REQUEST['check'][$val]) ? 1 : 0;
			}
		}

		//--------- Order --------//
		$chk_order['shipping_date'] = isset($_REQUEST['check']['shipping_date']) ? 1 : 0;
		$chk_order['payment_method'] = isset($_REQUEST['check']['payment_method']) ? 1 : 0;
		$chk_order['delivery_method'] = isset($_REQUEST['check']['delivery_method']) ? 1 : 0;
		$chk_order['delivery_date'] = isset($_REQUEST['check']['delivery_date']) ? 1 : 0;
		$chk_order['delivery_time'] = isset($_REQUEST['check']['delivery_time']) ? 1 : 0;
		$chk_order['delidue_date'] = isset($_REQUEST['check']['delidue_date']) ? 1 : 0;
		$chk_order['order_status'] = isset($_REQUEST['check']['order_status']) ? 1 : 0;
		$chk_order['receipt_status'] = isset($_REQUEST['check']['receipt_status']) ? 1 : 0;
		$chk_order['receipted_date'] = isset($_REQUEST['check']['receipted_date']) ? 1 : 0;
		$chk_order['order_type'] = isset($_REQUEST['check']['order_type']) ? 1 : 0;
		$chk_order['total_amount'] = isset($_REQUEST['check']['total_amount']) ? 1 : 0;
		$chk_order['getpoint'] = isset($_REQUEST['check']['getpoint']) ? 1 : 0;
		$chk_order['usedpoint'] = isset($_REQUEST['check']['usedpoint']) ? 1 : 0;
		$chk_order['discount'] = isset($_REQUEST['check']['discount']) ? 1 : 0;
		$chk_order['shipping_charge'] = isset($_REQUEST['check']['shipping_charge']) ? 1 : 0;
		$chk_order['cod_fee'] = isset($_REQUEST['check']['cod_fee']) ? 1 : 0;
		$chk_order['tax'] = isset($_REQUEST['check']['tax']) ? 1 : 0;

		$csod_beforeremarks = wc2_get_custom_field_keys(WC2_CSOD, 'beforeremarks');
		if( !empty($csod_beforeremarks) ){
			foreach( $csod_beforeremarks as $val ){
				$chk_order[$val] = isset($_REQUEST['check'][$val]) ? 1 : 0;
			}
		}
		$chk_order['note'] = isset($_REQUEST['check']['note']) ? 1 : 0;

		$csod_other = wc2_get_custom_field_keys(WC2_CSOD, 'other');
		if( !empty($csod_other) ){
			foreach( $csod_other as $val ){
				$chk_order[$val] = isset($_REQUEST['check'][$val]) ? 1 : 0;
			}
		}

		$opt_order['chk_order'] = apply_filters( 'wc2_filter_admin_order_list_chk_order', $chk_order );
		wc2_update_option( 'opt_order', $opt_order );

		//---------------------------- TITLE -----------------------------//
		$title = '';

		//-------- Customer --------//
		$title .= '"'. __('ID', 'wc2') .'"';
		if( 1 == $chk_order['deco_id'] ) $title .= ',"'. __('Order number', 'wc2') .'"';
		if( 1 == $chk_order['order_date'] ) $title .= ',"'. __('Order date', 'wc2') .'"';
		if( 1 == $chk_order['member_id'] ) $title .= ',"'. __('Membership ID', 'wc2') .'"';

		//cscs_head
		if( !empty($cscs_head) ){
			foreach( $cscs_head as $val ){
				if( 1 == $chk_order[$val] ){
					$name = wc2_get_custom_field_value($val, 'name');
					$title .= ',"'. wc2_entity_decode($name) .'"';
				}
			}
		}
		if( 1 == $chk_order['email'] ) $title .= ',"'. __('E-mail', 'wc2') .'"';

		//cscs_beforename
		if( !empty($cscs_beforename) ){
			foreach( $cscs_beforename as $val ){
				if( 1 == $chk_order[$val] ){
					$name = wc2_get_custom_field_value($val, 'name');
					$title .= ',"'. wc2_entity_decode($name) .'"';
				}
			}
		}
		if( 1 == $chk_order['name'] ) $title .= ',"'. __('Name', 'wc2') .'"';
		if( 1 == $chk_order['kana'] ) $title .= ',"'. __('Kana', 'wc2') .'"';

		//cscs_aftername
		if( !empty($cscs_aftername) ){
			foreach( $cscs_aftername as $val ){
				if( 1 == $chk_order[$val] ){
					$name = wc2_get_custom_field_value($val, 'name');
					$title .= ',"'. wc2_entity_decode($name) .'"';
				}
			}
		}
		if( 'JP' == $applyform){
			if( 1 == $chk_order['country'] ) $title .= ',"'. __('Country', 'wc2') .'"';
			if( 1 == $chk_order['zipcode'] ) $title .= ',"'. __('Postal Code', 'wc2') .'"';
			if( 1 == $chk_order['pref'] ) $title .= ',"'. __('Prefecture', 'wc2') .'"';
			if( 1 == $chk_order['address1'] ) $title .= ',"'. __('City', 'wc2') .'"';
			if( 1 == $chk_order['address2'] ) $title .= ',"'. __('Building name, floor, room number', 'wc2') .'"';
			if( 1 == $chk_order['tel'] ) $title .= ',"'. __('Phone number', 'wc2') .'"';
			if( 1 == $chk_order['fax'] ) $title .= ',"'. __('FAX number', 'wc2') .'"';
		}else{
			if( 1 == $chk_order['address2'] ) $title .= ',"'. __('Building name, floor, room number', 'wc2') .'"';
			if( 1 == $chk_order['address1'] ) $title .= ',"'. __('City', 'wc2') .'"';
			if( 1 == $chk_order['pref'] ) $title .= ',"'. __('Prefecture', 'wc2') .'"';
			if( 1 == $chk_order['zipcode'] ) $title .= ',"'. __('Postal Code', 'wc2') .'"';
			if( 1 == $chk_order['country'] ) $title .= ',"'. __('Country', 'wc2') .'"';
			if( 1 == $chk_order['tel'] ) $title .= ',"'. __('Phone number', 'wc2') .'"';
			if( 1 == $chk_order['fax'] ) $title .= ',"'. __('FAX number', 'wc2') .'"';
		}

		//cscs_bottom
		if( !empty($cscs_bottom) ){
			foreach( $cscs_bottom as $val ){
				if( 1 == $chk_order[$val] ){
					$name = wc2_get_custom_field_value($val, 'name');
					$title .= ',"'. wc2_entity_decode($name) .'"';
				}
			}
		}

		//cscs_other
		if( !empty($cscs_other) ){
			foreach( $cscs_other as $val ){
				if( 1 == $chk_order[$val] ){
					$name = wc2_get_custom_field_value($val, 'name');
					$title .= ',"'. wc2_entity_decode($name) .'"';
				}
			}
		}

		//-------- Delivery -------//
		//csde_head
		if( !empty($csde_head) ){
			foreach( $csde_head as $val ){
				if( 1 == $chk_order[$val] ){
					$name = wc2_get_custom_field_value($val, 'name');
					$title .= ',"'. wc2_entity_decode($name) .'"';
				}
			}
		}

		//csde_beforename
		if( !empty($csde_beforename) ){
			foreach( $csde_beforename as $val ){
				if( 1 == $chk_order[$val] ){
					$name = wc2_get_custom_field_value($val, 'name');
					$title .= ',"'. wc2_entity_decode($name) .'"';
				}
			}
		}
		if( 1 == $chk_order['delivery_name'] ) $title .= ',"'. __('Name', 'wc2') .'"';
		if( 1 == $chk_order['delivery_kana'] ) $title .= ',"'. __('Kana', 'wc2') .'"';

		//csde_aftername
		if( !empty($csde_aftername) ){
			foreach( $csde_aftername as $val ){
				if( 1 == $chk_order[$val] ){
					$name = wc2_get_custom_field_value($val, 'name');
					$title .= ',"'. wc2_entity_decode($name) .'"';
				}
			}
		}
		if( 'JP' == $applyform){
			if( 1 == $chk_order['delivery_country'] ) $title .= ',"'. __('Shipping country', 'wc2') .'"';
			if( 1 == $chk_order['delivery_zipcode'] ) $title .= ',"'. __('Shipping postal code', 'wc2') .'"';
			if( 1 == $chk_order['delivery_pref'] ) $title .= ',"'. __('Shipping prefecture', 'wc2') .'"';
			if( 1 == $chk_order['delivery_address1'] ) $title .= ',"'. __('Shipping city', 'wc2') .'"';
			if( 1 == $chk_order['delivery_address2'] ) $title .= ',"'. __('Shipping building name, floor, room number', 'wc2') .'"';
			if( 1 == $chk_order['delivery_tel'] ) $title .= ',"'. __('Shipping phone number', 'wc2') .'"';
			if( 1 == $chk_order['delivery_fax'] ) $title .= ',"'. __('Shipping FAX number', 'wc2') .'"';
		}else{
			if( 1 == $chk_order['delivery_address2'] ) $title .= ',"'. __('Shipping building name, floor, room number', 'wc2') .'"';
			if( 1 == $chk_order['delivery_address1'] ) $title .= ',"'. __('Shipping city', 'wc2') .'"';
			if( 1 == $chk_order['delivery_pref'] ) $title .= ',"'. __('Shipping prefecture', 'wc2') .'"';
			if( 1 == $chk_order['delivery_zipcode'] ) $title .= ',"'. __('Shipping postal code', 'wc2') .'"';
			if( 1 == $chk_order['delivery_country'] ) $title .= ',"'. __('Shipping country', 'wc2') .'"';
			if( 1 == $chk_order['delivery_tel'] ) $title .= ',"'. __('Shipping phone number', 'wc2') .'"';
			if( 1 == $chk_order['delivery_fax'] ) $title .= ',"'. __('Shipping FAX number', 'wc2') .'"';
		}

		//csde_bottom
		if( !empty($csde_bottom) ){
			foreach( $csde_bottom as $val ){
				if( 1 == $chk_order[$val] ){
					$name = wc2_get_custom_field_value($val, 'name');
					$title .= ',"'. wc2_entity_decode($name) .'"';
				}
			}
		}

		//csde_other
		if( !empty($csde_other) ){
			foreach( $csde_other as $val ){
				if( 1 == $chk_order[$val] ){
					$name = wc2_get_custom_field_value($val, 'name');
					$title .= ',"'. wc2_entity_decode($name) .'"';
				}
			}
		}

		//--------- Order ---------//
		if( 1 == $chk_order['shipping_date'] ) $title .= ',"'. __('Shipping date', 'wc2') .'"';
		if( 1 == $chk_order['payment_method'] ) $title .= ',"'. __('Payment method', 'wc2') .'"';
		if( 1 == $chk_order['delivery_method'] ) $title .= ',"'. __('Delivery method', 'wc2') .'"';
		if( 1 == $chk_order['delivery_date'] ) $title .= ',"'. __('Delivery date', 'wc2') .'"';
		if( 1 == $chk_order['delivery_time'] ) $title .= ',"'. __('Delivery time', 'wc2') .'"';
		if( 1 == $chk_order['delidue_date'] ) $title .= ',"'. __('Shipping schedule date', 'wc2') .'"';
		if( 1 == $chk_order['order_status'] ) $title .= ',"'. __('Order status', 'wc2') .'"';
		if( 1 == $chk_order['receipt_status'] ) $title .= ',"'. __('Receipt status', 'wc2') .'"';
		if( 1 == $chk_order['receipted_date'] ) $title .= ',"'. __('Receipted date', 'wc2') .'"';
		if( 1 == $chk_order['order_type'] ) $title .= ',"'. __('Order type', 'wc2') .'"';
		if( 1 == $chk_order['total_amount'] ) $title .= ',"'. __('Total Amount', 'wc2') .'"';
		if( 1 == $chk_order['getpoint'] ) $title .= ',"'. __('Granted points', 'wc2') .'"';
		if( 1 == $chk_order['usedpoint'] ) $title .= ',"'. __('Used points', 'wc2') .'"';
		if( 1 == $chk_order['discount'] ) $title .= ',"'. __('Discount', 'wc2') .'"';
		if( 1 == $chk_order['shipping_charge'] ) $title .= ',"'. __('Shipping charges', 'wc2') .'"';
		if( 1 == $chk_order['cod_fee'] ) $title .= ',"'. __('COD fee', 'wc2') .'"';
		if( 1 == $chk_order['tax'] ) $title .= ',"'. __('Consumption tax', 'wc2') .'"';

		//csod_beforeremarks
		if( !empty($csod_beforeremarks) ){
			foreach( $csod_beforeremarks as $val ){
				if( 1 == $chk_order[$val] ){
					$name = wc2_get_custom_field_value($val, 'name');
					$title .= ',"'. wc2_entity_decode($name) .'"';
				}
			}
		}

		if( 1 == $chk_order['note'] ) $title .= ',"'. __('Notes', 'wc2') .'"';

		//csod_other
		if( !empty($csod_other) ){
			foreach( $csod_other as $val ){
				if( 1 == $chk_order[$val] ){
					$name = wc2_get_custom_field_value($val, 'name');
					$title .= ',"'. wc2_entity_decode($name) .'"';
				}
			}
		}

		$list .= apply_filters( 'wc2_filter_admin_order_list_dl_order_title', $title, $chk_order );
		$list .= "\n";

		//----------------------------- DATA ----------------------------//
		foreach( (array)$list_data as $row ){
			$order_id = $row['ID'];
			$data = $wc2_order->get_order_data($order_id);
			$delivery_data = $data['delivery'][0];

			//-------- Customer -------//
			$line = '"'. $order_id .'"';
			if( 1 == $chk_order['deco_id'] ) $line .= ',"'.$data['dec_order_id'].'"';
			if( 1 == $chk_order['order_date'] ) $line .= ',"'.$data['order_date'].'"';
			if( 1 == $chk_order['member_id'] ) $line .= ',"'.$data['member_id'].'"';

			//cscs_head
			if( !empty($cscs_head) ){
				foreach( $cscs_head as $val){
					if( 1 == $chk_order[$val] ){
						list( $pfx, $cskey ) = explode( '_', $val, 2 );
						$value = isset($data[WC2_CUSTOM_CUSTOMER][$cskey]) ?  $data[WC2_CUSTOM_CUSTOMER][$cskey]: '';
						$line .= ',"'. wc2_entity_decode($value) .'"';
					}
				}
			}
			if( 1 == $chk_order['email'] ) $line .= ',"'.wc2_entity_decode($data['email']).'"';

			//cscs_beforename
			if( !empty($cscs_beforename) ){
				foreach( $cscs_beforename as $val ){
					if( 1 == $chk_order[$val] ){
						list( $pfx, $cskey ) = explode( '_', $val, 2 );
						$value = isset($data[WC2_CUSTOM_CUSTOMER][$cskey]) ?  $data[WC2_CUSTOM_CUSTOMER][$cskey]: '';
						$line .= ',"'. wc2_entity_decode($value) .'"';
					}
				}
			}
			if( 1 == $chk_order['name'] ) $line .= ',"'. wc2_entity_decode($data['name1'] .' '. $data['name2']) .'"';
			if( 1 == $chk_order['kana'] ) $line .= ',"'. wc2_entity_decode($data['name3'] .' '. $data['name4']) .'"';

			//cscs_aftername
			if( !empty($cscs_aftername) ){
				foreach( $cscs_aftername as $val ){
					if( 1 == $chk_order[$val] ){
						list( $pfx, $cskey ) = explode( '_', $val, 2 );
						$value = isset($data[WC2_CUSTOM_CUSTOMER][$cskey]) ?  $data[WC2_CUSTOM_CUSTOMER][$cskey]: '';
						$line .= ',"'. wc2_entity_decode($value) .'"';
					}
				}
			}

			if( 'JP' == $applyform){
				if( 1 == $chk_order['country'] ) $line .= ',"'. $locale_options['country'][$data['country']] .'"';
				if( 1 == $chk_order['zipcode'] ) $line .= ',"'. $data['zipcode'] .'"';
				if( 1 == $chk_order['pref'] ) $line .= ',"'. wc2_entity_decode($data['pref']) .'"';
				if( 1 == $chk_order['address1'] ) $line .= ',"'. wc2_entity_decode($data['address1']) .'"';
				if( 1 == $chk_order['address2'] ) $line .= ',"'. wc2_entity_decode($data['address2']) .'"';
				if( 1 == $chk_order['tel'] ) $line .= ',"'. $data['tel'] .'"';
				if( 1 == $chk_order['fax'] ) $line .= ',"'. $data['fax'] .'"';
			}else{
				if( 1 == $chk_order['address2'] ) $line .= ',"'. wc2_entity_decode($data['address2']) .'"';
				if( 1 == $chk_order['address1'] ) $line .= ',"'. wc2_entity_decode($data['address1']) .'"';
				if( 1 == $chk_order['pref'] ) $line .= ',"'. wc2_entity_decode($data['pref']) .'"';
				if( 1 == $chk_order['zipcode'] ) $line .= ',"'. $data['zipcode'] .'"';
				if( 1 == $chk_order['country'] ) $line .= ',"'. $locale_options['country'][$data['country']] .'"';
				if( 1 == $chk_order['tel'] ) $line .= ',"'. $data['tel'] .'"';
				if( 1 == $chk_order['fax'] ) $line .= ',"'. $data['fax'] .'"';
			}

			//cscs_bottom
			if( !empty($cscs_bottom) ){
				foreach( $cscs_bottom as $val ){
					if( 1 == $chk_order[$val] ){
						list( $pfx, $cskey ) = explode( '_', $val, 2 );
						$value = isset($data[WC2_CUSTOM_CUSTOMER][$cskey]) ?  $data[WC2_CUSTOM_CUSTOMER][$cskey]: '';
						$line .= ',"'. wc2_entity_decode($value) .'"';
					}
				}
			}

			//cscs_other
			if( !empty($cscs_other) ){
				foreach( $cscs_other as $val ){
					if( 1 == $chk_order[$val] ){
						list( $pfx, $cskey ) = explode( '_', $val, 2 );
						$value = isset($data[WC2_CUSTOM_CUSTOMER][$cskey]) ?  $data[WC2_CUSTOM_CUSTOMER][$cskey]: '';
						$line .= ',"'. wc2_entity_decode($value) .'"';
					}
				}
			}

			//-------- Delivery -------//
			//csde_head
			if( !empty($csde_head) ){
				foreach( $csde_head as $val ){
					if( 1 == $chk_order[$val] ){
						list( $pfx, $cskey ) = explode( '_', $val, 2 );
						$value = isset($data[WC2_CUSTOM_CUSTOMER][$cskey]) ?  $data[WC2_CUSTOM_CUSTOMER][$cskey]: '';
						$line .= ',"'. wc2_entity_decode($value) .'"';
					}
				}
			}

			//csde_beforename
			if( !empty($csde_beforename) ){
				foreach( $csde_beforename as $val ){
					if( 1 == $chk_order[$val] ){
						list( $pfx, $cskey ) = explode( '_', $val, 2 );
						$value = isset($data[WC2_CUSTOM_CUSTOMER][$cskey]) ?  $data[WC2_CUSTOM_CUSTOMER][$cskey]: '';
						$line .= ',"'. wc2_entity_decode($value) .'"';
					}
				}
			}
			if( 1 == $chk_order['delivery_name'] ) $line .= ',"'. wc2_entity_decode($delivery_data['name1'] .' '. $delivery_data['name2']) .'"';
			if( 1 == $chk_order['delivery_kana'] ) $line .= ',"'. wc2_entity_decode($delivery_data['name3'] .' '. $delivery_data['name4']) .'"';

			//csde_aftername
			if( !empty($csde_aftername) ){
				foreach( $csde_aftername as $val ){
					if( 1 == $chk_order[$val] ){
						list( $pfx, $cskey ) = explode( '_', $val, 2 );
						$value = isset($data[WC2_CUSTOM_CUSTOMER][$cskey]) ?  $data[WC2_CUSTOM_CUSTOMER][$cskey]: '';
						$line .= ',"'. wc2_entity_decode($value) .'"';
					}
				}
			}

			if( 'JP' == $applyform ) {
				if( 1 == $chk_order['delivery_country'] ) $line .= ',"'. $locale_options['country'][$delivery_data['country']] .'"';
				if( 1 == $chk_order['delivery_zipcode'] ) $line .= ',"'. $delivery_data['zipcode'] .'"';
				if( 1 == $chk_order['delivery_pref'] ) $line .= ',"'. $delivery_data['pref'] .'"';
				if( 1 == $chk_order['delivery_address1'] ) $line .= ',"'. $delivery_data['address1'] .'"';
				if( 1 == $chk_order['delivery_address2'] ) $line .= ',"'. $delivery_data['address2'] .'"';
				if( 1 == $chk_order['delivery_tel'] ) $line .= ',"'. $delivery_data['tel'] .'"';
				if( 1 == $chk_order['delivery_fax'] ) $line .= ',"'. $delivery_data['fax'] .'"';
			} else {
				if( 1 == $chk_order['delivery_address2'] ) $line .= ',"'. $delivery_data['address2'] .'"';
				if( 1 == $chk_order['delivery_address1'] ) $line .= ',"'. $delivery_data['address1'] .'"';
				if( 1 == $chk_order['delivery_pref'] ) $line .= ',"'. $delivery_data['pref'] .'"';
				if( 1 == $chk_order['delivery_zipcode'] ) $line .= ',"'. $delivery_data['zipcode'] .'"';
				if( 1 == $chk_order['delivery_country'] ) $line .= ',"'. $locale_options['country'][$delivery_data['country']] .'"';
				if( 1 == $chk_order['delivery_tel'] ) $line .= ',"'. $delivery_data['tel'] .'"';
				if( 1 == $chk_order['delivery_fax'] ) $line .= ',"'. $delivery_data['fax'] .'"';
			}

			//csde_bottom
			if( !empty($csde_bottom) ){
				foreach( $csde_bottom as $val ){
					if( 1 == $chk_order[$val] ){
						list( $pfx, $cskey ) = explode( '_', $val, 2 );
						$value = isset($data[WC2_CUSTOM_CUSTOMER][$cskey]) ?  $data[WC2_CUSTOM_CUSTOMER][$cskey]: '';
						$line .= ',"'. wc2_entity_decode($value) .'"';
					}
				}
			}

			//csde_other
			if( !empty($csde_other) ){
				foreach( $csde_other as $val ){
					if( 1 == $chk_order[$val] ){
						list( $pfx, $cskey ) = explode( '_', $val, 2 );
						$value = isset($data[WC2_CUSTOM_CUSTOMER][$cskey]) ?  $data[WC2_CUSTOM_CUSTOMER][$cskey]: '';
						$line .= ',"'. wc2_entity_decode($value) .'"';
					}
				}
			}

			//--------- Order ---------//
			if( 1 == $chk_order['shipping_date'] ) $line .= ',"'. $data['order_modified'] .'"';
			if( 1 == $chk_order['payment_method'] ) $line .= ',"'. wc2_entity_decode($data['payment_name']) .'"';
			if( 1 == $chk_order['delivery_method'] ) $line .= ',"'. wc2_entity_decode($data['delivery_name']) .'"';
			if( 1 == $chk_order['delivery_date'] ) $line .= ',"'. $data['delivery_date'] .'"';
			if( 1 == $chk_order['delivery_time'] ) $line .= ',"'. $data['delivery_time'] .'"';
			if( 1 == $chk_order['delidue_date'] ) $line .= ',"'. $data['delidue_date'] .'"';
			if( 1 == $chk_order['order_status'] ){
				$management_status_name = array_key_exists($data['order_status'], $management_status) ? $management_status[$data['order_status']]: '';
				$line .= ',"'. wc2_entity_decode($management_status_name) .'"';
			}
			if( 1 == $chk_order['receipt_status'] ) {
				$receipt_status_name = array_key_exists($data['receipt_status'], $receipt_status) ? $receipt_status[$data['receipt_status']]: '';
				$line .= ',"'. wc2_entity_decode($receipt_status_name) .'"';
			}
			if( 1 == $chk_order['receipted_date'] ) $line .= ',"'. $data['receipted_date'] .'"';
			if( 1 == $chk_order['order_type'] ){
				$order_type_name = array_key_exists($data['order_type'], $order_type) ? $order_type[$data['order_type']]: '';
				$line .= ',"'. wc2_entity_decode($order_type_name) .'"';
			}
			if( 1 == $chk_order['total_amount'] ){
				$total_price = $data['item_total_price'] - $data['usedpoint'] + $data['discount'] + $data['shipping_charge'] + $data['cod_fee'] + $data['tax'];
				$line .= ',"'. $total_price .'"';
			}
			if( 1 == $chk_order['getpoint'] ) $line .= ',"'. $data['getpoint'] .'"';
			if( 1 == $chk_order['usedpoint'] ) $line .= ',"'. $data['usedpoint'] .'"';
			if( 1 == $chk_order['discount'] ) $line .= ',"'. $data['discount'] .'"';
			if( 1 == $chk_order['shipping_charge'] ) $line .= ',"'. $data['shipping_charge'] .'"';
			if( 1 == $chk_order['cod_fee'] ) $line .= ',"'. $data['cod_fee'] .'"';
			if( 1 == $chk_order['tax'] ) $line .= ',"'. $data['tax'] .'"';

			//csod_beforeremarks
			if( !empty($csod_beforeremarks) ){
				foreach( $csod_beforeremarks as $val ){
					if( 1 == $chk_order[$val] ){
						list( $pfx, $cskey ) = explode( '_', $val, 2 );
						$value = isset($data[WC2_CUSTOM_ORDER][$cskey]) ?  $data[WC2_CUSTOM_ORDER][$cskey]: '';
						$line .= ',"'. wc2_entity_decode($value) .'"';
					}
				}
			}

			if( 1 == $chk_order['note'] ) $line .= ',"'. wc2_entity_decode($data['note']) .'"';

			//csod_other
			if( !empty($csod_other) ){
				foreach( $csod_other as $val ){
					if( 1 == $chk_order[$val] ){
						list( $pfx, $cskey ) = explode( '_', $val, 2 );
						$value = isset($data[WC2_CUSTOM_ORDER][$cskey]) ?  $data[WC2_CUSTOM_ORDER][$cskey]: '';
						$line .= ',"'. wc2_entity_decode($value) .'"';
					}
				}
			}

			$list .= apply_filters( 'wc2_filter_admin_order_list_dl_order', $line, $chk_order, $data );
			$list .= "\n";
		}

		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=wc2_order_list.csv");
		mb_http_output("pass");
		print(mb_convert_encoding($list, "SJIS-win", "UTF-8"));
		exit();
	}
}

function wc2_get_admin_order_cart_row( $order_id, $cart ) {
	$num = 0;
	ob_start();
	if( is_array($cart) and 0 < count($cart) ) :
		foreach( $cart as $idx => $row ) :
			if( isset($row['cart_id']) ) :
				$cart_id = $row['cart_id'];
				$post_id = $row['post_id'];
				$item_id = $row['item_id'];
				$item_name = $row['item_name'];
				$item_code = $row['item_code'];
				$sku_name = $row['sku_name'];
				$sku_code = $row['sku_code'];
				$sku_id = $row['sku_id'];
				$quantity = $row['quantity'];
				$price = $row['price'];
				$row_index = $row['row_index'];
				$meta_type = isset( $row['meta_type'] ) ? $row['meta_type'] : array();
				$meta_key = isset( $row['meta_key'] ) ? $row['meta_key'] : array();

				$pictid = wc2_get_mainpictid( $item_code );
				$cart_thumbnail = ( !empty($pictid ) ) ? wc2_the_item_image( 0, 60, 60, $post_id ) : wc2_no_image();
				$cart_item_name = wc2_get_cart_item_name( $item_name, $item_code, $sku_name, $sku_code );
				$cart_options = '';
				$cart_options = apply_filters( 'wc2_filter_order_cart_row_options', $cart_options, $idx, $row );
				$stock_status = wc2_get_item_stock_status( $item_id, $sku_id );
				$stock = wc2_get_item_stock( $item_id, $sku_id );
				$stock_signal_red = apply_filters( 'wc2_filter_stock_signal_red', 20 );
				$red = ( $stock_signal_red < $stock ) ? ' signal_red' : '';
				$num++;
?>
							<tr id="<?php echo $row_index; ?>">
								<td class="num"><?php echo $num; ?></td>
								<td class="thumbnail"><?php echo $cart_thumbnail; ?></td>
								<td class="name"><?php esc_html_e($cart_item_name); ?><?php echo $cart_options; ?></td>
								<td class="price"><input name="sku_price[<?php echo $cart_id; ?>]" id="sku_price-<?php echo $cart_id; ?>" class="text price right" type="text" value="<?php wc2_crform_e( $price, false, false, false ); ?>" /></td>
								<td class="quantity"><input name="quantity[<?php echo $cart_id; ?>]" id="quantity-<?php echo $cart_id; ?>" class="text quantity right" type="text" value="<?php esc_attr_e( $quantity ); ?>" /></td>
								<td id="subtotal-<?php echo $cart_id; ?>" class="subtotal">&nbsp;</td>
								<td class="stock<?php echo $red; ?>"><?php echo esc_html($stock_status); ?></td>
								<td class="action">
								<input type="hidden" name="cart_id[]" id="cart_id-<?php echo $cart_id; ?>" value="<?php esc_attr_e($cart_id); ?>" />
								<input type="hidden" name="cart_post_id[<?php echo $cart_id; ?>]" value="<?php esc_attr_e($post_id); ?>" />
								<input type="hidden" name="item_id[<?php echo $cart_id; ?>]" value="<?php esc_attr_e($item_id); ?>" />
								<input type="hidden" name="sku_id[<?php echo $cart_id; ?>]" value="<?php esc_attr_e($sku_id); ?>" />
								<input type="hidden" name="row_index[<?php echo $cart_id; ?>]" value="<?php esc_attr_e($row_index); ?>" />
							<?php foreach( (array)$meta_type as $type => $meta ) : ?>
								<?php foreach( (array)$meta as $key => $value ) : ?>
								<input type="hidden" name="meta_type[<?php echo $cart_id; ?>][<?php echo $type; ?>][<?php echo $key; ?>]" value="<?php esc_attr_e($value); ?>" />
								<?php endforeach; ?>
							<?php endforeach; ?>
							<?php foreach( (array)$meta_key as $key => $value ) : ?>
								<input type="hidden" name="meta_key[<?php echo $cart_id; ?>][<?php echo $key; ?>]" value="<?php esc_attr_e($value); ?>" />
							<?php endforeach; ?>
								<input type="button" id="cart-remove-<?php echo $cart_id; ?>" class="cart-remove button" value="<?php _e('削除','wc2'); ?>" />
								<?php do_action( 'wc2_action_admin_order_cart_row', $order_id, $cart, $idx ); ?>
								</td>
							</tr>
<?php 
			endif;
		endforeach;
	endif;
	$row = ob_get_contents();
	ob_end_clean();

	return apply_filters( 'wc2_filter_admin_order_cart_row', $row, $order_id, $cart );
}

function wc2_get_select_item( $cat_id ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$args = array( 'category' => $cat_id, 'numberposts' => 50, 'post_type' => 'item' );
	$args = apply_filters( 'wc2_filter_admin_order_select_item_args', $args );
	$posts = get_posts( $args );
	$option = '<option value="-1">'.__('商品を選択してください','wc2').'</option>';
	foreach( $posts as $post ) {
		$item_code = $wc2_item->get_item_value_by_post_id( $post->ID, 'item_code' );
		$item_name = $wc2_item->get_item_value_by_post_id( $post->ID, 'item_name' );
		$option .= '<option value="'.urlencode($item_code).'">'.$item_name.'('.$item_code.')'.'</option>';
	}
	return $option;
}

function wc2_get_add_item( $item_code ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_the_code( $item_code );
	$item_data = $wc2_item->get_item_data( 'item_code' );
	if( empty($item_data) ) die();

	$sku_format = $wc2_item->get_item_sku_format();
	$sku_column = $wc2_item->get_item_sku_column();
	$sku_meta_column = $wc2_item->get_item_sku_meta_column();
	$item_sku = $wc2_item->get_item_sku();
	$pictid = wc2_get_mainpictid( $item_code );
	$cart_thumbnail = ( !empty($pictid ) ) ? wc2_the_item_image( 0, 60, 60, $item_data[ITEM_POST_ID] ) : wc2_no_image();
	$item_name = $item_data[ITEM_NAME];

	ob_start();
?>
	<div id="additem-img"><?php echo $cart_thumbnail; ?></div>
	<div id="additem-name"><?php esc_html_e($item_name); ?></div>
<?php
	foreach( (array)$item_sku as $id => $sku ) : ?>
	<div id="additem-sku-table-<?php echo $id; ?>">
	<table>
		<thead><tr>
<?php	foreach( $sku_format as $sku ) :
			foreach( $sku as $key ) :
				$label = ( array_key_exists( $key, $sku_column ) and $sku_column[$key]['display'] == '' ) ? esc_attr( $wc2_item->get_the_item_label( $key ) ) : ''; ?>
			<th><?php echo $label; ?></th>
<?php		endforeach;
		endforeach; ?>
			<th>&nbsp;</th>
		</tr></thead>
		<?php wc2_admin_order_additem_sku_table_e( $id ); ?>
<?php do_action( 'wc2_action_admin_order_additem_sku', $sku, $id ); ?>
	</table>
	<table>
<?php
		if( 0 < count($sku_meta_column) ) {
			foreach( (array)$sku_meta_column as $key => $column ) {
				wc2_admin_order_additem_sku_field_e( $key, $column, (string)$id );
			}
		}
?>
<?php do_action( 'wc2_action_admin_order_additem_sku_meta', $sku, $id ); ?>
	</table>
	</div>
<?php
	endforeach;

	$html = ob_get_contents();
	ob_end_clean();
	return $html;
}

function wc2_admin_order_additem_sku_table_e( $id ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$sku_format = $wc2_item->get_item_sku_format();
	$sku_column = $wc2_item->get_item_sku_column();
	$item_id = $wc2_item->get_the_item_id();

	$html = '<tbody><tr>';
	foreach( $sku_format as $sku ) {
		foreach( $sku as $key ) {
			if( !empty($key) ) {
				$column = $sku_column[$key];
				$sku = '['.esc_attr($id).']';
				$sku_id = esc_attr($id);
				$data = $wc2_item->get_the_item_sku_value( $key, $id );
				$field = wc2_admin_order_additem_field( $key, $column, $sku, $sku_id, $data );
			} else {
				$field = '';
			}
			$html .= '<td>'.$field.'</td>';
		}
	}
	$html .= '
		<td>
			<label for="additem-quantity-'.$id.'"><span>'.__('Quantity','wc2').'</span><input type="text" id="additem-quantity-'.$id.'" class="small-text num right" value="1" /></label>
			<input type="button" class="button additem-sku" id="additem-sku-'.$id.'" value="'.__('リストに追加','wc2').'" />
			<input type="hidden" id="additem-item_id-'.$id.'" value="'.$item_id.'" />
			<input type="hidden" id="additem-sku_id-'.$id.'" value="'.$sku_id.'" />
		</td>
	</tr></tbody>';
	foreach( $sku_column as $key => $column ) {
		if( $column['display'] == 'hidden' ) {
			$sku_id = '_'.esc_attr($id);
			$data = $wc2_item->get_the_item_sku_value( $key, $id );
			$html .= wc2_admin_order_additem_field( $key, $column, '', $sku_id, $data );
		}
	}
	$html = apply_filters( 'wc2_filter_admin_order_additem_field', $html );
	echo $html;
}

function wc2_admin_order_additem_sku_field( $key, $column, $id = '' ) {
	$wc2_item = WC2_DB_Item::get_instance();

	if( '' != $id ) {
		$sku = '['.esc_attr($id).']';
		$sku_id = '_'.esc_attr($id);
	} else {
		$sku = '';
		$sku_id = '';
	}

	$html = '';
	if( $column['type'] == TYPE_PARENT ) {
		if( $column['display'] == '' ) {
			$label = $wc2_item->get_the_item_label( $key );
			$html .= '
			<tr><th>'.esc_attr($label).'</th><td>';
			$item_column = $wc2_item->get_item_column_all();
			$child = '';
			foreach( $item_column as $child_key => $child_column ) {
				if( array_key_exists( 'parent', $child_column ) and $child_column['parent'] == $key ) {
					$data = '';
					if( '' != $id ) {
						$data = $wc2_item->get_the_item_sku_value( $child_key, $id );
					} else {
						$data = $wc2_item->get_the_item_value( $child_key );
					}
					$child .= wc2_admin_order_additem_field( $child_key, $child_column, $sku, $sku_id, $data );
				}
			}
			$html .= $child.'
			</td></tr>';
		}

	} elseif( $column['parent'] == '' ) {
		$data = '';
		if( '' != $id ) {
			$data = ( $column['type'] != TYPE_PARENT ) ? $wc2_item->get_the_item_sku_value( $key, $id ) : '';
		} else {
			$data = ( $column['type'] != TYPE_PARENT ) ? $wc2_item->get_the_item_value( $key ) : '';
		}
		if( $column['display'] == 'hidden' ) {
		} elseif( $column['display'] == '' ) {
			$label = $wc2_item->get_the_item_label( $key );
			$html .= '
			<tr><th>'.esc_attr($label).'</th><td>';
				$html .= wc2_admin_order_additem_field( $key, $column, $sku, $sku_id, $data );
			$html .= '
			</td></tr>';
		}
	}

	return stripslashes( $html );
}

function wc2_admin_order_additem_sku_field_e( $key, $column ) {
	echo wc2_admin_order_additem_sku_field( $key, $column );
}

function wc2_admin_order_additem_field( $key, $column, $sku, $sku_id, $data ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$html = '';
	if( $column['display'] == 'hidden' ) {

	} elseif( $column['display'] == '' ) {
		switch( $column['type'] ) {
		case TYPE_TEXT:
		case TYPE_TEXT_Z:
		case TYPE_TEXT_ZK:
		case TYPE_TEXT_A:
		case TYPE_TEXT_I:
		case TYPE_TEXT_F:
		case TYPE_TEXTAREA:
			$html .= $column['label_pre'].esc_attr($data).$column['label_post'];
			break;

		case TYPE_TEXT_P:
			$html .= $column['label_pre'].esc_attr(floor($data)).$column['label_post'];
			break;

		case TYPE_SELECT:
		case TYPE_RADIO:
			$select = explode( ';', $column['value'] );
			foreach( $select as $option ) {
				list( $value, $name ) = explode( ':', $option );
				if( $data == $value ) {
					$html .= $column['label_pre'].esc_attr($name).$column['label_post'];
					break;
				}
			}
			break;

		case TYPE_SELECT_MULTIPLE:
		case TYPE_CHECK:
			$select = explode( ';', $column['value'] );
			foreach( $select as $option ) {
				list( $value, $name ) = explode( ':', $option );
				if( is_array($data) ) {
					if( array_key_exists( $value, $data ) ) {
						$html .= $column['label_pre'].esc_attr($name).$column['label_post'].', ';
					}
				} else {
					if( $data == $value ) {
						$html .= $column['label_pre'].esc_attr($name).$column['label_post'].', ';
					}
				}
			}
			$html = rtrim( $html, ', ' );
			break;
		}
	}
	return $html;
}

function wc2_get_order_item_option( $item_code ) {
	ob_start();
?>
<?php foreach( $optkeys as $optkey => $optvalue ) : ?>
<?php endforeach; ?>
<?php
	$html = ob_get_contents();
	ob_end_clean();
	return $html;
}

function wc2_get_member_neworder( $email ) {
	$member = wc2_get_member_data_by_email( urldecode($email) );
	if( !$member ) {
		$res = 'NG'.WC2_SPLIT;
	} else {
		$res = 'OK'.WC2_SPLIT.'member_id='.$member['ID'];
		$res .= WC2_SPLIT.'customer[name1]='.$member['name1'];
		$res .= WC2_SPLIT.'customer[name2]='.$member['name2'];
		$res .= WC2_SPLIT.'customer[name3]='.$member['name3'];
		$res .= WC2_SPLIT.'customer[name4]='.$member['name4'];
		$res .= WC2_SPLIT.'customer[zipcode]='.$member['zipcode'];
		$res .= WC2_SPLIT.'customer[pref]='.$member['pref'];
		$res .= WC2_SPLIT.'customer[address1]='.$member['address1'];
		$res .= WC2_SPLIT.'customer[address2]='.$member['address2'];
		$res .= WC2_SPLIT.'customer[tel]='.$member['tel'];
		$res .= WC2_SPLIT.'customer[fax]='.$member['fax'];
		$res .= WC2_SPLIT.'delivery[name1]='.$member['name1'];
		$res .= WC2_SPLIT.'delivery[name2]='.$member['name2'];
		$res .= WC2_SPLIT.'delivery[name3]='.$member['name3'];
		$res .= WC2_SPLIT.'delivery[name4]='.$member['name4'];
		$res .= WC2_SPLIT.'delivery[zipcode]='.$member['zipcode'];
		$res .= WC2_SPLIT.'delivery[pref]='.$member['pref'];
		$res .= WC2_SPLIT.'delivery[address1]='.$member['address1'];
		$res .= WC2_SPLIT.'delivery[address2]='.$member['address2'];
		$res .= WC2_SPLIT.'delivery[tel]='.$member['tel'];
		$res .= WC2_SPLIT.'delivery[fax]='.$member['fax'];
		if( array_key_exists( WC2_CUSTOM_MEMBER, $member ) ) {
			foreach( $member[WC2_CUSTOM_MEMBER] as $key => $value ) {
				$res .= WC2_SPLIT.'custom_customer['.$key.']='.$value;
				$res .= WC2_SPLIT.'custom_delivery['.$key.']='.$value;
			}
		}
	}
	return $res;
}

function wc2_order_recalculation( $order_id, $mem_id, $item_ids, $skus, $prices, $quants, $use_point, $shipping_charge, $cod_fee ) {

	$res = 'OK';
/*	if( !empty($order_id) ) {
		$data = $usces->get_order_data( $order_id, 'direct' );
		$condition = unserialize( $data['order_condition'] );
	} else {
		$condition = wc2_get_condition();
	}

	$item_id = explode(WC2_SPLIT, $item_ids);
	//$sku = explode(WC2_SPLIT, $skus);
	$price = explode(WC2_SPLIT, $prices);
	$quant = explode(WC2_SPLIT, $quants);
	$cart = array();
	for( $i = 0; $i < count($item_id); $i++ ) {
		if( $item_id[$i] ) 
			$cart[] = array( "item_id"=>$item_id[$i], "price"=>$price[$i], "quantity"=>$quant[$i] );
	}

	$total_items_price = 0;
	foreach( $cart as $cart_row ) {
		$total_items_price += $cart_row['price'] * $cart_row['quantity'];
	}
	$meminfo = $usces->get_member_info( $mem_id );

	$discount = 0;
	if( $condition['display_mode'] == 'Promotionsale' ) {
		if( $condition['campaign_privilege'] == 'discount' ) {
			if ( 0 === (int)$condition['campaign_category'] ) {
				$discount = $total_items_price * $condition['privilege_discount'] / 100;
			} else {
				foreach( $cart as $cart_row ) {
					if( in_category( (int)$condition['campaign_category'], $cart_row['item_id']) ) {
						$discount += $cart_row['price'] * $cart_row['quantity'] * $condition['privilege_discount'] / 100;
					}
				}
			}
		}
	}
	if( 0 < $discount ) $discount = ceil($discount * -1);

	$point = 0;
	if( 'activate' == $usces->options['membersystem_state'] && 'activate' == $usces->options['membersystem_point'] && !empty($meminfo['ID']) ) {
		if( $condition['display_mode'] == 'Promotionsale' ) {
			if( $condition['campaign_privilege'] == 'discount' ) {
				foreach( $cart as $cart_row ) {
					$cats = $usces->get_post_term_ids( $cart_row['item_id'], 'category' );
					if( !in_array( $condition['campaign_category'], $cats ) ) {
						$rate = get_post_meta( $cart_row['item_id'], '_itemPointrate', true );
						$price = $cart_row['price'] * $cart_row['quantity'];
						$point += $price * $rate / 100;
					}
				}
			} elseif( $condition['campaign_privilege'] == 'point' ) {
				foreach( $cart as $cart_row ) {
					$rate = get_post_meta( $cart_row['item_id'], '_itemPointrate', true );
					$price = $cart_row['price'] * $cart_row['quantity'];
					$cats = $usces->get_post_term_ids( $cart_row['item_id'], 'category' );
					if( in_array( $condition['campaign_category'], $cats ) ) {
						$point += $price * $rate / 100 * $condition['privilege_point'];
					} else {
						$point += $price * $rate / 100;
					}
				}
			}
		} else {
			foreach( $cart as $cart_row ) {
				$rate = get_post_meta( $cart_row['item_id'], '_itemPointrate', true );
				$price = $cart_row['price'] * $cart_row['quantity'];
				$point += $price * $rate / 100;
			}
		}
	}

	if( 0 < $point ) $point = ceil($point);
	if( 0 < $use_point ) {
		$point = ceil( $point - ( $point * $use_point / $total_items_price ) );
		if( 0 > $point )
			$point = 0;
	}
	$discount = apply_filters('usces_filter_order_discount_recalculation', $discount, $cart);
	$point = apply_filters( 'usces_filter_set_point_recalculation', $point, $condition, $cart, $meminfo, $use_point );
	$total_price = $total_items_price - $use_point + $discount + $shipping_charge + $cod_fee;
	$total_price = apply_filters('usces_filter_set_cart_fees_total_price', $total_price, $total_items_price, $use_point, $discount, $shipping_charge, $cod_fee);
	$materials = compact( 'total_items_price', 'shipping_charge', 'discount', 'cod_fee', 'use_point', 'discount' );
	$tax = $usces->getTax( $total_price, $materials );
	$total_full_price = $total_price + $tax;
	$total_full_price = apply_filters('usces_filter_set_cart_fees_total_full_price', $total_full_price, $total_items_price, $use_point, $discount, $shipping_charge, $cod_fee);
	return $res.WC2_SPLIT.$discount.WC2_SPLIT.usces_crform( $tax, false, false, 'return', false ).WC2_SPLIT.$point.WC2_SPLIT.usces_crform( $total_full_price, false, false, 'return', false );
*/
	return $res;
}
