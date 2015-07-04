<?php

class WC2_Page {

	var $page_type, $templates;
	var $current_page_type;
	var $current_page;
	public $error;

	public function __construct() {
		$this->error = new WP_Error();
		$dir_cart = get_stylesheet_directory().'/wc2_templates/cart';
		$dir_member = get_stylesheet_directory().'/wc2_templates/member';

		$this->page_type = array(
			'cart' => 'cart', 
			'member' => 'member', 
			'inquiry' => 'inquiry', 
			'search' => 'search', 
		);
		$this->templates = array(
			$this->page_type['cart'] => array(
				'top' =>            $dir_cart.'/wc2_cart_page.php', 
				'customer' =>       $dir_cart.'/wc2_customer_page.php', 
				'delivery' =>       $dir_cart.'/wc2_delivery_page.php', 
				'confirm' =>        $dir_cart.'/wc2_confirm_page.php', 
				'complete' =>       $dir_cart.'/wc2_completion_page.php', 
				'error' =>          $dir_cart.'/wc2_cart_error_page.php', 
			),
			$this->page_type['member'] => array(
				'login' =>          $dir_member.'/wc2_login_page.php',
				'logout' =>         $dir_member.'/wc2_member_completion_page.php',
				'memberform' =>     $dir_member.'/wc2_member_page.php',
				'newmemberform' =>  $dir_member.'/wc2_new_member_page.php',
				'lostpassword' =>   $dir_member.'/wc2_lostpassword_page.php',
				'changepassword' => $dir_member.'/wc2_changepassword_page.php',
				'newcomplete' =>    $dir_member.'/wc2_member_completion_page.php',
				'editcomplete' =>   $dir_member.'/wc2_member_completion_page.php',
				'lostcomplete' =>   $dir_member.'/wc2_member_completion_page.php',
				'changecomplete' => $dir_member.'/wc2_member_completion_page.php',
				'error' =>          $dir_member.'/wc2_member_error_page.php',
			),
			$this->page_type['inquiry'] => array(
				'top' =>            get_stylesheet_directory().'/inquiry.php', 
			),
			$this->page_type['search'] => array(
				'top' =>            get_stylesheet_directory().'/wc2_templates/wc2_search_page.php', 
			),
		);
		$this->templates = apply_filters( 'wc2_page_templates', $this->templates );

		add_filter( 'query_vars', array( $this, 'queryvars' ) );
		add_action( 'init', array( $this, 'flush_rewrite_rules' ) );
		add_action( 'init', array($this, 'analytics_tracking') );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts') );
		add_action( 'generate_rewrite_rules', array( $this, 'add_rewrite_rules' ) );
		add_action( 'wp_print_footer_scripts', array( $this, 'page_js' ) );
		add_action( 'wp_print_footer_scripts', array( $this, 'cart_js' ) );
		//add_action( 'wp_footer', array( $this, 'clear_error' ) ); 
		add_action( 'wp_footer', array($this, 'page_footer') );
	}

	public function queryvars( $qvars ) {
		if( empty($this->templates) )
			return;

		foreach( $this->templates as $page_type => $array ) {
			$qvars[] = $page_type;
		}
		return $qvars;
	}

	public function template_redirect() {
		global $wp_query;

		foreach( $this->page_type as $page_type ) {
			if( isset( $_GET[$page_type] ) ) {
				$slug = $_GET[$page_type];
				if( is_array($slug) )
					return;
				if( !array_key_exists( $slug, $this->templates[$page_type] ) )
					return;

				if( empty($this->current_page_type) ) $this->current_page_type = $page_type;
				if( empty($this->current_page) ) $this->current_page = $slug;

				add_filter( 'wp_title', array( $this, 'wc2_title' ), 10, 3 );
				add_filter( 'edit_post_link', create_function( '', 'return;' ) );

				if( file_exists( $this->templates[$this->current_page_type][$this->current_page] ) ) {
					$entry_data = wc2_get_entry();
					if( 'member' == $this->current_page_type ){
						if( wc2_is_login() ){
							//DBから会員データ取得
							if( !array_key_exists('error', $_SESSION[WC2]) ){
								$member_id = wc2_memberinfo( 'ID' );
								$member = wc2_get_member_data( $member_id );

								//SET SESSION
								wc2_set_session_current_member( $member );
							}
						}
						$member_data = wc2_get_member();
					}
					do_action( 'wc2_action_before_template_include', $this->current_page_type, $this->current_page, $this->templates );

					if( 'member' == $this->current_page_type && 'memberform' == $this->current_page && !wc2_is_login() ){
						$this->set_error( __('ログインしてください。', 'wc2'), 'purchase_process' );
						$this->current_page_type = 'member';
						$this->current_page = 'login';
						$this->page_redirect();
					}
					include( $this->templates[$this->current_page_type][$this->current_page] );
					exit;

				} else {
					$wp_query->is_home = NULL;
					$wp_query->is_page = 1;
					$wp_query->posts = array( $wp_query->post );
					$wp_query->post_count = 1;
					return;
				}
			}
		}
	}

	public function enqueue_scripts(){
		global $wp_scripts;
		wp_enqueue_script( 'enqueue-public-scripts', WC2_PLUGIN_URL.'/public/assets/js/public.js' );
		if( 'cart' == $this->current_page_type || 'member' == $this->current_page_type ){
			wp_enqueue_script( 'ajaxzip3', 'http://ajaxzip3.googlecode.com/svn/trunk/ajaxzip3/ajaxzip3.js' );
			$ui = $wp_scripts->query( 'jquery-ui-core' );
			//$url = "//ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/redmond/jquery-ui.min.css";
			$url = "//ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
			wp_enqueue_style( 'jquery-ui-smoothness', $url, false, null );
			wp_enqueue_script( 'jquery-ui-dialog' );
/*			wp_enqueue_script( 'jquery-ui-draggable' );
			wp_enqueue_script( 'jquery-ui-resizable' );
			wp_enqueue_script( 'jquery-effects-drop' );
			wp_enqueue_script( 'jquery-ui-position' );
*/
		}
	}

	public function wc2_title( $title, $sep, $seplocation ) {

		$page = $this->current_page_type . '/' . $this->current_page;

		switch( $page ) {
			case $this->page_type['cart'].'/top':
				$newtitle = 'Cart Page' . ' ' . $sep . ' ' . $title;
				break;
			case $this->page_type['cart'].'/customer':
				$newtitle = apply_filters( 'wc2_filter_title_customer', __('Customer Information', 'wc2') );
				break;
			case $this->page_type['cart'].'/delivery':
				$newtitle = apply_filters( 'wc2_filter_title_delivery', __('Shipping / Payment options', 'wc2') );
				break;
			case $this->page_type['cart'].'/confirm':
				$newtitle = apply_filters( 'wc2_filter_title_confirm', __('Confirmation', 'wc2') );
				break;
			case $this->page_type['cart'].'/complete':
				$newtitle = apply_filters( 'wc2_filter_title_ordercompletion', __('Completion', 'wc2') );
				break;
			case $this->page_type['cart'].'/error':
				$newtitle = apply_filters( 'wc2_filter_title_carterror', __('Error', 'wc2') );
				break;
			case $this->page_type['member'].'/login':
				$newtitle = apply_filters( 'wc2_filter_title_member_login', __('会員ログイン', 'wc2') );
				break;
			case $this->page_type['member'].'/logout':
				$newtitle = apply_filters( 'wc2_filter_title_member_logout', __('会員ログアウト', 'wc2') );
				break;
			case $this->page_type['member'].'/memberform':
				$newtitle = apply_filters( 'wc2_filter_title_member_memberform', __('会員ページ', 'wc2') );
				break;
			case $this->page_type['member'].'/newmemberform':
				$newtitle = apply_filters( 'wc2_filter_title_member_newmemberform', __('新規入会フォーム', 'wc2') );
				break;
			case $this->page_type['member'].'/lostpassword':
				$newtitle = apply_filters( 'wc2_filter_title_member_lostpassword', __('新パスワード取得', 'wc2') );
				break;
			case $this->page_type['member'].'/changepassword':
				$newtitle = apply_filters( 'wc2_filter_title_member_changepassword', __('パスワード変更', 'wc2') );
				break;
			case $this->page_type['member'].'/newcomplete':
				$newtitle = apply_filters( 'wc2_filter_title_member_newcomplete', __('会員登録完了', 'wc2') );
				break;
			case $this->page_type['member'].'/editcomplete':
				$newtitle = apply_filters( 'wc2_filter_title_member_editcomplete', __('会員情報編集完了', 'wc2') );
				break;
			case $this->page_type['member'].'/lostcomplete':
				$newtitle = apply_filters( 'wc2_filter_title_member_lostcomplete', __('パスワード再取得メール送信完了', 'wc2') );
				break;
			case $this->page_type['member'].'/changecomplete':
				$newtitle = apply_filters( 'wc2_filter_title_member_changecomplete', __('パスワード更新完了', 'wc2') );
				break;
			case $this->page_type['member'].'/error':
				$newtitle = apply_filters( 'wc2_filter_title_member_error', __('Error', 'wc2') );
				break;
			default:
				$newtitle = apply_filters( 'wc2_filter_title', $title, $sep, $seplocation, $page );
		}
		return $newtitle;
	}

