<?php

class WC2_Funcs
{
	/**
	 * Get Welcart option value by key,'option_name'.
	 * And set global $wc2_options.
	 * If $wc2_options has key, return $wc2_options[key].
	 *
	 * @param strings
	 * @return strings|array
	 */

	static function get_checked( $chk, $key ) {
		$checked = ( isset($chk[$key]) and $chk[$key] == 1 ) ? ' checked="checked"' : '';
		return $checked;
	}

	static function get_custom_field_keys( $prefix, $position = NULL  ){
		$wc2_options = wc2_get_option();
		$keys = array();
		foreach((array)$wc2_options as $key => $value){
			if( 0 !== strpos( $key, $prefix ) )
				continue;

			if( $position != NULL && $position != $value['position'] )
				continue;

			if( !is_admin() ){
				if( 'admin' == $value['capa'] )
					continue;
			}

			$keys[] = $key;
		}
		return $keys;
	}

	static function get_custom_field_value( $custom_key, $array_key = 'value' ){
		if( empty($custom_key) ){
			return;
		}
		$entry = wc2_get_option($custom_key);
		if( !array_key_exists($array_key, $entry) ){
			return;
		}
		return $entry[$array_key];
	}

	/**********************************
		国ごとの住所様式を振り分け
	**********************************/
	static function get_apply_addressform($country){
		$locale_options = wc2_get_option('locale_options');
		return $locale_options['addressform'][$country];
	}

	/*********************************
		共通アドレスフォーム

	Memo: 管理画面の$dataの形式が変更。metaも含まれるので修正
	
	*********************************/
	static function get_addressform( $data, $type ){
		if( empty($data) ){
			return;
		}

		$system_options = wc2_get_option( 'system' );
		$applyform = wc2_get_apply_addressform( $system_options['addressform'] );

		if( is_admin() ){
			switch($type){
			case 'member':
			case 'customer':
				$values = $data;
				break;
			case 'delivery':
				$values = $data['delivery'][0];
				break;
			}
			$essential_mark_off = ( $type == 'member' ) ? false : true;

		}else{
			switch($type){
			case 'member':
				$values = $data;
				break;
			case 'customer':
				$values = $data['customer'];
				break;
			case 'delivery':
				$values = $data['delivery'];
				break;
			}
			$essential_mark_off = false;
		}
		$values['country'] = !empty($values['country']) ? $values['country'] : wc2_get_local_addressform();
		$values = wc2_stripslashes_deep_post($values);

		$essential_mark = wc2_get_option( 'essential_mark' );
		if( $essential_mark_off ) {
			foreach( $essential_mark as $key => $value ) {
				$essential_mark[$key] = '';
			}
		}

		$formtag = '';

		switch($applyform){
		case 'JP': 
			$formtag .= self::get_custom_field_input($values, $type, 'head');
			if( is_admin() && ( $type == 'member' || $type == 'customer') ){
				$get_member = ( $type == 'customer') ? '<input type="button" id="get-member" class="button" value="'.__('会員情報取込', 'wc2').'" />' : '';
				$formtag .= '
				<tr class="'.$type.'-mail">
					<th>'. $essential_mark['email'] .'e-mail</th>
					<td><input name="'. $type .'[email]" type="text" class="text long" value="'. esc_attr($values['email']) .'" />'.$get_member. apply_filters('wc2_filter_after_email', NULL, $values, $type ) .'</td>
				</tr>';
			}
			$formtag .= self::get_custom_field_input($values, $type, 'beforename');
			$formtag .= '
			<tr class="'.$type.'-name">
				<th>'. $essential_mark['name1'] . __('Name', 'wc2') .'</th>
				<td>
					<input name="'. $type .'[name1]" type="text" class="text short" value="'. esc_attr($values['name1']) .'" placeholder="'. __('Last name', 'wc2') .'" />
					<input name="'. $type .'[name2]" type="text" class="text short" value="'. esc_attr($values['name2']) .'" placeholder="'. __('First name', 'wc2') .'" />'. apply_filters('wc2_filter_after_name2', NULL, $values, $type ) .'
				</td>
			</tr>
			<tr class="'.$type.'-phonetic">
				<th>' . $essential_mark['name3'] .__('Kana', 'wc2') .'</th>
				<td>
					<input name="'. $type .'[name3]" type="text" class="text short" value="'. esc_attr($values['name3']) .'" placeholder="'. __('セイ', 'wc2') .'" />
					<input name="'. $type .'[name4]" type="text" class="text short" value="'. esc_attr($values['name4']) .'" placeholder="'. __('メイ', 'wc2') .'" />'. apply_filters('wc2_filter_after_name4', NULL, $values, $type ) .'
				</td>
			</tr>';
			$formtag .= self::get_custom_field_input($values, $type, 'aftername');

			if( count($system_options['target_market']) == 1 ){
				$formtag .= '<input name="'. $type .'[country]" type="hidden" value="'. $system_options['target_market'][0] .'" />';
			}else{
				$formtag .= '
					<tr class="'.$type.'-country">
						<th>' . $essential_mark['country'] .__('国', 'wc2') .'</th>
						<td>'. wc2_get_target_market_form( $type, $values['country'] ) . apply_filters('wc2_filter_after_country', NULL, $values, $type ) .'</td>
					</tr>';
			}
			$formtag .= '
			<tr class="'.$type.'-zipcode">
				<th>' . $essential_mark['zipcode'] .__('郵便番号', 'wc2') .'</th>
				<td>
					<input name="'. $type .'[zipcode]" type="text" class="text short" value="'. esc_attr($values['zipcode']) .'" />
					<input id="search-zipcode-'.$type.'" type="button" class="search-zipcode button" value="住所検索" />'. apply_filters('wc2_filter_after_zipcode', NULL, $values, $type ) .'
				</td>
			</tr>
			<tr class="'.$type.'-pref">
				<th>'. $essential_mark['pref'] . __('都道府県', 'wc2') .'</th>
				<td>'. wc2_get_pref_select( $type, $values ) . apply_filters('wc2_filter_after_pref', NULL, $values, $type ) .'</td>
			</tr>
			<tr class="'.$type.'-address1">
				<th>'. $essential_mark['address1'] . __('City/Ward/Town/Village/Street name, street number', 'wc2') .'</th>
				<td><input name="'. $type .'[address1]" type="text" class="text long" value="'. esc_attr($values['address1']) .'" />'. apply_filters('wc2_filter_after_address1', NULL, $values, $type ) .'</td>
			</tr>
			<tr class="'.$type.'-address2">
				<th>'. $essential_mark['address2'] . __('Building name, floor, room number', 'wc2') .'</th>
				<td><input name="'. $type .'[address2]" type="text" class="text long" value="'. esc_attr($values['address2']) .'" />'. apply_filters('wc2_filter_after_address2', NULL, $values, $type ) .'</td>
			</tr>
			<tr class="'.$type.'-tel">
				<th>'. $essential_mark['tel'] . __('電話番号', 'wc2') . '</th>
				<td><input name="'. $type .'[tel]" type="text" class="text short" value="'. esc_attr($values['tel']) .'" />'. apply_filters('wc2_filter_after_tel', NULL, $values, $type ) .'</td>';
				$formtag .= '
				</td>
			</tr>
			<tr class="'.$type.'-fax">
				<th>'. $essential_mark['fax'] . __('FAX番号', 'wc2') .'</th>
				<td><input name="'. $type .'[fax]" type="text" class="text short" value="'. esc_attr($values['fax']) .'" />'. apply_filters('wc2_filter_after_fax', NULL, $values, $type ) .'</td>
			</tr>';

			$formtag .= self::get_custom_field_input($values, $type, 'bottom');
			break;
		}

		return $formtag;
	}

	/***************************************
		カスタムフィールド インプット (共用)
	****************************************/
	static function get_custom_field_input( $data, $type, $position ){
		switch($type){
		case 'order':
			$label = WC2_CUSTOM_ORDER;
			$prefix = WC2_CSOD;
			break;
		case 'customer':
			$label = WC2_CUSTOM_CUSTOMER;
			$prefix = WC2_CSCS;
			break;
		case 'delivery':
			$label = WC2_CUSTOM_DELIVERY;
			$prefix = WC2_CSDE;
			break;
		case 'member':
			$label = WC2_CUSTOM_MEMBER;
			$prefix = WC2_CSMB;
			break;
		default:
			return;
		}

		$html = '';
		$keys = self::get_custom_field_keys( $prefix, $position );

		if( is_admin() ) {
			$essential_mark_off = ( $type == 'member' ) ? false : true;
		} else {
			$essential_mark_off = false;
		}

		if(!empty($keys) && is_array($keys)) {
			foreach($keys as $key){
				$entry = wc2_get_option( $key );
				$name = $entry['name'];
				$means = $entry['means'];
				$essential = $entry['essential'];
				$value = $entry['value'];
				$cstm_data = '';	//customfield value

				list( $pfx, $cskey ) = explode( '_', $key, 2 );
				if( is_array($data) && isset($data[$label]) && !empty($data[$label]) ){
					$cstm_data = (isset($data[$label][$cskey])) ? $data[$label][$cskey] : '';
				}

				$row = '';
				if( $essential == 1 and !$essential_mark_off ){
					$row .= '
						<tr class="'.$type.'-'.$cskey.'">
							<th><span class="required">'. __('*', 'wc2') .'</span>'. esc_html($name) .'</th>';
				}else{
					$row .= '
						<tr class="'.$type.'-'.$cskey.'">
							<th>'. esc_html($name) .'</th>';
				}

				switch($means) {
				case 'select'://シングルセレクト
					$selects = explode("\n", $value);
					$multiple = ($means == 'select') ? '' : ' multiple';
					$multiple_array = ($means == 'select') ? '' : '[]';
					$row .= '
						<td>
						<select name="'.$label.'['.esc_attr($cskey).']'.$multiple_array.'" id="'.$label.'['.esc_attr($cskey).']" class="'.esc_attr($cskey).'"'.$multiple.'>';
					if($essential == 1)
						$row .= '
							<option value="#NONE#">'.__('-- Select --','wc2').'</option>';
					foreach($selects as $v) {
						$selected = ($cstm_data == $v) ? ' selected="selected"' : '';
						$row .= '
							<option value="'.esc_attr($v).'"'.$selected.'>'.esc_html($v).'</option>';
					}
					$row .= '
						</select></td>';
					
					break;

				case 'text'://テキスト
					$row .= '
						<td><input type="text" name="'.$label.'['.esc_attr($cskey).']" id="'.$label.'['.esc_attr($cskey).']" class="'.esc_attr($cskey).' text long" value="'.esc_attr($cstm_data).'" /></td>';
					break;

				case 'radio'://ラジオボタン
					$selects = explode("\n", $value);
					$row .= '
						<td class="horizontal">';
					foreach($selects as $v) {
						$checked = ($cstm_data == $v) ? ' checked="checked"' : '';
						$row .= '
						<label title="'.esc_attr($v).'"><input type="radio" name="'.$label.'['.esc_attr($cskey).']" id="'.$label.'['.esc_attr($cskey).']['.esc_attr($v).']" class="'.esc_attr($cskey).'" value="'.esc_attr($v).'"'.$checked.' /><span>'.esc_html($v).'</span></label>';
					}
					$row .= '
						</td>';
					break;

				case 'check'://チェックボックス
					$selects = explode("\n", $value);
					$row .= '
						<td>';
					foreach($selects as $v) {
						if(is_array($cstm_data)) {
							$checked = (array_key_exists($v, $cstm_data)) ? ' checked="checked"' : '';
						} else {
							$checked = ($cstm_data == $v) ? ' checked="checked"' : '';
						}
						$row .= '
						<label for="'.$label.'['.esc_attr($cskey).']['.esc_attr($v).']"><input type="checkbox" name="'.$label.'['.esc_attr($cskey).']['.esc_attr($v).']" id="'.$label.'['.esc_attr($cskey).']['.esc_attr($v).']" class="'.esc_attr($cskey).'" value="'.esc_attr($v).'"'.$checked.' />'.esc_html($v).'</label>';
					}
					$row .= '
						</td>';
					break;

				case 'textarea':
					$row .= '
						<td><textarea name="'.$label.'['.esc_attr($cskey).']" id="'.$label.'['.esc_attr($cskey).']" class="'.esc_attr($cskey).'">'. esc_attr($cstm_data). '</textarea></td>';
					break;
				}
				$row .= '
					</tr>';

				$html .= apply_filters('wc2_filter_custom_field_input_inside', $row, $type, $entry, $cstm_data, $label, $cskey, $essential_mark_off);
			}
		}
		$html = apply_filters( 'wc2_filter_custom_field_input', $html, $data, $type, $position );
		return $html;
	}

