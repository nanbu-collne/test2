<?php
function wc2_get_send_out_date() {
	$general = wc2_get_option( 'general' );
	$delivery = wc2_get_option( 'delivery' );
	$shipping_rule = wc2_get_option( 'shipping_rule' );

	$bus_day_arr = (isset($general['business_days'])) ? $general['business_days'] : false;
	list( $today_year, $today_month, $today_day, $hour, $minute, $second ) = preg_split( '([^0-9])', current_time('mysql') );
	if( !is_array($bus_day_arr) ) {
		$today_bus_flag = 1;
	} else {
		$today_bus_flag = isset($bus_day_arr[(int)$today_year][(int)$today_month][(int)$today_day]) ? (int)$bus_day_arr[(int)$today_year][(int)$today_month][(int)$today_day] : 1;
	}

	// get the time limit addition
	$limit_hour = (!empty($delivery['delivery_time_limit']['hour'])) ? $delivery['delivery_time_limit']['hour'] : false;
	$limit_min = (!empty($delivery['delivery_time_limit']['min'])) ? $delivery['delivery_time_limit']['min'] : false;

	if( false === $hour || false === $minute ) {
		$time_limit_addition = false;
	} elseif( ($hour.':'.$minute.':'.$second) > ($limit_hour.':'.$limit_min.':00') ) {
		$time_limit_addition = 1;
	} else {
		$time_limit_addition = 0;
	}

	// get the shipping indication in cart
	$cart = wc2_get_cart();
	$shipping_indication = apply_filters( 'wc2_filter_shipping_indication', $shipping_rule['indication'] );
	$shipping = 0;
	$indication_flag = true;
	foreach( $cart as $key => $cart_row ) {
		$shipment = wc2_get_item_value_by_item_id( $cart_row['item_id'], ITEM_PREPARATIONS_SHIPMENT );
		if( $shipment === 0 or $shipment === 9 ) {
			$indication_flag = false;
			break;
		}
		if( $shipping < $shipment ) $shipping = $shipment;
	}
	$indication_incart = ( $indication_flag ) ? $shipping_indication[$shipping] : false;
	$indication_incart = apply_filters( 'wc2_filter_indication_incart', $indication_incart, $shipping_indication, $shipping, $cart );

	// get the send out date
	$sendout_num = 0;
	if( $today_bus_flag ) {
		if( $time_limit_addition ) {
			$sendout_num += 1;
		}
		if( false !== $indication_incart ) {
			$sendout_num += $indication_incart;
		}
	} else {
		if( false !== $indication_incart ) {
			$sendout_num += $indication_incart;
		}
	}
	$holiday = 0;
	for( $i = 0; $i <= $sendout_num; $i++ ) {
		list($yyyy, $mm, $dd) = explode('-', date('Y-m-d', mktime(0,0,0,(int)$today_month,($today_day + $i),(int)$today_year)));
		if( isset($bus_day_arr[(int)$yyyy][(int)$mm][(int)$dd]) && !$bus_day_arr[(int)$yyyy][(int)$mm][(int)$dd] ) {
			$holiday++;
			$sendout_num++;
		}
		if( 100 < $sendout_num ) break;
	}
	list($send_y, $send_m, $send_d) = explode('-', date('Y-m-d', mktime(0,0,0,(int)$today_month,($today_day + $sendout_num),(int)$today_year)));

	$sendout = array(
		'today_bus_flag'      => $today_bus_flag, 
		'time_limit_addition' => $time_limit_addition, 
		'indication_incart'   => $indication_incart, 
		'holiday'             => $holiday, 
		'sendout_num'         => $sendout_num, 
		'sendout_date'        => array('year' => $send_y, 'month' => $send_m, 'day' => $send_d)
	);
	return $sendout;
}

function wc2_delivery_field() {
	$entry_data = wc2_get_entry();
	ob_start();
?>
		<table class="customer-form" id="delivery-field">
			<tr>
				<th class="delivery-method"><?php _e('Delivery method', 'wc2'); ?></th>
				<td><?php wc2_the_delivery_method_e( $entry_data['order']['delivery_method'] ); ?></td>
			</tr>
			<tr>
				<th class="delivery-date"><?php _e('Delivery date', 'wc2'); ?></th>
				<td><?php wc2_the_delivery_date_e( $entry_data['order']['delivery_date'] ); ?></td>
			</tr>
			<tr>
				<th class="delivery-time"><?php _e('Delivery time', 'wc2'); ?></th>
				<td><?php wc2_the_delivery_time_e( $entry_data['order']['delivery_time'] ); ?></td>
			</tr>
			<tr>
				<th class="payment-method"><span class="required"><?php _e('*', 'wc2'); ?></span><?php _e('Payment method', 'wc2'); ?></th>
				<td><?php wc2_the_payment_method_e( $entry_data['order']['payment_method'] ); ?></td>
			</tr>
		</table>
<?php
	$html = ob_get_contents();
	ob_end_clean();
	return apply_filters( 'wc2_filter_delivery_field', $html, $entry_data );
}

