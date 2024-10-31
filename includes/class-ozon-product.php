<?php

class OZON_Product {
	public $category_id;
	public $depth;
	public $height;
	public $width;
	public $dimension_unit;
	public $weight;
	public $weight_unit;
	public $vat;
	public $currency_code;
	public $name;
	public $images;
	public $primary_image;
	public $offer_id;
	public $price;
	private $old_price;
	public $attributes;
	public $complex_attributes;
	
	protected $product_id;
	protected $product;
	
	public function __construct( $_product_id ) {
		$this->product_id = $_product_id;
		$this->set_product();
		$arr_prod_info = $this->get_product_info();
		
		$this->dimension_unit = get_option( 'woocommerce_dimension_unit' ) ? get_option( 'woocommerce_dimension_unit' ) : "mm";
		$this->weight_unit = get_option( 'woocommerce_weight_unit' ) ? get_option( 'woocommerce_weight_unit' ) : "g";
		$this->currency_code = get_option( 'woocommerce_currency' ) ? get_option( 'woocommerce_currency' ) : "RUB";
		$this->vat = "0";
		
		$this->set_category_id( $arr_prod_info["export_catogory_id"] );
		$this->set_name( $arr_prod_info["name"] );
		$this->set_images( $arr_prod_info["images"] );
		$this->set_primary_image( $arr_prod_info["primary_image"] );
		$this->set_offer_id( 'prod_' . $this->product_id );
		$this->set_depth( $arr_prod_info["depth"] );
		$this->set_height( $arr_prod_info["height"] );
		$this->set_width( $arr_prod_info["width"] );
		$this->set_weight( $arr_prod_info["weight"] );
		$this->set_price( $arr_prod_info["price"] );
		
		$this->add_additional_attributes();
		$this->add_product_attributes();
		$this->add_product_metas();
		$this->add_additional_info();
		$this->set_attributes( 9048, $arr_prod_info["name"], false ); //Название модели (для объединения в одну карточку)
		$this->set_attributes( 4191, $arr_prod_info["description"], false ); //Описание
		if( count( $arr_prod_info["video"] ) > 0 ) {
			foreach( $arr_prod_info["video"] as $video_url ) {
				$this->set_video( $video_url, "видео" );
			}
		}
	}
	
	protected function set_product( ) {
		$this->product =  new WC_Product( $this->product_id );
	}
	
	public function set_category_id( $_category_id ) {
		$this->category_id = (int)$_category_id; 
	}
	
	public function set_name( $_name ) {
		$this->name = $_name; 
	}
	
	public function set_images( $arr_img_url ) {
		$this->images = $arr_img_url;
	}
		
	public function set_primary_image( $_primary_image ) {
		$this->primary_image = $_primary_image; 
	}
	
	public function set_offer_id( $_offer_id ) {
		$this->offer_id = $_offer_id; 
	}
	
	public function set_depth( $_depth ) {
		$this->depth = $_depth; 
	}
	
	public function set_height( $_height ) {
		$this->height = $_height; 
	}
	
	public function set_width( $_width ) {
		$this->width = $_width; 
	}
	
	public function set_weight( $_weight ) {
		$this->weight = $_weight; 
	}
	
	public function set_price( $_price ) {
		$price_extra_proc = get_option( 'ozep_price_extra_proc' );
		if( $price_extra_proc ) {
			$_price = $_price + ( $_price * $price_extra_proc ) / 100;
		}
		$this->price = (string)$_price; 
	}
	
	public function set_old_price( $_old_price ) {
		$this->old_price = $_old_price; 
	}
	
	public function set_attributes( $_id, $_value, $_is_dictionary = false ) {
		$attribute = array();
		$attribute["id"] = $_id;
		$attribute["values"] = array();
		if( $_is_dictionary ) {
			if( is_array( $_value ) ) {
				foreach( $_value as $val ) {
					$attribute["values"][] = (object)[ "dictionary_value_id" => (int)$val ];
				}
			}
			else {
				$attribute["values"][] = (object)[ "dictionary_value_id" => (int)$_value ];
			}
		}
		else {
			$attribute["values"][] = (object)[ "value" => $_value ];
		}
		$this->attributes[] = (object)$attribute;
	}
	
	public function set_video( $_video_url, $_video_name ) {
		$complex_attribute = array();
		$complex_attribute["attributes"] = [
			(object)[
			  "complex_id" => 100001,
			  "id" => 21841,
			  "values" => [
				(object)[
				  "value" => $_video_url
				]
			  ]
			],
			(object)[
			  "complex_id" => 100001,
			  "id" => 21837,
			  "values" => [
				(object)[
				  "value" => $_video_name
				]
			  ]
			]
		];

		$this->complex_attributes[] = (object)$complex_attribute;
	}
		
	public function get_product_info() {
		$prod_info = array();
		$prod_info["name"] = $this->product->get_title();
		$prod_info["description"] = $this->get_description();
		
		$attachment_ids = $this->get_attachment_ids();
		$image_urls = array();
		foreach( $attachment_ids as $attachment_id ) {
		  $image_urls[] = wp_get_attachment_url( $attachment_id );
		}
		
		$prod_info["primary_image"] = $this->get_primary_image(); 
		$prod_info["images"] = array_slice( $image_urls, 0, 14 );// ozon allows only 14 images
		$prod_info["price"] = $this->product->get_regular_price();
		$prod_info["export_catogory_id"] =  $this->get_export_catogory_id();
		$prod_info["depth"] = $this->get_depth();
		$prod_info["height"] = $this->get_height();
		$prod_info["width"] = $this->get_width();
		$prod_info["weight"] = $this->get_weight();
		$prod_info["video"] = $this->get_product_video_review( $this->product_id );
		
		return $prod_info;
	}
	
