<?php
/**
 * Welcart2.
 *
 * @package   WC2 Custom Field
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
class WC2_CustomField extends WC2_Admin_Page {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/***********************************
	 * Constructor
	 *
	 * @since     1.0.0
	 ***********************************/
	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_custom_field_ajax', array( $this, 'custom_field_ajax' ) );
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
				'title' => 'カスタムフィールド設定',
				'id' => 'customfield',
				'callback' => array( $this, 'get_help_customfield' )
			),
		);

		foreach( $tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}
	}

	function get_help_customfield() {
		echo "<dl>
				<dt>カスタム・メンバーフィールド</dt>
					<dd>会員情報ページに任意のフィールドを追加します。</dd>
				<dt>カスタム・カスタマーフィールド</dt>
					<dd>お客様情報に任意のフィールドを追加します。</dd>
				<dt>カスタム・デリバリーフィールド</dt>
					<dd>配送先情報に任意のフィールドを追加します。</dd>
				<dt>カスタム・オーダーフィールド</dt>
					<dd>支払方法のページに任意のフィールドを追加します。</dd>
			</dl>";
	}

	/***********************************
	 * Add a sub menu page.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function add_admin_menu() {
		$this->plugin_screen_hook_suffix = add_submenu_page( $this->menu_slug['setting'], 'カスタムフィールド', 'カスタムフィールド', 'create_users', 'wc2_customfield', array( $this, 'admin_csf_page' ) );
		add_action( 'load-' . $this->plugin_screen_hook_suffix, array( $this, 'load_csf_action' ) );
	}

	/***********************************
	 * Runs when an administration menu page is loaded.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function load_csf_action() {

	}

	/***********************************
	 * The function to be called to output the content for this page.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function admin_csf_page(){
		if( !isset( $this->plugin_screen_hook_suffix ) )
			return;

		$screen = get_current_screen();
		if( $this->plugin_screen_hook_suffix != $screen->id )
			return;

		$custom_field_option = wc2_get_option('custom_field_option');

		$csf_capacity = '';
		foreach($custom_field_option['capa'] as $key => $value) {
			$csf_capacity .= '<option value="'.esc_attr($key).'">'.esc_html($value)."</option>\n";
		}

		$csf_meansoption = '';
		foreach($custom_field_option['means'] as $key => $value) {
			$csf_meansoption .= '<option value="'.esc_attr($key).'">'.esc_html($value)."</option>\n";
		}

		$csf_positions = '';
		foreach($custom_field_option['position'] as $key => $value) {
			$csf_positions .= '<option value="'.esc_attr($key).'">'.esc_html($value)."</option>\n";
		}

		$csmb_field_keys = WC2_Funcs::get_custom_field_keys(WC2_CSMB);
		$csmb_display = (empty($csmb_field_keys)) ? ' style="display: none;"' : '';

		$cscs_field_keys = WC2_Funcs::get_custom_field_keys(WC2_CSCS);
		$cscs_display = (empty($cscs_field_keys)) ? ' style="display: none;"' : '';

		$csde_field_keys = WC2_Funcs::get_custom_field_keys(WC2_CSDE);
		$csde_display = (empty($csde_field_keys)) ? ' style="display: none;"' : '';

		$csod_field_keys = WC2_Funcs::get_custom_field_keys(WC2_CSOD);
		$csod_display = (empty($csod_field_keys)) ? ' style="display: none;"' : '';


		require_once( WC2_PLUGIN_DIR . '/admin/views/custom-field-page.php' );
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
?>
<script type="text/javascript">
jQuery(function($){
	var tb = $('#csf-tabs').tabs();

	if( $.fn.jquery < "1.10" ) {
		var $tabs = $('#csf-tabs').tabs({
			cookie: {
				expires: 1
			}
		});
	} else {
		$( "#csf-tabs" ).tabs({
			active: ($.cookie("csf-tabs")) ? $.cookie("csf-tabs") : 0
			, activate: function( event, ui ){
				$.cookie("csf-tabs", $(this).tabs("option", "active"));
			}
		});
	}

	customField = {
		settings: {
			url: ajaxurl,
			type: 'POST',
			dataType: 'JSON',
			cache: false
		},

		//** Custom Member **
		Add: function(prefix) {
			if( 'csmb' != prefix && 'cscs' != prefix && 'csde' != prefix && 'csod' != prefix ){
				alert('error：' + prefix );
				return false;
			}
			$("#ajax-response-" + prefix).html("");
			var nonce = $("#wc2_nonce").val();
			var key = $("#new" + prefix + "key").val();
			var name = $("#new" + prefix + "name").val();
			var value = $("#new" + prefix + "value").val();
			var capa = $("#new" + prefix + "capa").val();
			var means = $("#new" + prefix + "means").val();
			var essential = ($("input#new" + prefix + "essential").attr("checked")) ? '1' : '0';
			var position = $("#new" + prefix + "position").val();
			var mes = '';
			if( '' == key || !WC2Util.checkCode( key ) ) 
				mes += '<p>フィールドキーは半角英数（-_を含む）で入力してください。</p>';
			if( '' == name ) 
				mes += '<p>フィールド名の値を入力してください。</p>';
			if( ('select' == means || 'radio' == means || 'check' == means ) && '' == value ) 
				mes += '<p>セレクト値を入力してください。</p>';

			if( '' != mes ) {
				mes = '<div class="error">'+mes+'</div>';
				$("#ajax-response-" + prefix).html(mes);
				return false;
			}

			$("#new" + prefix + "_loading").html('<img src="'+WC2L10n.loading_gif+'" />');

			var s = customField.settings;
			s.data = "action=custom_field_ajax&directive=add&prefix="+prefix+"&newkey="+key+"&newname="+name+"&newvalue="+value+"&newcapa="+capa+"&newmeans="+means+"&newessential="+essential+"&newposition="+position+"&wc2_nonce="+nonce;
			$.ajax(s).done( function(ret){
				var list = null;
				$("#ajax-response-" + prefix).html('');
				$("#new" + prefix + "_loading").html('');
				if( !ret.success ) {
					var error_ul = '<ul>';
					$.each( ret.data.message, function(){
						error_ul += '<li>' + this + '</li>';
					});
					error_ul += '</ul>';
					$("#ajax-response-" + prefix).html('<div class="error">' + error_ul + '</div>');
				}
				list = ret.data.value;
				if(list.length > 1) $("table#" + prefix + "-list-table").removeAttr("style");
				$("tbody#" + prefix + "-list").html(list);
				$("#" + prefix + "-" + key).css({'background-color': '#FF4'});
				$("#" + prefix + "-" + key).animate({ 'background-color': '#FFFFEE' }, 2000 );
				$("#new" + prefix + "key").val("");
				$("#new" + prefix + "name").val("");
				$("#new" + prefix + "value").val("");
/*				$("#new" + prefix + "means").val(0);*/
				$("#new" + prefix + "essential").attr({checked: false});
			}).fail( function(msg){
				$("#ajax-response-" + prefix).html(msg);
				$("#new" + prefix + "_loading").html('');
			});

			return false;
		},

		Update: function(prefix, key) {
			if( 'csmb' != prefix && 'cscs' != prefix && 'csde' != prefix && 'csod' != prefix ){
				alert('error：' + prefix );
				return false;
			}
			$("#ajax-response-" + prefix).html("");
			var nonce = $("#wc2_nonce").val();
			var name = $(':input[name="' + prefix + '['+key+'][name]"]').val();
			var value = $(':input[name="' + prefix + '['+key+'][value]"]').val();
			var capa = $(':input[name="' + prefix + '['+key+'][capa]"]').val();
			var means = $(':input[name="' + prefix + '['+key+'][means]"]').val();
			var essential = ($(':input[name="' + prefix + '['+key+'][essential]"]').attr("checked")) ? '1' : '0';
			var position = $(':input[name="' + prefix + '['+key+'][position]"]').val();
			var mes = '';
			if( '' == name )
				mes += '<p>フィールド名の値を入力してください。</p>';
			if( ('select' == means || 'radio' == means || 'check' == means ) && '' == value ) 
				mes += '<p>セレクト値を入力してください。</p>';
			if( '' != mes ) {
				mes = '<div class="error">'+mes+'</div>';
				$("#ajax-response-" + prefix).html(mes);
				return false;
			}

			$("#" + prefix + "_loading-" + key).html('<img src="'+WC2L10n.loading_gif+'" />');

			var s = customField.settings;
			s.data = "action=custom_field_ajax&directive=update&prefix="+prefix+"&key="+key+"&name="+name+"&value="+value+"&capa="+capa+"&means="+means+"&essential="+essential+"&position="+position+"&wc2_nonce="+nonce;
			$.ajax(s).done( function( ret ) {
				var list = null;
				$("#ajax-response-" + prefix).html('');
				$("#" + prefix + "_loading-" + key).html('');
				if( !ret.success ) {
					var error_ul = '<ul>';
					$.each( ret.data.message, function(){
						error_ul += '<li>' + this + '</li>';
					});
					error_ul += '</ul>';
					$("#ajax-response-" + prefix).html('<div class="error">' + error_ul + '</div>');
				}
				list = ret.data.value;
				$("tbody#" + prefix + "-list").html(list);
				$("#" + prefix + "-" + key).css({'background-color': '#FF4'});
				$("#" + prefix + "-" + key).animate({ 'background-color': '#FFFFEE' }, 2000 );
			}).fail( function(msg){
				$("#ajax-response-" + prefix).html(msg);
				$("#" + prefix + "_loading-" + key).html('');
			});

			return false;
		},

		Delete: function(prefix, key) {
			if( 'csmb' != prefix && 'cscs' != prefix && 'csde' != prefix && 'csod' != prefix ){
				alert('error：' + prefix );
				return false;
			}
			$("#ajax-response-" + prefix).html("");
			var nonce = $("#wc2_nonce").val();
			$("#" + prefix + "-" + key).css({'background-color': '#F00'});
			$("#" + prefix + "-" + key).animate({ 'background-color': '#FFFFEE' }, 1000 );
			var s = customField.settings;
			s.data = "action=custom_field_ajax&directive=remove&prefix="+prefix+"&key="+key+"&wc2_nonce="+nonce;
			$.ajax(s).done(function(ret){
				var list = null;
				$("#ajax-response-" + prefix).html('');
				if( !ret.success ) {
					var error_ul = '<ul>';
					$.each( ret.data.message, function(){
						error_ul += '<li>' + this + '</li>';
					});
					error_ul += '</ul>';
					$("#ajax-response-" + prefix).html('<div class="error">' + error_ul + '</div>');
				}
				list = ret.data.value;
				$("tbody#" + prefix + "-list").html(list);
				//if(list.length < 1) $("table#" + prefix + "-list-table").attr("style", "display: none");
				if( null == list) $("table#" + prefix + "-list-table").attr("style", "display: none");
			}).fail(function(msg){
				$("#ajax-response-" + prefix).html(msg);
			});
			return false;
		}
	};
