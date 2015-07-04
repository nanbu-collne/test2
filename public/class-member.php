<?php
class WC2_Member_Front {
	
	protected $error_message = array();
	protected $current_member;
	protected static $instance = null;

	public function __construct(){

	//	add_filter( 'parse_query', array($this, 'parse_query_redirect') );//add
	}

	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function get_member_error(){
		return $this->error_message;
	}

	public function set_current_member() {
		if ( isset($_SESSION[WC2]['member']['ID']) ) {
			$this->current_member['id'] = $_SESSION[WC2]['member']['ID'];
			$this->current_member['name'] = wc2_localized_name( $_SESSION[WC2]['member']['name1'], $_SESSION[WC2]['member']['name2'] );
		} else {
			$this->current_member['id'] = 0;
			$this->current_member['name'] = __('guest', 'wc2');
		}
	}

	public function member_login_process() {
		//global $wpdb;

		$wc2_db_member = WC2_DB_Member::get_instance();
		$_POST = wc2_stripslashes_deep_post($_POST);
		$cookie = wc2_get_cookie();
		$this->error_message = array();
		//cookieあり・記憶checkなし・ログインキー(アカウント又はメールアドレス)項目なし
		if ( isset($cookie['rme']) && $cookie['rme'] == 'forever' && !isset($_POST['rememberme']) && !isset($_POST['loginkey'])) {
			$account = $cookie['name'];
			$id = $wc2_db_member->get_member_id_by_account($account);
			//cookie['name']のアカウントが会員にいない
			if ( !$id ) {
				$this->current_member['account'] = htmlspecialchars($account);
				$this->error_message[] = __('アカウントが違います。', 'wc2');
				return false;
			//cookie['name']のアカウントが会員にいる
			} else {
				$member = $wc2_db_member->get_member_data($id);
				if ( empty($member) ) {
					$this->current_member['account'] = htmlspecialchars($account);
					$this->error_message[] = __('パスワードが違います。', 'wc2');
					return false;
				} else {
					//セッションに会員情報をセット
					$this->set_session_current_member($member);

					do_action( 'wc2_action_after_login' );
					return apply_filters( 'wc2_filter_member_login', true, $member );
				}
			}
		//ログインキーとパスワードが入力されておらず、クッキーに記憶情報がない
		} else if ( isset($_POST['loginkey']) && WC2_Utils::is_blank($_POST['loginkey']) && isset($_POST['loginpass']) && WC2_Utils::is_blank($_POST['loginpass']) && isset($cookie['rme']) && $cookie['rme'] != 'forever' ) {
			$this->error_message[] = __('アカウント又はメールアドレスを入力してください。', 'wc2');
			$this->error_message[] = __('パスワードを入力してください。', 'wc2');
			return false;
		} else if ( isset($_POST['loginkey']) && WC2_Utils::is_blank($_POST['loginpass']) && isset($cookie['rme']) && $cookie['rme'] != 'forever' ) {
			$this->current_member['account'] = trim($_POST['loginkey']);
			$this->error_message[] = __('パスワードを入力してください。', 'wc2');
			return false;

		} else if ( !isset($_POST['loginkey']) ){
			return false;
		} else {
			$loginkey = isset($_POST['loginkey']) ? trim($_POST['loginkey']) : '';
			$pass = isset($_POST['loginpass']) ? md5(trim($_POST['loginpass'])) : '';
			$pos = strpos($loginkey, '@');
			$pos = apply_filters('wc2_filter_select_loginkey_check', $pos);
			if( false === $pos ){
				//アカウント
				$account = $loginkey;
				$id = $wc2_db_member->get_member_id_by_account($account);
				if ( !$id ) {
					$this->current_member['account'] = htmlspecialchars($account);
					$this->error_message[] = __('アカウントが違います。', 'wc2');
					return false;
				}
				$id = $wc2_db_member->login_check_by_account($account, $pass);
				if ( !$id ) {
					$this->current_member['account'] = htmlspecialchars($account);
					$this->error_message[] = __('パスワードが違います。', 'wc2');
					return false;
				}
			}else{
				//メールアドレス
				$email = $loginkey;
				$id = $wc2_db_member->get_member_id_by_email($email);
				if ( !$id ) {
					$this->current_member['email'] = htmlspecialchars($email);
					$this->error_message[] = __('メールアドレスが違います。', 'wc2');
					return false;
				}
				$id = $wc2_db_member->login_check_by_email($email, $pass);
				if ( !$id ) {
					$this->current_member['account'] = htmlspecialchars($account);
					$this->error_message[] = __('パスワードが違います。', 'wc2');
					return false;
				}
			}
			$member = $wc2_db_member->get_member_data($id);

			//セッションに会員情報をセット
			$this->set_session_current_member($member);

			if( isset($_POST['rememberme']) ){
				$cookie['name'] = $member['account'];
				$cookie['rme'] = 'forever';
				wc2_set_cookie($cookie);
			}else{
				$cookie['name'] = '';
				$cookie['rme'] = '';
				wc2_set_cookie($cookie);
			}

			do_action( 'wc2_action_after_login' );
			return apply_filters( 'wc2_filter_member_login_process', true, $member );
		}
	}

