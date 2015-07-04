<?php
/*
* Custome field page in Welcart setting
* 
*
*/
?>
<div class="wrap">
	<div class="wc2_admin">
		<h2><?php _e('カスタムフィールド', 'wc2'); ?></h2>
		<div id="poststuff" class="metabox-holder">
			<div class="wc2tabs" id="csf-tabs">
				<ul>
					<li><a href="#custom_member_field"><?php _e('カスタム・メンバーフィールド','wc2'); ?></a></li>
					<li><a href="#custom_customer_field"><?php _e('カスタム・カスタマーフィールド','wc2'); ?></a></li>
					<li><a href="#custom_delivery_field"><?php _e('カスタム・デリバリーフィールド','wc2'); ?></a></li>
					<li><a href="#custom_order_field"><?php _e('カスタム・オーダーフィールド','wc2'); ?></a></li>
				</ul>

				<div id="custom_member_field">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e('カスタム・メンバーフィールド', 'wc2'); ?></span></h3>
						<div class="inside">
							<div id="postoptcustomstuff">
							<table id="csmb-list-table" class="list"<?php echo $csmb_display; ?>>
								<thead>
									<tr>
										<th><?php _e('フィールドキー','wc2'); ?><br /><?php _e('フィールド名','wc2'); ?></th>
										<th><?php _e('セレクト値','wc2'); ?></th>
									</tr>
								</thead>
								<tbody id="csmb-list">
						<?php
							if(is_array($csmb_field_keys)) {
								foreach($csmb_field_keys as $prefix_key) 
									echo WC2_CustomField::get_custom_field( $prefix_key );
							}
						?>
								</tbody>
							</table>
							<div id="ajax-response-csmb"></div>

							<p><strong><?php _e('新規カスタム・メンバーフィールド追加','wc2'); ?> : </strong></p>
							<table id="newmeta2">
								<thead>
									<tr>
										<th><?php _e('フィールドキー','wc2'); ?><br /><?php _e('フィールド名','wc2'); ?></th>
										<th><?php _e('セレクト値','wc2'); ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td class="item-opt-key">
											<div><input type="text" name="newcsmbkey" id="newcsmbkey" class="regular-text optname" value="" /></div>
											<div><input type="text" name="newcsmbname" id="newcsmbname" class="regular-text optname" value="" /></div>
											<div class="optcheck">
												<select name="newcsmbcapa" id="newcsmbcapa"><?php echo $csf_capacity; ?></select>
												<select name="newcsmbmeans" id="newcsmbmeans"><?php echo $csf_meansoption; ?></select>
												<select name="newcsmbposition" id="newcsmbposition"><?php echo $csf_positions; ?></select>
												<label for="newcsmbessential"><input type="checkbox" name="newcsmbessential" id="newcsmbessential" /><span><?php _e('必須項目','wc2'); ?><span></label>
											</div>
										</td>
										<td class="item-opt-value"><textarea name="newcsmbvalue" id="newcsmbvalue" class="optvalue" rows="5"></textarea></td>
									</tr>
								</tbody>
							</table>
							<div class="submit"><input type="button" class="button action" name="add_csmb" id="add_csmb" value="<?php _e('新規追加','wc2'); ?>" onclick="customField.Add('csmb');" /></div>
							<div id="newcsmb_loading" class="meta_submit_loading"></div>
							</div><!--postoptcustomstuff-->
						</div><!--inside-->
					</div><!--postbox-->
				</div><!--custom_member_field-->

				<div id="custom_customer_field">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e('カスタム・カスタマーフィールド', 'wc2'); ?></span></h3>
						<div class="inside">
							<div id="postoptcustomstuff">
							<table id="cscs-list-table" class="list"<?php echo $cscs_display; ?>>
								<thead>
									<tr>
										<th><?php _e('フィールドキー','wc2'); ?><br /><?php _e('フィールド名','wc2'); ?></th>
										<th><?php _e('セレクト値','wc2'); ?></th>
									</tr>
								</thead>
								<tbody id="cscs-list">
						<?php
							if(is_array($cscs_field_keys)) {
								foreach($cscs_field_keys as $key => $prefix_key) 
									echo WC2_CustomField::get_custom_field( $prefix_key );
							}
						?>
								</tbody>
							</table>
							<div id="ajax-response-cscs"></div>

							<p><strong><?php _e('新規カスタム・カスタマーフィールド追加','wc2') ?> : </strong></p>
							<table id="newmeta2">
								<thead>
									<tr>
										<th><?php _e('フィールドキー','wc2'); ?><br /><?php _e('フィールド名','wc2'); ?></th>
										<th><?php _e('セレクト値','wc2'); ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td class="item-opt-key">
											<div><input type="text" name="newcscskey" id="newcscskey" class="regular-text optname" value="" /></div>
											<div><input type="text" name="newcscsname" id="newcscsname" class="regular-text optname" value="" /></div>
											<div class="optcheck">
												<select name="newcscscapa" id="newcscscapa"><?php echo $csf_capacity; ?></select>
												<select name="newcscsmeans" id="newcscsmeans"><?php echo $csf_meansoption; ?></select>
												<select name="newcscsposition" id="newcscsposition"><?php echo $csf_positions; ?></select>
												<label for="newcscsessential"><input type="checkbox" name="newcscsessential" id="newcscsessential" /><span><?php _e('必須項目','wc2'); ?></span></label>
											</div>
										</td>
										<td class="item-opt-value"><textarea name="newcscsvalue" id="newcscsvalue" class="optvalue" rows="5"></textarea></td>
									</tr>
								</tbody>
							</table>
							<div class="submit"><input type="button" class="button action" name="add_cscs" id="add_cscs" value="<?php _e('新規追加','wc2'); ?>" onclick="customField.Add('cscs');" /></div>
							<div id="newcscs_loading" class="meta_submit_loading"></div>
							</div><!--postoptcustomstuff-->
						</div><!--inside-->
					</div><!--postbox-->
				</div><!--custom_customer_field-->

				<div id="custom_delivery_field">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e('カスタム・デリバリーフィールド', 'wc2'); ?></span></h3>
						<div class="inside">
							<div id="postoptcustomstuff">
							<table id="csde-list-table" class="list"<?php echo $csde_display; ?>>
								<thead>
									<tr>
										<th><?php _e('フィールドキー','wc2'); ?><br /><?php _e('フィールド名','wc2'); ?></th>
										<th><?php _e('セレクト値','wc2'); ?></th>
									</tr>
								</thead>
								<tbody id="csde-list">
						<?php
							if(is_array($csde_field_keys)) {
								foreach($csde_field_keys as $key => $prefix_key) 
									echo WC2_CustomField::get_custom_field( $prefix_key );
							}
						?>
								</tbody>
							</table>
							<div id="ajax-response-csde"></div>

							<p><strong><?php _e('新規カスタム・デリバリーフィールド追加','wc2'); ?> : </strong></p>
							<table id="newmeta2">
								<thead>
									<tr>
										<th><?php _e('フィールドキー','wc2'); ?><br /><?php _e('フィールド名','wc2'); ?></th>
										<th><?php _e('セレクト値','wc2'); ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td class="item-opt-key">
											<div><input type="text" name="newcsdekey" id="newcsdekey" class="regular-text optname" value="" /></div>
											<div><input type="text" name="newcsdename" id="newcsdename" class="regular-text optname" value="" /></div>
											<div class="optcheck">
												<select name="newcsdecapa" id="newcsdecapa"><?php echo $csf_capacity; ?></select>
												<select name="newcsdemeans" id="newcsdemeans"><?php echo $csf_meansoption; ?></select>
												<select name="newcsdeposition" id="newcsdeposition"><?php echo $csf_positions; ?></select>
												<label for="newcsdeessential"><input type="checkbox" name="newcsdeessential" id="newcsdeessential" /><span><?php _e('必須項目','wc2'); ?></span></label>
											</div>
										</td>
										<td class="item-opt-value"><textarea name="newcsdevalue" id="newcsdevalue" class="optvalue" rows="5"></textarea></td>
									</tr>
								</tbody>
							</table>
							<div class="submit"><input type="button" class="button action" name="add_csde" id="add_csde" value="<?php _e('新規追加','wc2'); ?>" onclick="customField.Add('csde');" /></div>
							<div id="newcsde_loading" class="meta_submit_loading"></div>
							</div><!--postoptcustomstuff-->
						</div><!--inside-->
					</div><!--postbox-->
				</div><!--custom_delivery_field-->

				<div id="custom_order_field">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e('カスタム・オーダーフィールド', 'wc2'); ?></span></h3>
						<div class="inside">
							<div id="postoptcustomstuff">
							<table id="csod-list-table" class="list"<?php echo $csod_display; ?>>
								<thead>
									<tr>
										<th><?php _e('フィールドキー','wc2'); ?><br /><?php _e('フィールド名','wc2'); ?></th>
										<th><?php _e('セレクト値','wc2'); ?></th>
									</tr>
								</thead>
								<tbody id="csod-list">
						<?php
							if(is_array($csod_field_keys)) {
								foreach($csod_field_keys as $prefix_key) 
									echo WC2_CustomField::get_custom_field( $prefix_key );
							}
						?>
								</tbody>
							</table>
							<div id="ajax-response-csod"></div>

							<p><strong><?php _e('新規カスタム・オーダーフィールドの追加','wc2'); ?> : </strong></p>
							<table id="newmeta2">
								<thead>
									<tr>
										<th><?php _e('フィールドキー','wc2'); ?><br /><?php _e('フィールド名','wc2'); ?></th>
										<th><?php _e('セレクト値','wc2'); ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td class="item-opt-key">
											<div><input type="text" name="newcsodkey" id="newcsodkey" class="regular-text optname" value="" /></div>
											<div><input type="text" name="newcsodname" id="newcsodname" class="regular-text optname" value="" /></div>
											<div class="optcheck">
												<select name="newcsodcapa" id="newcsodcapa"><?php echo $csf_capacity; ?></select>
												<select name="newcsodmeans" id="newcsodmeans"><?php echo $csf_meansoption; ?></select>
												<select name="newcsodposition" id="newcsodposition">
													<option value="beforeremarks"><?php _e('備考の前','wc2'); ?></option>
													<option value="other"><?php _e('その他','wc2'); ?></option>
												</select>
												<label for="newcsodessential"><input type="checkbox" name="newcsodessential" id="newcsodessential" /><span><?php _e('必須項目','wc2') ?></span></label>
											</div>
										</td>
										<td class="item-opt-value"><textarea name="newcsodvalue" id="newcsodvalue" class="optvalue" rows="5"></textarea></td>
									</tr>
								</tbody>
							</table>
							<div class="submit"><input type="button" class="button action" name="add_csod" id="add_csod" value="<?php _e('新規追加','wc2') ?>" onclick="customField.Add('csod');" /></div>
							<div id="newcsod_loading" class="meta_submit_loading"></div>
							</div><!--postoptcustomstuff-->
						</div><!--inside-->
					</div><!--postbox-->
				</div><!--custom_order_field-->
				<?php wp_nonce_field( 'wc2_custom_field', 'wc2_nonce', false ); ?>
			</div><!--wc2_csf-->
		</div><!--poststuff-->
	</div><!--wc2_admin-->
</div><!--wrap-->