	public function add_rewrite_rules( $wp_rewrite ) {
		if( empty($this->templates) )
			return;

		$wc_rules = array();
		foreach( $this->templates as $page_type => $template ) {
			foreach( $template as $slug => $path ) {
				$key = $page_type . '/' . $slug . '$';
				$value = 'index.php?' . $page_type . '=' . $slug;
				$wc_rules[$key] = $value;
			}
		}
		$wc_rules = apply_filters( 'wc2_rewrite_rules', $wc_rules, $this->templates );
		$wp_rewrite->rules = $wc_rules + $wp_rewrite->rules;
	}

	public function flush_rewrite_rules() {
	   global $wp_rewrite;
	   $wp_rewrite->flush_rules();
	}

	public function analytics_tracking(){
		add_filter('yoast-ga-push-array-universal', 'wc2_Universal_trackPageview_by_Yoast');
		add_filter('yoast-ga-push-array-ga-js', 'wc2_Classic_trackPageview_by_Yoast');
	}

	public function member_login() {
		$res = wc2_member_login_process();
		if( !$res ){
			$error_mess = wc2_get_member_error();
			if( $error_mess ){
				foreach( $error_mess as $error ){
					$this->set_error( $error, 'login' );
				}
			}
			$this->current_page_type = 'member';
			$this->current_page = 'login';
			$this->page_redirect();
		}
	}

	public function member_logout() {
		$logout = wc2_member_logout_process();
		return $logout;
	}

	public function register_member() {
		$nonce = ( isset($_POST['wc2_nonce']) ) ? $_POST['wc2_nonce'] : '0';
		if( !wp_verify_nonce( $nonce, 'wc2_register_member' ) ) {
			$this->set_error( __( 'Security error.' ), 'register_member' );
			$this->current_page_type = 'member';
			$this->current_page = 'error';
			$this->page_redirect();
		}

		$wc2_member_front = WC2_Member_Front::get_instance();
		//$wc2_member_front->set_session_register_member_check_pre();

		$error_mess = wc2_member_check('member');
		if( array() == $error_mess ){
			$res = wc2_new_member_data();
			if( 1 === $res ){
				//登録完了メール送信
				$user = $_POST['member'];
				$wc2_db_member = WC2_DB_Member::get_instance();
				$user['ID'] = $wc2_db_member->get_member_id();
				wc2_send_regmembermail($user);
			}else{
				$this->set_error( __('登録に失敗しました。', 'wc2'), 'register_member' );
				$this->current_page_type = 'member';
				$this->current_page = 'newmemberform';
				$this->page_redirect();
			}
		}else{
			//SET SESSION
			$wc2_member_front->set_session_member_from_post_data();
			foreach($error_mess as $mess){
				$this->set_error( $mess, 'register_member' );
			}
			$this->current_page_type = 'member';
			$this->current_page = 'newmemberform';
			$this->page_redirect();
		}
	}

	public function update_member() {
		$nonce = ( isset($_POST['wc2_nonce']) ) ? $_POST['wc2_nonce'] : '0';
		if( !wp_verify_nonce( $nonce, 'wc2_member' ) ) {
			$this->set_error( __( 'Security error.' ), 'update_member' );
			$this->current_page_type = 'member';
			$this->current_page = 'error';
			$this->page_redirect();
		}

		$wc2_db_member = WC2_DB_Member::get_instance();
		$wc2_member_front = WC2_Member_Front::get_instance();

		$mem_id = wc2_memberinfo( 'ID' );
		$error_mess = wc2_member_check('member', $mem_id);

		if( array() == $error_mess ){
			$res = wc2_edit_member_data($mem_id);

			if( 1 === $res || 0 === $res ){
				//SESSION再セット
				//$member = $wc2_db_member->get_member_data($mem_id);
				//$wc2_member_front->set_session_current_member($member);
				//更新完了メール
				$user = $_POST['member'];
				$user['ID'] = $mem_id;
				wc2_send_editmembermail($user);
			}else{
				$this->set_error( __( 'Update Failed' ), 'update_member' );
				$this->current_page_type = 'member';
				$this->current_page = 'memberform';
				$this->page_redirect();
			}
		}else{
			//SET SESSION
			$wc2_member_front->set_session_member_from_post_data();

			foreach($error_mess as $mess){
				$this->set_error( $mess, 'update_member');
			}
			$this->current_page_type = 'member';
			$this->current_page = 'memberform';
			$this->page_redirect();
		}
	}

	public function delete_member() {
		$nonce = ( isset($_POST['wc2_nonce']) ) ? $_POST['wc2_nonce'] : '0';
		if( !wp_verify_nonce( $nonce, 'wc2_member' ) ) {
			$this->set_error( __( 'Security error.' ), 'delete_member' );
			$this->current_page_type = 'member';
			$this->current_page = 'error';
			$this->page_redirect();
		}

		$mem_id = wc2_memberinfo('ID');
		if( !$mem_id ){
			$this->set_error( __('削除に失敗しました。', 'wc2'), 'delete_member' );
			$this->current_page_type = 'member';
			$this->current_page = 'memberform';
			$this->page_redirect();
		}else{
			$user = wc2_get_member_data($mem_id);
			$res = wc2_delete_member_data($mem_id);
			if( $res === 1 ){
				//削除メール送信
				wc2_send_delmembermail($user);
				//セッション削除
				$this->member_logout();
			}else{
				$this->set_error( __('削除に失敗しました。', 'wc2'), 'delete_member' );
				$this->current_page_type = 'member';
				$this->current_page = 'memberform';
				$this->page_redirect();
			}
		}
	}