	//エラー時にフォームの入力項目をセッションに保存
	public function set_session_member_from_post_data(){
		//固定
		$member_id = $this->get_memberinfo('ID');
		$rank = wc2_get_member_data_value($member_id, MEMBER_RANK);
		$point = wc2_get_member_data_value($member_id, MEMBER_POINT);
		$registered = wc2_get_member_data_value($member_id, MEMBER_REGISTERED);

		if( isset( $_SESSION[WC2]['member'] ) ){
			unset($_SESSION[WC2]['member']);
		}

		$_SESSION[WC2]['member']['ID'] = $member_id;
		$_POST = WC2_Utils::stripslashes_deep_post($_POST);
		$base_member_key = array(
									'account',
									'email',
									'email2' ,
									'rank' ,
									'point' ,
									'name1' ,
									'name2' ,
									'name3' ,
									'name4' ,
									'country' ,
									'zipcode' ,
									'pref' ,
									'address1' ,
									'address2' ,
									'tel' ,
									'fax' ,
									'registered' ,
								);

		foreach( $base_member_key as $mem_key ){
			switch($mem_key){
			case 'rank':
				$_SESSION[WC2]['member'][$mem_key] = $rank;
				break;
			case 'point':
				$_SESSION[WC2]['member'][$mem_key] = $point;
				break;
			case 'registered':
				$_SESSION[WC2]['member'][$mem_key] = $registered;
				break;
			default:
				$_SESSION[WC2]['member'][$mem_key] = ( isset( $_POST['member'][$mem_key] ) ) ? $_POST['member'][$mem_key]: '';
				break;
			}
		}

		//csmb
		$csmb_keys = wc2_get_custom_field_keys(WC2_CSMB);
		if( !empty($csmb_keys) && is_array($csmb_keys) ){
			foreach($csmb_keys as $key){
				list( $pfx, $csmb_key ) = explode('_', $key, 2);
				$csmb_val = ( isset( $_POST[WC2_CUSTOM_MEMBER][$csmb_key] ) ) ? $_POST[WC2_CUSTOM_MEMBER][$csmb_key]: '';
				$_SESSION[WC2]['member'][WC2_CUSTOM_MEMBER][$csmb_key] = $csmb_val;
			}
		}
		//meta

		do_action('wc2_action_set_session_member_from_post_data');

	}

