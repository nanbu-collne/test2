<?php
/**
 * Welcart2.
 *
 * @package   Welcart2_Public
 * @author    Collne Inc. <author@collne.com>
 * @license   GPL-2.0+
 * @link      http://www.welcart2.com
 * @copyright 2014 Collne Inc.
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-plugin-name-admin.php`
 *
 * @TODO: Rename this class to a proper name for your plugin.
 *
 * @package Plugin_Name
 * @author  Your Name <email@example.com>
 */
class WC2_Public {


	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	public $order, $cart, $member, $page;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		global $wc2_order, $wc2_cart, $wc2_member, $wc2_options;

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

		//session_name( $wc2_options['session_name'] );
		$session_name = wc2_get_option( 'session_name' );
		session_name( $session_name );
		session_set_cookie_params ( 0, COOKIEPATH, $_SERVER['SERVER_NAME'], false, true );
		if ( !session_id() ) {
			session_start();
		}

		$this->page = new WC2_Page();

		$this->control();
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function control() {
		//if( !isset($_REQUEST['wcnonce']) )
		//	return;

		$action = isset( $_REQUEST['wcaction'] ) ? $_REQUEST['wcaction'] : '';

		do_action( 'wc2_action_control_pre', $action );

		switch( $action ) {
			case 'add2cart':
				$this->page->add2cart();
				break;

			case 'update_cart':
				$this->page->update_cart();
				break;

			case 'remove_cart':
				$this->page->remove_cart();
				break;

			case 'checkout':
				$this->page->checkout();
				break;

			case 'customer_login':
				$this->page->customer_login();
				break;

			case 'customer_process':
				$this->page->customer_process();
				break;

			case 'delivery_process':
				$this->page->delivery_process();
				break;

			case 'purchase_process':
				$this->page->purchase_process();
				break;

			case 'login':
				$this->page->member_login();
				break;

			case 'logout':
				$this->page->member_logout();
				break;

			case 'register_member':
				$this->page->register_member();
				break;

			case 'update_member':
				$this->page->update_member();
				break;

			case 'delete_member':
				$this->page->delete_member();
				break;

			case 'change_password':
				$this->page->change_password();
				break;

			case 'lost_password':
				$this->page->lost_password();
				break;
		}

		do_action( 'wc2_action_control', $action );
	}
}
