<?php

function wc2_add_admin_option( $option_name, $data ) {

	$data = wc2_stripslashes_deep_post( $data );
	$option_value = wc2_get_option( $option_name );
	$add_id = -1;

	if( !empty($option_value) ) {
		$unique = true;
		foreach( (array)$option_value as $key => $value ) {
			if( $value['name'] == $data['name'] ) {
				$unique = false;
				break;
			}
		}
		if( !$unique )
			return $add_id;

		$max_id = 0;
		foreach( (array)$option_value as $key => $value ) {
			if( $max_id < $value['id'] ) $max_id = $value['id'];
		}
		$add_id = $max_id + 1;
		$sort = count( $option_value );
	} else {
		$add_id = 0;
		$sort = 0;
	}
	$data['id'] = $add_id;
	$data['sort'] = $sort;
	$option_value[] = $data;
	wc2_update_option( $option_name, $option_value );

	return $add_id;
}

function wc2_update_admin_option( $option_name, $id, $data ) {

	$data = wc2_stripslashes_deep_post( $data );
	$option_value = wc2_get_option( $option_name );
	$upd_id = -1;

	if( !empty($option_value) and array_key_exists( $id, $option_value ) ) {
		$unique = true;
		foreach( (array)$option_value as $key => $value ) {
			if( $value['name'] == $data['name'] && $key != $id ) {
				$unique = false;
				break;
			}
		}
		if( !$unique )
			return $upd_id;

		$option_value[$id]['name'] = $data['name'];
		$option_value[$id]['explanation'] = $data['explanation'];
		$option_value[$id]['settlement'] = $data['settlement'];
		$option_value[$id]['charge'] = $data['charge'];
		$option_value[$id]['charge_price'] = $data['charge_price'];
		$option_value[$id]['use'] = $data['use'];
		wc2_update_option( $option_name, $option_value );
		$upd_id = $id;
	}

	return $upd_id;
}

function wc2_delete_admin_option( $option_name, $id ) {

	$option_value = wc2_get_option( $option_name );
	if( !empty($option_value) && isset($option_value[$id]) ) {
		unset( $option_value[$id] );

		$c = 0;
		foreach( (array)$option_value as $key => $value ) {
			$option_value[$key]['sort'] = $c;
			$c++;
		}
		wc2_update_option( $option_name, $option_value );
	}

	return count($option_value);
}

function wc2_sort_admin_option( $option_name, $idstr ) {

	$option_value = wc2_get_option( $option_name );
	if( !empty($option_value) ) {
		$ids = explode( ',', $idstr );
		$c = 0;
		foreach( (array)$ids as $id ) {
			$option_value[$id]['sort'] = $c;
			$c++;
		}
		$key_id = array();
		foreach( (array)$option_value as $key => $value ) {
			$key_id[$key] = $value['sort'];
		}
		array_multisort( $key_id, SORT_ASC, $option_value );
		wc2_update_option( $option_name, $option_value );
	}

	return count($option_value);
}

