<?php
/**
 * The WordPress Plugin Welcart2 for e-Commerce.
 *
 * We will provide the e-commerce tools to you. There are various 
 * operational forms to EC site. So only ease of customization is 
 * important for developers. We have developed it in mind that 
 * it will be convenient in many customizations.
 *
 * @package   Welcart2
 * @author    Collne Inc. <development@collne.com>
 * @license   GPL-2.0+
 * @link      http://www.collne.com/
 * @copyright 2015 Collne Inc.
 *
 * @wordpress-plugin
 * Plugin Name:       Welcart2
 * Plugin URI:        @TODO
 * Description:       @TODO
 * Version:           1.0.1507055
 * Author:            Collne Inc.
 * Author URI:        http://www.collne.com/
 * Text Domain:       plugin-name-locale
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * WordPress-Plugin-Boilerplate: v2.6.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WC2', 'Welcart2' );
define( 'WC2_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)) );
define( 'WC2_PLUGIN_URL', WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) );
define( 'WC2_PLUGIN_FOLDER', dirname(plugin_basename(__FILE__)) );
define( 'WC2_PLUGIN_BASENAME', plugin_basename(__FILE__) );
define( 'WC2_ADMIN_URL', admin_url('admin.php') );

/*----------------------------------------------------------------------------*
 * Loads the plugin's translated strings.
 *----------------------------------------------------------------------------*/
load_plugin_textdomain( 'wc2', WC2_PLUGIN_DIR.'/languages', WC2_PLUGIN_FOLDER.'/languages' );

/*----------------------------------------------------------------------------*
 * Common Functionality
 *----------------------------------------------------------------------------*/

/*
 * This block has a front-end and back-end common module.
 */
require_once( plugin_dir_path( __FILE__ ) . 'common/includes/default-constants.php' );
require_once( plugin_dir_path( __FILE__ ) . 'common/includes/utilities.php' );
require_once( plugin_dir_path( __FILE__ ) . 'common/includes/class-options.php' );
require_once( plugin_dir_path( __FILE__ ) . 'common/includes/initial.php' );
require_once( plugin_dir_path( __FILE__ ) . 'common/includes/functions.php' );
require_once( plugin_dir_path( __FILE__ ) . 'common/includes/template_func.php' );
require_once( plugin_dir_path( __FILE__ ) . 'common/includes/calendar-functions.php' );
require_once( plugin_dir_path( __FILE__ ) . 'common/class-welcart2.php' );
require_once( plugin_dir_path( __FILE__ ) . 'common/class-db-log.php' );
require_once( plugin_dir_path( __FILE__ ) . 'common/class-db-access.php' );
require_once( plugin_dir_path( __FILE__ ) . 'common/class-db-item.php' );
require_once( plugin_dir_path( __FILE__ ) . 'common/class-db-member.php' );
require_once( plugin_dir_path( __FILE__ ) . 'common/class-db-order.php' );
require_once( plugin_dir_path( __FILE__ ) . 'common/class-mail.php' );
require_once( plugin_dir_path( __FILE__ ) . 'common/class-member_func.php' );


/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 * @TODO:
 *
 * - replace Plugin_Name with the name of the class defined in
 *   `class-plugin-name.php`
 */
register_activation_hook( __FILE__, array( 'Welcart2', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Welcart2', 'deactivate' ) );
register_activation_hook( __FILE__, array( 'Welcart2', 'set_initial' ) );

/*
 * @TODO:
 *
 * - replace Plugin_Name with the name of the class defined in
 *   `class-plugin-name.php`
 */
add_action( 'after_setup_theme', 'wc2_initial_setting' );
add_action( 'plugins_loaded', array( 'Welcart2', 'get_instance' ), 8 );
add_action( 'plugins_loaded', array( 'WC2_DB_Item', 'get_instance' ) );

/*
/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * - replace `class-plugin-name.php` with the name of the plugin's class file
 *
 */
if ( !is_admin() ) {

	require_once( plugin_dir_path( __FILE__ ) . 'public/class-cart.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'public/class-entry.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'public/class-member.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'public/class-page.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'public/class-wc2-public.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'public/includes/functions.php' );

	add_action( 'init', array( 'WC2_Cart', 'get_instance' ), 9 );
	add_action( 'init', array( 'WC2_Entry', 'get_instance' ), 9 );
	add_action( 'init', array( 'WC2_Member_Front', 'get_instance' ), 9 );
	add_action( 'init', array( 'WC2_Public', 'get_instance' ), 9 );

}

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * - replace `class-plugin-name-admin.php` with the name of the plugin's admin file
 * - replace Plugin_Name_Admin with the name of the class defined in
 *   `class-plugin-name-admin.php`
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
//if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
if ( is_admin() ) {
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-wc2-admin.php' );
	add_action( 'plugins_loaded', array( 'WC2_Admin', 'get_instance' ), 9 );

	require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/functions.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-admin-page.php' );

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-admin-order.php' );
	add_action( 'plugins_loaded', array( 'WC2_Order', 'get_instance' ));

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-admin-member.php' );
	add_action( 'plugins_loaded', array( 'WC2_Member', 'get_instance' ) );

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-admin-general.php' );
	add_action( 'plugins_loaded', array( 'WC2_General_Setting', 'get_instance' ) );

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-admin-phrase.php' );
	add_action( 'plugins_loaded', array( 'WC2_Phrase_Setting', 'get_instance' ) );

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-admin-payment.php' );
	add_action( 'plugins_loaded', array( 'WC2_Payment_Setting', 'get_instance' ) );

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-admin-delivery.php' );
	add_action( 'plugins_loaded', array( 'WC2_Delivery_Setting', 'get_instance' ) );

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-admin-system.php' );
	add_action( 'plugins_loaded', array( 'WC2_System_Setting', 'get_instance' ) );

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-admin-customfield.php' );
	add_action( 'plugins_loaded', array( 'WC2_CustomField', 'get_instance' ) );

	require_once( plugin_dir_path( __FILE__ ) . 'admin/views/edit-item.php' );

}




