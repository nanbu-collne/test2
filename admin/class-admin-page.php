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
class WC2_Admin_Page {

	public $action_status, $action_message;
	public $error_message = array();

	/**
	 * Unique identifier for Top-level menu.
	 *
	 * @since    1.0.0
	 *
	 * @var      array
	 */
	protected $menu_slug = array();

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/*******************************
	 * Constructor
	 *
	 * @since     1.0.0
	 *******************************/
	public function __construct() {
		$this->init();
		$plugin = Welcart2::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();
		$admin = WC2_Admin::get_instance();
		$this->menu_slug = $admin->get_toplevel_menu_slug();
		$this->clear_action_status();

		/*******************************************/

		add_action( 'admin_head', array( $this, 'add_admin_head' ) );

		// Load admin style sheet and JavaScript.
		add_action( 'admin_print_styles', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		//スクリーン自体の表示・非表示
		add_filter( 'screen_options_show_screen', array( $this, 'admin_show_screen' ), 10, 2 );

		//スクリーンの表示件数取得
		add_filter( 'set-screen-option', array( $this, 'admin_set_screen_options' ), 10, 3 );

		add_filter( 'contextual_help', array( $this, 'admin_help_setting' ), 900, 3 );
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_page_scripts' ) );
		add_action( 'admin_footer', array( $this, 'clear_action_status' ) );
	}

	/***********************************
	 * Initial setting.
	 *
	 * @since     1.0.0
	 ***********************************/
	protected function init() {

	}

	/***********************************
	 * Add a tab to the Contextual Help menu in an admin page.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function admin_help_setting( $help, $screen_id, $screen ) {

	}

	/***********************************
	 * Register and enqueue admin-specific header processing.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function add_admin_head() {
		//if( $this->action_status == 'edit' || $this->action_status == 'editpost' ) {
			add_thickbox();
		//}
	}

	/***********************************
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function enqueue_admin_styles() {
		if( !isset( $this->plugin_screen_hook_suffix ) )
			return;

		global $wp_scripts;
		$screen = get_current_screen();

		if( $this->plugin_screen_hook_suffix == $screen->id || 'edit-item' == $screen->id ) {
			$ui = $wp_scripts->query( 'jquery-ui-core' );
			//$url = "//ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/redmond/jquery-ui.min.css";
			$url = "//ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
			wp_enqueue_style( 'jquery-ui-smoothness', $url, false, null );
		}
	}

	/***********************************
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 ***********************************/
	public function enqueue_admin_scripts() {
		if( !isset( $this->plugin_screen_hook_suffix ) )
			return;

		$screen = get_current_screen();

		if( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( 'jquery-color' );
			wp_enqueue_script( 'jquery-ui-tabs' );
			wp_enqueue_script( 'jquery-ui-dialog' );
		}
	}

	/***********************************
	 * 表示オプションの表示制御
	 * @since    1.0.0
	 *
	 * NOTE:  $show_screen = 1は表示オプションを表示、0は非表示
	 ***********************************/
	public function admin_show_screen( $show_screen, $screen ) {
//		if( !isset( $screen->id ) || false === strpos( $screen->id, 'toplevel_page_*****' ) )
//			return 0;
//
		return $show_screen;
	}

	/***********************************
	 * リストの表示件数取得
	 * @since    1.0.0
	 *
	 * NOTE:  screen_options_show_screen にフックして、保存されたリストの表示件数を適用
	 ***********************************/
	public function admin_set_screen_options( $result, $option, $value ) {
//		$screens = array( self::$per_page_slug );
//		if( in_array( $option, $screens ) )
//			$result = $value;
//
		return $result;
	}

	/***********************************
	 * Add a sub menu page.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function add_admin_menu() {

	}

	/***********************************
	 * Setting of action status.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function set_action_status( $status, $message ) {
		$this->action_status = $status;
		$this->action_message = $message;
	}

	/***********************************
	 * Initialization of action status.
	 *
	 * @since    1.0.0
	 ***********************************/
	public function clear_action_status() {
		$this->action_status = 'none';
		$this->action_message = '';
	}

	/*******************************
	 * The function to be called to output the common script source for this page.
	 *
	 * @since    1.0.0
	 *******************************/
	public function admin_scripts() {
		if( !isset( $this->plugin_screen_hook_suffix ) )
			return;

		$screen = get_current_screen();
		if( $this->plugin_screen_hook_suffix != $screen->id )
			return;
?>
<script type="text/javascript">
jQuery(document).ready( function($) {
<?php if( $this->action_status == 'success' ): ?>
	$("#anibox").animate( { backgroundColor: "#ECFFFF" }, 2000 );
<?php elseif( $this->action_status == 'caution' ): ?>
	$("#anibox").animate( { backgroundColor: "#FFF5CE" }, 2000 );
<?php elseif( $this->action_status == 'error' ): ?>
	$("#anibox").animate( { backgroundColor: "#FFE6E6" }, 2000 );
<?php endif; ?>
	$(".wc2tabs").css("display", "block");
<?php
	if( isset($_GET['page']) && ('wc2_order' == $_GET['page'] || 'wc2_member' == $_GET['page']) ):
		if( isset($_GET['action']) && ('edit' == $_GET['action'] || 'new' == $_GET['action']) ):
			switch( $_GET['page'] ) {
			case 'wc2_order':
				$admin_page = 'order';
				break;
			case 'wc2_member':
				$admin_page = 'member';
				break;
			}
?>
	$(document).on( "click", ".search-zipcode", function() {
		var idname = $(this).attr("id");
		var ids = idname.split("-");
		var id = ids[2];
		AjaxZip3.zip2addr( id+"[zipcode]", "", id+"[pref]", id+"[address1]" );
	});
<?php
		endif;
	endif;
?>
});
</script>
<?php
	}

	/*******************************
	 * The function to be called to output the script source for this page.
	 *
	 * @since    1.0.0
	 *******************************/
	public function admin_page_scripts() {

	}
}