	/********************************************
		カスタムフィールド出力 ( 内容確認ページ )
	*********************************************/
	static function get_custom_field_info( $data, $type, $position ){
		switch($type) {
			case 'order':
				$label = WC2_CUSTOM_ORDER;
				$prefix = WC2_CSOD;
				break;
			case 'customer':
				$label = WC2_CUSTOM_CUSTOMER;
				$prefix = WC2_CSCS;
				break;
			case 'delivery':
				$label = WC2_CUSTOM_DELIVERY;
				$prefix = WC2_CSDE;
				break;
			case 'member':
				$label = WC2_CUSTOM_MEMBER;
				$prefix = WC2_CSMB;
				break;
			default:
				return;
		}

		$html = '';
		$keys = self::get_custom_field_keys( $prefix, $position );
		if(!empty($keys) && is_array($keys)){
			foreach($keys as $key) {
				$entry = wc2_get_option( $key );
				$name = $entry['name'];
				$means = $entry['means'];
				$essential = $entry['essential'];
				$value = $entry['value'];
				$cstm_data = '';
				list( $pfx, $cskey ) = explode( '_', $key, 2 );
				//$cskey = substr_replace( $key , '', 0, 5 );
				if( is_array($data) && !empty($data[$label]) ) {
					$cstm_data = (isset($data[$label][$cskey])) ? $data[$label][$cskey] : '';
				}

				$row = '';
				$row .= '
					<tr class="'.$type.'-'.$cskey.'">
						<th>'. esc_html($name) .'</th>
						<td>';
				if(!empty($cstm_data)){
					switch($means){
						case 'select':
						case 'text':
						case 'radio':
						case 'textarea':
							$row .= esc_html($cstm_data);
							break;
						case 'multi':
						case 'check':
							if(is_array($cstm_data)) {
								$c = '';
								foreach($cstm_data as $v) {
									$row .= $c.esc_html($v);
									$c = ', ';
								}
							} else {
								if(!empty($cstm_data)) $row .= esc_html($cstm_data);
							}
							break;
					}
				}
				$row .= '
						</td>
					</tr>';
			}
			$html .= apply_filters('wc2_filter_custom_field_info_inside', $row, $type, $entry, $cstm_data, $label, $cskey);
		}
		$html = apply_filters('wc2_filter_custom_field_info', $html, $data, $type, $position);

		return $html;
	}

	static function get_essential_mark( $fielde, $data = NULL ){
		$essential_mark = wc2_get_option( 'essential_mark' );
		do_action('wc2_action_essential_mark', $data, $fielde);
		return $essential_mark[$fielde];
	}

	/****************************
	* 
	*****************************/
	static function get_pref_select( $type, $values ){
		$system_options = wc2_get_option('system');

		$country = empty($values['country']) ? wc2_get_base_country() : $values['country'];
		if( !in_array($country, $system_options['target_market']) )
			$country = $system_options['target_market'][0];
		$prefs = WC2_Funcs::get_states($country);
		$html = '<select name="'.esc_attr($type.'[pref]').'" id="'.esc_attr($type).'_pref" class="pref">';
		$prefs_count = count($prefs);
		if($prefs_count > 0) {
			$select = __('-- 選択 --', 'wc2');
			$html .= '<option value="'. esc_attr(WC2_UNSELECTED) .'">'.esc_html($select)."</option>\n";
			for($i = 0; $i < $prefs_count; $i++) 
				$html .= '<option value="'.esc_attr($prefs[$i]).'"'.($prefs[$i] == $values['pref'] ? ' selected="selected"' : '').'>'.esc_html($prefs[$i])."</option>\n";
		}
		$html .= "</select>\n";
	
		return $html;
	}

	/****************************
	* 
	*****************************/
	static function get_local_addressform(){
		$locale_options = wc2_get_option('locale_options');
		$base = self::get_base_country();
		if( array_key_exists($base, $locale_options['addressform']) )
			return $locale_options['addressform'][$base];
		else
			return 'US';
	}

	/****************************
	* WPのdefineからベース言語を取得
	*****************************/
	static function get_base_country(){
		$locale_options = wc2_get_option('locale_options');
		$locale = get_locale();
		$wplang = defined('WPLANG') ? WPLANG : '';
		$locale = empty( $wplang ) ? 'en' : $wplang;
		if( array_key_exists($locale, $locale_options['lungage2country']) ){
			return $locale_options['lungage2country'][$locale];
		}else{
			return 'US';
		}
	}

	/***************************
		販売対象国を取得
	****************************/
	static function get_local_target_market(){
		$base = self::get_base_country();
		return (array)$base;
	}

	/****************************
	* 州や都道府県を取得
	*****************************/
	static function get_states($country) {
		$system_options = wc2_get_option('system');

		$states = $system_options['province'][$country];
	/*
		$states = array();
		$prefs = maybe_unserialize($wc2->options['province']);
		if( !isset($prefs[$country]) || empty($prefs[$country]) ) {
			if($country == $wc2->options['system']['base_country']) {
				foreach((array)$prefs as $state) {
					if(!is_array($state))
						array_push($states, $state);
				}
				if(count($states) == 0) {
					if( !empty($wc2_states[$country]) ) {
						$prefs = $wc2_states[$country];
						if(is_array($prefs)) {
							$states = $prefs;
						}
					}
				}
			} else {
				if( !empty($wc2_states[$country]) ) {
					$prefs = $wc2_states[$country];
					if(is_array($prefs)) {
						$states = $prefs;
					}
				}
			}
		} else {
			$states = $prefs[$country];
		}
	*/
		return $states;
	}

	/****************************
	* 入力フォームの国のセレクト
	*****************************/
	static function get_target_market_form( $type, $selected ){
		$locale_options = wc2_get_option('locale_options');
		$system_options = wc2_get_option('system');

		$res = '<select name="'.$type.'[country]" id="'.$type.'_country">';
		foreach ( $locale_options['country'] as $key => $value ){
			if( in_array($key, $system_options['target_market']) )
				$res .= '<option value="'.$key.'"'.($selected == $key ? ' selected="selected"' : '').'>'.$value.'</option>';
		}
		$res .= '</select>';

		return $res;
	}

	/****************************
	* 配送方法名を取得
	*****************************/
	static function get_delivery_method_name( $id ){
		$delivery_options = wc2_get_option('delivery');
		if($id > -1){
			$index = self::get_delivery_method_index($id);
			$name = $delivery_options['delivery_method'][$index]['name'];
		}else{
			$name = __('指定しない','wc2');
		}
		return $name;
	}

	/****************************
	* 配送方法のindexを取得
	*****************************/
	static function get_delivery_method_index( $id ) {
		$delivery_options = wc2_get_option('delivery');
		$index = false;
		for($i=0; $i<count($delivery_options['delivery_method']); $i++){
			if( $delivery_options['delivery_method'][$i]['id'] === (int)$id ){
				$index = $i;
			}
		}
		if($index === false)
			return -1;
		else
			return $index;
	}

	/**************************
	* 金額をベース言語に合わせてフォーマット
	***************************/
	static function get_currency($amount, $symbol_pre = false, $symbol_post = false, $seperator_flag = true ){
		$locale_options = wc2_get_option('locale_options');
		$system_options = wc2_get_option('system');
		$cr = $system_options['currency'];
		list($code, $decimal, $point, $seperator, $symbol) = $locale_options['currency'][$cr];
		if( !$seperator_flag ){
			$seperator = '';
		}
		$price = number_format((double)$amount, $decimal, $point, $seperator);
		if( $symbol_pre )
			$price = ( WC2_Utils::is_entity($symbol) ? mb_convert_encoding($symbol, 'UTF-8', 'HTML-ENTITIES') : $symbol ).$price;
			
		if( $symbol_post )
			$price = $price.__($code, 'wc2');

		return $price;
	}

	/****************************
	* 通貨表示国
	*****************************/
	static function get_currency_code(){
		$locale_options = wc2_get_option('locale_options');
		$system_options = wc2_get_option('system');
		$cr = $system_options['currency'];
		list($code, $decimal, $point, $seperator, $symbol) = $locale_options['currency'][$cr];
		return $code;
	}

	/****************************
	* 通貨マーク
	*****************************/
	static function get_crsymbol( $js = NULL ) {
		$res = self::getCurrencySymbol();
		if( 'js' === $js && '&yen;' == $res ){
			$res = mb_convert_encoding($res, 'UTF-8', 'HTML-ENTITIES');
		}
		return $res;
	}

	/****************************
	* 
	****************************/
	static function getCurrencySymbol(){
		$locale_options = wc2_get_option('locale_options');
		$system_options = wc2_get_option('system');
		$cr = $system_options['currency'];
		list($code, $decimal, $point, $seperator, $symbol) = $locale_options['currency'][$cr];
		return $symbol;
	}

	/****************************
	* 配送設定の国セレクト
	*****************************/
	static function get_shipping_country_option( $selected = ''){
		$locale_options = wc2_get_option('locale_options');
		$system_options = wc2_get_option('system');
		$res = '';
		foreach ( $locale_options['country'] as $key => $value ){
			if( in_array($key, $system_options['target_market']) )
				$res .= '<option value="'.$key.'"'.($selected == $key ? ' selected="selected"' : '').'>'.$value."</option>\n";
		}
		return $res;
	}

