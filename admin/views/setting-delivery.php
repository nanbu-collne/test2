<?php
/*************************
		配送設定
**************************/
?>
<div class="wrap">
	<div class="wc2_admin">
		<div class="delivery-setting">
			<h2><?php _e('配送設定','wc2'); ?></h2>
			<div id="aniboxStatus" class="<?php echo $status; ?>">
				<div id="anibox" class="clearfix">
					<img id="info_image" src="<?php echo WC2_PLUGIN_URL; ?>/common/assets/images/list_message_<?php echo $status; ?>.gif" />
					<div class="mes" id="info_message"><?php echo $message; ?></div>
				</div>
			</div>
			<p><?php _e('※ 配送設定は商品登録よりも前に行う必要があります。','wc2'); ?><br />
			<?php _e('※ 商品登録後に配送方法の追加・削除を行った場合は全商品の更新が必要となりますのでご注意ください。','wc2'); ?></p>
			<div id="poststuff" class="metabox-holder">
				<!--tab-->
				<div class="wc2tabs" id="delivery-tabs">
					<ul>
						<li><a href="#delivery_page_setting_1"><?php _e('配送設定','wc2'); ?></a></li>
						<li><a href="#delivery_page_setting_2"><?php _e('配送方法','wc2'); ?></a></li>
						<li><a href="#delivery_page_setting_3"><?php _e('Shipping charges','wc2'); ?></a></li>
						<li><a href="#delivery_page_setting_4"><?php _e('配達日数','wc2'); ?></a></li>
					</ul>
					<div id="delivery_page_setting_1">
						<div class="postbox">
							<h3 class="hndle"><span><?php _e('配送設定', 'wc2'); ?></span></h3>
							<div class="inside">
								<form action="" method="post" name="option_form" id="option_form">
								<table class="form-table">
									<tr>
										<th><?php _e('配送業務締時間', 'wc2'); ?></th>
										<td>
											<select name="delivery_time_limit[hour]">
										<?php
											for( $i = 0; $i < 24; $i++ ) :
												$hour = sprintf('%02d', $i); ?>
												<option value="<?php echo $hour; ?>"<?php if($delivery_time_limit['hour'] == $hour) echo ' selected="selected"'; ?>><?php echo $hour; ?></option>
										<?php endfor; ?>
											</select>&nbsp;:&nbsp;
											<select name="delivery_time_limit[min]">
										<?php
											$i = 0;
											while( $i < 60 ) :
												$min = sprintf('%02d', $i); ?>
												<option value="<?php echo $min; ?>"<?php if($delivery_time_limit['min'] == $min) echo ' selected="selected"'; ?>><?php echo $min; ?></option>
										<?php
												$i += 10;
											endwhile; ?>
											</select>
										</td>
									</tr>
									<tr>
										<th><?php _e('午前着の可否', 'wc2'); ?></th>
										<td>
											<select name="shortest_delivery_time">
												<option value="0"<?php if($shortest_delivery_time == '0') echo ' selected="selected"'; ?>><?php _e('利用しない', 'wc2'); ?></option>
												<option value="1"<?php if($shortest_delivery_time == '1') echo ' selected="selected"'; ?>><?php _e('午前着可', 'wc2'); ?></option>
												<option value="2"<?php if($shortest_delivery_time == '2') echo ' selected="selected"'; ?>><?php _e('午前着不可', 'wc2'); ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<th><?php _e('配送希望日表示数', 'wc2'); ?></th>
										<td>
											<input name="delivery_after_days" type="text" class="charge_text small-text right" value="<?php echo $delivery_after_days; ?>">
										</td>
									</tr>
									<tr>
										<th><?php _e('配送制限', 'wc2'); ?></th>
										<td class="horizontal">
											<label title="none"><input name="delivery_limit_option" id="delivery_limit_option_none" type="radio" value="none" <?php if( 'none' == $delivery_limit_option ) echo ' checked="checked"'; ?> /><span><?php _e('利用しない', 'wc2'); ?></span></label>
											<label title="item"><input name="delivery_limit_option" id="delivery_limit_option_item" type="radio" value="item" <?php if( 'item' == $delivery_limit_option ) echo ' checked="checked"'; ?> /><span><?php _e('商品数', 'wc2'); ?></span></label>
											<!--<label title="weight"><input name="delivery_limit_option" id="delivery_limit_option_weight" type="radio" value="weight" <?php if( 'weight' == $delivery_limit_option ) echo ' checked="checked"'; ?> /><span><?php _e('重量', 'wc2'); ?></span></label>-->
										</td>
									</tr>
								</table>
								<input name="wc2_option_update" type="submit" class="button button-primary" value="<?php _e('Update Settings','wc2'); ?>" />
								<?php wp_nonce_field( 'wc2_setting_delivery','wc2_nonce', false ); ?>
								</form>
							</div>
						</div>
					</div>
					<div id="delivery_page_setting_2">
						<div class="postbox">
							<h3 class="hndle"><span><?php _e('配送方法', 'wc2'); ?></span></h3>
							<div class="inside column-2">
								<div class="delivery-container-1">
								<table class="form-table" align="left">
									<tr>
										<th></th>
										<td><a href="javascript:void(0);" id="new_delivery_method_action" class="add-new"><?php _e('新規追加', 'wc2'); ?></a></td>
									</tr>
									<tr>
										<th><?php _e('配送名', 'wc2'); ?></th>
										<td>
											<div id="delivery_method_name"></div>
											<div id="delivery_method_name2"></div>
											<div id="delivery_method_loading"></div>
											<div id="delivery_method_button"></div>
											<div><a href="#" id="moveup_action"><?php _e('優先順位を上げる', 'wc2'); ?></a></div>
											<div><a href="#" id="movedown_action"><?php _e('優先順位を下げる', 'wc2'); ?></a></div>
										</td>
									</tr>
								</table>
								</div>
								<div class="delivery-container-2">
								<table class="form-table">
									<tr>
										<th><?php _e('配送対象地域', 'wc2'); ?></th>
										<td class="horizontal"><div id="delivery_method_intl"></div></td>
									<tr>
										<th><?php _e('指定時間帯', 'wc2'); ?></th>
										<td><textarea name="delivery_method_time" id="delivery_method_time" rows="5"></textarea></td>
									</tr>
									<tr>
										<th><?php _e('送料固定', 'wc2'); ?></th>
										<td id="delivery_method_charge_td"></td>
									</tr>
									<tr>
										<th><?php _e('配達日数', 'wc2'); ?></th>
										<td id="delivery_method_days_td"></td>
									</tr>
									<tr>
										<th><?php _e('代引き不可', 'wc2'); ?></th>
										<td><div id="delivery_method_nocod"></div></td>
									</tr>
									<tr>
										<th><?php _e('配送制限商品数', 'wc2'); ?></th>
										<td><div id="delivery_method_item_limit_num"></div></td>
									</tr>
									<?php do_action( 'wc2_action_admin_delivery_method_setting' ); ?>
								</table>
								</div>
							</div><!--inside-->
						</div><!--postbox-->
					</div>
					<div id="delivery_page_setting_3">
						<div class="postbox">
							<h3 class="hndle"><span><?php _e('Shipping charges', 'wc2'); ?></span></h3>
							<div class="inside column-2">
								<div class="delivery-container-1">
								<table class="form-table" align="left">
									<tr>
										<th></th>
										<td><a href="javascript:void(0);" id="new_shipping_charge_action" class="add-new"><?php _e('新規追加', 'wc2'); ?></a></td>
									</tr>
									<tr>
										<th><?php _e('送料名', 'wc2'); ?></th>
										<td>
											<div id="shipping_charge_name"></div>
											<div id="shipping_charge_name2"></div>
											<div id="shipping_charge_loading"></div>
											<div id="shipping_charge_button"></div>
										</td>
									</tr>
								</table>
								</div>
								<div class="delivery-container-2">
								<table class="form-table">
									<tr>
										<th><?php _e('国', 'wc2'); ?></th>
										<td><select name="shipping_charge_country" id="shipping_charge_country">
											<?php echo wc2_shipping_country_option(); ?>
											</select>
										</td>
									</tr>
									<tr>
										<th><?php _e('Shipping charges', 'wc2'); ?></th>
										<td>
											<input id="allbutton" type="button" class="allbutton button" value="<?php _e('一括設定', 'wc2'); ?>" />
											<input name="allcharge" id="allcharge" type="text" class="charge_text medium-text right" /><?php echo wc2_crcode(); ?>
										</td>
									</tr>
									<tr>
										<th></th>
										<td><div id="shipping_charge_value"></div></td>
									</tr>
								</table>
								</div>
							</div>
						</div>
					</div>
					<div id="delivery_page_setting_4">
						<div class="postbox">
							<h3 class="hndle"><span><?php _e('配達日数', 'wc2'); ?></span></h3>
							<div class="inside column-2">
								<div class="delivery-container-1">
								<table class="form-table" align="left">
									<tr>
										<th></th>
										<td><a href="javascript:void(0);" id="new_delivery_days_action" class="add-new"><?php _e('新規追加', 'wc2'); ?></a></td>
									</tr>
									<tr>
										<th><?php _e("配達日数名", 'wc2'); ?></th>
										<td>
											<div id="delivery_days_name"></div>
											<div id="delivery_days_name2"></div>
											<div id="delivery_days_loading"></div>
											<div id="delivery_days_button"></div>
										</td>
									</tr>
								</table>
								</div>
								<div class="delivery-container-2">
								<table class="form-table">
									<tr>
										<th><?php _e('国', 'wc2'); ?></th>
										<td>
											<select name="delivery_days_country" id="delivery_days_country">
											<?php echo wc2_shipping_country_option(); ?>
											</select>
										</td>
									</tr>
									<tr>
										<th><?php _e('配達日数', 'wc2'); ?></th>
										<td>
											<input id="allbutton_delivery_days" type="button" class="allbutton button" value="<?php _e('一括設定', 'wc2'); ?>" />
											<input name="all_delivery_days" id="all_delivery_days" type="text" class="days_text small-text right" /><?php _e('日', 'wc2'); ?>
										</td>
									</tr>
									<tr>
										<th></th>
										<td><div id="delivery_days_value"></div></td>
									</tr>
								</table>
								</div>
							</div>
						</div>
					</div>
				</div><!--wc2tabs-->
			</div><!--poststuff-->
		</div><!--delivery_setting-->
	</div><!--wc2_admin-->
</div><!--wrap-->
