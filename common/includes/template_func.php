<?php
function wc2_entity_decode( $str ) {
	$pos = strpos( $str, '&' );
	if( $pos !== false )
		$str = htmlspecialchars_decode( $str );
	$str = str_replace( '"', '""', $str );
	return $str;
}

/**************************
* GET: wc2_options
***************************
function wc2_get_option($key){
	$res = WC2_Funcs::get_option($key);
	return $res;
}

/**************************
* UP: wc2_options
***************************
function wc2_update_option( $key, $value ){
	$res = WC2_Funcs::update_option( $key, $value );
	return $res;
}

/**************************
* REMOVE: wc2_options
***************************
function wc2_remove_option($key){
	$res = WC2_Funcs::remove_option($key);
	return $res;
}

/***************************
* 通貨表示国
****************************/
function wc2_crcode(){
	$res = esc_html(WC2_Funcs::get_currency_code());
	return __($res, 'wc2');
}
function wc2_crcode_e(){
	echo wc2_crcode();
}

/***************************
* 通貨マーク
****************************/
function wc2_crsymbol( $js = NULL ) {
	$res = WC2_Funcs::get_crsymbol( $js );
	return $res;
}

/***************************
* 配送設定の国セレクト
****************************/
function wc2_shipping_country_option( $selected = '' ){
	$res = WC2_Funcs::get_shipping_country_option( $selected );
	return $res;
}

/**************************
* ログ
**************************/
function wc2_log($log, $file, $place = ''){
	WC2_Utils::wc2_log($log, $file, $place);
}

/*************************
* 営業状態取得
***************************/
function wc2_get_condition(){
	return WC2_Funcs::get_condition();
}

/**************************
	checked="checked"
***************************/
function wc2_checked( $chk, $key ) {
	return WC2_Funcs::get_checked( $chk, $key );
}

function wc2_checked_e( $chk, $key ){
	esc_attr_e(wc2_checked( $chk, $key ));
}

/**************************
	金額をフォーマット
***************************/
function wc2_crform( $float, $symbol_pre = true, $symbol_post = true, $seperator_flag = true ) {
	$price = esc_html( WC2_Funcs::get_currency( $float, $symbol_pre, $symbol_post, $seperator_flag ) );
	$res = apply_filters( 'wc2_filter_crform', $price, $float );
	return $res;
}

function wc2_crform_e( $float, $symbol_pre = true, $symbol_post = true, $seperator_flag = true ){
	esc_attr_e(wc2_crform( $float, $symbol_pre, $symbol_post, $seperator_flag ));
}

//入力フォームの都道府県・州のセレクト
function wc2_pref_select( $type, $values ){
	$res = WC2_Funcs::get_pref_select( $type, $values );
	echo $res;

}

//settingのaddressformにベース言語があればそれを出力
function wc2_local_addressform(){
	$res = WC2_Funcs::get_local_addressform();
	echo $res;
}

//WPのdefineからベース言語を出力
function wc2_base_country(){
	$res = WC2_Funcs::get_base_country();
	echo $res;
}


//州や都道府県を出力
function wc2_get_states($country) {
	$res = WC2_Funcs::get_states($country);

	echo $res;
}

//入力フォームの国のセレクト
function wc2_target_market_form( $type, $selected ){
	$res = WC2_Funcs::get_target_market_form( $type, $selected );
	echo $res;
}

function wc2_get_target_market_form($type, $selected){
	$res = WC2_Funcs::get_target_market_form( $type, $selected );
	return $res;
}

function wc2_no_image() {
	return '<img src="'.WC2_PLUGIN_URL.'/common/assets/images/no-image.gif" alt="no image">';
}
function wc2_no_image_e() {
	echo wc2_no_image();
}

