<?php
/**
 *  class CART.
 *
 * @package   Welcart2
 * @author    Collne Inc. <author@collne.com>
 * @license   GPL-2.0+
 * @link      http://www.welcart2.com
 * @copyright 2014 Collne Inc.
 */

class WC2_Cart {

	/**
	 * Instance of this class.
	 */
	protected static $instance = null;
	protected static $slug = '';
	protected static $add2cart = 0;

	/**
	 * Constructor
	 *
	 */
	function __construct( $slug = '' ) {
		if( $slug == '' ) {
			$slug = apply_filters( 'wc2_filter_cart_slug', 'cart' );
		}
		$this->set_cart_slug( $slug );
		if( !isset( $_SESSION[WC2][$slug] ) )
			$_SESSION[WC2][$slug] = array();
		$general = wc2_get_option( 'general' );
		self::$add2cart = $general['add2cart'];
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance( $slug = '' ) {

		// If the single instance hasn't been set, set it now.
		//if ( null == self::$instance or '' == self::$slug or ( '' != $slug and $slug != self::$slug ) ) {
		if ( null == self::$instance ) {
			self::$instance = new self( $slug );
		}

		return self::$instance;
	}

	/**
	 * Add to cart.
	 *
	 */
	public function add2cart( $slug = '' ) {
		if( !isset($_REQUEST['item_id']) or !isset($_REQUEST['sku_id']) )
			return;

		if( empty($slug) ) $slug = self::get_cart_slug();
		$item_id = $_REQUEST['item_id'];
		$sku_id = $_REQUEST['sku_id'];

		$item_sku_data = wc2_get_item_sku_data( $item_id, $sku_id );
		$post_id = $item_sku_data[ITEM_POST_ID];
		$item_code = $item_sku_data[ITEM_CODE];
		$item_name = $item_sku_data[ITEM_NAME];
		$sku_code = $item_sku_data[ITEM_SKU_CODE];
		$sku_name = $item_sku_data[ITEM_SKU_NAME];
		$quantity = $_REQUEST['quantity'];
		$price = wc2_get_item_sku_price( $item_id, $sku_id );
		$price = apply_filters( 'wc2_filter_add2cart_price', $price, $quantity, $item_id, $sku_id, $slug );
		$cprice = $item_sku_data[ITEM_SKU_COSTPRICE];
		$unit = $item_sku_data[ITEM_SKU_UNIT];
		$tax = 0;
		$meta_type = apply_filters( 'wc2_filter_add2cart_meta_type', array(), $quantity, $item_id, $sku_id, $slug );
		$meta_key = apply_filters( 'wc2_filter_add2cart_meta_key', array(), $quantity, $item_id, $sku_id, $slug );

		if( array_key_exists( $slug, $_SESSION[WC2] ) ) {
			$cart_row = array(
				'post_id' => $post_id,
				'item_id' => $item_id,
				'item_code' => $item_code,
				'item_name' => $item_name,
				'sku_id' => $sku_id,
				'sku_code' => $sku_code,
				'sku_name' => $sku_name,
				'quantity' => $quantity,
				'price' => $price,
				'cprice' => $cprice,
				'unit' => $unit,
				'tax' => $tax,
				'meta_type' => $meta_type,
				'meta_key' => $meta_key );
			$cart_row = apply_filters( 'wc2_filter_add2cart', $cart_row, $slug );
			self::set_cart( $cart_row, $slug );
		}
	}

	public static function get_cart_slug() {
		return self::$slug;
	}

	public static function set_cart_slug( $slug ) {
		self::$slug = $slug;
	}

	public function clear_cart( $slug = '' ) {
		if( empty($slug) ) $slug = self::get_cart_slug();
		if( array_key_exists( $slug, $_SESSION[WC2] ) ) {
			unset( $_SESSION[WC2][$slug] );
		}
	}

	public function create_cart( $slug = '' ) {
		if( !array_key_exists( $slug, $_SESSION[WC2] ) ) {
			$_SESSION[WC2][$slug] = array();
		}
	}

	public function get_cart( $slug = '' ) {
		if( empty($slug) ) $slug = self::get_cart_slug();
		$cart = array();
		if( array_key_exists( $slug, $_SESSION[WC2] ) ) {
			$cart = $_SESSION[WC2][$slug];
		}
		return $cart;
	}

	public function set_cart( $cart_row, $slug = '' ) {
		if( empty($slug) ) $slug = self::get_cart_slug();
		if( array_key_exists( $slug, $_SESSION[WC2] ) ) {
			foreach( $_SESSION[WC2][$slug] as $idx => $row ) {
				if( $row['item_id'] == $cart_row['item_id'] and $row['sku_id'] == $cart_row['sku_id'] ) {
					if( self::$add2cart == '1' ) {
						$_SESSION[WC2][$slug][$idx]['quantity'] = $cart_row['quantity'];
					} else {
						$_SESSION[WC2][$slug][$idx]['quantity'] += $cart_row['quantity'];
					}
					$_SESSION[WC2][$slug][$idx]['price'] = $cart_row['price'];
					$_SESSION[WC2][$slug][$idx]['meta_type'] = serialize($cart_row['meta_type']);
					$_SESSION[WC2][$slug][$idx]['meta_key'] = serialize($cart_row['meta_key']);
					return;
				}
			}
			$idx = ( 0 < count($_SESSION[WC2][$slug]) ) ? max( array_keys( $_SESSION[WC2][$slug] ) ) + 1 : 1;
			$_SESSION[WC2][$slug][$idx]['post_id'] = $cart_row['post_id'];
			$_SESSION[WC2][$slug][$idx]['item_id'] = $cart_row['item_id'];
			$_SESSION[WC2][$slug][$idx]['item_code'] = $cart_row['item_code'];
			$_SESSION[WC2][$slug][$idx]['item_name'] = $cart_row['item_name'];
			$_SESSION[WC2][$slug][$idx]['sku_id'] = $cart_row['sku_id'];
			$_SESSION[WC2][$slug][$idx]['sku_code'] = $cart_row['sku_code'];
			$_SESSION[WC2][$slug][$idx]['sku_name'] = $cart_row['sku_name'];
			$_SESSION[WC2][$slug][$idx]['quantity'] = $cart_row['quantity'];
			$_SESSION[WC2][$slug][$idx]['price'] = $cart_row['price'];
			$_SESSION[WC2][$slug][$idx]['cprice'] = $cart_row['cprice'];
			$_SESSION[WC2][$slug][$idx]['unit'] = $cart_row['unit'];
			$_SESSION[WC2][$slug][$idx]['tax'] = $cart_row['tax'];
			$_SESSION[WC2][$slug][$idx]['meta_type'] = serialize($cart_row['meta_type']);
			$_SESSION[WC2][$slug][$idx]['meta_key'] = serialize($cart_row['meta_key']);
		}
	}

	/**
	 * Update the quantity.
	 *
	 */
	public function update_cart( $slug = '' ) {
		if( !isset($_POST['cart_id']) )
			return;

		if( empty($slug) ) $slug = self::get_cart_slug();
		foreach( $_POST['cart_id'] as $idx ) {
			$quantity = ( isset($_POST['quantity'][$idx]) ) ? $_POST['quantity'][$idx] : 0;
			if( 0 != $quantity ) {
				$item_id = ( isset($_POST['item_id'][$idx]) ) ? $_POST['item_id'][$idx] : 0;
				$sku_id = ( isset($_POST['sku_id'][$idx]) ) ? $_POST['sku_id'][$idx] : 0;
				$price = ( isset($_POST['sku_price'][$idx]) ) ? $_POST['sku_price'][$idx] : 0;
				$meta_type = ( isset($_POST['meta_type'][$idx]) ) ? $_POST['meta_type'][$idx] : array();
				$meta_key = ( isset($_POST['meta_key'][$idx]) ) ? $_POST['meta_key'][$idx] : array();
				$_SESSION[WC2][$slug][$idx]['quantity'] = $quantity;
				$_SESSION[WC2][$slug][$idx]['price'] = apply_filters( 'wc2_filter_updatecart_price', $price, $quantity, $item_id, $sku_id, $slug );
				$_SESSION[WC2][$slug][$idx]['meta_type'] = apply_filters( 'wc2_filter_updatecart_meta_type', $meta_type, $quantity, $item_id, $sku_id, $slug );
				$_SESSION[WC2][$slug][$idx]['meta_key'] = apply_filters( 'wc2_filter_updatecart_meta_key', $meta_key, $quantity, $item_id, $sku_id, $slug );
			}
		}
	}

	/**
	 * Remove the item in cart.
	 *
	 */
	public function remove_cart( $slug = '' ) {
		if( !isset($_REQUEST['cart_key']) )
			return;

		if( empty($slug) ) $slug = self::get_cart_slug();
		if( array_key_exists( $slug, $_SESSION[WC2] ) ) {
			if( array_key_exists( $_REQUEST['cart_key'], $_SESSION[WC2][$slug] ) ) {
				unset( $_SESSION[WC2][$slug][$_REQUEST['cart_key']] );
			}
		}
	}
}

function wc2_add2cart_button() {
	$item_id = wc2_get_the_item_id();
	$sku_id = wc2_get_the_item_sku_id();
	$label = apply_filters( 'wc2_filter_add2cart_button_label', __('Add to Shopping Cart', 'wc2') );
	$field = '<input type="button" class="add2cartbutton" id="add2cart-'.$item_id.'-'.$sku_id.'" value="'.$label.'" />';
	$wcfield = '<input type="hidden" name="wcreferer" value="'.$_SERVER['REQUEST_URI'].'" />
		<input type="hidden" name="wcaction" value="add2cart" />
		<input type="hidden" name="item_id" value="'.$item_id.'" />
		<input type="hidden" name="sku_id" value="'.$sku_id.'" />';
	$html = apply_filters( 'wc2_filter_add2cart_button', $field, $item_id, $sku_id ).$wcfield;
	return $html;
}

function wc2_add2cart_button_e() {
	echo wc2_add2cart_button();
}

function wc2_quantity_field() {
	$item_id = wc2_get_the_item_id();
	$sku_id = wc2_get_the_item_sku_id();
	$label = apply_filters( 'wc2_filter_quantity_field_label', __('Quantity', 'wc2') );
	$field = '<input type="number" class="quantity" name="quantity" id="quantity-'.$item_id.'-'.$sku_id.'" value="1" min="1" />';
	$html = apply_filters( 'wc2_filter_quantity_field', $label.$field, $item_id, $sku_id );
	return $html;
}

function wc2_quantity_field_e() {
	echo wc2_quantity_field();
}

function wc2_clear_cart( $slug = '' ) {
	$wc2_cart = WC2_Cart::get_instance( $slug );
	$wc2_cart->clear_cart( $slug );
}

function wc2_create_cart( $slug = '' ) {
	$wc2_cart = WC2_Cart::get_instance( $slug );
	$wc2_cart->create_cart( $slug );
}

function wc2_set_cart( $cart_row, $slug = '' ) {
	$wc2_cart = WC2_Cart::get_instance( $slug );
	$wc2_cart->set_cart( $cart_row, $slug );
}

function wc2_get_cart( $slug = '' ) {
	$wc2_cart = WC2_Cart::get_instance( $slug );
	$cart = $wc2_cart->get_cart( $slug );
	return $cart;
}

function wc2_add2cart( $slug = '' ) {
	$wc2_cart = WC2_Cart::get_instance( $slug );
	$wc2_cart->add2cart( $slug );
}

function wc2_update_cart( $slug = '' ) {
	$wc2_cart = WC2_Cart::get_instance( $slug );
	$wc2_cart->update_cart( $slug );
}

function wc2_remove_cart( $slug = '' ) {
	$wc2_cart = WC2_Cart::get_instance( $slug );
	$wc2_cart->remove_cart( $slug );
}

function wc2_is_cart( $slug = '' ) {
	$wc2_cart = WC2_Cart::get_instance( $slug );
	$cart = $wc2_cart->get_cart( $slug );
	$res = ( is_array($cart) and 0 < count($cart) ) ? true : false;
	return $res;
}

function wc2_cart_table( $slug = '' ) {
	$wc2_cart = WC2_Cart::get_instance( $slug );
	$cart = $wc2_cart->get_cart( $slug );
	$cart_num = count($cart);
	$total_price = 0;
	$num = 0;

	ob_start();
?>
	<div class="cart-update"><?php _e('数量を変更した場合は必ず数量更新ボタンを押してください。','wc2'); ?><input type="button" class="button" id="cart-update" value="<?php _e('数量更新','wc2'); ?>" /></div>
	<table class="cart-table">
		<thead>
		<tr>
<?php 
	$cart_header = '
			<th scope="row" class="num">No.</th>
			<th class="thumbnail">&nbsp;&nbsp;</th>
			<th class="name">'.__('Items','wc2').'</th>
			<th class="price">'.__('Unit price','wc2').'</th>
			<th class="quantity">'.__('Quantity','wc2').'</th>
			<th class="subtotal">'.__('Amount','wc2').'</th>
			<th class="stock">'.__('Stock','wc2').'</th>
			<th class="action">&nbsp;</th>';
	$cart_header = apply_filters( 'wc2_filter_cart_header', $cart_header, $cart, $slug );
	echo $cart_header;
?>
		</tr>
		</thead>
<?php if( 0 < $cart_num ): ?>
		<tbody>
	<?php foreach( $cart as $idx => $row ): ?>
		<tr>
<?php
			$post_id = $row['post_id'];
			$item_id = $row['item_id'];
			$item_name = $row['item_name'];
			$item_code = $row['item_code'];
			$sku_name = $row['sku_name'];
			$sku_code = $row['sku_code'];
			$sku_id = $row['sku_id'];
			$quantity = $row['quantity'];
			$price = $row['price'];
			$subtotal = $row['quantity'] * $row['price'];
			$total_price += $subtotal;
			$meta_type = ( isset( $row['meta_type'] ) ) ? maybe_unserialize($row['meta_type']) : array();
			$meta_key = ( isset( $row['meta_key'] ) ) ? maybe_unserialize($row['meta_key']) : array();

			$item_sku_data = wc2_get_item_sku_data( $item_id, $sku_id );
			$stock_status = $item_sku_data['stock_status'];
			$stock = $item_sku_data['sku_stock'];
			$pictid = wc2_get_mainpictid( $item_code );
			$cart_thumbnail_url = get_permalink( $post_id );
			$cart_thumbnail_url = apply_filters( 'wc2_filter_cart_row_thumbnail_url', $cart_thumbnail_url, $post_id, $pictid, $idx, $row, $slug );
			$cart_thumbnail = ( !empty($pictid ) ) ? wc2_the_item_image( 0, 60, 60, $post_id ) : wc2_no_image();
			$cart_thumbnail = apply_filters( 'wc2_filter_cart_row_thumbnail', $cart_thumbnail, $post_id, $pictid, $idx, $row, $slug );
			$cart_item_name = wc2_get_cart_item_name( $item_name, $item_code, $sku_name, $sku_code );
			$cart_options = '';
			$cart_options = apply_filters( 'wc2_filter_cart_row_options', $cart_options, $idx, $row, $slug );
			$cart_quantity = '<input type="number" class="quantity" name="quantity['.$idx.']" id="quantity-'.$idx.'" value="'.$quantity.'" min="1">';
			$cart_quantity = apply_filters( 'wc2_filter_cart_row_quantity', $cart_quantity, $idx, $row, $slug );

			$num++;
			$cart_row = '
			<td class="num">'.$num.'</td>
			<td class="thumbnail"><a href="'.esc_url($cart_thumbnail_url).'">'.$cart_thumbnail.'</a></td>
			<td class="name">'.$cart_item_name.$cart_options.'</td>
			<td class="price">'.wc2_crform($price, false, false).'</td>
			<td class="quantity">'.$cart_quantity.'</td>
			<td class="subtotal">'.wc2_crform($subtotal, false, false).'</td>
			<td class="stock">'.$stock_status.'</td>
			<td class="action">
				<input type="hidden" name="cart_id[]" id="cart_id-'.$idx.'" value="'.$idx.'" />
				<input type="hidden" name="item_id['.$idx.']" value="'.$item_id.'" />
				<input type="hidden" name="sku_id['.$idx.']" value="'.$sku_id.'" />
				<input type="hidden" name="sku_price['.$idx.']" value="'.$price.'" />';
			foreach( (array)$meta_type as $type => $meta ) {
				foreach( (array)$meta as $key => $value ) {
				$cart_row .= '
				<input type="hidden" name="meta_type['.$idx.']['.$type.']['.$key.']" value="'.$value.'" />';
				}
			}
			foreach( (array)$meta_key as $key => $value ) {
				$cart_row .= '
				<input type="hidden" name="meta_key['.$idx.']['.$key.']" value="'.$value.'" />';
			}
			$cart_row .= '
				<input type="button" class="button cart-remove" id="cart-remove-'.$idx.'" value="'.__('削除','wc2').'" />
			</td>';
			$cart_row = apply_filters( 'wc2_filter_cart_row', $cart_row, $idx, $row, $slug );
			echo $cart_row;
?>
		</tr>
	<?php endforeach; ?>
		</tbody>
<?php endif; ?>
		<tfoot>
		<tr>
<?php 
	$cart_footer = '
			<th class="total-title" colspan="5">'.__('合計','wc2').'</th>
			<th class="total-price">'.wc2_crform( $total_price, false, false ).'</th>
			<th colspan="2"></th>';
	$cart_footer = apply_filters( 'wc2_filter_cart_footer', $cart_footer, $cart, $slug );
	echo $cart_footer;
?>
		</tr>
		</tfoot>
	</table>
<?php
	$html = ob_get_contents();
	ob_end_clean();
	$html = apply_filters( 'wc2_filter_cart_table', $html, $slug );
	return $html;
}

function wc2_cart_table_e( $slug = '' ) {
	echo wc2_cart_table( $slug );
}

function wc2_cart_confirm_table( $slug = '' ) {
	$wc2_cart = WC2_Cart::get_instance( $slug );
	$cart = $wc2_cart->get_cart( $slug );
	$cart_num = count($cart);
	$entry_data = wc2_get_entry();
	$num = 0;

	ob_start();
?>
	<table class="cart-table">
		<thead>
		<tr>
<?php
	$cart_header = '
			<th scope="row" class="num">No.</th>
			<th class="thumbnail">&nbsp;&nbsp;</th>
			<th class="name">'.__('Items','wc2').'</th>
			<th class="price">'.__('Unit price','wc2').'</th>
			<th class="quantity">'.__('Quantity','wc2').'</th>
			<th class="subtotal">'.__('Amount','wc2').'</th>';
	$cart_header = apply_filters( 'wc2_filter_cart_confirm_header', $cart_header, $cart, $slug );
	echo $cart_header;
?>
		</tr>
		</thead>
		<tbody>
	<?php foreach( $cart as $idx => $row ): ?>
		<tr>
<?php
			$post_id = $row['post_id'];
			$item_id = $row['item_id'];
			$item_name = $row['item_name'];
			$item_code = $row['item_code'];
			$sku_name = $row['sku_name'];
			$sku_code = $row['sku_code'];
			$sku_id = $row['sku_id'];
			$quantity = $row['quantity'];
			$price = $row['price'];
			$subtotal = $row['quantity'] * $row['price'];

			//$item_sku_data = wc2_get_item_sku_data( $item_id, $sku_id );
			//$stock_status = $item_sku_data['stock_status'];
			//$stock = $item_sku_data['sku_stock'];
			$pictid = wc2_get_mainpictid( $item_code );
			$cart_thumbnail = ( !empty($pictid ) ) ? wc2_the_item_image( 0, 60, 60, $post_id ) : wc2_no_image();
			$cart_thumbnail = apply_filters( 'wc2_filter_cart_confirm_row_thumbnail', $cart_thumbnail, $post_id, $pictid, $idx, $row, $slug );

			$cart_item_name = wc2_get_cart_item_name( $item_name, $item_code, $sku_name, $sku_code );
			$cart_options = '';
			$cart_options = apply_filters( 'wc2_filter_cart_confirm_row_options', $cart_options, $idx, $row, $slug );

			$num++;
			$cart_row = '
			<td class="num">'.$num.'</td>
			<td class="thumbnail">'.$cart_thumbnail.'</td>
			<td class="name">'.$cart_item_name.$cart_options.'</td>
			<td class="price">'.wc2_crform( $price, false, false ).'</td>
			<td class="quantity">'.wc2_crform( $quantity, false, false ).'</td>
			<td class="subtotal">'.wc2_crform( $subtotal, false, false ).'</td>';
			$cart_row = apply_filters( 'wc2_filter_cart_confirm_row', $cart_row, $idx, $row );
			echo $cart_row;
?>
		</tr>
	<?php endforeach; ?>
		</tbody>
		<tfoot>
		<tr>
			<th colspan="5"><?php _e('Total amount of items', 'wc2'); ?></th>
			<th class="total-items-price"><?php wc2_crform_e( $entry_data['order']['item_total_price'], true, false ); ?></th>
		</tr>
<?php if( !empty($entry_data['order']['discount']) ) : ?>
		<tr>
			<td colspan="5"><?php echo apply_filters( 'wc2_filter_discount_label', __('Discount', 'wc2') ); ?></td>
			<td class="discount"><?php wc2_crform_e($entry_data['order']['discount'], true, false); ?></td>
		</tr>
<?php endif; ?>
<?php if( 0.00 < (float)$entry_data['order']['tax'] && 'products' == wc2_get_tax_target() ) : ?>
		<tr>
			<td colspan="5"><?php wc2_tax_label_e(); ?></td>
			<td class="tax"><?php wc2_tax_e( $entry_data['order'] ) ?></td>
		</tr>
<?php endif; ?>
		<tr>
			<td colspan="5"><?php _e('Shipping charges', 'wc2'); ?></td>
			<td class="aright"><?php wc2_crform_e( $entry_data['order']['shipping_charge'], true, false ); ?></td>
		</tr>
<?php if( !empty($entry_data['order']['cod_fee']) ) : ?>
		<tr>
			<td colspan="5"><?php echo apply_filters( 'wc2_filter_cod_label', __('COD fee', 'wc2') ); ?></td>
			<td class="aright"><?php wc2_crform_e( $entry_data['order']['cod_fee'], true, false ); ?></td>
		</tr>
<?php endif; ?>
<?php if( 0.00 < (float)$entry_data['order']['tax'] && 'all' == wc2_get_tax_target() ) : ?>
		<tr>
			<td colspan="5"><?php wc2_tax_label_e(); ?></td>
			<td class="tax"><?php wc2_tax_e( $entry_data['order'] ) ?></td>
		</tr>
<?php endif; ?>
<?php if( wc2_is_membersystem_state() && wc2_is_membersystem_point() && !empty($entry_data['order']['usedpoint']) ) : ?>
		<tr>
			<td colspan="5"><?php _e('Used points', 'wc2'); ?></td>
			<td class="usedpoint"><?php echo number_format( $entry_data['order']['usedpoint'] ); ?></td>
		</tr>
<?php endif; ?>
		<tr>
			<th colspan="5"><?php _e('Total amount', 'wc2'); ?></th>
			<th class="total_price"><?php wc2_crform_e( $entry_data['order']['total_price'], true, false ); ?></th>
		</tr>
		</tfoot>
	</table>
<?php if( wc2_is_membersystem_state() && wc2_is_membersystem_point() &&  wc2_is_login() ) : 
		$member_id = wc2_memberinfo( 'ID' );
		$point = wc2_get_member_data_value( $member_id, $key ); ?>
	<form id="cart-form-confirm-point" action="<?php wc2_cart_url_e( 'point' ); ?>" method="post">
	<table id="point-table">
		<tr>
			<td><?php _e('Current holdings points', 'wc2'); ?></td>
			<td><span class="point"><?php echo $point; ?></span><?php _e('Points', 'wc2'); ?></td>
		</tr>
		<tr>
			<td><?php _e('Point to use', 'wc2'); ?></td>
			<td><input name="offer[usedpoint]" class="used_point" type="text" value="<?php esc_attr_e($entry_data['order']['usedpoint']); ?>" /><?php _e('Points', 'wc2'); ?></td>
		</tr>
			<tr>
			<td colspan="2"><input name="use_point" type="button" class="use_point_button" value="<?php _e('Use the points', 'wc2'); ?>" /></td>
		</tr>
	</table>
	<?php do_action( 'wc2_action_confirm_point_page_form_inside' ); ?>
	</form>
	<?php do_action( 'wc2_action_confirm_point_page_form_outside' ); ?>
<?php endif; ?>
	<?php do_action( 'wc2_action_confirm_cart_footer' ); ?>
<?php
	$html = ob_get_contents();
	ob_end_clean();
	$html = apply_filters( 'wc2_filter_cart_confirm_table', $html, $slug );
	return $html;
}

function wc2_cart_confirm_table_e( $slug = '' ) {
	echo wc2_cart_confirm_table( $slug );
}
