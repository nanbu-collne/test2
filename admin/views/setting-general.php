<?php
/*************************
		基本設定
**************************/
?>
<div class="wrap">
	<div class="wc2_admin">
		<div class="general-setting">
			<h2><?php _e('基本設定','wc2'); ?></h2>
			<div id="aniboxStatus" class="<?php echo $status; ?>">
				<div id="anibox" class="clearfix">
					<img id="info_image" src="<?php echo WC2_PLUGIN_URL; ?>/common/assets/images/list_message_<?php echo $status; ?>.gif" />
					<div class="mes" id="info_message"><?php echo $message; ?></div>
				</div>
			</div>
			<form action="" method="post" name="option_form" id="option_form">
				<div id="poststuff" class="metabox-holder">
					<div class="wc2tabs" id="general-tabs">
						<ul>
							<li><a href="#general_page_setting_1"><?php _e('ショップ設定','wc2'); ?></a></li>
							<li><a href="#general_page_setting_2"><?php _e('営業設定','wc2'); ?></a></li>
							<li><a href="#general_page_setting_3"><?php _e('会員システム','wc2'); ?></a></li>
							<li><a href="#general_page_setting_4"><?php _e('商品名の表示ルール','wc2'); ?></a></li>
							<li><a href="#general_page_setting_5"><?php _e('カート関連ページに挿入する説明書き','wc2'); ?></a></li>
							<li><a href="#general_page_setting_6"><?php _e('会員関連ページに挿入する説明書き','wc2'); ?></a></li>
							<?php do_action('wc2_action_admin_general_head'); ?>
						</ul>
	<!--ショップ設定-->
						<div id="general_page_setting_1">
							<div class="postbox">
								<h3 class="hndle"><span><?php _e('ショップ設定', 'wc2'); ?></span></h3>
								<div class="inside">
									<table class="form-table">
										<tr>
											<th><?php _e('Company Name', 'wc2'); ?></th>
											<td><input name="company_name" type="text" class="regular-text" value="<?php esc_attr_e($general['company_name']); ?>" /></td>
										</tr>
										<tr>
											<th><?php _e('Postal Code', 'wc2'); ?></th>
											<td><input name="zip_code" type="text" class="medium-text" value="<?php esc_attr_e($general['zip_code']); ?>" /></td>
										</tr>
										<tr>
											<th><?php _e('Address', 'wc2'); ?>1</th>
											<td><input name="address1" type="text" class="regular-text" value="<?php esc_attr_e($general['address1']); ?>" /></td>
										</tr>
										<tr>
											<th><?php _e('Address', 'wc2'); ?>2</th>
											<td><input name="address2" type="text" class="regular-text" value="<?php esc_attr_e($general['address2']); ?>" /></td>
										</tr>
										<tr>
											<th><?php _e('Phone number', 'wc2'); ?></th>
											<td><input name="tel_number" type="text" class="medium-text" value="<?php esc_attr_e($general['tel_number']); ?>" /></td>
										</tr>
										<tr>
											<th><?php _e('FAX number', 'wc2'); ?></th>
											<td><input name="fax_number" type="text" class="medium-text" value="<?php esc_attr_e($general['fax_number']); ?>" /></td>
										</tr>
										<tr>
											<th><span class="required"><?php _e('*', 'wc2'); ?></span><?php _e('受注用メールアドレス', 'wc2'); ?></th>
											<td><input name="order_mail" type="text" class="regular-text" value="<?php esc_attr_e($general['order_mail']); ?>" /></td>
										</tr>
										<tr>
											<th><span class="required"><?php _e('*', 'wc2'); ?></span><?php _e('問い合わせメールアドレス', 'wc2'); ?></th>
											<td><input name="inquiry_mail" type="text" class="regular-text" value="<?php esc_attr_e($general['inquiry_mail']); ?>" /></td>
										</tr>
										<tr>
											<th><span class="required"><?php _e('*', 'wc2'); ?></span><?php _e("送信元メールアドレス", 'wc2'); ?></th>
											<td><input name="sender_mail" type="text" class="regular-text" value="<?php esc_attr_e($general['sender_mail']); ?>" /></td>
										</tr>
										<tr>
											<th><span class="required"><?php _e('*', 'wc2'); ?></span><?php _e('エラーメールアドレス', 'wc2'); ?></th>
											<td><input name="error_mail" type="text" class="regular-text" value="<?php esc_attr_e($general['error_mail']); ?>" /></td>
										</tr>
										<tr>
											<th><?php _e('Copyright', 'wc2'); ?></th>
											<td><input name="copyright" type="text" class="regular-text" value="<?php esc_attr_e($general['copyright']); ?>" /></td>
										</tr>
										<tr>
											<th><?php _e('送料無料条件', 'wc2'); ?></th>
											<td><input name="postage_privilege" type="text" class="medium-text num" value="<?php esc_attr_e($general['postage_privilege']); ?>" /><?php _e('以上', 'wc2'); ?></td>
										</tr>
										<tr>
											<th><?php _e('購入制限数初期値', 'wc2'); ?></th>
											<td><input name="purchase_limit" type="text" class="medium-text num" value="<?php esc_attr_e($general['purchase_limit']); ?>" /><?php _e('個まで', 'wc2'); ?></td>
										</tr>
										<tr>
											<th><?php _e('発送日の初期値', 'wc2'); ?></th>
											<td><select name="shipping_rule" class="short_select">
											<?php foreach( (array)$general['shipping_rule'] as $key => $label ):
													$selected = ( $key == $general['shipping_rule'] ) ? ' selected="selected"' : ''; ?>
													<option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
											<?php endforeach; ?>
												</select>
											</td>
										</tr>
										<tr>
											<th><?php _e('消費税区分', 'wc2'); ?></th>
											<td class="horizontal">
												<label title="include"><input name="tax_mode" id="tax_mode_include" type="radio" value="include"<?php if($general['tax_mode'] == 'include') echo ' checked="checked"'; ?> /><span><?php _e('税込', 'wc2'); ?></span></label>
												<label title="exclude"><input name="tax_mode" id="tax_mode_exclude" type="radio" value="exclude"<?php if($general['tax_mode'] == 'exclude') echo ' checked="checked"'; ?> /><span><?php _e('税別', 'wc2'); ?></span></label>
											</td>
										</tr>
										<tr>
											<th><?php _e('消費税対象', 'wc2'); ?></th>
											<td class="horizontal">
												<label title="products"><input name="tax_target" id="tax_mode_products" type="radio" value="products"<?php if($general['tax_target'] == 'products') echo ' checked="checked"'; ?> /><span><?php _e('商品代金のみ', 'wc2'); ?></span></label>
												<label title="all"><input name="tax_target" id="tax_mode_all" type="radio" value="all"<?php if($general['tax_target'] == 'all') echo ' checked="checked"'; ?> /><span><?php _e('総合計金額', 'wc2'); ?></span></label>
											</td>
										</tr>
										<tr>
											<th><?php _e('消費税率', 'wc2'); ?></th>
											<td><input name="tax_rate" type="text" class="small-text num" value="<?php esc_attr_e($general['tax_rate']); ?>" />%</td>
										</tr>
										<tr>
											<th><?php _e('税計算方法', 'wc2'); ?></th>
											<td class="horizontal">
												<label title="cutting"><input name="tax_method" id="tax_method_cutting" type="radio" value="cutting"<?php if($general['tax_method'] == 'cutting') echo ' checked="checked"'; ?> /><span><?php _e('切捨て', 'wc2'); ?></span></label>
												<label title="bring"><input name="tax_method" id="tax_method_bring" type="radio" value="bring"<?php if($general['tax_method'] == 'bring') echo ' checked="checked"'; ?> /><span><?php _e('切上げ', 'wc2'); ?></span></label>
												<label title="rounding"><input name="tax_method" id="tax_method_rounding" type="radio" value="rounding"<?php if($general['tax_method'] == 'rounding') echo ' checked="checked"'; ?> /><span><?php _e('四捨五入', 'wc2'); ?></span></label>
											</td>
										</tr>
										<tr>
											<th><?php _e('「カートに入れる」ボタンの挙動', 'wc2'); ?></th>
											<td class="vertical">
												<label title="0"><input type="radio" name="add2cart" id="add2cart0" value="0"<?php if( $general['add2cart'] === '0' ) echo ' checked="checked"'; ?> /><span><?php _e('数量を加算<br />（カートに同じ商品が入っていた場合、入力された数量を加算する）', 'wc2'); ?></span></label>
												<label title="1"><input type="radio" name="add2cart" id="add2cart1" value="1"<?php if( $general['add2cart'] === '1' ) echo ' checked="checked"'; ?> /><span><?php _e('数量を上書き<br />（カートに同じ商品が入っていた場合、入力された数量に変更する）', 'wc2'); ?></span></label>
											</td>
										</tr>
									</table>
								</div><!--inside-->
							</div><!--postbox-->
						</div><!--#general_page_setting_1-->
	<!--営業設定-->
						<div id="general_page_setting_2">
							<div class="postbox">
								<h3 class="hndle"><span><?php _e('営業設定','wc2'); ?></span></h3>
								<div class="inside">
									<table class="form-table">
									<?php
										if( is_array($display_mode_label) and 0 < count($display_mode_label) ) :
									?>
										<tr height="50">
											<th><?php _e('表示モード','wc2'); ?></th>
											<td class="horizontal">
									<?php
											foreach( (array)$display_mode_label as $key => $label ) :
												if( $key == 'Promotionsale' )
													continue;
												$checked = $general['display_mode'] == $key ? ' checked="checked"' : '';
									?>
												<label title="<?php echo $key; ?>"><input name="display_mode" type="radio" id="<?php echo $key; ?>" value="<?php echo $key; ?>"<?php echo $checked; ?> /><span><?php echo $label; ?></span></label>
									<?php
											endforeach;
									?>
											</td>
										</tr>
									<?php endif; ?>
										<tr>
											<th><?php _e('キャンペーン対象', 'wc2'); ?></th>
											<td class="vertical">
									<?php 
										$dropdown_selected = $general['campaign_category'];
										$dropdown_options = array('show_option_all' => __('All Items', 'wc2'), 'name' => 'campaign_category', 'hide_empty' => 0, 'hierarchical' => 1, 'orderby' => 'name', 'taxonomy' => 'item', 'selected' => $dropdown_selected);
										$dropdown_options = apply_filters( 'wc2_filter_admin_campaign_category_options', $dropdown_options );
										wp_dropdown_categories($dropdown_options);
									?>
											</td>
										</tr>
										<tr>
											<th><?php _e('キャンペーン特典', 'wc2'); ?></th>
											<td>
												<label title="point"><input name="cat_privilege" type="radio" id="privilege_point" value="point"<?php if($general['campaign_privilege'] == 'point') echo ' checked="checked"'; ?> /><span><?php _e('Points', 'wc2'); ?></span></label>
												<input name="point_num" type="text" class="short_str num" value="<?php esc_attr_e($general['privilege_point']); ?>" /><?php _e('倍', 'wc2'); ?>
												<br />
												<label title="discount"><input name="cat_privilege" type="radio" id="privilege_discount" value="discount"<?php if($general['campaign_privilege'] == 'discount') echo ' checked="checked"'; ?> /><span><?php _e('割引', 'wc2'); ?></span></label>
												<input name="discount_num" type="text" class="short_str num" value="<?php esc_attr_e($general['privilege_discount']); ?>" />%
											</td>
										</tr>
									</table>
								</div><!--inside-->
							</div><!--postbox-->
	<!--キャンペーンスケジュール-->
							<div class="postbox"><!--postbox-->
								<h3 class="hndle"><span><?php _e('Campaign Schedule','wc2'); ?></span></h3>
								<div class="inside campaign">
									<table class="form-table">
										<tr>
											<th><?php _e('Start date and time','wc2'); ?></th>
											<td>
												<select name="campaign_schedule[start][year]">
													<option value="0"<?php if($campaign_schedule_start_year == 0) echo ' selected="selected"'; ?>></option>
													<option value="<?php echo $yearstr; ?>"<?php if($campaign_schedule_start_year == $yearstr) echo ' selected="selected"'; ?>><?php echo $yearstr; ?></option>
													<option value="<?php echo $yearstr+1; ?>"<?php if($campaign_schedule_start_year == ($yearstr+1)) echo ' selected="selected"'; ?>><?php echo $yearstr+1; ?></option>
												</select>
												<?php _e('year','wc2'); ?>
												<select name="campaign_schedule[start][month]">
													<option value="0"<?php if($campaign_schedule_start_month == 0) echo ' selected="selected"'; ?>></option>
									<?php for($i=1; $i<13; $i++) : ?>
													<option value="<?php echo $i; ?>"<?php if($campaign_schedule_start_month == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
									<?php endfor; ?>
												</select>
												<?php _e('month','wc2'); ?>
												<select name="campaign_schedule[start][day]">
													<option value="0"<?php if($campaign_schedule_start_day == 0) echo ' selected="selected"'; ?>></option>
									<?php for($i=1; $i<32; $i++) : ?>
													<option value="<?php echo $i; ?>"<?php if($campaign_schedule_start_day == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
									<?php endfor; ?>
												</select>
												<?php _e('day','wc2'); ?>
												<select name="campaign_schedule[start][hour]">
									<?php for($i=0; $i<24; $i++) : ?>
													<option value="<?php echo $i; ?>"<?php if($campaign_schedule_start_hour == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
									<?php endfor; ?>
												</select>
												<?php _e('hour','wc2'); ?>
												<select name="campaign_schedule[start][min]">
									<?php for($i=0; $i<12; $i++) : ?>
													<option value="<?php echo $i*5; ?>"<?php if($campaign_schedule_start_min == ($i*5)) echo ' selected="selected"'; ?>><?php echo $i*5; ?></option>
									<?php endfor; ?>
												</select>
												<?php _e('min','wc2'); ?>
											</td>
										</tr>
										<tr>
											<th><?php _e('End date and time','wc2'); ?></th>
											<td>
												<select name="campaign_schedule[end][year]">
														<option value="0"<?php if($campaign_schedule_end_year == 0) echo ' selected="selected"'; ?>></option>
														<option value="<?php echo $yearstr; ?>"<?php if($campaign_schedule_end_year == $yearstr) echo ' selected="selected"'; ?>><?php echo $yearstr; ?></option>
														<option value="<?php echo $yearstr+1; ?>"<?php if($campaign_schedule_end_year == ($yearstr+1)) echo ' selected="selected"'; ?>><?php echo $yearstr+1; ?></option>
												</select>
												<?php _e('year','wc2'); ?>
												<select name="campaign_schedule[end][month]">
														<option value="0"<?php if($campaign_schedule_end_month == 0) echo ' selected="selected"'; ?>></option>
									<?php for($i=1; $i<13; $i++) : ?>
														<option value="<?php echo $i; ?>"<?php if($campaign_schedule_end_month == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
									<?php endfor; ?>
												</select>
												<?php _e('month','wc2'); ?>
												<select name="campaign_schedule[end][day]">
														<option value="0"<?php if($campaign_schedule_end_day == 0) echo ' selected="selected"'; ?>></option>
									<?php for($i=1; $i<32; $i++) : ?>
														<option value="<?php echo $i; ?>"<?php if($campaign_schedule_end_day == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
									<?php endfor; ?>
												</select>
												<?php _e('day','wc2'); ?>
												<select name="campaign_schedule[end][hour]">
									<?php for($i=0; $i<24; $i++) : ?>
														<option value="<?php echo $i; ?>"<?php if($campaign_schedule_end_hour == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
									<?php endfor; ?>
												</select>
												<?php _e('hour','wc2'); ?>
												<select name="campaign_schedule[end][min]">
									<?php for($i=0; $i<12; $i++) : ?>
														<option value="<?php echo $i*5; ?>"<?php if($campaign_schedule_end_min == ($i*5)) echo ' selected="selected"'; ?>><?php echo $i*5; ?></option>
									<?php endfor; ?>
												</select>
												<?php _e('min','wc2'); ?>
											</td>
										</tr>
									</table>
								</div>
							</div><!--postbox-->
	<!--営業日カレンダー-->
							<div class="postbox">
								<h3 class="hndle"><span><?php _e('Business Calendar', 'wc2'); ?></span></h3>
								<?php do_action( 'wc2_action_admin_general_business_calendar' ); ?>
								<div class="inside">
									<span class="business_days_exp_box" style="background-color:#DFFFDD"></span><?php _e('Business day', 'wc2'); ?><br />
									<span class="business_days_exp_box" style="background-color:#FFAA55"></span><?php _e('発送業務休日', 'wc2'); ?>
								</div>
								<div class="inside business-calendar">
								<?php foreach( $this->cal as $cal_key => $cal_val ) : ?>
									<?php 
										$cal_year = $cal_val->_year;
										$cal_month = $cal_val->_month;
									?>
									<div>
										<table id="calendar<?php esc_attr_e($cal_key); ?>" class="calendar">
											<caption><?php echo sprintf(__('%2$s/%1$s', 'wc2'), $cal_year, $cal_month); ?></caption>
											<tr>
												<th class="cal"><div class="cangeWday" id="cangeWday<?php esc_attr_e($cal_key); ?>_0"><font color="#FF3300"><?php _e('Sun', 'wc2'); ?></font></div></th>
												<th class="cal"><div class="cangeWday" id="cangeWday<?php esc_attr_e($cal_key); ?>_1"><?php _e('Mon', 'wc2'); ?></div></th>
												<th class="cal"><div class="cangeWday" id="cangeWday<?php esc_attr_e($cal_key); ?>_2"><?php _e('Tue', 'wc2'); ?></div></th>
												<th class="cal"><div class="cangeWday" id="cangeWday<?php esc_attr_e($cal_key); ?>_3"><?php _e('Wed', 'wc2'); ?></div></th>
												<th class="cal"><div class="cangeWday" id="cangeWday<?php esc_attr_e($cal_key); ?>_4"><?php _e('Thu', 'wc2'); ?></div></th>
												<th class="cal"><div class="cangeWday" id="cangeWday<?php esc_attr_e($cal_key); ?>_5"><?php _e('Fri', 'wc2'); ?></div></th>
												<th class="cal"><div class="cangeWday" id="cangeWday<?php esc_attr_e($cal_key); ?>_6"><?php _e('Sat', 'wc2'); ?></div></th>
											</tr>
									<?php for ($i = 0; $i < $cal_val->getRow(); $i++) : ?>
											<tr>
										<?php
											for ($d = 0; $d <= 6; $d++) : 
												$mday = $cal_val->getDateText($i, $d);
												if($mday != "") :
													$business = isset( $general['business_days'][$cal_year][$cal_month][$mday] ) ? $general['business_days'][$cal_year][$cal_month][$mday] : 1;
													$color = ($business == 1) ? '#DFFFDD' : '#FFAA55;color:#FFFFFF;font-weight:bold;';
										?>
												<td class="cal" id="cal<?php esc_attr_e($cal_key); ?>_<?php echo ($i + 1); ?>_<?php echo $d; ?>" style="background-color:<?php esc_attr_e($color); ?>">
													<div class="cangeBus" id="cangeBus<?php esc_attr_e($cal_key); ?>_<?php echo ($i + 1); ?>_<?php echo $d; ?>"><?php echo $mday; ?></div>
													<input name="business_days[<?php echo $cal_year; ?>][<?php echo $cal_month; ?>][<?php echo $mday; ?>]" id="calendar<?php esc_attr_e($cal_key); ?>_<?php echo ($i+1); ?>_<?php echo $d; ?>" type="hidden" value="<?php echo $business; ?>">
												</td>
											<?php else : ?>
												<td>&nbsp;</td>
											<?php endif; ?>
										<?php endfor; ?>
											</tr>
									<?php endfor; ?>
										</table>
									</div>
								<?php endforeach;?>
								</div>
							</div>
						</div><!--#general_page_setting_2-->
	<!--会員システム-->
						<div id="general_page_setting_3">
							<div class="postbox">
								<h3 class="hndle"><span><?php _e('Membership system', 'wc2'); ?></span></h3>
								<div class="inside">
									<table class="form-table">
										<tr>
											<th><?php _e('Membership system', 'wc2'); ?></th>
											<td class="horizontal">
												<label title="activate"><input name="membersystem_state" id="membersystem_state_activate" type="radio" value="activate"<?php if($general['membersystem_state'] == 'activate') echo ' checked="checked"'; ?> /><span><?php _e('利用する', 'wc2'); ?></span></label>
												<label title="deactivate"><input name="membersystem_state" id="membersystem_state_deactivate" type="radio" value="deactivate"<?php if($general['membersystem_state'] == 'deactivate') echo ' checked="checked"'; ?> /><span><?php _e('利用しない', 'wc2'); ?></span></label>
											</td>
										</tr>
										<tr>
											<th><?php _e('Membership points', 'wc2'); ?></th>
											<td class="horizontal">
												<label title="activate"><input name="membersystem_point" id="membersystem_point_activate" type="radio" value="activate"<?php if($general['membersystem_point'] == 'activate') echo ' checked="checked"'; ?> /><span><?php _e('付与する', 'wc2'); ?></span></label>
												<label title="deactivate"><input name="membersystem_point" id="membersystem_point_deactivate" type="radio" value="deactivate"<?php if($general['membersystem_point'] == 'deactivate') echo ' checked="checked"'; ?> /><span><?php _e('付与しない', 'wc2'); ?></span></label>
											</td>
										</tr>
										<tr>
											<th><?php _e('ポイント率初期値', 'wc2'); ?></th>
											<td><input name="point_rate" type="text" class="medium-text num" value="<?php esc_attr_e($general['point_rate']); ?>" />%</td>
										</tr>
										<tr>
											<th><?php _e('会員登録時ポイント', 'wc2'); ?></th>
											<td><input name="start_point" type="text" class="medium-text num" value="<?php esc_attr_e($general['start_point']); ?>" />pt</td>
										</tr>
										<tr>
											<th><?php _e('ポイントの適用範囲', 'wc2'); ?></th>
											<td class="vertical">
												<label title="0"><input name="point_coverage" type="radio" id="point_coverage0" value="0"<?php if( !$general['point_coverage'] ) echo ' checked="checked"'; ?> /><span><?php _e('商品合計額のみに制限', 'wc2'); ?></span></label>
												<label title="1"><input name="point_coverage" type="radio" id="point_coverage1" value="1"<?php if( $general['point_coverage'] ) echo ' checked="checked"'; ?> /><span><?php _e('商品合計額及び手数料などにも適用', 'wc2'); ?></span></label>
											</td>
										</tr>
										<tr>
											<th><?php _e('ポイント付与のタイミング', 'wc2'); ?></th>
											<td class="horizontal">
												<label title="1"><input name="point_assign" id="point_assign_receipt" type="radio" value="1"<?php if($general['point_assign'] == 1) echo ' checked="checked"'; ?> /><span><?php _e('入金時', 'wc2'); ?></span></label>
												<label title="0"><input name="point_assign" id="point_assign_immediately" type="radio" value="0"<?php if( WC2_Utils::is_zero($general['point_assign']) ) echo ' checked="checked"'; ?> /><span><?php _e('即時', 'wc2'); ?></span></label>
											</td>
										</tr>
										<tr>
											<th><?php _e('会員パスワードの文字数制限', 'wc2'); ?></th>
											<td>
												<input type="text" name="member_pass_rule_min" id="member_pass_rule_min" value="<?php esc_attr_e($general['member_pass_rule_min']); ?>" size="3" />&nbsp;<?php _e('文字以上', 'wc2'); ?>
												<input type="text" name="member_pass_rule_max" id="member_pass_rule_max" value="<?php esc_attr_e($general['member_pass_rule_max']); ?>" size="3" />&nbsp;<?php _e('文字以下', 'wc2'); ?>
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div><!--#general_page_setting_3-->

						<div id="general_page_setting_4">
							<div class="postbox">
								<h3 class="hndle"><span><?php _e('商品名の表示ルール','wc2'); ?></span></h3>
								<div class="inside">
									<table class="form-table">
										<tr>
											<th>
												<label for="indi_item_name"><input name="indication[item_name]" type="checkbox" id="indi_item_name" value="<?php echo esc_attr($indi_item_name['item_name']); ?>"<?php echo $checked_item_name['item_name']; ?> />
												<span><?php _e('商品名の表示','wc2'); ?></span></label>
											</th>
											<td>
												<span class="cartname-rules-label"><?php _e('商品名の位置','wc2'); ?></span>
												<input name="position[item_name]" type="text" class="num small-text" id="pos_item_name" value="<?php echo esc_attr($pos_item_name['item_name']); ?>" />(<?php _e('数値','wc2'); ?>)
											</td>
										</tr>
										<tr>
											<th>
												<label for="indi_item_code"><input name="indication[item_code]" type="checkbox" id="indi_item_code" value="<?php echo esc_attr($indi_item_name['item_code']); ?>"<?php echo $checked_item_name['item_code']; ?> />
												<span><?php _e('商品コードの表示','wc2'); ?></span></label>
											</th>
											<td>
												<span class="cartname-rules-label"><?php _e('商品コードの位置','wc2'); ?></span>
												<input name="position[item_code]" type="text" class="num small-text" id="pos_item_code" value="<?php echo esc_attr($pos_item_name['item_code']); ?>" />(<?php _e('数値','wc2'); ?>)
											</td>
										</tr>
										<tr>
											<th>
												<label for="indi_sku_name"><input name="indication[sku_name]" type="checkbox" id="indi_sku_name" value="<?php echo esc_attr($indi_item_name['sku_name']); ?>"<?php echo $checked_item_name['sku_name']; ?> />
												<span><?php _e('SKU名の表示','wc2'); ?></span></label>
											</th>
											<td>
												<span class="cartname-rules-label"><?php _e('SKU名の位置','wc2'); ?></span>
												<input name="position[sku_name]" type="text" class="num small-text" id="pos_sku_name" value="<?php echo esc_attr($pos_item_name['sku_name']); ?>" />(<?php _e('数値','wc2'); ?>)
											</td>
										</tr>
										<tr>
											<th>
												<label for="indi_sku_code"><input name="indication[sku_code]" type="checkbox" id="indi_sku_code" value="<?php echo esc_attr($indi_item_name['sku_code']); ?>"<?php echo $checked_item_name['sku_code']; ?> />
												<span><?php _e('SKUコードの表示','wc2'); ?></span></label>
											</th>
											<td>
												<span class="cartname-rules-label"><?php _e('SKUコードの位置','wc2'); ?></span>
												<input name="position[sku_code]" type="text" class="num small-text" id="pos_sku_code" value="<?php echo esc_attr($pos_item_name['sku_code']); ?>" />(<?php _e('数値','wc2'); ?>)
											</td>
										</tr>
									</table>
								</div>
							</div><!--postbox-->
						</div><!--#general_page_setting_4-->

						<div id="general_page_setting_5">
							<div class="postbox">
								<h3 class="hndle"><span><?php _e('カートページに挿入する説明書き','wc2'); ?></span></h3>
								<div class="inside">
									<table class="form-table phrase-area">
										<tr>
											<th><?php _e('Header','wc2'); ?></th>
											<td><textarea name="cart_header[top]" id="cart_header[top]" class="large-text mail-header"><?php if(isset($cart_page_data['cart_header']['top']) ) echo $cart_page_data['cart_header']['top']; ?></textarea></td>
										</tr>
										<tr>
											<th><?php _e('Footer','wc2'); ?></th>
											<td><textarea name="cart_footer[top]" id="cart_footer[top]" class="large-text mail-footer"><?php if(isset($cart_page_data['cart_footer']['top']) ) echo $cart_page_data['cart_footer']['top']; ?></textarea></td>
										</tr>
									</table>
								</div>
							</div><!--postbox-->

							<div class="postbox">
								<h3 class="hndle"><span><?php _e('お客様情報ページに挿入する説明書き','wc2'); ?></span></h3>
								<div class="inside">
									<table class="form-table phrase-area">
										<tr>
											<th><?php _e('Header','wc2'); ?></th>
											<td><textarea name="cart_header[customer]" id="cart_header[customer]" class="large-text mail-header"><?php if( isset($cart_page_data['cart_header']['customer']) ) echo $cart_page_data['cart_header']['customer']; ?></textarea></td>
										</tr>
										<tr>
											<th><?php _e('Footer','wc2'); ?></th>
											<td><textarea name="cart_footer[customer]" id="cart_footer[customer]" class="large-text mail-footer"><?php if( isset($cart_page_data['cart_footer']['customer']) ) echo $cart_page_data['cart_footer']['customer']; ?></textarea></td>
										</tr>
									</table>
								</div>
							</div><!--postbox-->

							<div class="postbox">
								<h3 class="hndle"><span><?php _e('配送・支払方法ページに挿入する説明書き','wc2'); ?></span></h3>
								<div class="inside">
									<table class="form-table phrase-area">
										<tr>
											<th><?php _e('Header','wc2'); ?></th>
											<td><textarea name="cart_header[delivery]" id="cart_header[delivery]" class="large-text mail-header"><?php if( isset($cart_page_data['cart_header']['delivery']) ) echo $cart_page_data['cart_header']['delivery']; ?></textarea></td>
										</tr>
										<tr>
											<th><?php _e('Footer','wc2'); ?></th>
											<td><textarea name="cart_footer[delivery]" id="cart_footer[delivery]" class="large-text mail-footer"><?php if( isset($cart_page_data['cart_footer']['delivery']) ) echo $cart_page_data['cart_footer']['delivery']; ?></textarea></td>
										</tr>
									</table>
								</div>
							</div><!--postbox-->

							<div class="postbox">
								<h3 class="hndle"><span><?php _e('内容確認ページに挿入する説明書き','wc2'); ?></span></h3>
								<div class="inside">
									<table class="form-table phrase-area">
										<tr>
											<th><?php _e('Header','wc2'); ?></th>
											<td><textarea name="cart_header[confirm]" id="cart_header[confirm]" class="large-text mail-header"><?php if( isset($cart_page_data['cart_header']['confirm']) ) echo $cart_page_data['cart_header']['confirm']; ?></textarea></td>
										</tr>
										<tr>
											<th><?php _e('Footer','wc2'); ?></th>
											<td><textarea name="cart_footer[confirm]" id="cart_footer[confirm]" class="large-text mail-footer"><?php if( isset($cart_page_data['cart_footer']['confirm']) ) echo $cart_page_data['cart_footer']['confirm']; ?></textarea></td>
										</tr>
									</table>
								</div>
							</div><!--postbox-->

							<div class="postbox">
								<h3 class="hndle"><span><?php _e('完了ページに挿入する説明書き','wc2'); ?></span></h3>
								<div class="inside">
									<table class="form-table phrase-area">
										<tr>
											<th><?php _e('Header','wc2'); ?></th>
											<td><textarea name="cart_header[complete]" id="cart_header[complete]" class="large-text mail-header"><?php if( isset($cart_page_data['cart_header']['complete']) ) echo $cart_page_data['cart_header']['complete']; ?></textarea></td>
										</tr>
										<tr>
											<th><?php _e('Footer','wc2'); ?></th>
											<td><textarea name="cart_footer[complete]" id="cart_footer[complete]" class="large-text mail-footer"><?php if( isset($cart_page_data['cart_footer']['complete']) ) echo $cart_page_data['cart_footer']['complete']; ?></textarea></td>
										</tr>
									</table>
								</div>
							</div><!--postbox-->
						</div><!--#general_page_setting_5-->

						<div id="general_page_setting_6">
							<div class="postbox">
								<h3 class="hndle"><span><?php _e('ログインページに挿入する説明書き','wc2'); ?></span></h3>
								<div class="inside">
									<table class="form-table phrase-area">
										<tr>
											<th><?php _e('Header','wc2'); ?></th>
											<td><textarea name="member_header[login]" id="member_header[login]" class="large-text mail-header"><?php if( isset($member_page_data['member_header']['login']) ) echo $member_page_data['member_header']['login']; ?></textarea></td>
										</tr>
										<tr>
											<th><?php _e('Footer','wc2'); ?></th>
											<td><textarea name="member_footer[login]" id="member_footer[login]" class="large-text mail-footer"><?php if( isset($member_page_data['member_footer']['login']) ) echo $member_page_data['member_footer']['login']; ?></textarea></td>
										</tr>
									</table>
								</div>
							</div><!--postbox-->

							<div class="postbox">
								<h3 class="hndle"><span><?php _e('新規会員登録ページに挿入する説明書き','wc2'); ?></span></h3>
								<div class="inside">
									<table class="form-table phrase-area">
										<tr>
											<th><?php _e('Header','wc2'); ?></th>
											<td><textarea name="member_header[newmemberform]" id="member_header[newmemberform]" class="large-text mail-header"><?php if( isset($member_page_data['member_header']['newmemberform']) ) echo $member_page_data['member_header']['newmemberform']; ?></textarea></td>
										</tr>
										<tr>
											<th><?php _e('Footer','wc2'); ?></th>
											<td><textarea name="member_footer[newmemberform]" id="member_footer[newmemberform]" class="large-text mail-footer"><?php if( isset($member_page_data['member_footer']['newmemberform']) ) echo $member_page_data['member_footer']['newmemberform']; ?></textarea></td>
										</tr>
									</table>
								</div>
							</div><!--postbox-->

							<div class="postbox">
								<h3 class="hndle"><span><?php _e('新パスワード取得ページに挿入する説明書き','wc2'); ?></span></h3>
								<div class="inside">
									<table class="form-table phrase-area">
										<tr>
											<th><?php _e('Header','wc2'); ?></th>
											<td><textarea name="member_header[lostpassword]" id="member_header[lostpassword]" class="large-text mail-header"><?php if( isset($member_page_data['member_header']['lostpassword']) ) echo $member_page_data['member_header']['lostpassword']; ?></textarea></td>
										</tr>
										<tr>
											<th><?php _e('Footer','wc2'); ?></th>
											<td><textarea name="member_footer[lostpassword]" id="member_footer[lostpassword]" class="large-text mail-footer"><?php if( isset($member_page_data['member_footer']['lostpassword']) ) echo $member_page_data['member_footer']['lostpassword']; ?></textarea></td>
										</tr>
									</table>
									</div>
							</div><!--postbox-->

							<div class="postbox">
								<h3 class="hndle"><span><?php _e('パスワード変更ページに挿入する説明書き','wc2'); ?></span></h3>
								<div class="inside">
									<table class="form-table phrase-area">
										<tr>
											<th><?php _e('Header','wc2'); ?></th>
											<td><textarea name="member_header[changepassword]" id="member_header[changepassword]" class="large-text mail-header"><?php if( isset($member_page_data['member_header']['changepassword']) ) echo $member_page_data['member_header']['changepassword']; ?></textarea></td>
										</tr>
										<tr>
											<th><?php _e('Footer','wc2'); ?></th>
											<td><textarea name="member_footer[changepassword]" id="member_footer[changepassword]" class="large-text mail-footer"><?php if( isset($member_page_data['member_footer']['changepassword']) ) echo $member_page_data['member_footer']['changepassword']; ?></textarea></td>
										</tr>
									</table>
								</div>
							</div><!--postbox-->

							<div class="postbox">
								<h3 class="hndle"><span><?php _e('会員情報ページに挿入する説明書き','wc2'); ?></span></h3>
								<div class="inside">
									<table class="form-table phrase-area">
										<tr>
											<th><?php _e('Header','wc2'); ?></th>
											<td><textarea name="member_header[memberform]" id="member_header[memberform]" class="large-text mail-header"><?php if( isset($member_page_data['member_header']['memberform']) ) echo $member_page_data['member_header']['memberform']; ?></textarea></td>
										</tr>
										<tr>
											<th><?php _e('Footer','wc2'); ?></th>
											<td><textarea name="member_footer[memberform]" id="member_footer[memberform]" class="large-text mail-footer"><?php if( isset($member_page_data['member_footer']['memberform']) ) echo $member_page_data['member_footer']['memberform']; ?></textarea></td>
										</tr>
									</table>
								</div>
							</div><!--postbox-->

							<div class="postbox">
								<h3 class="hndle"><span><?php _e('完了ページに挿入する説明書き','wc2'); ?></span></h3>
								<div class="inside">
									<table class="form-table phrase-area">
										<tr>
											<th><?php _e('Header','wc2'); ?></th>
											<td><textarea name="member_header[complete]" id="member_header[complete]" class="large-text mail-header"><?php if( isset($member_page_data['member_header']['complete']) ) echo $member_page_data['member_header']['complete']; ?></textarea></td>
										</tr>
										<tr>
											<th><?php _e('Footer','wc2'); ?></th>
											<td><textarea name="member_footer[complete]" id="member_footer[complete]" class="large-text mail-footer"><?php if( isset($member_page_data['member_footer']['complete']) ) echo $member_page_data['member_footer']['complete']; ?></textarea></td>
										</tr>
									</table>
								</div>
							</div><!--postbox-->
						</div>
						<?php do_action('wc2_action_admin_general_body'); ?>
					</div><!--.wc2tabs-->
				</div><!--#poststuff-->
				<div class="update-all-options"><input name="wc2_option_update" type="submit" class="button button-primary" value="<?php _e('Update Settings','wc2'); ?>" /></div>
				<?php wp_nonce_field( 'wc2_setting_general', 'wc2_nonce', false ); ?>
			</form>
		</div><!--general_setting-->
	</div><!--wc2_admin-->
</div><!--wrap-->

 