	public function change_password() {
		$nonce = ( isset($_POST['wc2_nonce']) ) ? $_POST['wc2_nonce'] : '0';
		if( !wp_verify_nonce( $nonce, 'wc2_change_password' ) ) {
			$this->set_error( __( 'Security error.' ), 'change_password' );
			$this->current_page_type = 'member';
			$this->current_page = 'error';
			$this->page_redirect();
		}

		$mem = ( isset($_REQUEST['mem']) ) ? $_REQUEST['mem'] : '';
		$key = ( isset($_REQUEST['key']) ) ? $_REQUEST['key'] : '';
		if( $mem == '' || $key == '' ) {
			//die('Invalid request 2');
			$this->set_error( '不正なパラメータです。', 'change_password' );
			$this->current_page_type = 'member';
			$this->current_page = 'error';
			$this->page_redirect();
		} else {
			$lostmail = trim(urldecode($mem));
			$lostkey = trim(urldecode($key));
			//$res1 = $wc2_db_member->check_lostkey($lostmail, $lostkey); //lostkeyチェック
			$res1 = wc2_check_lostkey($lostmail, $lostkey); //lostkeyチェック
			if( empty($res1) ){
				//die('Invalid request 3');
				$this->set_error( '不正なパラメータです。', 'change_password' );
				$this->current_page_type = 'member';
				$this->current_page = 'error';
				$this->page_redirect();
			} else {
				//$limit_check = $wc2_db_member->check_lostlimit($lostmail, $lostkey);
				$limit_check = wc2_check_lostlimit($lostmail, $lostkey);
				if( false === $limit_check){
					$this->set_error( 'パスワード変更までの制限時間を過ぎました。再度手続きを行ってください。', 'change_password' );
					$this->current_page_type = 'member';
					$this->current_page = 'error';
					$this->page_redirect();
				} else {
					if( isset($_POST['new_password1']) ) {
						$error_message = wc2_changepass_check(); //パスワード入力チェック
						if ( !empty($error_message) ) {
							foreach($error_message as $mess){
								$this->set_error( $mess, 'change_password' );
							}
							$this->current_page_type = 'member';
							$this->current_page = 'changepassword';
							$this->page_redirect();
						} else {
							$newpass = trim($_POST['new_password1']);
							$wc2_db_member = WC2_DB_Member::get_instance();
							$res2 = $wc2_db_member->changepassword($lostmail, $newpass);
							if( false === $res2 ){
								$this->set_error( 'パスワードの更新に失敗しました。', 'change_password' );
								$this->current_page_type = 'member';
								$this->current_page = 'changepassword';
								$this->page_redirect();
							}else{
								//$wc2_db_member->remove_lost_mail_key( $lostmail, $lostkey );
								wc2_remove_lost_mail_key( $lostmail, $lostkey );
							}
						}
					}
				}
			}
		}
	}

	public function lost_password() {
		$nonce = ( isset($_POST['wc2_nonce']) ) ? $_POST['wc2_nonce'] : '0';
		if( !wp_verify_nonce( $nonce, 'wc2_lost_password' ) ) {
			$this->set_error( __( 'Security error.' ), 'lost_password' );
			$this->current_page_type = 'member';
			$this->current_page = 'error';
			$this->page_redirect();
		}

		$error_mess = wc2_lostpass_mailaddcheck();
		if( !empty($error_mess) ){
			$this->set_error( $error_mess ,'lost_password' );
			$this->current_page_type = 'member';
			$this->current_page = 'lostpassword';
			$this->page_redirect();
		}else{
			$res = wc2_lostpass_process();
			if( $res == false ){
				$this->set_error( __('メール送信に失敗しました。', 'wc2'), 'lost_password' );
				$this->current_page_type = 'member';
				$this->current_page = 'lostpassword';
				$this->page_redirect();
			}
		}
	}

	public function is_member_logged_in() {
		$login = false;
		if( array_key_exists( 'member', $_SESSION[WC2] ) and !empty($_SESSION[WC2]['member']['ID']) ) {
			$login = true;
		}
		return $login;
	}

	/**
	 * Add to cart
	 *
	 */
	public function add2cart() {
		wc2_add2cart();
	}

	/**
	 * Cart quantity update
	 *
	 */
	public function update_cart() {
		$mes = $this->update_cart_check();
		if( 0 < count($mes) ) {
			foreach( $mes as $error )
				$this->set_error( $error, 'update_cart' );
			$this->current_page_type = 'cart';
			$this->current_page = 'top';
			$this->page_redirect();
		}
		wc2_update_cart();
	}

	/**
	 * Cart update check
	 *
	 */
	function update_cart_check() {
		$mes = array();
		$cart = wc2_get_cart();
		$mes = apply_filters( 'wc2_filter_update_cart_check', $mes, $cart );
		return $mes;
	}

	/**
	 * Remove cart
	 *
	 */
	public function remove_cart() {
		wc2_remove_cart();
	}

	/**
	 * Checkout
	 *
	 */
	public function checkout() {
		$mes = $this->checkout_check();
		if( 0 < count($mes) ) {
			foreach( $mes as $error )
				$this->set_error( $error, 'checkout' );
			$this->current_page_type = 'cart';
			$this->current_page = 'top';
			$this->page_redirect();
		}

		if( $this->is_member_logged_in() ) {
			$member_id = wc2_memberinfo( 'ID' );
			$member = wc2_get_member_data( $member_id );
			//SET SESSION
			wc2_set_session_current_member( $member );
		}
	//	wc2_clear_entry();
		wc2_set_entry();
		$mes = $this->stock_check();
		if( 0 < count($mes) ) {
			foreach( $mes as $error )
				$this->set_error( $error, 'checkout' );
			$this->current_page_type = 'cart';
			$this->current_page = 'top';
			$this->page_redirect();
		}

		if( $this->is_member_logged_in() ) {
			$this->current_page_type = 'cart';
			$this->current_page = 'delivery';
			$this->page_redirect();
		}
	}

	/**
	 * Checkout check
	 *
	 */
	function checkout_check() {
		$mes = array();
		$cart = wc2_get_cart();
		$mes = apply_filters( 'wc2_filter_checkout_check', $mes, $cart );
		return $mes;
	}

	/**
	 * Customer member check
	 *
	 */
	public function customer_login() {
		$nonce = ( isset($_POST['wc2_nonce']) ) ? $_POST['wc2_nonce'] : '0';
		if( !wp_verify_nonce( $nonce, 'wc2_customer_login' ) ) {
			$this->set_error( __( 'Security error.' ), 'customer_login' );
			$this->current_page_type = 'cart';
			$this->current_page = 'error';
			$this->page_redirect();
		}

		$res = wc2_member_login_process();
		if( !$res ) {
			$error_mess = wc2_get_member_error();
			if( $error_mess ){
				foreach( $error_mess as $error ){
					$this->set_error( $error, 'customer_login' );
				}
			}
			$this->current_page_type = 'cart';
			$this->current_page = 'customer';
			$this->page_redirect();
		}
		wc2_set_entry();
	}

