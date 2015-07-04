<?php
/**
 * Welcart2.
 *
 * @package   WC2_Delivery_Setting
 * @author    Collne Inc. <author@welcart.com>
 * @license   GPL-2.0+
 * @link      http://www.welcart2.com
 * @copyright 2014 Collne Inc.
 */
class WC2_Delivery_Setting extends WC2_Admin_Page {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	//screen_optionに設定
	public static $per_page_slug = 'delivery_setting_page';

	/***********************************
	 * Constructor
	 *
	 * @since     1.0.0
	 ***********************************/
	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_delivery_ajax', array( $this, 'delivery_ajax' ) );
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
	function admin_help_setting( $help, $screen_id, $screen ) {
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			$tabs = array(
				array(
					'title' => '配送設定',
					'id' => 'delivery_setting',
					'callback' => array( $this, 'get_help_delivery_setting' )
				),
				array(
					'title' => '配送方法',
					'id' => 'delivery_method',
					'callback' => array( $this, 'get_help_delivery_method' )
				),
				array(
					'title' => '送料',
					'id' => 'shipping_charge',
					'callback' => array( $this, 'get_help_shipping_charge' )
				),
				array(
					'title' => '配達日数',
					'id' => 'delivery_days',
					'callback' => array( $this, 'get_help_delivery_days' )
				)
			);

			foreach( $tabs as $tab ) {
				$screen->add_help_tab( $tab );
			}
		}
	}

	function get_help_delivery_setting() {
		echo "<dl>
				<dt>配送業務締時間</dt>
					<dd>商品発送を締め切る時間を指定します。この値は最短発送日を算出するために使用します。</dd>
				<dt>午前着の可否</dt>
					<dd>商品が最短発送日の午前中に到着が可能かどうかを指定します。</dd>
				<dt>配送希望日表示数</dt>
					<dd>お客様が選択する配送希望日の選択数を指定します。</dd>
				<dt>配送制限</dt>
					<dd>・利用しない</dd>
					<dd>・商品数…1梱包に収める商品数。配送方法ごとに設定します。</dd>
					<dd>・重量…1梱包に収める商品の重量。商品のSKUごとに設定します。</dd>
			</dl>";
	}

	function get_help_delivery_method() {
		echo "<dl>
				<dt>配送対象地域</dt>
					<dd>この配送方法が対象とする配送可能地域を選択します。</dd>
				<dt>指定時間帯</dt>
					<dd>配送可能な時間帯を入力します。お客様に時間帯を選択させない場合は空白にします。<br />
						例）<br />
						午前中<br />
						12：00～14：00<br />
						14：00～16：00<br />
						16：00～18：00<br />
					</dd>
				<dt>送料固定</dt>
					<dd>送料固定を選択すると上記料金設定に固定されます。「固定しない」の場合は商品に設定された送料が適用されます。</dd>
				<dt>配達日数</dt>
					<dd>下の配達日数設定で登録した配達日数名を選択します。最短配送日を算出するために使用します。お客様に配送希望日を選択させない場合は「配送希望日を利用しない」を指定します。</dd>
				<dt>代引き不可</dt>
					<dd>代引き支払いができない場合はチェックを入れます。</dd>
			</dl>";
	}

	function get_help_shipping_charge() {
		echo "<dl>
				<dt>送料名</dt>
					<dd>送料名。</dd>
				<dt>国</dt>
					<dd>この送料を適用する国を選択します。</dd>
				<dt>送料</dt>
					<dd>都道府県ごとに送料を設定します。</dd>
			</dl>";
	}

	function get_help_delivery_days() {
		echo "<dl>
				<dt>配達日数名</dt>
					<dd>配達日数名。</dd>
				<dt>国</dt>
					<dd>この配達日数を適用する国を選択します。</dd>
				<dt>配達日数</dt>
					<dd>都道府県ごとに配達日数を設定します。</dd>
			</dl>";
	}

	/***********************************
	 * Add a sub menu page.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function add_admin_menu() {
		$this->plugin_screen_hook_suffix = add_submenu_page( $this->menu_slug['setting'], '配送設定', '配送設定', 'create_users', 'wc2_delivery', array( $this, 'admin_delivery_page' ) );
		add_action( 'load-' . $this->plugin_screen_hook_suffix, array( $this, 'load_delivery_action' ) );
	}

	/***********************************
	 * Runs when an administration menu page is loaded.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function load_delivery_action() {

	}

	/***********************************
	 * The function to be called to output the content for this page.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function admin_delivery_page() {
		if( !isset( $this->plugin_screen_hook_suffix ) )
			return;

		$screen = get_current_screen();
		if( $this->plugin_screen_hook_suffix != $screen->id )
			return;

		$delivery = wc2_get_option( 'delivery' );
		if( isset($_POST['wc2_option_update']) ) {
			check_admin_referer( 'wc2_setting_delivery', 'wc2_nonce' );

			$_POST = WC2_Utils::stripslashes_deep_post($_POST);
			if( isset($_POST['delivery_time_limit']) ) $delivery['delivery_time_limit'] = $_POST['delivery_time_limit'];
			if( isset($_POST['shortest_delivery_time']) ) $delivery['shortest_delivery_time'] = $_POST['shortest_delivery_time'];
			if( isset($_POST['delivery_after_days']) ) $delivery['delivery_after_days'] =  $_POST['delivery_after_days'];
			if( isset($_POST['delivery_limit_option']) ) $delivery['delivery_limit_option'] =  $_POST['delivery_limit_option'];

			wc2_update_option( 'delivery', $delivery );

			$this->action_status = 'success';
			$this->action_message = __( 'Updated!' );

		} else {
			$this->action_status = 'none';
			$this->action_message = '';
		}

		$delivery_time_limit['hour'] = ( isset($delivery['delivery_time_limit']['hour']) ) ? $delivery['delivery_time_limit']['hour'] : '00';
		$delivery_time_limit['min'] = ( isset($delivery['delivery_time_limit']['min']) ) ? $delivery['delivery_time_limit']['min'] : '00';
		$shortest_delivery_time = ( isset($delivery['shortest_delivery_time']) ) ? $delivery['shortest_delivery_time'] : '0';
		$delivery_after_days = ( empty($delivery['delivery_after_days']) ) ? 15 : (int)$delivery['delivery_after_days'];
		$delivery_limit_option = isset( $delivery['delivery_limit_option'] ) ? $delivery['delivery_limit_option']: 'none';

		$status = $this->action_status;
		$message = $this->action_message;

		require_once( WC2_PLUGIN_DIR . '/admin/views/setting-delivery.php' );
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

		$delivery = wc2_get_option( 'delivery' );
		$system_options = wc2_get_option( 'system' );
		$base_country = $system_options['base_country'];
		$delivery_method = ( isset($delivery['delivery_method']) ) ? $delivery['delivery_method'] : array();
		//$shipping_charge = ( isset($delivery['shipping_charge']) ) ? $delivery['shipping_charge'] : array();
		$shipping_charge = wc2_get_option( 'shipping_charge' );
		//$delivery_days = ( isset($delivery['delivery_days']) ) ? $delivery['delivery_days'] : array();
		$delivery_days = wc2_get_option( 'delivery_days' );
		$target_market = ( isset($system_options['target_market']) && !empty($system_options['target_market']) ) ? $system_options['target_market'] : WC2_Funcs::get_local_target_market();
		$prefs = array();
		foreach( (array)$target_market as $tm ) {
			$prefs[$tm] = $system_options['province'][$tm];
		}
?>
<script type="text/javascript">
jQuery(function($){
	//tab
	$("#delivery-tabs").tabs();

	if( $.fn.jquery < "1.10" ) {
		var $tabs = $("#delivery-tabs").tabs({
			cookie: {
				expires: 1
			}
		});
	} else {
		$( "#delivery-tabs" ).tabs({
			active: ($.cookie("delivery-tabs")) ? $.cookie("delivery-tabs") : 0
			, activate: function( event, ui ){
				$.cookie("delivery-tabs", $(this).tabs("option", "active"));
			}
		});
	}

	$(document).on( "change", ".charge_text", function(){ check_money($(this)); });
	$(document).on( "change", ".days_text", function(){ check_num($(this)); });
<?php
	if(!array_key_exists($base_country, (array)$target_market)) $base_country = $target_market[0];
	$i = 0;
?>
	var target_market = [];
<?php foreach( (array)$target_market as $tm ) : ?>
	target_market[<?php echo $i; ?>] = "<?php echo $tm; ?>";
<?php $i++; ?>
<?php endforeach; ?>
	var base_country = "<?php echo $base_country; ?>";
	var delivery_method = [];
<?php for($i=0; $i<count((array)$delivery_method); $i++){ $lines = explode("\n", $delivery_method[$i]['time']); ?>
	delivery_method[<?php echo $i; ?>] = [];
	delivery_method[<?php echo $i; ?>]["id"] = <?php echo (int)$delivery_method[$i]['id']; ?>;
	delivery_method[<?php echo $i; ?>]["name"] = "<?php echo $delivery_method[$i]['name']; ?>";
	delivery_method[<?php echo $i; ?>]["charge"] = <?php echo (int)$delivery_method[$i]['charge']; ?>;
	delivery_method[<?php echo $i; ?>]["days"] = <?php echo (int)$delivery_method[$i]['days']; ?>;
	sttr = '';
	<?php foreach((array)$lines as $line){
		if(trim($line) != ""){ ?>
	sttr += "<?php echo trim($line); ?>\n";
	<?php } } ?>
	delivery_method[<?php echo $i; ?>]["time"] = sttr;
	delivery_method[<?php echo $i; ?>]["nocod"] = "<?php echo $delivery_method[$i]['nocod']; ?>";
	delivery_method[<?php echo $i; ?>]["intl"] = "<?php echo (!empty($delivery_method[$i]['intl'])) ? $delivery_method[$i]['intl'] : '0'; ?>";
	delivery_method[<?php echo $i; ?>]["limit_num"] = "<?php echo ( isset( $delivery_method[$i]['limit_num'] ) ) ? $delivery_method[$i]['limit_num']: ''; ?>";
<?php } ?>
	var pref = [];
<?php foreach((array)$target_market as $tm){ ?>
	pref["<?php echo $tm; ?>"] = [];
<?php foreach((array)$prefs[$tm] as $pref){ ?>
	pref["<?php echo $tm; ?>"].push("<?php echo $pref; ?>");
<?php }} ?>
	var shipping_charge = [];
<?php for($i=0; $i<count((array)$shipping_charge); $i++){ ?>
	shipping_charge[<?php echo $i; ?>] = [];
	shipping_charge[<?php echo $i; ?>]["id"] = <?php echo (int)$shipping_charge[$i]['id']; ?>;
	shipping_charge[<?php echo $i; ?>]["name"] = "<?php echo $shipping_charge[$i]['name']; ?>";
<?php foreach((array)$target_market as $tm) { ?>
	shipping_charge[<?php echo $i; ?>]["<?php echo $tm; ?>"] = [];
	<?php foreach( (array)$prefs[$tm] as $pref ) { ?>
		<?php if( isset($shipping_charge[$i][$tm][$pref]) ) : ?>
	shipping_charge[<?php echo $i; ?>]["<?php echo $tm; ?>"]["<?php echo $pref; ?>"] = "<?php echo (float)$shipping_charge[$i][$tm][$pref]; ?>";
		<?php else : ?>
	shipping_charge[<?php echo $i; ?>]["<?php echo $tm; ?>"]["<?php echo $pref; ?>"] = "0";
		<?php endif; ?>
<?php }}} ?>
	var delivery_days = [];
<?php for($i=0; $i<count((array)$delivery_days); $i++){ ?>
	delivery_days[<?php echo $i; ?>] = [];
	delivery_days[<?php echo $i; ?>]["id"] = <?php echo (int)$delivery_days[$i]['id']; ?>;
	delivery_days[<?php echo $i; ?>]["name"] = "<?php echo $delivery_days[$i]['name']; ?>";
<?php foreach((array)$target_market as $tm) { ?>
	delivery_days[<?php echo $i; ?>]["<?php echo $tm; ?>"] = [];
	<?php foreach( (array)$prefs[$tm] as $pref ) { ?>
		<?php if( isset($delivery_days[$i][$tm][$pref]) ) : ?>
	delivery_days[<?php echo $i; ?>]["<?php echo $tm; ?>"]["<?php echo $pref; ?>"] = "<?php echo (int)$delivery_days[$i][$tm][$pref]; ?>";
		<?php else : ?>
	delivery_days[<?php echo $i; ?>]["<?php echo $tm; ?>"]["<?php echo $pref; ?>"] = "0";
		<?php endif; ?>
<?php }}} ?>
	var selected_method = 0;
	function get_delivery_method_charge(selected){
		var index = 0;
		for(var i=0; i<delivery_method.length; i++){
			if(selected === delivery_method[i]["id"]){
				index = i;
			}
		}
		if(undefined === delivery_method[index]){
			return -1;
		}else{
			return delivery_method[index]["charge"];
		}
	}

	operation = {
		/*-------配送方法---------*/
		disp_delivery_method :function (id){
			selected_method = id;
			var index = false;
			for(var i=0; i<delivery_method.length; i++){
				if(id === delivery_method[i]["id"]){
					index = i;
				}
			}
			if(false === index){
				selected = 0;
			}else{
				selected = index;
			}
			if(delivery_method.length === 0) {
				$("#delivery_method_name").html('<input name="delivery_method_name" type="text" class="medium-text" value="" />');
				$("#delivery_method_name2").html("");
				$("#delivery_method_time").val("");
				$("#delivery_method_button").html('<div class="submit"><input name="add_delivery_method" id="add_delivery_method" type="button" class="button" value="<?php _e('追加', 'wc2'); ?>" onclick="operation.add_delivery_method();" /></div>');
				$("#delivery_method_nocod").html('<input name="delivery_method_nocod" type="checkbox" value="1" />');
				$("#delivery_method_intl").html('<label title="0"><input name="delivery_method_intl" id="delivery_method_intl_0" type="radio" value="0" checked="checked" /><span><?php _e('国内便', 'wc2'); ?></span></label><label title="1"><input name="delivery_method_intl" id="delivery_method_intl_1" type="radio" value="1" /><span><?php _e('国際便', 'wc2'); ?></span></label>');
				$("#delivery_method_item_limit_num").html('<input name="delivery_method_item_limit_num" type="text" class="medium-text right" value="" />');
				operation.make_delivery_method_charge(-1);
				operation.make_delivery_method_days(-1);
			}else{
				var name_select = '<select name="delivery_method_name_select" id="delivery_method_name_select" onchange="operation.onchange_delivery_select(this.selectedIndex);">';
				for(var i=0; i<delivery_method.length; i++){
					if(selected === i){
						name_select += '<option value="'+delivery_method[i]["id"]+'" selected="selected">'+delivery_method[i]["id"]+' : '+delivery_method[i]["name"]+'</option>';
					}else{
						name_select += '<option value="'+delivery_method[i]["id"]+'">'+delivery_method[i]["id"]+' : '+delivery_method[i]["name"]+'</option>';
					}
				}
				name_select += '</select>';
				$("#delivery_method_name").html(name_select);
				$("#delivery_method_name2").html('<input name="delivery_method_name" type="text" class="medium-text" value="'+delivery_method[selected]["name"]+'" />');
				$("#delivery_method_time").val(delivery_method[selected]["time"]);
				$("#delivery_method_button").html('<div class="submit"><input name="delete_delivery_method" id="delete_delivery_method" type="button" class="button" value="<?php _e('削除', 'wc2'); ?>" onclick="operation.delete_delivery_method();" />'+"\n"+'<input name="update_delivery_method" id="update_delivery_method" type="button" class="button" value="<?php _e('更新', 'wc2'); ?>" onclick="operation.update_delivery_method();" /></div>');
				var checked_nocod = (delivery_method[selected]["nocod"] == "1") ? ' checked="checked"' : "";
				$("#delivery_method_nocod").html('<input name="delivery_method_nocod" type="checkbox" value="1"'+checked_nocod+' />');
				var checked_intl_0 = (delivery_method[selected]["intl"] == "0") ? ' checked="checked"' : "";
				var checked_intl_1 = (delivery_method[selected]["intl"] == "1") ? ' checked="checked"' : "";
				$("#delivery_method_intl").html('<label title="0"><input name="delivery_method_intl" id="delivery_method_intl_0" type="radio" value="0"'+checked_intl_0+' /><span><?php _e('国内便', 'wc2'); ?></span></label><label title="1"><input name="delivery_method_intl" id="delivery_method_intl_1" type="radio" value="1"'+checked_intl_1+' /><span><?php _e('国際便', 'wc2'); ?></span></label>');
				$("#delivery_method_item_limit_num").html('<input name="delivery_method_item_limit_num" type="text" class="medium-text right" value="'+ delivery_method[selected]['limit_num'] +'" />');
				operation.make_delivery_method_charge(get_delivery_method_charge(selected_method));
				operation.make_delivery_method_days(get_delivery_method_days(selected_method));
			}
		},

		add_delivery_method : function() {
			if($('input[name="delivery_method_name"]').val() == "") return;
			$("#delivery_method_loading").html('<img src="'+WC2L10n.loading_gif+'" />');
			//var name = encodeURIComponent($("input[name='delivery_method_name']").val());
			//var time = encodeURIComponent($("#delivery_method_time").val());
			var name = $('input[name="delivery_method_name"]').val();
			var time = $("#delivery_method_time").val();
			var charge = $("#delivery_method_charge option:selected").val();
			var days = $("#delivery_method_days option:selected").val();
			var nocod = ($(':input[name="delivery_method_nocod"]').attr("checked")) ? "1" : "0";
			var intl = $(':radio[name="delivery_method_intl"]:checked').val();
			var limit_num = $('input[name="delivery_method_item_limit_num"]').val();
			$.ajax({
				url: ajaxurl,
				type: "POST",
				dataType: "JSON",
				scriptCharset: "UTF-8",
				processData: true,
				cache: false,
				data: {
					action: "delivery_ajax",
					mode: "add_delivery_method",
					name: name,
					time: time,
					charge: charge,
					days: days,
					nocod: nocod,
					intl: intl,
					limit_num: limit_num,
					wc2_nonce: $("#wc2_nonce").val()
				}
			}).done( function( ret, dataType ) {
				var mes = ret.data.message;
				if( ret.success ){
					var val = ret.data.value;
					var id = val.id - 0;
					var name = val.name;
					var time = val.time;
					var charge = val.charge - 0;
					var days = val.days - 0;
					var nocod = val.nocod;
					var intl = val.intl;
					var limit_num = val.limit_num;
					var index = delivery_method.length;
					delivery_method[index] = [];
					delivery_method[index]["id"] = id;
					delivery_method[index]["name"] = name;
					delivery_method[index]["time"] = time;
					delivery_method[index]["charge"] = charge;
					delivery_method[index]["days"] = days;
					delivery_method[index]["nocod"] = nocod;
					delivery_method[index]["intl"] = intl;
					delivery_method[index]["limit_num"] = limit_num;

					//success_message
					$("#aniboxStatus").attr("class","success");
					$("#info_image").attr("src", WC2L10n.success_info);
					$("#info_message").html(mes);

					operation.disp_delivery_method(id);
				}else{
					//error_message
					$("#aniboxStatus").attr("class","error");
					$("#info_image").attr("src", WC2L10n.error_info);
					$("#info_message").html(mes);
				}
				$("#delivery_method_loading").html("");
			}).fail( function( retVal ) {
				$("#delivery_method_loading").html("");
			});
			return false;
		},

		update_delivery_method : function() {
			if($('input[name="delivery_method_name"]').val() == "") return;

			$("#delivery_method_loading").html('<img src="'+WC2L10n.loading_gif+'" />');
			var id = $("#delivery_method_name_select option:selected").val();
			//var name = encodeURIComponent($("input[name='delivery_method_name']").val());
			//var time = encodeURIComponent($("#delivery_method_time").val());
			var name = $('input[name="delivery_method_name"]').val();
			var time = $("#delivery_method_time").val();
			var charge = $("#delivery_method_charge option:selected").val();
			var days = $("#delivery_method_days option:selected").val();
			var nocod = ($(':input[name="delivery_method_nocod"]').attr("checked")) ? "1" : "0";
			var intl = $(':radio[name="delivery_method_intl"]:checked').val();
			var limit_num = $('input[name="delivery_method_item_limit_num"]').val();

			$.ajax({
				url: ajaxurl,
				type: "POST",
				dataType: "JSON",
				scriptCharset: "UTF-8",
				processData: true,
				cache: false,
				data: {
					action: "delivery_ajax",
					mode: "update_delivery_method",
					id: id,
					name: name,
					time: time,
					charge: charge,
					days: days,
					nocod: nocod,
					intl: intl,
					limit_num: limit_num,
					wc2_nonce: $("#wc2_nonce").val()
				}
			}).done( function( ret, dataType ) {
				var mes = ret.data.message;
				if( ret.success ){
					var val = ret.data.value;
					var id = val.id - 0;
					var name = val.name;
					var time = val.time;
					var charge = val.charge - 0;
					var days = val.days - 0;
					var nocod = val.nocod;
					var intl = val.intl;
					var limit_num = val.limit_num;
					for(var i=0; i<delivery_method.length; i++){
						if(id === delivery_method[i]["id"]){
							index = i;
						}
					}
					delivery_method[index]["name"] = name;
					delivery_method[index]["time"] = time;
					delivery_method[index]["charge"] = charge;
					delivery_method[index]["days"] = days;
					delivery_method[index]["nocod"] = nocod;
					delivery_method[index]["intl"] = intl;
					delivery_method[index]["limit_num"] = limit_num;
					//success_message
					$("#aniboxStatus").attr("class","success");
					$("#info_image").attr("src", WC2L10n.success_info);
					$("#info_message").html(mes);

					operation.disp_delivery_method(id);
				}else{
					//error_message
					$("#aniboxStatus").attr("class","error");
					$("#info_image").attr("src", WC2L10n.error_info);
					$("#info_message").html(mes);
				}
				$("#delivery_method_loading").html("");
			}).fail( function( retVal ) {
				$("#delivery_method_loading").html("");
			});
			return false;
		},

		delete_delivery_method : function() {
			var delname = $("#delivery_method_name_select option:selected").html();
			if(!confirm(<?php _e("'配送方法「'+delname+'」を削除します。よろしいですか？'", 'wc2'); ?>)) return false;
			$("#delivery_method_loading").html('<img src="'+WC2L10n.loading_gif+'" />');
			var id = $("#delivery_method_name_select option:selected").val();
			$.ajax({
				url: ajaxurl,
				type: "POST",
				dataType: "JSON",
				scriptCharset: "UTF-8",
				processData: true,
				cache: false,
				data: {
					action: "delivery_ajax",
					mode: "delete_delivery_method",
					id: id,
					wc2_nonce: $("#wc2_nonce").val()
				}
			}).done( function( ret, dataType ) {
				var mes = ret.data.message;
				if( ret.success ) {
					var val = ret.data.value;
					var id = val.id - 0;
					for(var i=0; i<delivery_method.length; i++){
						if(id === delivery_method[i]["id"]){
							index = i;
						}
					}
					delivery_method.splice(index, 1);
					//success_message
					$("#aniboxStatus").attr("class","success");
					$("#info_image").attr("src", WC2L10n.success_info);
					$("#info_message").html(mes);
					$("#anibox").animate({"background-color":"#ECFFFF"},2000);

					operation.disp_delivery_method(0);
				}else{
					//error_message
					$("#aniboxStatus").attr("class","error");
					$("#info_image").attr("src", WC2L10n.error_info);
					$("#info_message").html(mes);
					$("#anibox").animate({"background-color":"#FFE6E6"},2000);
				}
				$("#delivery_method_loading").html("");
			}).fail( function( retVal ) {
				$("#delivery_method_loading").html("");
			});
			return false;
		},

		onchange_delivery_select : function(index) {
			var id = $("#delivery_method_name_select option:eq("+index+")").val()-0;
			operation.disp_delivery_method(id);
		},

		make_delivery_method_charge : function(selected) {
			var option = '<select name="delivery_method_charge" id="delivery_method_charge">';
			if(selected === -1){
				option += '<option value="-1" selected="selected"><?php _e('送料を固定しない', 'wc2'); ?></option>';
			}else{
				option += '<option value="-1"><?php _e('送料を固定しない', 'wc2'); ?></option>';
			}
			for(var i=0; i<shipping_charge.length; i++){
				if(selected === shipping_charge[i]["id"]){
					option += '<option value="'+shipping_charge[i]["id"]+'" selected="selected">'+shipping_charge[i]["name"]+'</option>';
				}else{
					option += '<option value="'+shipping_charge[i]["id"]+'">'+shipping_charge[i]["name"]+'</option>';
				}
			}
			option += '</select>';
			$("#delivery_method_charge_td").html(option);
		},

		make_delivery_method_days : function(selected) {
			var option = '<select name="delivery_method_days" id="delivery_method_days">';
			if(selected === -1){
				option += '<option value="-1" selected="selected"><?php _e('配送希望日を利用しない', 'wc2'); ?></option>';
			}else{
				option += '<option value="-1"><?php _e('配送希望日を利用しない', 'wc2'); ?></option>';
			}
			for(var i=0; i<delivery_days.length; i++){
				if(selected === delivery_days[i]["id"]){
					option += '<option value="'+delivery_days[i]["id"]+'" selected="selected">'+delivery_days[i]["name"]+'</option>';
				}else{
					option += '<option value="'+delivery_days[i]["id"]+'">'+delivery_days[i]["name"]+'</option>';
				}
			}
			option += '</select>';
			$("#delivery_method_days_td").html(option);
		},

		moveup_delivery_method : function(selected) {
			var index = 0;
			for(var i=0; i<delivery_method.length; i++){
				if(selected === delivery_method[i]["id"]){
					index = i;
				}
			}
			if( 0 === index ) return;

			$("#delivery_method_loading").html('<img src="'+WC2L10n.loading_gif+'" />');

			$.ajax({
				url: ajaxurl,
				type: "POST",
				dataType: "JSON",
				scriptCharset: "UTF-8",
				processData: true,
				cache: false,
				data: {
					action: "delivery_ajax",
					mode: "moveup_delivery_method",
					id: selected,
					wc2_nonce: $("#wc2_nonce").val()
				}
			}).done( function( ret, dataType ) {
				var mes = ret.data.message;
				if( ret.success ){
					var val = ret.data.value;
					var selected = ret.data.selected;
					var ct = delivery_method.length;
					for(var i=0; i<ct; i++){
						delivery_method[i]["id"] = val[i].id - 0;
						delivery_method[i]["name"] = val[i].name;
						delivery_method[i]["time"] = val[i].time;
						delivery_method[i]["charge"] = val[i].charge - 0;
						delivery_method[i]["days"] = val[i].days - 0;
						delivery_method[i]["nocod"] = val[i].nocod;
						delivery_method[i]["intl"] = val[i].intl;
						delivery_method[i]["limit_num"] = val[i].limit_num;
					}
					//success_message
					$("#aniboxStatus").attr("class","success");
					$("#info_image").attr("src", WC2L10n.success_info);
					$("#info_message").html(mes);

					operation.disp_delivery_method(selected);
				}else{
					//error_message
					$("#aniboxStatus").attr("class","error");
					$("#info_image").attr("src", WC2L10n.error_info);
					$("#info_message").html(mes);
				}
				$("#delivery_method_loading").html("");
			}).fail( function( retVal ) {
				$("#delivery_method_loading").html("");
			});
			return false;
		},

		movedown_delivery_method : function(selected) {
			var index = 0;
			var ct = delivery_method.length;
			for(var i=0; i<ct; i++){
				if(selected === delivery_method[i]["id"]){
					index = i;
				}
			}
			if( index >= ct-1 ) return;

			$("#delivery_method_loading").html('<img src="'+WC2L10n.loading_gif+'" />');

			$.ajax({
				url: ajaxurl,
				type: "POST",
				dataType: "JSON",
				scriptCharset: "UTF-8",
				processData: true,
				cache: false,
				data: {
					action: "delivery_ajax",
					mode: "movedown_delivery_method",
					id: selected,
					wc2_nonce: $("#wc2_nonce").val()
				}
			}).done( function( ret, dataType ) {
				var mes = ret.data.message;
				if( ret.success){
					var val = ret.data.value;
					var selected = ret.data.selected;
					var ct = delivery_method.length;
					for(var i=0; i<ct; i++){
						delivery_method[i]["id"] = val[i].id - 0;
						delivery_method[i]["name"] = val[i].name;
						delivery_method[i]["time"] = val[i].time;
						delivery_method[i]["charge"] = val[i].charge - 0;
						delivery_method[i]["days"] = val[i].days - 0;
						delivery_method[i]["nocod"] = val[i].nocod;
						delivery_method[i]["intl"] = val[i].intl;
						delivery_method[i]["limit_num"] = val[i].limit_num;
					}
					//success_message
					$("#aniboxStatus").attr("class","success");
					$("#info_image").attr("src", WC2L10n.success_info);
					$("#info_message").html(mes);

					operation.disp_delivery_method(selected);
				}else{
					//error_message
					$("#aniboxStatus").attr("class","error");
					$("#info_image").attr("src", WC2L10n.error_info);
					$("#info_message").html(mes);
				}
				$("#delivery_method_loading").html("");
			}).fail( function( retVal ) {
				$("#delivery_method_loading").html("");
			});
			return false;
		},

		/*--------送料---------*/
		disp_shipping_charge : function (id){
			var valuehtml = "";
			if(shipping_charge.length === 0) {
				for( var j = 0; j < target_market.length; j++ ) {
					var tm = target_market[j];
					valuehtml += '<div id="shipping_charge_'+tm+'">';
					for( var i = 0; i < pref[tm].length; i++ ) {
						var p = pref[tm][i];
						valuehtml += '<span class="shipping_charge_label">'+p+'</span><input type="text" name="shipping_charge_value_'+tm+'['+p+']" value="" class="charge_text medium-text right" /><?php echo wc2_crcode(); ?><br />';
					}
					valuehtml += '</div>';
				}
				$("#shipping_charge_name").html('<input name="shipping_charge_name" type="text" class="medium-text" value="" />');
				$("#shipping_charge_name2").html("");
				$("#shipping_charge_value").html(valuehtml);
				$("#shipping_charge_button").html('<div class="submit"><input name="add_shipping_charge" id="add_shipping_charge" type="button" class="button" value="<?php _e('追加', 'wc2'); ?>" onclick="operation.add_shipping_charge();" /></div>');
			}else{
				var selected = 0;
				var name_select = '<select name="shipping_charge_name_select" id="shipping_charge_name_select" onchange="operation.onchange_shipping_charge(this.selectedIndex);">';
				for(var i=0; i<shipping_charge.length; i++){
					if(shipping_charge[i]["id"] === id){
						selected = i;
						name_select += '<option value="'+shipping_charge[i]["id"]+'" selected="selected">'+shipping_charge[i]["name"]+'</option>';
					}else{
						name_select += '<option value="'+shipping_charge[i]["id"]+'">'+shipping_charge[i]["name"]+'</option>';
					}
				}

				name_select += '</select>';
				var value = "";
				for( var j = 0; j < target_market.length; j++ ) {
					var tm = target_market[j];
					valuehtml += '<div id="shipping_charge_'+tm+'">';
					for( var i = 0; i < pref[tm].length; i++ ) {
						var p = pref[tm][i];
						value = ( shipping_charge[selected][tm][p] == undefined ) ? '' : shipping_charge[selected][tm][p];
						valuehtml += '<span class="shipping_charge_label">'+p+'</span><input type="text" name="shipping_charge_value_'+tm+'['+p+']" value="'+value+'" class="charge_text medium-text right" /><?php echo wc2_crcode(); ?><br />';
					}
					valuehtml += '</div>';
				}
				$("#shipping_charge_name").html(name_select);
				$("#shipping_charge_name2").html('<input name="shipping_charge_name" type="text" class="medium-text" value="'+shipping_charge[selected]['name']+'" />');
				$("#shipping_charge_value").html(valuehtml);
				$("#shipping_charge_button").html('<div class="submit"><input name="delete_shipping_charge" id="delete_shipping_charge" type="button" class="button" value="<?php _e('削除', 'wc2'); ?>" onclick="operation.delete_shipping_charge();" />'+"\n"+'<input name="update_shipping_charge" id="update_shipping_charge" type="button" class="button" value="<?php _e('更新', 'wc2'); ?>" onclick="operation.update_shipping_charge();" /></div>');
			}
			$(document).on( "change", ".charge_text", function(){ check_money($(this)); });
			var country = $("#shipping_charge_country option:selected").val();
			for( var i = 0; i < target_market.length; i++ ) {
				if( country == target_market[i] ) {
					$("#shipping_charge_"+target_market[i]).css("display","");
				} else {
					$("#shipping_charge_"+target_market[i]).css("display","none");
				}
			}
		},

		add_shipping_charge : function() {
			var error = 0;
			if($('input[name="shipping_charge_name"]').val() == "") {
				error++;
				$('input[name="shipping_charge_name"]').css({'background-color': '#FFA'}).click(function() {
					$(this).css({'background-color': '#FFF'});
				});
			}
			for( var j = 0; j < target_market.length; j++ ) {
				var tm = target_market[j];
				for( var i = 0; i < pref[tm].length; i++ ) {
					var p = pref[tm][i];
					var value = $('input[name="shipping_charge_value_'+tm+'['+p+']"]').val();

					if( "" == value || !WC2Util.checkMoney(value) ) {
						error++;
						$('input[name="shipping_charge_value_'+tm+'['+p+']"]').css({'background-color': '#FFA'}).click(function() {
							$(this).css({'background-color': '#FFF'});
						});
					}
				}
			}
			if( 0 < error ) {
				alert("<?php _e('データに不備があります。','wc2'); ?>");
				return false;
			}
			$("#shipping_charge_loading").html('<img src="'+WC2L10n.loading_gif+'" />');
			//var name = encodeURIComponent($("input[name='shipping_charge_name']").val());
			var name = $('input[name="shipping_charge_name"]').val();
			var query = "";
			for( var j = 0; j < target_market.length; j++ ) {
				var tm = target_market[j];
				for( var i = 0; i < pref[tm].length; i++ ) {
					query += '&value_'+tm+'='+$('input[name="shipping_charge_value_'+tm+'['+pref[tm][i]+']"]').val();
				}
			}
			query = query.slice(1);
			$.ajax({
				url: ajaxurl,
				type: "POST",
				dataType: "JSON",
				scriptCharset: "UTF-8",
				processData: true,
				cache: false,
				data: {
					action: "delivery_ajax",
					mode: "add_shipping_charge",
					name: name,
					query: query,
					wc2_nonce: $("#wc2_nonce").val()
				}
			}).done( function( ret, dataType ) {
				var mes = ret.data.message;
				if( ret.success ){
					var val = ret.data.value;
					var id = val.id - 0;
					var name = val.name;
					var index = shipping_charge.length;
					shipping_charge[index] = [];
					shipping_charge[index]["id"] = id;
					shipping_charge[index]["name"] = name;

					for( var j = 0; j < target_market.length; j++ ) {
						var tm = target_market[j];
						shipping_charge[index][tm] = [];
						for( var i = 0; i < pref[tm].length; i++ ) {
							var p = pref[tm][i];
							shipping_charge[index][tm][p] = val[tm][p];
						}
					}
					//success_message
					$("#aniboxStatus").attr("class","success");
					$("#info_image").attr("src", WC2L10n.success_info);
					$("#info_message").html(mes);

					operation.disp_shipping_charge(id);
					operation.make_delivery_method_charge(get_delivery_method_charge(selected_method));
				}else{
					//error_message
					$("#aniboxStatus").attr("class","error");
					$("#info_image").attr("src", WC2L10n.error_info);
					$("#info_message").html(mes);
				}
				$("#shipping_charge_loading").html("");
			}).fail( function( retVal ) {
				$("#shipping_charge_loading").html("");
			});
			return false;
		},

		update_shipping_charge : function() {
			var error = 0;
			if($('input[name="shipping_charge_name"]').val() == "") {
				error++;
				$('input[name="shipping_charge_name"]').css({'background-color': '#FFA'}).click(function() {
					$(this).css({'background-color': '#FFF'});
				});
			}
			for( var j = 0; j < target_market.length; j++ ) {
				var tm = target_market[j];
				for( var i = 0; i < pref[tm].length; i++ ) {
					var p = pref[tm][i];
					var value = $('input[name="shipping_charge_value_'+tm+'['+p+']"]').val();
					if( "" == value || !WC2Util.checkMoney(value) ) {
						error++;
						$('input[name="shipping_charge_value_'+tm+'['+p+']"]').css({'background-color': '#FFA'}).click(function() {
							$(this).css({'background-color': '#FFF'});
						});
					}
				}
			}
			if( 0 < error ) {
				alert("<?php _e('データに不備があります。','wc2'); ?>");
				return false;
			}

			$("#shipping_charge_loading").html('<img src="'+WC2L10n.loading_gif+'" />');
			var id = $("#shipping_charge_name_select option:selected").val();
			//var name = encodeURIComponent($("input[name='shipping_charge_name']").val());
			var name = $('input[name="shipping_charge_name"]').val();
			var query = "";
			for( var j = 0; j < target_market.length; j++ ) {
				var tm = target_market[j];
				for( var i = 0; i < pref[tm].length; i++ ){
					query += '&value_'+tm+'='+$('input[name="shipping_charge_value_'+tm+'['+pref[tm][i]+']"]').val();
				}
			}
			query = query.slice(1);
			$.ajax({
				url: ajaxurl,
				type: "POST",
				dataType: "JSON",
				scriptCharset: "UTF-8",
				processData: true,
				cache: false,
				data: {
					action: "delivery_ajax",
					mode: "update_shipping_charge",
					id: id,
					name: name,
					query: query,
					wc2_nonce: $("#wc2_nonce").val()
				}
			}).done( function( ret, dataType ) {
				var mes = ret.data.message;
				if( ret.success ){
					var val = ret.data.value;
					var id = val.id - 0;
					var name = val.name;
					for(var i=0; i<shipping_charge.length; i++){
						if(id === shipping_charge[i]["id"]){
							index = i;
						}
					}
					shipping_charge[index]["name"] = name;
					for( var j = 0; j < target_market.length; j++ ) {
						var tm = target_market[j];
						for( var i = 0; i < pref[tm].length; i++ ) {
							var p = pref[tm][i];
							shipping_charge[index][tm][p] = val[tm][p];
						}
					}
					//success_message
					$("#aniboxStatus").attr("class","success");
					$("#info_image").attr("src", WC2L10n.success_info);
					$("#info_message").html(mes);

					operation.disp_shipping_charge(id);
					operation.make_delivery_method_charge(get_delivery_method_charge(selected_method));
				}else{
					//error_message
					$("#aniboxStatus").attr("class","error");
					$("#info_image").attr("src", WC2L10n.error_info);
					$("#info_message").html(mes);
				}
				$("#shipping_charge_loading").html("");
			}).fail( function( retVal ) {
				$("#shipping_charge_loading").html("");
			});
			return false;
		},

		delete_shipping_charge : function() {
			var delname = $("#shipping_charge_name_select option:selected").html();
			if(!confirm(<?php _e("'送料「'+delname+'」を削除します。よろしいですか？'", 'wc2'); ?>)) return false;

			$("#shipping_charge_loading").html('<img src="'+WC2L10n.loading_gif+'" />');
			var id = $("#shipping_charge_name_select option:selected").val();
			$.ajax({
				url: ajaxurl,
				type: "POST",
				dataType: "JSON",
				scriptCharset: "UTF-8",
				processData: true,
				cache: false,
				data: {
					action: "delivery_ajax",
					mode: "delete_shipping_charge",
					id: id,
					wc2_nonce: $("#wc2_nonce").val()
				}
			}).done( function( ret, dataType ) {
				var mes = ret.data.message;
				if( ret.success ){
					var val = ret.data.value;
					var id = val.id - 0;
					for(var i=0; i<shipping_charge.length; i++){
						if(id === shipping_charge[i]["id"]){
							index = i;
						}
					}
					shipping_charge.splice(index, 1);

					//success_message
					$("#aniboxStatus").attr("class","success");
					$("#info_image").attr("src", WC2L10n.success_info);
					$("#info_message").html(mes);

					operation.disp_shipping_charge(0);
					operation.make_delivery_method_charge(get_delivery_method_charge(selected_method));
				}else{
					$("#aniboxStatus").attr("class","error");
					$("#info_image").attr("src", WC2L10n.error_info);
					$("#info_message").html(mes);
				}
				$("#shipping_charge_loading").html("");
			}).fail( function( retVal ) {
				$("#shipping_charge_loading").html("");
			});
			return false;
		},

		onchange_shipping_charge : function(index) {
			var id = $("#shipping_charge_name_select option:eq("+index+")").val()-0;
			operation.disp_shipping_charge(id);
		},

		allCharge : function () {
			var charge = $("#allcharge").val();
			if( charge == "" ) return;
			if( confirm(<?php echo sprintf(__("'各都道府県の送料を%s' + charge + 'に一括設定します。よろしいですか？'", 'wc2'), esc_js(WC2_Funcs::get_crsymbol('js'))); ?>) ){
				var country = $("#shipping_charge_country option:selected").val();
					for( var i = 0; i < pref[country].length; i++ ) {
					$('input[name="shipping_charge_value_'+country+'['+pref[country][i]+']"]').val(charge);
				}
				$("#allcharge").val("");
			}
		},

		disp_delivery_days :function (id){
			var valuehtml = '';
			if(delivery_days.length === 0) {
				for( var j = 0; j < target_market.length; j++ ) {
					var tm = target_market[j];
					valuehtml += '<div id="delivery_days_'+tm+'">';
					for( var i = 0; i < pref[tm].length; i++ ) {
						var p = pref[tm][i];
						valuehtml += '<span class="delivery_days_label">'+p+'</span><input type="text" name="delivery_days_value_'+tm+'['+p+']" value="" class="days_text small-text right" /><?php _e('日', 'wc2'); ?><br />';
					}
					valuehtml += "</div>";
				}
				$("#delivery_days_name").html('<input name="delivery_days_name" type="text" class="medium-text" value="" />');
				$("#delivery_days_name2").html("");
				$("#delivery_days_value").html(valuehtml);
				$("#delivery_days_button").html('<div class="submit"><input name="add_delivery_days" id="add_delivery_days" type="button" class="button" value="<?php _e('追加', 'wc2'); ?>" onclick="operation.add_delivery_days();" /></div>');
			}else{
				var selected = 0;
				var name_select = '<select name="delivery_days_name_select" id="delivery_days_name_select" onchange="operation.onchange_delivery_days(this.selectedIndex);">';
				for(var i=0; i<delivery_days.length; i++){
					if(delivery_days[i]["id"] === id){
						selected = i;
						name_select += '<option value="'+delivery_days[i]["id"]+'" selected="selected">'+delivery_days[i]["name"]+'</option>';
					}else{
						name_select += '<option value="'+delivery_days[i]["id"]+'">'+delivery_days[i]["name"]+'</option>';
					}
				}
				name_select += '</select>';
				var value = "";
					for( var j = 0; j < target_market.length; j++ ) {
					var tm = target_market[j];
					valuehtml += '<div id="delivery_days_'+tm+'">';
					for( var i = 0; i < pref[tm].length; i++ ) {
						var p = pref[tm][i];
						value = ( delivery_days[selected][tm][p] == undefined ) ? '' : delivery_days[selected][tm][p];
						valuehtml += '<span class="delivery_days_label">'+p+'</span><input type="text" name="delivery_days_value_'+tm+'['+p+']" value="'+value+'" class="days_text small-text right" /><?php _e('日', 'wc2'); ?><br />';
					}
					valuehtml += '</div>';
				}
				$("#delivery_days_name").html(name_select);
				$("#delivery_days_name2").html('<input name="delivery_days_name" type="text" class="medium-text" value="'+delivery_days[selected]['name']+'" />');
				$("#delivery_days_value").html(valuehtml);
				$("#delivery_days_button").html('<div class="submit"><input name="delete_delivery_days" id="delete_delivery_days" type="button" class="button" value="<?php _e('削除', 'wc2'); ?>" onclick="operation.delete_delivery_days();" />'+"\n"+'<input name="update_delivery_days" id="update_delivery_days" type="button" class="button" value="<?php _e('更新', 'wc2'); ?>" onclick="operation.update_delivery_days();" /></div>');
			}
			$(document).on( "change", ".days_text", function(){ check_num($(this)); });
			var country = $("#delivery_days_country option:selected").val();
			for( var i = 0; i < target_market.length; i++ ) {
				if( country == target_market[i] ) {
					$("#delivery_days_"+target_market[i]).css("display","");
				} else {
					$("#delivery_days_"+target_market[i]).css("display","none");
				}
			}
		},

		add_delivery_days : function() {
			var error = 0;
			if($('input[name="delivery_days_name"]').val() == "") {
				error++;
				$('input[name="delivery_days_name"]').css({'background-color': '#FFA'}).click(function() {
					$(this).css({'background-color': '#FFF'});
				});
			}
			for( var j = 0; j < target_market.length; j++ ) {
				var tm = target_market[j];
				for( var i = 0; i < pref[tm].length; i++ ) {
					var p = pref[tm][i];
					var value = $('input[name="delivery_days_value_'+tm+'['+p+']"]').val();
					if( "" == value || !WC2Util.checkNum(value) ) {
						error++;
						$('input[name="delivery_days_value_'+tm+'['+p+']"]').css({'background-color': '#FFA'}).click(function() {
							$(this).css({'background-color': '#FFF'});
						});
					}
				}
			}
			if( 0 < error ) {
				alert("<?php _e('データに不備があります。','wc2'); ?>");
				return false;
			}

			$("#delivery_days_loading").html('<img src="'+WC2L10n.loading_gif+'" />');
			var name = $('input[name="delivery_days_name"]').val();
			var query = '';
			for( var j = 0; j < target_market.length; j++ ) {
				var tm = target_market[j];
				for( var i = 0; i < pref[tm].length; i++ ) {
					query += '&value_'+tm+'='+$('input[name="delivery_days_value_'+tm+'['+pref[tm][i]+']"]').val();
				}
			}
			query = query.slice(1);
			$.ajax({
				url: ajaxurl,
				type: "POST",
				dataType: "JSON",
				scriptCharset: "UTF-8",
				processData: true,
				cache: false,
				data: {
					action: "delivery_ajax",
					mode: "add_delivery_days",
					name: name,
					query: query,
					wc2_nonce: $("#wc2_nonce").val()
				}
			}).done( function( ret, dataType ) {
				var mes = ret.data.message;
				if( ret.success ){
					var val = ret.data.value;
					var id = val.id - 0;
					var name = val.name;
					var index = delivery_days.length;
					delivery_days[index] = [];
					delivery_days[index]["id"] = id;
					delivery_days[index]["name"] = name;
					for( var j = 0; j < target_market.length; j++ ) {
						var tm = target_market[j];
						delivery_days[index][tm] = [];
						for( var i = 0; i < pref[tm].length; i++ ) {
							var p = pref[tm][i];
							delivery_days[index][tm][p] = val[tm][p];
						}
					}
					//success_message
					$("#aniboxStatus").attr("class","success");
					$("#info_image").attr("src", WC2L10n.success_info);
					$("#info_message").html(mes);

					operation.disp_delivery_days(id);
					operation.make_delivery_method_days(get_delivery_method_days(selected_method));
				}else{
					//error_message
					$("#aniboxStatus").attr("class","error");
					$("#info_image").attr("src", WC2L10n.error_info);
					$("#info_message").html(mes);
				}
				$("#delivery_days_loading").html("");
			}).fail( function( retVal ) {
				$("#delivery_days_loading").html("");
			});
			return false;
		},

		update_delivery_days : function() {
			var error = 0;
			if($('input[name="delivery_days_name"]').val() == "") {
				error++;
				$('input[name="delivery_days_name"]').css({'background-color': '#FFA'}).click(function() {
					$(this).css({'background-color': '#FFF'});
				});
			}
			for( var j = 0; j < target_market.length; j++ ) {
				var tm = target_market[j];
				for( var i = 0; i < pref[tm].length; i++ ) {
					var p = pref[tm][i];
					var value = $('input[name="delivery_days_value_'+tm+'['+p+']"]').val();
					if( "" == value || !WC2Util.checkNum(value) ) {
						error++;
						$('input[name="delivery_days_value_'+tm+'['+p+']"]').css({'background-color': '#FFA'}).click(function() {
							$(this).css({'background-color': '#FFF'});
						});
					}
				}
			}
			if( 0 < error ) {
				alert("<?php _e('データに不備があります。','wc2'); ?>");
				return false;
			}

			$("#delivery_days_loading").html('<img src="'+WC2L10n.loading_gif+'" />');
			var id = $("#delivery_days_name_select option:selected").val();
			var name = $('input[name="delivery_days_name"]').val();
			var query = "";
			for( var j = 0; j < target_market.length; j++ ) {
				var tm = target_market[j];
				for( var i = 0; i < pref[tm].length; i++ ){
					query += '&value_'+tm+'='+$('input[name="delivery_days_value_'+tm+'['+pref[tm][i]+']"]').val();
				}
			}
			query = query.slice(1);
			$.ajax({
				url: ajaxurl,
				type: "POST",
				dataType: "JSON",
				scriptCharset: "UTF-8",
				processData: true,
				cache: false,
				data: {
					action: "delivery_ajax",
					mode: "update_delivery_days",
					id: id,
					name: name,
					query: query,
					wc2_nonce: $("#wc2_nonce").val()
				}
			}).done( function( ret, dataType ) {
				var mes = ret.data.message;
				if( ret.success ){
					var val = ret.data.value;
					var id = val.id - 0;
					var name = val.name;
					for(var i=0; i<delivery_days.length; i++){
						if(id === delivery_days[i]["id"]){
							index = i;
						}
					}
					delivery_days[index]["name"] = name;
					for( var j = 0; j < target_market.length; j++ ) {
						var tm = target_market[j];
						for( var i = 0; i < pref[tm].length; i++ ) {
							var p = pref[tm][i];
							delivery_days[index][tm][p] = val[tm][p];
						}
					}
					//success_message
					$("#aniboxStatus").attr("class","success");
					$("#info_image").attr("src", WC2L10n.success_info);
					$("#info_message").html(mes);

					operation.disp_delivery_days(id);
					operation.make_delivery_method_days(get_delivery_method_days(selected_method));
				}else{
					//error_message
					$("#aniboxStatus").attr("class","error");
					$("#info_image").attr("src", WC2L10n.error_info);
					$("#info_message").html(mes);
				}
				$("#delivery_days_loading").html("");
			}).fail( function( retVal ) {
				$("#delivery_days_loading").html("");
			});
			return false;
		},

		delete_delivery_days : function() {
			var delname = $("#delivery_days_name_select option:selected").html();
			if(!confirm(<?php _e("'配達日数設定「' + delname + '」を削除してもよろしいですか？'", 'wc2'); ?>)) return false;

			$("#delivery_days_loading").html('<img src="'+WC2L10n.loading_gif+'" />');
			var id = $("#delivery_days_name_select option:selected").val();
			$.ajax({
				url: ajaxurl,
				type: "POST",
				dataType: "JSON",
				scriptCharset: "UTF-8",
				processData: true,
				cache: false,
				data: {
					action: "delivery_ajax",
					mode: "delete_delivery_days",
					id: id,
					wc2_nonce: $("#wc2_nonce").val()
				}
			}).done( function( ret, dataType ) {
				var mes = ret.data.message;
				if( ret.success ){
					var val = ret.data.value;
					var id = val.id - 0;
					for(var i=0; i<delivery_days.length; i++){
						if(id === delivery_days[i]["id"]){
							index = i;
						}
					}
					delivery_days.splice(index, 1);
					//success_message
					$("#aniboxStatus").attr("class","success");
					$("#info_image").attr("src", WC2L10n.success_info);
					$("#info_message").html(mes);

					operation.disp_delivery_days(0);
					operation.make_delivery_method_days(get_delivery_method_days(selected_method));
				}else{
					//error_message
					$("#aniboxStatus").attr("class","error");
					$("#info_image").attr("src", WC2L10n.error_info);
					$("#info_message").html(mes);
				}
				$("#delivery_days_loading").html("");
			}).fail( function( retVal ) {
				$("#delivery_days_loading").html("");
			});
			return false;
		},

		onchange_delivery_days : function(index) {
			var id = $("#delivery_days_name_select option:eq("+index+")").val()-0;
			operation.disp_delivery_days(id);
		},

		allDeliveryDays : function () {
			var days = $("#all_delivery_days").val();
			if( days == "" ) return;
			if( confirm(<?php _e("'配達日数を全て ' + days + ' 日に変更してもよろしいですか？'", 'wc2'); ?>) ){
				var country = $("#delivery_days_country option:selected").val();
				for( var i = 0; i < pref[country].length; i++ ) {
					$('input[name="delivery_days_value_'+country+'['+pref[country][i]+']"]').val(days);
				}
				$("#all_delivery_days").val("");
			}
		}
	};

	$("#delivery_method_charge").click(function () {
		if(shipping_charge.length == 0){
			alert("<?php _e('送料を設定してください。', 'wc2'); ?>");
		}
	});

	function get_delivery_method_days(selected){
		var index = 0;
		for(var i=0; i<delivery_method.length; i++){
			if(selected === delivery_method[i]["id"]){
				index = i;
			}
		}
		if(undefined === delivery_method[index]){
			return -1;
		}else{
			return delivery_method[index]["days"];
		}
	}

	$("#delivery_method_days").click(function () {
		if(delivery_days.length == 0){
			alert("<?php _e('Please set the delivery days', 'wc2'); ?>");
		}
	});

	$("#new_delivery_method_action").click(function () {
		if(delivery_method.length === 0) return false;
		$("#delivery_method_name").html('<input name="delivery_method_name" type="text" class="medium-text" value="" />');
		$("#delivery_method_name2").html("");
		$("#delivery_method_time").val("");
		$("#delivery_method_button").html('<input name="cancel_delivery_method" id="cancel_delivery_method" type="button" class="button" value="<?php _e('キャンセル', 'wc2'); ?>" onclick="operation.disp_delivery_method(0);" /><input name="add_delivery_method" id="add_delivery_method" type="button" class="button" value="<?php _e('追加', 'wc2'); ?>" onclick="operation.add_delivery_method();" />');
		$("#delivery_method_nocod").html('<input name="delivery_method_nocod" type="checkbox" value="1" />');
		$("#delivery_method_intl").html('<label title="0"><input name="delivery_method_intl" id="delivery_method_intl_0" type="radio" value="0" checked="checked" /><span><?php _e('国内便', 'wc2'); ?></span></label><input name="delivery_method_intl" id="delivery_method_intl_1" type="radio" value="1" /><label title="1"><span><?php _e('国際便', 'wc2'); ?></span></label>');
		$("#delivery_method_item_limit_num").html('<input name="delivery_method_item_limit_num" type="text" class="medium-text right" value="" />');

		$('input[name="delivery_method_name"]').focus().select();
		operation.make_delivery_method_charge(-1);
		operation.make_delivery_method_days(-1);
	});

	$("#moveup_action").click(function () {
		var id = $("#delivery_method_name_select option:selected").val()-0;
		operation.moveup_delivery_method(id);
		operation.disp_delivery_method(id);
	});

	$("#movedown_action").click(function () {
		var id = $("#delivery_method_name_select option:selected").val()-0;
		operation.movedown_delivery_method(id);
		operation.disp_delivery_method(id);
	});

	$("#new_shipping_charge_action").click(function () {
		var valuehtml = "";
		for( var j = 0; j < target_market.length; j++ ) {
			var tm = target_market[j];
			valuehtml += '<div id="shipping_charge_'+tm+'">';
			for( var i = 0; i < pref[tm].length; i++ ) {
				var p = pref[tm][i];
				valuehtml += '<span class="shipping_charge_label">'+p+'</span><input type="text" name="shipping_charge_value_'+tm+'['+p+']" value="" class="charge_text medium-text right" /><?php echo wc2_crcode(); ?><br />';
			}
			valuehtml += '</div>';
		}
		$("#shipping_charge_country").val(base_country);
		$("#shipping_charge_name").html('<input name="shipping_charge_name" type="text" class="medium-text" value="" />');
		$("#shipping_charge_name2").html("");
		$("#shipping_charge_value").html(valuehtml);
		$("#shipping_charge_button").html('<input name="cancel_shipping_charge" id="cancel_shipping_charge" type="button" class="button" value="<?php _e('キャンセル', 'wc2'); ?>" onclick="operation.disp_shipping_charge(0);" /><input name="add_shipping_charge" id="add_shipping_charge" type="button" class="button" value="<?php _e('追加', 'wc2'); ?>" onclick="operation.add_shipping_charge();" />');
		$('input[name="shipping_charge_name"]').focus().select();
		$(document).on( "change", ".charge_text", function(){ check_money($(this)); });
		for( var i = 0; i < target_market.length; i++ ) {
			if( base_country == target_market[i] ) {
				$("#shipping_charge_"+target_market[i]).css("display","");
			} else {
				$("#shipping_charge_"+target_market[i]).css("display","none");
			}
		}
	});

	$("#new_delivery_days_action").click(function () {
		var valuehtml = "";
		for( var j = 0; j < target_market.length; j++ ) {
			var tm = target_market[j];
			valuehtml += '<div id="delivery_days_'+tm+'">';
			for( var i = 0; i < pref[tm].length; i++ ) {
				var p = pref[tm][i];
				valuehtml += '<span class="delivery_days_label">'+p+'</span><input type="text" name="delivery_days_value_'+tm+'['+p+']" value="" class="days_text small-text right" /><?php _e('日', 'wc2'); ?><br />';
			}
			valuehtml += '</div>';
		}
		$("#delivery_days_country").val(base_country);
		$("#delivery_days_name").html('<input name="delivery_days_name" type="text" class="medium-text" value="" />');
		$("#delivery_days_name2").html("");
		$("#delivery_days_value").html(valuehtml);
		$("#delivery_days_button").html('<input name="cancel_delivery_days" id="cancel_delivery_days" type="button" class="button" value="<?php _e('キャンセル', 'wc2'); ?>" onclick="operation.disp_delivery_days(0);" /><input name="add_delivery_days" id="add_delivery_days" type="button" class="button" value="<?php _e('追加', 'wc2'); ?>" onclick="operation.add_delivery_days();" />');
		$('input[name="delivery_days_name"]').focus().select();
		$(document).on( "change", ".days_text", function(){ check_num($(this)); });
		for( var i = 0; i < target_market.length; i++ ) {
			if( base_country == target_market[i] ) {
				$("#delivery_days_"+target_market[i]).css("display","");
			} else {
				$("#delivery_days_"+target_market[i]).css("display","none");
			}
		}
	});

	$("#shipping_charge_country").change(function () {
		var country = $("#shipping_charge_country option:selected").val();
		for( var i = 0; i < target_market.length; i++ ) {
			if( country == target_market[i] ) {
				$("#shipping_charge_"+target_market[i]).css("display","");
			} else {
				$("#shipping_charge_"+target_market[i]).css("display","none");
			}
		}
	});

	$("#delivery_days_country").change(function () {
		var country = $("#delivery_days_country option:selected").val();
		for( var i = 0; i < target_market.length; i++ ) {
			if( country == target_market[i] ) {
				$("#delivery_days_"+target_market[i]).css("display","");
			} else {
				$("#delivery_days_"+target_market[i]).css("display","none");
			}
		}
	});

	function check_num( obj ) {
		if( !WC2Util.checkNum( obj.val()) ) {
			alert("数値で入力してください。");
			obj.focus();
			return false;
		}
		return true;
	}

	function check_money( obj ) {
		if( !WC2Util.checkMoney(obj.val()) ) {
			alert("数値で入力してください。");
			obj.focus();
			return false;
		}
		return true;
	}