	//add additional attributes needed for correct export to ozon catalog (brend, code, type)
	public function add_additional_attributes() {
		$export_catogory_id = $this->get_export_catogory_id();
		
		foreach ( Ozon_Export_Products_Main::$OZON_EXPORT_ADDITIONAL_ATTRIBUTES as $attr ) { 
			if( $attr['cat'] == $export_catogory_id ) {
				$this->set_attributes( $attr['id'], $attr['val'], $attr['is_dictionary'] );
			}
		}
	}
	
	//add attributes of woocommerce products (size, color, weight)
	public function add_product_attributes() {
		$export_catogory_id = $this->get_export_catogory_id();

		foreach ( Ozon_Export_Products_Main::$OZON_EXPORT_PRODUCT_ATTRIBUTES as $attr ) { 
			$values = array();
			if( $attr['cat'] == $export_catogory_id ) {
				$attribute_values   =  $this->get_product_attributes( $attr['woo_id'], $this->product_id );
				foreach ( $attribute_values as $attribute_value ) {
					$values[] = $attr['val'][$attribute_value];
				}
				
				$this->set_attributes( $attr['ozon_id'], $values, true );
			}
		}
	}
	
	//add meta of woocommerce products
	public function add_product_metas() {
		$export_catogory_id = $this->get_export_catogory_id();

		foreach ( Ozon_Export_Products_Main::$OZON_EXPORT_META_ATTRIBUTES as $attr ) { 
			$values = array();
			if( $attr['cat'] == $export_catogory_id ) {
				if( metadata_exists( 'post', $this->product_id, $attr['val'] ) ) {
					$value = get_post_meta( $this->product_id, $attr['val'], true );
				}
				
				$this->set_attributes( $attr['id'], $value, false );
			}
		}
	}
	
	//add additional data from a product description or title
	public function add_additional_info() {
		$export_catogory_id = $this->get_export_catogory_id();

		foreach ( Ozon_Export_Products_Main::$OZON_EXPORT_ADDITIONAL_DATA as $attr ) { 
			$values = array();
			if( $attr['cat'] == $export_catogory_id ) {
				$text = '';
				if( $attr['val'] == 'description' ) {
					$text = $this->product->get_description();
				}
				else if( $attr['val'] == 'title' ) {
					$text = $this->product->get_title();
				}
				$func = 'ozep_get_additional_data_' . $export_catogory_id ;
				if( function_exists( $func ) ) {
					$value = $func( $text );
				
					$this->set_attributes( $attr['id'], $this->sanitizeDescriptionText( $value ), false );
				}
			}
		}
	}
	
	protected function get_description() {
		return $this->sanitizeDescriptionText( $this->product->get_description() );
	}
	
	protected function get_attachment_ids() {
		return $this->product->get_gallery_image_ids();	}
	
	protected function get_export_catogory_id() {
		return (int)get_post_meta( $this->product_id, 'ozep_export_catogory_id', true );
	}

	protected function get_depth() {
		return (int)get_post_meta( $this->product_id, 'ozep_depth', true );		
	}
	
	protected function get_height() {
		return (int)get_post_meta( $this->product_id, 'ozep_height', true );		
	}

	protected function get_width() {
		return (int)get_post_meta( $this->product_id, 'ozep_width', true );
	}
	
	protected function get_weight() {
		return (int)get_post_meta( $this->product_id, 'ozep_weight', true );
	}
	
	protected function get_primary_image() {
		return get_the_post_thumbnail_url( $this->product_id, 'full' );
	}
	
	protected function get_product_attributes( $woo_id, $product_id ) {
		global $wpdb;
	
		$arrAttributes = array();
		$sql = "SELECT t.*, tt.* FROM wp_terms AS t 
			INNER JOIN wp_term_taxonomy AS tt ON tt.term_id = t.term_id 
			INNER JOIN wp_term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id 
			WHERE tt.taxonomy IN ('" . $woo_id . "') AND tr.object_id IN (" . $product_id . ");";

		$results = $wpdb->get_results( $sql, ARRAY_A );
			
		if( $results ) {
			foreach( $results as $res ){
				$arrAttributes[] = $res['slug'];
			}
		}
			
		return $arrAttributes;
	}
	
	public function get_product_video_review() {
		
		$str_video_review = get_post_meta( $this->product_id, 'custom_reviews', true );

		preg_match_all('/www\.youtube\.com[^\"]*/', $str_video_review, $matches);

		return( $matches[0] );
	}
	
	public function sanitizeDescriptionText( $text ) {
		//remove shortcodes like [wpcmtt]
		$text = preg_replace('/\[[^\]]*\]/', '', $text);
		
		//remove iframes
		$text = preg_replace('/\<iframe(.)*\<\/iframe\>/', '', $text);
		
		//remove div tags
		$text = preg_replace('/\<div[^\>]*\>/', '', $text);
		$text = preg_replace('/\<\/div\>/', '', $text);
		
		//remove p tags
		$text = preg_replace('/\<p[^\>]*\>/', '', $text);
		$text = preg_replace('/\<\/p\>/', '', $text);
		
		//remove h2 tags
		$text = preg_replace('/\<h2[^\>]*\>/', '', $text);
		$text = preg_replace('/\<\/h2\>/', '', $text);

		//remove a href tags
		$text = preg_replace('/\<a[^\>]*\>/', '', $text);
		$text = preg_replace('/\<\/a\>/', '', $text);

		return $text;
	}
}

?>