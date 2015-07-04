<?php
/**
 * Welcart2.
 *
 * @package   WC2_Phrase_Setting
 * @author    Collne Inc. <author@welcart.com>
 * @license   GPL-2.0+
 * @link      http://www.welcart2.com
 * @copyright 2014 Collne Inc.
 */

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
class WC2_Payment_Setting extends WC2_Admin_Page {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	//screen_optionに設定
	public static $per_page_slug = 'payment_setting_page';

	/***********************************
	 * Constructor
	 *
	 * @since     1.0.0
	 ***********************************/
	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_payment_ajax', array( $this, 'payment_ajax' ) );
		add_action( 'wp_ajax_setup_cod_ajax', array( $this, 'setup_cod_ajax' ) );
		add_action( 'wp_ajax_bank_ajax', array( $this, 'bank_ajax' ) );
	}

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
	public function admin_help_setting( $help, $screen_id, $screen ) {
		if( !isset( $this->plugin_screen_hook_suffix ) or $this->plugin_screen_hook_suffix != $screen->id ) return;

		$tabs = array(
			array(
				'title' => '支払方法',
				'id' => 'payment',
				'callback' => array( $this, 'get_help_payment' )
			),
			array(
				'title' => '銀行振込設定',
				'id' => 'bt',
				'callback' => array( $this, 'get_help_bt' )
			),
			array(
				'title' => '代引手数料設定',
				'id' => 'cod',
				'callback' => array( $this, 'get_help_cod' )
			)
		);

		foreach( $tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}
	}

	function get_help_payment() {
		echo "<dl>
				<dt>支払方法名【必須】</dt>
					<dd>「銀行振込」や「代金引換」など自由に名前を付けられます。</dd>
				<dt>説明</dt>
					<dd>支払方法の条件などの説明書きです。</dd>
				<dt>決済種別【必須】</dt>
					<dd>システムが解釈するための決済種別です。該当するものを選択してください。Welcart 本体の搭載しているクレジットサービスを利用する場合は、予めクレジット決済設定を行う必要があります。</dd>
				<dt>手数料</dt>
					<dd>・手数料なし</dd>
					<dd>・代引手数料を適用 --- 代引手数料設定で設定した金額が適用されます。</dd>
					<dd>・金額を適用 --- 金額入力フィールドに設定した金額が適用されます。</dd>
				<dt>使用／停止</dt>
					<dd>支払方法の利用をやめたり、再開したりできます。</dd>
			</dl>";
	}

	function get_help_bt() {
		echo "<dl>
				<dt>振込先口座情報</dt>
					<dd>支払方法で銀行振込を選択した場合の振込先。改行して複数設定可。サンキューメールなどに記載されます。</dd>
			</dl>";
	}
	function get_help_cod() {
		echo "<dl>
				<dt>代引手数料のタイプ</dt>
					<dd>支払方法で代引を選択した場合の手数料。タイプを選択して固定額か、購入金額によって額が変わる変動額かを設定できます。</dd>
			</dl>";
	}

	/***********************************
	 * Add a sub menu page.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function add_admin_menu() {
		$this->plugin_screen_hook_suffix = add_submenu_page( $this->menu_slug['setting'], '支払設定', '支払設定', 'create_users', 'wc2_payment', array( $this, 'payment_setting_page' ) );
		add_action( 'load-'.$this->plugin_screen_hook_suffix, array( $this, 'load_payment_action' ) );
	}

	/***********************************
	 * Runs when an administration menu page is loaded.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function load_payment_action() {
	
		do_action( 'wc2_action_load_payment_action' );
	
	}

	/***********************************
	 * The function to be called to output the content for this page.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function payment_setting_page() {
		if( !isset( $this->plugin_screen_hook_suffix ) )
			return;

		$screen = get_current_screen();
		if( $this->plugin_screen_hook_suffix != $screen->id )
			return;

		$settlement_types = wc2_get_option( 'settlement_types' );
		$payment_method = wc2_get_option( 'payment_method' );
		$payment_info = wc2_get_option( 'payment_info' );
		$cod_type = ( 'change' == $payment_info['cod_type'] ) ? 'change' : 'fix';
		$transferee_info = wc2_get_option( 'transferee_info' );

		$status = $this->action_status;
		$message = $this->action_message;
		require_once( WC2_PLUGIN_DIR.'/admin/views/setting-payment.php' );
	}

	public function payment_list_row( $data, $id ) {
		if( empty($data) ) return;

		$settlement_types = wc2_get_option( 'settlement_types' );
		$name = ( isset($data['name']) ) ? esc_attr( $data['name'] ) : '';
		$explanation = ( isset($data['explanation']) ) ? esc_attr( $data['explanation'] ) : '';
		$settlement = $data['settlement'];
		$sort = ( isset($data['sort']) ) ? (int)$data['sort'] : 1;
		$use = ( isset($data['use']) ) ? $data['use'] : 'activate';
		$check_activate = ( $use != 'deactivate' ) ? ' checked="checked"' : '';
		$check_deactivate = ( $use == 'deactivate' ) ? ' checked="checked"' : '';
		$payment_charge = ( isset($data['charge']) ) ? $data['charge'] : 'none';
		$display = ( $payment_charge == 'price' ) ? '' : ' style="display:none;"';
		$charge_price = ( isset($data['charge_price']) ) ? (int)$data['charge_price'] : 0;

		ob_start();
?>
			<tr id="payment-<?php echo $id; ?>">
				<th class="hdl">　</th>
				<td class="payment-name">
					<div><input type="text" id="payment-name-<?php echo $id; ?>" class="medium-text" value="<?php echo $name; ?>" /></div>
					<div>
						<label title="activate"><input type="radio" name="payment_use_<?php echo $id; ?>" id="payment-use-activate-<?php echo $id; ?>" value="activate"<?php echo $check_activate; ?> /><span><?php _e('使用','wc2'); ?></span></label>　
						<label title="deactivate"><input type="radio" name="payment_use_<?php echo $id; ?>" id="payment-use-deactivate-<?php echo $id; ?>" value="deactivate"<?php echo $check_deactivate; ?> /><span><?php _e('停止','wc2'); ?></span></label>
					</div>
					<div id="payment-submit-<?php echo $id; ?>" class="submit">
						<input type="button" id="delete-payment-<?php echo $id; ?>" class="button action" value="<?php esc_attr_e(__( 'Delete' )); ?>" />
						<input type="button" id="update-payment-<?php echo $id; ?>" class="button action" value="<?php esc_attr_e(__( 'Update' )); ?>" />
						<input type="hidden" id="payment-sort-<?php echo $id; ?>" value="<?php echo $sort; ?>" />
					</div>
					<div id="payment-loading-<?php echo $id; ?>" class="loading"></div>
				</td>
				<td class="payment-explanation"><textarea id="payment-explanation-<?php echo $id; ?>" class="regular-text"><?php echo $explanation; ?></textarea></td>
				<td class="payment-settlement">
					<select id="payment-settlement-<?php echo $id; ?>">
						<option value="<?php echo WC2_UNSELECTED; ?>"><?php _e('-- Select --','wc2'); ?></option>
					<?php foreach( $settlement_types as $key => $type ): ?>
						<?php $selected = ( $key == $settlement ) ? ' selected="selected"' : ''; ?>
						<option value="<?php esc_attr_e($key); ?>"<?php echo $selected; ?>><?php esc_html_e($type); ?></option>
					<?php endforeach; ?>
					</select>
				</td>
				<td class="payment-charge">
					<select class="payment-charge-select" id="payment-charge-<?php echo $id; ?>">
						<option value="none"<?php if( $payment_charge == 'none' ) echo ' selected="selected"'; ?>><?php _e('手数料なし','wc2'); ?></option>
						<option value="cod"<?php if( $payment_charge == 'cod' ) echo ' selected="selected"'; ?>><?php _e('代引手数料を適用','wc2'); ?></option>
						<option value="price"<?php if( $payment_charge == 'price' ) echo ' selected="selected"'; ?>><?php _e('金額を適用','wc2'); ?></option>
					</select>
					<span id="payment-charge-price-input-<?php echo $id; ?>"<?php echo $display; ?>><input type="text" id="payment-charge-price-<?php echo $id; ?>" class="small-text right" value="<?php esc_html_e($charge_price); ?>"><?php echo wc2_crcode(); ?></span>
				</td>
			</tr>
<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function payment_list_row_e( $data, $id ) {
		echo $this->payment_list_row( $data, $id );
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

		$payment_method = wc2_get_option( 'payment_method' );
		$payment_info = wc2_get_option( 'payment_info' );
		$cod_type = ( 'change' == $payment_info['cod_type'] ) ? 'change' : 'fix';
		$transferee_info = wc2_get_option( 'transferee_info' );
?>
<script type="text/javascript">
jQuery(function($) {

	var tb = $("#payment-tabs").tabs();

	if( $.fn.jquery < "1.10" ) {
		var $tabs = $("#payment-tabs").tabs({
			cookie: {
				expires: 1
			}
		});
	} else {
		$("#payment-tabs").tabs({
			active: ($.cookie("payment-tabs")) ? $.cookie("payment-tabs") : 0
			, activate: function( event, ui ){
				$.cookie("payment-tabs", $(this).tabs("option", "active"));
			}
		});
	}

	/*-------支払方法---------*/
	paymentAction = {
		Add: function() {
			var name = $("#payment-name-add").val();
			var explanation = $("#payment-explanation-add").val();
			var settlement = $("#payment-settlement-add").val();
			var charge = $("#payment-charge-add").val();
			var charge_price = $("#payment-charge-price-add").val();

			var mes = "";
			if( name == "" ) {
				mes += "<p>支払方法名の値を入力してください。</p>";
			}
			if( settlement == "<?php echo WC2_UNSELECTED; ?>" ) {
				mes += "<p>決済種別を選択してください。</p>";
			}
			if( mes != "" ) {
				$("#payment-ajax-response").html('<div class="error">'+mes+'</div>');
				return false;
			}

			$("#payment-loading-add").html('<img src="'+WC2L10n.loading_gif+'" />');

			$.ajax({
				url: ajaxurl,
				type: "POST",
				cache: false,
				data: {
					action: "payment_ajax",
					mode: "add",
					name: name,
					explanation: explanation,
					settlement: settlement,
					charge: charge,
					charge_price: charge_price,
					wc2_nonce: $("#wc2_nonce").val()
				}
			}).done( function( retVal ) {
				$("#payment-loading-add").html("");
				$("#payment-ajax-response").html("");
				var data = retVal.split("<?php echo WC2_SPLIT; ?>");
				if( $("table#payment-table").css("display") == "none" ) {
					$("table#payment-table").css("display", "table");
				}
				if( data[0] < 0 ) {
					$("#payment-ajax-response").html('<div class="error"><p>'+data[1]+'</p></div>');
				} else {
					//$("tbody#payment-list").html(data[0]);
					$("tbody#payment-list").append(data[1]);
					$(document).on( "click", "#update-payment-"+data[0], function() { paymentAction.Update( data[0] ); });
					$(document).on( "click", "#delete-payment-"+data[0], function() { paymentAction.Delete( data[0] ); });
					$("#payment-name-add").val("");
					$("#payment-explanation-add").val("");
					$("#payment-settlement-add").val("<?php echo WC2_UNSELECTED; ?>");
					$("#payment-"+data[0]).css({"background-color":"#FF4"});
					$("#payment-"+data[0]).animate({"background-color":"#FFFFEE"}, 2000);
				}
			}).fail( function( retVal ) {
				$("#payment-ajax-response").html(retVal);
				$("#payment-loading-add").html('');
			});
			return false;
		},

		Update: function( id ) {
			var name = $("#payment-name-"+id).val();
			var explanation = $("#payment-explanation-"+id).val();
			var settlement = $("#payment-settlement-"+id).val();
			var use = $("input[name='payment["+id+"][use]']:checked").val();
			var charge = $("#payment-charge-"+id).val();
			var charge_price = $("#payment-charge-price-"+id).val();

			var mes = '';
			if( name == '' ) {
				mes += "<p>支払方法名の値を入力してください。</p>";
			}
			if( settlement == "<?php echo WC2_UNSELECTED; ?>" ) {
				mes += "<p>決済種別を選択してください。</p>";
			}
			if( mes != '' ) {
				$("#payment-ajax-response").html('<div class="error">'+mes+'</div>');
				return false;
			}

			$("#payment-loading-"+id).html('<img src="'+WC2L10n.loading_gif+'" />');

			$.ajax({
				url: ajaxurl,
				type: "POST",
				cache: false,
				data: {
					action: "payment_ajax",
					mode: "update",
					id: id,
					name: name,
					explanation: explanation,
					settlement: settlement,
					charge: charge,
					charge_price: charge_price,
					use: use,
					wc2_nonce: $("#wc2_nonce").val()
				}
			}).done( function( retVal ) {
				$("#payment-loading-"+id).html("");
				$("#payment-ajax-response").html("");
				var data = retVal.split("<?php echo WC2_SPLIT; ?>");
				if( data[0] < 0 ) {
					$("#payment-ajax-response").html('<div class="error"><p>'+data[1]+'</p></div>');
				} else {
					//$("tbody#payment-list").html(data[0]);
					$("#payment-"+id).css({"background-color":"#FF4"});
					$("#payment-"+id).animate({"background-color":"#FFFFEE"}, 2000);
				}
			}).fail( function( retVal ) {
				$("#payment-ajax-response").html(retVal);
				$("#payment-loading-"+id).html('');
			});
			return false;
		},

		Delete: function( id ) {
			$("#payment-"+id).css({"background-color":"#F00"});
			$("#payment-"+id).animate({"background-color":"#FFFFEE"}, 1000);
			$.ajax({
				url: ajaxurl,
				type: "POST",
				cache: false,
				data: {
					action: "payment_ajax",
					mode: "delete",
					id: id,
					wc2_nonce: $("#wc2_nonce").val()
				}
			}).done( function( retVal ) {
				$("#payment-"+id).remove();
				if( retVal == 0 ) {
					$("table#payment-table").css("display", "none");
				}

			}).fail( function( retVal ) {
				$("#payment-ajax-response").html(retVal);
			});
			return false;
		},

		DoSort: function( data ) {
			if( !data ) return;
			var id_str = data.replace(/payment-/g, "");
			var ids = id_str.split(',');
			if( 2 > ids.length ) return;
			for( i = 0 ; i < ids.length; i++ ) {
				$("#payment-loading-"+ids[i]).html('<img src="'+WC2L10n.loading_gif+'" />');
			}
			$.ajax({
				url: ajaxurl,
				type: "POST",
				cache: false,
				data: {
					action: "payment_ajax",
					mode: "sort",
					idstr: id_str,
					wc2_nonce: $('#wc2_nonce').val()
				}
			}).done( function( retVal ) {
				for( i = 0 ; i < ids.length; i++ ) {
					$("#payment-loading-"+ids[i]).html('');
					$("#payment-"+ids[i]).css({"background-color":"#FF4"});
					$("#payment-"+ids[i]).animate({"background-color":"#FFFFEE"}, 2000);
				}
			}).fail( function( retVal ) {
				$("#payment-ajax-response").html(retVal);
			});
			return false;
		}
	};

	$("#payment-list").sortable({
		handle: "th",
		axis: "y",
		cursor: "move",
		tolerance: "pointer",
		forceHelperSize: true,
		forcePlaceholderSize: true,
		revert: 300,
		opacity: 0.6,
		cancel: ":input,button",
		update: function() {
			//var data = [];
			//$("table","#payment-list").each( function( i, v ) {
			//	data.push( $(this).attr("id") );
			//});
			var data = $("#payment-list").sortable( "toArray" ).toString();
			if( 1 < arr.length ) {
				paymentAction.DoSort( arr.toString() );
			}
		}
	});
	$("#payment-list").disableSelection();

	$("#payment-add").click(function() {
		paymentAction.Add();
	});

	$("[id^='update-payment-']").click(function() {
		var idname = $(this).attr('id');
		var ids = idname.split('-');
		var id = ids[2];
		paymentAction.Update( id );
	});

	$("[id^='delete-payment-']").click(function() {
		var idname = $(this).attr('id');
		var ids = idname.split('-');
		var id = ids[2];
		paymentAction.Delete( id );
	});

	$(".payment-charge-select").click(function() {
		var idname = $(this).attr("id");
		var ids = idname.split("-");
		var id = ids[2];
		var charge = $("#payment-charge-"+id+" option:selected").val();
		if( charge == "price" ) {
			$("#payment-charge-price-input-"+id).css("display","block");
		} else {
			$("#payment-charge-price-input-"+id).css("display","none");
		}
	});

	/*-------代引手数料---------*/
	codSend = {
		settings: {
			url: ajaxurl,
			type: "POST",
			cache: false,
			success: function(data, dataType){
				$("#aniboxStatus").attr("class","success");
				$("#info_image").attr("src", WC2L10n.success_info);
				$("#info_message").html("<?php echo esc_js(__( 'Updated!' )); ?>");
			}, 
			error: function(msg){
				$("#aniboxStatus").attr("class","error");
				$("#info_image").attr("src", WC2L10n.error_info);
				$("#info_message").html("<?php echo esc_js(__( 'Update Failed' )); ?>");
			}
		},

		send : function() {
			$("#cod-response").html('<img src="'+WC2L10n.loading_gif+'" />');
			var query = 'action=setup_cod_ajax';
			var cod_type = $("input[name='cod_type']:checked").val();

			if( "fix" == cod_type ){
				var cod_fee = $("input[name='cod_fee']").val();
				var cod_limit_amount = $("#cod_limit_amount_fix").val();
				query += '&cod_type=' + cod_type + '&cod_fee=' + cod_fee + '&cod_limit_amount=' + cod_limit_amount;
			}else{
				var cod_first_amount = $("input[name='cod_first_amount']").val();
				var cod_limit_amount = $("#cod_limit_amount_change").val();
				var cod_first_fee = $("input[name='cod_first_fee']").val();
				var cod_end_fee = $("input[name='cod_end_fee']").val();
				query += '&cod_type=' + cod_type + '&cod_first_amount=' + cod_first_amount + '&cod_limit_amount=' + cod_limit_amount + '&cod_first_fee=' + cod_first_fee + '&cod_end_fee=' + cod_end_fee;
				var amounts = $("input[name^='cod_amounts']");
				for(var i=0; i<amounts.length; i++){
					query += '&cod_amounts[]=' + $("input[name='cod_amounts\[" + i + "\]']").val() + '&cod_fees[]=' + $("input[name='cod_fees\[" + i + "\]']").val();
				}
			}

			var s = codSend.settings;
			s.data = query+"&wc2_nonce="+$("#wc2_nonce").val();
			s.success = function(retVal, dataType){
				var data = retVal.split("<?php echo WC2_SPLIT; ?>");
				if( data[0] == "success" ) {
					$("#aniboxStatus").attr("class","success");
					$("#info_image").attr("src", WC2L10n.success_info);
					$("#info_message").html("<?php echo esc_js(__( 'Updated!' )); ?>");
				} else if( data[0] == "error" ) {
					$("#aniboxStatus").attr("class","error");
					$("#info_image").attr("src", WC2L10n.error_info);
					$("#info_message").html(data[1]);
				}
				$("#cod-response").html("");
			};
			$.ajax( s );
			return false;
		}
	};

	$("#cod-update")
		.click(function() {
			codSend.send();
	});

	$("#cod_type_fix")
		.click(function() {
			$("#cod_fix_table").css("display","");
			$("#cod_change_table").css("display","none");
	});

	$("#cod_type_change")
		.click(function() {
			$("#cod_fix_table").css("display","none");
			$("#cod_change_table").css("display","");
	});

	$("input[name='cod_first_amount']")
		.change(function() {
			var trs = $("input[name^='cod_amounts']");
			var first_amount = $("input[name='cod_first_amount']");
			if( 0 == trs.length && $(first_amount).val() != '' ){
				$("#end_amount").html(parseInt($(first_amount).val())+1);
			}else if( 0 < trs.length && $(first_amount).val() != '' ){
				$("#amount_0").html(parseInt($(first_amount).val())+1);
			}
	});

	$("#cod_limit_amount_change")
		.change(function() {
		if( "change" == $("input[name='cod_type']:checked").val() ){
			var pre = parseInt($("#end_amount").html());
			var limit = parseInt($("#cod_limit_amount_change").val());
			if( pre >= limit ){
				alert("<?php echo esc_js(__('上限金額の値が不正です。', 'wc2')); ?>" + pre + ':' + limit);
			}
		}
	});

	$("input[name^='cod_amounts']")
		.change(function() {
			var trs = $("input[name^='cod_amounts']");
			var cnt = $(trs).length;
			var id = $(trs).index(this);
			if( id >= cnt-1 ){
				$(end_amount).html( parseInt($(trs).eq(id).val()) + 1 );
			}else if( id < cnt-1 ){
				$("#amount_"+(id+1)).html( parseInt($(trs).eq(id).val()) + 1 );
			}
	});

	$("#add_row")
		.click(function() {
			var trs = $("input[name^='cod_amounts']");
			$(trs).unbind("change");
			var first_amount = $("input[name='cod_first_amount']");
			var first_fee = $("input[name='cod_first_fee']");
			var end_amount = $("#end_amount");
			var enf_fee = $("input[name='cod_enf_fee']");
			//alert(parseInt(first_amount)+':'+first_fee+':'+end_amount+':'+enf_fee+':'+trs.length);
			if( 0 == trs.length){
				prep = ( $(first_amount).val() == '' ) ? "" : parseInt( $(first_amount).val() )+1;
			}else if( 0 < trs.length){
				prep = ( $(trs).eq(trs.length-1).val() == "" ) ? "" : parseInt( $(trs).eq(trs.length-1).val() )+1;
			}
			html = '<tr id="tr_'+trs.length+'"><td class="cod_f"><span id="amount_'+trs.length+'">' + prep + '</span></td><td class="cod_m">～</td><td class="cod_e"><input name="cod_amounts['+trs.length+']" type="text" class="short_str ui-widget-content ui-corner-all num" /></td><td class="cod_cod"><input name="cod_fees['+trs.length+']" type="text" class="short_str num" /></td></tr>';
			$("#cod_change_field").append(html);
			trs = $("input[name^='cod_amounts']");
			$(document).on( "change", trs, function(){
				var cnt = $(trs).length;
				var id = $(trs).index(this);
				if( id >= cnt-1 ){
					$(end_amount).html( parseInt($(trs).eq(id).val()) + 1 );
				}else if( id < cnt-1 ){
					$("#amount_"+(id+1)).html( parseInt($(trs).eq(id).val()) + 1 );
				}
				return false;
			});
	});

	$("#del_row")
		.click(function() {
			var trs = $("input[name^='cod_amounts']");
			$(trs).unbind("change");
			var first_amount = $("input[name='cod_first_amount']");
			var end_amount = $("#end_amount");
			//alert(parseInt(first_amount)+':'+first_fee+':'+end_amount+':'+enf_fee+':'+trs.length);
			var del_id = trs.length - 1;
			//alert(trs.length);
			if( 0 < trs.length){
				$("#tr_"+del_id).remove();
			}
			trs = $("input[name^='cod_amounts']");
			if( 0 == trs.length && $(first_amount).val() != '' ){
				$(end_amount).html( parseInt($(first_amount).val())+1 );
			}else if( 0 < trs.length && $(trs).eq(trs.length-1).val() != '' ){
				$(end_amount).html( parseInt($(trs).eq(trs.length-1).val()) + 1 );
			}
			$(document).on( "change", trs, function(){
				var cnt = $(trs).length;
				var id = $(trs).index(this);
				
				if( id >= cnt-1 && $(trs).eq(id).val() != '' ){
					$(end_amount).html( parseInt($(trs).eq(id).val()) + 1 );
				}else if( id < cnt-1 && $(trs).eq(id).val() != '' ){
					$("#amount_"+(id+1)).html( parseInt($(trs).eq(id).val()) + 1 );
				}
			});
	});

