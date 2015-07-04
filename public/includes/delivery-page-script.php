<?php
	$entry_data = wc2_get_entry();
	$system = wc2_get_option( 'system' );
	$general = wc2_get_option( 'general' );
	$delivery = wc2_get_option( 'delivery' );
	$settlement = wc2_get_option( 'settlement' );
?>
	var toDoubleDigits = function(num) {
		num += "";
		if(num.length === 1) num = "0".concat(num);
		return num;
	};
	var selected_delivery_method = "";
	var selected_delivery_date = "";
	var selected_delivery_time = "";
	var add_shipping = new Array();

	function addDate(year, month, day, add) {
		var date = new Date(Number(year), (Number(month) - 1), Number(day));
		var baseSec = date.getTime();
		var addSec = Number(add) * 86400000;
		var targetSec = baseSec + addSec;
		date.setTime(targetSec);

		var yy = date.getFullYear() + "";
		var mm = toDoubleDigits(date.getMonth() + 1);
		var dd = toDoubleDigits(date.getDate());

		var newdate = new Array();
		newdate["year"] = yy;
		newdate["month"] = mm;
		newdate["day"] = dd;
		return(newdate);
	}
<?php
	//選択可能な配送方法
	$default_deli = array_values( apply_filters( 'wc2_filter_get_available_delivery_method', wc2_get_available_delivery_method() ) );
	if( !isset($entry_data['order']['delivery_method']) || '' == $entry_data['order']['delivery_method'] ) {
		$selected_delivery_method = $default_deli[0];
	} else {
		$selected_delivery_method = $entry_data['order']['delivery_method'];
	}
?>
	selected_delivery_method = "<?php echo $selected_delivery_method; ?>";
<?php if( isset($entry_data['order']['delivery_date']) ) : ?>
	selected_delivery_date = "<?php echo $entry_data['order']['delivery_date']; ?>";
<?php endif; ?>
<?php
	//カートに入っている商品の発送日目安
	$shipping = 0;
	$cart = wc2_get_cart();
	foreach( $cart as $key => $cart_row ) {
		$shipment = wc2_get_item_value_by_item_id( $cart_row['item_id'], ITEM_PREPARATIONS_SHIPMENT );
		if( $shipment == 0 or $shipment == 9 ) {
			$shipping = 0;
			break;
		}
		if( $shipping < $shipment ) $shipping = $shipment;
	}
?>
	var shipping = "<?php echo $shipping; ?>";
<?php
	//配送業務締時間
	$hour = ( !empty($delivery['delivery_time_limit']['hour']) ) ? $delivery['delivery_time_limit']['hour'] : '00';
	$min = ( !empty($delivery['delivery_time_limit']['min']) ) ? $delivery['delivery_time_limit']['min'] : '00';
	//配送希望日を何日後まで表示するか
	$delivery_after_days = ( !empty($delivery['delivery_after_days']) ) ? (int)$delivery['delivery_after_days'] : 15;
	$shortest_delivery_time = ( isset($delivery['shortest_delivery_time']) ) ? $delivery['shortest_delivery_time'] : '0';
?>
	var delivery_time_limit_hour = "<?php echo $hour; ?>";
	var delivery_time_limit_min = "<?php echo $min; ?>";
	var shortest_delivery_time = "<?php echo (int)$shortest_delivery_time; ?>";
	var delivery_after_days = "<?php echo $delivery_after_days; ?>";
	var customer_pref = "<?php echo esc_js($entry_data['customer']['pref']); ?>";
<?php
	//配送先県(customer/delivery)
	$delivery_pref = ( isset($entry_data['delivery']['pref']) && !empty($entry_data['delivery']['pref']) ) ? $entry_data['delivery']['pref'] : $entry_data['customer']['pref'];
	$delivery_country = ( isset($entry_data['delivery']['country']) && !empty($entry_data['delivery']['country']) ) ? $entry_data['delivery']['country'] : $entry_data['customer']['country'];
?>
	var delivery_pref = "<?php echo esc_js($delivery_pref); ?>";
	var delivery_country = "<?php echo esc_js($delivery_country); ?>";
