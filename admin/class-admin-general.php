<?php
/**
 * Welcart2.
 *
 * @package   WC2_General_Setting
 * @author    Collne Inc. <author@welcart.com>
 * @license   GPL-2.0+
 * @link      http://www.welcart2.com
 * @copyright 2014 Collne Inc.
 */

if( !class_exists('Calendar_Data') )
	require_once(WC2_PLUGIN_DIR . '/admin/includes/class-calendar.php');

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
class WC2_General_Setting extends WC2_Admin_Page {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	//screen_optionに設定
	public static $per_page_slug = 'general_setting_page';

	protected $cal = array();

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
	function admin_help_setting( $help, $screen_id, $screen ) {
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			$tabs = array(
				array(
					'title' => 'ショップ設定',
					'id' => 'shop_setting',
					'callback' => array( $this, 'get_help_shop_setting' )
				),
				array(
					'title' => '営業設定',
					'id' => ' business_setting',
					'callback' => array( $this, 'get_help_business_setting' )
				),
				array(
					'title' => '会員システム',
					'id' => 'membership_system',
					'callback' => array( $this, 'get_help_membership_system' )
				),
				array(
					'title' => '商品名の表示ルール',
					'id' => 'cartpage_setting',
					'callback' => array( $this, 'get_help_cartpage_setting' )
				),
				array(
					'title' => 'カート関連ページに挿入する説明書き',
					'id' => 'cart_description',
					'callback' => array( $this, 'get_help_cart_description' )
				),
				array(
					'title' => '会員関連ページに挿入する説明書き',
					'id' => 'member_description',
					'callback' => array( $this, 'get_help_member_description' )
				)
			);

			foreach( $tabs as $tab ) {
				$screen->add_help_tab( $tab );
			}
		}
	}

	function get_help_shop_setting() {
		echo "<dl>
				<dt>会社名【必須】</dt>
					<dd>ショップの運営会社名。</dd>
				<dt>郵便番号【必須】</dt>
					<dd>ショップ運営者の郵便番号。</dd>
				<dt>住所１【必須】</dt>
					<dd>ショップ運営者の住所。</dd>
				<dt>住所２</dt>
					<dd>ショップ運営者の住所のうち建物名など。</dd>
				<dt>電話番号・FAX番号</dt>
					<dd>ショップの連絡先。</dd>
				<dt>受注用メールアドレス【必須】</dt>
					<dd>注文内容を受け取るための管理者のメールアドレス。カンマ区切りで複数指定できます。</dd>
				<dt>問い合わせメールアドレス【必須】</dt>
					<dd>問い合わせ内容を受け取るための管理者のメールアドレス。</dd>
				<dt>送信元メールアドレス【必須】</dt>
					<dd>購入者にサンキューメールを送る際の送信者アドレス。</dd>
				<dt>エラーメールアドレス【必須】</dt>
					<dd>メールが不達の場合のエラーメールの送信先アドレス。</dd>
				<dt>コピーライト</dt>
					<dd>設定があればフッターに表示します。</dd>
					<dd>例）Copyright(c) 2015 Welcart.inc All Rights Reserved.</dd>
				<dt>送料無料条件</dt>
					<dd>送料無料サービスを行う条件となる最低購入金額。送料無料サービスを行わない場合は空白にしてください。</dd>
				<dt>購入制限数初期値</dt>
					<dd>一度に購入できる数量上限初期値。商品マスターで商品ごとに設定できます。必要ない場合は空白にしてください。</dd>
				<dt>発送日の初期値</dt>
					<dd>受注してから発送までの期間の目安。商品マスターで商品ごとに設定できます。</dd>
				<dt>消費税区分</dt>
					<dd>消費税込みか消費税別かを指定します。</dd>
				<dt>消費税対象</dt>
					<dd>消費税計算の対象を商品のみとするか、手数料や送料なども含めて計算するかを指定します。</dd>
				<dt>消費税率【必須】</dt>
					<dd>税込みの場合も入力してください。</dd>
				<dt>税計算方法</dt>
					<dd>外税の場合の消費税計算方法。</dd>
				<dt>「カートに入れる」ボタンの挙動</dt>
					<dd>カートに既に同じ商品が入っていた場合、数量を加算するか変更するかを設定します。</dd>
			</dl>";
	}

	function get_help_business_setting() {
		echo "<dl>
				<dt>表示モード</dt>
					<dd>ショップの表示モードを選択します。「メンテナンス中」を選んだ場合、ショップはメンテナンス中のメッセージを表示して、お客様はショップに入れなくなります。管理画面にログインしている管理者はメンテナンス中でも通常通りショップに入ることができます。この他「キャンペーン中」というモードがあります。こちらは「営業日設定」でスケジューリングされたキャンペーン期間中に「キャンペーン中」モードになります。
キャンペーン期間中は特定の商品に割引などの特典を付けることができます。モードの優先順位は、「メンテナンス中」→「キャンペーン中」→「通常営業中」となります。</dd>
				<dt>キャンペーン対象</dt>
					<dd>「キャンペーン中」モードのときに特典を付ける商品カテゴリーを選択します。このカテゴリーに属する商品を購入した際には特典が適用されます。</dd>
				<dt>キャンペーン特典</dt>
					<dd>キャンペーン期間中の特典を設定します。</dd>
					<dd>・ポイント --- 会員のみの特典で、通常のポイントの何倍で付与するかを設定します。</dd>
					<dd>・割引 --- 全購入者が対象で、販売価格の何割引で販売するかを設定します。</dd>
				<dt>キャンペーンスケジュール</dt>
					<dd>キャンペーンサービスを行う期間の設定を行います。開始日時になると、「基本設定」で設定した対象商品にキャンペーン特典がつきます。終了日時になるとキャンペーンモードは終了して通常営業モードに戻ります。キャンペーンを行わないときは、開始及び終了日時の「年」を空白にします。</dd>
				<dt>営業日カレンダー</dt>
					<dd>発送等業務の休日の設定を行います。発送業務をお休みにしたい日をクリックすると色が変わります。取り消す場合はもう一度クリックします。曜日をクリックすると該当曜日が全て変わります。</dd>
			</dl>";
	}

	function get_help_membership_system() {
		echo "<dl>
				<dt>会員システム</dt>
					<dd>会員システムを利用するかしないかを設定します。</dd>
				<dt>会員ポイント</dt>
					<dd>会員システムを利用した場合の、ポイント付与機能を利用するかしないかを設定します。</dd>
				<dt>ポイント率初期値</dt>
					<dd>商品登録時の初期値。必要ない場合は空白にしてください。</dd>
				<dt>会員登録時ポイント</dt>
					<dd>初回会員登録時に付与するポイント。必要ない場合は空白にしてください。</dd>
				<dt>ポイントの適用範囲</dt>
					<dd>お客様が利用できるポイントの適用範囲を選択します。<br />初期値は「商品合計金額のみに制限」されています。「商品合計額及び手数料などにも適用」を選択すると、送料や代引手数料もポイントで支払う事ができるようになります。</dd>
				<dt>ポイント付与のタイミング</dt>
					<dd>・即時 --- ポイントはお買い物完了時に付与されます。</dd>
					<dd>・入金時 --- 銀行振込、コンビニ決済などで、お買い物完了時にはポイントは付与されず、入金済になったときにポイントを付与します。</dd>
				<dt>会員パスワードの文字数制限</dt>
					<dd>会員のパスワードの長さを制限できます。文字数の上限値は空白で登録すると上限なしとなります。</dd>
			</dl>";
	}

	function get_help_cartpage_setting() {
		echo "<dl>
				<dt>商品名の表示ルール</dt>
					<dd>カートなどに表示する商品名の表示・非表示、及び表示の並び順を指定します。<br />このルールはカートページ、内容確認ページ、会員情報購入履歴、メール、見積書、納品書などの商品名として適用されます。</dd>
			</dl>";
	}

	function get_help_cart_description() {
		echo "<dl>
				<dt>カート関連ページに挿入する説明書き</dt>
					<dd>「カートページ」、「お客様情報ページ」、「配送・支払方法ページ」、「内容確認ページ」、「完了ページ」それぞれの冒頭部分と終わり部分に html を挿入できます。必要に応じて説明書きを設定してください。</dd>
			</dl>";
	}

	function get_help_member_description(){
		echo "<dl>
				<dt>会員関連ページに挿入する説明書き</dt>
					<dd>「ログインページ」、「新規会員登録ページ」、「新パスワード取得ページ」、「パスワード変更ページ」、「会員情報ページ」、「完了ページ」それぞれの冒頭部分と終わり部分に html を挿入できます。必要に応じて説明書きを設定してください。</dd>
			</dl>";
	}

	/***********************************
	 * 表示オプションの表示制御
	 * @since    1.0.0
	 *
	 * NOTE:  $show_screen = 1は表示オプションを表示、0は非表示
	 ***********************************/
