	<?php 
		$nonce = wp_create_nonce( 'ozep-nonce' );
		if ( $status == '') {
			$status = OZEP_STATUS['NEW'];
		}
		if ( $status == OZEP_STATUS['EXPORTED'] ) {
	?>
			Товар экспортирован &#9989;
		
	<?php	}
	?>

		<input type='hidden' name='ozep_wpnonce' value='<?php echo esc_html( $nonce ); ?>'/>
		<table class='form-table' border='0'>
			<tr valign='top'>
				<td scope='row'>
					Добавить товар в набор
				</td>
				<td>
					<input type="checkbox" name="ozep_add_product" <?php if( $status == OZEP_STATUS['SELECTED'] ) echo "checked" ?> >
				</td>
			</tr>
			<tr valign='top'>
				<td scope='row' colspan='2'>
					Категория ozon
					<select name='ozep_export_catogory_id' class="ozep-export-catogory">
						<?php
						
							foreach ( Ozon_Export_Products_Main::$OZON_EXPORT_CATEGORIES as $category ) {
								echo '<option value="' . esc_html( $category['ID'] ) . '" ' . ( esc_html( $category['ID'] ) == esc_html( $catogory_id )  ? 'selected' : '' ) . '>' . esc_html( $category['NAME'] ) . '</option>';
							}
						?>
					</select> 
				</td>
			</tr>
		</table>
		<table class='form-table' border='0'>
			<tr valign='top'>
				<td scope='row'>
					Высота
				</td>
				<td>
					<input type="text" name="ozep_height" size="2" value="<?php echo esc_html( $height ); ?>"> <?php echo( $dimension_unit ); ?>
				</td>
			</tr>
			<tr valign='top'>
				<td scope='row'>
					Ширина
				</td>
				<td>
					<input type="text" name="ozep_width" size="2" value="<?php echo esc_html( $width ); ?>"> <?php echo( $dimension_unit ); ?>
				</td>
			</tr>
			<tr valign='top'>
				<td scope='row'>
					Глубина
				</td>
				<td>
					<input type="text" name="ozep_depth" size="2" value="<?php echo esc_html( $depth ); ?>"> <?php echo( $dimension_unit ); ?>
				</td>
			</tr>
			<tr valign='top'>
				<td scope='row'>
					Вес
				</td>
				<td>
					<input type="text" name="ozep_weight" size="2" value="<?php echo esc_html( $weight ); ?>"> <?php echo( $weight_unit ); ?>
				</td>
			</tr>
		</table>
	