<?php if( "fix" == $cod_type ) : ?>
	$("#cod_fix_table").css("display","");
	$("#cod_change_table").css("display","none");
<?php else: ?>
	$("#cod_fix_table").css("display","none");
	$("#cod_change_table").css("display","");
<?php endif; ?>

	/*-------振込先口座情報---------*/
	$("#bank-update").click(function() {
		$("#bank-loading").html('<img src="'+WC2L10n.loading_gif+'" />');
		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: {
				action: "bank_ajax",
				mode: "update",
				transferee: encodeURIComponent($("#transferee").val()),
				wc2_nonce: $('#wc2_nonce').val()
			}
		}).done( function( retVal ) {
			$("#bank-loading").html("");
			if( retVal == "OK" ) {
				$("#aniboxStatus").attr("class","success");
				$("#info_image").attr("src", WC2L10n.success_info);
				$("#info_message").html("<?php echo esc_js(__( 'Updated!' )); ?>");
			}
		}).fail( function( retVal ) {
			$("#bank-loading").html("");
		});
		return false;
	});

<?php if( 0 == count($payment_method) ): ?>
	$("table#payment-table").css("display", "none");
<?php endif; ?>
<?php do_action( 'wc2_action_admin_payment_scripts' ); ?>
});
</script>
<?php
	}

	/***********************************
	 * AJAX request's action.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function payment_ajax() {
		if( !check_ajax_referer( 'wc2_setting_payment', 'wc2_nonce', false ) ) die();
		if( !isset($_POST['action']) or !isset($_POST['mode']) ) die();
		if( $_POST['action'] != 'payment_ajax' ) die();

		$res = '';
		$html = '';

		switch( $_POST['mode'] ) {
			case 'add':
				$data = array();
				$data['name'] = ( isset($_POST['name']) ) ? $_POST['name'] : '';
				$data['explanation'] = ( isset($_POST['explanation']) ) ? $_POST['explanation'] : '';
				$data['settlement'] = ( isset($_POST['settlement']) ) ? $_POST['settlement'] : WC2_UNSELECTED;
				$data['charge'] = ( isset($_POST['charge']) ) ? $_POST['charge'] : 'none';
				$data['charge_price'] = ( isset($_POST['charge_price']) ) ? (int)$_POST['charge_price'] : 0;
				$data['use'] = 'activate';
				if( $data['name'] != '' and $data['settlement'] != WC2_UNSELECTED ) {
					$id = wc2_add_admin_option( 'payment_method', $data );
					if( 0 > $id ) {
						$html = '<p>同じ支払方法名が存在します。</p>';
					} else {
						$data['id'] = $id;
						$html = $this->payment_list_row( $data, $id );
					}
					$res = $id.WC2_SPLIT.$html;
				}
				break;

			case 'update':
				$data = array();
				$id = ( isset($_POST['id']) ) ? (int)$_POST['id'] : '';
				$data['name'] = ( isset($_POST['name']) ) ? $_POST['name'] : '';
				$data['explanation'] = ( isset($_POST['explanation']) ) ? $_POST['explanation'] : '';
				$data['settlement'] = ( isset($_POST['settlement']) ) ? $_POST['settlement'] : WC2_UNSELECTED;
				$data['charge'] = ( isset($_POST['charge']) ) ? $_POST['charge'] : 'none';
				$data['charge_price'] = ( isset($_POST['charge_price']) ) ? (int)$_POST['charge_price'] : 0;
				$data['use'] = ( isset($_POST['use']) ) ? $_POST['use'] : 'activate';
				if( $data['name'] != '' and $data['settlement'] != WC2_UNSELECTED ) {
					$id = wc2_update_admin_option( 'payment_method', $id, $data );
					if( 0 > $id ) {
						$html = '<p>同じ支払方法名が存在します。</p>';
					}
					$res = $id.WC2_SPLIT.$html;
				}
				break;

			case 'delete':
				$id = ( isset($_POST['id']) ) ? (int)$_POST['id'] : '';
				wc2_delete_admin_option( 'payment_method', $id );
				$payments = wc2_get_payment_option();
				$res = count($payments).'';
				break;

			case 'sort':
				$idstr = ( isset($_POST['idstr']) ) ? $_POST['idstr'] : '';
				$res = wc2_sort_admin_option( 'payment_method', $idstr );
				break;
		}
		$res = apply_filters( 'wc2_filter_admin_payment_ajax', $res );
		die( $res );
	}

	function setup_cod_ajax(){
		if( !check_ajax_referer( 'wc2_setting_payment', 'wc2_nonce', false ) ) die();
		$payment_info = wc2_get_option('payment_info');
		$message = '';
		$_POST = WC2_Utils::stripslashes_deep_post($_POST);

		$payment_info['cod_type'] = isset($_POST['cod_type']) ? $_POST['cod_type'] : 'fix';
		if( isset($_POST['cod_fee']) )
			$payment_info['cod_fee'] = (int)$_POST['cod_fee'];

		if( 'fix' == $payment_info['cod_type'] ){
			if( isset($_POST['cod_fee']) ){
				$payment_info['cod_fee'] = (int)$_POST['cod_fee'];
				if( !is_numeric($_POST['cod_fee']) )
					$message = __('値が不正な項目があります', 'wc2');
			}
			if( isset($_POST['cod_limit_amount']) ){
				$payment_info['cod_limit_amount'] = (int)$_POST['cod_limit_amount'];
				if( !WC2_Utils::is_blank($_POST['cod_limit_amount']) && 0 === (int)$_POST['cod_limit_amount'] )
					$message = __('値が不正な項目があります', 'wc2');
			}
		}elseif( 'change' == $payment_info['cod_type'] ){
			if( isset($_POST['cod_first_amount']) ){
				$payment_info['cod_first_amount'] = (int)$_POST['cod_first_amount'];
				if( 0 === (int)$_POST['cod_first_amount'] )
					$message = __('値が不正な項目があります', 'wc2');
			}
			if( isset($_POST['cod_limit_amount']) ){
				$payment_info['cod_limit_amount'] = (int)$_POST['cod_limit_amount'];
				if( !WC2_Utils::is_blank($_POST['cod_limit_amount']) && 0 === (int)$_POST['cod_limit_amount'] )
					$message = __('値が不正な項目があります', 'wc2');
			}
			if( isset($_POST['cod_first_fee']) ){
				$payment_info['cod_first_fee'] = (int)$_POST['cod_first_fee'];
				if( 0 === (int)$_POST['cod_first_fee'] && '0' !== $_POST['cod_first_fee'])
					$message = __('値が不正な項目があります', 'wc2');
			}
			if( isset($_POST['cod_end_fee']) ){
				$payment_info['cod_end_fee'] = (int)$_POST['cod_end_fee'];
				if( 0 === (int)$_POST['cod_end_fee'] && '0' !== $_POST['cod_end_fee'] )
					$message = __('値が不正な項目があります', 'wc2');
			}
			
			unset($payment_info['cod_amounts'], $payment_info['cod_fees']);
			if( isset($_POST['cod_amounts']) ){
				for($i=0; $i<count((array)$_POST['cod_amounts']); $i++){
					$payment_info['cod_amounts'][$i] = (int)$_POST['cod_amounts'][$i];
					$payment_info['cod_fees'][$i] = (int)$_POST['cod_fees'][$i];
					if( 0 === (int)$_POST['cod_amounts'][$i] || (0 === (int)$_POST['cod_fees'][$i] && '0' !== $_POST['cod_fees'][$i]) )
						$message = __('値が不正な項目があります', 'wc2');
				}
			}
		}

		if( '' == $message ){
			$r = 'success';
			wc2_update_option('payment_info', $payment_info);
		}else{
			$r = 'error'.WC2_SPLIT.$message;
		}
		$r = apply_filters( 'wc2_filter_admin_payment_setup_cod_ajax', $r );
		die( $r );
	}

	public function bank_ajax() {
		if( !check_ajax_referer( 'wc2_setting_payment', 'wc2_nonce', false ) ) die();
		if( !isset($_POST['action']) or !isset($_POST['mode']) ) die();
		if( $_POST['action'] != 'bank_ajax' ) die();

		$res = '';
		switch( $_POST['mode'] ) {
		case 'update':
			$_POST = wc2_stripslashes_deep_post( $_POST );
			$transferee = urldecode(trim($_POST['transferee']));
			wc2_update_option( 'transferee_info', $transferee );
			$res = 'OK';
			break;
		}
		$res = apply_filters( 'wc2_filter_admin_bank_ajax', $res );
		die( $res );
	}
}
