<?php
/*************************
		支払設定
**************************/
?>
<div class="wrap">
	<div class="wc2_admin">
		<div class="payment-setting">
			<h2><?php _e('支払設定','wc2'); ?></h2>
			<div id="aniboxStatus" class="<?php echo $status; ?>">
				<div id="anibox" class="clearfix">
					<img id="info_image" src="<?php echo WC2_PLUGIN_URL; ?>/common/assets/images/list_message_<?php echo $status; ?>.gif" />
					<div class="mes" id="info_message"><?php echo $message; ?></div>
				</div>
			</div>

			<div id="poststuff" class="metabox-holder">
				<div class="wc2tabs" id="payment-tabs">
					<ul>
						<li><a href="#payment-method"><?php _e('Payment method','wc2'); ?></a></li>
						<li><a href="#bank-setting"><?php _e('銀行振込設定','wc2'); ?></a></li>
						<li><a href="#cod-setting"><?php _e('代引手数料設定','wc2'); ?></a></li>
<!--
						<li><a href="#settlement-zeus"><?php _e('ゼウス','wc2'); ?></a></li>
						<li><a href="#settlement-remise"><?php _e('ルミーズ','wc2'); ?></a></li>
						<li><a href="#settlement-jpayment"><?php _e('Cloud Payment','wc2'); ?></a></li>
						<li><a href="#settlement-paypal-ec"><?php _e('PayPal(EC)','wc2'); ?></a></li>
						<li><a href="#settlement-paypal-wpp"><?php _e('PayPal(WPP)','wc2'); ?></a></li>
						<li><a href="#settlement-sbps"><?php _e('ソフトバンク・ペイメント','wc2'); ?></a></li>
						<li><a href="#settlement-telecom"><?php _e('テレコムクレジット','wc2'); ?></a></li>
						<li><a href="#settlement-digitalcheck"><?php _e('ペイデザイン','wc2'); ?></a></li>
						<li><a href="#settlement-mizuho"><?php _e('みずほファクター','wc2'); ?></a></li>
						<li><a href="#settlement-anotherlane"><?php _e('アナザーレーン','wc2'); ?></a></li>
						<li><a href="#settlement-veritrans"><?php _e('ベリトランス','wc2'); ?></a></li>
						<li><a href="#settlement-paygent"><?php _e('ペイジェント','wc2'); ?></a></li>
