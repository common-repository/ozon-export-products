<?php

class OZEP_Admin_Menu {
	
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu_pages' ) );
	}
	
	function menu_pages() {
		// Add the top-level admin menu
		$page_title = 'Ozon Export Products';
		$menu_title = 'Ozon Export';
		$menu_slug  = 'ozep-settings';
		$capability = 'manage_options';
		$function   = array( $this, 'ozep_settings_page' );
		
		add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, null, 50);
		
		$sub_menu_title = 'Settings';
		add_submenu_page( $menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, $function );
	}
	
	public function ozep_settings_page(){
		$ozon_client_id = get_option( 'ozep_ozon_client_id' );
		$ozon_api_key = get_option( 'ozep_ozon_api_key' );
		$export_catogory_id_default = get_option( 'ozep_export_catogory_id_default' );
		$price_extra_proc = get_option( 'ozep_price_extra_proc' );

		include OZEP_ABSPATH . 'templates/ozep_admin_settings_page.php';
	}
}

?>