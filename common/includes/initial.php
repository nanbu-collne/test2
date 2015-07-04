<?php
function wc2_get_languages() {
	$res = array();
	if( $handle = opendir(WC2_PLUGIN_DIR.'/languages/') ) {
		while( false !== ( $file = readdir($handle) ) ) {
			if( '.' != $file && '..' != $file && preg_match('/^wc2-(.+)\.mo$/', $file, $matches) ) {
				$res[] = $matches[1];
			}
		}
		closedir( $handle );
	}
	return $res;
}

function wc2_initial_setting() {

	//Member rank
	$rank_type = wc2_get_option( 'rank_type' );
	//if( empty($rank_type) ) {
		$rank_type = array(
			'0' => __('Normal member', 'wc2'),
			'1' => __('Excellent member', 'wc2'),
			'2' => __('VIP member', 'wc2'),
			'99' => __('Bad member', 'wc2')
		);
	//}
	$rank_type = apply_filters( 'wc2_filter_rank_type', $rank_type );
	wc2_update_option( 'rank_type', $rank_type );

	//Admin screen label
	$admin_screen_label = wc2_get_option( 'admin_screen_label' );
	//if( empty($admin_screen_label) ) {
		$admin_screen_label = array(
			'order' => __('Order', 'wc2'),
			'member' => __('Member', 'wc2'),
			'general' => __('General', 'wc2'),
			'phrase' => __('Phrase', 'wc2'),
			'payment' => __('Payment', 'wc2'),
			'delivery' => __('Delivery', 'wc2'),
			'system' => __('System', 'wc2'),
			'customfield' => __('Custom field', 'wc2'),
		);
	//}
	$admin_screen_label = apply_filters( 'wc2_filter_admin_screen_label', $admin_screen_label );
	wc2_update_option( 'admin_screen_label', $admin_screen_label );

	//Stock status
	$stock_status = wc2_get_option( 'stock_status' );
	//if( empty($stock_status) ) {
		$stock_status = array(
			'0'  => __( 'In stock', 'wc2' ),
			'10' => __( 'Little stock', 'wc2' ),
			'20' => __( 'Sold Out', 'wc2' ),
			'30' => __( 'Out Of Stock', 'wc2' ),
			'90' => __( 'Discontinued', 'wc2' ),
		);
	//}
	$stock_status = apply_filters( 'wc2_filter_stock_status', $stock_status );
	wc2_update_option( 'stock_status', $stock_status );

	//Management status
	$management_status = wc2_get_option( 'management_status' );
	//if( empty($management_status) ) {
		$management_status = array(
			'new' => __('New order', 'wc2'),
			'backordered' => __('Backordered', 'wc2'),
			'cancel' => __('Cancel', 'wc2'),
			'completion' => __('Shipped', 'wc2'),
		);
	//}
	$management_status = apply_filters( 'wc2_filter_management_status', $management_status );
	wc2_update_option( 'management_status', $management_status );

	$receipt_status = wc2_get_option( 'receipt_status' );
	//if( empty($receipt_status) ) {
		$receipt_status = array(
			'unpaid' => __('Unpaid', 'wc2'),
			'paid' => __('Payment confirmed', 'wc2'),
			'pending' => __('Pending', 'wc2'),
		);
	//}
	$receipt_status = apply_filters( 'wc2_filter_receipt_status', $receipt_status );
	wc2_update_option( 'receipt_status', $receipt_status );

	$order_type = wc2_get_option( 'order_type' );
	//if( empty($order_type) ) {
		$order_type = array(
			'' => __('Normal order', 'wc2'),
			'estimate' => __('Estimates', 'wc2'),
			'adminorder' => __('Management order', 'wc2'),
		);
	//}
	$order_type = apply_filters( 'wc2_filter_order_type', $order_type );
	wc2_update_option( 'order_type', $order_type );

	//Display mode
	$display_mode_label = wc2_get_option( 'display_mode_label' );
	//if( empty($display_mode_label) ) {
		$display_mode_label = array(
			'Usualsale' => __('Open', 'wc2'),
			'Promotionsale' => __('Campaign Sale', 'wc2'),
			'Maintenancemode' => __('Maintenance Mode', 'wc2')
		);
	//}
	$display_mode_label = apply_filters( 'wc2_filter_display_mode_label', $display_mode_label );
	wc2_update_option( 'display_mode_label', $display_mode_label );

	//Shipping rule
	$shipping_rule = wc2_get_option( 'shipping_rule' );
	//if( empty($shipping_rule) ) {
		$shipping_rule['preparations'] = array(
			'0' => __('-- Select --', 'wc2'),
			'1' => __('Same day', 'wc2'),
			'2' => __('1-2 days', 'wc2'),
			'3' => __('2-3 days', 'wc2'),
			'4' => __('3-5 days', 'wc2'),
			'5' => __('4-6 days', 'wc2'),
			'6' => __('About 1 week later', 'wc2'),
			'7' => __('About 2 weeks later', 'wc2'),
			'8' => __('About 3 weeks later', 'wc2'),
			'9' => __('Stock after', 'wc2')
		);
		$shipping_rule['indication'] = array( 0, 0, 2, 3, 5, 6, 7, 14, 21, 0 );
	//}
	$shipping_rule = apply_filters( 'wc2_filter_shipping_rule', $shipping_rule );
	wc2_update_option( 'shipping_rule', $shipping_rule );

	//Locale default value
	$locale = wc2_get_option( 'locale_options' );
	//if( empty($locale) ) {
		$locale['language'] = array();
		$languages = wc2_get_languages();
		foreach( $languages as $l ) {
			$locale['language'][$l] = $l;
		}
		$locale['language']['others'] = __('Follow config.php', 'wc2');

		$locale['currency'] = array(
			'AR' => array('ARS', 2, '.', ',', '$'),
			'AU' => array('AUD', 2, '.', ',', '$'),
			'AT' => array('EUR', 2, '.', ',', '&#x20AC;'),
			'BE' => array('EUR', 2, '.', ',', '&#x20AC;'),
			'BR' => array('BRL', 2, '.', ',', '$'),
			'CA' => array('CAD', 2, '.', ',', '$'),
			'CL' => array('CLP', 2, '.', ',', '$'),
			'CN' => array('CNY', 2, '.', ',', '&yen;'),
			'CR' => array('CRC', 2, '.', ',', '₡'),
			'CZ' => array('CZK', 2, '.', ',', 'Kč'),
			'DK' => array('DKK', 2, '.', ',', 'kr'),
			'DO' => array('DOP', 2, '.', ',', 'RD$'),
			'FI' => array('EUR', 2, '.', ',', '&#x20AC;'),
			'FR' => array('EUR', 2, '.', ',', '&#x20AC;'),
			'DE' => array('EUR', 2, '.', ',', '&#x20AC;'),
			'GR' => array('EUR', 2, '.', ',', '&#x20AC;'),
			'GT' => array('GTQ', 2, '.', ',', ''),
			'HK' => array('HKD', 2, '.', ',', '$'),
			'HU' => array('HUF', 2, '.', ',', ''),
			'IN' => array('INR', 2, '.', ',', '&#x20A8;'),
			'ID' => array('IDR', 2, '.', ',', 'Rp'),
			'IE' => array('EUR', 2, '.', ',', '&#x20AC;'),
			'IL' => array('ILS', 2, '.', ',', '&#x20AA;'),
			'IT' => array('EUR', 2, '.', ',', '&#x20AC;'),
			'JP' => array('JPY', 0, '.', ',', '&yen;'),
			'MO' => array('MOP', 2, '.', ',', '$'),
			'MY' => array('MYR', 2, '.', ',', 'RM'),
			'MX' => array('MXN', 2, '.', ',', '$'),
			'NL' => array('EUR', 2, '.', ',', '&#x20AC;'),
			'NZ' => array('NZD', 2, '.', ',', '$'),
			'NO' => array('NOK', 2, '.', ',', ''),
			'PA' => array('PAB', 2, '.', ',', ''),
			'PH' => array('PHP', 2, '.', ',', 'P'),
			'PL' => array('PLN', 2, '.', ',', ''),
			'PT' => array('EUR', 2, '.', ',', '&#x20AC;'),
			'PR' => array('USD', 2, '.', ',', '$'),
			'RO' => array('ROL', 2, '.', ',', 'L'),
			'RU' => array('RUR', 2, '.', ',', ''),
			'SG' => array('SGD', 2, '.', ',', '$'),
			'KR' => array('KRW', 0, '.', ',', '&#x20A9;'),
			'ES' => array('EUR', 2, '.', ',', '&#x20AC;'),
			'SW' => array('SEK', 2, '.', ',', ''),
			'CH' => array('CHF', 2, '.', ',', 'Fr.'),
			'TW' => array('NT$', 0, '.', ',', '元'),
			'TH' => array('THB', 2, '.', ',', '฿'),
			'TR' => array('TRL', 2, '.', ',', '₤'),
			'GB' => array('GBP', 2, '.', ',', '£'),
			'US' => array('USD', 2, '.', ',', '$'),
			'VN' => array('VND', 2, '.', ',', '₫'),
			'OO' => array('USD', 2, '.', ',', '$'),
		);
		$locale['nameform'] = array(
			'AR' => 1,
			'AU' => 1,
			'AT' => 1,
			'BE' => 1,
			'BR' => 1,
			'CA' => 1,
			'CL' => 1,
			'CN' => 0,
			'CR' => 1,
			'CZ' => 1,
			'DK' => 1,
			'DO' => 1,
			'FI' => 1,
			'FR' => 1,
			'DE' => 1,
			'GR' => 1,
			'GT' => 1,
			'HK' => 1,
			'HU' => 1,
			'IN' => 1,
			'ID' => 1,
			'IE' => 1,
			'IL' => 1,
			'IT' => 1,
			'JP' => 0,
			'MO' => 1,
			'MY' => 1,
			'MX' => 1,
			'NL' => 1,
			'NZ' => 1,
			'NO' => 1,
			'PA' => 1,
			'PH' => 1,
			'PL' => 1,
			'PT' => 1,
			'PR' => 1,
			'RO' => 1,
			'RU' => 1,
			'SG' => 1,
			'KR' => 1,
			'ES' => 1,
			'SW' => 1,
			'CH' => 1,
			'TW' => 0,
			'TH' => 1,
			'TR' => 1,
			'GB' => 1,
			'US' => 1,
			'VN' => 1,
			'OO' => 1,
		);
		$locale['addressform'] = array(
			'AR' => 'US',
			'AU' => 'US',
			'AT' => 'US',
			'BE' => 'US',
			'BR' => 'US',
			'CA' => 'US',
			'CL' => 'US',
			'CN' => 'CN',
			'CR' => 'US',
			'CZ' => 'US',
			'DK' => 'US',
			'DO' => 'US',
			'FI' => 'US',
			'FR' => 'US',
			'DE' => 'US',
			'GR' => 'US',
			'GT' => 'US',
			'HK' => 'US',
			'HU' => 'US',
			'IN' => 'US',
			'ID' => 'US',
			'IE' => 'US',
			'IL' => 'US',
			'IT' => 'US',
			'JP' => 'JP',
			'MO' => 'US',
			'MY' => 'US',
			'MX' => 'US',
			'NL' => 'US',
			'NZ' => 'US',
			'NO' => 'US',
			'PA' => 'US',
			'PH' => 'US',
			'PL' => 'US',
			'PT' => 'US',
			'PR' => 'US',
			'RO' => 'US',
			'RU' => 'US',
			'SG' => 'US',
			'KR' => 'US',
			'ES' => 'US',
			'SW' => 'US',
			'CH' => 'US',
			'TW' => 'JP',
			'TH' => 'US',
			'TR' => 'US',
			'GB' => 'US',
			'US' => 'US',
			'VN' => 'US',
			'OO' => 'US',
		);
		$locale['country'] = array(
			'AR' => __('Argentina', 'wc2'),
			'AU' => __('Australia', 'wc2'),
			'AT' => __('Austria', 'wc2'),
			'BE' => __('Belgium', 'wc2'),
			'BR' => __('Brazil', 'wc2'),
			'CA' => __('Canada', 'wc2'),
			'CL' => __('Chile', 'wc2'),
			'CN' => __('China', 'wc2'),
			'CR' => __('Costa Rica', 'wc2'),
			'CZ' => __('Czech Republic', 'wc2'),
			'DK' => __('Denmark', 'wc2'),
			'DO' => __('Dominican Republic', 'wc2'),
			'FI' => __('Finland', 'wc2'),
			'FR' => __('France', 'wc2'),
			'DE' => __('Germany', 'wc2'),
			'GR' => __('Greece', 'wc2'),
			'GT' => __('Guatemala', 'wc2'),
			'HK' => __('Hong Kong', 'wc2'),
			'HU' => __('Hungary', 'wc2'),
			'IN' => __('India', 'wc2'),
			'ID' => __('Indonesia', 'wc2'),
			'IE' => __('Ireland', 'wc2'),
			'IL' => __('Israel', 'wc2'),
			'IT' => __('Italy', 'wc2'),
			'JP' => __('日本', 'wc2'),
			'MO' => __('Macau', 'wc2'),
			'MY' => __('Malaysia', 'wc2'),
			'MX' => __('Mexico', 'wc2'),
			'NL' => __('Netherlands', 'wc2'),
			'NZ' => __('New Zealand', 'wc2'),
			'NO' => __('Norway', 'wc2'),
			'PA' => __('Panama', 'wc2'),
			'PH' => __('Philippines', 'wc2'),
			'PL' => __('Poland', 'wc2'),
			'PT' => __('Portugal', 'wc2'),
			'PR' => __('Puerto Rico', 'wc2'),
			'RO' => __('Romania', 'wc2'),
			'RU' => __('Russia', 'wc2'),
			'SG' => __('Singapore', 'wc2'),
			'KR' => __('South Korea', 'wc2'),
			'ES' => __('Spain', 'wc2'),
			'SW' => __('Sweden', 'wc2'),
			'CH' => __('Switzerland', 'wc2'),
			'TW' => __('Taiwan', 'wc2'),
			'TH' => __('Thailand', 'wc2'),
			'TR' => __('Turkey', 'wc2'),
			'GB' => __('United Kingdom', 'wc2'),
			'US' => __('United States', 'wc2'),
			'VN' => __('Vietnam', 'wc2'),
			'OO' => __('Other', 'wc2'),
		);
		$locale['country_num'] = array(
			'AR' => '54',
			'AR' => '61',
			'AT' => '43',
			'BE' => '32',
			'BR' => '55',
			'CA' => '1',
			'CL' => '56',
			'CN' => '86',
			'CR' => '506',
			'CZ' => '420',
			'DK' => '45',
			'DO' => '1-809',
			'FI' => '358',
			'FR' => '33',
			'DE' => '49',
			'GR' => '30',
			'GT' => '502',
			'HK' => '852',
			'HU' => '36',
			'IN' => '91',
			'ID' => '62',
			'IE' => '353',
			'IL' => '972',
			'IT' => '39',
			'JP' => '81',
			'MO' => '853',
			'MY' => '60',
			'MX' => '52',
			'NL' => '31',
			'NZ' => '64',
			'NO' => '47',
			'PA' => '507',
			'PH' => '63',
			'PL' => '48',
			'PT' => '351',
			'PR' => '1-787',
			'RO' => '40',
			'RU' => '7',
			'SG' => '65',
			'KR' => '82',
			'ES' => '34',
			'SW' => '46',
			'CH' => '41',
			'TW' => '886',
			'TH' => '66',
			'TR' => '90',
			'GB' => '44',
			'US' => '1',
			'VN' => '84',
			'OO' => '1',
		);
		$locale['lungage2country'] = array(
			'es_AR' => 'AR',
			'en_AU' => 'AU',
			'de_AT' => 'AT',
			'nl_BE' => 'BE',
			'fr_BE' => 'BE',
			'pt_BR' => 'BR',
			'en_CA' => 'CA',
			'fr_CA' => 'CA',
			'es_CL' => 'CL',
			'zh_CN' => 'CN',
			'zh' => 'CN',
			'es_CR' => 'CR',
			'cs_CZ' => 'CZ',
			'cs' => 'CZ',
			'da' => 'DK',
			'da_DK' => 'DK',
			'es_DO' => 'DO',
			'fi_FI' => 'FI',
			'fi' => 'FI',
			'sv_FI' => 'FI',
			'fr' => 'FR',
			'fr_FR' => 'FR',
			'de' => 'DE',
			'de_DE' => 'DE',
			'el' => 'GR',
			'el_GR' => 'GR',
			'es_GT' => 'GT',
			'zh_HK' => 'HK',
			'en_HK' => 'HK',
			'hu_HU' => 'HU',
			'hu' => 'HU',
			'hi' => 'IN',
			'hi_IN' => 'IN',
			'id' => 'ID',
			'id_ID' => 'ID',
			'ga' => 'IE',
			'ga_IE' => 'IE',
			'en_IE' => 'IE',
			'he_IL' => 'IL',
			'ar_IL' => 'IL',
			'it' => 'IT',
			'it_IT' => 'IT',
			'ja' => 'JP',
			'ja_JP' => 'JP',
			'zh_MO' => 'MO',
			'pt_MO' => 'MO',
			'ms' => 'MY',
			'ms_MY' => 'MY',
			'es_MX' => 'MX',
			'nl' => 'NL',
			'nl_NL' => 'NL',
			'en_NZ' => 'NZ',
			'mi_NZ' => 'NZ',
			'mi' => 'NZ',
			'no' => 'NO',
			'no_NO' => 'NO',
			'es_PA' => 'PA',
			'tl' => 'PH',
			'tl_PH' => 'PH',
			'en_PH' => 'PH',
			'pl' => 'PL',
			'pl_PL' => 'PL',
			'pt' => 'PT',
			'pt_PT' => 'PT',
			'es_PR' => 'PR',
			'en_PR' => 'PR',
			'ro' => 'RO',
			'ro_RO' => 'RO',
			'ru' => 'RU',
			'ru_RU' => 'RU',
			'en_SG' => 'SG',
			'ms_SG' => 'SG',
			'zh_SG' => 'SG',
			'ko' => 'KR',
			'ko_KR' => 'KR',
			'es' => 'ES',
			'es_ES' => 'ES',
			'sv' => 'SW',
			'sv_SW' => 'SW',
			'de_CH' => 'CH',
			'fr_CH' => 'CH',
			'it_CH' => 'CH',
			'rm_CH' => 'CH',
			'rm' => 'CH',
			'zh_TW' => 'TW',
			'th' => 'TH',
			'th_TH' => 'TH',
			'tr' => 'TR',
			'tr_TR' => 'TR',
			'en' => 'GB',
			'en_GB' => 'GB',
			'' => 'US',
			'en_US' => 'US',
			'vi' => 'VN',
			'vi_VN' => 'VN',
			'zh_TW' => 'TW'
		);
	//}
	$locale = apply_filters( 'wc2_filter_locale', $locale );
	wc2_update_option( 'locale_options', $locale );

	//American state and prefectures of Japan
	$states = wc2_get_option( 'states_options' );
	//if( empty($states) ) {
		$states['JP'] = array("北海道","青森県","岩手県","宮城県","秋田県","山形県","福島県","茨城県",
			"栃木県","群馬県","埼玉県","千葉県","東京都","神奈川県","新潟県","富山県","石川県",
			"福井県","山梨県","長野県","岐阜県","静岡県","愛知県","三重県","滋賀県","京都府",
			"大阪府","兵庫県","奈良県","和歌山県","鳥取県","島根県","岡山県","広島県","山口県",
			"徳島県","香川県","愛媛県","高知県","福岡県","佐賀県","長崎県","熊本県","大分県",
			"宮崎県","鹿児島県","沖縄県"
		);
		$states['US'] = array("Alabama","Alaska","Arizona","Arkansas","California","Colorado",
			"Connecticut","Delaware","District of Columbia","Florida","Georgia","Hawaii",
			"Idaho","Illinois","Indiana","Iowa","Kansas","Kentucky","Louisiana","Maine",
			"Maryland","Massachusetts","Michigan","Minnesota","Mississippi","Missouri",
			"Montana","Nebraska","Nevada","New Hampshire","New Jersey","New Mexico","New York",
			"North Carolina","North Dakota","Ohio","Oklahoma","Oregon","Pennsylvania",
			"Rhode Island","South Carolina","South Dakota","Tennessee","Texas","Utah","Vermont",
			"Virginia","Washington","West Virginia","Wisconsin","Wyoming"
		);
	//}
	wc2_update_option( 'states_options', $states );

	//Option types
	$item_option_select = wc2_get_option( 'item_option_select' );
	//if( empty($item_option_select) ) {
		$item_option_select = array(
			'0' => __('シングルセレクト', 'wc2'),
			'1' => __('マルチセレクト', 'wc2'),
			'2' => __('テキスト', 'wc2'),
			'3' => __('ラジオボタン', 'wc2'),
			'4' => __('チェックボックス', 'wc2'),
			'5' => __('テキストエリア', 'wc2')
		);
	//}
	wc2_update_option( 'item_option_select', $item_option_select );

	//Fixed phrase
	$phrase_default = wc2_get_option( 'phrase_default' );
	if( empty($phrase_default) ) {
		$blogname = get_option( 'blogname' );
		$home_url = get_option( 'home' );

		$phrase_default['smtp_hostname'] = '';
		$phrase_default['newmem_admin_mail'] = 1;
		$phrase_default['delmem_admin_mail'] = 1;
		$phrase_default['delmem_customer_mail'] = 1;
		$phrase_default['editmem_customer_mail'] = 1;

		$phrase_default['title']['thankyou'] = __('ご注文内容の確認', 'wc2');
		$phrase_default['title']['order'] = __('受注報告', 'wc2');
		$phrase_default['title']['inquiry'] = __('お問い合わせを承りました', 'wc2');
		$phrase_default['title']['membercomp'] = __('ご入会完了のご連絡', 'wc2');
		$phrase_default['title']['completionmail'] = __('商品発送のご連絡', 'wc2');
		$phrase_default['title']['ordermail'] = __('ご注文内容の確認', 'wc2');
		$phrase_default['title']['changemail'] = __('ご注文内容変更の確認', 'wc2');
		$phrase_default['title']['receiptmail'] = __('ご入金確認のご連絡', 'wc2');
		$phrase_default['title']['estimatemail'] = __('お見積の件', 'wc2');
		$phrase_default['title']['cancelmail'] = __('ご注文キャンセルの確認', 'wc2');
		$phrase_default['title']['othermail'] = '';

		$phrase_default['header']['thankyou'] = sprintf(__("この度は%sをご利用くださいまして、誠にありがとうございます。", 'wc2'), $blogname) . "\r\n"
			. __("下記の通りご注文をお受けいたしましたのでご確認をお願いいたします。", 'wc2') . "\r\n\r\n"
			. __("商品の準備ができ次第、メールにて発送のご案内をさせていただきます。よろしくお願いいたします。", 'wc2') . "\r\n\r\n";
		$phrase_default['header']['order'] = sprintf(__("%sの注文が入りました。", 'wc2'), $blogname) . "\r\n";
		$phrase_default['header']['inquiry'] = sprintf(__("この度は%sをご利用くださいまして、誠にありがとうございます。", 'wc2'), $blogname) . "\r\n"
			. __("下記の通りお問い合わせをお受けいたしました。", 'wc2') . "\r\n\r\n"
			. __("準備ができ次第、メールにてご返答させていただきます。", 'wc2') . "\r\n\r\n";
		$phrase_default['header']['membercomp'] = sprintf(__("この度は%sの会員にご登録くださいまして、誠にありがとうございます。", 'wc2'), $blogname) . "\r\n\r\n"
			. __("「会員情報」にてご購入商品の履歴が確認できます。", 'wc2') . "\r\n\r\n";
		$phrase_default['header']['completionmail'] = __("本日、ご注文の商品を発送いたしました。", 'wc2') . "\r\n\r\n"
			. __("配送業者は○○運輸となっております。", 'wc2') . "\r\n\r\n"
			. __("万が一商品が届かない場合はご連絡ください。よろしくお願いいたします。", 'wc2') . "\r\n\r\n";
		$phrase_default['header']['ordermail'] = sprintf(__("この度は%sをご利用くださいまして、誠にありがとうございます。", 'wc2'), $blogname) . "\r\n"
			. __("下記の通りご注文内容を変更いたしましたので、ご確認をお願いいたします。", 'wc2') . "\r\n\r\n"
			. __("商品の準備ができ次第、メールにて発送のご案内をさせていただきます。よろしくお願いいたします。", 'wc2') . "\r\n\r\n";
		$phrase_default['header']['changemail'] = sprintf(__("この度は%sをご利用くださいまして、誠にありがとうございます。", 'wc2'), $blogname) . "\r\n"
			. __("下記の通りご注文内容を変更いたしましたので、ご確認をお願いいたします。", 'wc2') . "\r\n\r\n"
			. __("商品の準備ができ次第、メールにて発送のご案内をさせていただきます。よろしくお願いいたします。", 'wc2') . "\r\n\r\n";
		$phrase_default['header']['receiptmail'] =  sprintf(__("この度は%sをご利用くださいまして、誠にありがとうございます。", 'wc2'), $blogname) . "\r\n"
			. __("ご入金の確認ができましたので、ご連絡いたします。", 'wc2') . "\r\n\r\n"
			. __("商品の準備ができ次第、メールにて発送のご案内をさせていただきます。よろしくお願いいたします。", 'wc2') . "\r\n\r\n";
		$phrase_default['header']['estimatemail'] = sprintf(__("この度は%sをご利用くださいまして、誠にありがとうございます。", 'wc2'), $blogname) . "\r\n"
			. __("下記の通りお見積いたしましたので、ご確認をお願いいたします。", 'wc2') . "\r\n\r\n"
			. __("お見積の有効期限は一週間となっております。よろしくお願いいたします。", 'wc2') . "\r\n\r\n";
		$phrase_default['header']['cancelmail'] = sprintf(__("この度は%sをご利用くださいまして、誠にありがとうございます。", 'wc2'), $blogname) . "\r\n"
			. __("ご注文のキャンセルを承りました。今後ともよろしくお願いいたします。", 'wc2') . "\r\n\r\n";
		$phrase_default['header']['othermail'] = sprintf(__("この度は%sをご利用くださいまして、誠にありがとうございます。", 'wc2'), $blogname) . "\r\n\r\n";

		$general_options = wc2_get_option( 'wc2_general' );
		$company_name = ( isset($general_options['company_name']) ) ? $general_options['company_name'] : '';
		$zip_code = ( isset($general_options['zip_code']) ) ? $general_options['zip_code']: '';
		$address1 = ( isset($general_options['address1']) ) ? $general_options['address1'] : '';
		$address2 = ( isset($general_options['address2']) ) ? $general_options['address2'] : '';
		$tel_number = ( isset($general_options['tel_number']) ) ? $general_options['tel_number'] : '';
		$fax_number = ( isset($general_options['fax_number']) ) ? $general_options['fax_number'] : '';
		$inquiry_mail = ( isset($general_options['inquiry_mail']) ) ? $general_options['inquiry_mail'] : '';
		$footer = "=============================================\r\n" . $blogname . "\r\n" . $company_name . "\r\n" . $zip_code . "\r\n" . $address1 . "\r\n" . $address2 . "\r\n" . "TEL " . $tel_number . "\r\n" . "FAX " . $fax_number . "\r\n" . __('contact', 'wc2') . " " . $inquiry_mail . "\r\n" . $home_url . "\r\n" . "=============================================\r\n";
		$phrase_default['footer']['thankyou'] = $footer;
		$phrase_default['footer']['order'] = $footer;
		$phrase_default['footer']['inquiry'] = $footer;
		$phrase_default['footer']['membercomp'] = $footer;
		$phrase_default['footer']['completionmail'] = $footer;
		$phrase_default['footer']['ordermail'] = $footer;
		$phrase_default['footer']['changemail'] = $footer;
		$phrase_default['footer']['receiptmail'] = $footer;
		$phrase_default['footer']['estimatemail'] = $footer;
		$phrase_default['footer']['cancelmail'] = $footer;
		$phrase_default['footer']['othermail'] = $footer;
	}
	wc2_update_option( 'phrase_default', $phrase_default );

	//Essential mark
	$essential_mark = wc2_get_option( 'essential_mark' );
	//if( empty($essential_mark) ) {
		$essential_mark = array(
			'email' => '<span class="required">' . __('*', 'wc2') . '</span>',
			'name1' => '<span class="required">' . __('*', 'wc2') . '</span>',
			'name2' => '',
			'name3' => '',
			'name4' => '',
			'zipcode' => '<span class="required">' . __('*', 'wc2') . '</span>',
			'country' => '<span class="required">' . __('*', 'wc2') . '</span>',
			'pref' => '<span class="required">' . __('*', 'wc2') . '</span>',
			'address1' => '<span class="required">' . __('*', 'wc2') . '</span>',
			'address2' => '',
			'tel' => '<span class="required">' . __('*', 'wc2') . '</span>',
			'fax' => ''
		);
	//}
	$essential_mark = apply_filters( 'wc2_filter_essential_mark', $essential_mark );
	wc2_update_option( 'essential_mark', $essential_mark );

	$noreceipt_status = array(
		'BT', 
	);
	$noreceipt_status = apply_filters( 'wc2_filter_noreceipt_status', $noreceipt_status );
	wc2_update_option( 'noreceipt_status', $noreceipt_status );

	$custom_field_option = array(
		'capa' => array( 'admin' => '管理パネルのみ', 'public' => 'フロントにも表示' ),
		'means' => array( 'select' => 'シングルセレクト', 'radio' => 'ラジオボタン', 'check' => 'チェックボックス', 'text' => 'テキスト', 'textarea' => 'テキストエリア' ),
		'essential' => true,
		'position' => array( 'head' => '先頭', 'beforename' => '名前の前', 'aftername' => '名前の後', 'bottom' => '最後尾', 'other' => 'その他' ),
	);
	wc2_update_option( 'custom_field_option', $custom_field_option );

	//Orderlist refine period
	$order_refine_period = wc2_get_option( 'order_refine_period' );
	//if( empty($order_refine_period) ) {
		$order_refine_period = array(
			'0' => __('This month', 'wc2'),
			'1' => __('Last month', 'wc2'),
			'2' => __('The past one week', 'wc2'),
			'3' => __('Last 30 days', 'wc2'),
			'4' => __('Last 90days', 'wc2'),
			'5' => __('Period specified', 'wc2'),
			'6' => __('All', 'wc2')
		);
	//}
	$order_refine_period = apply_filters( 'wc2_filter_order_refine_period', $order_refine_period );
	wc2_update_option( 'order_refine_period', $order_refine_period );

	do_action( 'wc2_action_initial_setting' );
}