	public function set_session_current_member($member){

		if( isset( $_SESSION[WC2]['member'] ) ){
			unset($_SESSION[WC2]['member']);
		}
		if(empty($member)){
			return;
		}

		$_SESSION[WC2]['member']['ID'] = $member['ID'];
		$_SESSION[WC2]['member']['account'] = $member['account'];
		$_SESSION[WC2]['member']['email'] = $member['email'];
		$_SESSION[WC2]['member']['email2'] = $member['email'];
		$_SESSION[WC2]['member']['rank'] = $member['rank'];
		$_SESSION[WC2]['member']['point'] = $member['point'];
		$_SESSION[WC2]['member']['name1'] = $member['name1'];
		$_SESSION[WC2]['member']['name2'] = $member['name2'];
		$_SESSION[WC2]['member']['name3'] = $member['name3'];
		$_SESSION[WC2]['member']['name4'] = $member['name4'];
		$_SESSION[WC2]['member']['country'] = $member['country'];
		$_SESSION[WC2]['member']['zipcode'] = $member['zipcode'];
		$_SESSION[WC2]['member']['pref'] = $member['pref'];
		$_SESSION[WC2]['member']['address1'] = $member['address1'];
		$_SESSION[WC2]['member']['address2'] = $member['address2'];
		$_SESSION[WC2]['member']['tel'] = $member['tel'];
		$_SESSION[WC2]['member']['fax'] = $member['fax'];
		$_SESSION[WC2]['member']['registered'] = $member['registered'];

		//csmb
		if( isset($member[WC2_CUSTOM_MEMBER]) ){
			$_SESSION[WC2]['member'][WC2_CUSTOM_MEMBER] = $member[WC2_CUSTOM_MEMBER];
		}

		//meta
		if( isset($member['meta_type']) ){
			$_SESSION[WC2]['member']['meta_type'] = $member['meta_type'];
		}
		if( isset($member['meta_key']) ){
			$_SESSION[WC2]['member']['meta_key'] = $member['meta_key'];
		}

		$this->set_current_member();
	}

	public function member_logout_process(){
		//unset($_SESSION[WC2]['member'], $_SESSION['wc2_entry']);
		unset($_SESSION[WC2]['member'], $_SESSION[WC2]['entry']);
		do_action('wc2_action_member_logout_process');

		$wc2_db_member = WC2_DB_Member::get_instance();
		$wc2_db_member->clear_column();

		return 'logout';
		//wp_redirect(get_option('home'));
		//exit;
	}

	static function get_newmember_button($member_regmode){
		$html = '<input name="member_regmode" type="hidden" value="' . $member_regmode . '" />';
		$newmemberbutton = '<input name="regmember" type="submit" value="' . __('送信する', 'wc2') . '" />';
		$html .= apply_filters('wc2_filter_newmember_button', $newmemberbutton);
		return $html;
	}

	static function get_login_button(){
		$loginbutton = '<input type="submit" name="member_login" id="member_login" class="member_login_button" value="' . __('ログイン', 'wc2') . '" />';
		$html = apply_filters('wc2_filter_login_button', $loginbutton);
		echo $html;
	}

	static function is_member_logged_in( $id = false ) {
		if( $id === false ){
			if( !empty($_SESSION[WC2]['member']['ID']) )
				return true;
			else
				return false;
		}else{
			if( !empty($_SESSION[WC2]['member']['ID']) && $_SESSION[WC2]['member']['ID'] == $id )
				return true;
			else
				return false;
		}
	}

	static function get_loginout(){

		if( !wc2_is_login() ) {
			$res = '<a href="'. apply_filters('wc2_filter_login_url', WC2_LOGIN_URL) .'" class="wc2_login_a">' . apply_filters('wc2_filter_loginlink_label', __('ログイン','wc2')) . '</a>';
		}else{
			$res = '<a href="' . apply_filters('wc2_filter_logout_uri', WC2_LOGOUT_URL) . '" class="wc2_logout_a">' . apply_filters('wc2_filter_logoutlink_label', __('ログアウト','wc2')) . '</a>';
		}
		return $res;
	}

	public function get_memberinfo( $key ){
		//global $wpdb;
		$info = $this->get_member();
		if( empty($key) ) return $info;
		
/*
		switch ($key){
			case 'registered':
				$res = mysql2date(__('Mj, Y', 'wc2'), $info['registered']);
				break;
			case 'point':
				$member_table = $wpdb->prefix."wc2_member";
				$query = $wpdb->prepare("SELECT mem_point FROM $member_table WHERE ID = %d", $info['ID']);
				$res = $wpdb->get_var( $query );
				break;
			default:
				$res = isset($info[$key]) ? $info[$key] : '';
		}
		
*/
		$res = ( array_key_exists($key, $info) ) ? $info[$key] : '';
		return $res;
	}

