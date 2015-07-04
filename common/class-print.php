<?php
class WC2_PRINT {
	/* public variables */

	/* protected variables */
	protected $pdf = '';
	protected $font = '';

	protected static $instance = null;

	public static function get_instance() {
		if( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	private function __construct() {
		$plugin = Welcart2::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();
	}

	public function print_process() {
		if ( !class_exists('FPDI') ){
			require_once(WC2_PLUGIN_DIR.'/common/includes/pdf/tcpdf/config/lang/jpn.php');
			require_once(WC2_PLUGIN_DIR.'/common/includes/pdf/tcpdf/tcpdf.php');
			require_once(WC2_PLUGIN_DIR.'/common/includes/pdf/fpdi/fpdi.php');
			//require_once(WC2_PLUGIN_DIR.'/classes/orderData.class.php');

			//用紙サイズ(B5)
			$pdf = new FPDI('P', 'mm', 'B5', true, array(182, 257),'UTF-8');
		}
		//Custom return $pdf->addTTFfont( WC2_PLUGIN_DIR .'/common/includes/pdf/tcpdf/fonts/add_font_name.php');
		$this->font = apply_filters( 'wc2_filter_pdf_font', 'msgothic', $pdf );

		//$wc2_pdfo = new orderDataObject($_REQUEST['order_id']);
		$wc2_order = WC2_DB_Order::get_instance();
		$order_id = $_GET['order_id'];
		$order_data = $wc2_order->get_order_data($order_id);
		$this->pdf_out($pdf, $order_data);
		die();
	}

	public function conv_enc( $str ) {
		$str = apply_filters( 'wc2_filter_pdf_conv_enc', $str );
		return $str;
	}

	public function pdf_out( $pdf, $data ) {
		$pdf->setPrintHeader( false );
		$pdf->setPrintFooter( false );

		//PDF出力基本設定
		//******************************************************

		$border = 0;//セルのボーダー初期値

		// テンプレートファイル
		//$tplfile = WC2_PLUGIN_DIR."/common/includes/pdf/templates/orderform_B5.pdf";
		//$tplfile = apply_filters( 'wc2_filter_pdf_template', $tplfile );

		//$pagecount = $pdf->setSourceFile($tplfile);
		//$tplidx = $pdf->importPage(1);
		$pdf->SetLeftMargin(0);
		$pdf->SetTopMargin(0);
		$pdf->addPage();

		$font = $this->font;

		// 文書情報設定
		$pdf->SetCreator('Welcart');
		$pdf->SetAuthor('Collne Inc.');
		switch( $_REQUEST['type'] ) {
			//見積書
			case 'estimate':
				$pdf->SetTitle(__('見積書', 'wc2'));
				$filename = 'estimate_' . $data['dec_order_id'] . '.pdf';
				break;
			//納品書
			case 'deliveryslip':
				$pdf->SetTitle(__('納品書', 'wc2'));
				$filename = 'deliveryslip' . $data['dec_order_id'] . '.pdf';
				break;
			//領収書
			case 'receipt':
				$pdf->SetTitle(__('領収書', 'wc2'));
				$filename = 'receipt_' . $data['dec_order_id'] . '.pdf';
				break;
			//請求書
			case 'invoice':
				$pdf->SetTitle(__('請求書', 'wc2'));
				$filename = 'invoice_' . $data['dec_order_id'] . '.pdf';
				break;
		}

		//表示モードを指定する。
		$pdf->SetDisplayMode('real', 'continuous');

		// 総ページ数のエイリアスを定義する。
		// エイリアスはドキュメントをクローズするときに置換する。
		// '{nb}' で総ページ数が得られる

		$pdf->getAliasNbPages();

		//自動改ページモード
		$pdf->SetAutoPageBreak(true , 5);

		$pdf->SetFillColor(255, 255, 255);

		//**************************************************************
		$page = 1;//ページ数の初期化

		//--------------------------------------------------------------
		$this->pdfSetHeader($pdf, $data, $page);
		//$pdf->SetDrawColor(255,0,0);
		$border = 0;

		$pdf->SetLeftMargin(19.8);
		$x = 15.8;
		$y = 101;
		$onep = apply_filters( 'wc2_filter_pdf_page_height', 190 );
		$pdf->SetXY($x, $y);
		$next_y = $y;
		$line_x = array();
		$cart = $data['cart'];

		for( $index = 1; $index <= count($cart); $index++ ) {
			 $cart_row = $cart[$index];

			//if ($cnt > $pageRec-1) {//ページが変わるときの処理
			if( $onep < $next_y ) {//ページが変わるときの処理

				$pdf->addPage();
				//$pdf->useTemplate($tplidx);

				//-----------------------------------------------------
				$this->pdfSetHeader($pdf, $data, $page);

				$x = 15.8;
				$y = 101;
				$pdf->SetXY($x, $y);
				$next_y = $y;
			}

			//---------------------------------------------------------
			$item_name = $cart_row['item_name'];
			$item_code = $cart_row['item_code'];
			$sku_name = $cart_row['sku_name'];
			$sku_code = $cart_row['sku_code'];
			$cart_item_name = wc2_get_cart_item_name( $item_name, $item_code, $sku_name, $sku_code );
			$cart_row['options'] = isset( $cart_row['options'] ) ? $cart_row['options']: '';
			$optstr =  '';
			if( is_array($cart_row['options']) && count($cart_row['options']) > 0 ){
				foreach($cart_row['options'] as $key => $value){
					if( !empty($key) ) {
						$key = urldecode($key);
						$value = maybe_unserialize($value);
						if(is_array($value)) {
							$c = '';
							$optstr .= esc_html($key) . ' = ';
							foreach($value as $v) {
								$optstr .= $c.esc_html(urldecode($v));
								$c = ', ';
							}
							$optstr .= "\n";
						} else {
							$optstr .= esc_html($key) . ' = ' . esc_html(urldecode($value)) . "\n";
						}
					}
				}
				$optstr = apply_filters( 'wc2_filter_option_pdf', $optstr, $cart_row['options'] );
			}
			$optstr = apply_filters( 'wc2_filter_all_option_pdf', $optstr, $cart_row, $sku_code );

			$line_y[$index] = $next_y;

			list($fontsize, $lineheight, $linetop) = $this->set_font_size(8);	//10->8
			$pdf->SetFont( $font, '', $fontsize);
			$pdf->SetXY($x-0.2, $line_y[$index]+0.8);
			$pdf->MultiCell(4, $lineheight, '*', $border, 'C');
			$pdf->SetXY($x+3.0, $line_y[$index]);
			$pdf->MultiCell(84.6, $lineheight, $this->conv_enc($cart_item_name), $border, 'L');
			if( 'receipt' != $_REQUEST['type'] ){
				list($fontsize, $lineheight, $linetop) = $this->set_font_size(8);
				$pdf->SetFont($font, '', $fontsize);
				$pdf->SetXY($x+6.0, $pdf->GetY()+$linetop);
				$pdf->MultiCell(81.6, $lineheight-0.2, $this->conv_enc($optstr), $border, 'L');
			}

			$pdf_args = compact( 'page', 'x', 'y', 'onep', 'next_y', 'line_x', 'border', 'index', 'cart_row' );
			do_action( 'wc2_action_order_print_cart_row', $pdf, $data, $pdf_args );

			$next_y = $pdf->GetY()+2;
			list($fontsize, $lineheight, $linetop) = $this->set_font_size(7);
			$pdf->SetFont( $font, '', $fontsize);
			$pdf->SetXY($x+88.0, $line_y[$index]);
			$pdf->MultiCell(11.5, $lineheight, $this->conv_enc($cart_row['quantity']), $border, 'R');
			list($fontsize, $lineheight, $linetop) = $this->set_font_size(7);
			$pdf->SetFont( $font, '', $fontsize);
			$pdf->SetXY($x+99.6, $line_y[$index]);
			$pdf->MultiCell(11.5, $lineheight, $this->conv_enc( $cart_row['unit'] ), $border, 'C');
			$pdf->SetXY($x+111.5, $line_y[$index]);
			list($fontsize, $lineheight, $linetop) = $this->set_font_size(7);
			$pdf->SetFont( $font, '', $fontsize);
			$pdf->MultiCell(15.2, $lineheight, $this->conv_enc(wc2_get_currency($cart_row['price'])), $border, 'R');
			$pdf->SetXY($x+126.9, $line_y[$index]);
			list($fontsize, $lineheight, $linetop) = $this->set_font_size(7);
			$pdf->SetFont( $font, '', $fontsize);
			$pdf->MultiCell(22.8, $lineheight, apply_filters( 'wc2_filter_cart_row_price_pdf', $this->conv_enc(wc2_get_currency($cart_row['price']*$cart_row['quantity'])), $cart_row), $border, 'R');

			if( $onep < $next_y && 0 < $index ){
				$pdf->Rect($x, $line_y[$index]-0.4, 149.5, 197.4-$line_y[$index], 'F');

				$pdf->SetXY($x, 193);
				$pdf->MultiCell(88, $lineheight, $this->conv_enc(__('Continued on next page.', 'wc2')), $border, 'C');

				$this->pdfSetLine($pdf);
				$this->pdfSetFooter($pdf, $data);
				$index--;
				$page++;
			}
		}

		$this->pdfSetLine($pdf);
		$this->pdfSetFooter($pdf, $data);

		@ob_end_clean();	//error表示を取り除く

		// Output
		//*****************************************************************
		$pdf->Output($filename, 'I');
	}

	//Header
	public function pdfSetHeader( $pdf, $data, $page ) {
		$wc2_options = wc2_get_option();

		$border = 0;//border of cells
		$font = $this->font;

		switch( $_REQUEST['type'] ) {
			case  'estimate':
				$title = apply_filters( 'wc2_filter_pdf_estimate_title', __('Estimate', 'wc2'), $data );
				$message = sprintf(__("Thank you for choosing '%s'. We estimate your orders.", 'wc2'), apply_filters('wc2_filter_publisher', get_option('blogname')));
				$message = apply_filters('wc2_filter_pdf_estimate_message', $message, $data);
				$juchubi = apply_filters( 'wc2_filter_pdf_estimate_validdays', __('Valid:7days', 'wc2'), $data );
				$siharai = ' ';
				$sign_image = apply_filters('wc2_filter_pdf_estimate_sign', NULL);
				$effective_date = date(__('M j, Y', 'wc2'), strtotime($data['order_date']));
				break;

			case 'deliveryslip':
				$title = apply_filters( 'wc2_filter_pdf_deliveryslip_title', __('Delivery Statement', 'wc2'), $data );
				$message = sprintf(__("Thank you for choosing '%s'. We deliver your items as following.", 'wc2'), apply_filters('wc2_filter_publisher', get_option('blogname')));
				$message = apply_filters('wc2_filter_pdf_deliveryslip_message', $message, $data);
				$juchubi = __('Order Date', 'wc2').' : '.date(__('M j, Y', 'wc2'), strtotime($data['order_date']));
				$siharai = __('Payment division', 'wc2').' : ' . apply_filters('wc2_filter_pdf_payment_name', $data['payment_name'], $data);
				$sign_image = apply_filters('wc2_filter_pdf_deliveryslip_sign', NULL);

				if( !empty($data['delidue_date']) && '#none#' != $data['delidue_date'] ){
					$effective_date = date(__('M j, Y', 'wc2'), strtotime($data['delidue_date']));
				}else{
					if( empty($data['order_modified']) )
						$effective_date = date(__('M j, Y', 'wc2'), current_time('timestamp', 0));
					else
						$effective_date = date(__('M j, Y', 'wc2'), strtotime($data['order_modified']));
				}
				break;

			case 'receipt':
				$title = apply_filters( 'wc2_filter_pdf_receipt_title', __('Receipt', 'wc2'), $data );
				$message = apply_filters('wc2_filter_pdf_receipt_message', __("Your payment has been received.", 'wc2'), $data);
				$juchubi = __('Order Date', 'wc2').' : '.date(__('M j, Y', 'wc2'), strtotime($data['order_date']));
				$siharai = __('Payment division', 'wc2').' : ' . apply_filters('wc2_filter_pdf_payment_name', $data['payment_name'], $data);
				$sign_image = apply_filters('wc2_filter_pdf_receipt_sign', NULL);
				$receipted_date = $data['receipted_date'];
				if( empty($receipted_date) )
					$effective_date = date(__('M j, Y', 'wc2'), current_time('timestamp', 0));
				else
					$effective_date = date(__('M j, Y', 'wc2'), strtotime($receipted_date));
				break;

			case 'invoice':
				$title = apply_filters( 'wc2_filter_pdf_invoice_title', __('Invoice', 'wc2'), $data );
				$message = apply_filters('wc2_filter_pdf_invoice_message', __("Please remit payment at your earliest convenience.", 'wc2'), $data);
				$juchubi = __('Order Date', 'wc2').' : '.date(__('M j, Y', 'wc2'), strtotime($data['order_date']));
				$siharai = __('Payment division', 'wc2').' : ' . apply_filters('wc2_filter_pdf_payment_name', $data['payment_name'], $data);
				$sign_image = apply_filters('wc2_filter_pdf_invoice_sign', NULL);
				$effective_date = date(__('M j, Y', 'wc2'), current_time('timestamp', 0));
				break;
		}
		$effective_date = apply_filters('wc2_filter_pdf_effective_date', $effective_date, $_REQUEST['type'], $data);

		$pdf->SetLineWidth(0.4);
		$pdf->Line(65, 23, 110, 23);
		$pdf->SetLineWidth(0.1);
		$pdf->Line(124, 19, 167, 19);
		list($fontsize, $lineheight, $linetop) = $this->set_font_size(9);
		$pdf->SetFont($font, '', $fontsize);
		$pdf->SetXY(125, 15.0);
		$pdf->Write(5, 'No.');

		// Title
		list($fontsize, $lineheight, $linetop) = $this->set_font_size(15);
		$pdf->SetFont($font, '', $fontsize);
		$pdf->SetXY(63, 16);
		$pdf->MultiCell(50, $lineheight, $this->conv_enc($title), $border, 'C');

		// Date
		list($fontsize, $lineheight, $linetop) = $this->set_font_size(9);
		$pdf->SetFont($font, '', $fontsize);
		$pdf->SetXY(64, 24.2);
		$pdf->MultiCell(45.5, $lineheight, $this->conv_enc($effective_date), $border, 'C');

		// Order No.
		$pdf->SetXY(131, 15);
		$pdf->MultiCell(36, $lineheight,  $data['dec_order_id'], $border, 'R');

		// Page No.
		list($fontsize, $lineheight, $linetop) = $this->set_font_size(13);
		$pdf->SetFont($font, '', $fontsize);
		$pdf->SetXY(15.5, 15.4);

		$pdf->Cell( 20, 7, ' ' . $page . '/ ' . $pdf->getAliasNbPages(), 1);

		$width = 80;
		$leftside = 15;
		$pdf->SetLeftMargin($leftside);

		$person_honor = ( 'JP' == $wc2_options['system']['currency'] ) ? "様" : '';
		$person_honor = apply_filters( 'wc2_filters_pdf_person_honor', $person_honor );
		$company_honor = ( 'JP' == $wc2_options['system']['currency'] ) ? "御中" : '';
		$company_honor = apply_filters( 'wc2_filters_pdf_company_honor', $company_honor );
		$currency_post = ( 'JP' == $wc2_options['system']['currency'] ) ? "-" : '';
		$currency_post = apply_filters( 'wc2_filters_pdf_currency_post', $currency_post );

		if( 'receipt' == $_REQUEST['type'] ){
			$top = 40;

			$company = isset($data['custom_customer']['company']) ? $data['custom_customer']['company']: '';
			list($fontsize, $lineheight, $linetop) = $this->set_font_size(12);
			$pdf->SetFont($font, '', $fontsize);
			$pdf->SetXY($leftside, $top);

			if( empty( $company ) || !isset( $wc2_options['cscs_company'] ) ){
				$pdf->MultiCell($width, $lineheight, $this->conv_enc($this->get_pdf_name( $data )), $border, 'L');
				$x = $leftside + $width;
				$y = $pdf->GetY() - $lineheight;
				$pdf->SetXY($x, $y);
				$pdf->Write($lineheight ,$this->conv_enc( $person_honor ));
			}else{
				$pdf->MultiCell($width, $lineheight, $this->conv_enc($company), $border, 'L');
				$x = $leftside + $width;
				$y = $pdf->GetY() - $lineheight;
				$pdf->SetXY($x, $y);
				$pdf->Write($lineheight ,$this->conv_enc( $company_honor ));
			}
			$y = $pdf->GetY() + $lineheight + $linetop;
			$pdf->SetLineWidth(0.1);
			$pdf->Line($leftside, $y, $leftside+$width+7, $y);

			//Total
			$y = $pdf->GetY() + $lineheight + 7;
			list($fontsize, $lineheight, $linetop) = $this->set_font_size(20);
			$pdf->SetFont($font, '', $fontsize);
			$pdf->SetXY($leftside+2, $y);
			$total_price = $data['item_total_price'] - $data['usedpoint'] + $data['discount'] + $data['shipping_charge'] + $data['cod_fee'] + $data['tax'];

			$pdf->MultiCell($width, $lineheight+2, $this->conv_enc(wc2_get_currency($total_price, true, false) . $currency_post, 1), 'C');

			// Message
			$y = $pdf->GetY() + $lineheight;
			list($fontsize, $lineheight, $linetop) = $this->set_font_size(9);
			$pdf->SetFont($font, '', $fontsize);
			$pdf->SetXY($leftside, $y);
			$pdf->MultiCell($width+70, $lineheight, $this->conv_enc($message), $border, 'L');

			// Label
			list($fontsize, $lineheight, $linetop) = $this->set_font_size(10);
			$pdf->SetFont($font, '', $fontsize);
			$y = 89;
			$pdf->SetXY($leftside, $y);
			$pdf->MultiCell(75, $lineheight, $this->conv_enc(__('Statement', 'wc2')), $border, 'L');

		}elseif( 'deliveryslip' == $_REQUEST['type'] ){
			//「配送先を宛名とする」
			if( $wc2_options['system']['pdf_delivery'] == 1 ){
				$top = 30;

				$deliveri_company = isset($data['custom_delivery']['company']) ? $data['custom_delivery']['company']: '';
				list($fontsize, $lineheight, $linetop) = $this->set_font_size(12);
				$pdf->SetFont($font, '', $fontsize);
				$pdf->SetXY($leftside, $top);

				if( empty( $deliveri_company ) || !isset( $wc2_options['csde_company'] ) ){
					$pdf->MultiCell($width, $lineheight, $this->conv_enc($this->get_pdf_shipping_name( $data )), $border, 'L');
					$x = $leftside + $width;
					$y = $pdf->GetY() - $lineheight;
					$pdf->SetXY($x, $y);
					$pdf->Write($lineheight, $this->conv_enc( $person_honor ));
					$y = $pdf->GetY() + $lineheight + $linetop + 2;
				}else{
					$pdf->MultiCell($width, $lineheight, $this->conv_enc($deliveri_company), $border, 'L');
					$x = $leftside + $width;
					$y = $pdf->GetY() - $lineheight;
					$pdf->SetXY($x, $y);
					$pdf->Write($lineheight ,$this->conv_enc( $company_honor ));
					$y = $pdf->GetY() + $lineheight + $linetop;
					list($fontsize, $lineheight, $linetop) = $this->set_font_size(8);
					$pdf->SetFont($font, '', $fontsize);
					$pdf->SetXY($leftside, $y);
					$pdf->MultiCell($width, $lineheight, $this->conv_enc(__("Attn", 'wc2') . ' : ' . $this->get_pdf_shipping_name( $data ) . $person_honor ), $border, 'L');
					$y = $pdf->GetY() + $linetop + 2;
				}
				// Address
				list($fontsize, $lineheight, $linetop) = $this->set_font_size(8);
				$pdf->SetFont($font, '', $fontsize);

				$this->get_pdf_shipping_address($pdf, $data, $y, $linetop, $leftside, $width, $lineheight);
				$pdf->MultiCell($width, $lineheight, $this->conv_enc('TEL ' . $data['delivery'][0]['tel']), $border, 'L');

				if( !empty($data['delivery'][0]['fax']) ){
					$y = $pdf->GetY() + $linetop;
					$pdf->SetXY($leftside, $y);
					$pdf->MultiCell($width, $lineheight, $this->conv_enc('FAX ' . $data['delivery'][0]['fax']), $border, 'L');
				}
			//「購入者情報を宛名とする」
			}else{
				$top = 30;

				$company = isset($data['custom_customer']['company']) ? $data['custom_customer']['company']: '';
				list($fontsize, $lineheight, $linetop) = $this->set_font_size(12);
				$pdf->SetFont($font, '', $fontsize);
				$pdf->SetXY($leftside, $top);

				if( empty( $company ) || !isset( $wc2_options['cscs_company'] ) ){
					$pdf->MultiCell($width, $lineheight, $this->conv_enc($this->get_pdf_name( $data )), $border, 'L');
					$x = $leftside + $width;
					$y = $pdf->GetY() - $lineheight;
					$pdf->SetXY($x, $y);
					$pdf->Write($lineheight ,$this->conv_enc( $person_honor ));
					$y = $pdf->GetY() + $lineheight + $linetop + 2;
				}else{
					$pdf->MultiCell($width, $lineheight, $this->conv_enc($company), $border, 'L');
					$x = $leftside + $width;
					$y = $pdf->GetY() - $lineheight;
					$pdf->SetXY($x, $y);
					$pdf->Write($lineheight ,$this->conv_enc( $company_honor ));
					$y = $pdf->GetY() + $lineheight + $linetop;
					list($fontsize, $lineheight, $linetop) = $this->set_font_size(8);
					$pdf->SetFont($font, '', $fontsize);
					$pdf->SetXY($leftside, $y);
					$pdf->MultiCell($width, $lineheight, $this->conv_enc(__("Attn", 'wc2') . ' : ' . $this->get_pdf_name( $data ) . $person_honor ), $border, 'L');
					$y = $pdf->GetY() + $linetop + 2;
				}
				// Address
				list($fontsize, $lineheight, $linetop) = $this->set_font_size(8);
				$pdf->SetFont($font, '', $fontsize);

				$this->get_pdf_address($pdf, $data, $y, $linetop, $leftside, $width, $lineheight);

				$pdf->MultiCell($width, $lineheight, $this->conv_enc('TEL ' . $data['tel']), $border, 'L');

				if( !empty($data['fax']) ){
					$y = $pdf->GetY() + $linetop;
					$pdf->SetXY($leftside, $y);
					$pdf->MultiCell($width, $lineheight, $this->conv_enc('FAX ' . $data['fax']), $border, 'L');
				}
				//配送先情報
				$customer_name = trim( $data['name1'] ) . trim( $data['name2'] );
				$deliveri_name = trim( $data['delivery'][0]['name1'] ) . trim( $data['delivery'][0]['name2'] );
				$customer_zip = trim( $data['zipcode'] );
				$deliveri_zip = trim( $data['delivery'][0]['zipcode'] );
				$customer_address = trim( $data['address1'] ) . trim( $data['address2']);
				$deliveri_address = trim( $data['delivery'][0]['address1'] ) . trim( $data['delivery'][0]['address2']);

				//発送先が入力されているとき
				if( !empty($deliveri_address) ){
					//購入者と発送先情報が異なるとき
					if( $customer_name != $deliveri_name || $customer_zip != $deliveri_zip || $customer_address != $deliveri_address){
						// Line	
						$y = $pdf->GetY() + $linetop;
						$pdf->SetLineWidth(0.1);
						$pdf->Line( $leftside, $y, $leftside+$width+5, $y );

						//【配送先】タイトル
						list($fontsize, $lineheight, $linetop) = $this->set_font_size(8);	// 10->8
						$y = $pdf->GetY() + $linetop + 1;
						$pdf->SetFont($font, '', $fontsize);
						$pdf->SetXY($leftside, $y);
						$pdf->MultiCell($width, $lineheight, $this->conv_enc( __( "** A shipping address **", 'wc2' ) ), $border, 'L');

						//配送先宛名
						$delivery_company = isset($data['custom_delivery']['company']) ? $data['custom_delivery']['company']: '';
						list($fontsize, $lineheight, $linetop) = $this->set_font_size(6);
						$y = $pdf->GetY() + $linetop;
						$pdf->SetFont($font, '', $fontsize);
						$pdf->SetXY($leftside, $y);
						if( empty( $deliveri_company ) || !isset( $wc2_options['csde_company'] ) ){
							$pdf->MultiCell($width, $lineheight, $this->conv_enc( $this->get_pdf_shipping_name( $data ) ), $border, 'L');
							$x = $leftside + $width;
							$y = $pdf->GetY() - $lineheight - $linetop;
							$pdf->SetXY($x, $y);
							$pdf->Write($lineheight ,$this->conv_enc( $person_honor ));	//様
							$y = $pdf->GetY() + $lineheight + $linetop;
						}else{
							$pdf->MultiCell($width, $lineheight, $this->conv_enc($deliveri_company), $border, 'L');
							$x = $leftside + $width;
							$y = $pdf->GetY() - $lineheight;
							$pdf->SetXY($x, $y);
							$pdf->Write($lineheight, $this->conv_enc( $company_honor ));	//御中
							$y = $pdf->GetY() + $lineheight + $linetop;
							list($fontsize, $lineheight, $linetop) = $this->set_font_size(6);
							$pdf->SetFont($font, '', $fontsize);
							$pdf->SetXY($leftside, $y);
							$pdf->MultiCell($width, $lineheight, $this->conv_enc(__("Attn", 'wc2') . ' : ' . $this->get_pdf_shipping_name( $data ) . $person_honor ), $border, 'L');
							$y = $pdf->GetY() + $linetop;
						}
						//配送先住所
						list($fontsize, $lineheight, $linetop) = $this->set_font_size(6);
						$pdf->SetFont($font, '', $fontsize);
						$this->get_pdf_shipping_address($pdf, $data, $y, $linetop, $leftside, $width, $lineheight);

						//配送先電話番号
						$pdf->MultiCell($width, $lineheight, $this->conv_enc('TEL ' . $data['delivery'][0]['tel']), $border, 'L');
					}
				}
			}
			$y = $pdf->GetY() + $linetop + 0.5;

			$pdf->SetLineWidth(0.1);
			$pdf->Line($leftside, $y, $leftside+$width+5, $y);

			// Message
			$y = 80;
			list($fontsize, $lineheight, $linetop) = $this->set_font_size(9);
			$pdf->SetFont($font, '', $fontsize);
			$pdf->SetXY($leftside, $y);
			$pdf->MultiCell($width+70, $lineheight, $this->conv_enc($message), $border, 'L');

			// Order date
			list($fontsize, $lineheight, $linetop) = $this->set_font_size(10);
			$pdf->SetFont($font, '', $fontsize);
			$y = 89;
			$pdf->SetXY($leftside, $y);
			$pdf->MultiCell(75, $lineheight, $this->conv_enc($juchubi), $border, 'L');

			// Payment method
			$pdf->SetXY($leftside+68, $y);
			$pdf->Cell(75, $lineheight, $this->conv_enc($siharai), $border, 1, 'L');

		}else{
			$top = 30;
			$company = isset($data['custom_customer']['company']) ? $data['custom_customer']['company']: '';

			list($fontsize, $lineheight, $linetop) = $this->set_font_size(12);
			$pdf->SetFont($font, '', $fontsize);
			$pdf->SetXY($leftside, $top);

			if( empty( $company ) || !isset( $wc2_options['cscs_company'] ) ){
				$pdf->MultiCell($width, $lineheight, $this->conv_enc($this->get_pdf_name( $data )), $border, 'L');
				$x = $leftside + $width;
				$y = $pdf->GetY() - $lineheight;
				$pdf->SetXY($x, $y);
				$pdf->Write($lineheight ,$this->conv_enc( $person_honor ));
				$y = $pdf->GetY() + $lineheight + $linetop + 2;
			}else{
				$pdf->MultiCell($width, $lineheight, $this->conv_enc($company), $border, 'L');
				$x = $leftside + $width;
				$y = $pdf->GetY() - $lineheight;
				$pdf->SetXY($x, $y);
				$pdf->Write($lineheight ,$this->conv_enc( $company_honor ));
				$y = $pdf->GetY() + $lineheight + $linetop;
				list($fontsize, $lineheight, $linetop) = $this->set_font_size(8);
				$pdf->SetFont($font, '', $fontsize);
				$pdf->SetXY($leftside, $y);
				$pdf->MultiCell($width, $lineheight, $this->conv_enc(__("Attn", 'wc2') . ' : ' . $this->get_pdf_name( $data ) . $person_honor ), $border, 'L');
				$y = $pdf->GetY() + $linetop + 2;
			}
			// Address
			list($fontsize, $lineheight, $linetop) = $this->set_font_size(8);
			$pdf->SetFont($font, '', $fontsize);

			$this->get_pdf_address($pdf, $data, $y, $linetop, $leftside, $width, $lineheight);

			$pdf->MultiCell($width, $lineheight, $this->conv_enc('TEL ' . $data['tel']), $border, 'L');

			if( !empty($data['fax']) ){
				$y = $pdf->GetY() + $linetop;
				$pdf->SetXY($leftside, $y);
				$pdf->MultiCell($width, $lineheight, $this->conv_enc('FAX ' . $data['fax']), $border, 'L');
			}
			$y = $pdf->GetY() + $linetop + 0.5;

			$pdf->SetLineWidth(0.1);
			$pdf->Line($leftside, $y, $leftside+$width+5, $y);

			// Message
			$y = 80;
			list($fontsize, $lineheight, $linetop) = $this->set_font_size(9);
			$pdf->SetFont($font, '', $fontsize);
			$pdf->SetXY($leftside, $y);
			$pdf->MultiCell($width+70, $lineheight, $this->conv_enc($message), $border, 'L');

			// Order date
			list($fontsize, $lineheight, $linetop) = $this->set_font_size(10);
			$pdf->SetFont($font, '', $fontsize);
			$y = 89;
			$pdf->SetXY($leftside, $y);
			$pdf->MultiCell(75, $lineheight, $this->conv_enc($juchubi), $border, 'L');

			// Payment method
			$pdf->SetXY($leftside+68, $y);
			$pdf->Cell(75, $lineheight, $this->conv_enc($siharai), $border, 1, 'L');
		}

		// My company
		if( !empty($sign_image) ){
			$sign_data = apply_filters( 'wc2_filter_pdf_sign_data', array(140, 40, 25, 25));
			$pdf->Image($sign_image, $sign_data[0], $sign_data[1], $sign_data[2], $sign_data[3]);
		}
		$x = 110;
		$y = 45;
		$pdf->SetLeftMargin($x);
		list($fontsize, $lineheight, $linetop) = $this->set_font_size(9);
		$pdf->SetFont($font, '', $fontsize);
		$pdf->SetXY($x, $y);
		$pdf->MultiCell(60, $lineheight, $this->conv_enc(apply_filters('wc2_filter_publisher', get_option('blogname'))), 0, 'L');
		list($fontsize, $lineheight, $linetop) = $this->set_font_size(8);
		$pdf->SetFont($font, '', $fontsize);
		$pdf->MultiCell(60, $lineheight, $this->conv_enc(apply_filters('wc2_filter_pdf_mycompany', $wc2_options['general']['company_name'])), 0, 'L');
		$this->get_pdf_myaddress($pdf, $lineheight );
		$pdf->MultiCell(60, $lineheight, $this->conv_enc('TEL：'.$wc2_options['general']['tel_number']), 0, 'L');
		$pdf->MultiCell(60, $lineheight, $this->conv_enc('FAX：'.$wc2_options['general']['fax_number']), 0, 'L');
	}

	//Footer
	public function pdfSetFooter( $pdf, $data ) {
		global $wc2;

		$wc2_options = wc2_get_option();
		$font = $this->font;

		$border = 0;
		list($fontsize, $lineheight, $linetop) = $this->set_font_size(9);
		$pdf->SetFont($font, '', $fontsize);

		// Body label
		$pdf->SetXY(15.5, 94.9);
		$pdf->MultiCell(87.8, $lineheight, $this->conv_enc(__('Item name','wc2')), $border, 'C');
		$pdf->SetXY(103.7, 94.9);
		$pdf->MultiCell(11.4, $lineheight, $this->conv_enc(__('Quantity','wc2')), $border, 'C');
		$pdf->SetXY(115.8, 94.9);
		$pdf->MultiCell(11.0, $lineheight, $this->conv_enc(__('Unit', 'wc2')), $border, 'C');
		$pdf->SetXY(127.2, 94.9);
		$pdf->MultiCell(15.0, $lineheight, $this->conv_enc(__('Price','wc2')), $border, 'C');
		$pdf->SetXY(142.9, 94.9);
		$pdf->MultiCell(22.4, $lineheight, $this->conv_enc(__('Amount','wc2').'('.__(wc2_crcode(), 'wc2').')'), $border, 'C');

		// Footer label
		$labeldata = array(
			'order_condition' => $data['order_condition'],
			'order_item_total_price' => $data['item_total_price'],
			'order_discount' => $data['discount'],
			'order_shipping_charge' => $data['shipping_charge'],
			'order_cod_fee' => $data['cod_fee'],
		);
		$pdf->SetXY(104.3, 198.8);
		$pdf->MultiCell(37.7, $lineheight, $this->conv_enc(__('Total amount of items', 'wc2')), $border, 'C');
		$pdf->SetXY(104.3, 204.8);
		$pdf->MultiCell(37.7, $lineheight, $this->conv_enc(apply_filters('wc2_filter_discount_label', __('Discount', 'wc2'), $data )), $border, 'C');

		if( 'products' == wc2_get_tax_target() ){
			$data_1 = apply_filters('wc2_filter_tax_label', wc2_tax_label( $labeldata ));
			$data_2 = apply_filters('wc2_filter_shipping_label', __('Shipping charges', 'wc2'));
			$data_3 = apply_filters('wc2_filter_cod_label', __('COD fee', 'wc2'));
		}else{
			$data_1 = apply_filters('wc2_filter_shipping_label', __('Shipping charges', 'wc2'));
			$data_2 = apply_filters('wc2_filter_cod_label', __('COD fee', 'wc2'));
			$data_3 = apply_filters('wc2_filter_tax_label', wc2_tax_label( $labeldata ));
		}

		$pdf->SetXY(104.3, 210.8);
		$pdf->MultiCell(37.7, $lineheight, $this->conv_enc($data_1), $border, 'C');
		$pdf->SetXY(104.3, 216.7);
		$pdf->MultiCell(37.7, $lineheight, $this->conv_enc($data_2), $border, 'C');
		$pdf->SetXY(104.3, 222.7);
		$pdf->MultiCell(37.7, $lineheight, $this->conv_enc($data_3), $border, 'C');
		if( wc2_is_membersystem_point() ){
			$pdf->SetXY(104.3, 228.6);
			$pdf->MultiCell(37.7, $lineheight, $this->conv_enc(apply_filters('wc2_filter_point_label', __('Used points', 'wc2'))), $border, 'C');
			$pdf->SetXY(104.3, 235.8);
			$pdf->MultiCell(37.77, $lineheight, $this->conv_enc(__('Total Amount', 'wc2')), $border, 'C');
		}else{
			$pdf->SetXY(104.3, 235.8);
			$pdf->MultiCell(37.77, $lineheight*2, $this->conv_enc(__('Total Amount', 'wc2')), $border, 'C');
		}
		list($fontsize, $lineheight, $linetop) = $this->set_font_size(8);
		$pdf->SetFont($font, '', $fontsize);
		// Footer value
		$payment = wc2_get_payment($data['payment_method']);
		$transfers = apply_filters( 'wc2_filter_pdf_transfer', array('BT'), $data );
		if( 'invoice' == $_REQUEST['type'] && in_array( $payment['settlement'], $transfers ) ){
			$transferee = __('Transfer','wc2') . " : \r\n";
			$transferee .= wc2_get_option( 'transferee_info' ) . "\r\n";
			$note_text = apply_filters( 'wc2_filter_mail_transferee', $transferee, $data, 'pdf', $payment );
		}else{
			$note_text = $data['note'];
		}
		$pdf->SetXY(16.1, 198.8);
		$pdf->MultiCell(86.6, $lineheight, $this->conv_enc( apply_filters('wc2_filter_pdf_note', $note_text, $data, $_REQUEST['type'])), $border, 'J');
		list($fontsize, $lineheight, $linetop) = $this->set_font_size(9);
		$pdf->SetFont($font, '', $fontsize);
		$pdf->SetXY(142.9, 198.8);
		$total_price = $data['item_total_price'] - $data['usedpoint'] + $data['discount'] + $data['shipping_charge'] + $data['cod_fee'] + $data['tax'];
		$pdf->MultiCell(22.6, $lineheight, wc2_get_currency($data['item_total_price']), $border, 'R' );

		$materials = array(
			'total_price' => $data['item_total_price'],
			'discount' => $data['discount'],
			'shipping_charge' => $data['shipping_charge'],
			'cod_fee' => $data['cod_fee'],
		);

		if( 'include' == $wc2_options['general']['tax_mode'] ){
			$tax = '('.wc2_internal_tax( $materials ).')';
		}else{
			$tax = wc2_get_currency($data['tax']);
		}
		if( 'products' == wc2_get_tax_target() ){
			$datav_1 = apply_filters('wc2_filter_tax_value', $tax, $data);
			$datav_2 = apply_filters('wc2_filter_shipping_value', wc2_get_currency($data['shipping_charge']), $data);
			$datav_3 = apply_filters('wc2_filter_cod_value', wc2_get_currency($data['cod_fee']), $data);
		}else{
			$datav_1 = apply_filters('wc2_filter_shipping_value', wc2_get_currency($data['shipping_charge']), $data);
			$datav_2 = apply_filters('wc2_filter_cod_value', wc2_get_currency($data['cod_fee']), $data);
			$datav_3 = apply_filters('wc2_filter_tax_value', $tax, $data);
		}

		$pdf->SetXY(142.9, 204.8);
		$pdf->MultiCell(22.6, $lineheight, $this->conv_enc( apply_filters( 'wc2_filter_discount_value', wc2_get_currency($data['discount']), $data ) ), $border, 'R');
		$pdf->SetXY(142.9, 210.8);
		$pdf->MultiCell(22.6, $lineheight, $this->conv_enc($datav_1), $border, 'R');
		$pdf->SetXY(142.9, 216.7);
		$pdf->MultiCell(22.6, $lineheight, $this->conv_enc($datav_2), $border, 'R');
		$pdf->SetXY(142.9, 222.7);
		$pdf->MultiCell(22.6, $lineheight, $this->conv_enc($datav_3), $border, 'R');
		if( wc2_is_membersystem_point() ){
			$pdf->SetXY(142.9, 228.6);
			$pdf->MultiCell(22.6, $lineheight, $this->conv_enc(apply_filters('wc2_filter_point_value', wc2_get_currency($data['usedpoint']), $data)), $border, 'R');
			$pdf->SetXY(142.9, 235.8);
			$pdf->MultiCell(22.67, $lineheight, $this->conv_enc( wc2_get_currency($total_price) ), $border, 'R');
		}else{
			$pdf->SetXY(142.9, 235.8);
			$pdf->MultiCell(22.67, $lineheight, $this->conv_enc( wc2_get_currency($total_price) ), $border, 'R');
		}

		do_action( 'wc2_action_order_print_footer', $pdf, $data );
	}

	//Line
	public function pdfSetLine( $pdf ) {

		$pdf->Rect(14, 197.8, 153, 45, 'F');//Footer field
		$line_top = 93.5;
		$line_left = 15.4;
		$line_right = $line_left + 150.1;
		$line_bottom = $line_top + 147.9;
		$line_footertop = 197.5;

		// Horizontal lines
		$pdf->SetLineWidth(0.5);
		$pdf->Line($line_left, $line_top, $line_right, $line_top);
		$pdf->Line($line_left, $line_top+6.5, $line_right, $line_top+6.5);
		$pdf->Line($line_left, $line_top+104.0, $line_right, $line_top+104.0);
		$pdf->SetLineWidth(0.04);
		$pdf->Line(103.5, $line_footertop+5.9, $line_right, $line_footertop+5.9);
		$pdf->Line(103.5, $line_footertop+5.9*2, $line_right, $line_footertop+5.9*2);
		$pdf->Line(103.5, $line_footertop+5.9*3, $line_right, $line_footertop+5.9*3);
		$pdf->Line(103.5, $line_footertop+5.9*4, $line_right, $line_footertop+5.9*4);
		$pdf->SetLineWidth(0.5);
		$pdf->Line(103.5, $line_footertop+5.9*5, $line_right, $line_footertop+5.9*5);
		$pdf->Line(103.5, $line_footertop+5.9*6, $line_right, $line_footertop+5.9*6);
		$pdf->Line($line_left, $line_bottom, $line_right, $line_bottom);

		// Perpendicular lines
		$pdf->SetLineWidth(0.5);
		$pdf->Line($line_left, $line_top, $line_left, $line_bottom);
		$pdf->SetLineWidth(0.04);
		$pdf->Line(103.5, $line_top, 103.5, $line_footertop);
		$pdf->SetLineWidth(0.5);
		$pdf->Line(103.5, $line_footertop, 103.5, $line_bottom);
		$pdf->SetLineWidth(0.04);
		$pdf->Line(115.5, $line_top, 115.5, $line_footertop);
		$pdf->Line(127, $line_top, 127, $line_footertop);
		$pdf->Line(142.5, $line_top, 142.5, $line_bottom);
		$pdf->SetLineWidth(0.5);
		$pdf->Line($line_right, $line_top, $line_right, $line_bottom);
	}

	public function set_font_size( $size ){
		$lineheight = $size / 2.6;
		$linetop = $lineheight / 12;
		return array($size, $lineheight, $linetop);
	}

	public function get_pdf_name( $data ){
		$system = wc2_get_option('system');
		$applyform = wc2_get_apply_addressform($system['addressform']);
		$name = '';
		switch ($applyform){
		case 'JP': 
			$name = $data['name1'] . ' ' . $data['name2'];
			break;
		case 'US':
		default:
			$name = $data['name2'] . ' ' . $data['name1'];
		}
		$name = apply_filters('wc2_filter_get_pdf_name', $name, $data);

		return $name;
	}

	//配送先の名前を取得
	public function get_pdf_shipping_name( $data ){
		$system = wc2_get_option('system');
		$applyform = wc2_get_apply_addressform($system['addressform']);
		$name = '';
		switch ($applyform){
		case 'JP': 
			$name = $data['delivery'][0]['name1'] . ' ' . $data['delivery'][0]['name2'];
			break;
		case 'US':
		default:
			$name = $data['delivery'][0]['name2'] . ' ' . $data['delivery'][0]['name1'];
		}

		return $name;
	}

	public function get_pdf_address( $pdf, $data, $y, $linetop, $leftside, $width, $lineheight ){
		$system = wc2_get_option('system');
		$applyform = wc2_get_apply_addressform($system['addressform']);
		$name = '';
		$border = '';
		$pref = ( __( '-- Select --','wc2') == $data['pref'] ) ? '' : $data['pref'];

		switch ($applyform){
		case 'JP': 
			$pdf->SetXY($leftside, $y);
			$pdf->MultiCell($width, $lineheight, $this->conv_enc(__("zip code", 'wc2') . ' ' . $data['zipcode']), $border, 'L');
			$pdf->MultiCell($width, $lineheight, $this->conv_enc($pref . $data['address1'] . $data['address2']), $border, 'L');
			break;

		case 'US':
		default:
			$pdf->SetXY($leftside, $y);
			$pdf->MultiCell($width, $lineheight, $this->conv_enc($data['address2'] . ' ' . $data['address1'] . ' ' . $pref . ' ' . $data['country']), $border, 'L');

			$y = $pdf->GetY() + $linetop;
			$pdf->SetXY($leftside, $y);
			$pdf->MultiCell($width, $lineheight, $this->conv_enc(__("zip code", 'wc2') . ' ' . $data['zipcode']), $border, 'L');
			break;
		}
	}

	//配送先が異なる場合の表示
	public function get_pdf_shipping_address( $pdf, $data, $y, $linetop, $leftside, $width, $lineheight ){
		$system = wc2_get_option('system');
		$applyform = wc2_get_apply_addressform($system['addressform']);
		$name = '';
		$border = '';
		$pref = ( __( '-- Select --','wc2') == $data['delivery'][0]['pref'] ) ? '' : $data['delivery'][0]['pref'];

		switch ($applyform){
		case 'JP': 
			$pdf->SetXY($leftside, $y);
			$pdf->MultiCell($width, $lineheight, $this->conv_enc(__("zip code", 'wc2') . ' ' . $data['delivery'][0]['zipcode']), $border, 'L');
			$pdf->MultiCell($width, $lineheight, $this->conv_enc($pref . $data['delivery'][0]['address1'] . $data['delivery'][0]['address2']), $border, 'L');
			break;

		case 'US':
		default:
			$pdf->SetXY($leftside, $y);
			$pdf->MultiCell($width, $lineheight, $this->conv_enc($data['delivery'][0]['address2'] . ' ' .  $data['delivery'][0]['address1'] . ' ' . $pref . ' ' . $data['delivery'][0]['country']), $border, 'L');

			$y = $pdf->GetY() + $linetop;
			$pdf->SetXY($leftside, $y);
			$pdf->MultiCell($width, $lineheight, $this->conv_enc(__("zip code", 'wc2') . ' ' . $data['delivery'][0]['zipcode']), $border, 'L');
			break;
		}
	}

	public function get_pdf_myaddress($pdf, $lineheight){
		$system = wc2_get_option('system');
		$general = wc2_get_option('general');
		$applyform = wc2_get_apply_addressform($system['addressform']);
		$name = '';
		switch ($applyform){
		case 'JP': 
			$address = ( empty($general['address2']) ) ? $general['address1'] : $general['address1'] . "\n" . $general['address2'];
			$pdf->MultiCell(60, $lineheight, $this->conv_enc(__('zip code', 'wc2').' '.$general['zip_code']), 0, 'L');
			$pdf->MultiCell(60, $lineheight, $this->conv_enc($address), 0, 'L');
			break;

		case 'US':
		default:
			$address = ( empty($general['address2']) ) ? $general['address1'] : $general['address2'] . "\n" . $general['address1'];
			$pdf->MultiCell(60, $lineheight, $this->conv_enc($address), 0, 'L');
			$pdf->MultiCell(60, $lineheight, $this->conv_enc(__('zip code', 'wc2').' '.$general['zip_code']), 0, 'L');
			break;
		}
	}
}

?>
