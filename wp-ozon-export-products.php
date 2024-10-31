<?php
/**
 * Plugin Name:       Ozon Export Products
 * Description:       Плагин экспортирует товары в OZON маркетплейс. Для работы требуется установленный Woocommerce плагин.
 * Version:           1.2.3
 * Author:            LunaSite
 * Author URI:        https://www.woocommerce.com/
 * Requires at least: 3.0.0
 * Tested up to:      4.4.2
 *
 * @package Ozon_Export_Products
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Ozon_Export_Products' ) ) {
	/**
	 * Main Ozon_Export_Products Class
	 *
	 * @class Ozon_Export_Products
	 * @version	1.0.0
	 * @since 1.0.0
	 * @package	Ozon_Export_Products
	 */
	final class Ozon_Export_Products {

		/**
		 * Set up the plugin
		 */
		public function __construct() {
			$this->includes();
			
			// check if woocommerce plugin is active
			if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				if( is_admin() ) {
					new OZEP_Admin_Menu();
					new Ozon_Export_Products_Main();
				}
				new Ozep_Cron();
			}
			
			add_action( 'init', array( $this, 'setup' ), -1 );
			add_action( 'admin_enqueue_scripts', array( $this, 'ozep_enqueue_admin_scripts' ) );
		}

		public static function constants() {
			define( 'OZEP__FILE__', __FILE__ );
			define( 'OZEP_ABSPATH', dirname( OZEP__FILE__ ) . '/' );
			define( 'OZEP_STATUS', [ 'NEW' => 0, 'SELECTED' => 1, 'EXPORTED' => 2 ] );	//0 - new; 1 - add to scope for export; 2 - is added to OZON
			define( 'OZEP_META_NAME', 'ozep_status' );
		}
		/**
		 * Setup all the things
		 */
		public function setup() {
		}
		
		public function ozep_enqueue_admin_scripts() {		
			wp_enqueue_style( 'ozep-admin-css', plugins_url( '/assets/css/ozep_admin.css', __FILE__ ) );
		}
		public function includes() {
			require_once OZEP_ABSPATH . 'wp-ozon-export-products-main.php';
			$this->include_categories();
			require_once OZEP_ABSPATH . 'includes/ozep-admin-menu.php';
			require_once OZEP_ABSPATH . 'utils/functions.php';
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		public function include_categories() {
			foreach ( glob( OZEP_ABSPATH . "categories/*.php" ) as $filename ) {
					include $filename;
				}
		}

	} // End Class

	/**
	 * Initialise the plugin
	 */
	function ozon_export_products_main() {
		new Ozon_Export_Products();
	}
		
	add_action( 'plugins_loaded', 'ozon_export_products_main' );
	
	Ozon_Export_Products::constants();
	require_once OZEP_ABSPATH . '/includes/class-ozep-cron.php';
	register_activation_hook( OZEP__FILE__, 'Ozep_Cron::add_schedule_event' );
	register_deactivation_hook( OZEP__FILE__, 'Ozep_Cron::deactivate_schedule_event' );
}
?>