	public function get_member(){
		$res = array(
			'ID' => '',
			'account' => '',
			'email' => '',
			'email2' => '',
			'rank' => 0,
			'point' => '',
			'name1' => '',
			'name2' => '',
			'name3' => '',
			'name4' => '',
			'country' => '',
			'zipcode' => '',
			'pref' => '',
			'address1' => '',
			'address2' => '',
			'tel' => '',
			'fax' => '',
			'registered' => '',
		);

		if(!empty($_SESSION[WC2]['member'])) {
			foreach ( $_SESSION[WC2]['member'] as $key => $value ) {
				//if(is_array($_SESSION[WC2]['member'][$key])) 
				if(is_array($value))
					$res[$key] = stripslashes_deep($value);
				else
					$res[$key] = stripslashes($value);
			}
		}
		return $res;
	}

	//会員登録完了メール
	public function send_regmembermail($user) {
		$res = false;
		$phrase = wc2_get_option('phrase');
		$general = wc2_get_option('general');
		$newmem_admin_mail = ( isset($phrase['newmem_admin_mail']) ) ? $phrase['newmem_admin_mail'] : 1;
		$name = wc2_localized_name(trim($user['name1']), trim($user['name2']));

		$subject =  $phrase['title']['membercomp'];
		$message = $phrase['header']['membercomp'];
		$message .= __('【登録情報】', 'wc2')."\r\n";
		$message .= '--------------------------------'."\r\n";
		$message .= __('会員番号', 'wc2') . ' : ' . $user['ID'] . "\r\n";
		$message .= __('アカウント', 'wc2') . ' : ' . $user['account'] . "\r\n";
		$message .= __('Kana', 'wc2') . ' : ' . sprintf(__('%s 様', 'wc2'), $name) . "\r\n";
		$message .= __('メールアドレス', 'wc2') . ' : ' . trim( $user['email'] )."\r\n";
		$message .= '--------------------------------'."\r\n\r\n";
		$message .= $phrase['footer']['membercomp'];
		$message = apply_filters('wc2_filter_send_regmembermail_message', $message, $user);

		$wc2_mail = WC2_Mail::get_instance();
		$wc2_mail->clear_column();
		$wc2_mail->set_customer_para_value( 'to_name', sprintf(__('%s 様', 'wc2'), $name) );
		$wc2_mail->set_customer_para_value( 'to_address', trim($user['email']) );
		$wc2_mail->set_customer_para_value( 'from_name', get_option('blogname') );
		$wc2_mail->set_customer_para_value( 'from_address', $general['sender_mail'] );
		$wc2_mail->set_customer_para_value( 'return_path', $general['sender_mail'] );
		$wc2_mail->set_customer_para_value( 'subject', $subject );
		$wc2_mail->set_customer_para_value( 'message', do_shortcode($message) );
		do_action('wc2_action_send_regmembermail_customer_para');

		$res = $wc2_mail->send_customer_mail();
		
		if($newmem_admin_mail){
			$subject =  __('新規会員登録がありました。', 'wc2');
			$message = __('新規会員登録がありました。', 'wc2') . "\r\n\r\n";
			$message .= __('【登録情報】', 'wc2')."\r\n";
			$message .= '--------------------------------'."\r\n";
			$message .= __('Membership ID', 'wc2') . ' : ' . $user['ID'] . "\r\n";
			$message .= __('アカウント', 'wc2') . ' : ' . $user['account'] . "\r\n";
			$message .= __('Kana', 'wc2') . ' : ' . sprintf(__('%s 様', 'wc2'), $name) . "\r\n";
			$message .= __('メールアドレス', 'wc2') . ' : ' . trim( $user['email'] )."\r\n";
			$message .= '--------------------------------'."\r\n\r\n";
			$message = apply_filters('wc2_filter_send_regmembermail_notice', $message, $user);

			$wc2_mail->set_admin_para_value( 'to_name', __('新規会員登録通知', 'wc2') );
			$wc2_mail->set_admin_para_value( 'to_address', $general['order_mail'] );
			$wc2_mail->set_admin_para_value( 'from_name', 'Welcart Auto BCC' );
			$wc2_mail->set_admin_para_value( 'from_address', $general['sender_mail'] );
			$wc2_mail->set_admin_para_value( 'return_path', $general['sender_mail'] );
			$wc2_mail->set_admin_para_value( 'subject', $subject );
			$wc2_mail->set_admin_para_value( 'message', do_shortcode($message) );
			do_action('wc2_action_send_regmembermail_admin_para');

			$res = $wc2_mail->send_admin_mail();
		}
		return $res;
	}