	/**
	 * Customer information check
	 *
	 */
	public function customer_process() {
		$nonce = ( isset($_POST['wc2_nonce']) ) ? $_POST['wc2_nonce'] : '0';
		if( !wp_verify_nonce( $nonce, 'wc2_customer' ) ) {
			$this->set_error( __( 'Security error.' ), 'customer_process' );
			$this->current_page_type = 'cart';
			$this->current_page = 'error';
			$this->page_redirect();
		}

		wc2_set_entry();
		if( isset($_POST['member_regmode']) and $_POST['member_regmode'] == 'newmemberfromcart' ) {
			$mes = wc2_member_check('customer');
			if( 0 < count($mes) ) {
				foreach($mes as $error){
					$this->set_error( $error, 'customer_process' );
				}
				$this->current_page_type = 'cart';
				$this->current_page = 'customer';
				$this->page_redirect();
			}
			$res = wc2_new_member_data( 'customer' );
			if( 1 === $res ) {
				$wc2_db_member = WC2_DB_Member::get_instance();
				$mem_id = $wc2_db_member->get_member_id();
				$member = $wc2_db_member->get_member_data($mem_id);
				//SET SESSION
				wc2_set_session_current_member($member);
				wc2_set_entry_member_regmode('editmemberfromcart');
				//登録完了メール送信
				$user = $_POST['customer'];
				$user['ID'] = $mem_id;
				wc2_send_regmembermail($user);
			}else{
				$this->set_error( __('登録に失敗しました。', 'wc2'), 'newmemberfromcart' );
				$this->current_page_type = 'cart';
				$this->current_page = 'customer';
				$this->page_redirect();
			}
		}elseif( isset($_POST['member_regmode']) and $_POST['member_regmode'] == 'editmemberfromcart' ){
			$mem_id = wc2_memberinfo('ID');
			$mes = wc2_member_check('customer', $mem_id);
			if( 0 < count($mes) ) {
				foreach($mes as $error){
					$this->set_error( $error, 'customer_process' );
				}
				$this->current_page_type = 'cart';
				$this->current_page = 'customer';
				$this->page_redirect();
			}
			$res = wc2_edit_member_data($mem_id, 'customer');
			if( 1 === $res ) {
				$wc2_db_member = WC2_DB_Member::get_instance();
				$mem_id = $wc2_db_member->get_member_id();
				$member = $wc2_db_member->get_member_data($mem_id);
				//SET SESSION
				wc2_set_session_current_member($member);
				wc2_set_entry_member_regmode('editmemberfromcart');
				//登録完了メール送信
				$user = $_POST['customer'];
				$user['ID'] = $mem_id;
				wc2_send_regmembermail($user);
			}else{
				$this->set_error( __('登録に失敗しました。', 'wc2'), 'newmemberfromcart' );
				$this->current_page_type = 'cart';
				$this->current_page = 'customer';
				$this->page_redirect();
			}
		}else{
			$mes = $this->customer_check();
			if( 0 < count($mes) ) {
				foreach( $mes as $error )
					$this->set_error( $error, 'customer_process' );
				$this->current_page_type = 'cart';
				$this->current_page = 'customer';
				$this->page_redirect();
			}
		}
	}

	/**
	 * Shipping destination check
	 *
	 */
	public function delivery_process() {
		wc2_set_entry();
		$mes = $this->stock_check();
		if( 0 < count($mes) ) {
			foreach( $mes as $error )
				$this->set_error( $error, 'delivery_process' );
			$this->current_page_type = 'cart';
			$this->current_page = 'top';
			$this->page_redirect();
		}

		$mes = $this->delivery_check();
		if( 0 < count($mes) ) {
			foreach( $mes as $error )
				$this->set_error( $error, 'delivery_process' );
			$this->current_page_type = 'cart';
			$this->current_page = 'delivery';
			$this->page_redirect();
		}

		wc2_set_order_price();

		if( wc2_is_membersystem_state() && wc2_is_membersystem_point() && $this->is_member_logged_in() ) {
			$mem_point = wc2_get_member_data_value( $_SESSION[WC2]['member']['ID'], MEMBER_POINT );
			$_SESSION[WC2]['member']['point'] = $mem_point;
		}
	}

	/**
	 * Purchase check
	 *
	 */
	public function purchase_process() {
		$nonce = ( isset($_POST['wc2_nonce']) ) ? $_POST['wc2_nonce'] : '0';
		if( !wp_verify_nonce( $nonce, 'wc2_purchase' ) ) {
			$this->set_error( __( 'Security error.' ), 'purchase_process' );
			$this->current_page_type = 'cart';
			$this->current_page = 'error';
			$this->page_redirect();
		}

		//wc2_set_entry();
		$mes = $this->stock_check();
		if( 0 < count($mes) ) {
			foreach( $mes as $error )
				$this->set_error( $error, 'purchase_process' );
			$this->current_page_type = 'cart';
			$this->current_page = 'top';
			$this->page_redirect();
		}

		do_action( 'wc2_purchase_process' );

		$res = $this->order_processing();
		if( !$res ) {
			$this->current_page_type = 'cart';
			$this->current_page = 'error';
			$this->page_redirect();
		}

		//wc2_clear_cart();		page_footer()へ移動
		//wc2_clear_entry();
	}

	function order_processing() {

		do_action( 'wc2_register_order_data_pre' );

		$mes = array();
//		$mes = wc2_check_acting_return_duplicate( $results );
//		if( 0 < count($mes) ) {
//
//		}

		$order_id = wc2_register_order_data();
		do_action( 'wc2_register_order_data', $order_id );

		wc2_set_entry_order_value( 'ID', $order_id );

		if( $order_id ) {
			wc2_send_ordermail( $order_id );
			return true;

		} else {
			return false;
		}
	}

	/**
	 * Stock check
	 *
	 */
	function stock_check() {
		$mes = array();
		$stocks = array();
		$cart = wc2_get_cart();

		foreach( $cart as $idx => $cart_row ) {
			$item_id = $cart_row['item_id'];
			$sku_id = $cart_row['sku_id'];
			$item_sku_data = wc2_get_item_sku_data( $item_id, $sku_id );

			$quantity = ( isset($_POST['quantity'][$idx]) ) ? $_POST['quantity'][$idx] : $cart_row['quantity'];
			$stock_status = $item_sku_data['stock_status'];
			$stock = $item_sku_data['sku_stock'];
			if( !isset($stocks[$item_id][$sku_id]) ) {
				if( !WC2_Utils::is_blank($stock) ) {
					$stocks[$item_id][$sku_id] = $stock;
				} else {
					$stocks[$item_id][$sku_id] = NULL;
				}
			}
			$checkstock = $stocks[$item_id][$sku_id];
			$stocks[$item_id][$sku_id] = $stocks[$item_id][$sku_id] - $quantity;
			$purchase_limit = ( isset( $item_sku_data['item_purchase_limit'] ) ) ? (int)$item_sku_data['item_purchase_limit'] : 0;

			if( 1 > (int)$quantity ) {
				$mes[] = sprintf(__("%d番の商品の数量を正しく入力してください。", 'wc2'), $idx);
			} elseif( 1 < $stock_status || WC2_Utils::is_zero($stock) ) {
				$mes[] = sprintf(__("申し訳ありません。%d番の商品は売り切れました。", 'wc2'), $idx);
			} elseif( $quantity > $purchase_limit && !WC2_Utils::is_blank($purchase_limit) && !WC2_Utils::is_zero($purchase_limit) ) {
				$mes[] = sprintf(__("%2$d番の商品は一度に %1$d までの数量制限があります。", 'wc2'), $purchase_limit, $idx);
			} elseif( 0 > $stocks[$item_id][$sku_id] && !WC2_Utils::is_blank($stock) ) {
				$mes[] = sprintf(__("%1$d番の商品の在庫は残り %2$d です。", 'wc2'), $idx, $checkstock);
			}
		}
		$mes = apply_filters( 'wc2_filter_stock_check', $mes, $cart );
		return $mes;
	}

