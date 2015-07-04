<?php
/**
 * Welcart2.
 *
 * 受注一覧画面
 */
?>
<div class="wrap">
	<div class="wc2_admin">
		<div class="order_list">
			<h2><?php _e($this->title.'一覧', 'wc2'); ?>
				<!--新規追加-->
				<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'new' ) ) ); ?>" class="add-new-h2"><?php echo esc_html_x('新規追加', 'wc2'); ?></a>
			</h2>
			<?php
			//if ( array_key_exists( 'search_in', $_REQUEST ) ) {
			if( array_key_exists( 'search_column', $_REQUEST ) && 'none' != $_REQUEST['search_column'] && !empty($_REQUEST['search_word']) ) {
				$search_column_val = ( 'none' == $search_column_key || '' == $search_column_key ) ? '' : $search_columns[$search_column_key];
				echo sprintf( '<h3>' . __( '[%s] に [%s] を含む検索結果', 'wc2' ) . '</h3>', esc_html( $search_column_val ), esc_html( $search_word ) );
			}
			?>
			<form id="posts-filter" action="" method="get" name="search_form">
				<input type="hidden" name="page" value="<?php esc_attr_e( $_REQUEST['page'] ); ?>" />
				<div id="search-table" class="tablenav top">
					<div class="alignleft actions bulkactions">
						<select name="search_column" id="search-column">
							<option value="none"><?php _e('Search Filters', 'wc2'); ?></option>
						<?php
							foreach( $search_columns as $key => $value ) :
								$selected = ( $key == $search_column_key ) ? ' selected="selected"' : ''; ?>
							<option value="<?php esc_attr_e($key); ?>"<?php echo $selected; ?>><?php esc_html_e($value); ?></option>
						<?php endforeach; ?>
						</select>
						<span id="search-label"<?php if( $search_word_key != '' ) echo ' style="display:none"'; ?>><?php _e('Keyword', 'wc2'); ?></span>
						<span id="search-field"><input type="text" name="search_word[keyword]" class="search-word" id="search-word-keyword" value="<?php esc_attr_e($search_word); ?>" maxlength="50"<?php if( $search_word_key != '' ) echo ' style="display:none"'; ?> />
							<select name="search_word[order_status]" id="search-word-order_status"<?php if( $search_column_key != 'order_status' ) echo ' style="display:none"'; ?>>
							<?php
								foreach( $order_status as $status_key => $status_name ) :
									$selected = ( $status_key == $search_word_key ) ? ' selected="selected"' : ''; ?>
								<option value="<?php esc_attr_e($status_key); ?>"<?php echo $selected; ?>><?php esc_html_e($status_name); ?></option>
							<?php endforeach; ?>
							</select>
							<select name="search_word[receipt_status]" id="search-word-receipt_status"<?php if( $search_column_key != 'receipt_status' ) echo ' style="display:none"'; ?>>
							<?php
								foreach( $receipt_status as $status_key => $status_name ) :
									$selected = ( $status_key == $search_word_key ) ? ' selected="selected"' : ''; ?>
								<option value="<?php esc_attr_e($status_key); ?>"<?php echo $selected; ?>><?php esc_html_e($status_name); ?></option>
							<?php endforeach; ?>
							</select>
							<select name="search_word[order_type]" id="search-word-order_type"<?php if( $search_column_key != 'order_type' ) echo ' style="display:none"'; ?>>
							<?php
								foreach( $order_type as $status_key => $status_name ) :
									$selected = ( $status_key == $search_word_key ) ? ' selected="selected"' : ''; ?>
								<option value="<?php esc_attr_e($status_key); ?>"<?php echo $selected; ?>><?php esc_html_e($status_name); ?></option>
							<?php endforeach; ?>
							</select>
						</span>
						<input type="submit" name="search_in" id="search-in" class="button" value="<?php _e('Search', 'wc2'); ?>" />
						<input type="submit" name="search_out" id="search-out" class="button" value="<?php _e('Release', 'wc2'); ?>" />
					</div>
					<div class="alignleft actions bulkactions">
						<select name="search_period" id="search-period">
						<?php
							foreach( $order_refine_period as $key => $value ) : 
								$selected = ( $key == $search_period ) ? ' selected="selected"' : ''; ?>
							<option value="<?php esc_attr_e($key); ?>"<?php echo $selected; ?>><?php esc_html_e($value); ?></option>
						<?php endforeach; ?>
						</select>
						<span id="period-specified"<?php if( $search_period != 5 ) echo ' style="display:none"'; ?>><input type="text" name="startdate" id="startdate" value="<?php echo $startdate; ?>" />～<input type="text" name="enddate" id="enddate" value="<?php echo $enddate; ?>" /></span>
						<input type="submit" name="search_refine" id="search-refine" class="button" value="<?php _e('Period Filters', 'wc2'); ?>" />
					</div>
				</div><!--search-table-->

				<!--<div class="navi-box-link">
					<a style="cursor:pointer;" id="navi-box-link"><?php _e('操作フィールド表示', 'wc2'); ?></a>
				</div>-->
				<div id="navi-box">
					<div class="alignright actions">
				<?php
					ob_start();
				?>
						<input type="button" id="dl-orderdetail-list" class="button" value="<?php _e($this->title.'明細データ出力', 'wc2'); ?>" />
						<input type="button" id="dl-order-list" class="button" value="<?php _e($this->title.'データ出力', 'wc2'); ?>" />
				<?php
					$admin_order_list_download = ob_get_contents();
					ob_end_clean();
					echo apply_filters( 'wc2_filter_admin_order_list_download', $admin_order_list_download );
				?>
					</div>
				</div><!--navi-box-link-->
				<?php do_action( 'wc2_action_admin_order_list_searchbox' ); ?>
				<?php wp_nonce_field( 'wc2_order_list', 'wc2_nonce', false ); ?>
			</form>

			<form id="movies-filter" method="post">
				<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
				<?php $order_list->display(); ?>
				<?php wp_nonce_field( 'wc2_order_list', 'wc2_nonce', false ); ?>
			</form>

			<!--受注明細データ出力項目チェック-->
			<div id="dlOrderDetailListDialog" title="<?php _e($this->title.'明細データ出力', 'wc2'); ?>">
				<p><?php _e('出力したい項目を選択して、ダウンロードを押してください。', 'wc2'); ?></p>
				<input type="button" class="button" id="dl-detail" value="<?php _e('Download', 'wc2'); ?>" />
				<fieldset class="dl-check-option"><legend><?php _e('Header Information', 'wc2'); ?></legend>
				<?php
					ob_start();
				?>
					<label for="chk-detail-ID"><input type="checkbox" class="check-detail" id="chk-detail-ID" value="ID" checked="checked" disabled="disabled" /><?php _e('ID', 'wc2'); ?></label>
					<label for="chk-detail-deco_id"><input type="checkbox" class="check-detail" id="chk-detail-deco_id" value="deco_id" checked="checked" disabled="disabled" /><?php _e('Order number', 'wc2'); ?></label>
					<label for="chk-detail-date"><input type="checkbox" class="check-detail" id="chk-detail-date" value="date"<?php wc2_checked_e($chk_detail, 'date'); ?> /><?php _e('Order date', 'wc2'); ?></label>
				<?php if( wc2_is_membersystem_state() ) : ?>
					<label for="chk-detail-member_id"><input type="checkbox" class="check-detail" id="chk-detail-member_id" value="member_id"<?php wc2_checked_e($chk_detail, 'member_id'); ?> /><?php _e('Membership ID', 'wc2'); ?></label>
				<?php endif; ?>
					<label for="chk-detail-name"><input type="checkbox" class="check-detail" id="chk-detail-name" value="name"<?php wc2_checked_e($chk_detail, 'name'); ?> /><?php _e('Name', 'wc2'); ?></label>
					<label for="chk-detail-delivery_method"><input type="checkbox" class="check-detail" id="chk-detail-delivery_method" value="delivery_method"<?php wc2_checked_e($chk_detail, 'delivery_method'); ?> /><?php _e('Delivery method', 'wc2'); ?></label>
					<label for="chk-detail-shipping_date"><input type="checkbox" class="check-detail" id="chk-detail-shipping_date" value="shipping_date"<?php wc2_checked_e($chk_detail, 'shipping_date'); ?> /><?php _e('Shipping date', 'wc2'); ?></label>
				<?php do_action( 'wc2_action_admin_order_check_detail_head', $chk_detail ); ?>
				<?php
					$admin_order_list_check_detail_head = ob_get_contents();
					ob_end_clean();
					echo apply_filters( 'wc2_filter_admin_order_list_check_detail_head', $admin_order_list_check_detail_head, $chk_detail );
				?>
				</fieldset>
				<fieldset class="dl-check-option"><legend><?php _e('Item Information', 'wc2'); ?></legend>
				<?php
					ob_start();
				?>
					<label for="chk-detail-item_code"><input type="checkbox" class="check-detail" id="chk-detail-item_code" value="item_code" checked="checked" disabled="disabled" /><?php _e('Item code', 'wc2'); ?></label>
					<label for="chk-detail-sku_code"><input type="checkbox" class="check-detail" id="chk-detail-sku_code" value="sku_code"<?php wc2_checked_e($chk_detail, 'sku_code'); ?> /><?php _e('SKU code', 'wc2'); ?></label>
					<label for="chk-detail-item_name"><input type="checkbox" class="check-detail" id="chk-detail-item_name" value="item_name"<?php wc2_checked_e($chk_detail, 'item_name'); ?> /><?php _e('Item name', 'wc2'); ?></label>
					<label for="chk-detail-sku_name"><input type="checkbox" class="check-detail" id="chk-detail-sku_name" value="sku_name"<?php wc2_checked_e($chk_detail, 'sku_name'); ?> /><?php _e('SKU display name', 'wc2'); ?></label>
					<label for="chk-detail-options"><input type="checkbox" class="check-detail" id="chk-detail-options" value="options"<?php wc2_checked_e($chk_detail, 'options'); ?> /><?php _e('Options for items', 'wc2'); ?></label>
					<label for="chk-detail-quantity"><input type="checkbox" class="check-detail" id="chk-detail-quantity" value="quantity" checked="checked" disabled="disabled" /><?php _e('Quantity', 'wc2'); ?></label>
					<label for="chk-detail-price"><input type="checkbox" class="check-detail" id="chk-detail-price" value="price" checked="checked" disabled="disabled" /><?php _e('Unit price', 'wc2'); ?></label>
					<label for="chk-detail-unit"><input type="checkbox" class="check-detail" id="chk-detail-unit" value="unit"<?php wc2_checked_e($chk_detail, 'unit'); ?> /><?php _e('Unit', 'wc2'); ?></label>
				<?php do_action( 'wc2_action_admin_order_check_detail_info', $chk_detail ); ?>
				<?php
					$admin_order_list_check_detail_info = ob_get_contents();
					ob_end_clean();
					echo apply_filters( 'wc2_filter_admin_order_list_check_detail_info', $admin_order_list_check_detail_info, $chk_detail );
				?>
				</fieldset>
			</div>

			<!--受注データ出力項目チェック-->
			<div id="dlOrderListDialog" title="<?php _e($this->title.'データ出力', 'wc2'); ?>">
				<p><?php _e('出力したい項目を選択して、ダウンロードを押してください。', 'wc2'); ?></p>
				<input type="button" class="button" id="dl-order" value="<?php _e('Download', 'wc2'); ?>" />
				<fieldset class="dl-check-option"><legend><?php _e('Orderer Information', 'wc2'); ?></legend>
				<?php
					$wc2_opt_order = wc2_get_option('wc2_opt_order');
					$chk_order = isset($wc2_opt_order['chk_ord']) ? $wc2_opt_order['chk_ord']: '';
					ob_start();
				?>
					<label for="chk-order-ID"><input type="checkbox" class="check-order" id="chk-order-ID" value="ID" checked="checked" disabled="disabled" /><?php _e('ID', 'wc2'); ?></label>
					<label for="chk-order-deco_id"><input type="checkbox" class="check-order" id="chk-order-deco_id" value="deco_id" checked="checked" disabled="disabled" /><?php _e('Order number', 'wc2'); ?></label>
					<label for="chk-order-date"><input type="checkbox" class="check-order" id="chk-order-date" value="order_date"<?php wc2_checked_e($chk_order, 'order_date'); ?> /><?php _e('Order date', 'wc2'); ?></label>
				<?php if( wc2_is_membersystem_state() ) : ?>
					<label for="chk-order-member_id"><input type="checkbox" class="check-order" id="chk-order-member_id" value="member_id"<?php wc2_checked_e($chk_order, 'member_id'); ?> /><?php _e('Membership ID', 'wc2'); ?></label>
				<?php endif; ?>
				<?php
					$hd_keys = WC2_Funcs::get_custom_field_keys( WC2_CSCS, 'head' );
					if( !empty($hd_keys) ) :
						foreach( $hd_keys as $cs_key ) :
							$checked = WC2_Funcs::get_checked( $chk_order, $cs_key );
							$entry = wc2_get_option( $cs_key );
							$name = $entry['name']; ?>
					<label for="chk-order-<?php esc_attr_e($cs_key); ?>"><input type="checkbox" class="check-order" id="chk-order-<?php esc_attr_e($cs_key); ?>" value="<?php esc_attr_e($cs_key); ?>"<?php echo $checked; ?>/><?php esc_html_e($name); ?></label>
				<?php endforeach;
					endif; ?>
					<label for="chk-order-email"><input type="checkbox" class="check-order" id="chk-order-email" value="email"<?php wc2_checked_e($chk_order, 'email'); ?> /><?php _e('E-mail', 'wc2'); ?></label>
				<?php
					$bn_keys = WC2_Funcs::get_custom_field_keys( WC2_CSCS, 'beforename' );
					if( !empty($bn_keys) ) :
						foreach( $bn_keys as $cs_key ) :
							$checked = WC2_Funcs::get_checked( $chk_order, $cs_key );
							$entry = wc2_get_option( $cs_key );
							$name = $entry['name']; ?>
					<label for="chk-order-<?php esc_attr_e($cs_key); ?>"><input type="checkbox" class="check-order" id="chk-order-<?php esc_attr_e($cs_key); ?>" value="<?php esc_attr_e($cs_key); ?>"<?php echo $checked; ?>/><?php esc_html_e($name); ?></label>
				<?php endforeach;
					endif; ?>
					<label for="chk-order-name"><input type="checkbox" class="check-order" id="chk-order-name" value="name" checked="checked" disabled="disabled" /><?php _e('Name', 'wc2'); ?></label>
				<?php
					if( $applyform == 'JP' ) : ?>
					<label for="chk-order-kana"><input type="checkbox" class="check-order" id="chk-order-kana" value="kana"<?php wc2_checked_e($chk_order, 'kana'); ?> /><?php _e('Kana','wc2'); ?></label>
				<?php
					endif; ?>
				<?php
					$an_keys = WC2_Funcs::get_custom_field_keys( WC2_CSCS, 'aftername' );
					if( !empty($an_keys) ) :
						foreach( $an_keys as $cs_key ) :
							$checked = WC2_Funcs::get_checked( $chk_order, $cs_key );
							$entry = wc2_get_option( $cs_key );
							$name = $entry['name']; ?>
						<label for="chk-order-<?php esc_attr_e($cs_key); ?>"><input type="checkbox" class="check-order" id="chk-order-<?php esc_attr_e($cs_key); ?>" value="<?php esc_attr_e($cs_key); ?>"<?php echo $checked; ?>/><?php esc_html_e($name); ?></label>
				<?php endforeach;
					endif; ?>
				<?php
					switch( $applyform ) :
						case 'JP': ?>
					<label for="chk-order-country"><input type="checkbox" class="check-order" id="chk-order-country" value="country"<?php wc2_checked_e($chk_order, 'country'); ?> /><?php _e('Country', 'wc2'); ?></label>
					<label for="chk-order-zipcode"><input type="checkbox" class="check-order" id="chk-order-zipcode" value="zipcode"<?php wc2_checked_e($chk_order, 'zipcode'); ?> /><?php _e('Postal Code', 'wc2'); ?></label>
					<label for="chk-order-pref"><input type="checkbox" class="check-order" id="chk-order-pref" value="pref" checked="checked" disabled="disabled" /><?php _e('Prefecture', 'wc2'); ?></label>
					<label for="chk-order-address1"><input type="checkbox" class="check-order" id="chk-order-address1" value="address1" checked="checked" disabled="disabled" /><?php _e('City', 'wc2'); ?></label>
					<label for="chk-order-address2"><input type="checkbox" class="check-order" id="chk-order-address2" value="address2" checked="checked" disabled="disabled" /><?php _e('Building name, floor, room number', 'wc2'); ?></label>
					<label for="chk-order-tel"><input type="checkbox" class="check-order" id="chk-order-tel" value="tel"<?php wc2_checked_e($chk_order, 'tel'); ?> /><?php _e('Phone number', 'wc2'); ?></label>
					<label for="chk-order-fax"><input type="checkbox" class="check-order" id="chk-order-fax" value="fax"<?php wc2_checked_e($chk_order, 'fax'); ?> /><?php _e('FAX number', 'wc2'); ?></label>
				<?php
						break;
					case 'US':
					default: ?>
					<label for="chk-order-address2"><input type="checkbox" class="check-order" id="chk-order-address2" value="address2" checked="checked" disabled="disabled" /><?php _e('Building name, floor, room number', 'wc2'); ?></label>
					<label for="chk-order-address1"><input type="checkbox" class="check-order" id="chk-order-address1" value="address1" checked="checked" disabled="disabled" /><?php _e('City', 'wc2'); ?></label>
					<label for="chk-order-pref"><input type="checkbox" class="check-order" id="chk-order-pref" value="pref" checked="checked" disabled="disabled" /><?php _e('Prefecture', 'wc2'); ?></label>
					<label for="chk-order-zipcode"><input type="checkbox" class="check-order" id="chk-order-zipcode" value="zipcode"<?php wc2_checked_e($chk_order, 'zipcode'); ?> /><?php _e('Postal Code', 'wc2'); ?></label>
					<label for="chk-order-country"><input type="checkbox" class="check-order" id="chk-order-country" value="country"<?php wc2_checked_e($chk_order, 'country'); ?> /><?php _e('Country', 'wc2'); ?></label>
					<label for="chk-order-tel"><input type="checkbox" class="check-order" id="chk-order-tel" value="tel"<?php wc2_checked_e($chk_order, 'tel'); ?> /><?php _e('Phone number', 'wc2'); ?></label>
					<label for="chk-order-fax"><input type="checkbox" class="check-order" id="chk-order-fax" value="fax"<?php wc2_checked_e($chk_order, 'fax'); ?> /><?php _e('FAX number', 'wc2'); ?></label>
				<?php
						break;
					endswitch; ?>
				<?php
					$btm_keys = WC2_Funcs::get_custom_field_keys( WC2_CSCS, 'bottom' );
					if( !empty($btm_keys) ) :
						foreach( $btm_keys as $cs_key ) :
							$checked = WC2_Funcs::get_checked( $chk_order, $cs_key );
							$entry = wc2_get_option( $cs_key );
							$name = $entry['name']; ?>
					<label for="chk-order-<?php esc_attr_e($cs_key); ?>"><input type="checkbox" class="check-order" id="chk-order-<?php esc_attr_e($cs_key); ?>" value="<?php esc_attr_e($cs_key); ?>"<?php echo $checked; ?>/><?php esc_html_e($name); ?></label>
				<?php endforeach;
					endif; ?>
				<?php
					$oth_keys = WC2_Funcs::get_custom_field_keys( WC2_CSCS, 'other' );
					if( !empty($oth_keys) ) :
						foreach( $oth_keys as $cs_key ) :
							$checked = WC2_Funcs::get_checked( $chk_order, $cs_key );
							$entry = wc2_get_option( $cs_key );
							$name = $entry['name']; ?>
					<label for="chk-order-<?php esc_attr_e($cs_key); ?>"><input type="checkbox" class="check-order" id="chk-order-<?php esc_attr_e($cs_key); ?>" value="<?php esc_attr_e($cs_key); ?>"<?php echo $checked; ?>/><?php esc_html_e($name); ?></label>
				<?php endforeach;
					endif; ?>

				<?php do_action( 'wc2_action_admin_order_check_order_customer', $chk_order ); ?>
				<?php
					$admin_order_list_check_order_customer = ob_get_contents();
					ob_end_clean();
					echo apply_filters( 'wc2_filter_admin_order_list_check_order_customer', $admin_order_list_check_order_customer, $chk_order );
				?>
				</fieldset>
				<fieldset class="dl-check-option"><legend><?php _e('配送先情報', 'wc2'); ?></legend>
				<?php
					ob_start();
				?>
				<?php
					$hd_keys = WC2_Funcs::get_custom_field_keys( WC2_CSDE, 'head' );
					if( !empty($hd_keys) ) :
						foreach( $hd_keys as $cs_key ) :
							$checked = WC2_Funcs::get_checked( $chk_order, $cs_key );
							$entry = wc2_get_option( $cs_key );
							$name = $entry['name']; ?>
					<label for="chk-order-<?php esc_attr_e($cs_key); ?>"><input type="checkbox" class="check-order" id="chk-order-<?php esc_attr_e($cs_key); ?>" value="<?php esc_attr_e($cs_key); ?>"<?php echo $checked; ?>/><?php esc_html_e($name); ?></label>
				<?php endforeach;
					endif; ?>
				<?php
					$bn_keys = WC2_Funcs::get_custom_field_keys( WC2_CSDE, 'beforename' );
					if( !empty($bn_keys) ) :
						foreach( $bn_keys as $cs_key ) :
							$checked = WC2_Funcs::get_checked( $chk_order, $cs_key );
							$entry = wc2_get_option( $cs_key );
							$name = $entry['name']; ?>
					<label for="chk-order-<?php esc_attr_e($cs_key); ?>"><input type="checkbox" class="check-order" id="chk-order-<?php esc_attr_e($cs_key); ?>" value="<?php esc_attr_e($cs_key); ?>"<?php echo $checked; ?>/><?php esc_html_e($name); ?></label>
				<?php endforeach;
					endif; ?>
					<label for="chk-order-delivery_name"><input type="checkbox" class="check-order" id="chk-order-delivery_name" value="delivery_name" checked="checked" disabled="disabled" /><?php _e('Shipping name', 'wc2'); ?></label>
				<?php
					if( $applyform == 'JP' ) : ?>
					<label for="chk-order-delivery_kana"><input type="checkbox" class="check-order" id="chk-order-delivery_kana" value="delivery_kana"<?php wc2_checked_e($chk_order, 'kana'); ?> /><?php _e('Shipping kana','wc2'); ?></label>
				<?php
					endif; ?>
				<?php
					$an_keys = WC2_Funcs::get_custom_field_keys( WC2_CSDE, 'aftername' );
					if( !empty($an_keys) ) :
						foreach( $an_keys as $cs_key ) :
							$checked = WC2_Funcs::get_checked( $chk_order, $cs_key );
							$entry = wc2_get_option( $cs_key );
							$name = $entry['name']; ?>
						<label for="chk-order-<?php esc_attr_e($cs_key); ?>"><input type="checkbox" class="check-order" id="chk-order-<?php esc_attr_e($cs_key); ?>" value="<?php esc_attr_e($cs_key); ?>"<?php echo $checked; ?>/><?php esc_html_e($name); ?></label>
				<?php endforeach;
					endif; ?>
				<?php
					switch( $applyform ) :
						case 'JP': ?>
					<label for="chk-order-delivery_country"><input type="checkbox" class="check-order" id="chk-order-delivery_country" value="delivery_country"<?php wc2_checked_e($chk_order, 'delivery_country'); ?> /><?php _e('Shipping country', 'wc2'); ?></label>
					<label for="chk-order-delivery_zipcode"><input type="checkbox" class="check-order" id="chk-order-delivery_zipcode" value="delivery_zipcode"<?php wc2_checked_e($chk_order, 'delivery_zipcode'); ?> /><?php _e('Shipping postal code', 'wc2'); ?></label>
					<label for="chk-order-delivery_pref"><input type="checkbox" class="check-order" id="chk-order-delivery_pref" value="delivery_pref" checked="checked" disabled="disabled" /><?php _e('Shipping prefecture', 'wc2'); ?></label>
					<label for="chk-order-delivery_address1"><input type="checkbox" class="check-order" id="chk-order-delivery_address1" value="delivery_address1" checked="checked" disabled="disabled" /><?php _e('Shipping city', 'wc2'); ?></label>
					<label for="chk-order-delivery_address2"><input type="checkbox" class="check-order" id="chk-order-delivery_address2" value="delivery_address2" checked="checked" disabled="disabled" /><?php _e('Shipping building name, floor, room number', 'wc2'); ?></label>
					<label for="chk-order-delivery_tel"><input type="checkbox" class="check-order" id="chk-order-delivery_tel" value="delivery_tel"<?php wc2_checked_e($chk_order, 'delivery_tel'); ?> /><?php _e('Shipping phone number', 'wc2'); ?></label>
					<label for="chk-order-delivery_fax"><input type="checkbox" class="check-order" id="chk-order-delivery_fax" value="delivery_fax"<?php wc2_checked_e($chk_order, 'delivery_fax'); ?> /><?php _e('Shipping FAX number', 'wc2'); ?></label>
				<?php
						break;
					case 'US':
					default: ?>
					<label for="chk-order-delivery_address2"><input type="checkbox" class="check-order" id="chk-order-delivery_address2" value="delivery_address2" checked="checked" disabled="disabled" /><?php _e('Shipping building name, floor, room number', 'wc2'); ?></label>
					<label for="chk-order-delivery_address1"><input type="checkbox" class="check-order" id="chk-order-delivery_address1" value="delivery_address1" checked="checked" disabled="disabled" /><?php _e('Shipping city', 'wc2'); ?></label>
					<label for="chk-order-delivery_pref"><input type="checkbox" class="check-order" id="chk-order-delivery_pref" value="delivery_pref" checked="checked" disabled="disabled" /><?php _e('Shipping prefecture', 'wc2'); ?></label>
					<label for="chk-order-delivery_zipcode"><input type="checkbox" class="check-order" id="chk-order-delivery_zipcode" value="delivery_zipcode"<?php wc2_checked_e($chk_order, 'delivery_zipcode'); ?> /><?php _e('Shipping postal code', 'wc2'); ?></label>
					<label for="chk-order-delivery_country"><input type="checkbox" class="check-order" id="chk-order-delivery_country" value="delivery_country"<?php wc2_checked_e($chk_order, 'delivery_country'); ?> /><?php _e('Shipping country', 'wc2'); ?></label>
					<label for="chk-order-delivery_tel"><input type="checkbox" class="check-order" id="chk-order-delivery_tel" value="delivery_tel"<?php wc2_checked_e($chk_order, 'delivery_tel'); ?> /><?php _e('Shipping phone number', 'wc2'); ?></label>
					<label for="chk-order-delivery_fax"><input type="checkbox" class="check-order" id="chk-order-delivery_fax" value="delivery_fax"<?php wc2_checked_e($chk_order, 'delivery_fax'); ?> /><?php _e('Shipping FAX number', 'wc2'); ?></label>
				<?php
						break;
					endswitch; ?>
				<?php
					$btm_keys = WC2_Funcs::get_custom_field_keys( WC2_CSDE, 'bottom' );
					if( !empty($btm_keys) ) :
						foreach( $btm_keys as $cs_key ) :
							$checked = WC2_Funcs::get_checked( $chk_order, $cs_key );
							$entry = wc2_get_option( $cs_key );
							$name = $entry['name']; ?>
					<label for="chk-order-<?php esc_attr_e($cs_key); ?>"><input type="checkbox" class="check-order" id="chk-order-<?php esc_attr_e($cs_key); ?>" value="<?php esc_attr_e($cs_key); ?>"<?php echo $checked; ?>/><?php esc_html_e($name); ?></label>
				<?php endforeach;
					endif; ?>
				<?php
					$oth_keys = WC2_Funcs::get_custom_field_keys( WC2_CSDE, 'other' );
					if( !empty($oth_keys) ) :
						foreach( $oth_keys as $cs_key ) :
							$checked = WC2_Funcs::get_checked( $chk_order, $cs_key );
							$entry = wc2_get_option( $cs_key );
							$name = $entry['name']; ?>
					<label for="chk-order-<?php esc_attr_e($cs_key); ?>"><input type="checkbox" class="check-order" id="chk-order-<?php esc_attr_e($cs_key); ?>" value="<?php esc_attr_e($cs_key); ?>"<?php echo $checked; ?>/><?php esc_html_e($name); ?></label>
				<?php endforeach;
					endif; ?>
				<?php do_action( 'wc2_action_admin_order_check_order_delivery', $chk_order ); ?>
				<?php
					$admin_order_list_check_order_delivery = ob_get_contents();
					ob_end_clean();
					echo apply_filters( 'wc2_filter_admin_order_list_check_order_delivery', $admin_order_list_check_order_delivery, $chk_order );
				?>
				</fieldset>
				<fieldset class="dl-check-option"><legend><?php _e($this->title.'情報', 'wc2'); ?></legend>
				<?php
					ob_start();
				?>
					<label for="chk-order-shipping_date"><input type="checkbox" class="check-order" id="chk-order-shipping_date" value="shipping_date"<?php wc2_checked_e($chk_order, 'shipping_date'); ?> /><?php _e('Shipping date', 'wc2'); ?></label>
					<label for="chk-order-payment_method"><input type="checkbox" class="check-order" id="chk-order-payment_method" value="payment_method"<?php wc2_checked_e($chk_order, 'payment_method'); ?> /><?php _e('Payment method','wc2'); ?></label>
					<label for="chk-order-delivery_method"><input type="checkbox" class="check-order" id="chk-order-delivery_method" value="delivery_method"<?php wc2_checked_e($chk_order, 'delivery_method'); ?> /><?php _e('Delivery method','wc2'); ?></label>
					<label for="chk-order-delivery_date"><input type="checkbox" class="check-order" id="chk-order-delivery_date" value="delivery_date"<?php wc2_checked_e($chk_order, 'delivery_date'); ?> /><?php _e('Delivery date','wc2'); ?></label>
					<label for="chk-order-delivery_time"><input type="checkbox" class="check-order" id="chk-order-delivery_time" value="delivery_time"<?php wc2_checked_e($chk_order, 'delivery_time'); ?> /><?php _e('Delivery time','wc2'); ?></label>
					<label for="chk-order-delidue_date"><input type="checkbox" class="check-order" id="chk-order-delidue_date" value="delidue_date"<?php wc2_checked_e($chk_order, 'delidue_date'); ?> /><?php _e('Shipping schedule date', 'wc2'); ?></label>
					<label for="chk-order-order_status"><input type="checkbox" class="check-order" id="chk-order-status" value="order_status"<?php wc2_checked_e($chk_order, 'order_status'); ?> /><?php _e('Order status', 'wc2'); ?></label>
					<label for="chk-order-receipt_status"><input type="checkbox" class="check-order" id="chk-order-receipt_status" value="receipt_status"<?php wc2_checked_e($chk_order, 'receipt_status'); ?> /><?php _e('Receipt status', 'wc2'); ?></label>
					<label for="chk-order-receipted_date"><input type="checkbox" class="check-order" id="chk-order-receipted_date" value="receipted_date"<?php wc2_checked_e($chk_order, 'receipted_date'); ?> /><?php _e('Receipted date', 'wc2'); ?></label>
					<label for="chk-order-order_type"><input type="checkbox" class="check-order" id="chk-order-order_type" value="order_type"<?php wc2_checked_e($chk_order, 'order_type'); ?> /><?php _e('Order type', 'wc2'); ?></label>
					<label for="chk-order-total_amount"><input type="checkbox" class="check-order" id="chk-order-total_amount" value="total_amount" checked="checked" disabled="disabled" /><?php _e('Total Amount', 'wc2'); ?></label>
				<?php if( wc2_is_membersystem_state() && wc2_is_membersystem_point() ) : ?>
					<label for="chk-order-getpoint"><input type="checkbox" class="check-order" id="chk-order-getpoint" value="getpoint"<?php wc2_checked_e($chk_order, 'getpoint'); ?> /><?php _e('Granted points', 'wc2'); ?></label>
					<label for="chk-order-usedpoint"><input type="checkbox" class="check-order" id="chk-order-usedpoint" value="usedpoint"<?php wc2_checked_e($chk_order, 'usedpoint'); ?> /><?php _e('Used points', 'wc2'); ?></label>
				<?php endif; ?>
					<label for="chk-order-discount"><input type="checkbox" class="check-order" id="chk-order-discount" value="discount"<?php wc2_checked_e($chk_order, 'discount'); ?> /><?php echo apply_filters( 'wc2_filter_discount_label', __('Discount', 'wc2' ) ); ?></label>
					<label for="chk-order-shipping_charge"><input type="checkbox" class="check-order" id="chk-order-shipping_charge" value="shipping_charge"<?php wc2_checked_e($chk_order, 'shipping_charge'); ?> /><?php _e('Shipping charges', 'wc2'); ?></label>
					<label for="chk-order-cod_fee"><input type="checkbox" class="check-order" id="chk-order-cod_fee" value="cod_fee"<?php wc2_checked_e($chk_order, 'cod_fee'); ?> /><?php echo apply_filters( 'wc2_filter_cod_label', __('COD fee', 'wc2') ); ?></label>
					<label for="chk-order-tax"><input type="checkbox" class="check-order" id="chk-order-tax" value="tax"<?php wc2_checked_e($chk_order, 'tax'); ?> /><?php _e('Consumption tax', 'wc2'); ?></label>
				<?php
					$br_keys = WC2_Funcs::get_custom_field_keys( WC2_CSOD, 'beforeremarks' );
					if( !empty($br_keys) ) :
						foreach( $br_keys as $key => $cs_key ) :
							$checked = WC2_Funcs::get_checked( $chk_order, $cs_key );
							$entry = wc2_get_option( $cs_key );
							$name = $entry['name']; ?>
					<label for="chk-order-<?php esc_attr_e($cs_key); ?>"><input type="checkbox" class="check-order" id="chk-order-<?php esc_attr_e($cs_key); ?>" value="<?php esc_attr_e($cs_key); ?>"<?php echo $checked; ?>/><?php esc_html_e($name); ?></label>
				<?php endforeach;
					endif; ?>
					<label for="chk-order-note"><input type="checkbox" class="check-order" id="chk-order-note" value="note"<?php wc2_checked_e($chk_order, 'note'); ?> /><?php _e('Notes', 'wc2'); ?></label>
				<?php
					$oth_keys = WC2_Funcs::get_custom_field_keys( WC2_CSOD, 'other' );
					if( !empty($oth_keys) ) :
						foreach( $oth_keys as $key => $cs_key ) :
							$checked = WC2_Funcs::get_checked( $chk_order, $cs_key );
							$entry = wc2_get_option( $cs_key );
							$name = $entry['name']; ?>
					<label for="chk-order-<?php esc_attr_e($cs_key); ?>"><input type="checkbox" class="check-order" id="chk-order-<?php esc_attr_e($cs_key); ?>" value="<?php esc_attr_e($cs_key); ?>"<?php echo $checked; ?>/><?php esc_html_e($name); ?></label>
				<?php endforeach;
					endif; ?>
				<?php do_action( 'wc2_action_admin_order_check_order_info', $chk_order ); ?>
				<?php
					$admin_order_list_check_order_info = ob_get_contents();
					ob_end_clean();
					echo apply_filters( 'wc2_filter_admin_order_list_check_order_info', $admin_order_list_check_order_info, $chk_order );
				?>
				</fieldset>
			</div>
		</div><!--order_list-->
	</div><!--wc2_admin-->
</div><!--wrap-->