	//会員情報更新メール
	public function send_editmembermail($user){
		$res = false;
		$phrase = wc2_get_option('phrase');
		$general = wc2_get_option('general');
		$editmem_customer_mail = isset( $phrase['editmem_customer_mail'] ) ? $phrase['editmem_customer_mail']: 1;
		$wc2_mail = WC2_Mail::get_instance();
		$wc2_mail->clear_column();

		if($editmem_customer_mail){
			$name = wc2_localized_name( trim($user['name1']), trim($user['name2']));
			$subject = apply_filters( 'wc2_filter_send_editmembermail_subject', __('会員情報が更新されました。', 'wc2'), $user );
			$message = $subject."\r\n\r\n";
			$message .= __('【登録情報】', 'wc2')."\r\n";
			$message .= '--------------------------------'."\r\n";
			$message .= __('Membership ID', 'wc2').' : '. trim($user['ID']) ."\r\n";
			$message .= __('アカウント', 'wc2') . ' : ' . trim($user['account']) . "\r\n";
			$message .= __('Kana', 'wc2').' : '.sprintf(__('%s 様', 'wc2'), $name ) ."\r\n";
			$message .= __('メールアドレス', 'wc2').' : '. trim($user['email']) ."\r\n";
			$message .= '--------------------------------'."\r\n\r\n";
			$message .= $phrase['footer']['membercomp'];
			$message = apply_filters( 'wc2_filter_send_editmembermail_message', $message, $user );

			$wc2_mail->set_customer_para_value( 'to_name', sprintf(__('%s 様', 'wc2'), $name) );
			$wc2_mail->set_customer_para_value( 'to_address', trim($user['email']) );
			$wc2_mail->set_customer_para_value( 'from_name', get_option('blogname') );
			$wc2_mail->set_customer_para_value( 'from_address', $general['sender_mail'] );
			$wc2_mail->set_customer_para_value( 'return_path', $general['sender_mail'] );
			$wc2_mail->set_customer_para_value( 'subject', $subject );
			$wc2_mail->set_customer_para_value( 'message', do_shortcode($message) );
			do_action('wc2_action_send_editmembermail_customer_para');

			$res = $wc2_mail->send_customer_mail();
		}
		return $res;
	}

