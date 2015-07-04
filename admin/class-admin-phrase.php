<?php
/**
 * Welcart2.
 *
 * @package   WC2_Phrase_Setting
 * @author    Collne Inc. <author@welcart.com>
 * @license   GPL-2.0+
 * @link      http://www.welcart2.com
 * @copyright 2014 Collne Inc.
 */

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
class WC2_Phrase_Setting extends WC2_Admin_Page {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	//screen_optionに設定
	public static $per_page_slug = 'phrase_setting_page';

	/***********************************
	 * Constructor
	 *
	 * @since     1.0.0
	 ***********************************/
	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_phrase_ajax', array( $this, 'phrase_ajax' ) );
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
	 * Add a tab to the Contextual Help menu in an admin page.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function admin_help_setting( $help, $screen_id, $screen ) {
		if( !isset( $this->plugin_screen_hook_suffix ) or $this->plugin_screen_hook_suffix != $screen->id ) return;

		$tabs = array(
			array(
				'title' => 'メール送信設定',
				'id' => 'phrase_setting',
				'callback' => array( $this, 'get_help_phrase_setting' )
			),
			array(
				'title' => 'メール定型文設定',
				'id' => 'phrase_edit',
				'callback' => array( $this, 'get_help_phrase_edit' )
			)
		);

		foreach( $tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}
	}

	function get_help_phrase_setting() {
		echo "<dl>
				<dt>新規会員登録管理者宛メール</dt>
					<dd>新規会員登録があったとき、管理者にメールを送信するかしないかを設定します。</dd>
				<dt>会員削除管理者宛メール</dt>
					<dd>会員の削除があったとき、管理者にメールを送信するかしないかを設定します。</dd>
				<dt>会員削除お客様宛メール</dt>
					<dd>会員の削除があったとき、会員にメールを送信するかしないかを設定します。</dd>
				<dt>会員編集お客様宛メール</dt>
					<dd>会員情報の更新があったとき、会員にメールを送信するかしないかを設定します。</dd>
			</dl>";
	}

	function get_help_phrase_edit() {
		echo "<dl>
				<dt>サンキューメール（自動送信）</dt>
					<dd>受注時にお客様に対して自動送信するメールです。ボディには購入商品明細が入ります。</dd>
				<dt>受注メール（自動送信）</dt>
					<dd>受注時に受注用メールアドレス（基本設定）に対して送信するメールです。ボディには購入商品明細が入ります。</dd>
				<dt>問い合わせ受付メール（自動送信）</dt>
					<dd>問い合わせ時に、お客様宛てに自動送信するメールです。ボディはありません。</dd>
				<dt>入会完了のご連絡メール（自動送信）</dt>
					<dd>会員登録が完了したときに自動送信されるメールです。ボディはありません。</dd>
				<dt>発送完了メール（管理画面より送信）</dt>
					<dd>管理画面より発送完了登録した際に手動送信するメール。ボディには購入商品明細が入ります。</dd>
				<dt>ご注文確認メール（管理画面より送信）</dt>
					<dd>管理画面より新規受注を登録した際に手動送信するメール。ボディには購入商品明細が入ります。</dd>
				<dt>ご入金確認のご連絡メール（管理画面より送信）</dt>
					<dd>振込み入金等を確認したときに手動送信するメール。ボディには購入商品明細が入ります。</dd>
				<dt>お見積りメール（管理画面より送信）</dt>
					<dd>管理画面より見積り登録したときに手動送信するメール。ボディには見積り商品明細が入ります。</dd>
				<dt>ご注文キャンセルの確認メール（管理画面より送信）</dt>
					<dd>受注をキャンセルした際に手動送信するメール。ボディには購入商品明細が入ります。</dd>
				<dt>その他のメール（管理画面より送信）</dt>
					<dd>臨時で送信するメール。ボディはありません。</dd>
			</dl>";
	}

