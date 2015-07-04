<?php
/**
 * Welcart2.
 *
 * @package   WC2_Admin
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
class WC2_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

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
//	protected $plugin_screen_hook_suffix = null;
	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 * @TODO:
		 *
		 * - Rename "Plugin_Name" to the name of your initial plugin class
		 *
		 */
		$plugin = Welcart2::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();
		$this->version = Welcart2::VERSION;

		// Set the Top-level menu slug.
		$this->menu_slug['management'] = $this->plugin_slug . '_order';
		$this->menu_slug['setting'] = $this->plugin_slug . '_setting';

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'add_item_submenu' ) );

		// Add an action link pointing to the options page.
//		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
//		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
	}


	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Attempts to return the Top-level menu slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Top-level menu slug variable.
	 */
	public function get_toplevel_menu_slug() {

		return $this->menu_slug;

	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @TODO:
	 *
	 * - Rename "Plugin_Name" to the name your plugin
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

//		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
//			return;
//		}

//		$screen = get_current_screen();
//		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Welcart2::VERSION );
//		}
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @TODO:
	 *
	 * - Rename "Plugin_Name" to the name your plugin
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */

	public function enqueue_admin_scripts() {
		$screen = get_current_screen();

		if ( false === strpos( $screen->id, $this->menu_slug['management']) 
			&& false === strpos( $screen->id, $this->menu_slug['setting']) 
			&& 'item_page_import' != $screen->id ) {

			return;
		}

		global $wp_version;
		if( version_compare( $wp_version, '3.4', '>=' ) ) {
			$theme_ob = wp_get_theme();
			$theme['Name'] = esc_js( $theme_ob->get('Name') );
			$theme['Version'] = esc_js( $theme_ob->get('Version') );
		} else {
			$theme = get_theme_data( get_stylesheet_directory().'/style.css' );
		}
?>
<script type="text/javascript">
/* <![CDATA[ */
var WC2L10n = {
	<?php echo apply_filters( 'wc2_filter_admin_WC2L10n', NULL ); ?>
	"ajax_url": "<?php echo admin_url( 'admin-ajax.php' ); ?>",
	"version": "<?php echo Welcart2::VERSION; ?>",
	"locale": "<?php echo get_locale(); ?>",
	"theme": "<?php echo $theme['Name'] . '-' . $theme['Version']; ?>",
	"now_loading": "<?php _e('Now loading ...', 'wc2'); ?>",
	"loading_gif": "<?php echo WC2_PLUGIN_URL ?>/common/assets/images/loading.gif",
	"success_info": "<?php echo WC2_PLUGIN_URL; ?>/common/assets/images/list_message_success.gif",
	"error_info": "<?php echo WC2_PLUGIN_URL; ?>/common/assets/images/list_message_error.gif"
};
</script>
<?php
		wp_enqueue_script( 'jquery' );
		//wp_enqueue_script( 'jquery-ui-progressbar' );
		//wp_enqueue_script( $this->plugin_slug . '-ui-progressbar', plugins_url( 'assets/js/progressbar.min.js', __FILE__ ), array( 'jquery' ), Welcart2::VERSION);
		wp_enqueue_script( $this->plugin_slug . '-cookie-script', plugins_url( 'assets/js/jquery.cookie.js', __FILE__ ), array( 'jquery' ), Welcart2::VERSION );
		wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array(), Welcart2::VERSION, true );
	}


	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		global $admin_page_hooks;
		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 * @TODO:
		 *
		 * - Change 'Page Title' to the title of your plugin admin page
		 * - Change 'Menu Text' to the text for menu item for the plugin settings page
		 * - Change 'manage_options' to the capability you see fit
		 *   For reference: http://codex.wordpress.org/Roles_and_Capabilities
		 */
//		$this->plugin_screen_hook_suffix = add_options_page(
//			__( 'Page Title', $this->plugin_slug ),
//			__( 'Menu Text', $this->plugin_slug ),
//			'manage_options',
//			$this->plugin_slug,
//			array( $this, 'display_plugin_admin_page' )
//		);
		//add_action( 'admin_head', array( $this, 'admin_head' ) );
		//add_action( 'admin_print_footer_scripts', array( $this, 'admin_prodauct_footer' ) );

		$management_slug = $this->menu_slug['management'];
		$setting_slug = $this->menu_slug['setting'];
		$menu_slug_management = $this->menu_slug['management'];
		$menu_slug_setting = $this->menu_slug['setting'];
		add_object_page( 'ショップ管理', 'ショップ管理', 'edit_pages', $menu_slug_management, array( $this, 'display_plugin_admin_page' ) );
		add_object_page( 'ショップ設定', 'ショップ設定', 'create_users', $menu_slug_setting, array( $this, 'display_plugin_admin_page' ) );

		$admin_page_hooks[$menu_slug_management] = 'wc2_management';
		$admin_page_hooks[$menu_slug_setting] = 'wc2_setting';
	}

	public function add_item_submenu() {
		add_submenu_page( 'edit.php?post_type=item', __( '商品一括登録', 'wc2' ), __( '商品一括登録', 'wc2' ), 'level_8', 'import', array( $this, 'import' ) );
		do_action( 'wc2_action_item_submenu' );
	}

	public function import() {
		include_once( WC2_PLUGIN_DIR.'/admin/views/import-item.php' );
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
	//	include_once( 'views/admin.php' );
	}


	/**
	 * NOTE:     Actions are points in the execution of a page or process
	 *           lifecycle that WordPress fires.
	 *
	 *           Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}

	/**
	 * NOTE:     Filters are points of execution in which WordPress modifies data
	 *           before saving it or sending it to the browser.
	 *
	 *           Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

	function admin_head() {

	}

	function admin_prodauct_footer(){

	}
}