	//会員削除メール
	public function send_delmembermail( $user ) {
		$res = false;
		$phrase = wc2_get_option('phrase');
		$general = wc2_get_option('general');
		$delmem_customer_mail = isset( $phrase['delmem_customer_mail'] ) ? $phrase['delmem_customer_mail']: 1;
		$delmem_admin_mail = isset( $phrase['delmem_admin_mail'] ) ? $phrase['delmem_admin_mail']: 1;

		$name = wc2_localized_name(trim($user['name1']), trim($user['name2']));
		$subject = apply_filters( 'wc2_filter_send_delmembermail_subject', __('退会処理が完了しました。', 'wc2'), $user );
		$wc2_mail = WC2_Mail::get_instance();
		$wc2_mail->clear_column();

		if( $delmem_customer_mail ) {
			$message = $subject."\r\n\r\n";
			$message .= __('【登録情報】', 'wc2')."\r\n";
			$message .= '--------------------------------'."\r\n";
			$message .= __('Membership ID', 'wc2').' : '.$user['ID']."\r\n";
			$message .= __('アカウント', 'wc2') . ' : ' . $user['account'] . "\r\n";
			$message .= __('Name', 'wc2').' : '.sprintf(__('%s 様', 'wc2'), $name)."\r\n";
			$message .= __('メールアドレス', 'wc2').' : '. trim( $user['email'] ) ."\r\n";
			$message .= '--------------------------------'."\r\n\r\n";
			$message .= $phrase['footer']['membercomp'];
			$message = apply_filters( 'wc2_filter_send_delmembermail_message', $message, $user );

			$wc2_mail->set_customer_para_value( 'to_name', sprintf(__('%s 様', 'wc2'), $name) );
			$wc2_mail->set_customer_para_value( 'to_address', trim($user['email']) );
			$wc2_mail->set_customer_para_value( 'from_name', get_option('blogname') );
			$wc2_mail->set_customer_para_value( 'from_address', $general['sender_mail'] );
			$wc2_mail->set_customer_para_value( 'return_path', $general['sender_mail'] );
			$wc2_mail->set_customer_para_value( 'subject', $subject );
			$wc2_mail->set_customer_para_value( 'message', do_shortcode($message) );
			do_action('wc2_action_send_delmembermail_customer_para');

			$res = $wc2_mail->send_customer_mail();
		}

		if( $delmem_admin_mail ) {
			$message = $subject."\r\n\r\n";
			$message .= __('【登録情報】', 'wc2')."\r\n";
			$message .= '--------------------------------'."\r\n";
			$message .= __('Membership ID', 'wc2').' : '.$user['ID']."\r\n";
			$message .= __('アカウント', 'wc2') . ' : ' . $user['account'] . "\r\n";
			$message .= __('Kana', 'wc2').' : '.sprintf(__('%s 様', 'wc2'), $name)."\r\n";
			$message .= __('メールアドレス', 'wc2') . ' : '. trim( $user['email'] ) ."\r\n";
			$message .= '--------------------------------'."\r\n\r\n";
			$message = apply_filters( 'wc2_filter_send_delmembermail_notice', $message, $user );

			$wc2_mail->set_admin_para_value( 'to_name', __('会員削除通知', 'wc2') );
			$wc2_mail->set_admin_para_value( 'to_address', $general['order_mail'] );
			$wc2_mail->set_admin_para_value( 'from_name', 'Welcart Auto BCC' );
			$wc2_mail->set_admin_para_value( 'from_address', $general['sender_mail'] );
			$wc2_mail->set_admin_para_value( 'return_path', $general['sender_mail'] );
			$wc2_mail->set_admin_para_value( 'subject', $subject );
			$wc2_mail->set_admin_para_value( 'message', do_shortcode($message) );
			do_action('wc2_action_send_delmembermail_admin_para');

			$res = $wc2_mail->send_admin_mail();
		}
		return $res;
	}

	public function lostpass_mailaddcheck(){
		$mes = '';
		if ( !is_email($_POST['loginmail']) || WC2_Utils::is_blank($_POST['loginmail']) ) {
			$mes .= __('メールアドレスが不正です。', 'wc2');
		}elseif( !wc2_is_member($_POST['loginmail']) ){
			$mes .= __('会員データに存在しないメールアドレスです。', 'wc2');
		}
		return $mes;
	}

	public function lostpass_process(){
		$permalink_structure = get_option('permalink_structure');
		//wc2_use_ssl
		$delim = ( !wc2_use_ssl() && $permalink_structure) ? '?': '&';
		$lostmail = trim($_POST['loginmail']);
		$lostkey = $this->make_lostkey();
		
		//$wc2_db_member = WC2_DB_Member::get_instance();
		//$wc2_db_member->store_lost_mail_key( $lostmail, $lostkey);
		wc2_store_lost_mail_key( $lostmail, $lostkey );
		$system_options = get_option('system');

		if( wc2_use_ssl() ){
			$system_options = get_option('system');
			$ssl_url = $system_options['ssl_url'];
			$uri = $ssl_url . '/' . $delim . 'member=changepassword&mem=' . urlencode($lostmail) . '&key=' . urlencode($lostkey) . '&wcaction=change_password';
		}else{
			$uri = home_url() . '/' . $delim . 'member=changepassword&mem=' . urlencode($lostmail) . '&key=' . urlencode($lostkey) . '&wcaction=change_password';
		}
		$res = $this->send_lostmail($uri);
		return $res;
	}

	public function make_lostkey(){
		return uniqid('wc', true);
	}