	/**
	 * Customer input check
	 *
	 */
	public function customer_check() {
		$mes = array();
		//メールアドレス
		if( !is_email($_POST['customer']['email']) || 
			WC2_Utils::is_blank($_POST['customer']['email']) || 
			WC2_Utils::is_blank($_POST['customer']['email2']) || 
			trim($_POST['customer']['email']) != trim($_POST['customer']['email2']) )
			$mes[] = __('メールアドレスを正しく入力してください。', 'wc2');
		//氏名
		if ( WC2_Utils::is_blank($_POST['customer']['name1']) || WC2_Utils::is_blank($_POST['customer']['name2']) ){
			$mes[] = __('氏名を入力してください。', 'wc2');
		}
		//郵便番号
		if ( WC2_Utils::is_blank($_POST['customer']['zipcode']) ){
			$mes[] = __('郵便番号が入力されていません。', 'wc2');
		}elseif( preg_match('/[^\d-]/', trim($_POST['customer']['zipcode'])) ){
			$mes[] = __('郵便番号は半角数字で入力してください。', 'wc2');
		}
		//都道府県
		if ( WC2_UNSELECTED == ($_POST['customer']['pref']) )
			$mes[] = __('都道府県が選択されていません。', 'wc2');
		//市区町村・番地
		if ( WC2_Utils::is_blank($_POST['customer']['address1']) )
			$mes[] = __('市区町村・番地が入力されていません。', 'wc2');
		//電話番号
		if( WC2_Utils::is_blank($_POST['customer']['tel']) ){
			$mes[] = __('電話番号が入力されていません。', 'wc2');
		}elseif( !WC2_Utils::is_blank($_POST['customer']['tel']) && preg_match('/[^\d]/', trim($_POST['customer']['tel'])) ){
			$mes[] = __('電話番号は半角数字で入力してください。', 'wc2');
		}

		//custom_customer check
		$cscs_mes = wc2_custom_field_enter_check('customer');
		foreach( $cscs_mes as $cscs_mes_val ){
			$mes[] = $cscs_mes_val;
		}

		$mes = apply_filters( 'wc2_filter_customer_check', $mes );
		return $mes;
	}

	/**
	 * Delivery input check
	 *
	 */
	public function delivery_check() {
		$mes = array();
		if( isset($_POST['delivery']['delivery_flag']) && $_POST['delivery']['delivery_flag'] == 1 ) {
			//氏名
			if ( WC2_Utils::is_blank($_POST['delivery']['name1']) || WC2_Utils::is_blank($_POST['delivery']['name2']) ){
				$mes[] = __('氏名を入力してください。', 'wc2');
			}
			//郵便番号
			if ( WC2_Utils::is_blank($_POST['delivery']['zipcode']) ){
				$mes[] = __('郵便番号が入力されていません。', 'wc2');
			}elseif( preg_match('/[^\d-]/', trim($_POST['delivery']['zipcode'])) ){
				$mes[] = __('郵便番号は半角数字で入力してください。', 'wc2');
			}
			//都道府県
			if ( WC2_UNSELECTED == ($_POST['delivery']['pref']) )
				$mes[] = __('都道府県が選択されていません。', 'wc2');
			//市区町村・番地
			if ( WC2_Utils::is_blank($_POST['delivery']['address1']) )
				$mes[] = __('市区町村・番地が入力されていません。', 'wc2');
			//電話番号
			if( WC2_Utils::is_blank($_POST['delivery']['tel']) ){
				$mes[] = __('電話番号が入力されていません。', 'wc2');
			}elseif( !WC2_Utils::is_blank($_POST['delivery']['tel']) && preg_match('/[^\d]/', trim($_POST['delivery']['tel'])) ){
				$mes[] = __('電話番号は半角数字で入力してください。', 'wc2');
			}
		}
		if( !isset($_POST['offer']['delivery_method']) || (empty($_POST['offer']['delivery_method']) && !WC2_Utils::is_zero($_POST['offer']['delivery_method'])) )
			$mes[] = __('配送方法を選択してください。', 'wc2');
		if( !isset($_POST['offer']['payment_method']) ) {
			$mes[] = __('支払方法を選択してください。', 'wc2');
		} else {
			$entry_data = wc2_get_entry();
			$delivery = wc2_get_option( 'delivery' );
			$general = wc2_get_option( 'general' );
			$payment = wc2_get_payment( $_POST['offer']['payment_method'] );
			if( 'COD' == $payment['settlement'] ) {
				$item_total_price = wc2_get_item_total_price();
				$materials = array(
					'total_price' => $item_total_price,
					'discount' => $entry_data['order']['discount'],
					'shipping_charge' => $entry_data['order']['shipping_charge'],
					'cod_fee' => $entry_data['order']['cod_fee'],
				);
				$item_total_price += wc2_get_tax( $materials );
				$cod_limit_amount = ( isset($general['cod_limit_amount']) && 0 < (int)$general['cod_limit_amount'] ) ? $general['cod_limit_amount'] : 0;
				if( 0 < $cod_limit_amount && $item_total_price > $cod_limit_amount )
					$mes[] = sprintf(__('商品合計金額が、代引きでご購入できる上限額（%s）を超えています。', 'wc2'), wc2_crform( $general['cod_limit_amount'], true, false ));
			}

			$d_method_index = wc2_get_delivery_method_index( (int)$_POST['offer']['delivery_method'] );
			if( $delivery['delivery_method'][$d_method_index]['nocod'] == 1 ) {
				if('COD' == $payment['settlement'])
					$mes[] = __('この配送方法では、代引きはご利用できません。', 'wc2');
			}

			$country = $entry_data['delivery']['country'];
			$local_country = wc2_get_base_country();
			if( $country == $local_country ) {
				if( $delivery['delivery_method'][$d_method_index]['intl'] == 1 ) {
					$mes[] = __('配送方法が誤っています。国際便は指定できません。', 'wc2');
				}
			} else {
				if( WC2_Utils::is_zero($delivery['delivery_method'][$d_method_index]['intl']) ) {
					$mes[] = __('配送方法が誤っています。国際便を指定してください。', 'wc2');
				}
			}
		}
	
		//custom_delivery check
		$csde_mes = wc2_custom_field_enter_check('delivery');
		foreach( $csde_mes as $csde_mes_val ){
			$mes[] = $csde_mes_val;
		}

		//custom_order check
		$csod_mes = wc2_custom_field_enter_check('order');
		foreach( $csod_mes as $csod_mes_val ){
			$mes[] = $csod_mes_val;
		}

		$mes = apply_filters( 'wc2_filter_delivery_check', $mes );
		return $mes;
	}

	public function cart_page_next( $now ) {
		if( empty($now) ) {
			$next_page = 'top';
		} elseif( $now == 'top' ) {
			$next_page = 'customer';
		} elseif( $now == 'customer' ) {
			$next_page = 'delivery';
		} elseif( $now == 'delivery' ) {
			$next_page = 'confirm';
		} elseif( $now == 'confirm' ) {
			$next_page = 'complete';
		} elseif( $now == 'point' ) {
			$next_page = 'confirm';
		}
		$next_page = apply_filters('wc2_filter_cart_page_next', $next_page, $now);

		return $next_page;
	}

	public function cart_page_back( $now ) {
		if( $now == 'top' ) {
			$back_page = 'top';
		} elseif( $now == 'customer' ) {
			$back_page = 'top';
		} elseif( $now == 'delivery' ) {
			$back_page = 'customer';
		} elseif( $now == 'confirm' ) {
			$back_page = 'delivery';
		}
		$back_page = apply_filters('wc2_filter_cart_page_back', $back_page, $now);

		return $back_page;
	}

