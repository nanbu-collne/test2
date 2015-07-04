<?php
	$entry_data = wc2_get_entry();
	$payment = wc2_get_payment( $entry_data['order']['payment_method'] );
	$transaction_key = wc2_transaction_key();
	$cart = wc2_get_cart();

	ob_start();
	//if( 'acting' != $payment['settlement'] || 0 == $entry_data['order']['total_price'] ) :
?>
		<form id="cart-form-confirm" action="<?php wc2_cart_url_e(); ?>" method="post">
		<div class="send">
			<input type="button" class="button back" id="bk2delivery" value="戻る">
			<input type="button" class="button action" id="purchase" value="購入する">
			<input type="hidden" name="wcaction" value="purchase_process">
		</div>
		<?php wp_nonce_field( 'wc2_purchase', 'wc2_nonce' ); ?>
		<?php do_action( 'wc2_action_confirm_page_form_inside' ); ?>
		</form>
		<?php do_action( 'wc2_action_confirm_page_form_outside' ); ?>
<?php
	//endif;
	$html = ob_get_contents();
	ob_end_clean();
?>