	/******************************
	* 拡張プラグイン
	*******************************/
	static function get_wcex(){
		$wcex = array();
		if( defined('WCEX_DLSELLER_VERSION'))
			$wcex['DLSELLER'] = array('name'=>'Dl Seller', 'version'=>WCEX_DLSELLER_VERSION);
		if( defined('WCEX_ITEM_LIST_LAYOUT_VERSION'))
			$wcex['ITEM_LIST_LAYOUT'] = array('name'=>'Item List Layout', 'version'=>WCEX_ITEM_LIST_LAYOUT_VERSION);
		if( defined('WCEX_MOBILE_VERSION'))
			$wcex['MOBILE'] = array('name'=>'Mobile', 'version'=>WCEX_MOBILE_VERSION);
		if( defined('WCEX_MULTIPRICE_VERSION'))
			$wcex['MULTIPRICE'] = array('name'=>'Multi Price', 'version'=>WCEX_MULTIPRICE_VERSION);
		if( defined('WCEX_SLIDE_SHOWCASE_VERSION'))
			$wcex['SLIDE_SHOWCASE'] = array('name'=>'Slide Showcase', 'version'=>WCEX_SLIDE_SHOWCASE_VERSION);
		if( defined('WCEX_WIDGET_CART_VERSION'))
			$wcex['WIDGET_CART'] = array('name'=>'Widget Cart', 'version'=>WCEX_WIDGET_CART_VERSION);
		
		return $wcex;
	}
/*
	/****************************
	* postmetaのデータを取得
	*****************************
	static function wc2_get_post_meta( $post_id, $key ) {
		global $wpdb;

		$res = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $post_id, $key), ARRAY_A);
		return $res;
	}

	/****************************
	* 商品のオプション取得
	****************************
	static function wc2_get_opts( $post_id, $keyflag = 'sort' ) {
		$opts = array();
		$metas = WC2_Funcs::wc2_get_post_meta($post_id, '_iopt_');

		if( empty($metas) ) return $opts;

		foreach( $metas as $rows ){
			$values = unserialize($rows['meta_value']);
			$key = isset($values[$keyflag]) ? $values[$keyflag] : $values['sort'];
			$opts[$key] = array(
								'meta_id' => $rows['meta_id'],
								'name' => $values['name'],
								'means' => $values['means'],
								'essential' => $values['essential'],
								'value' => $values['value'],
								'sort' => $values['sort']
							);
		}
		ksort($opts);

		return $opts;
	}

	/****************************
	* 商品オプション追加
	*****************************
	static function wc2_add_opt( $post_id, $newvalue, $check = true ) {
		global $wpdb;
		if( $check ){
			$data = wc2_get_item_data();
			$metas = WC2_Funcs::wc2_get_post_meta($post_id, '_iopt_');
			if( !empty($metas) ){
				$meta_num = count($metas);
				$unique = true;
				$sortnull = true;
				foreach( $metas as $meta ){
					$values = unserialize($meta['meta_value']);
					if( $values['name'] == $newvalue['name'] )
						$unique = false;
					if( !isset($values['sort']) )
						$sortnull = false;
					$sort[] = $values['sort'];
				}
				if( !$unique )
					return -1;

				rsort($sort);
				$next_number = reset($sort) + 1;
				$unique_sort = array_unique($sort);
				if( $meta_num !== count($unique_sort) || $meta_num !== $next_number || !$sortnull){
					$i = 0;
					foreach( $metas as $rows ){
						$values = unserialize($rows['meta_value']);
						$values['sort'] = $i;
						$serialized_values = serialize($values);
						$wpdb->query( $wpdb->prepare("UPDATE $wpdb->postmeta SET meta_value = %s WHERE meta_id = %d", $serialized_values, $rows['meta_id']) );
						$i++;
					}
				}
			}
			$newvalue['sort'] = !empty($meta_num) ? $meta_num : 0;
		}
		$serialized_newvalue = serialize($newvalue);
		$wpdb->query( $wpdb->prepare("INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value ) VALUES (%d, '_iopt_', %s)", $post_id, $serialized_newvalue) );
		$id = $wpdb->insert_id;
		return $id;
	}


	/***************************
	* 商品オプション
	****************************
	static function list_item_option_meta( $opts ) {

		//商品にオプションが定められていない場合は表示なし
		if ( ! $opts ) {
			?>
			<table id="optlist-table" class="list" style="display: none;">
				<thead>
				<tr>
					<th class="hanldh">　</th>
					<th class="item-opt-key"><?php _e('オプション名','wc2') ?></th>
					<th class="item-opt-value"><?php _e('セレクト値','wc2') ?></th>
				</tr>
				</thead>
				<tbody id="item-opt-list">
				<tr><td></td></tr>
				</tbody>
			</table>
			<?php
		//商品にオプションが定められている場合はそのオプションを表示しておく
		}else{
			?>
			<table id="optlist-table" class="list">
				<thead>
				<tr>
					<th class="hanldh">　</th>
					<th class="item-opt-key"><?php _e('オプション名','wc2') ?></th>
					<th class="item-opt-value"><?php _e('セレクト値','wc2') ?></th>
				</tr>
				</thead>
				<tbody id="item-opt-list">
			<?php
				foreach ( $opts as $opt )
					echo self::_list_item_option_meta_row( $opt );
			?>
				</tbody>
			</table>
			<?php
		}
	}

	/****************************
	* 商品オプション
	*****************************
	static function _list_item_option_meta_row( $opt ) {
		$r = '';
		$style = '';
		$general_options = wc2_get_option('general');
		$means = wc2_get_option('wc2_item_option_select');
		$name = esc_attr($opt['name']);
		$meansoption = '';

		foreach($means as $meankey => $meanvalue){
			if($meankey == $opt['means']) {
				$selected = ' selected="selected"';
			}else{
				$selected = '';
			}
			$meansoption .= '<option value="'.esc_attr($meankey).'"'.$selected.'>'.esc_html($meanvalue)."</option>\n";
		}
		$essential = $opt['essential'] == 1 ? ' checked="checked"' : '';
		$value = '';
		if(is_array($opt['value'])){
			foreach($opt['value'] as $k => $v){
				$value .= $v."\n";
			}
		}else{
			//$value = esc_attr(trim($opt['value']));
			$value = $opt['value'];
		}
		$value = trim($value);
		$id = (int) $opt['meta_id'];
		$sort = (int) $opt['sort'];

		ob_start();
		?>
		<tr class="metastuffrow"><td colspan="3">
			<table id="itemopt-<?php echo $id; ?>" class="metastufftable">
				<tr>
					<th class='handlb' rowspan='2'>　</th>
					<td class='item-opt-key'>
						<div><input name='itemopt[<?php echo $id; ?>][name]' id='itemopt[<?php echo $id; ?>][name]' class='metaboxfield' type='text' size='20' value='<?php echo $name; ?>' /></div>
						<div class='optcheck'>
							<select name='itemopt[<?php echo $id; ?>][means]' id='itemopt[<?php echo $id; ?>][means]'><?php echo $meansoption; ?></select>
							<label for='itemopt[<?php echo $id; ?>][essential]'><input name='itemopt[<?php echo $id; ?>][essential]' id='itemopt[<?php echo $id; ?>][essential]' type='checkbox' value='1'<?php echo $essential; ?> class='metaboxcheckfield' /><?php _e('必須項目','wc2'); ?></label>
						</div>
					</td>
					<td class='item-opt-value'>
						<textarea name='itemopt[<?php echo $id; ?>][value]' id='itemopt[<?php echo $id; ?>][value]' class='metaboxfield'><?php echo esc_html($value); ?></textarea>
					</td>
				</tr>
				<tr>
					<td colspan='2' class='submittd'>
						<div id='itemoptsubmit-<?php echo $id; ?>' class='submit'>
							<input name='deleteitemopt[<?php echo $id; ?>]' id='deleteitemopt[<?php echo $id; ?>]' type='button' value='<?php esc_attr_e(__( '削除' )); ?>' onclick="if( jQuery('#post_ID').val() < 0 ) return; itemOpt.post('deleteitemopt', <?php echo $id; ?>);" />
							<input name='updateitemopt[<?php echo $id; ?>]' id='updateitemopt[<?php echo $id; ?>]' type='button' value='<?php esc_attr_e(__( '更新' )); ?>' onclick="if( jQuery('#post_ID').val() < 0 ) return; itemOpt.post('updateitemopt', <?php echo $id; ?>);" />
							<input name='itemopt[<?php echo $id; ?>][sort]' id='itemopt[<?php echo $id; ?>][sort]' type='hidden' value='<?php echo $sort; ?>' />
						</div>
						<div id='itemopt_loading-<?php echo $id; ?>' class='meta_submit_loading'></div>
					</td>
				</tr>
			</table>
		</td></tr>
		<?php
		$r = ob_get_contents();
		ob_end_clean();
		return $r;
	}

	/*****************************
	* 商品オプション（商品画面）
	******************************
	static function item_option_meta_form() {
		global $wpdb;

		$general_options = wc2_get_option('general');
		$limit = (int) apply_filters( 'postmeta_form_limit', 30 );
		$item_options = isset( $general_options['item_option'] ) ? $general_options['item_option'] : '';
		$means = wc2_get_option('wc2_item_option_select');
		$meansoption = '';
		foreach($means as $meankey => $meanvalue){
			$meansoption .= '<option value="'.esc_attr($meankey).'">'.esc_html($meanvalue)."</option>\n";
		}
		?>
		<div id="itemopt_ajax-response"></div>
		<p><strong><?php _e('商品オプションの適用','wc2') ?> : </strong></p>
		<table id="newmeta2">
			<thead>
			<tr>
				<th class="item-opt-key"><label for="metakeyselect"><?php _e('オプション名','wc2') ?></label></th>
				<th class="item-opt-value"><label for="metavalue"><?php _e('セレクト値','wc2') ?></label></th>
			</tr>
			</thead>
			
			<tbody>
			<tr>
				<td class='item-opt-key'>

				<?php //if( !empty($item_options) ) { ?>
					<select id="optkeyselect" name="optkeyselect" class="optkeyselect metaboxfield" style="display: inline-block;" tabindex="7" onchange="if( jQuery('#post_ID').val() < 0 ) return; itemOpt.post('keyselect', this.value);">
						<option value="#NONE#"><?php _e( '-- Select --','wc2' ); ?></option>
					<?php foreach ( $item_options as $opt ){ ?>
						<option value='<?php echo $opt['meta_id']; ?>'><?php esc_attr_e($opt['name']); ?></option>
					<?php } ?>
					</select>
					<input type="text" id="newoptname" name="newoptname" class="metaboxfield" style="display: none;" />
					<div class="optcheck">
						<select name='newoptmeans' id='newoptmeans' style="display: inline-block;"><?php echo $meansoption; ?></select>
						<label for='newoptessential'><input name="newoptessential" type="checkbox" id="newoptessential" class="metaboxcheckfield" /><?php _e('必須項目','wc2') ?></label>
					</div>

					<a href="javascript:void(0);"><span id="newopt" style="display: inline;"><?php _e( '新規追加', 'wc2' ) ?></span></a>
					<a href="javascript:void(0);"><span id="cancelopt" style="display: none;"><?php _e( 'キャンセル', 'wc2' ) ?></span></a>

					<input type="hidden" name="opt_status" id="opt_status" value="select_opt">

<?php /*			<input type="hidden" name="opt_status" value="new_opt">
				<?php } else { ?>
					<input type="text" id="newoptname" name="newoptname" class="metaboxfield" style="display: inline-block;"/>
					<span id="newopt" style="display: none;"></span>
					<span id="cancelopt" style="display: inline;"></span>
* ?>
				<?php //} ?>

				</td>
				<td class='item-opt-value'><textarea id="newoptvalue" name="newoptvalue" class='metaboxfield'></textarea></td>
			</tr>
			
			<tr>
				<td colspan="2" class="submittd">
				<?php // if( is_array($item_options) ) { ?>
				<div id='newitemoptsubmit' class='submit'>
					<input name="add_itemopt" type="button" id="add_itemopt" tabindex="9" value="<?php _e('オプションを追加する','wc2') ?>" onclick="if( jQuery('#post_ID').val() < 0 ) return; itemOpt.post('additemopt', 0);" />
				</div>
				<div id="newitemopt_loading" class="meta_submit_loading"></div>
				<?php //} ?>
				</td>
			</tr>
			</tbody>
		</table>
		<?php 
	}

	/****************************
	* 商品オプション
	*****************************
/*
	static function common_option_meta_form() {
		$means = wc2_get_option('wc2_item_option_select');
		$meansoption = '';
		foreach($means as $meankey => $meanvalue){
			$meansoption .= '<option value="'.esc_attr($meankey).'">'.esc_html($meanvalue)."</option>\n";
		}
		?>
		<div id="itemopt_ajax-response"></div>
		<p><strong><?php _e('新しいオプションを追加','wc2') ?> : </strong></p>
		<table id="newmeta2">
			<thead>
			<tr>
				<th class="left"><label for="metakeyselect"><?php _e('オプション名','wc2') ?></label></th>
				<th><label for="metavalue"><?php _e('セレクト値','wc2') ?></label></th>
			</tr>
			</thead>
			
			<tbody>
			<tr>
				<td class='item-opt-key'>
					<input type="text" id="newoptname" name="newoptname" class="metaboxfield" tabindex="7" value="" />
					<div class="optcheck">
						<select name='newoptmeans' id='newoptmeans' class="metaboxfield long"><?php echo $meansoption; ?></select>
						<label for='newoptessential'><input name="newoptessential" type="checkbox" id="newoptessential" class="metaboxcheckfield" /><?php _e('必須項目','wc2') ?></label>
					</div>
				</td>
				<td class='item-opt-value'><textarea id="newoptvalue" name="newoptvalue" class='metaboxfield'></textarea></td>
			</tr>
			
			<tr>
				<td colspan="2" class="submittd">
					<div id='newcomoptsubmit' class='submit'>
						<input name="add_comopt" type="button" id="add_comopt" tabindex="9" value="<?php _e('共通オプションを追加','wc2') ?>" onclick="itemOpt.post('addcommonopt', 0);" />
					</div>
					<div id="newcomopt_loading" class="meta_submit_loading"></div>
				</td>
			</tr>
			</tbody>
		</table>
		<?php 
	}
*/

