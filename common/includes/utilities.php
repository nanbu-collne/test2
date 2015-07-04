<?php
class WC2_Utils
{
	static function disp_var( $val, $title = NULL ){
		$pre = '<pre class="disp_var"><strong>[' . $title . ']</strong><br>' . print_r( $val, true ) . '</pre>';
		if( !is_admin() ){
			add_action( 'wp_footer', create_function('', 'print(\'' . $pre . '\');')); 
		}else{
			add_action( 'in_admin_footer', create_function('', 'print(\'' . $pre . '\');')); 
		}
	}

	static function wc2_log($log, $file, $place = ''){
		$log = date('[Y-m-d H:i:s]', current_time('timestamp')). "\t" .$place. "\n" .$log. "\n";
		$file_path = WC2_PLUGIN_DIR . '/logs/' . $file;
		if( is_dir($file_path) )
			return;
			
		$fp = fopen($file_path, 'a');
		if( false !== $fp ){
			$log = mb_convert_encoding($log, "UTF-8");
			fwrite($fp, $log);
			fclose($fp);
		}
	}

	static function stripslashes_deep_post( $array ){
		$res = array();
		foreach( $array as $key => $value ){
			$key = stripslashes($key);
			if( is_array($value) ){
				$value = WC2_Utils::stripslashes_deep_post( $value );
			}else{
				$value = stripslashes($value);
			}
			$res[$key] = $value;
		}
		return $res;
	}

	static function is_blank($val, $strict=false){
		if ( !is_scalar($val) && NULL != $val ){
			trigger_error("Value is not a scalar", E_USER_NOTICE);
			return true;
		}
		
		if($strict)
			$val = preg_replace("/　/", "", $val);

		$val = trim($val);

		if ( strlen($val) > 0 ){
			return false;
		}else{
			return true; 
		}
	}

	static function is_zero($val){
		if ( !is_scalar($val) && NULL != $val ){
			trigger_error("Value is not a scalar", E_USER_NOTICE);
			return false;
		}
		
		$val = trim($val); 
		if( !self::is_blank($val) && is_numeric($val) && 1 === strlen($val) && 0 === (int)$val )
			return true;
		else
			return false; 
	}

	static function is_entity($entity){
		$temp = substr($entity, 0, 1);
		$temp .= substr($entity, -1, 1);
		if ($temp != '&;')
			return false;
		else
			return true;
	}

	static function get_key( $digit ) {
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$max = strlen($chars) - 1;
		$str = '';
		for($i=0; $i<$digit; $i++){
			$str .= $chars[mt_rand(0, $max)];
		}
		return $str;
	}

	//全角文字ならtrue
	static function is_Em($val){
		if ( !is_scalar($val) && NULL != $val ){
			trigger_error("Value is not a scalar", E_USER_NOTICE);
			return false;
		}
		$val = trim($val);
		if( preg_match("/(?:\xEF\xBD[\xA1-\xBF]|\xEF\xBE[\x80-\x9F])|[\x20-\x7E]/", $val) ){
			return false;
		}else{
			return true;
		}
	}

	//全角カタカナならtrue
	static function is_Katakana($val){
		if( preg_match("/^[ァ-ヶー]+$/u", $val) ){
			return true;
		}else{
			return false;
		}
	}

	//半角数値(整数)ならtrue
	static function is_Number($val){
		if ( !is_scalar($val) && NULL != $val ){
			trigger_error("Value is not a scalar", E_USER_NOTICE);
			return false;
		}
		$val = trim($val);
		if( preg_match('/^[\d]+$/', $val) ){
			return true;
		}else{
			return false;
		}
	}


	//半角英数字ならtrue
	static function is_AlphaNum($val){
		if ( !is_scalar($val) && NULL != $val ){
			trigger_error("Value is not a scalar", E_USER_NOTICE);
			return false;
		}
		$val = trim($val);
		if( preg_match("/^[a-zA-Z0-9]+$/", $val) ){
			return true;
		}else{
			return false;
		}
	}

	//数値 or ハイフンならtrue
	static function is_HyphenNum($val){
		if ( !is_scalar($val) && NULL != $val ){
			trigger_error("Value is not a scalar", E_USER_NOTICE);
			return false;
		}
		$val = trim($val);
		if( preg_match( '/^[\d-]+$/', $val ) ){
			return true;
		}else{
			return false;
		}
	}

	//日付型チェック
	static function is_Date($val){
		if( strptime( $val, '%Y-%m-%d' ) ){
		 	return true;
		}else{
			return false;
		}
	}

	//日付時間型チェック
	static function is_DateTime($val){
		if( strptime( $val, '%Y-%m-%d %H:%M:%S' ) ){
		 	return true;
		}else{
			return false;
		}
	}

}

//template
function wc2_stripslashes_deep_post($array){
	$res = WC2_Utils::stripslashes_deep_post( $array );
	return $res;
}

function wc2_is_blank($val, $strict=false){
	$res = WC2_Utils::is_blank($val, $strict);
	return $res;
}

function wc2_get_key( $digit ) {
	$res = WC2_Utils::get_key( $digit );
	return $res;
}

function wc2_is_Em($val){
	$res = WC2_Utils::is_Em($val);
	return $res;
}

function wc2_is_Katakana($val){
	$res = WC2_Utils::is_Katakana($val);
	return $res;
}

function wc2_is_Number($val){
	$res = WC2_Utils::is_Number($val);
	return $res;
}

function wc2_is_AlphaNum($val){
	$res = WC2_Utils::is_AlphaNum($val);
	return $res;
}

function wc2_is_HyphenNum($val){
	$res = WC2_Utils::is_HyphenNum($val);
	return $res;
}

function wc2_is_Date($val){
	return WC2_Utils::is_Date($val);
}

function wc2_is_DateTime($val){
	return WC2_Utils::is_DateTime($val);
}