<?php

/* Tables */
//const TABLE_MEMBER = 'wc2_member';

class WC2_Mail
{
	/* Private variables */
	protected $mail_data = array();
	protected $content_type = ''; //Value is 'html' Content-type: text/html 
	protected static $instance = null;

	/* Constructor */
	private function __construct() {
		$plugin = Welcart2::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();
		if( 'html' == $this->content_type ){
			add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
		}
		//Initial processing.
		//$this->_set_table();
	}


	/* Public functions */

	public static function get_instance() {
		if( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	// Set values
	public function set_customer_para_value( $key, $value ){
		$this->mail_data['customer_para'][$key] = $value;
	}

	public function set_admin_para_value( $key, $value ){
		$this->mail_data['admin_para'][$key] = $value;
	}

	// Get values
	public function get_customer_para_value( $key ) {
		$value = ( is_array($this->mail_data['customer_para']) and array_key_exists( $key, $this->mail_data['customer_para'] ) ) ? $this->mail_data['customer_para'][$key] : '';
		$value = maybe_unserialize($value);
		return $value;
	}

	public function get_admin_para_value( $key ) {
		$value = ( is_array($this->mail_data['admin_para']) and array_key_exists( $key, $this->mail_data['admin_para'] ) ) ? $this->mail_data['admin_para'][$key] : '';
		$value = maybe_unserialize($value);
		return $value;
	}

	//Clear values
	public function clear_column() {
		$this->mail_data = array();
	}

	//利用客に送信
	function send_customer_mail(){
		$res = false;
		$general = wc2_get_option('general');
		$para = array(
				'to_name' => $this->mail_data['customer_para']['to_name'],
				'to_address' => $this->mail_data['customer_para']['to_address'],
				'from_name' => $this->mail_data['customer_para']['from_name'],
				'from_address' => $this->mail_data['customer_para']['from_address'],
				'return_path' => $this->mail_data['customer_para']['return_path'],
				'subject' => $this->mail_data['customer_para']['subject'],
				'message' => $this->mail_data['customer_para']['message'],
				);
		$res = $this->send_mail( $para );

		return $res;
	}

	//管理者へ送信
	function send_admin_mail(){
		$res = false;
		$general = wc2_get_option('general');
		$para = array(
				'to_name' => $this->mail_data['admin_para']['to_name'],
				'to_address' => $this->mail_data['admin_para']['to_address'],
				'from_name' => $this->mail_data['admin_para']['from_name'],
				'from_address' => $this->mail_data['admin_para']['from_address'],
				'return_path' => $this->mail_data['admin_para']['return_path'],
				'subject' => $this->mail_data['admin_para']['subject'],
				'message' => do_shortcode($this->mail_data['admin_para']['message']),
				);
		$res = $this->send_mail( $para );

		return $res;
	}

	//メール送信
	public function send_mail($para){
		$from_name = $para['from_name'];
		$from_address = $para['from_address'];
		if (strpos($para['from_address'], '..') !== false || strpos($para['from_address'], '.@') !== false) {
			$fname = str_replace(strstr($para['from_address'], '@'), '', $para['from_address']);
			if( '"' != substr($fname, 0, 1) && '"' != substr($fname, -1) ){
				$para['from_address'] = str_replace($fname, '"RFC_violation"', $para['from_address']);
				$from_name = $para['from_name'] . '(' . $from_address . ')';
			}
		}
		$from_name = mb_encode_mimeheader($from_name);
		$from = htmlspecialchars(html_entity_decode($from_name, ENT_QUOTES));
		$para['from_name'] = $from;

		$this->mail_para = $para;
		add_action('phpmailer_init', 'wc2_send_mail_init', 11);

		$subject = html_entity_decode($para['subject'], ENT_QUOTES);
		if('html' == $this->content_type){
			$message = nl2br($para['message']);
		}else{
			$message = $para['message'];
		}

/*
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
*/
		$mails = explode( ',', $para['to_address'] );
		$to_mailes = array();
		foreach( $mails as $mail ){
			if( is_email( trim($mail) ) ){
				$to_mailes[] = $mail;
			}
		}
		if( !empty( $to_mailes ) ){
			$res = @wp_mail( $to_mailes , $subject , $message );
		}else{
			$res = false;
		}
		
		remove_action('phpmailer_init','wc2_send_mail_init', 11);
		$this->mail_para = array();
		return $res;
	}

	//メール送信用
	public function send_mail_init($phpmailer){
		$phpmailer->Mailer = 'mail';
		$phpmailer->From = $this->mail_para['from_address'];
		$phpmailer->FromName = apply_filters('wc2_filter_send_mail_from', $this->mail_para['from_name'], $this->mail_para);
		$phpmailer->Sender = $this->mail_para['from_address'];

		do_action('wc2_filter_phpmailer_init', array( &$phpmailer ));
	}
}

function wc2_send_mail($para){
	$wc2_mail = WC2_Mail::get_instance();
	return $wc2_mail->send_mail($para);
}

function wc2_send_mail_init($phpmailer){
	$wc2_mail = WC2_Mail::get_instance();
	return $wc2_mail->send_mail_init($phpmailer);
}