	static function get_condition(){
		$general_options = wc2_get_option( 'general' );
		$conditions = array(
			'display_mode' => $general_options['display_mode'],
			'campaign_privilege' => $general_options['campaign_privilege'],
			'campaign_category' => $general_options['campaign_category'],
			'privilege_point' => $general_options['privilege_point'],
			'privilege_discount' => $general_options['privilege_discount'],
			'tax_mode' => $general_options['tax_mode'],
			'tax_target' => $general_options['tax_target'],
			'tax_rate' => $general_options['tax_rate'],
			'tax_method' => $general_options['tax_method'],
			'membersystem_state' => $general_options['membersystem_state'],
			'membersystem_point' => $general_options['membersystem_point'],
		);
		return $conditions;
	}

	static function settle_info_field( $order_id, $type='nl' ) {
		$str = '';
		//$fields = self::get_settle_info_field( $order_id );
		$fields = array();
		$acting = isset($fields['acting']) ? $fields['acting'] : '';
		foreach($fields as $key => $value){
			$keys = array(
				'acting','order_no','tracking_no','status','error_message','money',
				'pay_cvs', 'pay_no1', 'pay_no2', 'pay_limit', 'error_code',
				'settlement_id','RECDATE','JOB_ID','S_TORIHIKI_NO','TOTAL','CENDATE',
				'gid', 'rst', 'ap', 'ec', 'god', 'ta', 'cv', 'no', 'cu', 'mf', 'nk', 'nkd', 'bank', 'exp', 
				'order_number',
				'res_tracking_id', 'res_payment_date', 'res_payinfo_key',
				'SID', 'DATE', 'TIME', 'CVS', 'SHNO', 'FURL', 'settltment_status', 'settltment_errmsg', 
				'stran', 'mbtran', 'bktrans', 'tranid', 'TransactionId', 
				'mStatus', 'vResultCode', 'orderId', 'cvsType', 'receiptNo', 'receiptDate', 'rcvAmount' 
			);
			$keys = apply_filters( 'wc2_filter_settle_info_field_keys', $keys );
			if( !in_array($key, $keys) ) {
				continue;
			}

			switch($acting){
				case 'zeus_bank':
					if( 'status' == $key){
						if( '01' === $value ){
							$value = '受付中';
						}elseif( '02' === $value ){
							$value = __( 'Unpaid', 'wc2' );
						}elseif( '03' === $value ){
							$value = __( 'Payment confirmed', 'wc2' );
						}elseif( '04' === $value ){
							$value = 'エラー';
						}elseif( '05' === $value ){
							$value = '入金失敗';
						}
					}elseif( 'error_message' == $key){
						if( '0002' === $value ){
							$value = '入金不足';
						}elseif( '0003' === $value ){
							$value = '過剰入金';
						}
					}
					break;
				case 'zeus_conv':
					if( 'pay_cvs' == $key){
						$value = esc_html(wc2_get_conv_name($value));
					}elseif( 'status' == $key){
						if( '01' === $value ){
							$value = __( 'Unpaid', 'wc2' );
						}elseif( '02' === $value ){
							$value = '申込エラー';
						}elseif( '03' === $value ){
							$value = '期日切';
						}elseif( '04' === $value ){
							$value = __( 'Payment confirmed', 'wc2' );
						}elseif( '05' === $value ){
							$value = '売上確定';
						}elseif( '06' === $value ){
							$value = '入金取消';
						}elseif( '11' === $value ){
							$value = 'キャンセル後入金';
						}elseif( '12' === $value ){
							$value = 'キャンセル後売上';
						}elseif( '13' === $value ){
							$value = 'キャンセル後取消';
						}
					}elseif( 'pay_limit' == $key){
						$value = substr($value, 0, 4).'年'.substr($value, 4, 2).'月'.substr($value, 6, 2).'日';
					}
					break;
				case 'jpayment_conv':
					switch($key) {
					case 'rst':
						switch($value) {
						case '1':
							$value = 'OK'; break;
						case '2':
							$value = 'NG'; break;
						}
						break;
					case 'ap':
						switch($value) {
						case 'CPL_PRE':
							$value = 'コンビニペーパーレス決済識別コード'; break;
						case 'CPL':
							$value = '入金確定'; break;
						case 'CVS_CAN':
							$value = '入金取消'; break;
						}
						break;
					case 'cv':
						$value = esc_html(wc2_get_conv_name($value));
						break;
					case 'mf':
					case 'nk':
					case 'nkd':
					case 'bank':
					case 'exp':
						continue;
						break;
					}
					break;

				case 'jpayment_bank':
					switch($key) {
					case 'rst':
						switch($value) {
						case '1':
							$value = 'OK'; break;
						case '2':
							$value = 'NG'; break;
						}
						break;
					case 'ap':
						switch($value) {
						case 'BANK':
							$value = '受付完了'; break;
						case 'BAN_SAL':
							$value = '入金完了'; break;
						}
						break;
					case 'mf':
						switch($value) {
						case '1':
							$value = 'マッチ'; break;
						case '2':
							$value = '過少'; break;
						case '3':
							$value = '過剰'; break;
						}
						break;
					case 'nkd':
						$value = substr($value, 0, 4).'年'.substr($value, 4, 2).'月'.substr($value, 6, 2).'日';
						break;
					case 'exp':
						$value = substr($value, 0, 4).'年'.substr($value, 4, 2).'月'.substr($value, 6, 2).'日';
						break;
					case 'cv':
					case 'no':
					case 'cu':
						continue;
						break;
					}
					break;
				case 'veritrans_conv':
					if( 'cvsType' == $key ) {
						switch( $value ) {
						case 'sej':
							$value = 'セブン－イレブン';
							break;
						case 'econ-lw':
							$value = 'ローソン';
							break;
						case 'econ-fm':
							$value = 'ファミリーマート';
							break;
						case 'econ-mini':
							$value = 'ミニストップ';
							break;
						case 'econ-other':
							$value = 'セイコーマート';
							break;
						}
					}
					break;
			}
			$value = apply_filters( 'wc2_filter_settle_info_field_value', $value, $key, $acting );
			switch($type){
				case 'nl':
					$str .= $key.' : '.$value."<br />\n";
					break;
					
				case 'tr':
					$str .= '<tr><td class="label">'.$key.'</td><td>'.$value."</td></tr>\n";
					break;
					
				case 'li':
					$str .= '<li>'.$key.' : '.$value."</li>\n";
					break;
			}
		}
	}

	//内税表示
	static function tax_label( $data = array() ) {
		$general_options = wc2_get_option( 'general' );

		if( is_array($data) && array_key_exists( 'order_condition', $data ) ) {
			$condition = maybe_unserialize($data['order_condition']);
			$tax_mode = ( isset($condition['tax_mode']) ) ? $condition['tax_mode'] : $general_options['tax_mode'];
		} else {
			$tax_mode = $general_options['tax_mode'];
		}

		if( 'exclude' == $tax_mode ) {
			$label = __('Consumption tax', 'wc2');
		} else {
			if( isset($condition['tax_mode']) && !empty($data['ID']) ) {
				$materials = array(
					'total_price' => $data['item_total_price'],
					'discount' => $data['discount'],
					'shipping_charge' => $data['shipping_charge'],
					'cod_fee' => $data['cod_fee'],
				);
				$label = __('Internal consumption tax', 'wc2').'('.wc2_crform( wc2_internal_tax($materials), true, false, true ).')';
			} else {
				$label = __('Internal consumption tax', 'wc2');
			}
		}
		$label = apply_filters( 'wc2_filter_tax_label', $label, $tax_mode);

		return $label;
	}

	//内税取得
	static function internal_tax( $materials ) {
		$tax = 0;
		$general_options = wc2_get_option( 'general' );
		$system_options = wc2_get_option( 'system' );
		$locale_options = wc2_get_option( 'locale_options' );

		if( 'products' == $general_options['tax_target'] ) {
			$total = $materials['total_price'] + $materials['discount'];
		} else {
			$total = $materials['total_price'] + $materials['discount'] + $materials['shipping_charge'] + $materials['cod_fee'];
		}
		$total = apply_filters( 'wc2_filter_internal_tax_total_price', $total, $materials );

		$tax = $total - $total / ( 1 + ( $general_options['tax_rate'] / 100 ) );

//$general_options['tax_target']
		$cr = $system_options['currency'];
		$decimal = $locale_options['currency'][$cr][1];
		$decipad = (int)str_pad( '1', $decimal+1, '0', STR_PAD_RIGHT );
		switch( $general_options['tax_method'] ) {
			case 'cutting':
				$tax = floor($tax*$decipad)/$decipad;
				break;
			case 'bring':
				$tax = ceil($tax*$decipad)/$decipad;
				break;
			case 'rounding':
				if( 0 < $decimal ) {
					$tax = round($tax, (int)$decimal);
				} else {
					$tax = round($tax);
				}
				break;
		}
		$tax = apply_filters( 'wc2_filter_internal_tax', $tax, $materials );

		return $tax;
	}

	/*************************************************
		wc2_optionからカスタムフィールドkeyを取得
	**************************************************/
	static function get_has_custom_field_key($type){
		switch($type){
			case 'order':
				$field = WC2_CSOD;
				break;
			case 'customer':
				$field = WC2_CSCS;
				break;
			case 'delivery':
				$field = WC2_CSDE;
				break;
			case 'member':
				$field = WC2_CSMB;
				break;
			default:
				return array();
		}
		$keys = self::get_custom_field_keys($field);
		
		return $keys;
	}

	/**********************************
		住所様式から名前を並び替える
	***********************************/
	static function get_localized_name( $Familly_name, $Given_name ){
		$locale_options = wc2_get_option('locale_options');
		$system_options = wc2_get_option('system');
		$form = $system_options['addressform'];//JP
		if( $locale_options['nameform'][$form] ){
			$res = $Given_name.' '.$Familly_name;
		}else{
			$res = $Familly_name.' '.$Given_name;
		}
		
		return $res;
	}

	/************************************
		引数に応じたurlを返す
	************************************/
	static function get_url( $type ) {
		switch ( $type ){
//			case 'cart':
//				$url = USCES_CART_URL;
//				break;
			case 'login':
				$url = WC2_LOGIN_URL;
				break;
			case 'member':
				$url = WC2_MEMBER_URL;
				break;
			case 'newmemberform':
				$url = WC2_NEWMEMBER_URL;
				break;
			case 'lostpassword':
				$url = WC2_LOSTPASSWORD_URL;
				break;
//			case 'cartnonsession':
//				$url = USCES_CART_NONSESSION_URL;
//				break;
		}
		return $url;
	}

	/*******************************
		会員システムの利用
	********************************/
	static function is_membersystem_state(){
		$general_options = wc2_get_option('general');

		if( 'activate' == $general_options['membersystem_state'] ){
			return true;
		}else{
			return false;
		}
	}

	/*******************************
		会員ポイントシステムの利用
	********************************/
	static function is_membersystem_point(){
		$general_options = wc2_get_option('general');

		if( 'activate' == $general_options['membersystem_point'] ){
			return true;
		}else{
			return false;
		}
	}
	
	/*******************************
		SSL使用 判定
	********************************/
	static function use_ssl(){
		$system_options = wc2_get_option('system');
		$use_ssl = $system_options['use_ssl'];
		
		return $use_ssl;
	}
}

//template_funcへ移動
function wc2_get_apply_addressform($country){
	$res = WC2_Funcs::get_apply_addressform($country);
	return $res;
}

function wc2_settle_info_field($order_id, $type='nl'){
	$res = WC2_Funcs::settle_info_field($order_id, $type);
	return $res;
}

function wc2_tax_label( $data = array() ){
	$res = WC2_Funcs::tax_label($data);
	return $res;
}

function wc2_tax_label_e( $data = array() ){
	echo wc2_tax_label( $data );
}

function wc2_internal_tax( $materials ){
	$res = WC2_Funcs::internal_tax( $materials );
	return $res;
}

function wc2_internal_tax_e( $materials ){
	echo wc2_internal_tax( $materials );
}

