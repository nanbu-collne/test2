<?php
class WC2_Member_Func
{

	protected static $instance = null;


	private function __construct(){
		$plugin = Welcart2::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();
	}

	public static function get_instance() {
		if( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/*******************************
	* メンバー登録前チェック
	* @since		1.0.0
	* 
	* NOTE:
	********************************/
	public function member_check($type, $member_id = '') {
		global $wpdb;
		
		//Variable "type" is "order" or "customer" or "delivery" or "member"
		$mes = array();
		$action ='';
		if( is_admin() ){
			if( isset($_POST['addButton']) ){
				$action = 'admin_add';
			}elseif( isset($_POST['upButton']) ){
				$action = 'admin_update';
			}
		}else{
			if( isset($_POST['wcaction']) ){
				if( 'register_member' == $_POST['wcaction'] || 'customer_process' == $_POST['wcaction'] ){
					$action = 'front_add';
				}elseif( 'update_member' == $_POST['wcaction']){	//カートから編集あとで追加
					$action = 'front_update';
				}
			}
		}

		if( empty($action) ){
			$mes[] = __('不正なデータが送信されました。', 'wc2');
			return $mes;
		}

		$general = wc2_get_option('general');
		$wc2_db_member = WC2_DB_Member::get_instance();
		$wc2_db_member->get_member_data($member_id);

		$account = $wc2_db_member->get_value('account');
		$email = $wc2_db_member->get_value('email');
		$member_pass_rule_min = $general['member_pass_rule_min'];
		$member_pass_rule_max = $general['member_pass_rule_max'];

		//アカウント
		if( WC2_Utils::is_blank( $_POST[$type]['account'] ) ){
			$mes[] = __('ログインアカウントを入力してください。', 'wc2');
		}elseif( !preg_match( "/^[a-zA-Z0-9]+$/", trim( $_POST[$type]['account'] ) ) ){
			$mes[] = __('ログインアカウントは半角英数字で入力してください。', 'wc2');
		}else{
			if( isset($_POST[$type]['account']) && trim($_POST[$type]['account']) != $account ){
				$mem_ID = $wc2_db_member->get_member_id_by_account( trim($_POST[$type]['account']) );
				if( !empty($mem_ID) && $member_id != $mem_ID )
					$mes[] = __('このログインアカウントは既に使用されています。', 'wc2');
			}
		}

		//パスワード
		if( 'front_update' == $action ){
			if( !empty($_POST[$type]['passwd']) || !empty($_POST[$type]['passwd2']) ){
				//フロント更新
				if( trim($_POST[$type]['passwd']) != trim($_POST[$type]['passwd2']) ){
					$mes[] = __('確認用パスワードと異なります。', 'wc2');
				}elseif( !preg_match( "/^[a-zA-Z0-9]+$/", trim( $_POST[$type]['passwd'] ) ) ){
					$mes[] = __('ログインパスワードは半角英数字で入力してください。', 'wc2');
				}else{
					if( !empty( $member_pass_rule_max ) ){
						if( $member_pass_rule_min > strlen( trim($_POST[$type]['passwd']) ) || strlen( trim($_POST[$type]['passwd']) ) > $member_pass_rule_max ){
							$mes[] = sprintf(__('パスワードは%1$s文字以上%2$s文字以下で入力してください。', 'wc2'), $member_pass_rule_min, $member_pass_rule_max );
						}
					}else{
						if( $member_pass_rule_min > strlen( trim($_POST[$type]['passwd']) ) ){
							$mes[] = sprintf(__('パスワードは%s文字以上で入力してください。', 'wc2'), $member_pass_rule_min);
						}
					}
				}
			}
		}elseif( 'front_add' == $action ) {
			//フロント新規登録 (パスワード・パスワード確認項目有）
			if( WC2_Utils::is_blank($_POST[$type]['passwd']) || WC2_Utils::is_blank($_POST[$type]['passwd2']) ){
				$mes[] = __('ログインパスワードを入力してください。', 'wc2');
			}elseif( trim($_POST[$type]['passwd']) != trim($_POST[$type]['passwd2']) ){
				$mes[] = __('確認用パスワードと異なります。', 'wc2');
			}elseif( !preg_match( "/^[a-zA-Z0-9]+$/", trim( $_POST[$type]['passwd'] ) ) ){
				$mes[] = __('ログインパスワードは半角英数字で入力してください。', 'wc2');
			}else{
				if( !empty( $member_pass_rule_max ) ){
					if( $member_pass_rule_min > strlen( trim($_POST[$type]['passwd']) ) || strlen( trim($_POST[$type]['passwd']) ) > $member_pass_rule_max ){
						$mes[] = sprintf(__('パスワードは%1$s文字以上%2$s文字以下で入力してください。', 'wc2'), $member_pass_rule_min, $member_pass_rule_max );
					}
				}else{
					if( $member_pass_rule_min > strlen( trim($_POST[$type]['passwd']) ) ){
						$mes[] = sprintf(__('パスワードは%s文字以上で入力してください。', 'wc2'), $member_pass_rule_min);
					}
				}
			}
		}elseif( 'admin_add' == $action ){
			//管理画面新規 （パスワード有・パスワード確認無し）
			if ( WC2_Utils::is_blank($_POST[$type]['passwd']) ){
				$mes[] = __('ログインパスワードを入力してください。', 'wc2');
			}elseif( !preg_match( "/^[a-zA-Z0-9]+$/", trim( $_POST[$type]['passwd'] ) ) ){
				$mes[] = __('ログインパスワードは半角英数字で入力してください。', 'wc2');
			}else{
				if( !empty( $member_pass_rule_max ) ){
					if( $member_pass_rule_min > strlen( trim($_POST[$type]['passwd']) ) || strlen( trim($_POST[$type]['passwd']) ) > $member_pass_rule_max ){
						$mes[] = sprintf(__('パスワードは%1$s文字以上%2$s文字以下で入力してください。', 'wc2'), $member_pass_rule_min, $member_pass_rule_max );
					}
				}else{
					if( $member_pass_rule_min > strlen( trim($_POST[$type]['passwd']) ) ){
						$mes[] = sprintf(__('パスワードは%s文字以上で入力してください。', 'wc2'), $member_pass_rule_min);
					}
				}
			}
		}

		//メールアドレス&メールアドレス確認項目有
		if( array_key_exists('email', $_POST[$type]) && array_key_exists('email2', $_POST[$type])  ){
			if( WC2_Utils::is_blank( $_POST[$type]['email'] ) || WC2_Utils::is_blank( $_POST[$type]['email2'] )  ){
				$mes[] = __('メールアドレスを入力してください。', 'wc2');
			}elseif( trim($_POST[$type]['email']) != trim($_POST[$type]['email2']) ){
				$mes[] = __('確認用メールアドレスと異なります。', 'wc2');
			}elseif( !is_email( trim( $_POST[$type]['email'] ) ) ){
				$mes[] = __('メールアドレスの値が不正です。', 'wc2');
			}else{
				if( trim($_POST[$type]['email']) != $email ){
					$mem_ID = $wc2_db_member->get_member_id_by_email($_POST[$type]['email']);
					if( !empty($mem_ID) )
						$mes[] = __('このメールアドレスは既に使用されています。', 'wc2');
				}
			}
		//メールアドレス項目有 確認無し
		}elseif( array_key_exists('email', $_POST[$type]) && !array_key_exists('email2', $_POST[$type]) ){
			if( WC2_Utils::is_blank( $_POST[$type]['email'] ) ){
				$mes[] = __('メールアドレスを入力してください。', 'wc2');
			}elseif( !is_email( trim( $_POST[$type]['email'] ) ) ){
				$mes[] = __('メールアドレスの値が不正です。', 'wc2');
			}else{
				if( trim($_POST[$type]['email']) != $email ){
					$mem_ID = $wc2_db_member->get_member_id_by_email($_POST[$type]['email']);
					if( !empty($mem_ID) )
						$mes[] = __('このメールアドレスは既に使用されています。', 'wc2');
				}
			}
		}

		//氏名
		if ( WC2_Utils::is_blank($_POST[$type]['name1']) || WC2_Utils::is_blank($_POST[$type]['name2']) ){
			$mes[] = __('氏名を入力してください。', 'wc2');
		}
		//郵便番号
		if ( WC2_Utils::is_blank($_POST[$type]['zipcode']) ){
			$mes[] = __('郵便番号が入力されていません。', 'wc2');
		}elseif( preg_match('/[^\d-]/', trim($_POST[$type]['zipcode'])) ){
			$mes[] = __('郵便番号は半角数字で入力してください。', 'wc2');
		}
		//都道府県
		if ( WC2_UNSELECTED == ($_POST[$type]['pref']) )
			$mes[] = __('都道府県が選択されていません。', 'wc2');
		//市区町村・番地
		if ( WC2_Utils::is_blank($_POST[$type]['address1']) )
			$mes[] = __('市区町村・番地が入力されていません。', 'wc2');
		//電話番号
		if( WC2_Utils::is_blank($_POST[$type]['tel']) ){
			$mes[] = __('電話番号が入力されていません。', 'wc2');
		}elseif( !WC2_Utils::is_blank($_POST[$type]['tel']) && preg_match('/[^\d-]/', trim($_POST[$type]['tel'])) ){
			$mes[] = __('電話番号は半角数字で入力してください。', 'wc2');
		}
		//FAX番号
		if( !WC2_Utils::is_blank($_POST[$type]['fax']) && preg_match('/[^\d-]/', trim($_POST[$type]['fax'])) ){
			$mes[] = __('FAX番号は半角数字で入力してください。', 'wc2');
		}

		//custom_field check
		$cstm_mes = wc2_custom_field_enter_check($type);
		foreach( $cstm_mes as $cstm_mes_val ){
			$mes[] = $cstm_mes_val;
		}

		$message = apply_filters('wc2_filter_member_check', $mes, $type, $action);

		return $message;
	}

	public function new_member_data( $type = 'member' ){
		if( !array_key_exists($type, $_POST) ){
			return false;
		}
		$_POST = WC2_Utils::stripslashes_deep_post($_POST);
		$general_options = wc2_get_option('general');

		$passwd = md5( trim($_POST[$type]['passwd']) );
		$rank = ( is_admin() && isset($_POST['member']['rank']) ) ? trim($_POST['member']['rank']): 0; //フロントからは通常会員0
		$point = ( is_admin() && isset($_POST['member']['point']) ) ? trim($_POST['member']['point']): $general_options['start_point'];

		$wc2_db_member = WC2_DB_Member::get_instance();
		$wc2_db_member->set_value( 'account', trim( $_POST[$type]['account'] ) );
		$wc2_db_member->set_value( 'email', trim( $_POST[$type]['email'] ) );
		$wc2_db_member->set_value( 'passwd', $passwd );
		$wc2_db_member->set_value( 'rank', $rank );
		$wc2_db_member->set_value( 'point', $point );
		$wc2_db_member->set_value( 'name1', trim( $_POST[$type]['name1'] ) );
		$wc2_db_member->set_value( 'name2', trim( $_POST[$type]['name2'] ) );
		$wc2_db_member->set_value( 'name3', trim( $_POST[$type]['name3'] ) );
		$wc2_db_member->set_value( 'name4', trim( $_POST[$type]['name4'] ) );
		$wc2_db_member->set_value( 'country', trim( $_POST[$type]['country'] ) );
		$wc2_db_member->set_value( 'zipcode', trim( $_POST[$type]['zipcode'] ) );
		$wc2_db_member->set_value( 'pref', trim( $_POST[$type]['pref'] ) );
		$wc2_db_member->set_value( 'address1', trim( $_POST[$type]['address1'] ) );
		$wc2_db_member->set_value( 'address2', trim( $_POST[$type]['address2'] ) );
		$wc2_db_member->set_value( 'tel', trim( $_POST[$type]['tel'] ) );
		$wc2_db_member->set_value( 'fax', trim( $_POST[$type]['fax'] ) );

		if( $type == 'member' ) {
			//csmb
			$csmb_keys = wc2_get_custom_field_keys(WC2_CSMB);
			if( !empty($csmb_keys) && is_array($csmb_keys) ){
				$csmb = array();
				foreach($csmb_keys as $key){
					list( $pfx, $csmb_key ) = explode('_', $key, 2);
					//$csmb_val = ( isset( $_POST[WC2_CUSTOM_MEMBER][$csmb_key] ) ) ? $_POST[WC2_CUSTOM_MEMBER][$csmb_key]: '';
					$csmb[$csmb_key] = ( isset( $_POST[WC2_CUSTOM_MEMBER][$csmb_key] ) ) ? $_POST[WC2_CUSTOM_MEMBER][$csmb_key]: '';
				}
				$wc2_db_member->set_value( WC2_CUSTOM_MEMBER, $csmb );
			}
			//meta
			//meta_typeあり
			//$wc2_db_member->set_meta_value($key, $value, $type);

			//meta_typeなし
			//$wc2_db_member->set_meta_value($key, $value);
		} elseif( $type == 'customer' ) {
			//cscs
			$cscs_keys = wc2_get_custom_field_keys(WC2_CSCS);
			if( !empty($cscs_keys) && is_array($cscs_keys) ){
				$cscs = array();
				foreach($cscs_keys as $key){
					list( $pfx, $cscs_key ) = explode('_', $key, 2);
					//$cscs_val = ( isset( $_POST[WC2_CUSTOM_CUSTOMER][$cscs_key] ) ) ? $_POST[WC2_CUSTOM_CUSTOMER][$cscs_key]: '';
					$cscs[$cscs_key] = ( isset( $_POST[WC2_CUSTOM_CUSTOMER][$cscs_key] ) ) ? $_POST[WC2_CUSTOM_CUSTOMER][$cscs_key]: '';
				}
				$wc2_db_member->set_value( WC2_CUSTOM_CUSTOMER, $cscs );
			}
			//meta
			//meta_typeあり
			//$wc2_db_member->set_meta_value($key, $value, $type);

			//meta_typeなし
			//$wc2_db_member->set_meta_value($key, $value);
		}
		do_action('wc2_action_new_member_data', $type);

		$res = $wc2_db_member->add_member_data();

		return $res;
	}

	public function edit_member_data($mem_id, $type = 'member'){
		global $wpdb;

		if( empty( $mem_id ) || !array_key_exists($type, $_POST) ){
			return false;
		}
		$wc2_db_member = WC2_DB_Member::get_instance();
		//$wc2_db_member->get_member_data($mem_id);
		$_POST = WC2_Utils::stripslashes_deep_post($_POST);

		$passwd  = ( is_admin() || (!is_admin() && wc2_is_blank($_POST[$type]['passwd']) && wc2_is_blank($_POST[$type]['passwd2'])) ) ? $wc2_db_member->get_value('passwd') : md5( trim($_POST[$type]['passwd']) );
		$point = ( is_admin() && isset($_POST['member']['point']) ) ? trim($_POST['member']['point']): $wc2_db_member->get_value('rank');
		$rank = ( is_admin() ) ? trim( $_POST['member']['rank'] ): $wc2_db_member->get_value('rank');

		$wc2_db_member->clear_column();
		$wc2_db_member->set_member_id($mem_id);

		$wc2_db_member->set_value( 'account', trim( $_POST[$type]['account'] ) );
		$wc2_db_member->set_value( 'email', trim( $_POST[$type]['email'] ) );
		$wc2_db_member->set_value( 'passwd', $passwd );
		$wc2_db_member->set_value( 'rank', $rank );
		$wc2_db_member->set_value( 'point', $point );
		$wc2_db_member->set_value( 'name1', trim( $_POST[$type]['name1'] ) );
		$wc2_db_member->set_value( 'name2', trim( $_POST[$type]['name2'] ) );
		$wc2_db_member->set_value( 'name3', trim( $_POST[$type]['name3'] ) );
		$wc2_db_member->set_value( 'name4', trim( $_POST[$type]['name4'] ) );
		$wc2_db_member->set_value( 'country', trim( $_POST[$type]['country'] ) );
		$wc2_db_member->set_value( 'zipcode', trim( $_POST[$type]['zipcode'] ) );
		$wc2_db_member->set_value( 'pref', trim( $_POST[$type]['pref'] ) );
		$wc2_db_member->set_value( 'address1', trim( $_POST[$type]['address1'] ) );
		$wc2_db_member->set_value( 'address2', trim( $_POST[$type]['address2'] ) );
		$wc2_db_member->set_value( 'tel', trim( $_POST[$type]['tel'] ) );
		$wc2_db_member->set_value( 'fax', trim( $_POST[$type]['fax'] ) );

		if( $type == 'member' ) {
			//csmb
			$csmb_keys = wc2_get_custom_field_keys(WC2_CSMB);
			if( !empty($csmb_keys) && is_array($csmb_keys) ){
				$csmb = array();
				foreach($csmb_keys as $key){
					list( $pfx, $csmb_key ) = explode('_', $key, 2);
					//$csmb_val = ( isset( $_POST[WC2_CUSTOM_MEMBER][$csmb_key] ) ) ? $_POST[WC2_CUSTOM_MEMBER][$csmb_key]: '';
					$csmb[$csmb_key] = ( isset( $_POST[WC2_CUSTOM_MEMBER][$csmb_key] ) ) ? $_POST[WC2_CUSTOM_MEMBER][$csmb_key]: '';
				}
				$wc2_db_member->set_value( WC2_CUSTOM_MEMBER, $csmb );
			}
			//meta
			//meta_typeあり
			//$wc2_db_member->set_meta_value($key, $value, $type);

			//meta_typeなし
			//$wc2_db_member->set_meta_value($key, $value);
		} elseif( $type == 'customer' ) {
			//cscs
			$cscs_keys = wc2_get_custom_field_keys(WC2_CSCS);
			if( !empty($cscs_keys) && is_array($cscs_keys) ){
				$cscs = array();
				foreach($cscs_keys as $key){
					list( $pfx, $cscs_key ) = explode('_', $key, 2);
					//$cscs_val = ( isset( $_POST[WC2_CUSTOM_CUSTOMER][$cscs_key] ) ) ? $_POST[WC2_CUSTOM_CUSTOMER][$cscs_key]: '';
					$cscs[$cscs_key] = ( isset( $_POST[WC2_CUSTOM_CUSTOMER][$cscs_key] ) ) ? $_POST[WC2_CUSTOM_CUSTOMER][$cscs_key]: '';
				}
				$wc2_db_member->set_value( WC2_CUSTOM_CUSTOMER, $cscs );
			}
			//meta
			//meta_typeあり
			//$wc2_db_member->set_meta_value($key, $value, $type);

			//meta_typeなし
			//$wc2_db_member->set_meta_value($key, $value);
		}

		do_action('wc2_action_edit_member_data', $mem_id, $type);

		$res = $wc2_db_member->update_member_data($mem_id);

		return $res;
	}

	public function member_history_rows(){
		$wc2_db_member = WC2_DB_Member::get_instance();
		$wc2_order = WC2_DB_Order::get_instance();
		$wc2_item = WC2_DB_Item::get_instance();

		if( is_admin() ){
			$member_id = $wc2_db_member->get_member_id();
		}else{
			if( wc2_is_login() && 0 != $_SESSION[WC2]['member']['ID'] ){
				$member_id = $_SESSION[WC2]['member']['ID'];
			}else{
				return false;
			}
		}

		$member_history = $wc2_order->get_member_history( $member_id );
        $colspan = wc2_is_membersystem_point() ? 9 : 7;

		$html = '<div class="history-area">
					<table>';
		if ( !count($member_history) ) {
			$html .= '<tr>
			<td>' . __('There is no your purchase history.', 'wc2') . '</td>
			</tr>';
		}
		foreach ( $member_history as $umhs ) {
			$cart = $umhs['cart'];
			$history_member_head = '<tr>
				<th class="historyrow">' . __('Order number', 'wc2') . '</th>
				<th class="historyrow">' . __('Order status', 'wc2') . '</th>
				<th class="historyrow">' . __('Purchase date', 'wc2') . '</th>
				<th class="historyrow">' . __('Purchase amount', 'wc2') . '</th>';
			if( wc2_is_membersystem_point() ){
				$history_member_head .= '<th class="historyrow">' . __('Used points', 'wc2') . '</th>';
			}
			$history_member_head .= '<th class="historyrow">' . apply_filters( 'wc2_filter_discount_label', __('Discount', 'wc2'), $umhs ) . '</th>
				<th class="historyrow">' . __('Shipping charges', 'wc2') . '</th>
				<th class="historyrow">' . apply_filters( 'wc2_filter_cod_label', __('COD fee', 'wc2') ) . '</th>
				<th class="historyrow">' . __('Consumption tax', 'wc2') . '</th>';
			if( wc2_is_membersystem_point() ){
				$history_member_head .= '<th class="historyrow">' . __('Acquired points', 'wc2') . '</th>';
			}
			$history_member_head .= '</tr>
				<tr>
				<td class="rightnum"><a href="'.WC2_ADMIN_URL.'?page=wc2_order&action=edit&target='.$umhs['ID'].'">' . $umhs['dec_order_id'] . '</a></td>
				<td class="aleft">'. wc2_get_order_status_name($umhs['order_status']) .'</td>
				<td class="date">' . $umhs['order_date'] . '</td>
				<td class="rightnum">' .wc2_crform(($umhs['item_total_price']-$umhs['usedpoint']+$umhs['discount']+$umhs['shipping_charge']+$umhs['cod_fee']+$umhs['tax']), true, false ) . '</td>';
			if( wc2_is_membersystem_point() ){
				$history_member_head .= '<td class="rightnum">' . number_format($umhs['usedpoint']) . '</td>';
			}
			$history_member_head .= '<td class="rightnum">' . wc2_crform($umhs['discount'], true, false) . '</td>
				<td class="rightnum">' . wc2_crform($umhs['shipping_charge'], true, false) . '</td>
				<td class="rightnum">' . wc2_crform($umhs['cod_fee'], true, false) . '</td>
				<td class="rightnum">' . wc2_crform($umhs['tax'], true, false) . '</td>';
			if( wc2_is_membersystem_point() ){
				$history_member_head .= '<td class="rightnum">' . number_format($umhs['getpoint']) . '</td>';
			}
			$history_member_head .= '</tr>';
			$html .= apply_filters( 'wc2_filter_history_member_head', $history_member_head, $umhs );
			$html .= apply_filters('wc2_filter_member_history_header', NULL, $umhs);
			$html .= '<tr>
				<td class="retail" colspan="' . $colspan . '">
					<table id="retail_table_' . $umhs['ID'] . '" class="retail">';
			$history_cart_head = '<tr>
					<th scope="row" class="num">No.</th>
					<th class="thumbnail">&nbsp;</th>
					<th>' . __('Items', 'wc2') . '</th>
					<th class="price ">' . __('Unit price', 'wc2') . '(' . wc2_crcode() . ')' . '</th>
					<th class="quantity">' . __('Quantity', 'wc2') . '</th>
					<th class="subtotal">' . __('Amount', 'wc2') . '(' . wc2_crcode() . ')' . '</th>
					</tr>';
			$html .= apply_filters('wc2_filter_history_cart_head', $history_cart_head, $umhs);

			$i = 1;
			foreach( $cart as $cart_row ){
				$ordercart_id = $cart_row['cart_id'];
				$post_id = $cart_row['post_id'];
				$item_id = $cart_row['item_id'];
				$sku_id = $cart_row['sku_id'];
				$quantity = $cart_row['quantity'];
				$options = isset( $cart_row['options'] ) ? $cart_row['options']: '';
				//$options = wc2_get_ordercart_meta_value( 'option', $ordercart_id );
				//$options = wc2_get_ordercart_meta( 'option', $ordercart_id );
				$item_name = $cart_row['item_name'];
				$item_code = $cart_row['item_code'];
				$sku_name = $cart_row['sku_name'];
				$sku_code = $cart_row['sku_code'];

				$cart_item_name = wc2_get_cart_item_name( $item_name, $item_code, $sku_name, $sku_code );
				$skuPrice = $cart_row['price'];
				$pictid = (int)$wc2_item->get_mainpictid($item_code);

				$optstr =  '';
				if( is_array($options) && count($options) > 0 ){
					$optstr = '';
					foreach($options as $key => $value){
						if( !empty($key) ) {
							$key = urldecode($key);
							$value = maybe_unserialize($value);
							if(is_array($value)) {
								$c = '';
								$optstr .= esc_html($key) . ' : '; 
								foreach($value as $v) {
									$optstr .= $c.nl2br(esc_html(urldecode($v)));
									$c = ', ';
								}
								$optstr .= "<br />\n"; 
							} else {
								$optstr .= esc_html($key) . ' : ' . nl2br(esc_html(urldecode($value))) . "<br />\n"; 
							}
						}
					}
					$optstr = apply_filters( 'wc2_filter_option_history', $optstr, $options);
				}
				$optstr = apply_filters( 'wc2_filter_option_info_history', $optstr, $umhs, $cart_row, $i );

				$permalink = apply_filters( 'wc2_filter_link_item_history', get_permalink($post_id), $cart_row );
				$history_cart_row = '<tr>
					<td>' . ($i) . '</td>
					<td>';
				if( $pictid ){
					$cart_thumbnail = '<a href="' . esc_url($permalink) . '">' . wp_get_attachment_image( $pictid, array(60, 60), true ) . '</a>';
				}else{
					$cart_thumbnail = '<p>'. wc2_no_image() .'</p>';
				}
				$history_cart_row .= apply_filters('wc2_filter_cart_thumbnail', $cart_thumbnail, $post_id, $pictid, $i, $cart_row);
				$history_cart_row .= '</td>
					<td class="aleft"><a href="' . esc_url($permalink) . '">' . esc_html($cart_item_name) . '<br />' . $optstr . '</a>' . apply_filters('wc2_filter_history_item_name', NULL, $umhs, $cart_row, $i) . '</td>
					<td class="rightnum">' . wc2_crform($skuPrice, true, false) . '</td>
					<td class="rightnum">' . number_format($cart_row['quantity']) . '</td>
					<td class="rightnum">' . wc2_crform($skuPrice * $cart_row['quantity'], true, false) . '</td>
					</tr>';
				$materials = compact( 'cart_thumbnail', 'post_id', 'pictid', 'cart_item_name', 'optstr' );
				$html .= apply_filters( 'wc2_filter_history_cart_row', $history_cart_row, $umhs, $cart_row, $i, $materials );
				$i++;
			}

			$html .= '</table>
				</td>
				</tr>';
		}

		$html .= '</table>
		</div>';

		$html = apply_filters('wc2_filter_member_history_rows', $html, $member_id, $member_history, $colspan );

		return $html;
	}

	//パスワード制限表記文字数
	function member_password_rule(){
		$general = wc2_get_option('general');
		$member_pass_rule_min = isset($general['member_pass_rule_min']) ? $general['member_pass_rule_min']: '';
		$member_pass_rule_max = isset($general['member_pass_rule_max']) ? $general['member_pass_rule_max']: '';
		if( empty($member_pass_rule_min) )
			return;
		if( empty($member_pass_rule_max) ){
			$rule = $member_pass_rule_min .'文字以上';
		}else{
			$rule = $member_pass_rule_min .'文字以上 '. $member_pass_rule_max .'文字以下';
		}
		return $rule;
	}
}

/***** template tag *****/
function wc2_member_check($type, $member_id = ''){
	$wc2_member_func = WC2_Member_Func::get_instance();
	return $wc2_member_func->member_check($type, $member_id);
}

function wc2_new_member_data( $type = 'member' ){
	$wc2_member_func = WC2_Member_Func::get_instance();
	return $wc2_member_func->new_member_data( $type );
}

function wc2_edit_member_data($mem_id, $type = 'member'){
	$wc2_member_func = WC2_Member_Func::get_instance();
	return $wc2_member_func->edit_member_data($mem_id, $type);
}

function wc2_member_history_rows_e(){
	$wc2_member_func = WC2_Member_Func::get_instance();
	echo $wc2_member_func->member_history_rows();
}

function wc2_member_password_rule(){
	$wc2_member_func = WC2_Member_Func::get_instance();
	echo $wc2_member_func->member_password_rule();
}