	/***********************************
	 * Add a sub menu page.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function add_admin_menu() {
		$this->plugin_screen_hook_suffix = add_submenu_page( $this->menu_slug['setting'], 'メール設定', 'メール設定', 'create_users', 'wc2_phrase', array( $this, 'phrase_setting_page' ) );
		add_action( 'load-' . $this->plugin_screen_hook_suffix, array( $this, 'load_phrase_action' ) );
	}

	/***********************************
	 * Runs when an administration menu page is loaded.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function load_phrase_action() {

	}

	/***********************************
	 * The function to be called to output the content for this page.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function phrase_setting_page() {
		if( !isset( $this->plugin_screen_hook_suffix ) )
			return;

		$screen = get_current_screen();
		if( $this->plugin_screen_hook_suffix != $screen->id )
			return;

		$phrase_options = wc2_get_option( 'phrase' );

		if( array_key_exists( 'wc2_option_update', $_POST ) ) {
			$phrase_options['newmem_admin_mail'] = ( isset($_POST['newmem_admin_mail']) ) ? $_POST['newmem_admin_mail'] : 1;
			$phrase_options['delmem_admin_mail'] = ( isset($_POST['delmem_admin_mail']) ) ? $_POST['delmem_admin_mail'] : 1;
			$phrase_options['delmem_customer_mail'] = ( isset($_POST['delmem_customer_mail']) ) ? $_POST['delmem_customer_mail'] : 1;
			$phrase_options['editmem_customer_mail'] = ( isset($_POST['editmem_customer_mail']) ) ? $_POST['editmem_customer_mail'] : 0;
			wc2_update_option( 'phrase', $phrase_options );

			$this->action_status = 'success';
			$this->action_message = __( 'Updated!' );

		} else {
			$this->action_status = 'none';
			$this->action_message = '';
		}

		$newmem_admin_mail = ( isset($phrase_options['newmem_admin_mail']) ) ? $phrase_options['newmem_admin_mail'] : 1;
		$delmem_admin_mail = ( isset($phrase_options['delmem_admin_mail']) ) ? $phrase_options['delmem_admin_mail'] : 1;
		$delmem_customer_mail = ( isset($phrase_options['delmem_customer_mail']) ) ? $phrase_options['delmem_customer_mail'] : 1;
		$editmem_customer_mail = ( isset($phrase_options['editmem_customer_mail']) ) ? $phrase_options['editmem_customer_mail'] : 0;

		$status = $this->action_status;
		$message = $this->action_message;

		require_once( WC2_PLUGIN_DIR.'/admin/views/setting-phrase.php' );
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
?>
<script type="text/javascript">
jQuery(function($) {
	var tb = $("#phrase-tabs").tabs();

	if( $.fn.jquery < "1.10" ) {
		var $tabs = $("#phrase-tabs").tabs({
			cookie: {
				expires: 1
			}
		});
	} else {
		$( "#phrase-tabs" ).tabs({
			active: ($.cookie("phrase-tabs")) ? $.cookie("phrase-tabs") : 0
			, activate: function( event, ui ){
				$.cookie("phrase-tabs", $(this).tabs("option", "active"));
			}
		});
	}

	$("#phrase_select").change(function() {
		var phrase = $("#phrase_select option:selected").val();
		if( phrase == "" ) return false;
		$("#loading").html('<img src="'+WC2L10n.loading_gif+'" />');
		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: {
				action: "phrase_ajax",
				mode: "select",
				phrase: phrase,
				wc2_nonce: $("#wc2_nonce").val()
			}
		}).done( function( retVal ) {
			$("#loading").html("");
			var phrase = retVal.split("<?php echo WC2_SPLIT; ?>");
			if( phrase[0] == "OK" ) {
				$("#title").val("");
				$("#header").val("");
				$("#footer").val("");
				$("#title").val(phrase[1]);
				$("#header").val(phrase[2]);
				$("#footer").val(phrase[3]);
			}
		}).fail( function( retVal ) {
			$("#loading").html("");
		});
		return false;
	});

	$("#update-phrase").click(function() {
		var phrase = $("#phrase_select option:selected").val();
		if( phrase == "" ) return false;
		//if( !confirm($("#phrase_select option:selected").text()+"を更新します。よろしいですか？") ) {
		//	return false;
		//}

		$("#loading").html('<img src="'+WC2L10n.loading_gif+'" />');
		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: {
				action: "phrase_ajax",
				mode: "update",
				phrase: phrase,
				title: encodeURIComponent($("#title").val()),
				header: encodeURIComponent($("#header").val()),
				footer: encodeURIComponent($("#footer").val()),
				wc2_nonce: $("#wc2_nonce").val()
			}
		}).done( function( retVal ) {
			$("#loading").html("");
			var data = retVal.split("<?php echo WC2_SPLIT; ?>");
			if( data[0] == "OK" ) {
				if( $("#footer").val() == "" ) $("#footer").val(data[1]);
				$("#aniboxStatus").attr("class","success");
				$("#info_image").attr("src", WC2L10n.success_info);
				$("#info_message").html("<?php echo esc_js(__( 'Updated!' )); ?>");
			}
		}).fail( function( retVal ) {
			$("#loading").html("");
		});
		return false;
	});
<?php do_action( 'wc2_action_admin_phrase_scripts' ); ?>
});
</script>
<?php
	}

	/***********************************
	 * AJAX request's action.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function phrase_ajax() {
		//if( !check_ajax_referer( 'wc2_setting_phrase', 'wc2_nonce', false ) ) die();
		if( !isset($_POST['action']) or !isset($_POST['mode']) ) die();
		if( $_POST['action'] != 'phrase_ajax' ) die();

		$res = '';

		switch( $_POST['mode'] ) {
		case 'select':
			$phrase = $_POST['phrase'];
			$phrase_options = wc2_get_option( 'phrase' );
			$phrase_default = wc2_get_option( 'phrase_default' );

			$title = ( WC2_Utils::is_blank($phrase_options['title'][$phrase]) ) ? $phrase_default['title'][$phrase] : $phrase_options['title'][$phrase];
			$header = ( WC2_Utils::is_blank($phrase_options['header'][$phrase]) ) ? $phrase_default['header'][$phrase] : $phrase_options['header'][$phrase];
			$footer = ( WC2_Utils::is_blank($phrase_options['footer'][$phrase]) ) ? $phrase_default['footer'][$phrase] : $phrase_options['footer'][$phrase];
			$res = 'OK'.WC2_SPLIT.$title.WC2_SPLIT.$header.WC2_SPLIT.$footer;
			break;

		case 'update':
			if( !check_ajax_referer( 'wc2_setting_phrase', 'wc2_nonce', false ) ) {
				$res = 'NG'.WC2_SPLIT.__( 'Security error.' );
			} else {
				$_POST = wc2_stripslashes_deep_post( $_POST );
				$phrase = $_POST['phrase'];
				$phrase_options = wc2_get_option( 'phrase' );

				$phrase_options['title'][$phrase] = urldecode(trim($_POST['title']));
				$phrase_options['header'][$phrase] = urldecode(trim($_POST['header']));
				$phrase_options['footer'][$phrase] = urldecode(trim($_POST['footer']));
				$phrase_options = apply_filters( 'wc2_filter_admin_phrase_update', $phrase_options, $phrase );
				wc2_update_option( 'phrase', $phrase_options );
				$res = 'OK'.WC2_SPLIT.$phrase_options['footer'][$phrase];
			}
			break;
		}
		$res = apply_filters( 'wc2_filter_admin_phrase_ajax', $res );
		die( $res );
	}

}
