<?php

function convert_weight( $val ) {
	$unit = get_option( 'woocommerce_weight_unit' );
	$g_val = 0;
	
	switch ($unit) {
		case "kg":
			$g_val = $val*100;
			break;
		case "g":
			$g_val = $val;
			break;
		case "lbs":
			$g_val = $val*453.59;
			break;
		case "oz":
			$g_val = $val*28.35;
			break;
	}
	return $g_val;
}

function convert_dimension( $val ) {
	$unit = get_option( 'woocommerce_dimension_unit' );
	$mm_val = 0;
	
	switch ($unit) {
		case "m":
			$mm_val = $val*1000;
			break;
		case "cm":
			$mm_val = $val*10;
			break;
		case "mm":
			$mm_val = $val;
			break;
		case "in":
			$mm_val = $val*25.4;
			break;
		case "yd":
			$mm_val = $val*914.4;
			break;
	}
	return $mm_val;
}
?>