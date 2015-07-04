<?php
/*************************
		システム設定
**************************/
?>
<div class="wrap">
	<div class="wc2_admin">
		<div class="system-setting">
			<h2><?php _e('システム設定','wc2'); ?></h2>
			<div id="aniboxStatus" class="<?php echo $status; ?>">
				<div id="anibox" class="clearfix">
					<img id="info_image" src="<?php echo WC2_PLUGIN_URL; ?>/common/assets/images/list_message_<?php echo $status; ?>.gif" />
					<div class="mes" id="info_message"><?php echo $message; ?></div>
				</div>
			</div>

			<form action="" method="post" name="setting_form" id="setting_form">
			<div id="poststuff" class="metabox-holder">
				<div class="wc2tabs" id="system-tabs">
					<ul>
						<li><a href="#system-values-setting"><?php _e('システム設定','wc2'); ?></a></li>
						<li><a href="#system-locale_setting"><?php _e('国・言語・通貨','wc2'); ?></a></li>
					</ul>

					<div id="system-values-setting">
						<div class="postbox">
							<h3 class="hndle"><?php _e('システム設定','wc2'); ?></h3>
							<div class="inside">
							<table class="form-table">
								<tr>
									<th><?php _e('SSLを使用する','wc2'); ?></th>
									<?php $checked = ( $use_ssl == 1 ) ? ' checked="checked"' : ''; ?>
									<td><input type="checkbox" name="use_ssl" id="use_ssl" value="<?php esc_attr_e($use_ssl); ?>"<?php echo $checked; ?> /></td>
								</tr>
								<tr>
									<th><?php _e('WordPress のアドレス (SSL)', 'wc2'); ?></th>
									<td><input type="text" name="ssl_url_admin" id="ssl_url_admin" value="<?php esc_attr_e($ssl_url_admin); ?>" class="large-text" /></td>
								</tr>
								<tr>
									<th><?php _e('ブログのアドレス (SSL)', 'wc2'); ?></th>
									<td><input type="text" name="ssl_url" id="ssl_url" value="<?php esc_attr_e($ssl_url); ?>" class="large-text" /></td>
								</tr>
							</table>
							<table class="form-table">
								<tr>
									<th><?php _e('表示モード','wc2'); ?></th>
									<td>
										<?php $checked = ( $divide_item == 1 ) ? ' checked="checked"' : ''; ?>
										<input type="checkbox" name="divide_item" id="divide_item" value="<?php esc_attr_e($divide_item); ?>"<?php echo $checked; ?> />
										<label for="divide_item"><?php _e('ループ表示の際、商品を分離して表示する', 'wc2'); ?></label>
									</td>
								</tr>
							</table>
							<table class="form-table">
								<tr>
									<th><?php _e('rel属性', 'wc2'); ?></th>
									<td>rel="<input type="text" name="itemimg_anchor_rel" id="itemimg_anchor_rel" value="<?php esc_attr_e($itemimg_anchor_rel); ?>" class="regular-text" />"</td>
								</tr>
							</table>
							<table class="form-table">
								<tr>
									<th><?php _e('複合カテゴリーソート項目', 'wc2'); ?></th>
									<td>
										<select name="composite_category_orderby" id="composite_category_orderby">
											<option value="ID"<?php if( $composite_category_orderby == 'ID' ) echo ' selected="selected"'; ?>><?php _e('カテゴリーID', 'wc2'); ?></option>
											<option value="name"<?php if( $composite_category_orderby == 'name' ) echo ' selected="selected"'; ?>><?php _e('カテゴリー名', 'wc2'); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<th><?php _e('複合カテゴリーソート順', 'wc2'); ?></th>
									<td><select name="composite_category_order" id="composite_category_order">
											<option value="ASC"<?php if( $composite_category_order == 'ASC' ) echo ' selected="selected"'; ?>><?php _e('昇順', 'wc2'); ?></option>
											<option value="DESC"<?php if( $composite_category_order == 'DESC' ) echo ' selected="selected"'; ?>><?php _e('降順', 'wc2'); ?></option>
										</select>
									</td>
								</tr>
							</table>
							<table class="form-table">
								<tr>
									<th><?php _e('お問い合わせフォームのページID', 'wc2'); ?></th>
									<td><input type="text" name="inquiry_id" id="inquiry_id" value="<?php esc_attr_e($inquiry_id); ?>" class="small-text right" /></td>
								</tr>
							</table>
							<table class="form-table">
								<tr>
									<th><?php _e('注文番号ルール', 'wc2'); ?></th>
									<td>
										<label title="0"><input type="radio" name="dec_orderID_flag" id="dec_orderID_flag0" value="0"<?php if( $dec_orderID_flag === 0 ) echo ' checked="checked"'; ?> /><span><?php _e('連番（数値）', 'wc2'); ?></span></label>
										<label title="1"><input type="radio" name="dec_orderID_flag" id="dec_orderID_flag1" value="1"<?php if( $dec_orderID_flag === 1 ) echo ' checked="checked"'; ?> /><span><?php _e('ランダムな文字列（英字）', 'wc2'); ?></span></label>
									</td>
								</tr>
								<tr>
									<th><?php _e('注文番号プレフィックス', 'wc2'); ?></th>
									<td><input type="text" name="dec_orderID_prefix" id="dec_orderID_prefix" value="<?php esc_attr_e($dec_orderID_prefix); ?>" class="regular-text" /></td>
								</tr>
								<tr>
									<th><?php _e('注文番号の桁数', 'wc2'); ?></th>
									<td><input type="text" name="dec_orderID_digit" id="dec_orderID_digit" value="<?php esc_attr_e($dec_orderID_digit); ?>" class="small-text right" /></td>
								</tr>
							</table>
							<table class="form-table">
								<tr>
									<th><?php _e('商品サブ画像適用ルール', 'wc2'); ?></th>
									<td class="vertical">
										<label title="0"><input type="radio" name="subimage_rule" id="subimage_rule0" value="0"<?php if( $subimage_rule === 0 ) echo ' checked="checked"'; ?> /><span><?php _e('新しいルールを適用しない<br />（商品コード前方一致）', 'wc2'); ?></span></label>
										<label title="1"><input type="radio" name="subimage_rule" id="subimage_rule1" value="1"<?php if( $subimage_rule === 1 ) echo ' checked="checked"'; ?> /><span><?php _e('新しいルールを適用する<br />（商品コードと連番の間にハイフンを2つ置く【例：a001--01】）', 'wc2'); ?></span></label>
									</td>
								</tr>
							</table>
							<table class="form-table">
								<tr>
									<th><?php _e('納品書の記載方法', 'wc2'); ?></th>
									<td class="vertical">
										<label title="0"><input type="radio" name="pdf_delivery" id="pdf_delivery0" value="0"<?php if( $pdf_delivery === 0 ) echo ' checked="checked"'; ?> /><span><?php _e('購入者情報を宛名とする', 'wc2'); ?></span></label>
										<label title="1"><input type="radio" name="pdf_delivery" id="pdf_delivery1" value="1"<?php if( $pdf_delivery === 1 ) echo ' checked="checked"'; ?> /><span><?php _e('配送先情報を宛名とする', 'wc2'); ?></span></label>
									</td>
								</tr>
							</table>
							<table class="form-table">
								<tr>
									<th><?php _e('CSVファイルの文字コード', 'wc2'); ?></th>
									<td class="vertical">
										<label title="0"><input type="radio" name="csv_encode_type" id="csv_encode_type0" value="0"<?php if( $csv_encode_type === 0 ) echo ' checked="checked"'; ?> /><span>Sift-JIS</span></label>
										<label title="1"><input type="radio" name="csv_encode_type" id="csv_encode_type1" value="1"<?php if( $csv_encode_type === 1 ) echo ' checked="checked"'; ?> /><span>UTF-8</span></label>
									</td>
								</tr>
							</table>
							</div><!--.inside-->
						</div><!--#postbox-->
					</div><!--#system-values-setting-->

					<div id="system-locale_setting">
						<div class="postbox">
							<h3 class="hndle"><span><?php _e('国・言語・通貨','wc2'); ?></span></h3>
							<div class="inside">
							<table class="form-table">
								<tr>
									<th><?php _e('フロントエンドの言語', 'wc2'); ?></th>
									<td><select name="front_lang" id="front_lang">
										<?php foreach( $locale['language'] as $key => $value ): ?>
											<option value="<?php esc_attr_e($key); ?>"<?php if( $system_front_lang == $key ) echo ' selected="selected"'; ?>><?php esc_html_e($value); ?></option>
										<?php endforeach; ?>
										</select>
									</td>
								</tr>
								<tr>
									<th><?php _e('通貨表示', 'wc2'); ?></th>
									<td><select name="currency" id="currency">
										<?php foreach( $locale['country'] as $key => $value ): ?>
											<option value="<?php esc_attr_e($key); ?>"<?php if( $system_currency == $key ) echo ' selected="selected"'; ?>><?php echo $value; ?></option>
										<?php endforeach; ?>
										<option value="manual"<?php echo ($system_currency == 'manual' ? ' selected="selected"' : ''); ?>><?php _e('Manual', 'wc2'); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<th><?php _e('住所氏名の様式', 'wc2'); ?></th>
									<td><select name="addressform" id="addressform">
										<?php foreach( $locale['country'] as $key => $value ): ?>
											<option value="<?php esc_attr_e($key); ?>"<?php if( $system_addressform == $key ) echo ' selected="selected"'; ?>><?php esc_html_e($value); ?></option>
										<?php endforeach; ?>
										</select>
									</td>
								</tr>
								<tr>
									<th><?php _e('Target market country', 'wc2'); ?>
										<div><input type="button" class="button" name="set_target_market" id="set_target_market" value="<?php _e('選択してください', 'wc2'); ?>" /></div>
									</th>
									<td><select name="target_market[]" size="10" multiple="multiple" class="multipleselect" id="target_market">
										<?php foreach( $locale['country'] as $key => $value ): ?>
											<option value="<?php esc_attr_e($key); ?>"<?php if( in_array( $key, $system_target_markets ) ) echo ' selected="selected"'; ?>><?php esc_html_e($value); ?></option>
										<?php endforeach; ?>
										</select>
									</td>
								</tr>
								<tr>
									<th><?php _e('都道府県', 'wc2'); ?>
										<div><span id="target_market_loading"></span><span id="target_market_province"></span></div>
									</th>
									<td><textarea name="province" id="province" class="regular-text" rows="10"></textarea><div id="province_ajax"></div></td>
								</tr>
							</table>
							</div><!--.inside-->
						</div><!--postbox-->
					</div><!--#system-locale_setting-->

				</div><!--.wc2tabs-->
			</div><!--#poststuff-->
			<div class="update-all-options"><input name="wc2_option_update" type="submit" class="button button-primary" value="<?php _e('Update Settings','wc2'); ?>" /></div>
			<?php wp_nonce_field( 'wc2_setting_system', 'wc2_nonce', false ); ?>
			</form>
		</div><!--.system-setting-->
	</div><!--.wc2-admin-->
</div><!--.wrap-->