function wc2_get_local_addressform(){
	$res = WC2_Funcs::get_local_addressform();
	return $res;
}
//to change wc2_get_local_addressform
//function get_local_addressform(){
//	$res = wc2_get_local_addressform();
//	return $res;
//}

function wc2_mail_custom_field_info( $data, $type, $position ) {
	$msg_body = '';
	switch( $type ) {
	case 'order':
		$label = WC2_CUSTOM_ORDER;
		$prefix = WC2_CSOD;
		break;
	case 'customer':
		$label = WC2_CUSTOM_CUSTOMER;
		$prefix = WC2_CSCS;
		break;
	case 'delivery':
		$label = WC2_CUSTOM_DELIVERY;
		$prefix = WC2_CSDE;
		break;
	case 'member':
		$label = WC2_CUSTOM_MEMBER;
		$prefix = WC2_CSMB;
		break;
	default:
		return $msg_body;
	}

	$keys = wc2_get_custom_field_keys( $prefix, $position );

	if( !empty($keys) && is_array($keys) ) {
		foreach( $keys as $key ) {
			$entry = wc2_get_option( $key );
			$name = $entry['name'];
			$means = $entry['means'];
			$value = $entry['value'];
			$cstm_data = '';
			list( $pfx, $cskey ) = explode( '_', $key, 2 );
			//$cskey = substr_replace( $key , '', 0, 5 );
			if( is_array($data) && !empty($data[$label]) ) {
				$cstm_data = ( isset($data[$label][$cskey]) ) ? $data[$label][$cskey] : '';
			}

			$msg_body .= $name." : ";
			if( !empty($cstm_data) ) {
				switch( $means ) {
					case 'select':
					case 'text':
					case 'radio':
					case 'textarea':
						$msg_body .= $cstm_data;
						break;
					case 'multi':
					case 'check':
						if( is_array($cstm_data) ) {
							$c = '';
							foreach( $cstm_data as $v ) {
								$msg_body .= $c.$v;
								$c = ', ';
							}
						} else {
							$msg_body .= $cstm_data;
						}
						break;
				}
			}
			$msg_body .= "\r\n";
		}
	}
	$msg_body = apply_filters( 'wc2_filter_mail_custom_field_info', $msg_body, $data, $type, $position );
	return $msg_body;
}

function wc2_get_mail_addressform( $data, $type ) {

	$locale_options = wc2_get_option( 'locale_options' );
	$system_options = wc2_get_option( 'system' );
	$applyform = wc2_get_apply_addressform( $system_options['addressform'] );

	switch( $type ) {
	case 'customer':
		$values = $data;
		$name_label = __('ご購入者','wc2');
		break;
	case 'delivery':
		//$values = $data['delivery'];
		$values = array_shift($data['delivery']);
		$name_label = __('宛名','wc2');
		break;
	}

	$formtag = '';
	switch( $applyform ) {
	case 'JP': 
		$formtag .= wc2_mail_custom_field_info( $values, $type, 'head' );
		if( $type == 'customer' ) {
			$formtag .= ( !empty( $data['member_id'] ) ) ? __('Membership number', 'wc2')." : ".$data['member_id']."\r\n" : '';
			$formtag .= ( !empty( $data['email'] ) ) ? __('E-mail address', 'wc2')." : ".$data['email']."\r\n" : '';
		}
		$formtag .= wc2_mail_custom_field_info( $values, $type, 'beforename' );
		$formtag .= $name_label." : ".sprintf(__('Mr/Mrs %s', 'wc2'), ($values['name1'].' '.$values['name2']))."\r\n";
		if( !empty($values['name3']) || !empty($values['name4']) ) {
			$formtag .= __('Kana','wc2')." : ".$values['name3'].' '.$values['name4']."\r\n";
		}
		$formtag .= wc2_mail_custom_field_info( $values, $type, 'aftername' );
		if( 1 < count( $system_options['target_market'] ) ) {
			$formtag .= __('Country','wc2')." : ".$locale_options['country'][$values['country']]."\r\n";
		}
		$formtag .= __('Postal Code','wc2')." : ".$values['zipcode']."\r\n";
		$formtag .= __('Address','wc2')." : ".$values['pref'].$values['address1'].$values['address2']."\r\n";
		$formtag .= __('Phone number','wc2')." : ".$values['tel']."\r\n";
		$formtag .= __('FAX number','wc2')." : ".$values['fax']."\r\n";
		$formtag .= wc2_mail_custom_field_info( $values, $type, 'bottom' );
		break;

	case 'CN':
		$formtag .= wc2_mail_custom_field_info( $values, $type, 'head' );
		if( $type == 'customer' ) {
			$formtag .= ( !empty( $data['member_id'] ) ) ? __('Membership number', 'wc2')." : ".$data['member_id']."\r\n" : '';
			$formtag .= ( !empty( $data['email'] ) ) ? __('E-mail address', 'wc2')." : ".$data['email']."\r\n" : '';
		}
		$formtag .= wc2_mail_custom_field_info( $values, $type, 'beforename' );
		$formtag .= $name_label." : ".sprintf(__('Mr/Mrs %s', 'wc2'), ($values['name1'].' '.$values['name2']))."\r\n";
		$formtag .= wc2_mail_custom_field_info( $values, $type, 'aftername' );
		if( 1 < count( $system_options['target_market'] ) ) {
			$formtag .= __('Country','wc2')." : ".$locale_options['country'][$values['country']]."\r\n";
		}
		$formtag .= __('State','wc2')." : ".$values['pref']."\r\n";
		$formtag .= __('City','wc2')." : ".$values['address1']."\r\n";
		$formtag .= __('Address','wc2')." : ".$values['address2']."\r\n";
		$formtag .= __('Postal Code','wc2')." : ".$values['zipcode']."\r\n";
		$formtag .= __('Phone number','wc2')." : ".$values['tel']."\r\n";
		$formtag .= __('FAX number','wc2')." : ".$values['fax']."\r\n";
		$formtag .= wc2_mail_custom_field_info( $values, $type, 'bottom' );
		break;

	case 'US':
	default:
		$formtag .= wc2_mail_custom_field_info( $values, $type, 'head' );
		if( $type == 'customer' ) {
			$formtag .= ( !empty( $data['member_id'] ) ) ? __('Membership number', 'wc2')." : ".$data['member_id']."\r\n" : '';
			$formtag .= ( !empty( $data['email'] ) ) ? __('E-mail address', 'wc2')." : ".$data['email']."\r\n" : '';
		}
		$formtag .= wc2_mail_custom_field_info( $values, $type, 'beforename' );
		$formtag .= $name_label." : ".sprintf(__('Mr/Mrs %s', 'wc2'), ($values['name2'].' '.$values['name1']))."\r\n";
		$formtag .= wc2_mail_custom_field_info( $values, $type, 'aftername' );
		$formtag .= __('Address','wc2')." : ".$values['address2']."\r\n";
		$formtag .= __('City','wc2')." : ".$values['address1']."\r\n";
		$formtag .= __('State','wc2')." : ".$values['pref']."\r\n";
		if( 1 < count( $system_options['target_market'] ) ) {
			$formtag .= __('Country','wc2')." : ".$locale_options['country'][$values['country']]."\r\n";
		}
		$formtag .= __('Postal Code','wc2')." : ".$values['zipcode']."\r\n";
		$formtag .= __('Phone number','wc2')." : ".$values['tel']."\r\n";
		$formtag .= __('FAX number','wc2')." : ".$values['fax']."\r\n";
		$formtag .= wc2_mail_custom_field_info( $values, $type, 'bottom' );
		break;
	}
	$addressform = apply_filters( 'wc2_filter_mail_addressform', $formtag, $data, $type );
	return $addressform;
}

function wc2_mail_line( $type, $email = '' ) {
	$line = '';
	switch( $type ) {
	case 1:
		$line = "******************************************************";
		break;
	case 2:
		$line = "------------------------------------------------------------------";
		break;
	case 3:
		$line = "=============================================";
		break;
	}
	$line = apply_filters( 'wc2_filter_mail_line', $line, $type, $email );
	return $line."\r\n";
}

function wc2_get_cart_item_name($item_name, $item_code, $sku_name, $sku_code){
	$name_arr = array();
	$name_str = '';

	$wc2_item = WC2_DB_Item::get_instance();

	$general = wc2_get_option('general');
	foreach($general['indi_item_name'] as $key => $value){
		if($value){
			$pos = (int)$general['pos_item_name'][$key];
			$ind = ($pos === 0) ? 'A' : $pos;

			switch($key){
				case 'item_name':
					$name_arr[$ind][$key] = $item_name;
					break;
				case 'item_code':
					$name_arr[$ind][$key] = $item_code;
					break;
				case 'sku_name':
					$name_arr[$ind][$key] = $sku_name;
					break;
				case 'sku_code':
					$name_arr[$ind][$key] = $sku_code;
					break;
			}
		}
	}
	ksort($name_arr);
	foreach($name_arr as $vals){
		foreach($vals as $key => $value){
		
			$name_str .= $value . ' ';
		}
	}
	$name_str = apply_filters('wc2_admin_order_item_name_filter', $name_str, $item_name, $item_code, $sku_name, $sku_code);
	return trim($name_str);
}

function wc2_get_custom_field_keys( $prefix, $position = NULL ){
	$res = WC2_Funcs::get_custom_field_keys( $prefix, $position );
	return $res;
}

function wc2_get_custom_field_value( $custom_key, $array_key = 'value' ){
	$res = WC2_Funcs::get_custom_field_value( $custom_key, $array_key );
	return $res;
}

function wc2_get_has_custom_field_key($fieldname){
	$res = WC2_Funcs::get_has_custom_field_key($fieldname);
	return $res;
}

function wc2_localized_name($Familly_name, $Given_name){
	$res = WC2_Funcs::get_localized_name($Familly_name, $Given_name);
	return $res;
}

function wc2_url($type){
	$res = WC2_Funcs::get_url($type);
	return $res;
}

function wc2_is_membersystem_state(){
	$res = WC2_Funcs::is_membersystem_state();
	return $res;
}

function wc2_is_membersystem_point(){
	$res = WC2_Funcs::is_membersystem_point();
	return $res;
}

//addressform
function wc2_get_addressform( $data, $type ){
	$res = WC2_Funcs::get_addressform( $data, $type );
	return $res;
}

//customfield
function wc2_custom_field_input($data, $type, $position){
	$res = WC2_Funcs::get_custom_field_input( $data, $type, $position );
	return $res;
}

function wc2_get_essential_mark( $fielde, $data = NULL ){
	$res = WC2_Funcs::get_essential_mark( $fielde, $data );
	return $res;
}

function wc2_get_local_target_market(){
	$res = WC2_Funcs::get_local_target_market();
	return $res;
}

function wc2_get_base_country(){
	$res = WC2_Funcs::get_base_country();
	return $res;
}

function wc2_get_pref_select($type, $values){
	$res = WC2_Funcs::get_pref_select($type, $values);
	return $res;
}

function wc2_use_ssl(){
	$res = WC2_Funcs::use_ssl();
	return $res;
}

function wc2_get_local_language() {
	$locale = get_locale();
	switch( $locale ) {
		case '':
		case 'en':
		case 'en_US':
			$front_lang = 'en';
			break;
		case 'ja':
		case 'ja_JP':
			$front_lang = 'ja';
			break;
		default:
			$front_lang = 'others';
	}
	return $front_lang;
}