<?php
	//選択可能な配送方法に設定されている配達日数
	$script_days = '
	var delivery_days = [];';
	foreach( (array)$default_deli as $id ) {
		$index = wc2_get_delivery_method_index( $id );
		if( 0 <= $index ) {
			$script_days .= '
	delivery_days['.$id.'] = [];';
			$script_days .= '
	delivery_days['.$id.'].push("'.$delivery['delivery_method'][$index]['days'].'");';
		}
	}

	//配達日数に設定されている県毎の日数
	$target_market = ( isset($system['target_market']) && !empty($system['target_market']) ) ? $system['target_market'] : wc2_get_local_target_market();
	$prefs = array();
	foreach( (array)$target_market as $tm ) {
		$prefs[$tm] = $system['province'][$tm];
	}
	$delivery_days = wc2_get_option( 'delivery_days' );
	$script_days .= '
	var delivery_days_value = [];';
	foreach( (array)$default_deli as $id ) {
		$index = wc2_get_delivery_method_index( $id );
		if( 0 <= $index ) {
			$days = (int)$delivery['delivery_method'][$index]['days'];
			if( 0 <= $days ) {
				for( $i = 0; $i < count((array)$delivery_days); $i++ ) {
					if( (int)$delivery_days[$i]['id'] == $days ) {
						$script_days .= '
	delivery_days_value['.$days.'] = [];';
						foreach( (array)$target_market as $tm ) {
							$script_days .= '
	delivery_days_value['.$days.']["'.$tm.'"] = [];';
							foreach( (array)$prefs[$tm] as $pref ) {
								$pref = esc_js($pref);
								$script_days .= '
	delivery_days_value['.$days.']["'.$tm.'"]["'.$pref.'"] = [];';
								if( isset($delivery_days[$i][$tm][$pref]) )
								$script_days .= '
	delivery_days_value['.$days.']["'.$tm.'"]["'.$pref.'"].push("'.(int)$delivery_days[$i][$tm][$pref].'");';
							}
						}
					}
				}
			}
		}
	}
	echo $script_days;
?>
<?php
	//営業日
	$business_days = 0;
	list( $yy, $mm, $dd ) = wc2_get_today();
	$business = ( isset($general['business_days'][$yy][$mm][$dd]) ) ? $general['business_days'][$yy][$mm][$dd] : 1;
	while( $business != 1 ) {
		$business_days++;
		list( $yy, $mm, $dd ) = wc2_get_nextday( $yy, $mm, $dd );
		$business = $general['business_days'][$yy][$mm][$dd];
	}
?>
	var business_days = "<?php echo $business_days; ?>";
	selected_delivery_time = "<?php echo esc_js($entry_data['order']['delivery_time']); ?>";
<?php
	$script_delivery_time = '
	var delivery_time = [];
	delivery_time[0] = [];';
	foreach( (array)$delivery['delivery_method'] as $dmid => $dm ) {
		$lines = explode("\n", $dm['time']);
		$script_delivery_time .= '
	delivery_time[' . $dm['id'] . '] = [];';
		foreach( (array)$lines as $line ) {
			if( trim($line) != '' ) {
				$script_delivery_time .= '
	delivery_time[' . $dm['id'] . '].push("' . trim($line) . '");';
			}
		}
	}
	echo $script_delivery_time;

	//支払方法
	$payments_str = '';
	$payments_arr = array();
	$payments = wc2_get_payment_option();
	$payments = apply_filters( 'wc2_filter_available_payment_method', $payments );
	foreach( (array)$payments as $array ) {
		switch( $array['settlement'] ) {
			case 'acting_zeus_card':
				$paymod_base = 'zeus';
				if( 'on' == $settlement[$paymod_base]['card_activate'] 
					&& 'on' == $settlement[$paymod_base]['activate'] ) {
					$payments_str .= "'" . $array['name'] . "': '" . $paymod_base . "', ";
					$payments_arr[] = $paymod_base;
				}
				break;
			case 'acting_zeus_conv':
				$paymod_base = 'zeus';
				if( 'on' == $settlement[$paymod_base]['conv_activate'] 
					&& 'on' == $settlement[$paymod_base]['activate'] ) {
					$payments_str .= "'" . $array['name'] . "': '" . $paymod_base . "_conv', ";
					$payments_arr[] = $paymod_base.'_conv';
				}
				break;
			case 'acting_remise_card':
				$paymod_base = 'remise';
				if( 'on' == $settlement[$paymod_base]['card_activate'] 
					&& 'on' == $settlement[$paymod_base]['howpay'] 
					&& 'on' == $settlement[$paymod_base]['activate'] ) {
					$payments_str .= "'" . $array['name'] . "': '" . $paymod_base . "', ";
					$payments_arr[] = $paymod_base;
				}
				break;
		}
	}
	$payments_str = rtrim($payments_str, ', ');
?>
	var paymod = { <?php echo $payments_str; ?> };
<?php
	$sendout = wc2_get_send_out_date();
	ob_start();