	public function page_js() {
?>
<script type="text/javascript">
jQuery( function($) {
	$("#member-login").click(function() {
		<?php $this->js_member_login_click(); ?>
	});
<?php if( 'login' == $this->current_page ) : ?>
	<?php if( $this->is_member_logged_in() ) : ?>
	setTimeout( function(){ try{
	d = document.getElementById('loginpass');
	d.value = '';
	d.focus();
	} catch(e){}
	}, 200);
	<?php else : ?>
	try{document.getElementById('loginmail').focus();}catch(e){}
	<?php endif; ?>
<?php elseif( 'memberform' == $this->current_page ) : ?>
	$("#gohome").click(function() {
		location.href = "<?php echo home_url( '/' ); ?>";
	});
	$("#member-update").click(function() {
		<?php $this->js_member_update_click(); ?>
	});
	$("#member-delete").click(function() {
		<?php $this->js_member_delete_click(); ?>
	});
<?php elseif( 'newmemberform' == $this->current_page ) : ?>
	$("#member-register").click(function() {
		<?php $this->js_member_register_click(); ?>
	});
<?php elseif( 'lostpassword' == $this->current_page ) : ?>
	$("#member-lostpassword").click(function() {
		<?php $this->js_lost_password_click(); ?>
	});
<?php elseif( 'changepassword' == $this->current_page ) : ?>
	$("#member-changepassword").click(function() {
		<?php $this->js_change_password_click(); ?>
	});
<?php elseif( 'top' == $this->current_page ) : ?>
	$("#go2customer").click(function() {
		<?php $this->js_go2customer_click(); ?>
	});
	$("#bk2shopping").click(function() {
		<?php $this->js_bk2shopping_click(); ?>
	});
<?php elseif( 'customer' == $this->current_page ) : ?>
	$("#customerlogin").click(function() {
		<?php $this->js_customerlogin_click(); ?>
	});
	$("#go2delivery").click(function() {
		<?php $this->js_go2delivery_click(); ?>
	});
	$("#reganddelivery").click(function() {
		<?php $this->js_reganddelivery_click(); ?>
	});
	$("#editanddelivery").click(function() {
		<?php $this->js_editanddelivery_click(); ?>
	});	
	$("#bk2cart").click(function() {
		<?php $this->js_bk2cart_click(); ?>
	});
<?php elseif( 'delivery' == $this->current_page ) :
	require_once( WC2_PLUGIN_DIR . '/public/includes/delivery-page-script.php' );
?>
	$("input[name='delivery[delivery_flag]']:radio").change(function() {
		if( $("#delivery-flag1").is(":checked") ) {
			$("#delivery-table").css("display", "none");
		} else if( $("#delivery-flag2").is(":checked") ) {
			$("#delivery-table").css("display", "table");
		}
	});
	$("#go2confirm").click(function() {
		<?php $this->js_go2confirm_click(); ?>
	});
	$("#bk2customer").click(function() {
		<?php $this->js_bk2customer_click(); ?>
	});
});
jQuery(document).ready(function($) {
	$("input[name='delivery[delivery_flag]']:radio").triggerHandler("change");
<?php elseif( 'confirm' == $this->current_page ) : ?>
	$("#purchase").click(function() {
		<?php $this->js_purchase_click(); ?>
	});
	$("#bk2delivery").click(function() {
		<?php $this->js_bk2delivery_click(); ?>
	});
<?php endif; ?>
<?php if( 'customer' == $this->current_page or 'delivery' == $this->current_page or 'memberform' == $this->current_page or 'newmemberform' == $this->current_page ) : ?>
	$(document).on( "click", ".search-zipcode", function() {
		var idname = $(this).attr("id");
		var ids = idname.split("-");
		var id = ids[2];
		AjaxZip3.zip2addr( id+"[zipcode]", "", id+"[pref]", id+"[address1]" );
	});
<?php endif; ?>
});
//jQuery(document).ready( function($) {
//	$("input[type='text']").keypress(function(ev) {
//		if( (ev.which && ev.which === 13) || (ev.keyCode && ev.keyCode === 13) ) {
//			return false;
//		} else {
//			return true;
//		}
//	});
//});
</script>
<?php
	}

	private function js_member_login_click() {
		$js = '$("#member-form-login").submit();';
		$html = apply_filters( 'wc2_filter_member_login_click', $js );
		echo $html;
	}

