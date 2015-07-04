jQuery(function ( $ ){
	//商品オプション
	itemOpt = {
		settings: {
			url: ajaxurl,
			type: 'POST',
			cache: false,
			success: function(data, dataType){
				$("tbody#item-opt-list").html( data );
			}, 
			error: function(msg){
				$("#ajax-response").html(msg);
			}
		},
		
		post : function(action, arg) {
			if( action == 'updateitemopt' ) {
				itemOpt.updateitemopt(arg);
			} else if( action == 'deleteitemopt' ) {
				itemOpt.deleteitemopt(arg);
			} else if( action == 'additemopt' ) {
				var status = $("#opt_status").val();
				if( 'new_opt' == status ){
					itemOpt.newitemopt();
				} else if( 'select_opt' == status){
					itemOpt.additemopt();
				}
			} else if( action == 'addcommonopt' ) {
				itemOpt.addcommonopt();
			} else if( action == 'keyselect' ) {
				itemOpt.keyselect(arg);
			}
		},

		newitemopt : function() {
			var id = $("#post_ID").val();
			var name = $("#newoptname").val();
			var value = $("#newoptvalue").val();
			var means = $("#newoptmeans").val();
			if($("input#newoptessential").attr("checked")){
				var essential = '1';
			}else{
				var essential = '0';
			}

			var mes = '';
			if( '' == name ){
				mes += '<p>オプション名を入力してください。</p>';
			} else {
				var check = true;
				$("input[name*='\[name\]']").each(function(){ if( name == $(this).val() ){ check = false; }});
				if( !check ){
					mes += '<p>同じ名前のオプションが存在します。</p>';
				}
			}
			if( '' == value && (0 == means || 1 == means || 3 == means || 4 == means) ){
				mes += '<p>セレクト値を入力してください。</p>';
			}else if( '' != value && (2 == means || 5 == means) ){
				mes += '<p>テキスト、テキストエリアの場合はセレクト値を空白にしてください。</p>';
			}
			if( '' != mes ){
				$("#itemopt_ajax-response").html('<div class="error">' + mes + '</div>');
				return false;
			}

			$("#newcomopt_loading").html('<img src="'+WC2L10n.loading_gif+'" />');

			var s = itemOpt.settings;
			s.data = "action=item_option_ajax&ID=" + id + "&newoptname=" + encodeURIComponent(name) + "&newoptvalue=" +encodeURIComponent(value) + "&newoptmeans=" + encodeURIComponent(means) + "&newoptessential=" + encodeURIComponent(essential);
			s.success = function(data, dataType){
				$("#newcomopt_loading").html('');
				$("#itemopt_ajax-response").html('');
				strs = data.split('#usces#');
				$("table#optlist-table").removeAttr("style");
				var meta_id = strs[1];
				if( 0 > meta_id ){
					$("#itemopt_ajax-response").html('<div class="error"><p>同じ名前のオプションが存在します。</p></div>');
				}else{
					$("tbody#item-opt-list").html( strs[0] );
					$("#newoptname").val('');
					$("#newoptvalue").val('');
					$("#newoptmeans").val(0);
					$("#newoptessential").attr({checked: false});
					$("#itemopt-" + meta_id).css({'background-color': '#FF4'});
					$("#itemopt-" + meta_id).animate({ 'background-color': '#FFFFEE' }, 2000 );
				}
			};
			s.error = function(msg){
				$("#comopt_ajax-response").html(msg);
				$("#newcomopt_loading").html('');
			};
			$.ajax( s );
			return false;
		},

		additemopt : function() {
			if($("#optkeyselect").val() == "#NONE#") return;

			var id = $("#post_ID").val();
			var name = $("#optkeyselect option:selected").html();
			var value = $("#newoptvalue").val();
			var means = $("#newoptmeans").val();
			if($("input#newoptessential").attr("checked")){
				var essential = '1';
			}else{
				var essential = '0';
			}

			var mes = '';
			if( '' == name ){
				mes += '<p>オプション名を入力してください。</p>';
			} else {
				var check = true;
				$("input[name*='\[name\]']").each(function(){ if( name == $(this).val() ){ check = false; }});
				if( !check ){
					mes += '<p>同じ名前のオプションが存在します。</p>';
				}
			}
			if( '' == value && (0 == means || 1 == means || 3 == means || 4 == means) ){
				mes += '<p>セレクト値を入力してください。</p>';
			}else if( '' != value && (2 == means || 5 == means) ){
				mes += '<p>テキスト、テキストエリアの場合はセレクト値を空白にしてください。</p>';
			}
			if( '' != mes ){
				$("#itemopt_ajax-response").html('<div class="error">' + mes + '</div>');
				return false;
			}
			
			$("#newitemopt_loading").html('<img src="'+WC2L10n.loading_gif+'" />');

			var s = itemOpt.settings;
			s.data = "action=item_option_ajax&ID=" + id + "&newoptname=" + encodeURIComponent(name) + "&newoptvalue=" + encodeURIComponent(value) + "&newoptmeans=" + encodeURIComponent(means) + "&newoptessential=" + encodeURIComponent(essential);
			s.success = function(data, dataType){
				$("#itemopt_ajax-response").html('');
				$("#newitemopt_loading").html('');
				$("table#optlist-table").removeAttr("style");
				strs = data.split('#wc2#');
				meta_id = strs[1];
				$("tbody#item-opt-list").html( strs[0] );
				$("#optkeyselect").val('#NONE#');
				$("#newoptvalue").html('');
				$("#newoptmeans").val(0);
				$("#newoptessential").attr({checked: false});
				$("#itemopt-" + meta_id).css({'background-color': '#FF4'});
				$("#itemopt-" + meta_id).animate({ 'background-color': '#FFFFEE' }, 2000 );
			};
			s.error = function(msg){
				$("#itemopt_ajax-response").html(msg);
				$("#newitemopt_loading").html('');
			};
			$.ajax( s );
			return false;
		},

		addcommonopt : function() {
			var id = $("#post_ID").val();
			var name = $("#newoptname").val();
			var value = $("#newoptvalue").val();
			var means = $("#newoptmeans").val();
			if($("input#newoptessential").attr("checked")){
				var essential = '1';
			}else{
				var essential = '0';
			}

			var mes = '';
			if( '' == name ){
				mes += '<p>オプション名を入力してください。</p>';
			} else {
				var check = true;
				$("input[name*='\[name\]']").each(function(){ if( name == $(this).val() ){ check = false; }});
				if( !check ){
					mes += '<p>同じ名前のオプションが存在します。</p>';
				}
			}
			if( '' == value && (0 == means || 1 == means || 3 == means || 4 == means) ){
				mes += '<p>セレクト値を入力してください。</p>';
			}else if( '' != value && (2 == means || 5 == means) ){
				mes += '<p>テキスト、テキストエリアの場合はセレクト値を空白にしてください。</p>';
			}
			if( '' != mes ){
				$("#itemopt_ajax-response").html('<div class="error">' + mes + '</div>');
				return false;
			}
			
			$("#newcomopt_loading").html('<img src="'+WC2L10n.loading_gif+'" />');

			var s = itemOpt.settings;
			s.data = "action=item_option_ajax&ID=" + id + "&newoptname=" + encodeURIComponent(name) + "&newoptvalue=" +encodeURIComponent(value) + "&newoptmeans=" + encodeURIComponent(means) + "&newoptessential=" + encodeURIComponent(essential);
			s.success = function(data, dataType){
				$("#newcomopt_loading").html('');
				$("#itemopt_ajax-response").html('');
				strs = data.split('#wc2#');
				$("table#optlist-table").removeAttr("style");
				var meta_id = strs[1];
				if( 0 > meta_id ){
					$("#itemopt_ajax-response").html('<div class="error"><p>同じ名前のオプションが存在します。</p></div>');
				}else{
					$("tbody#item-opt-list").html( strs[0] );
					$("#newoptname").val('');
					$("#newoptvalue").val('');
					$("#newoptmeans").val(0);
					$("#newoptessential").attr({checked: false});
					$("#itemopt-" + meta_id).css({'background-color': '#FF4'});
					$("#itemopt-" + meta_id).animate({ 'background-color': '#FFFFEE' }, 2000 );
				}
			};
			s.error = function(msg){
				$("#comopt_ajax-response").html(msg);
				$("#newcomopt_loading").html('');
			};
			$.ajax( s );
			return false;
		},

		updateitemopt : function(meta_id) {
			var id = $("#post_ID").val();
			nm = document.getElementById('itemopt\['+meta_id+'\]\[name\]');
			vs = document.getElementById('itemopt\['+meta_id+'\]\[value\]');
			ms = document.getElementById('itemopt\['+meta_id+'\]\[means\]');
			es = document.getElementById('itemopt\['+meta_id+'\]\[essential\]');
			so = document.getElementById('itemopt\['+meta_id+'\]\[sort\]');
			var name = $(nm).val();
			var value = wc2Item.trim($(vs).val());
			var means = $(ms).val();
			var sortnum = $(so).val();
			if($(es).attr("checked")){
				var essential = '1';
			}else{
				var essential = '0';
			}

			var mes = '';
			if( '' == name ){
				mes += '<p>オプション名を入力してください。</p>';
			}
			if( '' == value && (0 == means || 1 == means || 3 == means || 4 == means) ){
				mes += '<p>セレクト値を入力してください。</p>';
			}else if( '' != value && (2 == means || 5 == means) ){
				mes += '<p>テキスト、テキストエリアの場合はセレクト値を空白にしてください。</p>';
			}
			if( '' != mes ){
				$("#itemopt_ajax-response").html('<div class="error">' + mes + '</div>');
				return false;
			}

			$("#itemopt_loading-" + meta_id).html('<img src="'+WC2L10n.loading_gif+'" />');

			var s = itemOpt.settings;
			s.data = "action=item_option_ajax&ID=" + id + "&update=1&optname=" + encodeURIComponent(name) + "&optvalue=" + encodeURIComponent(value) + "&optmeans=" + means + "&optessential=" + essential + "&sort=" + sortnum + "&optmetaid=" + meta_id;
			s.success = function(data, dataType){
				$("#itemopt_ajax-response").html('');
				$("#itemopt_loading-" + meta_id).html('');
				strs = data.split('#wc2#');
				$("tbody#item-opt-list").html( strs[0] );
				$("#itemopt-" + meta_id).css({'background-color': '#FF4'});
				$("#itemopt-" + meta_id).animate({ 'background-color': '#FFFFEE' }, 2000 );
			};
			s.error = function(msg){
				$("#itemopt_ajax-response").html(msg);
				$("#itemopt_loading-" + meta_id).html('');
			};
			$.ajax( s );
			return false;
		},

		deleteitemopt : function(meta_id) {
			$("#itemopt-" + meta_id).css({'background-color': '#F00'});
			$("#itemopt-" + meta_id).animate({ 'background-color': '#FFFFEE' }, 1000 );
			var id = $("#post_ID").val();
			var s = itemOpt.settings;
			s.data = "action=item_option_ajax&ID=" + id + "&delete=1&optmetaid=" + meta_id;
			s.success = function(data, dataType){
				$("#itemopt_ajax-response").html("");
				strs = data.split('#wc2#');
				$("tbody#item-opt-list").html( strs[0] );
			};
			s.error = function(msg){

			};
			$.ajax( s );
			return false;
		},
		
		keyselect : function( meta_id ) {
			if(meta_id == '#NONE#'){
				$("#newoptvalue").val('');
				$("#newoptmeans").val(0);
				$("#newoptessential").attr({checked: false});
				return;
			}
			var id = WC2L10n.cart_number;
			
			$("#newitemopt_loading").html('<img src="'+WC2L10n.loading_gif+'" />');
			$("#add_itemopt").attr("disabled", true);
			
			var s = itemOpt.settings;
			s.data = "action=item_option_ajax&ID=" + id + "&select=1&meta_id=" + meta_id;
			s.success = function(data, dataType){
				$("#itemopt_ajax-response").html("");
				strs = data.split('#wc2#');
				var means = strs[0];
				var essential = strs[1];
				var value = strs[2];
				if( 2 == means || 5 == means ){
					value = '';
				}
				$("#newoptvalue").html(value);
				$("#newoptmeans").val(means);
				if( essential == '1') {
					$("#newoptessential").attr({checked: true});
				}else{
					$("#newoptessential").attr({checked: false});
				}
				$("#newitemopt_loading").html('');
				$("#add_itemopt").attr("disabled", false);
			};
			s.error = function(msg){
				$("#itemopt_ajax-response").html(msg);
				$("#newitemopt_loading").html('');
			};
			$.ajax( s );
			return false;
		},
		
		dosort : function( str ) {
			if( !str ) return;
			var id = $("#post_ID").val();
			var meta_id_str = str.replace(/itemopt-/g, "");
			var meta_ids = meta_id_str.split(',');
			if( 2 > meta_ids.length ) return;

			for(i=0; i<meta_ids.length; i++){
				$("#itemopt_loading-" + meta_ids[i]).html('<img src="'+WC2L10n.loading_gif+'" />');
			}
			var s = itemOpt.settings;
			s.data = "action=item_option_ajax&ID=" + id + "&sort=1&meta=" + encodeURIComponent(meta_id_str);
			s.success = function(data, dataType){
				$("#itemopt_ajax-response").html("");
				strs = data.split('#wc2#');
				$("tbody#item-opt-list").html( strs[0] );
				for(i=0; i<meta_ids.length; i++){
					$("#itemopt_loading-" + meta_ids[i]).html('');
					$("#itemopt-" + meta_ids[i]).css({'background-color': '#FF4'});
					$("#itemopt-" + meta_ids[i]).animate({ 'background-color': '#FFFFEE' }, 2000 );
				}
			};
			s.error = function(msg){
				$("#opt_ajax-response").html('<div class="error"><p>error sort</p></div>');
			};
			$.ajax( s );
			return false;
		}
	};

	$("#newopt").click(function() {
		$("#newopt").css('display', 'none');
		$("#cancelopt").css('display', 'inline');
		$("#newoptname").css('display', 'inline-block');
		$("#optkeyselect").css('display', 'none');
		$("#opt_status").val('new_opt');
	});

	$("#cancelopt").click(function() {
		$("#newopt").css('display', 'inline');
		$("#cancelopt").css('display', 'none');
		$("#newoptname").css('display', 'none');
		$("#optkeyselect").css('display', 'inline-block');
		$("#opt_status").val('select_opt');
	});

/*
	$("#new_delivery_method_action").click(function () {
		if(delivery_method.length === 0) return false;
		$("#delivery_method_name").html('<input name="delivery_method_name" type="text" value="" />');
		$("#delivery_method_name2").html('');
		$("#delivery_method_time").val('');
		$("#delivery_method_button").html('<input name="cancel_delivery_method" id="cancel_delivery_method" type="button" value="<?php _e('キャンセル', 'wc2'); ?>" onclick="operation.disp_delivery_method(0);" /><input name="add_delivery_method" id="add_delivery_method" type="button" value="<?php _e('追加', 'wc2'); ?>" onclick="operation.add_delivery_method();" />');
		$("#delivery_method_nocod").html('<input name="delivery_method_nocod" type="checkbox" value="1" />');
		$("#delivery_method_intl").html('<input name="delivery_method_intl" id="delivery_method_intl_0" type="radio" value="0" checked="checked" /><label for="delivery_method_intl_0"><?php _e('国内便', 'wc2'); ?></label><input name="delivery_method_intl" id="delivery_method_intl_1" type="radio" value="1" /><label for="delivery_method_intl_1"><?php _e('国際便', 'wc2'); ?></label>');
		$("input[name='delivery_method_name']").focus().select();
		operation.make_delivery_method_charge(-1);
		operation.make_delivery_method_days(-1);
	});
*/

});