function wc2_delivery_field_e() {
	echo wc2_delivery_field();
}

//Univarsal Analytics( Yoast )
function wc2_Universal_trackPageview_by_Yoast($push){
	$page_type = wc2_get_current_page_type();
	$page = wc2_get_current_page();
	$action = isset($_REQUEST['wcaction']) ? $_REQUEST['wcaction']: '';
	$row = array();
	if( 'cart' == $page_type && 'complete' == $page && 'purchase_process' == $action ){
		$entry_data =  wc2_get_entry();
		$order_id = $entry_data['order']['ID'];
		$data = wc2_get_order_data( $order_id );
		$cart = $data['cart'];
		$total_price = $data['item_total_price'] + $data['discount'] - $data['usedpoint'];
		//$row[] = "'send', 'pageview', {'page' : '/wc_ordercompletion'}";
		$row[] = "'require', 'ecommerce', 'ecommerce.js'";
		$row[] = "'ecommerce:addTransaction', { 
						id: '". $order_id ."', 
						affiliation: '". get_option('blogname') ."',
						revenue: '". $total_price ."',
						shipping: '". $data['shipping_charge'] ."',
						tax: '". $data['tax'] ."'
					}";
		foreach( $cart as $index => $cart_row ){
			$skuName = urldecode($cart_row['sku_name']);
			$itemName = $cart_row['item_name'];
			$post_id = $cart_row['post_id'];
			$cats = wc2_get_item_cat_genre_ids($post_id);

			if( is_array($cats) )
				sort($cats);

			$category = isset($cats[0]) ? get_term($cats[0], 'item'): '';
			$catName = isset($category->name) ? $category->name: '';
			$skuPrice = $cart_row['price'];
			$quantity = $cart_row['quantity'];

			$row[] = "'ecommerce:addItem', {
				id: '". $order_id ."',
				sku: '". $skuName ."',
				name: '". $itemName."',
				category: '". $catName."',
				price: '". $skuPrice."',
				quantity: '". $quantity."'
			}";
		}
		$row[] = "'ecommerce:send'";
		//break;
	}
	$row = apply_filters('wc2_filter_Universal_trackPageview_by_Yoast', $row, $page_type, $page, $action);
	$push = array_merge( $push, $row );

	return $push;
}

//Classic Analytics ( Yoast )
function wc2_Classic_trackPageview_by_Yoast($push){
	$page_type = wc2_get_current_page_type();
	$page = wc2_get_current_page();
	$action = isset($_REQUEST['wcaction']) ? $_REQUEST['wcaction']: '';
	$row = array();
	if( 'cart' == $page_type && 'complete' == $page && 'purchase_process' == $action ){
		$entry_data =  wc2_get_entry();
		$order_id = $entry_data['order']['ID'];
		$data = wc2_get_order_data( $order_id );
		$cart = $data['cart'];
		$total_price = $data['item_total_price'] + $data['discount'] - $data['usedpoint'];

		$row[] = "'_addTrans', '" . $order_id . "', '" . get_option('blogname') . "', '" . $total_price . "', '" . $data['tax'] . "', '" . $data['shipping_charge'] . "', '" . $data['address1'] . $data['address2'] . "', '" . $data['pref'] . "', '" . get_locale() . "'";
		foreach( $cart as $index => $cart_row ){
			$skuName = urldecode($cart_row['sku_name']);
			$itemName = $cart_row['item_name'];
			$post_id = $cart_row['post_id'];
			$cats = wc2_get_item_cat_genre_ids($post_id);

			if( is_array($cats) )
				sort($cats);

			$category = isset($cats[0]) ? get_term($cats[0], 'item'): '';
			$catName = isset($category->name) ? $category->name: '';
			$skuPrice = $cart_row['price'];
			$quantity = $cart_row['quantity'];

			$row[] = "'_addItem', '" . $order_id . "', '" . $skuName . "', '" . $itemName . "', '" . $catName . "', '" . $skuPrice . "', '" . $quantity . "'";
		}

		$row[] = "'_trackTrans'";
	}
	$row = apply_filters('wc2_filter_Classic_trackPageview_by_Yoast', $row, $page_type, $page, $action);
	$push = array_merge( $push, $row );

	return $push;
}