<?php do_action( 'wc2_action_admin_customfield_scripts' ); ?>
});
</script>
<?php
	}

	/***********************************
	 * AJAX request's action.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function custom_field_ajax(){
		$err = new WP_Error();
		if( !check_ajax_referer( 'wc2_custom_field', 'wc2_nonce', false ) || !isset( $_REQUEST['directive'] ) || !isset( $_REQUEST['prefix'] ) ) {
			$err->add('error', '不正なパラメータです');
		} else {
			$action = $_REQUEST['directive'];
			$prefix = $_REQUEST['prefix'];
			$value = NULL;

			switch($action){

				//case 'add_member':
				case 'add':
					$args = array(
						'key' 		=> $_REQUEST['newkey'], 
						'name' 		=> $_REQUEST['newname'], 
						'value' 	=> rtrim($_REQUEST['newvalue'],"\n"), 
						'capa' 		=> $_REQUEST['newcapa'], 
						'means' 	=> $_REQUEST['newmeans'], 
						'essential' => $_REQUEST['newessential'], 
						'position' 	=> $_REQUEST['newposition'] 
					);
					$new_key = $this->add_custom_field($prefix, $args);

					if( 0 === $new_key ){
						$err->add($action, '同じフィールドキーが存在します。');
					}elseif( !$new_key ){
						$err->add($action, '新規カスタムフィールドを登録できませんでした。');
					}

					$csmb_field_keys = WC2_Funcs::get_custom_field_keys($prefix);
					if(is_array($csmb_field_keys)) {
						foreach($csmb_field_keys as $custom_field_key) 
							$value .= $this->get_custom_field( $custom_field_key );
					}
					break;

				case 'update':
					$args = array(
						'key' 		=> $_REQUEST['key'], 
						'name' 		=> $_REQUEST['name'], 
						'value' 	=> rtrim($_REQUEST['value'],"\n"), 
						'capa' 		=> $_REQUEST['capa'], 
						'means' 	=> $_REQUEST['means'], 
						'essential' => $_REQUEST['essential'], 
						'position' 	=> $_REQUEST['position'] 
					);

					$res = $this->update_custom_field($prefix, $args);

					if( !$res && 0 !== $res ){
						$err->add($action, 'カスタムフィールドを更新できませんでした。');
					}

					$csmb_field_keys = WC2_Funcs::get_custom_field_keys($prefix);
					if(is_array($csmb_field_keys)) {
						foreach($csmb_field_keys as $custom_field_key) 
							$value .= $this->get_custom_field( $custom_field_key );
					}
					break;

				case 'remove':
					$args = array(
						'key' 		=> $_REQUEST['key'] 
					);
					$res = $this->remove_custom_field($prefix, $args);

					if( !$res ){
						$err->add($action, 'カスタムフィールドを削除できませんでした。');
					}
					$csmb_field_keys = WC2_Funcs::get_custom_field_keys($prefix);
					if(is_array($csmb_field_keys)) {
						foreach($csmb_field_keys as $custom_field_key) 
							$value .= $this->get_custom_field( $custom_field_key );
					}
					break;
				default:
					$err->add('error', '不正なパラメータです');
					break;
			}
		}

		if( $err->get_error_code() ){
			$data = array(
			'message'	=> $err->get_error_messages(),
			'value'		=> $value
			);
			wp_send_json_error($data);
		}else{
			$data = array(
			'message'	=> 'OK',
			'value'		=> $value
			);
			wp_send_json_success($data);
		}
	}
	
	public function add_custom_field( $prefix, $args){
		
		$key = $prefix . '_' . $args['key'];
		if( wc2_get_option( $key ) )
			return 0;
		
		return wc2_update_option( $key, $args );
	}
	
	public function update_custom_field( $prefix, $args){
		
		$key = $prefix . '_' . $args['key'];
		
		return wc2_update_option( $key, $args );
	}
	
	public function remove_custom_field( $prefix, $args){
		
		$key = $prefix . '_' . $args['key'];
		
		return wc2_delete_option( $key );
	}
	
	public function get_custom_field( $custom_field_key ){
		
		$field_param = wc2_get_option( $custom_field_key );
		list( $prefix, $key) = explode( '_', $custom_field_key, 2 );
		$r = '';

		if( empty($field_param) )
			return $r;

		$custom_field_option = wc2_get_option('custom_field_option');
		$name = $field_param['name'];
		
		$capaoption = '';
		foreach($custom_field_option['capa'] as $capakey => $capavalue) {
			$selected = ($capakey == $field_param['capa']) ? ' selected="selected"' : '';
			$capaoption .= '<option value="' . $capakey . '"' . $selected . '>' . esc_html($capavalue) . '</option>' . "\n";
		}

		$meansoption = '';
		foreach($custom_field_option['means'] as $meankey => $meanvalue) {
			$selected = ($meankey == $field_param['means']) ? ' selected="selected"' : '';
			$meansoption .= '<option value="' . $meankey . '"' . $selected . '>' . esc_html($meanvalue) . '</option>' . "\n";
		}

		$essential = $field_param['essential'] == 1 ? ' checked="checked"' : '';

		$value = trim($field_param['value']);
		
		$positionsoption = '';

		if( WC2_CSOD == $prefix ){
			$csod_position = array('beforeremarks' => '備考の前', 'other' => 'その他');
			foreach( $csod_position as $poskey => $posvalue ){
				$selected = ($poskey == $field_param['position']) ? ' selected="selected"' : '';
				$positionsoption .= '<option value="' . $poskey . '"' . $selected . '>' . esc_html($posvalue) . '</option>' . "\n";
			}
		}else{
			foreach($custom_field_option['position'] as $poskey => $posvalue) {
				$selected = ($poskey == $field_param['position']) ? ' selected="selected"' : '';
				$positionsoption .= '<option value="' . $poskey . '"' . $selected . '>' . esc_html($posvalue) . '</option>' . "\n";
			}
		}
		
		$r .= '
		<tr id="' . $prefix . '-' . $key . '" class="' . $prefix . ' ' . $key . '">
			<td class="item-opt-key">
				<div><input type="text" name="' . $prefix . '[' . $key . '][key]" id="' . $prefix . '[' . $key . '][key]" class="regular-text optname" value="' . $key . '" readonly /></div>
				<div><input type="text" name="' . $prefix . '[' . $key . '][name]" id="' . $prefix . '[' . $key . '][name]" class="regular-text optname" value="' . $name . '" /></div>
				<div class="optcheck">
					<select name="' . $prefix . '[' . $key . '][capa]" id="' . $prefix . '[' . $key . '][capa]">' . $capaoption . '</select>
					<select name="' . $prefix . '[' . $key . '][means]" id="' . $prefix . '[' . $key . '][means]">' . $meansoption . '</select>
					<select name="' . $prefix . '[' . $key . '][position]" id="' . $prefix . '[' . $key . '][position]">' . $positionsoption . '</select>
					<label for="' . $prefix . '[' . $key . '][essential]"><input type="checkbox" name="' . $prefix . '[' . $key . '][essential]" id="' . $prefix . '[' . $key . '][essential]" value="1"' . $essential . ' /><span>' . __('必須項目','wc2') . '</span></label>
				</div>
				<div class="submit">
					<input type="button" class="button" name="del_' . $prefix . '[' . $key . ']" id="del_' . $prefix . '[' . $key . ']" value="' . esc_attr(__( 'Delete' )) . '" onclick="customField.Delete(\''. $prefix . '\', \'' . $key . '\');" />
					<input type="button" class="button" name="upd_' . $prefix . '[' . $key . ']" id="upd_' . $prefix . '[' . $key . ']" value="' . esc_attr(__( 'Update' )) . '" onclick="customField.Update(\''. $prefix . '\', \'' . $key . '\');" />
				</div>
				<div id="' . $prefix . '_loading-' . $key . '" class="meta_submit_loading"></div>
			</td>
			<td class="item-opt-value"><textarea name="' . $prefix . '[' . $key . '][value]" id="' . $prefix . '[' . $key . '][value]" class="optvalue">' . $value . '</textarea></td>
		</tr>';

		return $r;
	}
}

