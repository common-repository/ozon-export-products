<?php

class OZON_Product_Variable_Child extends OZON_Product{
	
	protected $parent_product_id;
	protected $parent_product;
	
	public function __construct( $_parent_product_id, $_product_id )
	{
		$this->product_id = $_product_id;
		$this->parent_product_id = $_parent_product_id;

		parent::__construct( $this->product_id );
		
		$this->set_attributes( 8292, $this->parent_product_id . date("Ymd"), false ); //Объединить на одной карточке
	}
	
	protected function set_product( ) {
		$this->product =  new WC_Product_Variation( $this->product_id );
		$this->parent_product = new WC_Product_Variable( $this->parent_product_id );
	}
	
	protected function get_description() {
		$description = parent::get_description();
		if( $description == '' ) {
			$description = $this->sanitizeDescriptionText( $this->parent_product->get_description() );
		}
		return $description;
	}
	
	protected function get_attachment_ids() {
		return $this->parent_product->get_gallery_image_ids();;
	}
	
	protected function get_export_catogory_id() {
		return (int)get_post_meta( $this->parent_product_id, 'ozep_export_catogory_id', true );
	}

	protected function get_depth() {
		return (int)get_post_meta( $this->parent_product_id, 'ozep_depth', true );		
	}
	
	protected function get_height() {
		return (int)get_post_meta( $this->parent_product_id, 'ozep_height', true );		
	}

	protected function get_width() {
		return (int)get_post_meta( $this->parent_product_id, 'ozep_width', true );
	}
	
	protected function get_weight() {
		return (int)get_post_meta( $this->parent_product_id, 'ozep_weight', true );
	}
	
	protected function get_primary_image() {
		$url = get_the_post_thumbnail_url( $this->product_id, 'full' );
		if( !$url ) {
			$url = get_the_post_thumbnail_url( $this->parent_product_id, 'full' );
		}
		return $url;
	}
	
	protected function get_product_attributes( $woo_id, $product_id ) {
		$arrAttributes = array();
		$attributeValue = get_post_meta( $product_id, 'attribute_'.$woo_id, true );
		if( $attributeValue == '' ) {
			$arrAttributes = parent::get_product_attributes( $woo_id, $this->parent_product_id );
		}
		else {
			 $arrAttributes[] = $attributeValue;
		}
					
		return $arrAttributes;
	}
	
	//add meta of woocommerce products
	public function add_product_metas() {
		$export_catogory_id = $this->get_export_catogory_id();

		foreach ( Ozon_Export_Products_Main::$OZON_EXPORT_META_ATTRIBUTES as $attr ) { 
			$values = array();
			if( $attr['cat'] == $export_catogory_id ) {
				if( metadata_exists( 'post', $this->parent_product_id, $attr['val'] ) ) {
					$value = get_post_meta( $this->parent_product_id, $attr['val'], true );
				}
				
				$this->set_attributes( $attr['id'], $value, false );
			}
		}
	}
}

?>