function wc2_get_available_delivery_method() {
	$cart = wc2_get_cart();
	if( 0 < count($cart) ) {
		$delivery = wc2_get_option( 'delivery' );
		$delivery_limit_option = ( isset($delivery['delivery_limit_option']) ) ? $delivery['delivery_limit_option'] : '';

		$intersect = array();
		$integration = array();
		$temp = array();
		$in = 0;
		$total_quantity = 0;
		foreach( $cart as $key => $cart_row ) {
			$total_quantity += (int)$cart_row['quantity'];

			$item_delivery = maybe_unserialize( wc2_get_item_value_by_item_id( $cart_row['item_id'], ITEM_DELIVERY_METHOD ) );
			if( empty($item_delivery) )
				continue;

			if( 0 === $in ) {
				$intersect = $item_delivery;
			}
			$intersect = array_intersect($item_delivery, $intersect);
			foreach( $item_delivery as $value ) {
				$integration[] = $value;
			}
			$in++;
		}
		$integration = array_unique($integration);
		foreach( $integration as $id ) {
			$index = self::get_delivery_method_index($id);
			$temp[$index] = $id;
		}
		ksort($temp);
		$force = array(array_shift($temp));
		if( empty($intersect) ) {
			$available = $force;
		} else {
			$available = $intersect;
		}

		if( 'item' == $delivery_limit_option ) {
			$available_delivery = array();
			foreach( $available as $id ) {
				$index = wc2_get_delivery_method_index( $id );
				$limit_num = ( !empty($delivery_options['delivery_method'][$index]['limit_num']) ) ? $delivery_options['delivery_method'][$index]['limit_num'] : 0;
				if( 0 < $limit_num and $limit_num <= $total_quantity ) {
				} else {
					$available_delivery[$index] = $id;
				}
			}
			return $available_delivery;
		} else {
			return $available;
		}
	}
	return array();
}

function wc2_get_delivery_method_index( $id ) {
	$delivery = wc2_get_option( 'delivery' );
	$index = -1;
	for( $i = 0; $i < count($delivery['delivery_method']); $i++ ) {
		if( $delivery['delivery_method'][$i]['id'] === (int)$id ) {
			$index = $i;
			break;
		}
	}
	return $index;
}

function wc2_get_shipping_charge_index( $id ) {
	$shipping_charge = wc2_get_option( 'shipping_charge' );
	$index = -1;
	for( $i = 0; $i < $shipping_charge; $i++) {
		if( (int)$shipping_charge[$i]['id'] == (int)$id ) {
			$index = $i;
			break;
		}
	}
	return $index;
}

function wc2_get_delivery_method_name( $id ) {
	$name = WC2_Funcs::get_delivery_method_name( $id );
	return $name;
}

function wc2_get_tax( $materials ) {
//	$materials = array(
//		'entry_data',
//		'cart',
//		'total_price',
//		'discount',
//		'shipping_charge',
//		'usedpoint',
//		'cod_fee',
//		'payment',
//	);
//	extract( $materials );
	$tax = 0;
	$general_options = wc2_get_option( 'general' );
	$system_options = wc2_get_option( 'system' );
	$locale_options = wc2_get_option( 'locale_options' );

	//税率
	if( empty($general_options['tax_rate']) )
		return $tax;

	//税込
	if( 'include' == $general_options['tax_mode'] )
		return $tax;

	//商品代金のみ
	if( 'products' == $general_options['tax_target'] ) {
		$total = $materials['total_price'] + $materials['discount'];

	//総合計金額
	} else {
		$total = $materials['total_price'] + $materials['discount'] + $materials['shipping_charge'] + $materials['cod_fee'];
	}
	$total = apply_filters( 'wc2_filter_get_tax_total_price', $total, $materials );
	$tax = $total * $general_options['tax_rate'] / 100;

	$cr = $system_options['currency'];
	$decimal = $locale_options['currency'][$cr][1];
	$decipad = (int)str_pad( '1', $decimal+1, '0', STR_PAD_RIGHT );
	switch( $general_options['tax_method'] ) {
		case 'cutting':
			$tax = floor($tax*$decipad)/$decipad;
			break;
		case 'bring':
			$tax = ceil($tax*$decipad)/$decipad;
			break;
		case 'rounding':
			if( 0 < $decimal ) {
				$tax = round($tax, (int)$decimal);
			} else {
				$tax = round($tax);
			}
			break;
	}
	$tax = apply_filters( 'wc2_filter_get_tax', $tax, $materials );

	return $tax;
}

function wc2_custom_field_info( $data, $type, $position = NULL ) {
	$res = WC2_Funcs::get_custom_field_info( $data, $type, $position );
	return $res;
}

function wc2_get_addressform_confirm( $entry_data, $type ) {
	$locale_options = wc2_get_option( 'locale_options' );
	$system_options = wc2_get_option( 'system' );
	$applyform = wc2_get_apply_addressform( $system_options['addressform'] );

	switch( $type ) {
	case 'customer':
		$data = $entry_data['customer'];
		break;
	case 'delivery':
		$data = $entry_data['delivery'];
		break;
	}

	$formtag = '';
	$formtag .= wc2_custom_field_info( $data, $type, 'head' );
	if( $type == 'customer' ) {
		$formtag .= '
		<tr class="'.$type.'-mail"><th>'.__('E-mail address', 'wc2').'</th><td>'.__( $data['email'] ).'</td></tr>';
	}
	switch( $applyform ) {
	case 'JP':
		$formtag .= wc2_custom_field_info( $data, $type, 'beforename' );
		$formtag .= '
		<tr class="'.$type.'-name"><th>'.__('Full name', 'wc2').'</th><td>'.esc_html($data['name1']).' '.esc_html($data['name2']).'</td></tr>';
		$kana = '
		<tr class="'.$type.'-phonetic"><th>'.__('Kana', 'wc2').'</th><td>'.esc_html($data['name3']).' '.esc_html($data['name4']).'</td></tr>';
		$formtag .= apply_filters( 'wc2_filter_confirm_kana', $kana, $type, $data );
		$formtag .= wc2_custom_field_info( $data, $type, 'aftername' );
		$formtag .= '
		<tr class="'.$type.'-zipcode"><th>'.__('Postal Code', 'wc2').'</th><td>'.esc_html($data['zipcode']).'</td></tr>';
		if( count( $system_options['target_market'] ) != 1 ) {
			$country = ( array_key_exists($data['country'], $locale_options['country']) ) ? $locale_options['country'][$data['country']] : '';
			$formtag .= '
		<tr class="'.$type.'-country"><th>'.__('Country', 'wc2').'</th><td>'.esc_html($country).'</td></tr>';
		}
		$formtag .= '
		<tr class="'.$type.'-pref"><th>'.__('Prefecture', 'wc2').'</th><td>'.esc_html($data['pref']).'</td></tr>
		<tr class="'.$type.'-address1"><th>'.__('City/Ward/Town/Village/Street name, street number', 'wc2').'</th><td>'.esc_html($data['address1']).'</td></tr>
		<tr class="'.$type.'-address2"><th>'.__('Building name, floor, room number', 'wc2').'</th><td>'.esc_html($data['address2']).'</td></tr>
		<tr class="'.$type.'-tel"><th>'.__('Phone number', 'wc2').'</th><td>'.esc_html($data['tel']).'</td></tr>
		<tr class="'.$type.'-fax"><th>'.__('FAX number', 'wc2').'</th><td>'.esc_html($data['fax']).'</td></tr>';
		break;

	case 'CN':
		$formtag .= wc2_custom_field_info( $data, $type, 'beforename' );
		$formtag .= '<tr class="'.$type.'-name"><th>'.__('Full name', 'wc2').'</th><td>'.esc_html(wc2_localized_name( $data['name1'], $data['name2'] )).'</td></tr>';
		$formtag .= wc2_custom_field_info( $data, $type, 'aftername' );
		if( count( $system_options['target_market'] ) != 1 ) {
			$country = ( array_key_exists($data['country'], $locale_options['country']) ) ? $locale_options['country'][$data['country']] : '';
			$formtag .= '<tr><th class="'.$type.'-country">'.__('Country', 'wc2').'</th><td>'.esc_html($country).'</td></tr>';
		}
		$formtag .= '
		<tr class="'.$type.'-pref"><th>'.__('State', 'wc2').'</th><td>'.esc_html($data['pref']).'</td></tr>
		<tr class="'.$type.'-address1"><th>'.__('City/Ward/Town/Village/Street name, street number', 'wc2').'</th><td>'.esc_html($data['address1']).'</td></tr>
		<tr class="'.$type.'-address2"><th>'.__('Building name, floor, room number', 'wc2').'</th><td>'.esc_html($data['address2']).'</td></tr>
		<tr class="'.$type.'-zipcode"><th>'.__('ZIP code', 'wc2').'</th><td>'.esc_html($data['zipcode']).'</td></tr>
		<tr class="'.$type.'-tel"><th>'.__('Phone number', 'wc2').'</th><td>'.esc_html($data['tel']).'</td></tr>
		<tr class="'.$type.'-fax"><th>'.__('FAX number', 'wc2').'</th><td>'.esc_html($data['fax']).'</td></tr>';
		break;

	case 'US':
	default :
		$formtag .= wc2_custom_field_info( $data, $type, 'beforename' );
		$formtag .= '<tr class="'.$type.'-name"><th>'.__('Full name', 'wc2').'</th><td>'.esc_html($data['name2']).' '.esc_html($data['name1']).'</td></tr>';
		$formtag .= wc2_custom_field_info( $data, $type, 'aftername' );
		$formtag .= '
		<tr class="'.$type.'-address2"><th>'.__('Building name, floor, room number', 'wc2').'</th><td>'.esc_html($data['address2']).'</td></tr>
		<tr class="'.$type.'-address1"><th>'.__('City/Ward/Town/Village/Street name, street number', 'wc2').'</th><td>'.esc_html($data['address1']).'</td></tr>
		<tr class="'.$type.'-pref"><th>'.__('State', 'wc2').'</th><td>'.esc_html($data['pref']).'</td></tr>';
		if( count( $system_options['target_market'] ) != 1 ) {
			$country = ( array_key_exists($data['country'], $locale_options['country']) ) ? $locale_options['country'][$data['country']] : '';
			$formtag .= '<tr class="'.$type.'-country"><th>'.__('Country', 'wc2').'</th><td>'.esc_html($country).'</td></tr>';
		}
		$formtag .= '
		<tr class="'.$type.'-zipcode"><th>'.__('ZIP code', 'wc2').'</th><td>'.esc_html($data['zipcode']).'</td></tr>
		<tr class="'.$type.'-tel"><th>'.__('Phone number', 'wc2').'</th><td>'.esc_html($data['tel']).'</td></tr>
		<tr class="'.$type.'-fax"><th>'.__('FAX number', 'wc2').'</th><td>'.esc_html($data['fax']).'</td></tr>';
		break;
	}
	$formtag .= wc2_custom_field_info( $data, $type, 'bottom' );
	$res = apply_filters( 'wc2_filter_addressform_confirm', $formtag, $type, $data );
	return $res;
}

function wc2_get_addressform_confirm_e( $data, $type ) {
	echo wc2_get_addressform_confirm( $data, $type );
}

function wc2_tax( $entry_order_data ) {
	$general_options = wc2_get_option( 'general' );
	if( 'exclude' == $general_options['tax_mode'] ) {
		//税別
		$tax = wc2_crform( $entry_order_data['tax'], true, false );
	} else {
		//税込
		$materials = array(
			'total_price' => $entry_order_data['item_total_price'],
			'discount' => $entry_order_data['discount'],
			'shipping_charge' => $entry_order_data['shipping_charge'],
			'cod_fee' => $entry_order_data['cod_fee'],
		);
		$tax = '('.wc2_crform( wc2_internal_tax( $materials ), true, false ).')';
	}
	$tax = apply_filters( 'wc2_filter_tax', $tax, $entry_order_data );
	return $tax;
}

function wc2_tax_e( $entry_order_data ) {
	echo wc2_tax( $entry_order_data );
}

