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
class WC2_System_Setting extends WC2_Admin_Page {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	//screen_optionに設定
	public static $per_page_slug = 'system_setting_page';

	/***********************************
	 * Constructor
	 *
	 * @since     1.0.0
	 ***********************************/
	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_system_ajax', array( $this, 'system_ajax' ) );
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
				'title' => 'システム設定',
				'id' => 'system',
				'callback' => array( $this, 'get_help_system' )
			),
			array(
				'title' => '国・言語・通貨',
				'id' => 'locale',
				'callback' => array( $this, 'get_help_locale' )
			)
		);

		foreach( $tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}
	}

	function get_help_system() {
		echo "<dl>
				<dt>SSLを使用する</dt>
					<dd>SSL を使用する場合はチェックを付けてください。</dd>
				<dt>WordPress のアドレス (SSL)</dt>
					<dd>WordPress「一般」設定の「WordPress のアドレス」が SSL を通した場合、どのようなアドレスになるかを入力します。例えば、独自ドメインの場合はスキームが https:// と変わるだけですが、共用SSL の場合はドメインやパスも変わってきますのでご注意ください。（管理画面が SSL になるわけではありません）</dd>
				<dt>ブログのアドレス（SSL）</dt>
					<dd>WordPress「一般」設定の「ブログのアドレス」が SSL を通した場合、どのようなアドレスになるかを入力します。</dd>
				<dt>表示モード</dt>
					<dd>ショップにて、複数の投稿が表示されるループ表示の際、商品データを表示させるかどうかを設定します。</dd>
				<dt>rel属性</dt>
					<dd>商品詳細ページにて、Lightbox などプラグインを利用してイメージを表示させるためのアンカータグ用 rel 属性を指定します。</dd>
				<dt>複合カテゴリーソート項目</dt>
					<dd>複合カテゴリー検索ページで表示するカテゴリーにおいて、ソートする対象を選択します。</dd>
				<dt>複合カテゴリーソート順</dt>
					<dd>複合カテゴリー検索ページで表示するカテゴリーにおいて、ソートする順を選択します。</dd>
				<dt>お問い合わせフォームのページID</dt>
					<dd>SSL を通した Welcart お問い合わせフォームを利用したい場合、その page_id を指定します。SSL を通す必要が無い場合は空白にしておきます。パーマリンクを使用している場合、このページのパーマリンクは”usces-inquiry”でなくてはいけません。</dd>
				<dt>注文番号ルール</dt>
					<dd>・連番 --- 注文番号は1000から始まる連番となります。（初期値）</dd>
					<dd>・ランダムな文字列 --- プレフィックスや指定した桁数でアルファベットによる文字列を生成します。</dd>
				<dt>注文番号プレフィックス【半角英字】</dt>
					<dd>必要ない場合は空白にします。</dd>
				<dt>注文番号の桁数【半角数字】</dt>
					<dd>注文番号の桁数を指定できます。「6」未満は指定できません。「8」桁以上にすることをお勧めします。この桁数にはプレフィックスは含まれていません。</dd>
				<dt>商品サブ画像適用ルール</dt>
					<dd>サブ画像が正常に適用されない場合は新しいルールを適用してください。</dd>
				<dt>納品書の記載方法</dt>
					<dd>「購入者情報を宛名とする」を選択した場合、配送先が購入者の情報と異なるときは配送先が宛名（購入者情報）の下に記載されます。「配送先を宛名とする」を選択した場合は配送先の情報のみが宛名として記載されます。</dd>
				<dt>CSVファイルの文字コード</dt>
					<dd>CSVファイルをアップロードして商品登録を行う場合は、ここで選択した文字コードのCSVファイルをアップロードしてください。</dd>
			</dl>";
	}

	function get_help_locale() {
		echo "<dl>
				<dt>フロントエンドの言語</dt>
					<dd>フロントエンド（ショップ側）の言語を選択できます。バックエンド（管理パネル）の言語は config.php の設定に従います。</dd>
				<dt>通貨表示</dt>
					<dd>選択した国に合わせた通貨記号や金額の区切り文字や少数桁を設定します。フロントエンド（ショップ側）、バックエンド（管理パネル）共通です。</dd>
				<dt>住所氏名の様式</dt>
					<dd>住所氏名などの入力フォームの様式を、どの国のものにするか選択します。</dd>
				<dt>販売対象国</dt>
					<dd>販売・配送可能な地域を国単位で選択します。複数選択可（Ctrl＋クリック）。値を変更した場合は「選択してください」ボタンを押してください。いずれも選択されていない場合はエラーとなります。</dd>
				<dt>都道府県</dt>
					<dd>販売対象地区（都道府県) を、改行して1行に1つずつ記入します。販売対象国が選択されると、設定できる国がセレクトボックスに追加されます。セレクトボックスから国を選んで、販売対象地区（都道府県）を入力してください。日本とアメリカのみ、空白にして更新した場合、初期値が設定されます。</dd>
			</dl>";
	}

	/***********************************
	 * Add a sub menu page.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function add_admin_menu() {
		$this->plugin_screen_hook_suffix = add_submenu_page( $this->menu_slug['setting'], 'システム設定', 'システム設定', 'create_users', 'wc2_system', array( $this, 'system_setting_page' ) );
		add_action( 'load-' . $this->plugin_screen_hook_suffix, array( $this, 'load_system_action' ) );
	}

	/***********************************
	 * Runs when an administration menu page is loaded.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function load_system_action() {

	}

	/***********************************
	 * The function to be called to output the content for this page.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function system_setting_page() {
		if( !isset( $this->plugin_screen_hook_suffix ) )
			return;

		$screen = get_current_screen();
		if( $this->plugin_screen_hook_suffix != $screen->id )
			return;

		$system_options = wc2_get_option( 'system' );
		$states = wc2_get_option( 'states_options' );
		$locale = wc2_get_option( 'locale_options' );

		if( array_key_exists( 'wc2_option_update', $_POST ) ) {

			$this->action_status = 'success';
			$_POST = WC2_Utils::stripslashes_deep_post($_POST);

			$system_options['divide_item'] = ( isset($_POST['divide_item']) ) ? 1 : 0;
			$system_options['itemimg_anchor_rel'] = ( isset($_POST['itemimg_anchor_rel']) ) ? trim($_POST['itemimg_anchor_rel']) : '';
			$system_options['composite_category_orderby'] = ( isset($_POST['composite_category_orderby']) ) ? $_POST['composite_category_orderby'] : '';
			$system_options['composite_category_order'] = ( isset($_POST['composite_category_order']) ) ? $_POST['composite_category_order'] : '';
			$system_options['settlement_path'] = ( isset($_POST['settlement_path']) ) ? $_POST['settlement_path'] : '';
			if( WC2_Utils::is_blank($system_options['settlement_path']) ) $system_options['settlement_path'] = WC2_PLUGIN_DIR . '/settlement/';
			$sl = substr( $system_options['settlement_path'], -1 );
			if( $sl != '/' && $sl != '\\' ) $system_options['settlement_path'] .= '/';
			$system_options['logs_path'] = ( isset($_POST['logs_path']) ) ? $_POST['logs_path'] : '';
			if( !WC2_Utils::is_blank($system_options['logs_path']) ) {
				$sl = substr($system_options['logs_path'], -1);
				if( $sl == '/' || $sl == '\\' ) $system_options['logs_path'] = substr( $system_options['logs_path'], 0, -1 );
			}
			$system_options['use_ssl'] = ( isset($_POST['use_ssl']) ) ? 1 : 0;
			$system_options['ssl_url'] = ( isset($_POST['ssl_url']) ) ? rtrim( $_POST['ssl_url'], '/' ) : '';
			$system_options['ssl_url_admin'] = ( isset($_POST['ssl_url_admin']) ) ? rtrim($_POST['ssl_url_admin'], '/') : '';
			if( WC2_Utils::is_blank($system_options['ssl_url']) || WC2_Utils::is_blank($system_options['ssl_url_admin']) ) $system_options['use_ssl'] = 0;
			$system_options['inquiry_id'] = ( isset($_POST['inquiry_id']) ) ? esc_html(rtrim($_POST['inquiry_id'])) : '';
			$system_options['use_javascript'] = ( isset($_POST['use_javascript']) ) ? (int)$_POST['use_javascript'] : 1;
			$system_options['front_lang'] = ( isset($_POST['front_lang']) && 'others' != $_POST['front_lang'] ) ? $_POST['front_lang'] : wc2_get_local_language();
			$system_options['currency'] = ( isset($_POST['currency']) && 'others' != $_POST['currency']) ? $_POST['currency'] : wc2_get_base_country();
			$system_options['addressform'] = ( isset($_POST['addressform']) ) ? $_POST['addressform'] : wc2_get_local_addressform();
			$system_options['target_market'] = ( isset($_POST['target_market']) ) ? $_POST['target_market'] : wc2_get_local_target_market();
			$system_options['no_cart_css'] = ( isset($_POST['no_cart_css']) ) ? 1 : 0;
			$system_options['dec_orderID_flag'] = ( isset($_POST['dec_orderID_flag']) ) ? (int)$_POST['dec_orderID_flag'] : 0;
			$system_options['dec_orderID_prefix'] = ( isset($_POST['dec_orderID_prefix']) ) ? esc_html(rtrim($_POST['dec_orderID_prefix'])) : '';
			$system_options['pdf_delivery'] = ( isset($_POST['pdf_delivery']) ) ? (int)$_POST['pdf_delivery'] : 0;
			$system_options['csv_encode_type'] = ( isset($_POST['csv_encode_type']) ) ? (int)$_POST['csv_encode_type'] : 0;

			if( isset($_POST['dec_orderID_digit']) ) {
				$dec_orderID_digit = (int)rtrim($_POST['dec_orderID_digit']);
				if( 6 > $dec_orderID_digit ){
					$system_options['dec_orderID_digit'] = 6;
				} else {
					$system_options['dec_orderID_digit'] = $dec_orderID_digit;
				}
			} else {
				$system_options['dec_orderID_digit'] = 6;
			}
			$system_options['subimage_rule'] = ( isset($_POST['subimage_rule']) ) ? (int)$_POST['subimage_rule'] : 0;
			unset( $system_options['province'] );
			foreach( (array)$system_options['target_market'] as $target_market ) {
				$province = array();
				if( !empty($_POST['province_'.$target_market]) ) {
					$temp_pref = explode( "\n", $_POST['province_'.$target_market] );
					foreach( $temp_pref as $pref ) {
						if( !WC2_Utils::is_blank($pref) ) 
							$province[] = trim($pref);
					}
					if( 1 == count($province) ) 
						$this->action_status = 'error';
				} else {
					if( isset($states[$target_market]) && is_array($states[$target_market]) ) {
						$province = $states[$target_market];
					} else {
						$this->action_status = 'error';
					}
				}
				$system_options['province'][$target_market] = $province;
			}

			if( $this->action_status != 'success' ) {
				$this->action_message = __('データに不備があります','wc2');
			} else {
				wc2_update_option( 'system', $system_options );
				$this->action_message = __( 'Updated!' );
			}

		} else {

			if( !isset($system_options['province']) || empty($system_options['province']) ) {
				$system_options['province'][$system_options['base_country']] = $states[$system_options['base_country']];
			}

			$this->action_status = 'none';
			$this->action_message = '';
		}

		$status = $this->action_status;
		$message = $this->action_message;

		$divide_item = $system_options['divide_item'];
		$itemimg_anchor_rel = $system_options['itemimg_anchor_rel'];
		$composite_category_orderby = $system_options['composite_category_orderby'];
		$composite_category_order = $system_options['composite_category_order'];
		$logs_path = ( isset($system_options['logs_path']) ) ? $system_options['logs_path'] : '';
		$use_ssl = $system_options['use_ssl'];
		$ssl_url = $system_options['ssl_url'];
		$ssl_url_admin = $system_options['ssl_url_admin'];
		$inquiry_id = $system_options['inquiry_id'];
		$orderby_itemsku = ( isset($system_options['orderby_itemsku']) ) ? $system_options['orderby_itemsku'] : 0;
		$orderby_itemopt = ( isset($system_options['orderby_itemopt']) ) ? $system_options['orderby_itemopt'] : 0;
		$system_front_lang = ( isset($system_options['front_lang']) && !empty($system_options['front_lang']) ) ? $system_options['front_lang'] : wc2_get_local_language();
		$system_currency = ( isset($system_options['currency']) && !empty($system_options['currency']) ) ? $system_options['currency'] : wc2_get_base_country();
		$system_addressform = ( isset($system_options['addressform']) && !empty($system_options['addressform']) ) ? $system_options['addressform'] : wc2_get_local_addressform();
		$system_target_markets = ( isset($system_options['target_market']) && !empty($system_options['target_market']) ) ? $system_options['target_market'] : wc2_get_local_target_market();
		$no_cart_css = isset($system_options['no_cart_css']) ? $system_options['no_cart_css'] : 0;
		$dec_orderID_flag = ( isset($system_options['dec_orderID_flag']) ) ? $system_options['dec_orderID_flag'] : 0;
		$dec_orderID_prefix = ( isset($system_options['dec_orderID_prefix']) ) ? $system_options['dec_orderID_prefix'] : '';
		$dec_orderID_digit = ( isset($system_options['dec_orderID_digit']) ) ? $system_options['dec_orderID_digit'] : '';
		$subimage_rule = ( isset($system_options['subimage_rule']) ) ? $system_options['subimage_rule'] : 0;
		$pdf_delivery = ( isset($system_options['pdf_delivery']) ) ? $system_options['pdf_delivery'] : 0;
		$csv_encode_type = ( isset($system_options['csv_encode_type']) ) ? $system_options['csv_encode_type'] : 0;

		require_once( WC2_PLUGIN_DIR.'/admin/views/setting-system.php' );
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
jQuery( function($) {
	var tb = $('#system-tabs').tabs();

	if( $.fn.jquery < "1.10" ) {
		var $tabs = $('#system-tabs').tabs({
			cookie: {
				expires: 1
			}
		});
	} else {
		$( "#system-tabs" ).tabs({
			active: ($.cookie("system-tabs")) ? $.cookie("system-tabs") : 0
			, activate: function( event, ui ){
				$.cookie("system-tabs", $(this).tabs("option", "active"));
			}
		});
	}

	var pre_target = '';

	operation = {
		set_target_market: function() {
			var target = [];
			var target_text = [];
			$('#target_market option:selected').each(function () {
				target.push($(this).val());
				target_text.push($(this).text());
			});
			if( target.length == 0 ) {
				alert("<?php _e('Please select one of the country.', 'wc2'); ?>");
				return -1;
			}
			var sel = $('select_target_market_province').val();
			var name_select = '<select name="select_target_market_province" id="select_target_market_province" onchange="operation.onchange_target_market_province(this.selectedIndex);">'+"\n";
			var target_args = '';
			var c = '';
			for( var i = 0; i < target.length; i++ ) {
				name_select += '<option value="'+target[i]+'">'+target_text[i]+'</option>'+"\n";
				target_args += c+target[i];
				c = ',';
			}
			name_select += "</select>\n";
			$('#target_market_province').html(name_select);
			$('#target_market_loading').html('<img src="'+WC2L10n.loading_gif+'" />');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				cache: false,
				data: {
					action: 'system_ajax',
					mode: 'target_market',
					target: target_args,
					wc2_nonce: $('#wc2_nonce').val()
				}
			}).done( function( retVal ) {
				$('#target_market_loading').html('');
				$('#province_ajax').html('');
				var province = retVal.split('<?php echo WC2_SPLIT; ?>');
				for( var i = 0; i < province.length; i++ ) {
					if( province[i].length > 0 ) {
						var state = province[i].split(',');
						$('#province_ajax').append('<input type="hidden" name="province_'+state[0]+'" id="province_'+state[0]+'" value="'+state[1]+'">');
					}
				}
				$('#select_target_market_province').triggerHandler('change', 0);
				$('#target_market_loading').html('');
			}).fail( function( retVal ) {
				$('#target_market_loading').html('');
			});
			return false;
		},

		onchange_target_market_province: function(index) {
			if( pre_target != '' ) $('#province_'+pre_target).val($('#province').val());
			var target = $('#select_target_market_province option:selected').val();
			$('#province').val('');
			$('#province').val($('#province_'+target).val());
			pre_target = target;
		},

		error_bg_color: function(id) {
			$(id).css({'background-color': '#FFA'}).click(function() {
				$(id).css({'background-color': '#FFF'});
			});
		}
	};

	$("#set_target_market").click(function() {
		operation.set_target_market();
	});

	$('form').submit(function() {
		$('#province_'+pre_target).val($('#province').val());

		var error = 0;
		var tabs = 0;

		if( !WC2Util.checkAlp( $('#dec_orderID_prefix').val() ) ) {
			error++;
			$('#dec_orderID_prefix').css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !WC2Util.checkNum( $('#dec_orderID_digit').val() ) ) {
			error++;
			$('#dec_orderID_digit').css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		var target = [];
		$('#target_market option:selected').each(function() {
			target.push($(this).val());
		});
		if( target.length == 0 ) {
			error++;
			tabs = 1;
			$('#target_market').css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});

		} else {
			var province = 'OK';
			for( var i = 0; i < target.length; i++ ) {
				if( target[i] != 'JP' && target[i] != 'US' ) {
					if( '' == $('#province_'+target[i]).val() ) province = 'NG';
				}
			}
			if( 'OK' != province ) {
				error++;
				tabs = 1;
				$('#province').css({'background-color': '#FFA'}).click(function() {
					$(this).css({'background-color': '#FFF'});
				});
			}
		}

		if( 0 < error ) {
			$("#aniboxStatus").attr("class","error");
			$("#info_image").attr("src", WC2L10n.error_info);
			$("#info_message").html("データに不備があります");
			tb.tabs('option', 'active', tabs);
			return false;
		} else {
			return true;
		}
	});
<?php do_action( 'wc2_action_admin_system_scripts' ); ?>
});
jQuery(document).ready(function($) {
	operation.set_target_market();
});
</script>
<?php
	}

	/***********************************
	 * AJAX request's action.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function system_ajax() {
		if( !check_ajax_referer( 'wc2_setting_system', 'wc2_nonce', false ) ) die();
		if( !isset($_POST['action']) or !isset($_POST['mode']) ) die();
		if( $_POST['action'] != 'system_ajax' ) die();

		$res = '';
		switch( $_POST['mode'] ) {
		case 'target_market':
			$states = wc2_get_option( 'states_options' );
			$data = wc2_stripslashes_deep_post($_POST);
			$target = explode(',', $data['target']);
			foreach( (array)$target as $country ) {
				$prefs = $states[$country];
				if( is_array($prefs) and 0 < count($prefs) ) {
					$res .= $country.",";
					foreach( (array)$prefs as $state ) {
						$res .= $state."\n";
					}
					$res = rtrim($res, "\n").WC2_SPLIT;
				} else {
					$res .= $country.','.WC2_SPLIT;
				}
			}
			$res = rtrim($res, WC2_SPLIT);
		}
		$res = apply_filters( 'wc2_filter_admin_system_ajax', $res );
		die($res);
	}
}
