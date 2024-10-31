<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Ozep_Cron {

	const IVENT_NAME = 'ozep_export_product_daily';
	
	public function __construct() {
		add_action( self::IVENT_NAME, array( $this, 'do_ozon_export' ) );

		// for fast testing
		//require_once OZEP_ABSPATH . 'includes/class-ozon-api.php';
		//$oz_api = new Ozon_Api();
		//$oz_api->send_OZON_products();
	}
		
	public function do_ozon_export(){
		require_once OZEP_ABSPATH . 'includes/class-ozon-api.php';
		$oz_api = new Ozon_Api();
		$oz_api->send_OZON_products();
	}
	
	public static function add_schedule_event() {
		wp_clear_scheduled_hook( self::IVENT_NAME );
		wp_schedule_event( time(), 'daily', self::IVENT_NAME);		
	}
	
	public static function deactivate_schedule_event(){
		wp_clear_scheduled_hook( self::IVENT_NAME );
	}
}

?>