-->
						<?php do_action( 'wc2_action_admin_payment_head' ); ?>
					</ul>

					<div id="payment-method">
						<div class="postbox">
							<h3 class="hndle"><?php _e('Payment method','wc2'); ?></h3>
							<div class="inside">

								<table id="payment-table">
									<thead>
									<tr>
										<th class="hdl">　</th>
										<th><?php _e('支払方法名','wc2') ?></th>
										<th><?php _e('説明','wc2') ?></th>
										<th><?php _e('決済種別','wc2') ?></th>
										<th><?php _e('手数料','wc2') ?></th>
									</tr>
									</thead>
									<tbody id="payment-list">
								<?php
									foreach( (array)$payment_method as $id => $data ):
										$this->payment_list_row_e( $data, $id );
									endforeach;
								?>
									</tbody>
								</table>
								<div id="payment-ajax-response"></div>

								<p><strong><?php _e('新しい支払方法を追加','wc2') ?></strong></p>
								<table id="payment-table-add">
									<thead>
									<tr>
										<th><?php _e('支払方法名','wc2') ?></th>
										<th><?php _e('説明','wc2') ?></th>
										<th><?php _e('決済種別','wc2') ?></th>
										<th><?php _e('手数料','wc2') ?></th>
									</tr>
									</thead>
									<tbody>
									<tr>
										<td class="payment-name"><input type="text" id="payment-name-add" class="medium-text" /></td>
										<td class="payment-explanation"><textarea id="payment-explanation-add" class="regular-text"></textarea></td>
										<td class="payment-settlement">
											<select id="payment-settlement-add">
												<option value="<?php echo WC2_UNSELECTED; ?>"><?php _e('-- Select --','wc2'); ?></option>
										<?php foreach( $settlement_types as $key => $type ): ?>
												<option value="<?php esc_attr_e($key); ?>"><?php esc_html_e($type); ?></option>
										<?php endforeach; ?>
											</select>
										</td>
										<td class="payment-charge">
											<select class="payment-charge-select" id="payment-charge-add">
												<option value="none"><?php _e('手数料なし','wc2'); ?></option>
												<option value="cod"><?php _e('代引手数料を適用','wc2'); ?></option>
												<option value="price"><?php _e('金額を適用','wc2'); ?></option>
											</select>
											<span id="payment-charge-price-input-add" style="display:none;"><input type="text" id="payment-charge-price-add" class="small-text right" /><?php echo wc2_crcode(); ?></span>
										</td>
									</tr>
									</tbody>
								</table>
								<div id="payment-button-add"><input type="button" class="button action" id="payment-add" value="<?php _e('新しい支払方法を追加','wc2') ?>" /></div>
								<div id="payment-loading-add"></div>

							</div><!--inside-->
						</div><!--postbox-->
					</div><!--#payment-method-->

					<div id="bank-setting">
						<div class="postbox">
							<h3 class="hndle"><?php _e('銀行振込設定','wc2'); ?></h3>
							<div class="inside">
								<table class="form-table">
									<tr>
										<th><?php _e('振込先口座情報', 'wc2'); ?></th>
										<td><textarea id="transferee" class="regular-text" rows="6"><?php esc_html_e($transferee_info); ?></textarea></td>
									</tr>
								</table>
								<div><input type="button" class="button button-primary" id="bank-update" value="<?php _e('Update Settings','wc2') ?>" /></div>
								<div id="bank-loading"></div>
							</div><!--inside-->
						</div><!--postbox-->
					</div><!--#bank-setting-->

					<div id="cod-setting">
						<div class="postbox">
							<h3 class="hndle"><?php _e('代引手数料設定','wc2'); ?></h3>
							<div class="inside">
								<table class="form-table" id="cod_type_table">
									<tr>
										<th><?php _e('代引手数料のタイプ', 'wc2'); ?></th>
										<td class="horizontal">
											<label title="fix"><input name="cod_type" id="cod_type_fix" type="radio" value="fix"<?php if( 'fix' == $cod_type) echo ' checked="checked"'; ?> /><span><?php _e('固定額', 'wc2'); ?></span></label>
											<label title="change"><input name="cod_type" id="cod_type_change" type="radio" value="change"<?php if( 'change' == $cod_type) echo ' checked="checked"'; ?> /><span><?php _e('変動額', 'wc2'); ?></span></label>
											<table id="cod_fix_table">
												<tr>
													<th><?php _e('手数料', 'wc2'); ?></th>
													<td><input name="cod_fee" type="text" class="medium-text right" value="<?php echo (isset($payment_info['cod_fee']) ? $payment_info['cod_fee'] : ''); ?>" /><?php echo wc2_crcode(); ?></td>
												</tr>
												<tr>
													<th><?php _e('上限額', 'wc2'); ?></th>
													<td><input name="cod_limit_amount" id="cod_limit_amount_fix" type="text" class="medium-text right" value="<?php echo (!empty($payment_info['cod_limit_amount']) ? $payment_info['cod_limit_amount'] : ''); ?>" /><?php echo wc2_crcode(); ?></td>
												</tr>
											</table>
											<div id="cod_change_table">
												<div>
													<input name="addrow" type="button" class="button action" id="add_row" value="<?php _e('行追加', 'wc2'); ?>" />
													<input name="delrow" type="button" class="button action" id="del_row" value="<?php _e('行削除', 'wc2'); ?>" />
												</div>
												<table>
													<thead>
														<tr>
															<th colspan="3"><?php _e('Purchase amount', 'wc2'); ?>(<?php echo wc2_crcode(); ?>)</th>
															<th><?php _e('手数料', 'wc2'); ?>(<?php echo wc2_crcode(); ?>)</th>
														</tr>
														<tr>
															<td class="cod_f">0</td><td class="cod_m">～</td>
															<td class="cod_e"><input name="cod_first_amount" type="text" class="medium-text right" value="<?php esc_attr_e((isset($payment_info['cod_first_amount']) ? $payment_info['cod_first_amount'] : '')); ?>" /></td>
															<td class="cod_cod"><input name="cod_first_fee" type="text" class="medium-text right" value="<?php esc_attr_e((isset($payment_info['cod_first_fee']) ? $payment_info['cod_first_fee'] : '')); ?>" /></td>
														</tr>
													</thead>
													<tbody id="cod_change_field">
											<?php
												if( isset($payment_info['cod_amounts']) && isset($payment_info['cod_fees']) ):
													foreach ( (array)$payment_info['cod_amounts'] as $key => $value ):
											?>
														<tr id="tr_<?php esc_attr_e($key); ?>">
															<td class="cod_f"><span id="amount_<?php esc_attr_e($key); ?>"><?php if( $key === 0 ){echo ((isset($payment_info['cod_first_amount']) ? $payment_info['cod_first_amount'] : 0) + 1);}else{echo ($payment_info['cod_amounts'][($key-1)] + 1);} ?></span></td><td class="cod_m">～</td>
															<td class="cod_e"><input name="cod_amounts[<?php esc_attr_e($key); ?>]" type="text" class="medium-text right" value="<?php esc_attr_e($value); ?>" /></td>
															<td class="cod_cod"><input name="cod_fees[<?php esc_attr_e($key); ?>]" type="text" class="medium-text right" value="<?php esc_attr_e($payment_info['cod_fees'][$key]); ?>" /></td>
														</tr>
											<?php
													endforeach;
												endif;
												if( !isset($payment_info['cod_amounts']) || empty($payment_info['cod_amounts']) ) {
													$end_amount = ( isset($payment_info['cod_first_amount']) ? $payment_info['cod_first_amount'] : 0 ) + 1;
												} else {
													$cod_last = count($payment_info['cod_amounts']) - 1;
													$end_amount = $payment_info['cod_amounts'][$cod_last] + 1;
												}
											?>
													</tbody>
													<tfoot>
														<tr>
															<td class="cod_f"><span id="end_amount"><?php esc_attr_e($end_amount); ?></span></td><td class="cod_m">～</td>
															<td class="cod_e"><input name="cod_limit_amount" id="cod_limit_amount_change" type="text" class="medium-text right" value="<?php esc_attr_e((!empty($payment_info['cod_limit_amount']) ? $payment_info['cod_limit_amount'] : '')); ?>" /></td>
															<td class="cod_cod"><input name="cod_end_fee" type="text" class="medium-text right" value="<?php esc_attr_e((!empty($payment_info['cod_end_fee']) ? $payment_info['cod_end_fee'] : '')); ?>" /></td>
														</tr>
													</tfoot>
												</table>
											</div>
										</td>
									</tr>
								</table>
								<div><input type="button" class="button button-primary" id="cod-update" value="<?php _e('Update Settings','wc2') ?>" /></div>
								<div id="cod-response"></div>
							</div><!--inside-->
						</div><!--postbox-->
					</div><!--#cod-setting-->

					<?php do_action( 'wc2_action_admin_payment_form' ); ?>

				</div><!--.wc2tabs-->
			</div><!--#poststuff-->

			<?php wp_nonce_field( 'wc2_setting_payment', 'wc2_nonce', false ); ?>
		</div><!--.payment-setting-->
	</div><!--.wc2-admin-->
</div><!--.wrap-->
