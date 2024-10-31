		
<div class="wrap">
	<h2><?php esc_html_e( 'OZON Export Настройки', 'ozep' ); ?></h2>

		<form action='' method='POST'>
		<?php
			$nonce = wp_create_nonce( 'ozep-settings-nonce' );
		?>
			<input type='hidden' name='ozep_settings_wpnonce' value='<?php echo esc_html( $nonce ) ?>'/>
			<input type='hidden' name='action' value='ozep_save_settings'/>
			<table class='form-table' border='0'>
				<tr valign='top'>
					<th scope='row'>
						<label>OZON Client-Id</label>
					</th>
					<td>
						<input size="40" type='text' name='ozep_ozon_client_id' value='<?php echo esc_html( $ozon_client_id ); ?>'>
					</td>
				</tr>
				<tr valign='top'>
					<th scope='row'>
						<label>OZON Api-Key</label>
					</th>
					<td>
						<input size="40" type='text' name='ozep_ozon_api_key' value='<?php echo esc_html( $ozon_api_key ); ?>'>
					</td>
				</tr>
				<tr valign='top'>
					<th scope='row'>
						<label>Категория по умолчанию</label>
					</th>
					<td>
						<select name='ozep_export_catogory_id_default'>
						<?php
						
							foreach ( Ozon_Export_Products_Main::$OZON_EXPORT_CATEGORIES as $category ) {
								echo '<option value="' . esc_html( $category['ID'] ) . '" ' . ( esc_html( $category['ID'] ) == esc_html( $export_catogory_id_default )  ? 'selected' : '' ) . '>' . esc_html( $category['NAME'] ) . '</option>';
							}
						?>
						</select> 
						для добавления нужной Вам ozon категории напишите разработчику на ozepdevelopment@mail.ru
					</td>
				</tr>
				<tr valign='top'>
					<th scope='row'>
						<label>Добавить к цене</label>
					</th>
					<td>
						<input size="3" type='text' name='ozep_price_extra_proc' value='<?php echo esc_html( $price_extra_proc ); ?>'>% 
						этот процент автоматически добавится к цене для компенсации комиссии ozon 
					</td>
				</tr>
			</table>
			<p class='submit'>
				<button type='submit' class='button button-primary' >
					Сохранить Настройки
				</button>
			</p>
		</form>
		<hr/>
</div>