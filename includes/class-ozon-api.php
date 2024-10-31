<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Ozon_Api {
	
	public $arrWooProducts = array();
	
	public function __construct() {
		require_once OZEP_ABSPATH . 'includes/class-ozon-product.php';
		require_once OZEP_ABSPATH . 'includes/class-ozon-product-variable-child.php';
	}
	
	public function get_woo_products_scop() {
		global $wpdb;
		
		$sql = "SELECT terms.slug, posts.* FROM {$wpdb->prefix}posts AS posts
			INNER JOIN {$wpdb->prefix}term_relationships AS term_relationships ON posts.ID = term_relationships.object_id
			INNER JOIN {$wpdb->prefix}term_taxonomy AS term_taxonomy ON term_relationships.term_taxonomy_id = term_taxonomy.term_taxonomy_id and term_taxonomy.taxonomy = 'product_type'
			INNER JOIN {$wpdb->prefix}terms AS terms ON term_taxonomy.term_id = terms.term_id
			INNER JOIN {$wpdb->prefix}postmeta AS postmeta ON (posts.ID = postmeta.post_id AND `meta_key` = '" . OZEP_META_NAME . "' AND `meta_value` = " . OZEP_STATUS['SELECTED'] . ") 
		WHERE posts.post_type='product' LIMIT 100";

		$this->arrWooProducts = $wpdb->get_results( $sql, ARRAY_A  );
	}
	
	public function get_OZON_request() {
		$items = array();
		
		$this->get_woo_products_scop();
		foreach ( $this->arrWooProducts as $woo_product ) {
			$product_id = $woo_product["ID"];
			
			//product is variable
			if( $woo_product["slug"] == 'variable' ) {
				$product = new WC_Product_Variable( $product_id );
				$child_products = $product->get_children();
				foreach ( $child_products as $woo_child_product ) {
					$items[] = new OZON_Product_Variable_Child( $product_id, $woo_child_product );
				}
			}
			else {
				$items[] = new OZON_Product( $product_id );
			}
		}
		
		return array( "items" => $items );
	}
	
	public function send_OZON_products() {
		$ozon_client_id = get_option( 'ozep_ozon_client_id' );
		$ozon_api_key = get_option( 'ozep_ozon_api_key' );
		
		if( !$ozon_client_id || !$ozon_api_key ) return;
		
		$request = $this->get_OZON_request();
		
		if( count( $this->arrWooProducts ) == 0 ) return;

		$this->write_to_request_log( wp_json_encode( $request, JSON_UNESCAPED_UNICODE ) );
		
		$url = "https://api-seller.ozon.ru/v2/product/import";
		
		$response = wp_remote_post( $url, array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
				'Client-Id' => $ozon_client_id,
				'Api-Key'	=> $ozon_api_key,
				'Content-Type' => 'application/json',
			),
			'body'        => wp_json_encode( $request, JSON_UNESCAPED_UNICODE ),
			'cookies'     => array()
			)
		);
		
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo wp_kses( "Something went wrong: $error_message" );
		}
		else {
			$res = (array)$response;
			//print_r($res);
			if( $res['response']['code'] == 200 ) {
				$this->set_woo_products_ozon_export_status();
			}
		}
		
		$this->write_to_response_log( wp_json_encode($response) );
		
	}
	
	public function set_woo_products_ozon_export_status() {
		foreach ( $this->arrWooProducts as $woo_product ) {
			$product_id = $woo_product["ID"];
			
			update_post_meta( $product_id, OZEP_META_NAME, OZEP_STATUS['EXPORTED'] );
		}
	}

	public function write_to_request_log( $_request ) {
		$file = plugin_dir_path( __DIR__ ) . 'logs/request.txt';

		file_put_contents($file, $_request);
	}
	
	public function write_to_response_log( $_response ) {
		$file = plugin_dir_path( __DIR__ ) . 'logs/response.txt';

		file_put_contents($file, $_response);
	}

	static public function get_OZON_directories() {
		$ozon_client_id = get_option( 'ozep_ozon_client_id' );
		$ozon_api_key = get_option( 'ozep_ozon_api_key' );
		
		if( !$ozon_client_id || !$ozon_api_key ) return;
		
		$url = "https://api-seller.ozon.ru/v2/category/tree";

		$response = wp_remote_post( $url, array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
				'Client-Id' => $ozon_client_id,
				'Api-Key'	=> $ozon_api_key,
				'Content-Type' => 'application/json',
			),
			'body'        => wp_json_encode( array(
				//'category_id' => '17036196',
				'language' => 'DEFAULT',
			) ),
			'cookies'     => array()
			)
		);

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo wp_kses( "Something went wrong: $error_message" );
		}
		$res = json_decode( $response['body'], true);
		
		$file = plugin_dir_path( __DIR__ ) . 'logs/categories.txt';
		file_put_contents($file, json_encode( $res['result'], JSON_UNESCAPED_UNICODE ));
		
		return $res['result'];
	}
	
	static public function get_OZON_attribute( $last_value_id = 0 ) {
		$ozon_client_id = get_option( 'ozep_ozon_client_id' );
		$ozon_api_key = get_option( 'ozep_ozon_api_key' );
		
		if( !$ozon_client_id || !$ozon_api_key ) return;
		
		$url = "https://api-seller.ozon.ru/v2/category/attribute/values";

		$response = wp_remote_post( $url, array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
				'Client-Id' => $ozon_client_id,
				'Api-Key'	=> $ozon_api_key,
				'Content-Type' => 'application/json',
			),
			'body'        => wp_json_encode( array(
				'attribute_id' => '85',
				'category_id' => '93446795',
				'language' => 'DEFAULT',
				'last_value_id' => $last_value_id,
				'limit' => '5000',
			) ),
			'cookies'     => array()
			)
		);

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo wp_kses( "Something went wrong: $error_message" );
		}
		$res = json_decode( $response['body'], true);
		
		$file = plugin_dir_path( __DIR__ ) . 'logs/attribute.txt';
		file_put_contents($file, json_encode( $res['result'], JSON_UNESCAPED_UNICODE ));
		
		return $res['result'];
	}
	
	static public function find_OZON_attribute( $name ) {
		$last_value_id = 0;
		$file = plugin_dir_path( __DIR__ ) . 'logs/attribute_id.txt';
		for( $i=0; $i < 20; $i++ ) {
			$arrRes = Ozon_Api::get_OZON_attribute( $last_value_id );
			if( !$arrRes ) {
				file_put_contents($file, $i);
				return;
			}
			foreach ( $arrRes as $res ) {
				if( $res["value"] == $name ) {
					file_put_contents($file, $res["id"]);
					return;
				}
				$last_value_id = $res["id"];
			}
		}
	}
}

?>