?>
	$("input[name='offer[payment_name]']").click(function() {
<?php foreach( $payments_arr as $pm ) : ?>
		$("#<?php echo $pm; ?>").css({"display":"none"});
<?php endforeach; ?>
		var chk_pay = $("input[name='offer[payment_name]']:checked").val();
		if( paymod[chk_pay] != "" ) {
			$("#" + paymod[chk_pay]).css({"display":"table"});
		}
	});

	$("#delivery_method_select").change(function() {
		orderfunc.make_delivery_date(($("#delivery_method_select option:selected").val()-0));
		orderfunc.make_delivery_time(($("#delivery_method_select option:selected").val()-0));
	});

	$("#delivery_flag1").click(function() {
		if( customer_pref != delivery_pref ) {
			delivery_pref = customer_pref;
			orderfunc.make_delivery_date(($("#delivery_method_select option:selected").val()-0));
		}
	});

	$("#delivery_flag2").click(function() {
		if( $("#delivery_flag2").attr("checked") && undefined != $("#delivery_pref").get(0) && $("#delivery_pref").get(0).selectedIndex > 0 ) {
			delivery_pref = $("#delivery_pref").val();
			orderfunc.make_delivery_date(($("#delivery_method_select option:selected").val()-0));
		}
	});

	$("#delivery_pref").change(function() {
		if( $("#delivery_flag2").attr("checked") && undefined != $("#delivery_pref").get(0) && $("#delivery_pref").get(0).selectedIndex > 0 ) {
			delivery_pref = $("#delivery_pref").val();
			orderfunc.make_delivery_date(($("#delivery_method_select option:selected").val()-0));
		}
	});

	var prefd = $('#delivery_pref').val();
	$('*[name="delivery[address1]"]').focus(function() {
//console.log(prefd);
//console.log($('#delivery_pref').val());
		if( prefd != $("#delivery_pref").val() ) {
			delivery_pref = $("#delivery_pref").val();
			orderfunc.make_delivery_date(($("#delivery_method_select option:selected").val()-0));
		}
		prefd = $("#delivery_pref").val();
	});

	orderfunc = {
		make_delivery_date : function(selected) {
			var option = "";
			var message = "";
			if( delivery_days[selected] != undefined && delivery_days[selected] >= 0 ) {
				switch( shipping ) {
				case 0://指定なし
				case 9://商品入荷後
					break;
				default:
					var date = new Array();
					date["year"] = "<?php echo $sendout['sendout_date']['year']; ?>";
					date["month"] = "<?php echo $sendout['sendout_date']['month']; ?>";
					date["day"] = "<?php echo $sendout['sendout_date']['day']; ?>";
					//配達日数加算
					if( delivery_days_value[delivery_days[selected]] != undefined ) {
						if( delivery_days_value[delivery_days[selected]][delivery_country][delivery_pref] != undefined ) {
							date = addDate(date["year"], date["month"], date["day"], delivery_days_value[delivery_days[selected]][delivery_country][delivery_pref]);
						}
					}
					//最短配送時間帯メッセージ
					var date_str = date["year"]+"-"+date["month"]+"-"+date["day"];
					switch( shortest_delivery_time ) {
					case 0://指定しない
						//message = "最短 " + date_str + " からご指定いただけます。";
						break;
					case 1://午前着可
						message = "最短 " + date_str + " の午前中からご指定いただけます。";
						break;
					case 2://午前着不可
						message = "最短 " + date_str + " の午後からご指定いただけます。";
						break;
					}
<?php $delivery_after_days_script = '
					option += \'<option value="'.__('指定しない', 'wc2').'">'.__('指定しない', 'wc2').'</option>\';
					for( var i = 0; i < delivery_after_days; i++ ) {
						date_str = date["year"]+"-"+date["month"]+"-"+date["day"];
						if( date_str == selected_delivery_date ) {
							option += \'<option value=" + date_str + " selected="selected">\' + date_str + \'</option>\';
						} else {
							option += \'<option value=" + date_str + ">\' + date_str + \'</option>\';
						}
						date = addDate(date["year"], date["month"], date["day"], 1);
					}';
	echo apply_filters( 'wc2_delivery_after_days_script', $delivery_after_days_script );
?>
					break;
				}
			}
			if( option == "" ) {
				option = '<option value="<?php _e('指定できません', 'wc2'); ?>"><?php _e('指定できません', 'wc2'); ?></option>';
			}
			$("#delivery_date_select").html(option);
			$("#delivery_time_limit_message").html(message);
		},
		make_delivery_time : function(selected) {
			var option = "";
			if( delivery_time[selected] != undefined ) {
				for( var i = 0; delivery_time[selected].length > i ; i++ ) {
					if( delivery_time[selected][i] == selected_delivery_time ) {
						option += '<option value="' + delivery_time[selected][i] + '" selected="selected">' + delivery_time[selected][i] + '</option>';
					} else {
						option += '<option value="' + delivery_time[selected][i] + '">' + delivery_time[selected][i] + '</option>';
					}
				}
			}
			if( option == "" ) {
				option = '<option value="<?php _e('指定できません', 'wc2'); ?>"><?php _e('指定できません', 'wc2'); ?></option>';
			}
			$("#delivery_time_select").html(option);
		}
	};