function wc2_payment_detail( $entry_order_data ) {
	$payment = wc2_get_payment( $entry_order_data['payment_method'] );
	$acting_flag = ( 'acting' == $payment['settlement'] ) ? $payment['module'] : $payment['settlement'];
	$detail_str = '';
	switch( $acting_flag ) {
		case 'acting_zeus_card':
			if( !isset($entry_order_data['cbrand']) || (isset($entry_order_data['howpay']) && '1' === $entry_order_data['howpay']) ) {
				$str = '　一括払い';
			} else {
				$div_name = 'div_'.$entry_order_data['cbrand'];
				switch( $entry_order_data[$div_name] ) {
					case '01':
						$detail_str = '　一括払い';
						break;
					case '99':
						$detail_str = '　分割（リボ払い）';
						break;
					case '03':
						$detail_str = '　分割（3回）';
						break;
					case '05':
						$detail_str = '　分割（5回）';
						break;
					case '06':
						$detail_str = '　分割（6回）';
						break;
					case '10':
						$detail_str = '　分割（10回）';
						break;
					case '12':
						$detail_str = '　分割（12回）';
						break;
					case '15':
						$detail_str = '　分割（15回）';
						break;
					case '18':
						$detail_str = '　分割（18回）';
						break;
					case '20':
						$detail_str = '　分割（20回）';
						break;
					case '24':
						$detail_str = '　分割（24回）';
						break;
				}
			}
			break;

		case 'acting_zeus_bank':
			break;

		case 'acting_zeus_conv':
			if( isset($entry_order_data['pay_cvs']) ) {
				$conv_name = wc2_get_conv_name( $entry_order_data['pay_cvs'] );
				$detail_str = ( '' != $conv_name ) ? '　（'.$conv_name.'）' : '';
			}
			break;

		case 'acting_remise_card':
			if( isset( $entry_order_data['div'] ) ) {
				switch( $entry_order_data['div'] ) {
					case '0':
						$detail_str = '　一括払い';
						break;
					case '1':
						$detail_str = '　分割（2回）';
						break;
					case '2':
						$detail_str = '　分割（リボ払い）';
						break;
				}
			}
			break;

		case 'acting_remise_conv':
			break;
	}
	$detail_str = apply_filters( 'wc2_filter_payment_detail', $detail_str, $entry_order_data );
	return $detail_str;
}

//Copyright
function wc2_copyright(){
	$general = wc2_get_option('general');
	return $general['copyright'];
}

//カート  ヘッダー・フッター説明書き挿入
//top customer delivery confirm complete
function wc2_cart_insert_description_e($position, $page){
	switch($position){
		case 'header' :
			$cart_position = 'cart_header';
		break;
		case 'footer' :
			$cart_position = 'cart_footer';
		break;
	}
	//$general = wc2_get_option('general');
	$cart_description = wc2_get_option('cart_description');
	if( isset($cart_description[$cart_position][$page]) ){
		$html = $cart_description[$cart_position][$page];
		echo '<div class="'. esc_attr( $position ) .'-explanation">' . do_shortcode( stripslashes(nl2br($html)) ) . '</div>';
	}
}

//メンバー  ヘッダー・フッター説明書き挿入
//login newmemberform lostpassword changepassword memberform complete
function wc2_member_insert_description_e( $position, $page ){
	switch($position){
		case 'header':
			$member_position = 'member_header';
		break;
		case 'footer':
			$member_position = 'member_footer';
		break;
	}
	if( 'newcomplete' == $page || 'editcomplete' == $page || 'lostcomplete' == $page || 'changecomplete' == $page){
		$page = 'complete';
	}
	//$general = wc2_get_option('general');
	$member_description = wc2_get_option('member_description');
	if( isset($member_description[$member_position][$page]) ){
		$html = $member_description[$member_position][$page];
		echo '<div class="'. esc_attr( $position ) .'-explanation">' . do_shortcode( stripslashes(nl2br($html)) ) . '</div>';
	}
}

function wc2_ordermail( $data, $send = 'order' ) {

	$cart = $data['cart'];
	$payment = wc2_get_payment( $data['payment_method'] );
	$general_options = wc2_get_option( 'general' );
	$total_price = $data['item_total_price'] - $data['usedpoint'] + $data['discount'] + $data['shipping_charge'] + $data['cod_fee'] + $data['tax'];

	$msg_body = "";
	if( $data['order_type'] == 'estimate' ) {
		$msg_top = "\r\n\r\n\r\n".__('【お見積】','wc2')."\r\n";
		$msg_top .= wc2_mail_line( 1, $data['email'] );//********************
		$msg_top .= apply_filters( 'wc2_filter_ordermail_first', "", $data, $payment, $send );
		$msg_top .= wc2_get_mail_addressform( $data, 'customer' );
		$msg_top .= __('お見積番号','wc2')." : ".$data['order_id']."\r\n";
	} else {
		$msg_top = "\r\n\r\n\r\n".__('【ご注文内容】','wc2')."\r\n";
		$msg_top .= wc2_mail_line( 1, $data['email'] );//********************
		$msg_top .= apply_filters( 'wc2_filter_ordermail_first', "", $data, $payment, $send );
		$msg_top .= wc2_get_mail_addressform( $data, 'customer' );
		$msg_top .= __('Order number','wc2')." : ".$data['dec_order_id']."\r\n";
		$msg_top .= __('注文日時','wc2' )." : ".$data['order_date']."\r\n";
	}
	$msg_top .= "\r\n";
	$msg_body = apply_filters( 'wc2_filter_ordermail_top', $msg_top, $data, $payment, $send );

	$msg_detail = __('Items','wc2')."\r\n";
	foreach( $cart as $idx => $cart_row ) {
//		$item_id = $cart_row['item_id'];
//		$sku_id = $cart_row['sku_id'];
		$item_name = $cart_row['item_name'];
		$item_code = $cart_row['item_code'];
		$sku_name = $cart_row['sku_name'];
		$sku_code = $cart_row['sku_code'];

		$cart_item_name = wc2_get_cart_item_name( $item_name, $item_code, $sku_name, $sku_code );
		$cart_options = '';

		$msg_detail .= wc2_mail_line( 2, $data['email'] );//--------------------
		$msg_detail .= $cart_item_name."\r\n";
		if( is_array($cart_options) && count($cart_options) > 0 ) {
			$optstr = '';
			foreach( $cart_options as $key => $value ) {
				if( !empty($key) ) {
					$key = urldecode($key);
					if( is_array($value) ) {
						$c = '';
						$optstr .= $key.' : ';
						foreach( $value as $v ) {
							$optstr .= $c.urldecode($v);
							$c = ', ';
						}
						$optstr .= "\r\n";
					} else {
						$optstr .= $key.' : '.urldecode($value)."\r\n";
					}
				}
			}
			$msg_detail .= apply_filters( 'wc2_filter_ordermail_cartrow_options', $optstr, $cart_options, $send );
		}
		$msg_detail .= __('単価','wc2')." ".wc2_crform( $cart_row['price'], true, false ).__(' * ','wc2').$cart_row['quantity']."\r\n";
	}
	$msg_detail .= wc2_mail_line( 3, $data['email'] );//====================
	$msg_detail .= __('商品合計','wc2')." : ".wc2_crform( $data['item_total_price'], true, false )."\r\n";
	if( $data['discount'] != 0 )
		$msg_detail .= apply_filters( 'wc2_filter_discount_label', __('値引', 'wc2'), $data['order_id'] )." : ".wc2_crform( $data['discount'], true, false )."\r\n";
	if( 0.00 < (float)$data['tax'] && 'products' == $general_options['tax_target'] )
		$msg_detail .= wc2_tax_label( $data )." : ".wc2_crform( $data['tax'], true, false )."\r\n";
	$msg_detail .= __('送料','wc2')." : ".wc2_crform( $data['shipping_charge'], true, false )."\r\n";
	if( $payment['settlement'] == 'COD' )
		$msg_detail .= apply_filters( 'wc2_filter_cod_label', __('COD fee','wc2'))." : ".wc2_crform( $data['cod_fee'], true, false )."\r\n";
	if( 0.00 < (float)$data['tax'] && 'all' == $general_options['tax_target'] )
		$msg_detail .= wc2_tax_label( $data )." : ".wc2_crform( $data['tax'], true, false )."\r\n";
	if( $data['usedpoint'] != 0 )
		$msg_detail .= __('ご利用ポイント','wc2')." : ".number_format($data['usedpoint']).__('ポイント','wc2')."\r\n";
	$msg_detail .= wc2_mail_line( 2, $data['email'] );//--------------------
	$msg_detail .= __('お支払金額','wc2')." : ".wc2_crform( $total_price, true, false )."\r\n";
	$msg_detail .= wc2_mail_line( 2, $data['email'] );//--------------------
	$msg_detail .= "(".__('Currency', 'wc2').' : '.__(wc2_crcode(),'wc2').")\r\n\r\n\r\n";
	$msg_body .= apply_filters( 'wc2_filter_ordermail_detail', $msg_detail, $data, $payment, $send );

	$msg_shipping = __('【配送先】','wc2')."\r\n";
	$msg_shipping .= wc2_mail_line( 1, $data['email'] );//********************
	$msg_shipping .= wc2_get_mail_addressform( $data, 'delivery' );
	$msg_shipping .= __('配送方法','wc2')." : ".$data['delivery_name']."\r\n";
	$msg_shipping .= __('配送希望日','wc2')." : ".$data['delivery_date']."\r\n";
	$msg_shipping .= __('配送希望時間','wc2')." : ".$data['delivery_time']."\r\n";
	$msg_shipping .= "\r\n\r\n";
	$msg_body .= apply_filters( 'wc2_filter_ordermail_shipping', $msg_shipping, $data, $payment, $send );

	$msg_payment = __('【お支払方法】','wc2')."\r\n";
	$msg_payment .= wc2_mail_line( 1, $data['email'] );//********************
	$msg_payment .= $payment['name'].wc2_payment_detail($data)."\r\n\r\n";
	if( $payment['settlement'] == 'BT' ) {
		$transferee = __('お振込先','wc2')." : \r\n";
		$transferee .= wc2_get_option( 'transferee_info' )."\r\n\r\n";
		$transferee .= wc2_mail_line( 2, $data['email'] )."\r\n";//--------------------
		$msg_payment .= apply_filters( 'wc2_filter_mail_transferee', $transferee, $data, $payment, $send );
	}
	$msg_payment .= "\r\n\r\n";
	$msg_body .= apply_filters( 'wc2_filter_ordermail_payment', $msg_payment, $data, $payment, $send );

	$msg_other = __('【その他】','wc2')."\r\n";
	$msg_other .= wc2_mail_line( 1, $data['email'] );//********************
	$msg_other .= wc2_mail_custom_field_info( $data, 'order', 'beforeremarks' );
	$msg_other .= $data['note']."\r\n";
	$msg_other .= wc2_mail_custom_field_info( $data, 'order', 'other' );
	$msg_other .= "\r\n\r\n\r\n";
	$msg_body .= apply_filters( 'wc2_filter_ordermail_other', $msg_other, $data, $payment, $send );

	$msg_body = apply_filters( 'wc2_filter_ordermail_body', $msg_body, $data, $payment, $send );

	return $msg_body;
}

function wc2_send_ordermail( $order_id ) {

	$data = wc2_get_order_data( $order_id );
	$msg_body = wc2_ordermail( $data, 'order' );
	$general_options = wc2_get_option( 'general' );
	$phrase = wc2_get_option( 'phrase' );

	$name = sprintf(__('Mr/Mrs %s', 'wc2'), ($data['name1'].$data['name2']));
	$subject = apply_filters( 'wc2_filter_send_ordermail_subject_to_customer', $phrase['title']['thankyou'], $data );
	$header = apply_filters( 'wc2_filter_send_ordermail_header_to_customer', $phrase['header']['thankyou'], $data );
	$footer = apply_filters( 'wc2_filter_send_ordermail_footer_to_customer', $phrase['footer']['thankyou'], $data );
	$message = do_shortcode($header).$msg_body.do_shortcode($footer);

	$wc2_mail = WC2_Mail::get_instance();
	$wc2_mail->clear_column();
	$wc2_mail->set_customer_para_value( 'to_name', $name );
	$wc2_mail->set_customer_para_value( 'to_address', $data['email'] );
	$wc2_mail->set_customer_para_value( 'from_name', get_option('blogname') );
	$wc2_mail->set_customer_para_value( 'from_address', $general_options['sender_mail'] );
	$wc2_mail->set_customer_para_value( 'return_path', $general_options['sender_mail'] );
	$wc2_mail->set_customer_para_value( 'subject', $subject );
	$wc2_mail->set_customer_para_value( 'message', $message );
	do_action( 'wc2_action_send_ordermail_parameter_to_customer' );
	$res = $wc2_mail->send_customer_mail();

	$admin_subject = apply_filters( 'wc2_filter_send_ordermail_subject_to_manager', $phrase['title']['order'], $data );
	$admin_header = apply_filters( 'wc2_filter_send_ordermail_header_to_manager', $phrase['header']['order'], $data );
	$admin_footer = apply_filters( 'wc2_filter_send_ordermail_footer_to_manager', $phrase['footer']['order'], $data );
	$admin_message = do_shortcode($admin_header).$msg_body.do_shortcode($admin_footer)
	. "\n----------------------------------------------------\n"
	. "REMOTE_ADDR : " . $_SERVER['REMOTE_ADDR']
	. "\n----------------------------------------------------\n";

	$wc2_mail->set_admin_para_value( 'to_name', __('新規受注登録通知', 'wc2') );
	$wc2_mail->set_admin_para_value( 'to_address', $general_options['order_mail'] );
	$wc2_mail->set_admin_para_value( 'from_name', $name );
	$wc2_mail->set_admin_para_value( 'from_address', $data['email'] );
	$wc2_mail->set_admin_para_value( 'return_path', $general_options['error_mail'] );
	$wc2_mail->set_admin_para_value( 'subject', $admin_subject );
	$wc2_mail->set_admin_para_value( 'message', $admin_message );
	do_action( 'wc2_action_send_ordermail_parameter_to_manager' );
	$res = $wc2_mail->send_admin_mail();
}

