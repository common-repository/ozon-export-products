<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Ozon_Export_Products_Main {

	public $ozep_status = OZEP_STATUS['NEW'] ;
	static $OZON_EXPORT_CATEGORIES = array();
	static $OZON_EXPORT_ADDITIONAL_ATTRIBUTES = array();
	static $OZON_EXPORT_PRODUCT_ATTRIBUTES = array();
	static $OZON_EXPORT_META_ATTRIBUTES = array();
	static $OZON_EXPORT_ADDITIONAL_DATA = array();
	

	public function __construct() {
		add_action( 'init', array( $this, 'setup' ), -1 );
	}
	
	public function setup() {
		// add ozep block to admin woo product page
		$this->add_ozep_block();
		
		//save settings on admin menu page
		$this->save_ozep_settings();
		
		//require_once OZEP_ABSPATH . 'includes/class-ozon-api.php';
		//$directories = Ozon_Api::get_OZON_directories();
		//$attribute = Ozon_Api::get_OZON_attribute();
		//Ozon_Api::find_OZON_attribute( 'Нет бренда' );

		
		//remove settings after plugin deactivation
		//register_deactivation_hook( OZEP__FILE__, array( $this, 'ozep_remove_plugin_options' ) );
	}
	
	public function add_ozep_block(){
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );
	}

	public function register_meta_boxes() {
		add_meta_box( 'ozep_meta_box', __( 'Ozon экспорт', 'ozep' ),  array( $this, 'display_admin_block'), 'product', 'side', 'high' );
	}

	public function display_admin_block( $post ) {
		$status = $this->get_ozep_meta( OZEP_META_NAME );
		$export_catogory_id = $this->get_ozep_meta( 'ozep_export_catogory_id' );
		$export_catogory_id_default = get_option( 'ozep_export_catogory_id_default' );
		$catogory_id = $export_catogory_id ? $export_catogory_id : $export_catogory_id_default;
		$height = $this->get_ozep_meta( 'ozep_height' );
		$width = $this->get_ozep_meta( 'ozep_width' );
		$depth = $this->get_ozep_meta( 'ozep_depth' );
		$dimension_unit = esc_html( __( get_option( 'woocommerce_dimension_unit' ), 'woocommerce' ) );
		$weight = $this->get_ozep_meta( 'ozep_weight' );
		$weight_unit = esc_html( __( get_option( 'woocommerce_weight_unit', 'kg' ), 'woocommerce' ) );
		//get woocommerce delivery params
		if( $height == '' || $height == 0 ) $height = $this->get_ozep_meta( '_height' );
		if( $width == '' || $width == 0 ) $width = $this->get_ozep_meta( '_width' );
		if( $depth == '' || $depth == 0 ) $depth = $this->get_ozep_meta( '_length' );
		if( $weight == '' || $weight == 0 ) $weight = $this->get_ozep_meta( '_weight' );
		
		include OZEP_ABSPATH . 'templates/ozep_admin_block.php';
	}


	public function save_meta_box( $post_id ) {
		$nonce = sanitize_text_field( $_REQUEST['ozep_wpnonce'] );
		$add_prod = sanitize_text_field( $_REQUEST['ozep_add_product'] ) ? OZEP_STATUS['SELECTED'] : OZEP_STATUS['NEW'];
		$export_catogory_id = sanitize_text_field( $_REQUEST['ozep_export_catogory_id'] );
		$height = sanitize_text_field( $_REQUEST['ozep_height'] );
		$width = sanitize_text_field( $_REQUEST['ozep_width'] );
		$depth = sanitize_text_field( $_REQUEST['ozep_depth'] );
		$weight = sanitize_text_field( $_REQUEST['ozep_weight'] );
		if ( wp_verify_nonce( $nonce, 'ozep-nonce' ) ) {
			update_post_meta( $post_id, OZEP_META_NAME, $add_prod );
			update_post_meta( $post_id, "ozep_export_catogory_id", (int)$export_catogory_id );
			update_post_meta( $post_id, "ozep_height", (int)$height );
			update_post_meta( $post_id, "ozep_width", (int)$width );
			update_post_meta( $post_id, "ozep_depth", (int)$depth );
			update_post_meta( $post_id, "ozep_weight", (int)$weight );
		}
	}

	public function get_ozep_meta( $meta_name) {
		$post_id = get_the_ID();
		if( metadata_exists( 'post', $post_id, $meta_name ) ) {
			return get_post_meta( $post_id, $meta_name, true );
		}
		return '';
	}
	
	public function save_ozep_settings(){
		if( $_POST['action'] == 'ozep_save_settings' ) {
			$nonce = sanitize_text_field( $_REQUEST['ozep_settings_wpnonce'] );
			
			$ozon_client_id = sanitize_text_field( $_POST['ozep_ozon_client_id'] );
			$ozon_api_key = sanitize_text_field( $_POST['ozep_ozon_api_key'] );
			$ozon_catogory_id_default = sanitize_text_field( $_POST['ozep_export_catogory_id_default'] );
			$price_extra_proc = sanitize_text_field( $_POST['ozep_price_extra_proc'] );
			
			if ( wp_verify_nonce( $nonce, 'ozep-settings-nonce' ) ) {
				update_option( 'ozep_ozon_client_id', $ozon_client_id );
				update_option( 'ozep_ozon_api_key', $ozon_api_key );
				update_option( 'ozep_export_catogory_id_default', $ozon_catogory_id_default );
				update_option( 'ozep_price_extra_proc', $price_extra_proc );
			}
		}
	}
	
	public function ozep_remove_plugin_options() {
		$settingOptions = array( 'ozep_ozon_client_id', 'ozep_ozon_api_key', 'ozep_export_catogory_id_default', 'ozep_price_extra_proc' ); // etc

		// Clear up our settings
		foreach ( $settingOptions as $settingName ) {
			delete_option( $settingName );
		}
	}
}
?>