<?php if( $entry_data['delivery']['delivery_flag'] != 1 ) : ?>
	$("#delivery_table").css({display: "none"});
<?php endif; ?>
	orderfunc.make_delivery_date(selected_delivery_method);
	orderfunc.make_delivery_time(selected_delivery_method);
<?php
	$cart_delivery_script = ob_get_contents();
	ob_end_clean();
	$cart_delivery_script = apply_filters( 'wc2_filter_cart_delivery_script', $cart_delivery_script, $entry_data, $sendout );
	echo $cart_delivery_script;

	foreach( $payments_arr as $pn => $pm ) : ?>
	$("#<?php echo $pm; ?>").css({"display":"none"});
<?php
		switch( $pm ) :
		case 'zeus':
			if( 'on' == $settlement[$pm]['howpay'] ) :
?>
	$("input[name='offer[howpay]']").change(function() {
		if( '' != $("select[name='offer[cbrand]'] option:selected").val() ) {
			$("#div_<?php echo $pm; ?>").css({"display": ""});
		}
		if( '1' == $("input[name='offer[howpay]']:checked").val() ) {
			$("#cbrand_<?php echo $pm; ?>").css({"display": "none"});
			$("#div_<?php echo $pm; ?>").css({"display": "none"});
		} else {
			$("#cbrand_<?php echo $pm; ?>").css({"display": ""});
		}
	});

	$("select[name='offer[cbrand]']").change(function() {
		$("#div_<?php echo $pm; ?>").css({"display": ""});
		if( '1' == $("select[name='offer[cbrand]'] option:selected").val() ) {
			$("#brand1").css({"display": ""});
			$("#brand2").css({"display": "none"});
			$("#brand3").css({"display": "none"});
		} else if( '2' == $("select[name='offer[cbrand]'] option:selected").val() ) {
			$("#brand1").css({"display": "none"});
			$("#brand2").css({"display": ""});
			$("#brand3").css({"display": "none"});
		} else if( '3' == $("select[name='offer[cbrand]'] option:selected").val() ) {
			$("#brand1").css({"display": "none"});
			$("#brand2").css({"display": "none"});
			$("#brand3").css({"display": ""});
		} else {
			$("#brand1").css({"display": "none"});
			$("#brand2").css({"display": "none"});
			$("#brand3").css({"display": "none"});
		}
	});

	if( '1' == $("input[name='offer[howpay]']:checked").val() ) {
		$("#cbrand_<?php echo $pm; ?>").css({"display": "none"});
		$("#div_<?php echo $pm; ?>").css({"display": "none"});
	} else {
		$("#cbrand_<?php echo $pm; ?>").css({"display": ""});
		$("#div_<?php echo $pm; ?>").css({"display": ""});
	}

	if( '1' == $("select[name='offer[cbrand]'] option:selected").val() ) {
		$("#brand1").css({"display": ""});
		$("#brand2").css({"display": "none"});
		$("#brand3").css({"display": "none"});
	} else if( '2' == $("select[name='offer[cbrand]'] option:selected").val() ) {
		$("#brand1").css({"display": "none"});
		$("#brand2").css({"display": ""});
		$("#brand3").css({"display": "none"});
	} else if( '3' == $("select[name='offer[cbrand]'] option:selected").val() ) {
		$("#brand1").css({"display": "none"});
		$("#brand2").css({"display": "none"});
		$("#brand3").css({"display": ""});
	} else {
		$("#brand1").css({"display": "none"});
		$("#brand2").css({"display": "none"});
		$("#brand3").css({"display": "none"});
	}
<?php
			endif;
			break;
		endswitch;
	endforeach;
?>
	ch_pay = $("input[name='offer[payment_name]']:checked").val();
	if( paymod[ch_pay] != '' ) {
		$("#" + paymod[ch_pay]).css({"display":""});
	}