/*
	function admin_show_screen( $show_screen, $screen ) {
		if( !isset( $screen->id ) || false === strpos( $screen->id, 'toplevel_page_wc2_setting' ) )
			return $show_screen;

		return 1;
	}
*/

	/***********************************
	 * Add a sub menu page.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function add_admin_menu() {
		$this->plugin_screen_hook_suffix = add_submenu_page( $this->menu_slug['setting'], '基本設定', '基本設定', 'create_users', $this->menu_slug['setting'], array( $this, 'general_setting_page' ) );
		add_action( 'load-' . $this->plugin_screen_hook_suffix, array( $this, 'load_general_action' ) );
	}

	/***********************************
	 * Runs when an administration menu page is loaded.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function load_general_action() {

	}

	/***********************************
	 * The function to be called to output the content for this page.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function general_setting_page() {
		global $allowedposttags;

		if( !isset( $this->plugin_screen_hook_suffix ) )
			return;

		$screen = get_current_screen();
		if( $this->plugin_screen_hook_suffix != $screen->id )
			return;

		$general = wc2_get_option('general');
		$cart_description = wc2_get_option('cart_description');
		$member_description = wc2_get_option('member_description');

		if( array_key_exists( 'wc2_option_update', $_POST ) ){
			check_admin_referer( 'wc2_setting_general', 'wc2_nonce' );
//			$this->error_message = $this->setting_delivery_check();

			$_POST = WC2_Utils::stripslashes_deep_post($_POST);

			//ショップ設定
			$general['company_name'] = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
			$general['zip_code'] = isset($_POST['zip_code']) ? trim($_POST['zip_code']) : '';
			$general['address1'] = isset($_POST['address1']) ? trim($_POST['address1']) : '';
			$general['address2'] = isset($_POST['address2']) ? trim($_POST['address2']) : '';
			$general['tel_number'] = isset($_POST['tel_number']) ? trim($_POST['tel_number']) : '';
			$general['fax_number'] = isset($_POST['fax_number']) ? trim($_POST['fax_number']) : '';
			$general['order_mail'] = isset($_POST['order_mail']) ? trim($_POST['order_mail']) : '';
			$general['inquiry_mail'] = isset($_POST['inquiry_mail']) ? trim($_POST['inquiry_mail']) : '';
			$general['sender_mail'] = isset($_POST['sender_mail']) ? trim($_POST['sender_mail']) : '';
			$general['error_mail'] = isset($_POST['error_mail']) ? trim($_POST['error_mail']) : '';
			$general['postage_privilege'] = isset($_POST['postage_privilege']) ? trim($_POST['postage_privilege']) : '';
			$general['purchase_limit'] = isset($_POST['purchase_limit']) ? trim($_POST['purchase_limit']) : '';
			$general['shipping_rule'] = isset($_POST['shipping_rule']) ? trim($_POST['shipping_rule']) : '';
			$general['tax_mode'] = isset($_POST['tax_mode']) ? trim($_POST['tax_mode']) : 'include';
			$general['tax_target'] = isset($_POST['tax_target']) ? trim($_POST['tax_target']) : 'products';
			$general['tax_rate'] = isset($_POST['tax_rate']) ? (int)$_POST['tax_rate'] : '';
			$general['tax_method'] = isset($_POST['tax_method']) ? trim($_POST['tax_method']) : '';
			$general['copyright'] = isset($_POST['copyright']) ? trim($_POST['copyright']) : '';
			$general['add2cart'] = isset($_POST['add2cart']) ? trim($_POST['add2cart']) : '0';

			//営業設定
			$general['display_mode'] = isset($_POST['display_mode']) ? trim($_POST['display_mode']) : '';
			$general['campaign_category'] = empty($_POST['cat']) ? '0' : $_POST['cat'];
			$general['campaign_privilege'] = isset($_POST['cat_privilege']) ? trim($_POST['cat_privilege']) : '';
			$general['privilege_point'] = isset($_POST['point_num']) ? (int)$_POST['point_num'] : '';
			$general['privilege_discount'] = isset($_POST['discount_num']) ? (int)$_POST['discount_num'] : '';
			$general['campaign_schedule'] = isset($_POST['campaign_schedule']) ? $_POST['campaign_schedule'] : '0';
			if(isset($_POST['business_days'])){
				$general['business_days'] = $_POST['business_days'];
			}

			//会員システム
			$general['membersystem_state'] = isset($_POST['membersystem_state']) ? trim($_POST['membersystem_state']) : '';
			$general['membersystem_point'] = isset($_POST['membersystem_point']) ? trim($_POST['membersystem_point']) : '';
			$general['point_rate'] = isset($_POST['point_rate']) ? (int)$_POST['point_rate'] : 1;
			$general['start_point'] = isset($_POST['start_point']) ? (int)$_POST['start_point'] : '';
			$general['point_coverage'] = isset($_POST['point_coverage']) ? (int)$_POST['point_coverage'] : 0;
			$general['point_assign'] = isset($_POST['point_assign']) ? (int)$_POST['point_assign'] : 1;
			$general['member_pass_rule_min'] = isset($_POST['member_pass_rule_min']) ? (int)$_POST['member_pass_rule_min'] : 6;
			$general['member_pass_rule_max'] = isset($_POST['member_pass_rule_max']) && !empty($_POST['member_pass_rule_max']) ? (int)$_POST['member_pass_rule_max'] : '';

			//カートページ設定
			foreach( $general['indi_item_name'] as $key => $val ){
				$general['indi_item_name'][$key] = isset($_POST['indication'][$key]) ? 1 : 0;
			}
			foreach ( $_POST['position'] as $key => $value ) {
				$general['position'][$key] = $value;
			}

			//カート関連ページに挿入する説明書き
			foreach ($_POST['cart_header'] as $key => $value ) {
				$cart_description['cart_header'][$key] = isset($_POST['cart_header'][$key]) ? addslashes(wp_kses($value, $allowedposttags)) : '';
			}
			foreach ( $_POST['cart_footer'] as $key => $value ) {
				$cart_description['cart_footer'][$key] = isset($_POST['cart_footer'][$key]) ? addslashes(wp_kses($value, $allowedposttags)) : '';
			}

			//会員関連ページに挿入する説明書き
			foreach ( $_POST['member_header'] as $key => $value ) {
				$member_description['member_header'][$key] = isset($_POST['member_header'][$key]) ? addslashes(wp_kses($value, $allowedposttags)) : '';
			}
			foreach ( $_POST['member_footer'] as $key => $value ) {
				$member_description['member_footer'][$key] = isset($_POST['member_footer'][$key]) ? addslashes(wp_kses($value, $allowedposttags)) : '';
			}

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

			$general = apply_filters( 'wc2_filter_admin_setup_options', $general );
			wc2_update_option( 'general', $general );
			wc2_update_option( 'cart_description', $cart_description );
			wc2_update_option( 'member_description', $member_description );

			do_action('wc2_action_general_option_update');

			$this->action_status = 'success';
			$this->action_message = __( 'Updated!' );
		} else {
			$this->action_status = 'none';
			$this->action_message = '';
		}

		$status = $this->action_status;
		$message = $this->action_message;

		//today
		list( $todayyy, $todaymm, $todaydd ) = wc2_get_today();
		for( $i = 0; $i <12; $i++ ){
			if( 0 == $i ){
				$this->cal[$i] = new Calendar_Data();
				$this->cal[$i]->setToday($todayyy, $todaymm, $todaydd);
				$this->cal[$i]->setCalendarData();
			}
			list( $month_yy[$i], $month_mm[$i], $month_dd[$i] ) = wc2_get_aftermonth( $todayyy, $todaymm, 1, $i );
			$this->cal[$i] = new Calendar_Data();
			$this->cal[$i]->setToday( $month_yy[$i], $month_mm[$i], $month_dd[$i] );
			$this->cal[$i]->setCalendarData();
		}

		$yearstr = substr(get_date_from_gmt(gmdate('Y-m-d H:i:s', time())), 0, 4);

		$campaign_schedule_start_year = isset($general['campaign_schedule']['start']['year']) ? $general['campaign_schedule']['start']['year'] : 0;
		$campaign_schedule_start_month = isset($general['campaign_schedule']['start']['month']) ? $general['campaign_schedule']['start']['month'] : 0;
		$campaign_schedule_start_day = isset($general['campaign_schedule']['start']['day']) ? $general['campaign_schedule']['start']['day'] : 0;
		$campaign_schedule_start_hour = isset($general['campaign_schedule']['start']['hour']) ? $general['campaign_schedule']['start']['hour'] : 0;
		$campaign_schedule_start_min = isset($general['campaign_schedule']['start']['min']) ? $general['campaign_schedule']['start']['min'] : 0;

		$campaign_schedule_end_year = isset($general['campaign_schedule']['end']['year']) ? $general['campaign_schedule']['end']['year'] : 0;
		$campaign_schedule_end_month = isset($general['campaign_schedule']['end']['month']) ? $general['campaign_schedule']['end']['month'] : 0;
		$campaign_schedule_end_day = isset($general['campaign_schedule']['end']['day']) ? $general['campaign_schedule']['end']['day'] : 0;
		$campaign_schedule_end_hour = isset($general['campaign_schedule']['end']['hour']) ? $general['campaign_schedule']['end']['hour'] : 0;
		$campaign_schedule_end_min = isset($general['campaign_schedule']['end']['min']) ? $general['campaign_schedule']['end']['min'] : 0;

		$common_opts = isset($general['_iopt_']) ? $general['_iopt_'] : '';
		$display_mode_label = wc2_get_option( 'display_mode_label' );

		$indi_item_name = $general['indi_item_name'];
		$pos_item_name = $general['pos_item_name'];
		foreach( (array)$indi_item_name as $key => $value){
			$checked_item_name[$key] = $indi_item_name[$key] == 1 ? ' checked="checked"' : ''; 
		}

		if( !empty($cart_description) ){
			$cart_page_data = stripslashes_deep($cart_description);
		}else{
			$cart_page_data['cart_header'] = array();
			$cart_page_data['cart_footer'] = array();
		}

		if( !empty($member_description) ){
			$member_page_data = stripslashes_deep($member_description);
		}else{
			$member_page_data['member_header'] = array();
			$member_page_data['member_footer'] = array();
		}

		require_once( WC2_PLUGIN_DIR . '/admin/views/setting-general.php' );
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
	$(document).on( "change", ".num", function(){ check_num($(this)); });

	$("#option_form").submit(function(e) {
		var error = 0;

		if( "" == $("*[name='order_mail']").val() ) {
			error++;
			$("*[name='order_mail']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( "" == $("*[name='inquiry_mail']").val() ) {
			error++;
			$("*[name='inquiry_mail']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( "" == $("*[name='sender_mail']").val() ) {
			error++;
			$("*[name='sender_mail']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( "" == $("*[name='error_mail']").val() ) {
			error++;
			$("*[name='error_mail']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !WC2Util.checkNum( $("*[name='point_num']").val() ) ) {
			error++;
			$("*[name='point_num']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !WC2Util.checkNum( $("*[name='discount_num']").val() ) ) {
			error++;
			$("*[name='discount_num']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !WC2Util.checkNum( $("*[name='postage_privilege']").val() ) ) {
			error++;
			$("*[name='postage_privilege']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !WC2Util.checkNum( $("*[name='purchase_limit']").val() ) ) {
			error++;
			$("*[name='purchase_limit']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !WC2Util.checkNum( $("*[name='tax_rate']").val() ) ) {
			error++;
			$("*[name='tax_rate']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !WC2Util.checkNum( $("*[name='point_rate']").val() ) ) {
			error++;
			$("*[name='point_rate']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !WC2Util.checkNum( $("*[name='start_point']").val() ) ) {
			error++;
			$("*[name='start_point']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}

		if( !WC2Util.checkNum( $('#member_pass_rule_min').val() ) || $('#member_pass_rule_min').val() == false ) {
			error++;
			$("*[name='member_pass_rule_min']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !WC2Util.checkNum( $('#member_pass_rule_max').val() ) ) {
			error++;
			$("*[name='member_pass_rule_max']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}

		if( !WC2Util.checkNum( $("*[name='position[item_name]']").val() ) ) {
			error++;
			$("*[name='position[item_name]']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !WC2Util.checkNum( $("*[name='position[item_code]']").val() ) ) {
			error++;
			$("*[name='position[item_code]']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !WC2Util.checkNum( $("*[name='position[sku_name]']").val() ) ) {
			error++;
			$("*[name='position[sku_name]']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !WC2Util.checkNum( $("*[name='position[sku_code]']").val() ) ) {
			error++;
			$("*[name='position[sku_code]']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}

		if( 0 < error ) {
			$("#aniboxStatus").attr("class", "error");
			$("#info_image").attr("src", WC2L10n.error_info);
			$("#info_message").html("<?php _e('データに不備があります。','wc2'); ?>");
			$("#anibox").animate({ backgroundColor: "#FFE6E6" }, 2000);
			return false;
		} else {
			return true;
		}
	});

	$(".cangeBus").click(function(){
		var fix = $(this).attr("id").slice(8);
		var calendar = $("#calendar" + fix);
		var cal = $("#cal" + fix);
		if( calendar.val() == '0'){
			calendar.val('1');
			cal.css({"background-color":"#DFFFDD", "color":"#555555", "font-weight":"normal"});
		}else{
			calendar.val('0');
			cal.css({"background-color":"#FFAA55", "color":"#FFFFFF", "font-weight":"bold"});
		}
	});

	$(".cangeWday").click(function(){
	<?php $enc_cal = json_encode($this->cal); ?>
		var enc_cal = <?php echo $enc_cal; ?>;
		var th_fix = $(this).attr("id").slice(9);
		var keys = th_fix.split('_');
		for(var i=0; i<enc_cal[keys[0]]._row; i++ ){
			var fix = keys[0] +"_"+ (i+1) +"_"+ keys[1];
			var calendar = $("#calendar"+ fix);
			var cal = $("#cal" + fix);
			if( calendar.val() == '0'){
				calendar.val('1');
				cal.css({"background-color":"#DFFFDD", "color":"#555555", "font-weight":"normal"});
			}else{
				calendar.val('0');
				cal.css({"background-color":"#FFAA55", "color":"#FFFFFF", "font-weight":"bold"});
			}
		}
	});

	function check_num( obj ) {
		if( !WC2Util.checkNum( obj.val()) ) {
			alert('数値で入力してください。');
			obj.focus();
			return false;
		}
		return true;
	}

	if( $.fn.jquery < "1.10" ) {
		var $tabs = $('#general-tabs').tabs({
			cookie: {
				expires: 1
			}
		});
	} else {
		$( "#general-tabs" ).tabs({
			active: ($.cookie("general-tabs")) ? $.cookie("general-tabs") : 0
			, activate: function( event, ui ){
				$.cookie("general-tabs", $(this).tabs("option", "active"));
			}
		});
	}
<?php do_action( 'wc2_action_admin_general_scripts' ); ?>
});
</script>
<?php
	}
}