	public function send_lostmail($uri){
		$res = false;
		
		if( isset($_REQUEST['loginmail']) && !empty($_REQUEST['loginmail']) ){
			$phrase_options = wc2_get_option('phrase');
			$general = wc2_get_option('general');
			$lostmail = $_REQUEST['loginmail'];
			$subject = apply_filters( 'wc2_filter_lostmail_subject', __('パスワード変更','wc2') );
			$message = __('下記のURLをクリックしてパスワードの変更を行ってください。','wc2') . "\n\r\n\r\n\r"
					. $uri . "\n\r\n\r\n\r"
					. __('このメールにお心当たりがない場合は破棄していただきますようお願いいたします。','wc2') . "\n\r";
			$message = apply_filters( 'wc2_filter_lostmail_message', $message, $uri );
			$message .= apply_filters( 'wc2_filter_lostmail_footer', $phrase_options['footer']['othermail'] );
			$wc2_mail = WC2_Mail::get_instance();
			$wc2_mail->clear_column();
			$wc2_mail->set_customer_para_value( 'to_name', __('パスワード変更手続き', 'wc2') );
			$wc2_mail->set_customer_para_value( 'to_address', $lostmail );
			$wc2_mail->set_customer_para_value( 'from_name', get_option('blogname') );
			$wc2_mail->set_customer_para_value( 'from_address', $general['sender_mail'] );
			$wc2_mail->set_customer_para_value( 'return_path', $general['sender_mail'] );
			$wc2_mail->set_customer_para_value( 'subject', $subject );
			$wc2_mail->set_customer_para_value( 'message', do_shortcode($message) );
			do_action('wc2_action_send_lostmail_customer_para');

			$res = $wc2_mail->send_customer_mail();
		}
		
		if($res === false) {
			$this->error_message = __('メールを送信できませんでした。','wc2');
		}

		return $res;
	}

	public function changepass_check(){
		$mes = array();
		//文字数チェック追加
		if( WC2_Utils::is_blank($_POST['new_password1']) || WC2_Utils::is_blank($_POST['new_password2']) ){
			$mes[] = __('新しいパスワードを入力してください。', 'wc2');
		}elseif( trim($_POST['new_password1']) != trim($_POST['new_password2']) ){
			$mes[] = __('確認用パスワードと異なります。', 'wc2');
		}elseif( !preg_match( "/^[a-zA-Z0-9]+$/", trim( $_POST['new_password1'] ) ) ){
			$mes[] = __('パスワードは半角英数字で入力してください。', 'wc2');
		}else{
			$general = wc2_get_option('general');
			$member_pass_rule_min = $general['member_pass_rule_min'];
			$member_pass_rule_max = $general['member_pass_rule_max'];
			if( !empty( $member_pass_rule_max ) ){
				if( $member_pass_rule_min > strlen( trim($_POST['new_password1']) ) || strlen( trim($_POST['new_password1']) ) > $member_pass_rule_max ){
					$mes[] = sprintf(__('パスワードは%1$s文字以上%2$s文字以下で入力してください。', 'wc2'), $member_pass_rule_min, $member_pass_rule_max );
				}
			}else{
				if( $member_pass_rule_min > strlen( trim($_POST['new_password1']) ) ){
					$mes[] = sprintf(__('パスワードは%s文字以上で入力してください。', 'wc2'), $member_pass_rule_min);
				}
			}
		}
		return $mes;
	}

	//hidden
	public function get_action_lostmail_inform(){
		if( !isset( $_REQUEST['mem']) || !isset($_REQUEST['key'] ) ){
			die('不正なパラメータです。');
		}
		$lostmail = urldecode($_REQUEST['mem']);
		$lostkey = urldecode($_REQUEST['key']);
		$html = '
			<input type="hidden" name="mem" value="' . esc_attr($lostmail) . '" />
			<input type="hidden" name="key" value="' . esc_attr($lostkey) . '" />' . "\n";
		return $html;
	}

	public function get_member_page_back_url(){
		if( isset($_SERVER['HTTP_REFERER']) ){
			if( false !== strpos( $_SERVER['HTTP_REFERER'], home_url() ) ){
				$url = $_SERVER['HTTP_REFERER'];
			}else{
				$url = home_url();
			}
		}else{
			$url = home_url();
		}
		$url = apply_filters('wc2_filter_member_page_back_url', $url);
		return $url;
	}
}

