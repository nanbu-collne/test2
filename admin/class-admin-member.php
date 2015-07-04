<?php
/**
 * Welcart2.
 *
 * @package   WC2 Member
 * @author    Collne Inc. <author@welcart.com>
 * @license   GPL-2.0+
 * @link      http://www.welcart2.com
 * @copyright 2014 Collne Inc.
 */

if( !class_exists('Member_List_Table') )
	require_once( WC2_PLUGIN_DIR . '/admin/includes/class-member-list-table.php' );

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-plugin-name.php`
 *
 * @TODO: Rename this class to a proper name for your plugin.
 *
 * @package WC2_Admin
 * @author  Collne Inc. <author@welcart.com>
 */
class WC2_Member extends WC2_Admin_Page {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	//screen_optionに設定
	public static $per_page_slug = 'member_list_page';

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
		$this->title = $admin_screen_label['member'];
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
				'title' => '会員一覧',
				'id' => 'member-list',
				'callback' => array( $this, 'get_help_member_list' )
			),
			array(
				'title' => '会員データ編集',
				'id' => 'member_edit',
				'callback' => array( $this, 'get_help_member_edit' )
			),
		);

		foreach( $tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}
	}

	function get_help_member_list() {
		echo "<dl>
				<dt>会員一覧の詳細な説明</dt>
					<dd></dd>
				<dt></dt>
					<dd></dd>
			</dl>";
	}

	function get_help_member_edit() {
		echo "<dl>
				<dt>会員データ編集の詳細な説明</dt>
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
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'_admin_member_styles', plugins_url( 'assets/css/admin_member.css', __FILE__ ), array(), Welcart2::VERSION );
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
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			//wp_enqueue_script( $this->plugin_slug . '_admin_member_script', plugins_url( 'assets/js/admin-member.js', __FILE__ ), array( 'jquery' ), Welcart2::VERSION );
			wp_enqueue_script( 'ajaxzip3', 'http://ajaxzip3.googlecode.com/svn/trunk/ajaxzip3/ajaxzip3.js' );
		}
	}

	/***********************************
	 * 表示オプションの表示制御
	 * @since    1.0.0
	 *
	 * NOTE:  $show_screen = 1は表示オプションを表示、0は非表示
	 ***********************************/
	public function admin_show_screen( $show_screen, $screen ){
		if( !isset( $screen->id ) || false === strpos( $screen->id,  $this->plugin_slug .'_member' ) )
			return $show_screen;

		if( isset($_REQUEST['action']) && '-1' != $_REQUEST['action'] ){
			$member_action = $_REQUEST['action'];
		}elseif( isset($_REQUEST['action2']) && '-1' != $_REQUEST['action2'] ){
			$member_action = $_REQUEST['action2'];
		}else{
			$member_action = 'list';
		}

		switch( $member_action ){
			case 'new' :
			case 'edit':
				$show_screen = 0;
				break;
			case '-1':
			case 'list':
			case 'delete':
			case 'delete_batch':
			default :
				$show_screen = 1;
				break;
		}

		return $show_screen;
	}

	/***********************************
	 * リストの表示件数取得
	 * @since    1.0.0
	 *
	 * NOTE:  screen_options_show_screen にフックして、保存されたリストの表示件数を適用
	 ***********************************/
	public function admin_set_screen_options( $result, $option, $value ){
		$member_list_screens = array( self::$per_page_slug );

		if( in_array( $option, $member_list_screens ) )
			$result = $value;

		return $result;
	}

	/***********************************
	 * Add a sub menu page.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function add_admin_menu() {
		$this->plugin_screen_hook_suffix = add_submenu_page( $this->menu_slug['management'], '会員一覧', '会員一覧', 'edit_pages', $this->plugin_slug .'_member', array( $this, 'admin_member_page' ) );
		add_action( 'load-' . $this->plugin_screen_hook_suffix, array( $this, 'load_member_action' ) );
	}

	/***********************************
	 * Runs when an administration menu page is loaded.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function load_member_action() {
		$current_screen = get_current_screen();

		//searchout
		if( array_key_exists( 'search_out', $_REQUEST ) && array_key_exists( 'search_column', $_REQUEST) && array_key_exists( 'search_word', $_REQUEST) ){
			$referer = remove_query_arg( array( 'search_in', 'search_column', 'search_word' ), wp_unslash(wp_get_referer()) );
			wp_redirect( $referer );
			die();
		}

		//リストのカスタム・カラム（Member_List_Table::define_columns）をフック
		add_filter( 'manage_' . $current_screen->id . '_columns', array( 'Member_List_Table', 'define_columns' ) );

		//リストの表示件数設定（表示オプション内の件数フィールド）
		add_screen_option( 'per_page', array( 'label' => __( '件', 'wc2' ), 'default' => 10, 'option' => self::$per_page_slug ) );
	}

	/***********************************
	 * The function to be called to output the content for this page.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function admin_member_page() {
		if( !isset( $this->plugin_screen_hook_suffix ) )
			return;

		$screen = get_current_screen();
		if( $this->plugin_screen_hook_suffix != $screen->id )
			return;

		if( isset($_REQUEST['action']) && '-1' != $_REQUEST['action'] ) {
			$this->mode = $_REQUEST['action'];
		} elseif( isset($_REQUEST['action2']) && '-1' != $_REQUEST['action2'] ) {
			$this->mode = $_REQUEST['action2'];
		} else {
			$this->mode = 'list';
		}
		$member_id = ( isset( $_REQUEST['target'] ) ) ? trim($_REQUEST['target']) : '';
		$wc2_db_member = WC2_DB_Member::get_instance();

		switch( $this->mode ) {
			case 'new':
				$this->page = 'member-post';
				break;

			case 'edit':
				if( array_key_exists( 'addButton', $_POST ) ){
					check_admin_referer( 'wc2_member_post', 'wc2_nonce' );
					$this->error_message = wc2_member_check('member', $member_id);
					if( array() == $this->error_message ){
						$res = wc2_new_member_data();
						if ( 1 === $res  ) {
							$this->set_action_status('success', __('登録が完了しました。','wc2'));
						} else {
							$this->set_action_status('error', __('登録に失敗しました。','wc2'));
						}
					}else{
						$this->set_action_status('error', __('登録に失敗しました。','wc2'));
					}
				}
				//upmem_check
				if( array_key_exists( 'upButton', $_POST ) ){
					check_admin_referer( 'wc2_member_post', 'wc2_nonce' );
					$this->error_message = wc2_member_check('member', $_REQUEST['target']);
					if( array() == $this->error_message ){
						$res = wc2_edit_member_data($member_id);
						if ( 1 === $res ) {
							$this->set_action_status('success', __('登録が完了しました。','wc2'));
						}elseif( 0 !== $res ){
							$this->set_action_status('error', __('登録に失敗しました。','wc2'));
						}
					}else{
						$this->set_action_status('error', __('登録に失敗しました。','wc2'));
					}
				}
				$this->page = 'member-post';
				break;

			case 'delete':
				check_admin_referer( 'wc2_member_list', 'wc2_nonce' );
				$res = $wc2_db_member->delete_member_data( $member_id );
				if ( 1 === $res ) {
					$this->set_action_status('success', __('削除しました。','wc2'));
				} else {
					$this->set_action_status('error', __('削除に失敗しました。','wc2'));
				}
				$this->page = 'member-list';
				break;

			case 'delete_batch':
				check_admin_referer( 'wc2_member_list', 'wc2_nonce' );
				if( isset( $_REQUEST['member'] ) && !empty( $_REQUEST['member'] ) ){
					$mem_ids = $_REQUEST['member'];
					$res = $this->delete_batch_member_data( $mem_ids );
					if ( 1 === $res ) {
						$this->set_action_status('success', __('削除しました。','wc2'));
					} else {
						$this->set_action_status('error', __('削除に失敗しました。','wc2'));
					}
				}
				$this->page = 'member-list';
				break;

			case 'dlmemberlist':
				check_admin_referer( 'wc2_dl_memberlist', 'wc2_nonce' );
				$this->wc2_download_member_list();
				$this->page = '';
				break;

			case 'list';
			default:
				$this->page = 'member-list';
				break;
		}

		if( !empty($this->page) ) {
			$rank_type = wc2_get_option('rank_type');

			if( $this->page == 'member-list' ) {
				$Member_List_Table = new Member_List_Table();
				$Member_List_Table->prepare_items();

				$status = $this->action_status;
				$message = $this->action_message;
				$this->action_status = 'none';
				$this->action_message = '';

				$search_column_key = ( isset($_REQUEST['search_column']) ) ? $_REQUEST['search_column'] : '';
				$search_word = '';
				$search_word_key = '';
				switch( $search_column_key ) {
				case 'none':
					break;
				case 'mem_rank':
					$search_word_key = ( isset($_REQUEST['search_word']['mem_rank']) ) ? $_REQUEST['search_word']['mem_rank'] : '';
					if( array_key_exists( $search_word_key, $rank_type ) ) $search_word = $rank_type[$search_word_key];
					break;
				default:
					if( isset($_REQUEST['search_word']['keyword']) ) $search_word = $_REQUEST['search_word']['keyword'];
				}

				$search_columns = $Member_List_Table->define_columns();
				unset( $search_columns['cb'] );
				$search_columns = apply_filters( 'wc2_filter_admin_member_list_search_columns', $search_columns );

				$wc2_opt_member = wc2_get_option('wc2_opt_member');
				$chk_mem = $wc2_opt_member['chk_mem'];

			} elseif( $this->page == 'member-post' ) {
				$wc2_options = wc2_get_option();

				$status = $this->action_status;
				$message = $this->action_message;
				$this->action_status = 'none';
				$this->action_message = '';

				$member_action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
				if( array() != $this->error_message && array_key_exists('addButton', $_POST) ){
					$member_action = 'new';
				}

				$data = array();
				//$referer = wp_get_referer();
				//新規会員登録ページ
				if( 'new' == $member_action ){
					$title = '新規会員登録';
					$page = 'wc2_member';
					$oa = 'edit';
					$data['ID'] = '';
					$data['account'] = ( isset( $_POST['member']['account'] ) ) ? $_POST['member']['account'] : '';
					$data['email'] = ( isset( $_POST['member']['email'] ) ) ? $_POST['member']['email'] : '';
					$data['passwd'] = ( isset( $_POST['member']['passwd'] ) ) ? $_POST['member']['passwd'] : '';
					$data['rank'] = ( isset( $_POST['member']['rank'] ) ) ? $_POST['member']['rank'] : '';
					$data['point'] = ( isset( $_POST['member']['point'] ) ) ? $_POST['member']['point'] : '';
					$data['name1'] = ( isset( $_POST['member']['name1'] ) ) ? $_POST['member']['name1'] : '';
					$data['name2'] = ( isset( $_POST['member']['name2'] ) ) ? $_POST['member']['name2'] : '';
					$data['name3'] = ( isset( $_POST['member']['name3'] ) ) ? $_POST['member']['name3'] : '';
					$data['name4'] = ( isset( $_POST['member']['name4'] ) ) ? $_POST['member']['name4'] : '';
					$data['country'] = ( isset( $_POST['member']['country'] ) ) ? $_POST['member']['country'] : '';
					$data['zipcode'] = ( isset( $_POST['member']['zipcode'] ) ) ? $_POST['member']['zipcode'] : '';
					$data['pref'] = ( isset( $_POST['member']['pref'] ) ) ? $_POST['member']['pref'] : '';
					$data['address1'] = ( isset( $_POST['member']['address1'] ) ) ? $_POST['member']['address1'] : '';
					$data['address2'] = ( isset( $_POST['member']['address2'] ) ) ? $_POST['member']['address2'] : '';
					$data['tel'] = ( isset( $_POST['member']['tel'] ) ) ? $_POST['member']['tel'] : '';
					$data['fax'] = ( isset( $_POST['member']['fax'] ) ) ? $_POST['member']['fax'] : '';
					$data['registered'] = '--------------';

					//csmb
					$csmb_keys = wc2_get_custom_field_keys(WC2_CSMB);
					if( !empty($csmb_keys) && is_array($csmb_keys) ){
						foreach($csmb_keys as $key){
							list( $pfx, $csmb_key ) = explode('_', $key, 2);
							$csmb_val = ( isset( $_POST[WC2_CUSTOM_MEMBER][$csmb_key] ) ) ? $_POST[WC2_CUSTOM_MEMBER][$csmb_key]: '';
							$data[WC2_CUSTOM_MEMBER][$csmb_key] = $csmb_val;
						}
					}

				//履歴
				//$wc2_member_history =array();

				//会員情報編集ページ
				} elseif( 'edit' == $member_action ){
					$title = '会員情報編集';
					$page = 'wc2_member';
					$oa = 'edit';
					if( isset( $_REQUEST['target']) ){
						$member_id = $_REQUEST['target'];
					}else{
						$member_id = $wc2_db_member->get_member_id();
					}
					
					$data = $wc2_db_member->get_member_data($member_id);
					//履歴
				//	$wc2_member_history = wc2_get_member_history();
				} else {
					die('不正なパラメータです');
				}
				//$material = compact('data', 'meta_data');
			}
			require_once( WC2_PLUGIN_DIR.'/admin/views/'.$this->page.'.php' );
		}
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

		ob_start();
?>
<script type="text/javascript">
jQuery(function($) {
<?php if( $this->page == 'member-list' ) : ?>

	$("#dlMemberListDialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 400,
		width: 700,
		resizable: true,
		modal: true,
		buttons: {
			'閉じる': function() {
				$(this).dialog('close');
			}
		},
		close: function() {
		}
	});

	$('#dl_mem').click(function() {
		//var args = window.location.search;
		var search_column = $("#search-column option:selected").val();
		var search_word = "";
		switch( search_column ) {
		case "mem_rank":
			search_word = $("#search-word-mem_rank option:selected").val();
			break;
		default:
			search_word = $("#search-word-keyword").val();
		}
		var args = "&search[column]="+search_column
			+"&search[word]="+search_word;
		$('*[class=check_member]').each(function(i) {
			if($(this).attr('checked')) {
				args += '&check['+$(this).val()+']=on';
			}
		});
		location.href = "<?php echo WC2_ADMIN_URL; ?>"+args+"&action=dlmemberlist&noheader=true&ftype=csv&wc2_nonce=<?php echo wp_create_nonce( 'wc2_dl_memberlist' ); ?>";
	});

	$('#dl_memberlist').click(function() {
		$('#dlMemberListDialog').dialog('open');
	});

	//batch
	$("#doaction").click( function(){
		if( '-1' == $('select[name="action"]').val() ){
			alert("操作を選択してください。");
			return false;
		}
		if( 'delete_batch' == $('select[name="action"]').val() ){
			if( $('input[name="member[]"]:checked').length > 0 ){
				if(confirm('チェックした会員を削除します。よろしいですか？')){
					return true;
				}else{
					return false;
				}
			}else{
				alert('削除する会員にチェックを入れてください。');
				return false;
			}
		}
	});

	$("#doaction2").click( function(){
		if( '-1' == $('select[name="action2"]').val() ){
			alert("操作を選択してください。");
			return false;
		}
		if( 'delete_batch' == $('select[name="action2"]').val() ){
			if( $('input[name="member[]"]:checked').length > 0 ){
				if(confirm('チェックした会員を削除します。よろしいですか？')){
					return true;
				}else{
					return false;
				}
			}else{
				alert('削除する会員にチェックを入れてください。');
				return false;
			}
		}
	});

	//delete
	$(".delete_member").click( function(){
		var delete_id = $(this).attr('id').replace('delete-', '');
		if(confirm('会員ID ' + delete_id + ' を削除します。よろしいですか？')){
			return true;
		}else{
			return false;
		}
	});

	//search
	$("#search-in").click( function(){
		var search_column = $("#search-column option:selected").val();
		switch( search_column ) {
		case "none":
			alert("検索項目を選択してください。");
			return false;
			break;
		case "mem_rank":
			break;
		default:
			if( '' == $("#search-word-keyword").val() ) {
				alert("検索するキーワードを入力してください。");
				return false;
			}
		}
	});

	$("#search-column").change( function() {
		var search_column = $("#search-column option:selected").val();
		memberList.selectSearchColumn(search_column);
	});

	memberList = {
		selectSearchColumn : function( search_column ) {
			switch( search_column ) {
			case "mem_rank":
				$("#search-label").css("display", "none");
				$("#search-word-keyword").css("display", "none");
				$("#search-word-mem_rank").css("display", "inline-block");
				break;
			default:
				$("#search-label").css("display", "inline-block");
				$("#search-word-keyword").css("display", "inline-block");
				$("#search-word-mem_rank").css("display", "none");
			}
		}
	};

<?php elseif( $this->page == 'member-post' ) : ?>

	//tab
	$("#mem-ui-tab").tabs();
	//tabの表示
	$(".wc2tabs").css("display", "block");

	//form_check
	$("#mem_addButton, #mem_upButton").click( function(){
		var error = 0;
		if( '' == $('input[name="member[account]"]').val() ){
			error++;
			$('input[name="member[account]"]').css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( '' == $('input[name="member[passwd]"]').val() ){
			error++;
			$('input[name="member[passwd]"]').css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( '' == $('input[name="member[email]"]').val() ){
			error++;
			$('input[name="member[email]"]').css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( '' == $('input[name="member[name1]"]').val() ){
			error++;
			$('input[name="member[name1]"]').css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( '' == $('input[name="member[name2]"]').val() ){
			error++;
			$('input[name="member[name2]"]').css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( '' == $('input[name="member[name3]"]').val() ){
			error++;
			$('input[name="member[name3]"]').css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( '' == $('input[name="member[name4]"]').val() ){
			error++;
			$('input[name="member[name4]"]').css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( '' == $('input[name="member[zipcode]"]').val() ){
			error++;
			$('input[name="member[zipcode]"]').css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( '#NONE#' == $('select[name="member[pref]"]').val() ){
			error++;
			$('select[name="member[pref]"]').css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( '' == $('input[name="member[address1]"]').val() ){
			error++;
			$('input[name="member[address1]"]').css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( '' == $('input[name="member[tel]"]').val() ){
			error++;
			$('input[name="member[tel]"]').css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}

		if( 0 < error ) {
			$("#aniboxStatus").attr("class", "error");
			$("#info_message").html("データに不備があります。");
//			$("#anibox").animate({ backgroundColor: "#FFE6E6" }, 2000);
			return false;
		} else {
			return true;
		}
	});
<?php endif; ?>
});
</script>
<?php
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
	}

	/*******************************
	* 会員データCSVダウンロード
	* @since 1.0.0

	* NOTE
	********************************/
	public function wc2_download_member_list(){
		global $wpdb;

		$wc2_options = wc2_get_option();
		$locale_options = wc2_get_option('locale_options');
		$applyform = WC2_Funcs::get_apply_addressform( $wc2_options['system']['addressform']);
		$target_market = $wc2_options['system']['target_market'];

		$MLT = new Member_List_Table();
		//1ぺーじあたりのテーブルの行数
        $per_page = $MLT->get_items_per_page( self::$per_page_slug );
		//ソート
		$args = $MLT->sort_culum_order_by($per_page);
		//データ
		$rows = $MLT->get_list_data($args);

		$ext = $_REQUEST['ftype'];
		if($ext == 'csv') {//CSV
			$table_h = "";
			$table_f = "";
			$tr_h = "";
			$tr_f = "";
			$th_h1 = '"';
			$th_h = ',"';
			$th_f = '"';
			$td_h1 = '"';
			$td_h = ',"';
			$td_f = '"';
			$nb = " ";
			$lf = "\n";
		} else {
			exit();
		}
		$wc2_opt_member = wc2_get_option('wc2_opt_member');
		if(!is_array($wc2_opt_member)){
			$wc2_opt_member = array();
		}
		$wc2_opt_member['ftype_mem'] = $ext;

		//---------------------- checkbox Check -----------------------//
		$chk_mem = array();
		$chk_mem['ID'] = 1;
		//$chk_mem['code'] = 1;
		$chk_mem['account'] = 1;
		//head
		$hd_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'head' );
		if( !empty($hd_keys) ){
			foreach( $hd_keys as $csmb_key ){
				$chk_mem[$csmb_key] = ( isset($_REQUEST['check'][$csmb_key]) ) ? 1: 0;
			}
		}
		$chk_mem['email'] = ( isset( $_REQUEST['check']['email'] ) ) ? 1: 0;
		//beforename
		$bn_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'beforename' );
		if( !empty($bn_keys) ){
			foreach( $bn_keys as $csmb_key ){
				$chk_mem[$csmb_key] = ( isset($_REQUEST['check'][$csmb_key]) ) ? 1: 0;
			}
		}
		$chk_mem['name'] = 1;
		$chk_mem['kana'] = ( isset( $_REQUEST['check']['kana'] ) ) ? 1: 0;
		//aftername
		$an_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'aftername' );
		if( !empty($an_keys) ){
			foreach( $an_keys as $csmb_key ){
				$chk_mem[$csmb_key] = ( isset($_REQUEST['check'][$csmb_key]) ) ? 1: 0;
			}
		}
		$chk_mem['country'] = ( isset( $_REQUEST['check']['country'] ) ) ? 1: 0;
		$chk_mem['zipcode'] = ( isset( $_REQUEST['check']['zipcode'] ) ) ? 1: 0;
		$chk_mem['pref'] = 1;
		$chk_mem['address1'] = 1;
		$chk_mem['address2'] = 1;
		$chk_mem['tel'] = ( isset( $_REQUEST['check']['tel'] ) ) ? 1: 0;
		$chk_mem['fax'] = ( isset( $_REQUEST['check']['fax'] ) ) ? 1: 0;
		//bottom
		$btm_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'bottom' );
		if( !empty($btm_keys) ){
			foreach( $btm_keys as $csmb_key ){
				$chk_mem[$csmb_key] = ( isset($_REQUEST['check'][$csmb_key]) ) ? 1: 0;
			}
		}
		$chk_mem['registered'] = ( isset( $_REQUEST['check']['registered'] ) ) ? 1: 0;
		$chk_mem['point'] = ( isset( $_REQUEST['check']['point'] ) ) ? 1: 0;
		$chk_mem['rank'] = ( isset( $_REQUEST['check']['rank'] ) ) ? 1: 0;
		//other
		$oth_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'other' );
		if( !empty($oth_keys) ){
			foreach( $oth_keys as $csmb_key ){
				$chk_mem[$csmb_key] = ( isset($_REQUEST['check'][$csmb_key]) ) ? 1: 0;
			}
		}
		$wc2_opt_member['chk_mem'] = apply_filters( 'wc2_filter_chk_mem', $chk_mem );
//		update_option('wc2_opt_member', $wc2_opt_member);
		wc2_update_option( 'wc2_opt_member', $wc2_opt_member );
		
		//---------------------- TITLE -----------------------//
		$line = $table_h;
		$line .= $tr_h;
		$line .= $th_h1 . __('Membership ID', 'wc2') . $th_f;
		$line .= $th_h . __('Login account', 'wc2') . $th_f;
		//csmb head
		$hd_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'head' );
		if( !empty($hd_keys) ){
			foreach( $hd_keys as $csmb_key ){
				if( isset($_REQUEST['check'][$csmb_key]) )
					$line .= $th_h.wc2_entity_decode( $wc2_options[$csmb_key]['name'], $ext ).$th_f;
			}
		}
		if( isset( $_REQUEST['check']['email'] ) ){
			$line .= $th_h . __('E-mail', 'wc2') . $th_f;
		}
		//csmb beforename
		$bn_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'beforename' );
		if( !empty($bn_keys) ){
			foreach( $bn_keys as $csmb_key ){
				if( isset($_REQUEST['check'][$csmb_key]) )
					$line .= $th_h.wc2_entity_decode( $wc2_options[$csmb_key]['name'], $ext ).$th_f;
			}
		}
		$line .= $th_h . __('Name', 'wc2') . $th_f;
		if( 'JP' == $applyform ){
			if(isset($_REQUEST['check']['kana'])) 
				$line .= $th_h . __('Kana', 'wc2') . $th_f;
		}
		//csmb aftername
		$an_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'aftername' );
		if( !empty($an_keys) ){
			foreach( $an_keys as $csmb_key ){
				if( isset($_REQUEST['check'][$csmb_key]) )
					$line .= $th_h.wc2_entity_decode( $wc2_options[$csmb_key]['name'], $ext ).$th_f;
			}
		}
		switch($applyform){
		case 'JP':
			if(isset($_REQUEST['check']['country'])) $line .= $th_h.__('Country', 'wc2').$th_f;
			if( isset( $_REQUEST['check']['zipcode'] ) ) $line .= $th_h.__('Postal Code', 'wc2').$th_f;
			$line .= $th_h.__('Prefecture', 'wc2').$th_f;
			$line .= $th_h.__('City', 'wc2').$th_f;
			$line .= $th_h.__('Building name, floor, room number', 'wc2').$th_f;
			if(isset($_REQUEST['check']['tel'])) $line .= $th_h.__('Phone number', 'wc2').$th_f;
			if(isset($_REQUEST['check']['fax'])) $line .= $th_h.__('FAX number', 'wc2').$th_f;
			break;
		case 'US':
		default:
			$line .= $th_h.__('Building name, floor, room number', 'wc2').$th_f;
			$line .= $th_h.__('City', 'wc2').$th_f;
			$line .= $th_h.__('Prefecture', 'wc2').$th_f;
			$line .= $th_h.__('Postal Code', 'wc2').$th_f;
			if(isset($_REQUEST['check']['country'])) $line .= $th_h.__('Country', 'wc2').$th_f;
			if(isset($_REQUEST['check']['tel'])) $line .= $th_h.__('Phone number', 'wc2').$th_f;
			if(isset($_REQUEST['check']['fax'])) $line .= $th_h.__('FAX number', 'wc2').$th_f;
			break;
		}
		//csmb bottom
		$btm_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'bottom' );
		if( !empty($btm_keys) ){
			foreach( $btm_keys as $csmb_key ){
				if( isset($_REQUEST['check'][$csmb_key]) )
					$line .= $th_h.wc2_entity_decode( $wc2_options[$csmb_key]['name'], $ext ).$th_f;
			}
		}
		if(isset($_REQUEST['check']['rank'])) $line .= $th_h.__('ランク', 'wc2').$th_f;
		if(isset($_REQUEST['check']['point'])) $line .= $th_h.__('保有ポイント', 'wc2').$th_f;
		if(isset($_REQUEST['check']['registered'])) $line .= $th_h.__('Started date', 'wc2').$th_f;
		
		//csmb other
		$oth_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'other' );
		if( !empty($oth_keys) ){
			foreach( $oth_keys as $csmb_key ){
				if( isset($_REQUEST['check'][$csmb_key]) )
					$line .= $th_h.wc2_entity_decode( $wc2_options[$csmb_key]['name'], $ext ).$th_f;
			}
		}

		$line .= apply_filters( 'wc2_filter_chk_mem_label', NULL, $wc2_opt_member, $rows );
		$line .= $tr_f.$lf;

		//---------------------- DATA -----------------------//
		foreach( (array)$rows as $array ){
			$member_id = $array['ID'];
			$data = wc2_get_member_data($member_id);
			//$meta_data = wc2_get_member_data($member_id);
			$line .= $tr_h;
			$line .= $td_h1.$member_id.$td_f;
			$line .= $td_h.wc2_entity_decode($data['account'], $ext).$td_f;
			//csmb head
			$hd_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'head' );
			if( !empty($hd_keys) ){
				foreach( $hd_keys as $csmb_key ){
					if( isset($_REQUEST['check'][$csmb_key]) ){
						$meta_value = ( isset( $data[WC2_CUSTOM_MEMBER][$csmb_key] ) ) ? $data[WC2_CUSTOM_MEMBER][$csmb_key]: ''; 
						$line .= $td_h.wc2_entity_decode($meta_value, $ext).$td_f;
					}
				}
			}
			if(isset($_REQUEST['check']['email']))
				$line .= $td_h.wc2_entity_decode($data['email'], $ext).$td_f;

			//csmb beforename
			$bn_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'beforename' );
			if( !empty($bn_keys) ){
				foreach( $bn_keys as $csmb_key ){
					if( isset($_REQUEST['check'][$csmb_key]) ){
						$meta_value = ( isset( $data[WC2_CUSTOM_MEMBER][$csmb_key] ) ) ? $data[WC2_CUSTOM_MEMBER][$csmb_key]: ''; 
						$line .= $td_h.wc2_entity_decode($meta_value, $ext).$td_f;
					}
				}
			}
			switch( $applyform){
				case 'JP':
					$line .= $td_h.wc2_entity_decode($data['name1'].' '.$data['name2'], $ext).$td_f;
					if(isset($_REQUEST['check']['kana'])) $line .= $td_h.wc2_entity_decode($data['name3'].' '.$data['name4'], $ext).$td_f;
					break;
				default:
					$line .= $td_h.wc2_entity_decode($data['name2'].' '.$data['name1'], $ext).$td_f;
					break;
			}
			//csmb aftername
			$an_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'aftername' );
			if( !empty($an_keys) ){
				foreach( $an_keys as $csmb_key ){
					if( isset($_REQUEST['check'][$csmb_key]) ){
						$meta_value = ( isset( $data[WC2_CUSTOM_MEMBER][$csmb_key] ) ) ? $data[WC2_CUSTOM_MEMBER][$csmb_key]: '';
						$line .= $td_h.wc2_entity_decode($meta_value, $ext).$td_f;
					}
				}
			}

			$address_info = '';
			switch($applyform) {
			case 'JP':
				if(isset($_REQUEST['check']['country'])) {
					$country_code = $data['country'];
					$member_country = ( !empty($country_code) ) ? $locale_options['country'][$country_code] : '';
					$address_info .= $td_h.$member_country.$td_f;
				}
				if(isset($_REQUEST['check']['zipcode'])) $address_info .= $td_h.wc2_entity_decode($data['zipcode'], $ext).$td_f;
				$address_info .= $td_h.wc2_entity_decode($data['pref'], $ext).$td_f;
				$address_info .= $td_h.wc2_entity_decode($data['address1'], $ext).$td_f;
				$address_info .= $td_h.wc2_entity_decode($data['address2'], $ext).$td_f;
				if(isset($_REQUEST['check']['tel'])) $address_info .= $td_h.wc2_entity_decode($data['tel'], $ext).$td_f;
				if(isset($_REQUEST['check']['fax'])) $address_info .= $td_h.wc2_entity_decode($data['fax'], $ext).$td_f;
				break;
			case 'US':
			default:
				$address_info .= $td_h.wc2_entity_decode($data['address2'], $ext).$td_f;
				$address_info .= $td_h.wc2_entity_decode($data['address1'], $ext).$td_f;
				$address_info .= $td_h.wc2_entity_decode($data['pref'], $ext).$td_f;
				if(isset($_REQUEST['check']['zipcode'])) $address_info .= $td_h.wc2_entity_decode($data['zipcode'], $ext).$td_f;
				if(isset($_REQUEST['check']['country'])) {
					$country_code = $data['country'];
					$member_country = ( !empty($country_code) ) ? $locale_options['country'][$country_code] : '';
					$address_info .= $td_h.$member_country.$td_f;
				}
				if(isset($_REQUEST['check']['tel'])) $address_info .= $td_h.wc2_entity_decode($data['tel'], $ext).$td_f;
				if(isset($_REQUEST['check']['fax'])) $address_info .= $td_h.wc2_entity_decode($data['fax'], $ext).$td_f;
				break;
			}
			$address_info_args = compact( 'td_h', 'td_f', 'ext', 'member_id', 'applyform' );
			$line .= apply_filters( 'wc2_filter_mem_csv_address_info', $address_info, $data, $address_info_args );

			//csmb bottom
			$btm_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'bottom' );
			if( !empty($btm_keys) ){
				foreach( $btm_keys as $csmb_key ){
					if( isset($_REQUEST['check'][$csmb_key]) ){
						$meta_value = ( isset( $data[WC2_CUSTOM_MEMBER][$csmb_key] ) ) ? $data[WC2_CUSTOM_MEMBER][$csmb_key]: '';
						$line .= $td_h.wc2_entity_decode($meta_value, $ext).$td_f;
					}
				}
			}
			
			if(isset($_REQUEST['check']['rank'])) {
				$rank_num = $data['rank'];
				$line .= $td_h.$wc2_options['rank_type'][$rank_num].$td_f;
			}
			if(isset($_REQUEST['check']['point'])) $line .= $td_h.$data['point'].$td_f;
			if(isset($_REQUEST['check']['registered'])) $line .= $td_h.$data['registered'].$td_f;

			//csmb other
			$oth_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'other' );
			if( !empty($oth_keys) ){
				foreach( $oth_keys as $csmb_key ){
					if( isset($_REQUEST['check'][$csmb_key]) ){
						$meta_value = ( isset( $data[WC2_CUSTOM_MEMBER][$csmb_key] ) ) ? $data[WC2_CUSTOM_MEMBER][$csmb_key]: '';
						$line .= $td_h.wc2_entity_decode($meta_value, $ext).$td_f;
					}
				}
			}
			$line .= apply_filters( 'wc2_filter_chk_mem_data', NULL, $wc2_opt_member, $member_id, $data );
			$line .= $tr_f.$lf;
		}
		$line .= $table_f.$lf;

		if($ext == 'xls') {
			header("Content-Type: application/vnd.ms-excel; charset=Shift-JIS");
		} elseif($ext == 'csv') {
			header("Content-Type: application/octet-stream");
		}
		header("Content-Disposition: attachment; filename=wc2_member_list.".$ext);
		mb_http_output('pass');
		print(mb_convert_encoding($line, "SJIS-win", "UTF-8"));
		exit();
	}

	/*******************************************
	* 会員データ一括削除
	* @since	1.0.0
	*
	* NOTE:
	*
	********************************************/
	public function delete_batch_member_data( $mem_ids = array() ){
		if( empty( $mem_ids ) ){
			return false;
		}elseif( !is_array($mem_ids) ){
			$mem_ids = (array)$mem_ids;
		}
		$res = array();
		$i = 0;
		$wc2_db_member = WC2_DB_Member::get_instance();
		foreach( $mem_ids as $mem_id ){
			$i++;
			$res[$i] = $wc2_db_member->delete_member_data( $mem_id );
			if( -1 === $res[$i] ) break;
		}

		if( in_array( -1, $res, true ) ) {
			$result = -1;
		} else {
			$result = ( 0 < array_sum($res) ) ? 1 : 0;
		}
		return $result;
	}
}