function wc2_ordermail_admin( $order_id ) {

	$data = wc2_get_order_data( $order_id );
	$phrase = wc2_get_option( 'phrase' );
	$msg_body = wc2_ordermail( $data, 'admin' );

	switch( $_POST['mode'] ) {
		case 'mail_completion'://発送完了
			$header = apply_filters( 'wc2_filter_ordermail_admin_header', $phrase['header']['completionmail'], $data );
			$footer = apply_filters( 'wc2_filter_ordermail_admin_footer', $phrase['footer']['completionmail'], $data );
			$body = apply_filters( 'wc2_filter_ordermail_admin_body', $msg_body, $data, 'mail_completion' );
			$message = do_shortcode($header).$body.do_shortcode($footer);
			break;
		case 'mail_order'://注文確認
			$header = apply_filters( 'wc2_filter_ordermail_admin_header', $phrase['header']['ordermail'], $data );
			$footer = apply_filters( 'wc2_filter_ordermail_admin_footer', $phrase['footer']['ordermail'], $data );
			$body = apply_filters( 'wc2_filter_ordermail_admin_body', $msg_body, $data, 'mail_order' );
			$message = do_shortcode($header).$body.do_shortcode($footer);
			break;
		case 'mail_change'://変更確認
			$header = apply_filters( 'wc2_filter_ordermail_admin_header', $phrase['header']['changemail'], $data );
			$footer = apply_filters( 'wc2_filter_ordermail_admin_footer', $phrase['footer']['changemail'], $data );
			$body = apply_filters( 'wc2_filter_ordermail_admin_body', $msg_body, $data, 'mail_change' );
			$message = do_shortcode($header).$body.do_shortcode($footer);
			break;
		case 'mail_receipt'://入金確認
			$header = apply_filters( 'wc2_filter_ordermail_admin_header', $phrase['header']['receiptmail'], $data );
			$footer = apply_filters( 'wc2_filter_ordermail_admin_footer', $phrase['footer']['receiptmail'], $data );
			$body = apply_filters( 'wc2_filter_ordermail_admin_body', $msg_body, $data, 'mail_receipt' );
			$message = do_shortcode($header).$body.do_shortcode($footer);
			break;
		case 'mail_estimate'://見積
			$header = apply_filters( 'wc2_filter_ordermail_admin_header', $phrase['header']['estimatemail'], $data );
			$footer = apply_filters( 'wc2_filter_ordermail_admin_footer', $phrase['footer']['estimatemail'], $data );
			$body = apply_filters( 'wc2_filter_ordermail_admin_body', $msg_body, $data, 'mail_estimate' );
			$message = do_shortcode($header).$body.do_shortcode($footer);
			break;
		case 'mail_cancel'://キャンセル
			$header = apply_filters( 'wc2_filter_ordermail_admin_header', $phrase['header']['cancelmail'], $data );
			$footer = apply_filters( 'wc2_filter_ordermail_admin_footer', $phrase['footer']['cancelmail'], $data );
			$body = apply_filters( 'wc2_filter_ordermail_admin_body', $msg_body, $data, 'mail_cancel' );
			$message = do_shortcode($header).$body.do_shortcode($footer);
			break;
		case 'mail_other'://その他
			$header = apply_filters( 'wc2_filter_ordermail_admin_header', $phrase['header']['othermail'], $data );
			$footer = apply_filters( 'wc2_filter_ordermail_admin_footer', $phrase['footer']['othermail'], $data );
			$body = apply_filters( 'wc2_filter_ordermail_admin_body', $msg_body, $data, 'mail_other' );
			$message = do_shortcode($header).$body.do_shortcode($footer);
			break;
		default:
			$header = apply_filters( 'wc2_filter_ordermail_admin_header', '', $data );
			$footer = apply_filters( 'wc2_filter_ordermail_admin_footer', '', $data );
			$message = apply_filters( 'wc2_filter_ordermail_admin_body', $msg_body, $data, '' );
	}
	return apply_filters( 'wc2_filter_ordermail_admin_message', $message, $data );
}

function wc2_send_ordermail_admin() {
	$_POST = wc2_stripslashes_deep_post($_POST);
	$general_options = wc2_get_option( 'general' );

	$name = sprintf(__('Mr/Mrs %s', 'wc2'), trim(urldecode($_POST['name'])));
	$message = trim(urldecode($_POST['message']));

	$wc2_mail = WC2_Mail::get_instance();
	$wc2_mail->clear_column();
	$wc2_mail->set_customer_para_value( 'to_name', $name );
	$wc2_mail->set_customer_para_value( 'to_address', trim(urldecode($_POST['mailaddress'])) );
	$wc2_mail->set_customer_para_value( 'from_name', get_option('blogname') );
	$wc2_mail->set_customer_para_value( 'from_address', $general_options['sender_mail'] );
	$wc2_mail->set_customer_para_value( 'return_path', $general_options['sender_mail'] );
	$wc2_mail->set_customer_para_value( 'subject', trim(urldecode($_POST['subject'])) );
	$wc2_mail->set_customer_para_value( 'message', $message );
	do_action( 'wc2_action_send_ordermail_admin_parameter_to_customer', $_POST['checked']  );
	$res = $wc2_mail->send_customer_mail();

	if( $res ) {
		wc2_update_order_check( $_POST['order_id'], $_POST['checked'] );

		$wc2_mail->set_admin_para_value( 'to_name', 'Shop Admin' );
		$wc2_mail->set_admin_para_value( 'to_address', $general_options['order_mail'] );
		$wc2_mail->set_admin_para_value( 'from_name', 'Welcart Auto BCC' );
		$wc2_mail->set_admin_para_value( 'from_address', $general_options['sender_mail'] );
		$wc2_mail->set_admin_para_value( 'return_path', $general_options['sender_mail'] );
		$wc2_mail->set_admin_para_value( 'subject', trim(urldecode($_POST['subject'])).' to '.$name );
		$wc2_mail->set_admin_para_value( 'message', $message );
		do_action( 'wc2_action_send_ordermail_admin_parameter_to_manager', $_POST['checked'] );
		$res = $wc2_mail->send_admin_mail();

		return 'OK';
	} else {
		return 'NG';
	}
}

function wc2_custom_field_enter_check($type){
	switch($type){
	case 'order':
		$label = WC2_CUSTOM_ORDER;
		$prefix = WC2_CSOD;
		break;
	case 'customer':
		$label = WC2_CUSTOM_CUSTOMER;
		$prefix = WC2_CSCS;
		break;
	case 'delivery':
		$label = WC2_CUSTOM_DELIVERY;
		$prefix = WC2_CSDE;
		break;
	case 'member':
		$label = WC2_CUSTOM_MEMBER;
		$prefix = WC2_CSMB;
		break;
	default:
		return;
	}

	$custome_field_keys = wc2_get_custom_field_keys($prefix);

	$mes =array();
	foreach( $custome_field_keys as $csfd_key){
		$custom_field = wc2_get_option($csfd_key);
		//必須カスタムフィールド入力チェック
		if( 1 == $custom_field['essential'] ){
			switch( $custom_field['means'] ){
			case 'radio':
			case 'check':
				if( !isset( $_POST[$label][$custom_field['key']]) ){
					$mes[] = sprintf( __('%sが選択されていません。', 'wc2'), $custom_field['name'] );
				}
				break;
			case 'select':
			case 'multi':
				if( !isset( $_POST[$label][$custom_field['key']]) || '#NONE#' == $_POST[$label][$custom_field['key']] ){
					$mes[] = sprintf( __('%sが選択されていません。', 'wc2'), $custom_field['name'] );
				}
				break;
			case 'text':
			case 'textarea':
				if( empty( $_POST[$label][$custom_field['key']] ) ){
					$mes[] = sprintf( __('%sが入力されていません。', 'wc2'), $custom_field['name'] );
				}
				break;
			}
		}
	}

	return $mes;
}

function wc2_get_order_status_name($status_key){
	$management_status = wc2_get_option( 'management_status' );
	$status_name = '';
	foreach( $management_status as $key =>$val){
		if( $key == $status_key ){
			$status_name = $val;
		}
	}

	return $status_name;
}

//「基本設定」＞「カートページ設定」を基に
/*
function wc2_getCartItemName($post_id, $sku_code){
	$name_arr = array();
	$name_str = '';

	$wc2_item = WC2_DB_Item::get_instance();

	$general = wc2_get_option('general');
	foreach($general['indi_item_name'] as $key => $value){
		if($value){
			$pos = (int)$general['pos_item_name'][$key];
			$ind = ($pos === 0) ? 'A' : $pos;

			switch($key){
				case 'item_name':
					$name_arr[$ind][$key] = $wc2_item->get_item_value_by_post_id( $post_id, 'item_name' );
					break;
				case 'item_code':
					$name_arr[$ind][$key] = $wc2_item->get_item_value_by_post_id( $post_id, 'item_code' );
					break;
				case 'sku_name':
					$name_arr[$ind][$key] = $wc2_item->get_sku_name_by_sku_code($sku_code);
					break;
				case 'sku_code':
					$name_arr[$ind][$key] = $sku_code;
					break;
			}
		}
	}
	ksort($name_arr);
	foreach($name_arr as $vals){
		foreach($vals as $key => $value){
		
			$name_str .= $value . ' ';
		}
	}
	$name_str = apply_filters('wc2_admin_order_item_name_filter', $name_str, $post_id, $sku_code);
	return trim($name_str);
}
*/

function wc2_get_currency($amount, $symbol_pre = false, $symbol_post = false, $seperator_flag = true){
	return WC2_Funcs::get_currency($amount, $symbol_pre, $symbol_post, $seperator_flag);
}

function wc2_get_tax_target(){
	$general = wc2_get_option('general');
	$tax_target = isset($general['tax_target']) ? $general['tax_target']: '';

	return $tax_target;
}

//itemタクソノミーの中で商品が属する商品ジャンル内のカテゴリを取得
function wc2_get_item_cat_genre_ids( $post_id ){
	$ids = array();
	$all_ids = array();
	$genre = get_category_by_slug( 'item-genre' );
	$genre_id = isset($genre->term_id) ? $genre->term_id: 0;
	$args = array('child_of' => $genre_id, 'hide_empty' => 0, 'hierarchical' => 0, 'taxonomy' => 'item');
	$categories = get_categories( $args );

	foreach($categories as $category){
		$ids[] = $category->term_id;
	}
	$allcats = get_the_category( $post_id );

//$allterms = get_the_terms($post_id, 'kaitai');

	foreach($allcats as $cat){
		$all_ids[] = $cat->term_id;
	}
	$results = array_intersect($ids, $all_ids);

	$results = apply_filters('wc2_filter_get_item_cat_genre_ids', $results, $post_id);
	return $results;
}