function wc2_get_member_page_back_url(){
	$wc2_member_front = WC2_Member_Front::get_instance();
	return $wc2_member_front->get_member_page_back_url();
}


function wc2_get_member_error(){
	$wc2_member_front = WC2_Member_Front::get_instance();
	$res = $wc2_member_front->get_member_error();
	return $res;
}

function wc2_newmember_button($member_regmode){
	$res = WC2_Member_Front::get_newmember_button($member_regmode);
	return $res;
}

function wc2_newmember_button_e($member_regmode){
	echo wc2_newmember_button($member_regmode);
}

function wc2_login_button(){
	$res = WC2_Member_Front::get_login_button();
	return $res;
}

function wc2_is_login( $id = false ) {
	if( false === WC2_Member_Front::is_member_logged_in($id) ){
		$res = false;
	}else{
		$res = true;
	}
	return $res;
}

function wc2_memberinfo( $key = '' ){
	$wc2_member_front = WC2_Member_Front::get_instance();
	$res = $wc2_member_front->get_memberinfo( $key );
	return $res;
}

function wc2_memberinfo_e( $key = '' ){
	echo wc2_memberinfo( $key );
}

function wc2_get_member(){
	$wc2_member_front = WC2_Member_Front::get_instance();
	$res = $wc2_member_front->get_member();
	return $res;
}

function wc2_member_login_process(){
	$wc2_member_front = WC2_Member_Front::get_instance();
	$res = $wc2_member_front->member_login_process();
	return $res;
}

function wc2_member_logout_process(){
	$wc2_member_front = WC2_Member_Front::get_instance();
	$res = $wc2_member_front->member_logout_process();
	return $res;
//	wp_redirect(get_option('home'));
}

function wc2_loginout(){
	$res = WC2_Member_Front::get_loginout();
	return $res;
}

function wc2_send_regmembermail($user){
	$wc2_member_front = WC2_Member_Front::get_instance();
	return $wc2_member_front->send_regmembermail($user);	
}

function wc2_send_editmembermail($user){
	$wc2_member_front = WC2_Member_Front::get_instance();
	return $wc2_member_front->send_editmembermail($user);	
}

function wc2_send_delmembermail($user){
	$wc2_member_front = WC2_Member_Front::get_instance();
	return $wc2_member_front->send_delmembermail($user);	
}

function wc2_lostpass_mailaddcheck(){
	$wc2_member_front = WC2_Member_Front::get_instance();
	$res = $wc2_member_front->lostpass_mailaddcheck();
	return $res;
}

function wc2_lostpass_process(){
	$wc2_member_front = WC2_Member_Front::get_instance();
	return $wc2_member_front->lostpass_process();
}

function wc2_send_lostmail(){
	$wc2_member_front = WC2_Member_Front::get_instance();
	return $wc2_member_front->send_lostmail();
}

function wc2_make_lostkey(){
	$wc2_member_front = WC2_Member_Front::get_instance();
	return $wc2_member_front->make_lostkey();
}

function wc2_changepass_check(){
	$wc2_member_front = WC2_Member_Front::get_instance();
	return $wc2_member_front->changepass_check();
}

function wc2_changepass_field(){
	//$wc2_meber_front = WC2_Member_Front::get_instance();
	//$res = $wc2_meber_front->get_action_lostmail_inform();
	//echo $res;
	$lostmail = ( isset($_REQUEST['mem']) ) ? urldecode($_REQUEST['mem']) : '';
	$lostkey = ( isset($_REQUEST['key']) ) ? urldecode($_REQUEST['key']) : '';
	$html = '
		<input type="hidden" name="mem" value="' . esc_attr($lostmail) . '" />
		<input type="hidden" name="key" value="' . esc_attr($lostkey) . '" />';
	echo $html;
}

function wc2_set_session_current_member($member){
	$wc2_member_front = WC2_Member_Front::get_instance();
	return $wc2_member_front->set_session_current_member($member);
}

function wc2_set_session_member_from_post_data(){
	$wc2_member_front = WC2_Member_Front::get_instance();
	return $wc2_member_front->set_session_member_from_post_data();
}