<?php do_action( 'wc2_action_admin_delivery_scripts' ); ?>
});
jQuery(document).ready(function($) {
	operation.disp_delivery_method(-1);
	operation.disp_shipping_charge(-1);
	operation.disp_delivery_days(-1);
	$("#allbutton").click( function() {
		operation.allCharge();
	});
	$("#allbutton_delivery_days").click( function() {
		operation.allDeliveryDays();
	});
});
</script>
<?php
	}

	/***********************************
	 * AJAX request's action.
	 *
	 * @since    1.0.0
	 ***********************************/
	function delivery_ajax() {
		if( !check_ajax_referer( 'wc2_setting_delivery', 'wc2_nonce', false ) ) {
			//error
			$data = array(
				'message' => '不正なパラメータです'
			);
			wp_send_json_error($data);
		}
		if( !isset($_POST['action']) or !isset($_POST['mode']) ) die();
		if( $_POST['action'] != 'delivery_ajax' ) die();

		switch ($_POST['mode']) {
			case 'add_delivery_method':
				$res = $this->add_delivery_method();
				break;
			case 'update_delivery_method':
				$res = $this->update_delivery_method();
				break;
			case 'delete_delivery_method':
				$res = $this->delete_delivery_method();
				break;
			case 'moveup_delivery_method':
				$res = $this->moveup_delivery_method();
				break;
			case 'movedown_delivery_method':
				$res = $this->movedown_delivery_method();
				break;
			case 'add_shipping_charge':
				$res = $this->add_shipping_charge();
				break;
			case 'update_shipping_charge':
				$res = $this->update_shipping_charge();
				break;
			case 'delete_shipping_charge':
				$res = $this->delete_shipping_charge();
				break;
			case 'add_delivery_days':
				$res = $this->add_delivery_days();
				break;
			case 'update_delivery_days':
				$res = $this->update_delivery_days();
				break;
			case 'delete_delivery_days':
				$res = $this->delete_delivery_days();
				break;
		}
		$res = apply_filters( 'wc2_filter_admin_delivery_ajax', $res );
		die( $res );
	}

	/*******************************************
	* 配送名追加
	* @since	1.0.0
	*
	* NOTE: 
	********************************************/
	function add_delivery_method(){
		$data = wc2_stripslashes_deep_post( $_POST );
		$delivery_ops = wc2_get_option('delivery');
		$name = trim($data['name']);
		foreach((array)$delivery_ops['delivery_method'] as $deli){
			$ids[] = (int)$deli['id'];
		}
		if(isset($ids)){
			rsort($ids);
			$newid = $ids[0]+1;
		}else{
			$newid = 0;
		}
		$index = isset($delivery_ops['delivery_method']) ? count($delivery_ops['delivery_method']) : 0;
		$delivery_ops['delivery_method'][$index]['id'] = $newid;
		$delivery_ops['delivery_method'][$index]['name'] = $name;
		$delivery_ops['delivery_method'][$index]['time'] = str_replace("\r\n", "\n", $data['time']);
		$delivery_ops['delivery_method'][$index]['time'] = str_replace("\r", "\n", $delivery_ops['delivery_method'][$index]['time']);
		$delivery_ops['delivery_method'][$index]['charge'] = (int)$data['charge'];
		$delivery_ops['delivery_method'][$index]['days'] = (int)$data['days'];
		$delivery_ops['delivery_method'][$index]['nocod'] = $data['nocod'];
		$delivery_ops['delivery_method'][$index]['intl'] = $data['intl'];
		$delivery_ops['delivery_method'][$index]['limit_num'] = $data['limit_num'];

		$res = wc2_update_option('delivery', $delivery_ops);
		if( $res !== NULL ){
			//success
			$new_delivery_ops = wc2_get_option('delivery');
			$data_val = $new_delivery_ops['delivery_method'][$index];
			$data = array(
				'message' => __( 'Updated!' ),
				'value'   => $data_val
			);
			wp_send_json_success($data);
		}else{
			//error
			$data = array(
				'message' => __( 'Update Failed' )
			);
			wp_send_json_error($data);
		}
	}

	/*******************************************
	* 配送名更新
	* @since	1.0.0
	*
	* NOTE: 
	********************************************/
	function update_delivery_method() {
		$data = wc2_stripslashes_deep_post( $_POST );
		$delivery_ops = wc2_get_option('delivery');
		$name = trim($data['name']);
		$id = (int)$data['id'];
		$charge = (int)$data['charge'];
		for($i=0; $i<count($delivery_ops['delivery_method']); $i++){
			if($delivery_ops['delivery_method'][$i]['id'] === $id){
				$index = $i;
			}
		}
		$delivery_ops['delivery_method'][$index]['name'] = $name;
		$delivery_ops['delivery_method'][$index]['charge'] = $charge;
		$delivery_ops['delivery_method'][$index]['time'] = str_replace("\r\n", "\n", $data['time']);
		$delivery_ops['delivery_method'][$index]['time'] = str_replace("\r", "\n", $delivery_ops['delivery_method'][$index]['time']);
		$delivery_ops['delivery_method'][$index]['days'] = (int)$data['days'];
		$delivery_ops['delivery_method'][$index]['nocod'] = $data['nocod'];
		$delivery_ops['delivery_method'][$index]['intl'] = $data['intl'];
		$delivery_ops['delivery_method'][$index]['limit_num'] = $data['limit_num'];

		$res = wc2_update_option('delivery', $delivery_ops);

		if( $res !== NULL ){
			//success
			$new_delivery_ops = wc2_get_option('delivery');
			$data_val = $new_delivery_ops['delivery_method'][$index];
			$data = array(
				'message' => __( 'Updated!' ),
				'value'   => $data_val
			);
			wp_send_json_success($data);
		}else{
			//error
			$data = array(
				'message' => __( 'Update Failed' )
			);
			wp_send_json_error($data);
		}
	}

	/*******************************************
	* 配送名削除
	* @since	1.0.0
	*
	* NOTE: 
	********************************************/
	function delete_delivery_method() {
		$delivery_ops = wc2_get_option('delivery');
		$id = (int)$_POST['id'];
		for($i=0; $i<count($delivery_ops['delivery_method']); $i++){
			if($delivery_ops['delivery_method'][$i]['id'] === $id){
				$index = $i;
			}
		}
		array_splice($delivery_ops['delivery_method'], $index, 1);
		$res = wc2_update_option('delivery', $delivery_ops);

		if( $res !== NULL ){
			//success
			$data_val = array('id' => $id);
			$data = array(
				'message' => __( 'Updated!' ),
				'value'   => $data_val
			);
			wp_send_json_success($data);
		}else{
			//error
			$data = array(
				'message' => __( 'Update Failed' ),
			);
			wp_send_json_error($data);
		}
	}

	/*******************************************
	* 配送名の優先順位を上げる
	* @since	1.0.0
	*
	* NOTE: 
	********************************************/
	function moveup_delivery_method() {
		$delivery_ops = wc2_get_option('delivery');
		$selected_id = (int)$_POST['id'];
		$ct = count($delivery_ops['delivery_method']);
		for($i=0; $i<$ct; $i++){
			if($delivery_ops['delivery_method'][$i]['id'] === $selected_id){
				$index = $i;
			}
		}
		if($index !== 0) {
			$from_index = $index;
			$to_index = $index - 1;
			$from_dm = $delivery_ops['delivery_method'][$from_index];
			$to_dm = $delivery_ops['delivery_method'][$to_index];
			for($i=0; $i<$ct; $i++){
				if($i === $to_index){
					$delivery_ops['delivery_method'][$i] = $from_dm;
				}else if($i === $from_index){
					$delivery_ops['delivery_method'][$i] = $to_dm;
				}
			}
			$res = wc2_update_option('delivery', $delivery_ops);
			if( $res !== NULL ){
				//success
				$new_delivery_ops = wc2_get_option('delivery');
				$data_val = $new_delivery_ops['delivery_method'];
				$data = array(
					'message' => __( 'Updated!' ),
					'value' => $data_val,
					'selected' => $selected_id
				);
				wp_send_json_success($data);
			}else{
				//error
				$data = array(
					'message' => __( 'Update Failed' ),
				);
				wp_send_json_error($data);
			}
		}
	}

	/*******************************************
	* 配送名の優先順位を下げる
	* @since	1.0.0
	*
	* NOTE: 
	********************************************/
	function movedown_delivery_method() {
		$delivery_ops = wc2_get_option('delivery');
		$selected_id = (int)$_POST['id'];
		$ct = count($delivery_ops['delivery_method']);
		for($i=0; $i<$ct; $i++){
			if($delivery_ops['delivery_method'][$i]['id'] === $selected_id){
				$index = $i;
			}
		}
		if($index < $ct-1) {
			$from_index = $index;
			$to_index = $index + 1;
			$from_dm = $delivery_ops['delivery_method'][$from_index];
			$to_dm = $delivery_ops['delivery_method'][$to_index];
			for($i=0; $i<$ct; $i++){
				if($i === $to_index){
					$delivery_ops['delivery_method'][$i] = $from_dm;
				}else if($i === $from_index){
					$delivery_ops['delivery_method'][$i] = $to_dm;
				}
			}
			$res = wc2_update_option('delivery', $delivery_ops);
			if( $res !== NULL ){
				//success
				$new_delivery_ops = wc2_get_option('delivery');
				$data_val = $new_delivery_ops['delivery_method'];
				$data = array(
					'message' => __( 'Updated!' ),
					'value' => $new_delivery_ops['delivery_method'],
					'selected' => $selected_id
				);
				wp_send_json_success($data);
			}else{
				//error
				$data = array(
					'message' => __( 'Update Failed' ),
				);
				wp_send_json_error($data);
			}
		}
	}

	/*******************************************
	* 送料を追加する
	* @since	1.0.0
	*
	* NOTE: 
	********************************************/
	function add_shipping_charge() {
		$data = wc2_stripslashes_deep_post( $_POST );
		$shipping_charge = wc2_get_option('shipping_charge');
		$system_ops = wc2_get_option('system');
		$name = trim($data['name']);
		foreach((array)$shipping_charge as $charge){
			$ids[] = (int)$charge['id'];
		}
		if(isset($ids)){
			rsort($ids);
			$newid = $ids[0]+1;
		}else{
			$newid = 0;
		}
		$index = isset($shipping_charge) ? count($shipping_charge) : 0;
		$target_market = ( isset($system_ops['target_market']) && !empty($system_ops['target_market']) ) ? $system_ops['target_market'] : WC2_Funcs::get_local_target_market();
		$query = explode('&', $data['query']);
		foreach((array)$query as $query_val){
			list($key,$val) = explode('=', $query_val);
			$data[$key][] = $val;
		}

		foreach( (array)$target_market as $tm ) {
			$prefs = $system_ops['province'][$tm];
			$value = $data['value_'.$tm];
			$shipping_charge[$index]['id'] = $newid;
			$shipping_charge[$index]['name'] = $name;
			for( $i = 0; $i < count($prefs); $i++ ) {
				$shipping_charge[$index][$tm][$prefs[$i]] = (float)$value[$i];
			}
		}

		$res = wc2_update_option( 'shipping_charge', $shipping_charge );
		if( $res !== NULL ){
			//error
			$new_shipping_charge = wc2_get_option('shipping_charge');
			$data_val = $new_shipping_charge[$index];
			$data = array(
				'message' => __( 'Updated!' ),
				'value'   => $data_val
			);
			wp_send_json_success($data);
		}else{
			$data = array(
				'message' => __( 'Update Failed' )
			);
			wp_send_json_error($data);
		}
	}

	/**********************************
	* 送料を更新する
	*
	*
	**********************************/
	function update_shipping_charge() {
		$data = wc2_stripslashes_deep_post( $_POST );
		$shipping_charge = wc2_get_option('shipping_charge');
		$system_ops = wc2_get_option('system');
		$name = trim($data['name']);
		$id = (int)$data['id'];
		for($i=0; $i<count($shipping_charge); $i++){
			if($shipping_charge[$i]['id'] === $id){
				$index = $i;
			}
		}
		$shipping_charge[$index]["name"] = $name;
		$target_market = ( isset($system_ops['target_market']) && !empty($system_ops['target_market']) ) ? $system_ops['target_market'] : WC2_Funcs::get_local_target_market();
		$query = explode('&', $data['query']);
		foreach((array)$query as $query_val){
			list($key,$val) = explode('=', $query_val);
			$data[$key][] = $val;
		}

		foreach( (array)$target_market as $tm ) {
			$prefs = $system_ops['province'][$tm];
			$value = $data['value_'.$tm];
			for( $i = 0; $i < count($prefs); $i++ ) {
				$shipping_charge[$index][$tm][$prefs[$i]] = (float)$value[$i];
			}
		}

		$res = wc2_update_option('shipping_charge', $shipping_charge);
		if( $res !== NULL ){
			//success
			$new_shipping_charge = wc2_get_option('shipping_charge');
			$data_val = $new_shipping_charge[$index];
			$data = array(
				'message' => __( 'Updated!' ),
				'value'   => $data_val
			);
			wp_send_json_success($data);
		}else{
			//error
			$data = array(
				'message' => __( 'Update Failed' )
			);
			wp_send_json_error($data);
		}
	}

	/********************************
	* 送料を削除する
	*
	*
	*********************************/
	function delete_shipping_charge() {
		$shipping_charge = wc2_get_option('shipping_charge');
		$id = (int)$_POST['id'];
		for($i=0; $i<count($shipping_charge); $i++){
			if($shipping_charge[$i]['id'] === $id){
				$index = $i;
			}
		}
		array_splice($shipping_charge, $index, 1);
		$res = wc2_update_option('shipping_charge', $shipping_charge);

		if( $res !== NULL ){
			//success
			$data_val = array('id' => $id);
			$data = array(
				'message' => __( 'Updated!' ),
				'value' => $data_val
			);
			wp_send_json_success($data);
		}else{
			//error
			$data = array(
				'message' => __( 'Update Failed' )
			);
			wp_send_json_error($data);
		}
	}

	/*******************************************
	* 配達日数を追加
	* @since	1.0.0
	*
	* NOTE: 
	********************************************/
	function add_delivery_days() {
		$data = wc2_stripslashes_deep_post( $_POST );
		$delivery_days = wc2_get_option('delivery_days');
		$system_ops = wc2_get_option('system');
		$name = trim($data['name']);
		foreach((array)$delivery_days as $charge){
			$ids[] = (int)$charge['id'];
		}
		if(isset($ids)){
			rsort($ids);
			$newid = $ids[0]+1;
		}else{
			$newid = 0;
		}
		$index = isset($delivery_days) ? count($delivery_days) : 0;
		$target_market = ( isset($system_ops['target_market']) && !empty($system_ops['target_market']) ) ? $system_ops['target_market'] : WC2_Funcs::get_local_target_market();
		$query = explode('&', $data['query']);
		foreach((array)$query as $query_val){
			list($key,$val) = explode('=', $query_val);
			$data[$key][] = $val;
		}

		foreach( (array)$target_market as $tm ) {
			$prefs = $system_ops['province'][$tm];
			$value = $data['value_'.$tm];
			$delivery_days[$index]['id'] = $newid;
			$delivery_days[$index]['name'] = $name;
			for( $i = 0; $i < count($prefs); $i++ ) {
				$delivery_days[$index][$tm][$prefs[$i]] = (int)$value[$i];
			}
		}

		$res = wc2_update_option('delivery_days', $delivery_days);
		if( $res !== NULL ){
			//success
			$new_delivery_days = wc2_get_option('delivery_days');
			$data_val = $new_delivery_days[$index];
			$data = array(
				'message' => __( 'Updated!' ),
				'value'   => $data_val
			);
			wp_send_json_success($data);
		}else{
			//error
			$data = array(
				'message' => __( 'Update Failed' )
			);
			wp_send_json_error($data);
		}
	}

	/*******************************************
	* 配達日数を更新
	* @since	1.0.0
	*
	* NOTE: 
	********************************************/
	function update_delivery_days() {
		$data = wc2_stripslashes_deep_post( $_POST );
		$delivery_days = wc2_get_option('delivery_days');
		$system_ops = wc2_get_option('system');
		$name = trim($data['name']);
		$id = (int)$data['id'];
		for($i=0; $i<count($delivery_days); $i++){
			if($delivery_days[$i]['id'] === $id){
				$index = $i;
			}
		}
		$delivery_days[$index]['name'] = $name;
		$target_market = ( isset($system_ops['target_market']) && !empty($system_ops['target_market']) ) ? $system_ops['target_market'] : WC2_Funcs::get_local_target_market();
		$query = explode('&', $data['query']);
		foreach((array)$query as $query_val){
			list($key,$val) = explode('=', $query_val);
			$data[$key][] = $val;
		}

		foreach( (array)$target_market as $tm ) {
			$prefs = $system_ops['province'][$tm];
			$value = $data['value_'.$tm];
			for( $i = 0; $i < count($prefs); $i++ ) {
				$delivery_days[$index][$tm][$prefs[$i]] = (int)$value[$i];
			}
		}
		$res = wc2_update_option('delivery_days', $delivery_days);

		if($res !== NULL){
			//success
			$new_delivery_days = wc2_get_option('delivery_days');
			$data_val = $new_delivery_days[$index];
			$data = array(
				'message' => __( 'Updated!' ),
				'value'   => $data_val
			);
			wp_send_json_success($data);
		}else{
			//error
			$data = array(
				'message' => __( 'Update Failed' )
			);
			wp_send_json_error($data);
		}
	}

	/*******************************************
	* 配達日数を削除
	* @since	1.0.0
	*
	* NOTE: 
	********************************************/
	function delete_delivery_days() {
		$delivery_days = wc2_get_option('delivery_days');
		$id = (int)$_POST['id'];
		for($i=0; $i<count($delivery_days); $i++){
			if($delivery_days[$i]['id'] === $id){
				$index = $i;
			}
		}
		array_splice($delivery_days, $index, 1);
		$res = wc2_update_option('delivery_days', $delivery_days);

		if( $res !== NULL ){
			$data_val = array('id' => $id);
			$data = array(
				'message' => __( 'Updated!' ),
				'value' => $data_val
			);
			wp_send_json_success($data);
		}else{
			//error
			$data = array(
				'message' => __( 'Update Failed' )
			);
			wp_send_json_error($data);
		}
	}

}

