<?php
/**
 * Welcart2.
 *
 * 受注編集画面
 */
?>
<div class="wrap">
<?php if( 'new' == $this->mode ) : ?>
	<form action="<?php echo add_query_arg( 'action', 'edit' ); ?>" method="post" name="newpost" id="order-edit-form" class="newform"/>
<?php else : ?>
	<form action="<?php echo add_query_arg( array( 'action' => 'edit', 'target' => $order_id ) ); ?>" method="post" name="editpost" id="order-edit-form" class="editform" />
<?php endif; ?>
		<h2><?php esc_html_e( $title ); ?>
			<?php
				$order_list_url = add_query_arg( array( 'action' => 'list' ) );
				$order_list_url = remove_query_arg( 'target', $order_list_url );
			?>
			<a href="<?php echo esc_url( $order_list_url ); ?>" class="add-new-h2"><?php echo esc_html_x( $this->title.'一覧', 'wc2' ); ?></a>
	<?php if( 'new' != $this->mode ) : ?>
			<?php 
				$order_new_url = add_query_arg( array( 'action' => 'new' ) );
				$order_new_url = remove_query_arg( 'target', $order_new_url );
			?>
			<a href="<?php echo esc_url( $order_new_url ); ?>" class="add-new-h2"><?php echo esc_html_x( '新規追加', 'wc2' ); ?></a>
	<?php endif; ?>
		</h2>
	<div id="poststuff" class="wc2_admin">
		<div id="post-body" class="order_post metabox-holder columns-2">
			<p class="version_info">Version <?php echo Welcart2::VERSION; ?></p>
			<div id="aniboxStatus" class="<?php echo $status; ?>">
				<div id="anibox" class="clearfix">
					<?php if( !empty($status) ) : ?>
					<img id="info_image" src="<?php echo WC2_PLUGIN_URL; ?>/common/assets/images/list_message_<?php echo $status; ?>.gif" />
					<?php endif; ?>
					<div class="mes" id="info_message"><?php echo $message; ?></div>
				</div>
			</div>

			<div id="postbox-container-1" class="postbox-container">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<?php do_action( 'wc2_action_admin_order_postbox_container1_0', $data, $this->mode ); ?>
					<div id="submit-box" class="postbox">
						<div class="inside">
							<p class="notes"><?php _e('値を変更した場合は必ず最後に「設定を更新」を押してください', 'wc2'); ?></p>
							<input id="update-order" class="update button button-primary" type="button" value="<?php _e('Update Settings', 'wc2'); ?>" />
						</div>
					</div><!--#submit-box-->
					<?php do_action( 'wc2_action_admin_order_postbox_container1_1', $data, $this->mode ); ?>
					<div id="mail-print-box" class="postbox">
						<div class="handlediv" title="<?php _e('Click to toggle'); ?>"><br /></div>
						<h3 class="hndle"><span><?php _e('メール・印刷フィールド', 'wc2'); ?></span></h3>
						<div class="inside">
							<p class="title dashicons-before dashicons-email-alt"><?php _e('メール送信', 'wc2'); ?></p>
							<ul class="mail-box">
								<?php $yes = 'yes dashicons-before dashicons-yes'; $no = 'no'; ?>
								<li class="mail-order status-<?php if( in_array('mail_order', $order_check) ) {echo $yes;} else {echo $no;}; ?>"><a id="mail-order"><?php _e('注文確認メール', 'wc2'); ?></a></li>
								<li class="mail-change status-<?php if( in_array('mail_change', $order_check) ) {echo $yes;} else {echo $no;}; ?>"><a id="mail-change"><?php _e('変更確認メール', 'wc2'); ?></a></li>
								<li class="mail-receipt status-<?php if( in_array('mail_receipt', $order_check) ) {echo $yes;} else {echo $no;}; ?>"><a id="mail-receipt"><?php _e('入金確認メール', 'wc2'); ?></a></li>
								<li class="mail-estimate status-<?php if( in_array('mail_estimate', $order_check) ) {echo $yes;} else {echo $no;}; ?>"><a id="mail-estimate"><?php _e('見積メール', 'wc2'); ?></a></li>
								<li class="mail-cancel status-<?php if( in_array('mail_cancel',$order_check) ) {echo $yes;} else {echo $no;}; ?>"><a id="mail-cancel"><?php _e('キャンセルメール', 'wc2'); ?></a></li>
								<li class="mail-other status-<?php if( in_array('mail_other',$order_check) ) {echo $yes;} else {echo $no;}; ?>"><a id="mail-other"><?php _e('その他のメール', 'wc2'); ?></a></li>
								<li class="mail-completion status-<?php if( in_array('mail_completion',$order_check) ) {echo $yes;} else {echo $no;}; ?>"><a id="mail-completion"><?php _e('発送完了メール', 'wc2'); ?></a></li>
								<?php do_action( 'wc2_action_admin_order_navibox_mail', $data, $this->mode ); ?>
							</ul>
							<p class="title dashicons-before dashicons-download"><?php _e('PDF印刷', 'wc2'); ?></p>
							<ul class="print-box">
								<li class="print-estimate status-<?php if( in_array('print_estimate',$order_check) ) {echo $yes;} else {echo $no;}; ?>"><a id="print-estimate"><?php _e('見積書印刷', 'wc2'); ?></a></li>
								<li class="print-deliveryslip status-<?php if( in_array('print_deliveryslip',$order_check) ) {echo $yes;} else {echo $no;}; ?>"><a id="print-deliveryslip"><?php _e('納品書印刷', 'wc2'); ?></a></li>
								<li class="print-invoice status-<?php if( in_array('print_invoice',$order_check) ) {echo $yes;} else {echo $no;}; ?>"><a id="print-invoice"><?php _e('請求書印刷', 'wc2'); ?></a></li>
								<li class="print-receipt status-<?php if( in_array('print_receipt',$order_check) ) {echo $yes;} else {echo $no;}; ?>"><a id="print-receipt"><?php _e('領収書印刷', 'wc2'); ?></a></li>
								<?php do_action( 'wc2_action_admin_order_navibox_print', $data, $this->mode ); ?>
							</ul>
							<div class="description">
								送信済み・印刷済みのものには<span class="dashicons-before dashicons-yes"></span>が表示されます。
							</div>
							<p class="notes"><?php _e('※変更がある場合は「設定を更新」を押してから送信・印刷を行ってください', 'wc2'); ?></p>
						</div>
					</div><!--#mail-print-box-->
					<?php do_action( 'wc2_action_admin_order_postbox_container1_2', $data, $this->mode ); ?>
				</div>
			</div><!--#postbox-container-1-->

			<div id="postbox-container-2" class="postbox-container">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">
					<div id="order-information">
						<?php do_action( 'wc2_action_admin_order_postbox_container2_0', $data, $this->mode ); ?>
					<?php
						ob_start();
					?>
						<div id="info-adminorder-box" class="postbox">
							<div class="handlediv" title="<?php _e('Click to toggle'); ?>"><br /></div>
							<h3 class="hndle"><span><?php _e('Administrator Information', 'wc2'); ?></span></h3>
							<div class="inside">
								<table>
								<tr class="order-memo">
									<?php $order_memo = ( !empty($data['meta_key'][ORDER_MEMO]) ) ? esc_attr($data['meta_key'][ORDER_MEMO]) : ''; ?>
									<th><?php _e('Administrator Note', 'wc2'); ?></th>
									<td><textarea name="order_memo" class="large-text" rows="2"><?php echo $order_memo; ?></textarea></td>
								</tr>
								</table>
							</div>
						</div><!--#info-adminorder-box-->
					<?php
						$admin_order_info = ob_get_contents();
						ob_end_clean();
						echo apply_filters( 'wc2_filter_admin_order_info', $admin_order_info, $data, $this->mode );
					?>
						<div id="info-head-box" class="postbox primary-box">
							<h3><span><?php _e($this->title.'明細', 'wc2'); ?></span></h3>
							<div class="inside">
								<table>
								<tr>
									<th class="order-id-label"><?php _e('Order number', 'wc2'); ?><span>(<?php esc_html_e($order_id); ?>)</span></th>
									<td class="order-id-value"><?php esc_html_e($data[ORDER_DEC_ID]); ?></td>
									<th class="order-date-label"><?php _e('Order date', 'wc2'); ?></th>
									<td class="order-date-value"><?php esc_html_e($data[ORDER_DATE]); ?></td>
									<th class="order-modified-label"><?php echo apply_filters( 'wc2_filter_admin_order_modified_label', __('Shipping date', 'wc2') ); ?></th>
									<td class="order-modified-value" id="order_modified"><?php esc_html_e($data[ORDER_MODIFIED]); ?></td>
								</tr>
								</table>
							</div>
						</div><!--#info-head-box-->
						<?php do_action( 'wc2_action_admin_order_postbox_container2_1', $data, $this->mode ); ?>
						<div class="cf">
							<div class="secondary-box">
								<div id="status-box" class="postbox">
									<h3><span><?php _e('Status', 'wc2'); ?></span></h3>
									<div class="inside">
										<table>
										<tr class="order-mode">
											<th><?php _e('Display Mode', 'wc2'); ?></th>
											<td>
										<?php if( is_array($order_condition) and array_key_exists('display_mode', $order_condition) ): ?>
											<?php
												if( $order_condition['display_mode'] == 'Usualsale' ) {
													$display_mode = __('通常セール', 'wc2');
												} elseif( $order_condition['display_mode'] == 'Promotionsale' ) {
													$display_mode = __('キャンペーンセール', 'wc2');
												} else {
													$display_mode = '';
												}
												$display_mode = apply_filters( 'wc2_filter_admin_order_display_mode', $display_mode, $order_condition ); ?>
												<div class="title2"><?php esc_html_e($display_mode); ?></div>
												<div class="condition">
											<?php if( $order_condition['display_mode'] == 'Promotionsale' ) : ?>
												<?php
													if( $order_condition['campaign_privilege'] == 'discount' ) {
														$campaign_privilege = $order_condition['privilege_discount'].__('%引', 'wc2');
													} elseif( $order_condition["campaign_privilege"] == 'point' ) {
														$campaign_privilege = $order_condition['privilege_point'].__('倍、会員のみ', 'wc2');
													} else {
														$campaign_privilege = '';
													}
													if( !isset($order_condition['campaign_category']) || $order_condition['campaign_category'] == 0 ) {
														$campaign_category = __('All Items', 'wc2');
													} else {
														$campaign_category = get_cat_name($order_condition['campaign_category']);
													} ?>
												<span><?php _e('特典', 'wc2'); ?> : </span><?php esc_html_e($campaign_privilege); ?><br />
												<span><?php _e('対象', 'wc2'); ?> : </span><?php esc_html_e($campaign_category); ?><br />
											<?php endif; ?>
												</div>
										<?php endif; ?>
											</td>
										</tr>
										<tr class="order-status">
											<th><?php _e('Order status', 'wc2'); ?></th>
											<td>
											<select name="offer[order_status]" id="order_status">
										<?php foreach( $order_status as $status_key => $status_name ) : ?>
											<option value="<?php esc_attr_e($status_key); ?>"<?php if( $data[ORDER_STATUS] == $status_key ) echo ' selected="selected"'; ?>><?php esc_html_e($status_name); ?></option>
										<?php endforeach; ?>
											</select>
											</td>
										</tr>
										<tr class="receipt-status">
											<th><?php _e('Receipt status', 'wc2'); ?></th>
											<td>
											<select name="offer[receipt_status]">
										<?php foreach( $receipt_status as $status_key => $status_name ) : ?>
											<option value="<?php esc_attr_e($status_key); ?>"<?php if( $data[RECEIPT_STATUS] == $status_key ) echo ' selected="selected"'; ?>><?php esc_html_e($status_name); ?></option>
										<?php endforeach; ?>
											</select>
											</td>
										</tr>
										<tr class="order-type">
											<th><?php _e('Order type', 'wc2'); ?></th>
											<td>
											<select name="offer[order_type]">
										<?php foreach( $order_type as $status_key => $status_name ) : ?>
											<option value="<?php esc_attr_e($status_key); ?>"<?php if( $data[ORDER_TYPE] == $status_key ) echo ' selected="selected"'; ?>><?php esc_html_e($status_name); ?></option>
										<?php endforeach; ?>
											</select>
											</td>
										</tr>
										</table>
									</div>
								</div><!--#status-box-->
								<div id="delivery-method-box" class="postbox">
									<h3><span><?php _e('Delivery and Payment', 'wc2'); ?></span></h3>
									<div class="inside">
										<table>
										<tr class="payment-method">
											<th><?php _e('Payment method', 'wc2'); ?></th>
											<td>
											<select name="offer[payment_method]" id="payment_method_select">
										<?php if( $this->mode == 'new' ) : ?>
												<option value=""><?php _e('-- Select --', 'wc2'); ?></option>
										<?php endif; ?>
										<?php if( '' != $data[ORDER_PAYMENT_METHOD] ) : ?>
												<option value="<?php esc_attr_e($data[ORDER_PAYMENT_METHOD]); ?>"><?php esc_html_e($data[ORDER_PAYMENT_NAME]); ?></option>
										<?php endif; ?>
										<?php
											foreach( (array)$payment_method as $payment ) :
												if( $payment['id'] != $data[ORDER_PAYMENT_METHOD] ) : ?>
												<option value="<?php esc_attr_e($payment['id']); ?>"><?php esc_html_e($payment['name']); ?></option>
												<?php
												endif;
											endforeach; ?>
											</select>
											</td>
										</tr>
										<tr class="delivery-method">
											<th><?php _e('Delivery method','wc2'); ?></th>
											<td>
											<select name="offer[delivery_method]" id="delivery_method_select">
												<option value="-1"><?php _e('No preference', 'wc2'); ?></option>
										<?php
											foreach( (array)$delivery_method as $dkey => $delivery ) :
											$selected = $data[ORDER_DELIVERY_METHOD] == $delivery['id'] ? ' selected="selected"' : ''; ?>
												<option value="<?php esc_attr_e($delivery['id']); ?>"<?php echo $selected; ?>><?php esc_attr_e($delivery['name']); ?></option>
										<?php
											endforeach; ?>
											</select>
											</td>
										</tr>
										<tr class="delivery-date">
											<th><?php _e('Delivery date', 'wc2'); ?></th>
											<td>
											<select name="offer[delivery_date]" id="delivery_date_select">
										<?php
											$delivery_days_select = '<option value="">'.__('No preference', 'wc2').'</option>';
											for( $i = 0; $i < $delivery_after_days; $i++ ) {
												$value = date_i18n( 'Y-m-d', mktime(0,0,0,$order_date[1],$order_date[2]+$i,$order_date[0]) );
												$selected = ( isset($data[ORDER_DELIVERY_DATE]) && $data[ORDER_DELIVERY_DATE] == $value ) ? ' selected="selected"' : '';
												$delivery_days_select .= '<option value="'.$value.'"'.$selected.'>'.$value.'</option>';
											}
										?>
											<?php echo apply_filters( 'wc2_filter_admin_order_delivery_days_select', $delivery_days_select, $data, $delivery_after_days ); ?>
											</select>
											</td>
										</tr>
										<tr class="delivery-time">
											<th><?php _e('Delivery time', 'wc2'); ?></th>
											<td>
											<select name="offer[delivery_time]" id="delivery_time_select"></select>
											</td>
										</tr>
										<tr class="shipping-date">
											<th><?php _e('Shipping schedule date', 'wc2'); ?></th>
											<td>
											<select name="offer[delidue_date]">
												<option value=""><?php _e('Not notify', 'wc2'); ?></option>
										<?php
											for( $i = 0; $i < $delivery_after_days; $i++ ) :
												$value = date_i18n( 'Y-m-d', mktime(0,0,0,date('m'),date('d')+$i,date('Y')) );
												$selected = ( isset($data[ORDER_DELIDUE_DATE] ) && $data[ORDER_DELIDUE_DATE] == $value ) ? ' selected="selected"' : ''; ?>
												<option value="<?php esc_attr_e($value); ?>"<?php echo $selected; ?>><?php esc_html_e($value); ?></option>
										<?php
											endfor; ?>
											</select>
											</td>
										</tr>
										</table>
									</div>
								</div><!--#delivery-method-box-->
							</div>
							<div id="customer-box" class="postbox tertiary-box">
								<h3><span><?php _e('Orderer Information', 'wc2'); ?></span></h3>
								<div class="inside">
									<table>
									<?php if( wc2_is_membersystem_state() ) : ?>
									<tr>
										<th class="member-id-label"><?php _e('Membership ID', 'wc2'); ?></th>
										<td class="member-id-value"><?php esc_html_e($data[ORDER_MEMBER_ID]); ?></td>
									</tr>
									<?php endif; ?>
									<?php echo wc2_get_addressform( $data, 'customer' ); ?>
									<?php do_action( 'wc2_action_admin_order_customer_box', $data, $this->mode ); ?>
									</table>
								</div>
							</div><!--#customer-box-->
							<div id="delivery-box" class="postbox quaternary-box">
								<h3><span><?php _e('Shipping address information', 'wc2'); ?></span></h3>
								<div class="inside">
									<table>
									<?php if( $this->mode == 'new' ) : ?>
									<tr class="delivery-copy-button">
										<th></th>
										<td><input type="button" class="button" id="costomer-copy" value="<?php _e('注文者と同じ', 'wc2'); ?>" /></td>
									</tr>
									<?php endif; ?>
									<?php echo wc2_get_addressform( $data, 'delivery' ); ?>
									<?php do_action( 'wc2_action_admin_order_delivery_box', $data, $this->mode ); ?>
									</table>
								</div>
							</div><!--#delivery-box-->
						</div>
						<?php do_action( 'wc2_action_admin_order_postbox_container2_2', $data, $this->mode ); ?>
						<div id="other-box" class="postbox quinary-box">
							<h3><span><?php _e('Others', 'wc2'); ?></span></h3>
							<div class="inside">
								<table>
								<?php echo wc2_custom_field_input( $data, 'order', 'beforeremarks' ); ?>
								<tr class="order-note">
									<th><?php _e('Notes', 'wc2'); ?></th>
									<td><textarea name="offer[note]" class="large-text" rows="5"><?php echo $data[ORDER_NOTE]; ?></textarea></td>
								</tr>
								<?php echo wc2_custom_field_input( $data, 'order', 'other' ); ?>
								</table>
							</div>
						</div><!--#other-box-->
						<?php do_action( 'wc2_action_admin_order_postbox_container2_3', $data, $this->mode ); ?>
					<?php if( false !== strpos( $payment['settlement'], 'acting' ) ) : ?>
						<div id="info-settlement-box" class="postbox">
							<h3><span><?php _e('Payment information', 'wc2'); ?></span></h3>
							<div class="inside">
								<table>
								</table>
							</div>
						</div><!--#info-settlement-box-->
					<?php endif; ?>
					</div><!--#order-information-->
					<?php do_action( 'wc2_action_admin_order_postbox_container2_4', $data, $this->mode ); ?>
					<div id="order-cart" class="postbox">
						<h3><span><?php _e('明細', 'wc2'); ?></span></h3>
						<div class="inside">
						<?php
							$cart_items = wc2_get_admin_order_cart_row( $order_id, $cart );
							ob_start();
						?>
							<table id="order-cart-table">
							<thead>
							<tr>
								<th scope="row" class="num"><?php _e('No.','wc2'); ?></th>
								<th class="thumbnail"></th>
								<th class="name"><?php _e('Items','wc2'); ?></th>
								<th class="price"><?php _e('Unit price','wc2'); ?></th>
								<th class="quantity"><?php _e('Quantity','wc2'); ?></th>
								<th class="subtotal"><?php _e('Amount','wc2'); ?>(<?php wc2_crcode_e(); ?>)</th>
								<th class="stock"><?php _e('Stock', 'wc2'); ?></th>
								<th class="action"><input id="additem" class="button" type="button" value="<?php _e('Add item', 'wc2'); ?>" /></th>
							</tr>
							</thead>
							<tbody id="order-cart-items">
								<?php echo $cart_items; ?>
							</tbody>
							<tfoot>
							<tr class="item-total">
								<th colspan="5"><?php _e('Total amount of items','wc2'); ?></th>
								<th><span id="item-total">&nbsp;</span></th>
								<th class="notes" colspan="2">&nbsp;</th>
							</tr>
							<?php
								$discount_label = apply_filters( 'wc2_filter_discount_label', __('Discount', 'wc2' ), $data );
								$discount = ( isset($data[ORDER_DISCOUNT]) && !empty($data[ORDER_DISCOUNT]) ) ? wc2_crform( $data[ORDER_DISCOUNT], false, false, false ) : '0'; ?>
							<tr class="discount">
								<td colspan="5"><?php echo $discount_label; ?></td>
								<td><input name="offer[discount]" id="discount" class="text price right" type="text" value="<?php echo $discount; ?>" /></td>
								<td class="notes" colspan="2"><?php _e('※値引は-（マイナス）で入力します', 'wc2'); ?></td>
							</tr>
							<?php if( 'products' == $general_options['tax_target'] ) : 
								$tax = ( isset($data[ORDER_TAX]) && !empty($data[ORDER_TAX]) ) ? wc2_crform( $data[ORDER_TAX], false, false, false ) : '0'; ?>
							<tr class="tax">
								<td colspan="5"><?php wc2_tax_label_e( $data ); ?></td>
								<td><input name="offer[tax]" id="tax" type="text" class="text price right" value="<?php echo $tax; ?>" /></td>
								<td class="notes" colspan="2"><?php _e('※自動計算されません', 'wc2'); ?></td>
							</tr>
							<?php endif; ?>
							<?php
								$shipping_charge = ( isset($data[ORDER_SHIPPING_CHARGE]) && !empty($data[ORDER_SHIPPING_CHARGE]) ) ? wc2_crform( $data[ORDER_SHIPPING_CHARGE], false, false, false ) : '0'; ?>
							<tr class="shipping-charge">
								<td colspan="5"><?php _e('Shipping charges', 'wc2'); ?></td>
								<td><input name="offer[shipping_charge]" id="shipping_charge" class="text price right" type="text" value="<?php echo $shipping_charge; ?>" /></td>
								<td class="notes" colspan="2"><?php _e('※自動計算されません', 'wc2'); ?></td>
							</tr>
							<?php
								$cod_label = apply_filters( 'wc2_filter_cod_label', __('COD fee', 'wc2') );
								$cod_fee = ( isset($data[ORDER_COD_FEE]) && !empty($data[ORDER_COD_FEE]) ) ? wc2_crform( $data[ORDER_COD_FEE], false, false, false ) : '0'; ?>
							<tr class="cod-fee">
								<td colspan="5"><?php echo $cod_label; ?></td>
								<td><input name="offer[cod_fee]" id="cod_fee" class="text price right" type="text" value="<?php echo $cod_fee; ?>" /></td>
								<td class="notes" colspan="2"><?php _e('※自動計算されません', 'wc2'); ?></td>
							</tr>
							<?php if( 'all' == $general_options['tax_target'] ) : 
									$tax = ( isset($data[ORDER_TAX]) && !empty($data[ORDER_TAX]) ) ? wc2_crform( $data[ORDER_TAX], false, false, false ) : '0'; ?>
							<tr class="tax">
								<td colspan="5"><?php wc2_tax_label_e( $data ); ?></td>
								<td><input name="offer[tax]" id="tax" class="text price right" type="text" value="<?php echo $tax; ?>" /></td>
								<td class="notes" colspan="2"><?php _e('※自動計算されません', 'wc2'); ?></td>
							</tr>
							<?php endif; ?>
							<?php if( wc2_is_membersystem_state() && wc2_is_membersystem_point() ) : 
									$usedpoint = ( isset($data[ORDER_USEDPOINT]) && !empty($data[ORDER_USEDPOINT]) ) ? $data[ORDER_USEDPOINT] : '0';
									$getpoint = ( isset($data[ORDER_GETPOINT]) && !empty($data[ORDER_GETPOINT]) ) ? $data[ORDER_GETPOINT] : '0'; ?>
							<tr class="used-point">
								<td colspan="5"><?php _e('Used points','wc2'); ?></td>
								<td><input name="offer[usedpoint]" id="usedpoint" class="text price red right" type="text" value="<?php echo $usedpoint; ?>" /></td>
								<td><?php _e('Granted points', 'wc2'); ?></td>
								<td><input name="offer[getpoint]" id="getpoint" class="text price right" type="text" value="<?php echo $getpoint; ?>" /></td>
							</tr>
							<?php endif; ?>
							<tr class="total-price">
								<th colspan="5"><?php _e('Total Amount','wc2'); ?></th>
								<th id="total">&nbsp;</th>
								<th class="notes" colspan="2"><input id="recalc" class="button" type="button" value="<?php _e('Recalculation', 'wc2'); ?>" /></th>
							</tr>
							</tfoot>
							</table>
						<?php
							$cart_table = ob_get_contents();
							ob_end_clean();
							echo apply_filters( 'wc2_filter_admin_order_cart_table', $cart_table, $data, $this->mode );
						?>
							<input name="offer[delivery_name]" id="delivery_name" type="hidden" value="<?php esc_attr_e($data[ORDER_DELIVERY_NAME]); ?>" />
							<input name="offer[payment_name]" id="payment_name" type="hidden" value="<?php esc_attr_e($data[ORDER_PAYMENT_NAME]); ?>" />
							<input name="offer[item_total_price]" id="item_total_price" type="hidden" value="<?php esc_attr_e($item_total_price); ?>" />
							<input name="delivery[deli_id]" type="hidden" value="<?php echo $data['delivery'][0][ORDER_DELIVERY_ID]; ?>" />
							<input name="order_action" id="order_action" type="hidden" value="<?php esc_attr_e($order_action); ?>" />
							<input name="order_id" id="order_id" type="hidden" value="<?php esc_attr_e($order_id); ?>" />
							<input name="member_id" id="member_id" type="hidden" value="<?php esc_attr_e($data[ORDER_MEMBER_ID]); ?>" />
							<input name="cart_row" id="cart_row" type="hidden" value="<?php echo $cart_row; ?>" />
							<?php if( wc2_is_membersystem_state() && wc2_is_membersystem_point() ) : ?>
							<input name="old_usedpoint" type="hidden" value="<?php echo $usedpoint; ?>" />
							<input name="old_getpoint" type="hidden" value="<?php echo $getpoint; ?>" />
							<?php else: ?>
							<input name="offer[usedpoint]" id="usedpoint" type="hidden" value="0" />
							<input name="offer[getpoint]" id="getpoint" type="hidden" value="0" />
							<?php endif; ?>
							<input name="modified" id="modified" type="hidden" value="<?php esc_attr_e(isset($data[ORDER_MODIFIED]) ? $data[ORDER_MODIFIED] : ''); ?>" />
							<input name="up_modified" id="up_modified" type="hidden" value="" />
						</div>
					</div><!--#order-cart-->
					<?php do_action( 'wc2_action_admin_order_postbox_container2_5', $data, $this->mode ); ?>
				</div>
			</div><!--#postbox-container-2-->
		</div><!--#post-body-->

		<!-- When open the dialog, "appendto" this tag. -->
		<div id="dialog-parent"></div>

		<!--商品追加-->
		<div id="addItemDialog" title="<?php _e('Add item', 'wc2'); ?>">
			<div id="additem-response"></div>
			<fieldset>
				<div class="clearfix">
					<div class="dialogsearch">
						<label><?php _e('Items Category', 'wc2'); ?></label>
						<?php
							$args = array( 'show_option_none' => 'カテゴリーを選択してください', 'name' => 'additem_category', 'id' => 'additem-category', 'hide_empty' => 1, 'hierarchical' => 1, 'orderby' => 'name', 'taxonomy' => 'item' );
							$args = apply_filters( 'wc2_filter_admin_order_additem_category', $args );
							wp_dropdown_categories( $args ); ?>
						<br />
						<label><?php _e('追加する商品', 'wc2'); ?></label>
						<select name="additem_select" id="additem-select"></select><br />
						<label for="name"><?php _e('Item code', 'wc2'); ?></label>
						<input type="text" name="additem_code" id="additem-code" class="regular-text text" />
						<input name="getitem" id="getitem" type="button" class="button" value="<?php _e('取得', 'wc2'); ?>" />
						<div id="additem-loading"></div>
					</div>
					<div id="additem-form"></div>
				</div>
			</fieldset>
		</div>

		<!--メール送信-->
		<div id="sendMailDialog" title="">
			<div id="sendmail-response"></div>
			<fieldset>
				<p><?php _e('メールの内容を確認して「送信」を押してください', 'wc2'); ?></p>
				<label><?php _e('メールアドレス', 'wc2'); ?></label><input type="text" name="sendmail_address" id="sendmail-address" class="regular-text" /><br />
				<label><?php _e('お客様名', 'wc2'); ?></label><input type="text" name="sendmail_name" id="sendmail-name" class="regular-text" /><br />
				<label><?php _e('件名', 'wc2'); ?></label><input type="text" name="sendmail_subject" id="sendmail-subject" class="regular-text" /><input name="sendmail" id="sendmail" type="button" class="button button-primary" value="<?php _e('送信', 'wc2'); ?>" /><br />
				<textarea name="sendmail_message" id="sendmail-message" class="large-text"></textarea>
				<input name="mail_checked" id="mail-checked" type="hidden" />
			</fieldset>
		</div>
		<div id="sendMailAlert" title="">
			<fieldset>
			</fieldset>
		</div>

		<!--PDF印刷-->
		<div id="PDFDialog" title="">
			<div id="pdf-response"></div>
			<fieldset>
				<div id="new-pdf"></div>
			</fieldset>
		</div>

		<?php do_action( 'wc2_action_admin_order_dialog_form', $data, $this->mode ); ?>
	</div><!--#wc2_admin-->
	<input name="wc2_referer" type="hidden" id="wc2_referer" value="<?php if(isset($_REQUEST['wc2_referer'])) echo $_REQUEST['wc2_referer']; ?>" />
	<?php wp_nonce_field( 'wc2_order_post', 'wc2_nonce', false ); ?>
	</form>
</div><!--#wrap-->