	private function js_member_update_click() {
		$js = '
			$("input[name=\'wcaction\']").val(\'update_member\');
			$("#member-form-edit").submit();';
		$html = apply_filters( 'wc2_filter_member_update_click', $js );
		echo $html;
	}

	private function js_member_delete_click() {
		$js = '
			if( !confirm("'.__('会員情報を削除します。よろしいですか？','wc2').'") ) {
				return false;
			}
			$("input[name=\'wcaction\']").val(\'delete_member\');
			$("#member-form-edit").submit();';
		$html = apply_filters( 'wc2_filter_member_delete_click', $js );
		echo $html;
	}

	private function js_member_register_click() {
		$js = '$("#member-form-register").submit();';
		$html = apply_filters( 'wc2_filter_member_register_click', $js );
		echo $html;
	}

	private function js_lost_password_click() {
		$js = '$("#member-form-lostpassword").submit();';
		$html = apply_filters( 'wc2_filter_lost_password_click', $js );
		echo $html;
	}

	private function js_change_password_click() {
		$js = '$("#member-form-changepassword").submit();';
		$html = apply_filters( 'wc2_filter_change_password_click', $js );
		echo $html;
	}

	private function js_go2customer_click() {
		$js = '$("#cart-form-top").submit();';
		$html = apply_filters( 'wc2_filter_go2customer_click', $js );
		echo $html;
	}

	private function js_bk2shopping_click() {
		$referer = ( isset( $_REQUEST['wcreferer'] ) ) ? urldecode($_REQUEST['wcreferer']) : home_url();
		$js = 'location.href = "'.$referer.'";';
		$html = apply_filters( 'wc2_filter_bk2shopping_click', $js );
		echo $html;
	}

	private function js_customerlogin_click() {
		$js = '$("#cart-form-customer-login").submit();';
		$html = apply_filters( 'wc2_filter_customerlogin_click', $js );
		echo $html;
	}

	private function js_go2delivery_click() {
		$js = '$("#cart-form-customer").submit();';
		$html = apply_filters( 'wc2_filter_go2delivery_click', $js );
		echo $html;
	}

	private function js_reganddelivery_click() {
		$js = '
			$("input[name=\'member_regmode\']").val( "newmemberfromcart" );
			$("#cart-form-customer").submit();';
		$html = apply_filters( 'wc2_filter_reganddelivery_click', $js );
		echo $html;
	}

	private function js_editanddelivery_click() {
		$js = '
			$("input[name=\'member_regmode\']").val( "editmemberfromcart" );
			$("#cart-form-customer").submit();';
		$html = apply_filters( 'wc2_filter_editanddelivery_click', $js );
		echo $html;
	}

	private function js_bk2cart_click() {
		$back_page = $this->cart_page_back( 'customer' );
		$cart_url = home_url( '/' ).'?cart='.$back_page;
		$js = 'location.href = "'.$cart_url.'";';
		$html = apply_filters( 'wc2_filter_bk2cart_click', $js );
		echo $html;
	}

	private function js_go2confirm_click() {
		$js = '$("#cart-form-delivery").submit();';
		$html = apply_filters( 'wc2_filter_go2confirm_click', $js );
		echo $html;
	}

	private function js_bk2customer_click() {
		$back_page = $this->cart_page_back( 'delivery' );
		$cart_url = home_url( '/' ).'?cart='.$back_page;
		$js = 'location.href = "'.$cart_url.'";';
		$html = apply_filters( 'wc2_filter_bk2customer_click', $js );
		echo $html;
	}

	private function js_purchase_click() {
		$js = '$("#cart-form-confirm").submit();';
		$html = apply_filters( 'wc2_filter_checkout_click', $js );
		echo $html;
	}

	private function js_bk2delivery_click() {
		$back_page = $this->cart_page_back( 'confirm' );
		$cart_url = home_url( '/' ).'?cart='.$back_page;
		$js = 'location.href = "'.$cart_url.'";';
		$html = apply_filters( 'wc2_filter_bk2delivery_click', $js );
		echo $html;
	}

	public function cart_js() {
		if( $this->current_page_type == 'cart' ) {
			$url = wc2_cart_top_url().'&';
		} else {
			$parse_url = parse_url( $_SERVER['REQUEST_URI'] );
			$url = $parse_url['path'].'?';
		}

		ob_start();
?>
<script type="text/javascript">
jQuery( function($) {
<?php //if( is_singular( 'item' ) or 'top' == $current_page ) : ?>
	cart = {
		numcheck: function( val ) {
			var mes = "";
			if( val == "" || !val.match(/^[0-9]+$/) ) {
				mes += "<?php _e('半角数字で入力してください。', 'wc2'); ?>\n";
			}
			return mes;
		},

		update: function() {
			$('input[name="wcaction"]').val("update_cart");
			$("#cart-form-top").attr( "action", $('input[name="wcreferer"]').val() );
			$("#cart-form-top").submit();
		},

		remove: function( cart_key ) {
			location.href = "<?php echo $url; ?>wcaction=remove_cart&cart_key="+cart_key;
		}
	};

	$(".quantity").blur(function() {
		if( $(this).val() == "" ) return;
		var mes = "";
		mes += cart.numcheck( $(this).val() );
		if( mes != "" ) {
			alert(mes);
			$(this).focus();
		}
	});

<?php //endif; ?>
<?php if( is_singular( 'item' ) ) : ?>
	$(".add2cartbutton").click(function() {
		var idname = $(this).attr("id");
		var ids = idname.split("-");
		var item_id = ids[1];
		var sku_id = ids[2];
		var mes = cart.numcheck( $("#quantity-"+item_id+"-"+sku_id).val() );
		if( mes != "" ) {
			alert(mes);
			$("#quantity-"+item_id+"-"+sku_id).focus();
			return false;
		}
		<?php self::js_add2cart_click(); ?>
	});
<?php endif; ?>
	<?php if( 'top' == $this->current_page ) :
		$cart = wc2_get_cart();
		if( 0 == count($cart) ): ?>
	$("#go2customer").attr("disabled", true);
	<?php else: ?>
	$("#cart-update").click(function() {
		cart.update();
	});
	$(".cart-remove").click(function() {
		var idname = $(this).attr("id");
		var ids = idname.split("-");
		var idx = ids[2];
		cart.remove(idx);
	});
	<?php endif; ?>
<?php endif; ?>
});
</script>
<?php
		$scripts = ob_get_contents();
		ob_end_clean();
		$scripts = apply_filters( 'wc2_filter_cart_scripts', $scripts, $this->current_page_type, $this->current_page, $url );
		echo $scripts;
	}

	private function js_add2cart_click( $submit = true ) {
		if( $submit ) {
			$js = '
			if( $("#quantity-"+item_id+"-"+sku_id).vai() != "" ) {
				$("#item-"+item_id+"-"+sku_id).submit();
			}';
		} else {

		}
		$html = apply_filters( 'wc2_filter_add2cart_click', $js );
		echo $html;
	}

	public function get_error() {
		$error_messages = $this->error->get_error_messages();
		$error = array();
		foreach( (array)$error_messages as $key => $message ) {
			$error[] = $message;
		}
		return $error;
	}

	public function set_error( $message, $status ) {
		$this->error->add( $status, $message );
	}

	public function page_redirect() {
		$_SESSION[WC2]['error'] = $this->error;
		$url = apply_filters( 'wc2_filter_page_redirect_url', home_url( '/?'.$this->current_page_type.'='.$this->current_page ) );
		wp_redirect( $url );
		die();
	}

/*
	public function clear_error() {
		unset( $_SESSION[WC2]['error'] );
	}
*/

	public function page_footer(){
		$action = isset($_REQUEST['wcaction']) ? $_REQUEST['wcaction']: '';

		if( 'purchase_process' == $action ){
			wc2_clear_cart();
			wc2_clear_entry();
		}
		unset( $_SESSION[WC2]['error'] );

		do_action('wc2_action_page_footer', $action);
	}

	function get_current_page_type() {
		return $this->current_page_type;
	}

	function get_current_page() {
		$current_page = ( !empty($this->current_page) ) ? $this->current_page : 'top';
		return $current_page;
	}

	function set_current_page_type( $page_type ) {
		$this->current_page_type = $page_type;
	}

	function set_current_page( $page ) {
		$this->current_page = $page;
	}

	function get_current_slug() {
		global $wp_query;
		$slug = '';
		foreach( $this->page_type as $page_type ) {
			if( isset( $_GET[$page_type] ) ) {
				$slug = $_GET[$page_type];
				break;
			}
		}
		return $slug;
	}
}

function wc2_cart_top_url() {
	$cart_url = home_url( '/?cart=top' );
	return $cart_url;
}

function wc2_cart_top_url_e() {
	echo wc2_cart_top_url();
}

function wc2_cart_url() {
	$wc2_public = WC2_Public::get_instance();
	$slug = $wc2_public->page->get_current_slug();
	$current_page = $wc2_public->page->cart_page_next( $slug );
	$cart_url = home_url( '/?cart=' ).$current_page;

	$cart_url = apply_filters('wc2_filter_cart_url', $cart_url, $current_page, $slug);
	return $cart_url;
}

function wc2_cart_url_e() {
	echo wc2_cart_url();
}

function wc2_is_member_logged_in() {
	//$wc2_public = WC2_Public::get_instance();
	//$login = $wc2_public->page->is_member_logged_in();
	$login = false;
	if( array_key_exists( 'member', $_SESSION[WC2] ) and !empty($_SESSION[WC2]['member']['ID']) ) {
		$login = true;
	}
	return $login;
}

function wc2_member_url( $slug = '' ) {
	if( empty($slug) ) {
		$slug = ( wc2_is_member_logged_in() ) ? 'memberform' : 'login';
	}
	return esc_url( home_url( '/?member=' ).$slug );
}

function wc2_member_url_e( $slug = '' ) {
	echo wc2_member_url( $slug );
}

function wc2_member_login_url_e() {
	$url = ( wc2_is_member_logged_in() ) ? home_url( '/?member=logout&wcaction=logout' ) : home_url( '/?member=login' );
	echo esc_url( $url );
}

function wc2_member_info_url_e() {
	$url = ( wc2_is_member_logged_in() ) ? home_url( '/?member=memberform' ) : home_url( '/?member=newmemberform' );
	$url = apply_filters('wc2_filter_member_info_url_e', $url);
	echo esc_url( $url );
}

function wc2_member_login_title_e() {
	$title = ( wc2_is_member_logged_in() ) ? 'ログアウト' : '会員ログイン';
	esc_html_e( $title );
}

function wc2_member_info_title_e() {
	$title = ( wc2_is_member_logged_in() ) ? '会員情報' : '新規会員登録';
	$title = apply_filters('wc2_filter_member_info_title', $title);
	esc_html_e( $title );
}

function wc2_member_completion_message_e() {
	$wc2_public = WC2_Public::get_instance();
	$current_page = $wc2_public->page->get_current_slug();

	switch( $current_page ) {
	case 'logout':
		$message = 'ログアウトしました'. wc2_member_page_back_link();
		break;
	case 'newcomplete':
		$message = '会員登録が完了しました'. wc2_member_page_back_link();
		break;
	case 'editcomplete':
		if('delete_member' == $_POST['wcaction']){
			$message = '会員情報を削除しました'. wc2_member_page_back_link();
		}else{
			$message = __( 'Updated!' ). wc2_member_page_back_link();
		}
		break;
	case 'lostcomplete':
		$message = 'メールを送信しました。<br />メールの内容にしたがって24時間以内にパスワードを変更してください。'. wc2_member_page_back_link();
		break;
	case 'changecomplete':
		$message = 'パスワードを更新しました'. wc2_member_page_back_link();
		break;
	default:
		$message = __( 'Updated!' ). wc2_member_page_back_link();
	}
	$message = apply_filters( 'wc2_filter_member_completion_message', $message, $current_page );
	_e( $message );
}

function wc2_member_page_back_link(){
	$wc2_public = WC2_Public::get_instance();
	$current_page = $wc2_public->page->get_current_slug();
	switch( $current_page ) {
	case 'logout':
		$link = '<div class="member-back-link"><a href="'.home_url('/?member=login').'">再ログインはこちら</a></div>';
		break;
	case 'newcomplete':
		$link = '<div class="member-back-link"><a href="'.home_url('/?member=login').'">ログインはこちら</a></div>';
		break;
	case 'editcomplete':
		if('delete_member' == $_POST['wcaction']){
			$link = '<div class="member-back-link"><a href="'.home_url().'">トップページへ</a></div>';
		}else{
			$link = '<div class="member-back-link"><a href="'.home_url('/?member=memberform').'">戻る</a></div>';
		}
		break;
	case 'lostcomplete':
		$link = '<div class="member-back-link"><a href="'.home_url().'">トップページへ</a></div>';
		break;
	case 'changecomplete':
		$link = '<div class="member-back-link"><a href="'.home_url('/?member=login').'">ログインはこちら</a></div>';
		break;
	default:
		$link = '<div class="member-back-link"><a href="'.home_url().'">トップページへ</a></div>';
	}
	$link = apply_filters( 'wc2_filter_member_page_back_link', $link, $current_page );
	return $link;
}

function wc2_get_error_message() {
	$wc2_public = WC2_Public::get_instance();
	$error = $wc2_public->page->get_error();
	return $error;
}

function wc2_error_message() {
	if( array_key_exists( 'error', $_SESSION[WC2] ) ) {
		$error_message = '';
		if( array_key_exists( 'error', $_SESSION[WC2] ) and is_wp_error($_SESSION[WC2]['error']) ) {
			$error = $_SESSION[WC2]['error']->get_error_messages();
			foreach( (array)$error as $key => $message ) {
				$error_message .= esc_html($message).'<br />';
			}
		}
		if( '' != $error_message ) {
			echo '<div class="error">'.$error_message.'</div>';
		}
	}
}

function wc2_the_delivery_method( $value = '' ) {
	$deli_id = apply_filters( 'wc2_filter_get_available_delivery_method', wc2_get_available_delivery_method() );
	if( empty($deli_id) ) {
		$html = '
		<p>' . __('有効な配送方法がありません。', 'wc2') . '</p>';
	} else {
		$delivery = wc2_get_option( 'delivery' );
		$cdeliid = count($deli_id);
		$html = '
		<select name="offer[delivery_method]" id="delivery_method_select" class="delivery_method">';
		foreach( $deli_id as $id ) {
			$index = wc2_get_delivery_method_index( $id );
			$selected = ( $id == $value || 1 === $cdeliid ) ? ' selected="selected"' : '';
			$html .= "
			<option value=\"{$id}\"{$selected}>" . esc_html($delivery['delivery_method'][$index]['name']) . '</option>';
		}
		$html .= '
		</select>';
	}
	return $html;
}

function wc2_the_delivery_method_e( $value = '' ) {
	echo wc2_the_delivery_method( $value );
}

function wc2_the_delivery_date( $value = '' ) {
	$html = '
	<select name="offer[delivery_date]" id="delivery_date_select" class="delivery_date">
	</select>';
	return $html;
}

function wc2_the_delivery_date_e( $value = '' ) {
	echo wc2_the_delivery_date( $value );
}

function wc2_the_delivery_time( $value = '' ) {
	$html = '
	<div id="delivery_time_limit_message"></div>
	<select name="offer[delivery_time]" id="delivery_time_select" class="delivery_time">
	</select>';
	return $html;
}

function wc2_the_delivery_time_e( $value = '' ) {
	echo wc2_the_delivery_time( $value );
}

function wc2_the_payment_method( $value = '' ) {
	$payment_method = wc2_get_option( 'payment_method' );
	$payment_method = apply_filters( 'wc2_filter_the_payment_method', $payment_method, $value );
	$html = '
	<dl>';
	$list = '';
	$payment_ct = count($payment_method);
	foreach( (array)$payment_method as $id => $payment ) {
		if( $payment['name'] != '' and $payment['use'] != 'deactivate' ) {
			if( !WC2_Utils::is_blank($value) ) {
				$checked = ( $id == $value ) ? ' checked="checked"' : '';
			} else if( 1 == $payment_ct ) {
				$checked = ' checked="checked"';
			} else {
				$checked = '';
			}
			$checked = apply_filters( 'wc2_filter_the_payment_method_checked', $checked, $payment, $value );
			$list .= '
		<dt class="payment_'.$id.'"><label title="'.$id.'"><input name="offer[payment_method]" id="payment_method_'.$id.'" type="radio" value="'.$id.'"'.$checked.' /><span>'.esc_attr($payment['name']).'</span></label></dt>';
			$list .= '
		<dd class="payment_'.$id.'">'.$payment['explanation'].'</dd>'."\n";
		}
	}
	$html .= $list.'
	</dl>';

	if( empty($list) )
		$html = __('まだお支払方法の準備ができておりません。<br />管理者にお問い合わせください。', 'wc2');

	return $html;
}

function wc2_the_payment_method_e( $value = '' ) {
	echo wc2_the_payment_method( $value );
}

function wc2_delivery_secure_form_e() {
	$html = '';
	$payments = wc2_get_payment_option();
	$payments = apply_filters( 'wc2_filter_available_payment_method', $payments );
	foreach( (array)$payments as $payment ) {
		switch( $payment['settlement'] ) {
			case 'acting_zeus_card':
			case 'acting_zeus_conv':
				//include( WC2_PLUGIN_DIR.'/public/includes/secure-form-zeus.php' );
				break;
		}
	}
	echo $html;
}

function wc2_purchase_form_e() {
	$payment_method = wc2_get_entry_order_value( 'payment_method' );
	$payment = wc2_get_payment( $payment_method );

	$html = '';
	switch( $payment['settlement'] ) {
	case 'acting_zeus_card':
	case 'acting_zeus_conv':
		//include( WC2_PLUGIN_DIR.'/public/includes/purchase-form-zeus.php' );
		break;

	default:
		include( WC2_PLUGIN_DIR.'/public/includes/purchase-form.php' );
	}
	echo $html;
}

function wc2_transaction_key( $digit = 10 ) {
	$transaction_key = wc2_rand( $digit );
	return $transaction_key;
}

function wc2_rand( $digit = 10 ) {
	$num = str_repeat( "9", $digit );
	$rand = apply_filters( 'wc2_filter_rand_value', sprintf( '%0'.$digit.'d', mt_rand( 1, (int)$num ) ), $num );
	return $rand;
}

function wc2_is_cart_page() {
	global $query_string;
	parse_str( $query_string, $query_var );
	return array_key_exists( 'cart', $query_var );
}

function wc2_is_member_page() {
	global $query_string;
	parse_str( $query_string, $query_var );

	return array_key_exists( 'member', $query_var );
}

function wc2_set_error_front($message, $status){
	$wc2_public = WC2_Public::get_instance();
	$wc2_public->page->set_error( $message, $status );
}

function wc2_page_redirect(){
	$wc2_public = WC2_Public::get_instance();
	$wc2_public->page->page_redirect();
}

function wc2_get_current_page_type(){
	$wc2_public  = WC2_Public::get_instance();
	return $wc2_public->page->get_current_page_type();
}

function wc2_get_current_page(){
	$wc2_public  = WC2_Public::get_instance();
	return $wc2_public->page->get_current_page();
}

function wc2_set_current_page_type($page_type){
	$wc2_public  = WC2_Public::get_instance();
	return $wc2_public->page->set_current_page_type($page_type);
}

function wc2_set_current_page( $page ) {
	$wc2_public  = WC2_Public::get_instance();
	return $wc2_public->page->set_current_page($page);
}

function wc2_get_current_slug(){
	$wc2_public  = WC2_Public::get_instance();
	return $wc2_public->page->get_current_slug();
}


