<?php
add_filter( 'manage_posts_columns', 'wc2_manage_posts_columns' );
function wc2_manage_posts_columns( $columns ) {
	if( is_post_type_archive( ITEM_POST_TYPE ) ) {
		$list_column = array(
			'thumbnail' => __( 'Image' ),
			'title' => __( '商品コード／商品名', 'wc2' ),
			'skucode' => __( 'SKUコード', 'wc2' ),
			'skuprice' => __( '販売価格', 'wc2' ),
			'stock' => __( '在庫数', 'wc2' ),
			'stock_status' => __( '在庫状態', 'wc2' ),
			'category' => __( 'Categories' ),
			'date' => __( 'Publish' )
		);
		$list_column = apply_filters( 'wc2_filter_manage_posts_columns', $list_column );

		unset( $columns['title'] );
		unset( $columns['date'] );
		unset( $columns['author'] );
		unset( $columns['comments'] );
		foreach( $list_column as $key => $label ) {
			$columns[$key] = $label;
		}
	}
	return $columns;
}

add_action( 'load-edit.php', 'wc2_item_edit_title' );
function wc2_item_edit_title() {
	if( ITEM_POST_TYPE == $GLOBALS['typenow'] )
	add_filter( 'the_title', 'wc2_title_itemcode', 9, 2 );
}

function wc2_title_itemcode( $title, $post_id ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$item_code_name = $wc2_item->get_list_item_code( $post_id );
	return $item_code_name;
}

add_action( 'manage_posts_custom_column', 'wc2_manage_posts_custom_column', 10, 2 );
function wc2_manage_posts_custom_column( $column_name, $post_id ) {
	$wc2_item = WC2_DB_Item::get_instance();

	if( is_post_type_archive( ITEM_POST_TYPE ) ) {
		switch( $column_name ) {
		case 'thumbnail':
			$item_code = wc2_get_item_code_by_post_id( $post_id );
			$pictid = wc2_get_mainpictid( $item_code );
			$cart_thumbnail = ( !empty($pictid ) ) ? wc2_the_item_image( 0, 60, 60, $post_id ) : '<img src="'.WC2_PLUGIN_URL.'/common/assets/images/no-image.gif" width="60" height="60" alt="no image">';
			echo $cart_thumbnail;
			break;
		case 'title':
			break;
		case 'skucode':
			echo $wc2_item->get_list_item_sku_code( $post_id );
			break;
		case 'skuprice':
			echo $wc2_item->get_list_item_sku_price( $post_id );
			break;
		case 'stock':
			echo $wc2_item->get_list_item_sku_stock( $post_id );
			break;
		case 'stock_status':
			echo $wc2_item->get_list_item_sku_status( $post_id );
			break;
		case 'category':
			$search_genre = apply_filters( 'wc2_filter_item_search_genre', 'item' );
			$terms = get_the_terms( $post_id, $search_genre );
			if( !empty( $terms ) ) {
				$out = array();
				foreach( $terms as $term ) 
					$out[] = $term->name;
				echo join( ', ', $out );
			} else {
				_e('Uncategorized');
			}
			break;
		}

		do_action( 'wc2_action_manage_posts_custom_column' );
	}
}

//add_action( 'admin_menu', 'wc2_add_custom_box' );
add_action( 'admin_init', 'wc2_add_custom_box' );
function wc2_add_custom_box() {
	$base_info_label = apply_filters( 'wc2_filter_item_base_info_label', __( '商品情報', 'wc2' ) );
	$meta_info_label = apply_filters( 'wc2_filter_item_meta_info_label', __( '配送関連情報', 'wc2' ) );
	$sku_info_label = apply_filters( 'wc2_filter_item_sku_info_label', __( 'SKU情報', 'wc2' ) );
	$option_info_label = apply_filters( 'wc2_filter_item_option_info_label', __( '商品オプション情報', 'wc2' ) );
	$content_info_label = apply_filters( 'wc2_filter_item_content_info_label', __( '商品詳細ページ', 'wc2' ) );
	$pict_info_label = apply_filters( 'wc2_filter_item_pict_info_label', __( '商品画像', 'wc2' ) );

	add_meta_box( 'item_base', $base_info_label, 'wc2_item_base_info_box', ITEM_POST_TYPE, 'normal', 'high' );
	do_action( 'wc2_action_item_base_info_box_post' );

	add_meta_box( 'item_meta', $meta_info_label, 'wc2_item_meta_info_box', ITEM_POST_TYPE, 'normal', 'high' );
	do_action( 'wc2_action_item_meta_info_box_post' );

	add_meta_box( 'item_sku', $sku_info_label, 'wc2_item_sku_info_box', ITEM_POST_TYPE, 'normal', 'high' );
	do_action( 'wc2_action_item_sku_info_box_post' );

	add_meta_box( 'item_option', $option_info_label, 'wc2_item_option_info_box', ITEM_POST_TYPE, 'normal', 'high' );
	do_action( 'wc2_action_item_option_info_box_post' );

	add_meta_box( 'item_content', $content_info_label, 'wc2_item_content_info_box', ITEM_POST_TYPE, 'normal', 'high' );
	add_meta_box( 'item_pict', $pict_info_label, 'wc2_item_pict_info_box', ITEM_POST_TYPE, 'side', 'high' );
}

add_action( 'add_meta_boxes_item', 'wc2_admin_action_edit' );
function wc2_admin_action_edit() {
	global $post, $pagenow;
	$wc2_item = WC2_DB_Item::get_instance();

	if( $pagenow == 'post.php' ) {
		$wc2_item->set_the_post_id( $post->ID );
		$wc2_item->get_item_data();
	} else {
		$wc2_item->clear_column();
	}
}

function wc2_item_base_info_box() {
	global $post, $pagenow;
	$wc2_item = WC2_DB_Item::get_instance();

	$item_base_column = $wc2_item->get_item_base_column();

	wp_nonce_field( 'wc2_item_edit', 'wc2_nonce', false );
	ob_start();
?>
	<div id="item_base">
	<table>
<?php
	foreach( (array)$item_base_column as $key => $column ) {
		wc2_item_edit_field_e( $key, $column );
	}
?>
<?php do_action( 'wc2_action_item_base_box' ); ?>
	</table>
	<input type="hidden" name="<?php echo ITEM_ID; ?>" class="<?php echo ITEM_ID; ?>" id="<?php echo ITEM_ID; ?>" value="<?php esc_attr_e( $wc2_item->get_the_item_id() ); ?>" />
	<input type="hidden" name="<?php echo ITEM_POST_ID; ?>" class="<?php echo ITEM_POST_ID; ?>" id="<?php echo ITEM_POST_ID; ?>" value="<?php esc_attr_e( $wc2_item->get_the_post_id() ); ?>" />
	</div>
<?php
	$html = ob_get_contents();
	ob_end_clean();
	echo apply_filters( 'wc2_filter_item_base_box', $html );
}

function wc2_item_meta_info_box() {
	global $post;
	$wc2_item = WC2_DB_Item::get_instance();
	$item_meta_column = $wc2_item->get_item_meta_info_box_column();

	wp_nonce_field( 'wc2_item_edit', 'wc2_nonce', false );
	ob_start();
?>
	<div id="item_meta">
	<table>
<?php
	foreach( (array)$item_meta_column as $key => $column ) {
		wc2_item_edit_field_e( $key, $column );
	}
?>
<?php do_action( 'wc2_action_item_meta_box' ); ?>
	</table>
	</div>
<?php
	$html = ob_get_contents();
	ob_end_clean();
	echo apply_filters( 'wc2_filter_item_meta_box', $html );
}

function wc2_item_sku_info_box() {
	global $post;
	$wc2_item = WC2_DB_Item::get_instance();
	$sku_column = $wc2_item->get_item_sku_column();
	$sku_meta_column = $wc2_item->get_item_sku_meta_column();
	$item_sku = $wc2_item->get_item_sku();
	$sku_id_max = $wc2_item->get_item_sku_id_max();

	wp_nonce_field( 'wc2_item_edit', 'wc2_nonce', false );
	ob_start();
?>
	<div id="item_sku">
	<ul id="item_sku_update">
<?php
	foreach( (array)$item_sku as $id => $sku ) :
?>
	<li id="item_sku_<?php echo $id; ?>">
	<table>
		<?php wc2_item_sku_table_header_e(); ?>
		<?php wc2_item_sku_table_data_e( $id ); ?>
<?php do_action( 'wc2_action_item_sku_box', $sku, $id ); ?>
	</table>
	<table>
<?php
		if( 0 < count($sku_meta_column) ) {
			foreach( (array)$sku_meta_column as $key => $column ) {
				wc2_item_sku_edit_field_e( $key, $column, (string)$id );
			}
		}
?>
<?php do_action( 'wc2_action_item_sku_meta_box', $sku, $id ); ?>
	</table>
	<div>
		<input type="button" name="delete_sku[<?php echo $id; ?>]" class="delete_sku button" id="delete_sku_<?php echo $id; ?>" value="<?php esc_attr_e(__( 'Delete' )); ?>" onclick="wc2_item_sku.del(<?php echo $id; ?>);" />
		<input type="hidden" name="sku_id[<?php echo $id; ?>]" id="sku_id_<?php echo $id; ?>" value="<?php echo $sku[ITEM_SKU_ID]; ?>" />
		<input type="hidden" name="sku_sort[<?php echo $id; ?>]" id="sku_sort_<?php echo $id; ?>" value="<?php echo $sku[ITEM_SKU_SORT]; ?>" />
	</div>
	</li><!-- end item_sku_<?php echo $id; ?> -->
<?php
	endforeach;
?>
	</ul><!-- end item_sku_update -->
	<input type="hidden" id="sku_id_max" value="<?php echo $sku_id_max; ?>" />
	<input type="hidden" id="orders" name="sku_orders" value="" />
<?php
	$id = '0';
?>
	<div><?php _e( '新しいSKUの追加：', 'wc2' ); ?></div>
	<div id="item_sku_<?php echo $id; ?>">
	<table>
		<?php wc2_item_sku_table_header_e(); ?>
		<?php wc2_item_sku_table_data_e( $id ); ?>
<?php do_action( 'wc2_action_item_sku_box_new' ); ?>
	</table>
	<table>
<?php
	if( 0 < count($sku_meta_column) ) {
		foreach( (array)$sku_meta_column as $key => $column ) {
			wc2_item_sku_edit_field_e( $key, $column, (string)$id );
		}
	}
?>
<?php do_action( 'wc2_action_item_sku_meta_box_new' ); ?>
	</table>
	<div>
		<input type="button" name="add_sku[<?php echo $id; ?>]" class="add_sku button" id="add_sku_<?php echo $id; ?>" value="<?php esc_attr_e(__( 'Add SKU', 'wc2' )); ?>" onclick="wc2_item_sku.add();" />
		<input type="hidden" name="sku_id[<?php echo $id; ?>]" id="sku_id_<?php echo $id; ?>" value="<?php echo $id; ?>" />
	</div>
	</div><!-- end item_sku_<?php echo $id; ?> -->
	</div>
<?php
	$html = ob_get_contents();
	ob_end_clean();
	echo apply_filters( 'wc2_filter_item_sku_box', $html );
}

function wc2_item_option_info_box() {
	global $post;
	$wc2_item = WC2_DB_Item::get_instance();

	wp_nonce_field( 'wc2_item_edit', 'wc2_nonce', false );
	ob_start();
?>
	<div id="item_option">
	<table>
<?php
	//$opts = WC2_Funcs::wc2_get_opts($post->ID);
	//WC2_Funcs::list_item_option_meta($opts);
	//WC2_Funcs::item_option_meta_form();

	do_action( 'wc2_action_item_option_box' );
?>
	</table>
	</div>
<?php
	$html = ob_get_contents();
	ob_end_clean();
	echo apply_filters( 'wc2_filter_item_option_box', $html );
}
/*
	/********************************
	* 商品オプション

	*********************************
	function item_option_ajax() {
	if( $_POST['action'] != 'item_option_ajax' ) die(0);
	
	if(isset($_POST['update'])){
		$id = $this->up_item_option_meta( $_POST['ID'] );
		
	}else if(isset($_POST['delete'])){
		$id = $this->del_item_option_meta( $_POST['ID'] );
		
	}else if(isset($_POST['select'])){
		$res = $this->select_common_option( $_POST['ID'] );
		die( $res );
		
	}else if(isset($_POST['sort'])){
		$id = $this->wc2_sort_post_meta( $_POST['ID'], $_POST['meta'] );
		//die( $res );
		
	}else{
		$id = $this->add_item_option_meta( $_POST['ID'] );
		
	}
		
	$opts = WC2_Funcs::wc2_get_opts( $_POST['ID'] );
	
	$r = '';
	foreach ( $opts as $opt )
		$r .= WC2_Funcs::_list_item_option_meta_row( $opt );
	
	$res = $r . '#wc2#' . $id;

	die( $res );
	}

	/******************************
		商品オプション追加
	*******************************
	function add_item_option_meta( $post_ID ) {
		$post_ID = (int) $post_ID;
		$value = array();
		$opts = array();
		$protected = array( '#NONE#', '_wp_attached_file', '_wp_attachment_metadata', '_wp_old_slug', '_wp_page_template' );

		$newoptname = isset($_POST['newoptname']) ? trim( $_POST['newoptname'] ) : '';
		$newoptmeans = isset($_POST['newoptmeans']) ? (int)$_POST['newoptmeans']: 0;
		$newoptessential = isset($_POST['newoptessential']) ? $_POST['newoptessential']: 0;
		$newoptvalue = isset($_POST['newoptvalue']) ? trim($_POST['newoptvalue']) : '';
		
		if ( ($newoptmeans >= 2 || WC2_Utils::is_zero($newoptvalue) || !empty ( $newoptvalue )) && !empty ( $newoptname) ) {
			if ( $newoptname )
				$metakey = $newoptname; // default

			if ( in_array($metakey, $protected) )
				return false;

			wp_cache_delete($post_ID, 'post_meta');
			
			$value['name'] = str_replace("\\",'',$newoptname);
			$value['means'] = $newoptmeans;
			$value['essential'] = $newoptessential;
			$value['value'] = str_replace("\\",'',$newoptvalue);
			$value = WC2_Utils::stripslashes_deep_post($value);

			$id = WC2_Funcs::wc2_add_opt($post_ID, $value);

			return $id;
		}
		return false;
	}


	/**********************************
	* 商品オプション更新
	***********************************
	function up_item_option( $post_ID ) {
		$post_ID = (int) $post_ID;

		$general_options = wc2_get_option('general');

		$optmetaid = isset($_POST['optmetaid']) ? (int)$_POST['optmetaid'] : '';
		$optname = isset($_POST['optname']) ? $_POST['optname'] : '';
		$optmeans = isset($_POST['optmeans']) ? (int)$_POST['optmeans']: 0;
		$optessential = isset($_POST['optessential']) ? $_POST['optessential']: 0;
		$optsort = isset($_POST['sort']) ? $_POST['sort']: 0;
		$optvalue = isset($_POST['optvalue']) ? trim($_POST['optvalue']) : '';

		$value = array();
		$value['name'] = str_replace("\\",'',$optname);
		$value['means'] = $optmeans;
		$value['essential'] = $optessential;
		$value['value'] = str_replace("\\",'',$optvalue);
		$value['sort'] = $optsort;
		$value = WC2_Utils::stripslashes_deep_post($value);
		$valueserialized = serialize($value);

		$general_options['_iopt_'] = $value;

//		wp_cache_delete($post_ID, 'post_meta');

		$res = wc2_update_option( 'general', $general_options );

//		$res = $wpdb->query( $wpdb->prepare("UPDATE $wpdb->postmeta SET meta_value = %s WHERE meta_id = %d", $valueserialized, $optmetaid) );

		return $res;
	}

	/**********************************
	* 商品オプション削除
	***********************************
	function del_item_option( $post_ID ) {
		$post_ID = (int) $post_ID;
		$optmetaid = isset($_POST['optmetaid']) ? (int)$_POST['optmetaid'] : '';

		wp_cache_delete($post_ID, 'post_meta');

		$res= wc2_remove_option();
//		$res = $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE meta_id = %d", $optmetaid) );

		$opts = usces_get_opts($post_ID);
		if( !empty($opts) ){
			$i = 0;
			foreach( $opts as $opt ){
				$opt['sort'] = $i;
				$meta_id = $opt['meta_id'];
				unset($opt['meta_id']);
				$serialized_values = serialize($opt);
				$wpdb->query( $wpdb->prepare("UPDATE $wpdb->postmeta SET meta_value = %s WHERE meta_id = %d", $serialized_values, $meta_id) );
				$i++;
			}
		}
		return ;
	}

	public function enqueue_item_scripts() {
		$screen = get_current_screen();

		if( 'item' == $screen->id ){
			wp_enqueue_script('jquery');
			wp_enqueue_script( $this->plugin_slug . '_item_script', plugins_url( 'assets/js/item_option.js', __FILE__ ), array( 'jquery' ), Welcart2::VERSION );
		}
	}
*/

function wc2_item_content_info_box() {
	global $post;
	$wc2_item = WC2_DB_Item::get_instance();
?>
<div class="postbox">
<?php //if( post_type_supports( $post->post_type, 'title' ) ) : ?>
<div class="inside">
<div class="itempagetitle"><?php _e( 'ページタイトル：', 'wc2' ); ?></div>
<div id="titlediv">
	<div id="titlewrap">
		<label class="hide-if-no-js" style="visibility:hidden" id="title-prompt-text" for="title"><?php _e('Enter title here'); ?></label>
		<input type="text" name="post_title" size="30" tabindex="1" value="<?php esc_attr_e( htmlspecialchars( $post->post_title ) ); ?>" id="title" autocomplete="off" />
	</div>
<?php
$sample_permalink_html = get_sample_permalink_html( $post->ID );
$shortlink = wp_get_shortlink( $post->ID, ITEM_POST_TYPE );
if( !empty($shortlink) )
	$sample_permalink_html .= '<input id="shortlink" type="hidden" value="'.esc_attr($shortlink).'" /><a href="#" class="button" onclick="prompt(&#39;URL:&#39;, jQuery(\'#shortlink\').val()); return false;">'.__('Get Shortlink').'</a>';

if( !( 'pending' == $post->post_status && !current_user_can( $post_type_object->cap->publish_posts ) ) ) : ?>
	<div id="edit-slug-box">
	<?php
		if( !empty($post->ID) && !empty($sample_permalink_html) && 'auto-draft' != $post->post_status )
			echo $sample_permalink_html;
	?>
	</div>
<?php
endif;
?>
</div>
<?php
wp_nonce_field( 'samplepermalink', 'samplepermalinknonce', false );
?>
<?php //endif; ?>
<?php //if( post_type_supports($post->post_type, 'editor' ) ) : ?>
<div class="itempagetitle"><?php _e( '商品説明：', 'wc2' ); ?></div>
<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
<style type="text/css">
<!--
.wp_themeSkin table td {
	background-color: white;
}
-->
</style>
<?php
	wp_editor( $post->post_content, 'content', array( 'dfw' => true, 'tabindex' => 1 ) );
?>
<table id="post-status-info" cellspacing="0"><tbody><tr>
	<td id="wp-word-count"></td>
	<td class="autosave-info">
	<span id="autosave">&nbsp;</span>
<?php
	if( 'auto-draft' != $post->post_status ) {
		echo '<span id="last-edit">';
		if( $last_id = get_post_meta( $post->ID, '_edit_last', true ) ) {
			$last_user = get_userdata( $last_id );
			printf(__('Last edited by %1$s on %2$s at %3$s'), esc_attr($last_user->display_name), mysql2date(get_option('date_format'), $post->post_modified), mysql2date(get_option('time_format'), $post->post_modified));
		} else {
			if( isset( $post->post_modified ) )
				printf(__('Last edited on %1$s at %2$s'), mysql2date(get_option('date_format'), $post->post_modified), mysql2date(get_option('time_format'), $post->post_modified));
		}
		echo '</span>';
	}
?>
	</td>
</tr></tbody></table>
</div>
<?php //endif; ?>
</div>
</div>
<?php
}

function wc2_item_pict_info_box( $post ) {
	global $current_screen;
	$wc2_item = WC2_DB_Item::get_instance();

	$item_picts = array();
	$item_sumnails = array();
	$post_id = ( isset($post->ID) ) ? $post->ID : 0;
	$item_code = wc2_get_item_code_by_post_id( $post_id );
	if( !empty($item_code) ) {
		$pictid = (int)$wc2_item->get_mainpictid( $item_code );

		$item_picts[] = wp_get_attachment_image( $pictid, array(260, 200), true );
		$item_sumnails[] = wp_get_attachment_image( $pictid, array(50, 50), true );
		$item_pictids = $wc2_item->get_pictids( $item_code );
		for( $i = 0; $i < count($item_pictids); $i++ ) {
			$item_picts[] = wp_get_attachment_image( $item_pictids[$i], array(260, 200), true );
			$item_sumnails[] = wp_get_attachment_image( $item_pictids[$i], array(50, 50), true );
		}
	}
?>
	<div class="item-main-pict">
		<div id="item-select-pict">
<?php
	if( $item_sumnails ) :
		echo $item_picts[0];
	else:
?>
	<!--<img src="#" width="260" height="200" alt="" />-->
<?php
	endif;
?>
		</div>
		<div class="clearfix">
<?php for( $i = 0; $i < count($item_sumnails); $i++ ) : ?>
			<div class="subpict"><a onclick='wc2_item.changepict("<?php echo str_replace( '"', '\"', $item_picts[$i] ); ?>");'><?php echo $item_sumnails[$i]; ?></a></div>
<?php endfor; ?>
		</div>
	</div>
<?php
}

add_action( 'publish_'.ITEM_POST_TYPE, 'wc2_update_item_data', 10, 2 );//公開・ゴミ箱から復元
//add_action( 'future_'.ITEM_POST_TYPE, 'wc2_update_item_data', 10, 2 );//予約
add_action( 'draft_'.ITEM_POST_TYPE, 'wc2_update_item_data', 10, 2 );//下書き
//add_action( 'pending_'.ITEM_POST_TYPE, 'wc2_update_item_data', 10, 2 );//レビュー待ち
//add_action( 'private_'.ITEM_POST_TYPE, 'wc2_update_item_data', 10, 2 );//非公開
//add_action( 'trash_'.ITEM_POST_TYPE, 'wc2_update_item_data', 10, 2 );//ゴミ箱
function wc2_update_item_data( $post_id, $post ) {
	$wc2_item = WC2_DB_Item::get_instance();

	if( empty($_POST) )
		return;

	$message = '';

	check_admin_referer( 'wc2_item_edit', 'wc2_nonce' );
	//if( !wp_verify_nonce( 'wc2_item_edit', 'wc2_nonce' ))
	//	return $post_id;

	if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
		return $post_id;

	$post_status = get_post_status( $post_id );
	if( !$post_status or $post_status == 'trash' )
		return $post_id;

	$wc2_item->set_the_post_id( $post_id );
	if( array_key_exists( ITEM_ID, $_POST ) ) {
		$wc2_item->set_the_item_id( $_POST[ITEM_ID] );
	}

	$item_base_column = $wc2_item->get_item_base_column();
	$item_meta_column = $wc2_item->get_item_meta_column();
	$sku_column = $wc2_item->get_item_sku_column();
	$sku_meta_column = $wc2_item->get_item_sku_meta_column();

	foreach( $item_base_column as $key => $column ) {
		if( array_key_exists( $key, $_POST ) ) {
			$wc2_item->set_the_item_value( $key, $_POST[$key] );
		}
	}
	foreach( $item_meta_column as $key => $column ) {
		if( array_key_exists( $key, $_POST ) ) {
			$wc2_item->set_the_item_value( $key, $_POST[$key] );
		}
	}
	$sku_id = ( isset( $_POST[ITEM_SKU_ID] ) ) ? $_POST[ITEM_SKU_ID] : array();
	foreach( $sku_id as $id ) {
		if( array_key_exists( ITEM_SKU_ID, $_POST ) ) {
			$wc2_item->set_the_item_sku_value( ITEM_SKU_ID, $id, $_POST[ITEM_SKU_ID][$id] );
		}
		if( array_key_exists( ITEM_SKU_SORT, $_POST ) ) {
			$wc2_item->set_the_item_sku_value( ITEM_SKU_SORT, $id, $_POST[ITEM_SKU_SORT][$id] );
		}
		foreach( $sku_column as $key => $column ) {
			if( array_key_exists( $key, $_POST ) ) {
				$wc2_item->set_the_item_sku_value( $key, $id, $_POST[$key][$id] );
			}
		}
		foreach( $sku_meta_column as $key => $column ) {
			if( array_key_exists( $key, $_POST ) ) {
				$wc2_item->set_the_item_sku_value( $key, $id, $_POST[$key][$id] );
			}
		}
	}

	// SKU sort orders
	if( !empty($_POST['sku_orders']) ) {
		$sku_orders = explode( ',', $_POST['sku_orders'] );
		$sort = 1;
		foreach( $sku_orders as $orders ) {
			$sku = explode( '_', $orders );
			$wc2_item->set_the_item_sku_value( ITEM_SKU_SORT, $sku[2], $sort );
			$sort++;
		}
	}

	do_action( 'wc2_action_admin_update_item_data_pre', $post_id, $post );
	$wc2_item->update_item_data( true );
	do_action( 'wc2_action_admin_update_item_data', $post_id, $post );
}

function wc2_item_edit_field_input( $key, $column, $sku, $sku_id, $data ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$html = '';
	if( $column['display'] == 'hidden' ) {
		$html .= '
		<input type="hidden" name="'.esc_attr($key).$sku.'" class="'.esc_attr($key).'" id="'.esc_attr($key).$sku_id.'" value="'.esc_attr($data).'" />';

	} elseif( $column['display'] == '' ) {
		switch( $column['type'] ) {
		case TYPE_SELECT:
		case TYPE_SELECT_MULTIPLE:
			$multiple = ( $column['type'] == TYPE_SELECT_MULTIPLE ) ? ' multiple' : '';
			$multiple_array = ( $column['type'] == TYPE_SELECT_MULTIPLE ) ? '[]' : '';
			$select = explode( ';', $column['value'] );
			$html .= $column['label_pre'].'
				<select name="'.esc_attr($key).$multiple_array.$sku.'" class="select" id="'.esc_attr($key).$sku_id.'"'.$multiple.'>';
			if( $column['essential'] == 1 )
				$html .= '
					<option value="#NONE#">'.__( '選択してください', 'wc2' ).'</option>';
			foreach( $select as $option ) {
				list( $value, $name ) = explode( ':', $option );
				$selected = ( $data == $value ) ? ' selected="selected"' : '';
				$html .= '
					<option value="'.esc_attr($value).'"'.$selected.'>'.esc_attr($name).'</option>';
			}
			$html .= '
				</select>'.$column['label_post'];
			break;

		case TYPE_TEXT:
		case TYPE_TEXT_Z:
		case TYPE_TEXT_ZK:
		case TYPE_TEXT_A:
			$class = ( empty($sku_id) ) ? 'regular-text ' : '';
			$html .= $column['label_pre'].'
				<input type="text" name="'.esc_attr($key).$sku.'" class="'.$class.esc_attr($key).'" id="'.esc_attr($key).$sku_id.'" value="'.esc_attr($data).'" />'.$column['label_post'];
			break;

		case TYPE_TEXT_I:
		case TYPE_TEXT_F:
			$class = ( empty($sku_id) ) ? 'regular-text ' : '';
			$html .= $column['label_pre'].'
				<input type="text" name="'.esc_attr($key).$sku.'" class="'.$class.esc_attr($key).' right" id="'.esc_attr($key).$sku_id.'" value="'.esc_attr($data).'" />'.$column['label_post'];
			break;

		case TYPE_TEXT_P:
			$class = ( empty($sku_id) ) ? 'regular-text ' : '';
			$html .= $column['label_pre'].'
				<input type="text" name="'.esc_attr($key).$sku.'" class="'.$class.esc_attr($key).' right" id="'.esc_attr($key).$sku_id.'" value="'.esc_attr(floor($data)).'" />'.$column['label_post'];
			break;

		case TYPE_RADIO:
			$select = explode( ';', $column['value'] );
			foreach( $select as $option ) {
				list( $value, $name ) = explode( ':', $option );
				$checked = ( $data == $value ) ? ' checked="checked"' : '';
				$html .= $column['label_pre'].'
				<label title="'.esc_attr($value).'"><input type="radio" name="'.esc_attr($key).$sku.'" class="'.esc_attr($key).'" id="'.esc_attr($key).'_'.esc_attr($value).$sku_id.'" value="'.esc_attr($value).'"'.$checked.' /><span>'.esc_attr($name).'</span></label>'.$column['label_post'];
			}
			break;

		case TYPE_CHECK:
			$select = explode( ';', $column['value'] );
			foreach( $select as $option ) {
				list( $value, $name ) = explode( ':', $option );
				if( is_array($data) ) {
					$checked = ( array_key_exists( $value, $data ) ) ? ' checked="checked"' : '';
				} else {
					$checked = ( $data == $value ) ? ' checked="checked"' : '';
				}
				$html .= $column['label_pre'].'
				<label for="'.esc_attr($key).'_'.esc_attr($value).'"><input type="checkbox" name="'.esc_attr($key).$sku.'" id="'.esc_attr($key).'_'.esc_attr($value).$sku_id.'" value="'.esc_attr($value).'"'.$checked.' />'.esc_attr($name).'</label>'.$column['label_post'];
			}
			break;

		case TYPE_TEXTAREA:
			$html .= $column['label_pre'].'
				<textarea name="'.esc_attr($key).$sku.'" class="large-text '.esc_attr($key).'" id="'.esc_attr($key).$sku_id.'" rows="5">'.esc_attr($data).'</textarea>'.$column['label_post'];
		}
	}
	return $html;
}

function wc2_item_edit_field( $key, $column, $id = '' ) {
	$wc2_item = WC2_DB_Item::get_instance();

	if( '' != $id ) {
		$sku = '['.esc_attr($id).']';
		$sku_id = '_'.esc_attr($id);
	} else {
		$sku = '';
		$sku_id = '';
	}

	$html = '';
	if( $column['type'] == TYPE_PARENT ) {
		if( $column['display'] == '' ) {
			$label = $wc2_item->get_the_item_label( $key );
			$html .= '
			<tr><th>'.esc_attr($label).'</th><td>';
			$item_column = $wc2_item->get_item_column_all();
			$child = '';
			foreach( $item_column as $child_key => $child_column ) {
				if( array_key_exists( 'parent', $child_column ) and $child_column['parent'] == $key ) {
					$data = '';
					if( '' != $id ) {
						$data = $wc2_item->get_the_item_sku_value( $child_key, $id );
					} else {
						$data = $wc2_item->get_the_item_value( $child_key );
					}
					$child .= wc2_item_edit_field_input( $child_key, $child_column, $sku, $sku_id, $data );
				}
			}
			$html .= $child.'
			</td></tr>';
		}

	} elseif( $column['parent'] == '' ) {
		$data = '';
		if( '' != $id ) {
			$data = ( $column['type'] != TYPE_PARENT ) ? $wc2_item->get_the_item_sku_value( $key, $id ) : '';
		} else {
			$data = ( $column['type'] != TYPE_PARENT ) ? $wc2_item->get_the_item_value( $key ) : '';
		}
		if( $column['display'] == 'hidden' ) {
			$html .= '
			<input type="hidden" name="'.esc_attr($key).$sku.'" class="'.esc_attr($key).'" id="'.esc_attr($key).$sku_id.'" value="'.esc_attr($data).'" />';
		} elseif( $column['display'] == '' ) {
			$label = $wc2_item->get_the_item_label( $key );
			$html .= '
			<tr><th>'.esc_attr($label).'</th><td>';
				$html .= wc2_item_edit_field_input( $key, $column, $sku, $sku_id, $data );
			$html .= '
			</td></tr>';
		}
	}

	return stripslashes( $html );
}

function wc2_item_edit_field_e( $key, $column ) {
	echo wc2_item_edit_field( $key, $column );
}

function wc2_item_sku_edit_field_e( $key, $column, $id ) {
	echo wc2_item_edit_field( $key, $column, $id );
}

add_action( 'delete_post', 'wc2_delete_item_data' );
function wc2_delete_item_data( $post_id ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$wc2_item->set_the_post_id( $post_id );
	$wc2_item->delete_item_data();
}

function wc2_item_sku_table_header_e() {
	$wc2_item = WC2_DB_Item::get_instance();
	$sku_format = $wc2_item->get_item_sku_format();
	$sku_column = $wc2_item->get_item_sku_column();

	$html = '<thead>';
	foreach( $sku_format as $sku ) {
		$html .= '<tr>';
		foreach( $sku as $key ) {
			$label = ( array_key_exists( $key, $sku_column ) and $sku_column[$key]['display'] == '' ) ? esc_attr( $wc2_item->get_the_item_label( $key ) ) : '';
			$html .= '<th>'.$label.'</th>';
		}
		$html .= '</tr>';
	}
	$html .= '</thead>';
	$html = apply_filters( 'wc2_filter_item_sku_table_header', $html );
	echo $html;
}

function wc2_item_sku_table_data_e( $id ) {
	$wc2_item = WC2_DB_Item::get_instance();
	$sku_format = $wc2_item->get_item_sku_format();
	$sku_column = $wc2_item->get_item_sku_column();

	$html = '<tbody>';
	foreach( $sku_format as $sku ) {
		$html .= '<tr>';
		foreach( $sku as $key ) {
			if( !empty($key) ) {
				$column = $sku_column[$key];
				$sku = '['.esc_attr($id).']';
				$sku_id = '_'.esc_attr($id);
				$data = $wc2_item->get_the_item_sku_value( $key, $id );
				$field = wc2_item_edit_field_input( $key, $column, $sku, $sku_id, $data );
			} else {
				$field = '';
			}
			$html .= '<td>'.$field.'</td>';
		}
		$html .= '</tr>';
	}
	$html .= '</tbody>';
	foreach( $sku_column as $key => $column ) {
		if( $column['display'] == 'hidden' ) {
			$sku_id = '_'.esc_attr($id);
			$data = $wc2_item->get_the_item_sku_value( $key, $id );
			$html .= wc2_item_edit_field_input( $key, $column, '', $sku_id, $data );
		}
	}
	$html = apply_filters( 'wc2_filter_item_sku_table_data', $html );
	echo $html;
}

add_action( 'wp_ajax_wc2_edit_item_ajax', 'wc2_edit_item_ajax' );
function wc2_edit_item_ajax() {
	$wc2_item = WC2_DB_Item::get_instance();

	$html = '';
	if( $_POST['mode'] == 'add_sku' ) {
		$sku_column = $wc2_item->get_item_sku_column();
		$sku_meta_column = $wc2_item->get_item_sku_meta_column();
		$id = $_POST['add_id'];
		if( array_key_exists( ITEM_SKU_ID, $_POST ) ) {
			$wc2_item->set_the_item_sku_value( ITEM_SKU_ID, $id, $_POST[ITEM_SKU_ID] );
		}
		if( array_key_exists( ITEM_SKU_SORT, $_POST ) ) {
			$wc2_item->set_the_item_sku_value( ITEM_SKU_SORT, $id, $_POST[ITEM_SKU_SORT] );
		}

		foreach( $sku_column as $key => $column ) {
			if( array_key_exists( $key, $_POST ) ) {
				$wc2_item->set_the_item_sku_value( $key, $id, $_POST[$key] );
			}
		}
		foreach( $sku_meta_column as $key => $column ) {
			if( array_key_exists( $key, $_POST ) ) {
				$wc2_item->set_the_item_sku_value( $key, $id, $_POST[$key] );
			}
		}

		ob_start();
?>
	<li id="item_sku_<?php echo $id; ?>">
	<table>
		<?php wc2_item_sku_table_header_e(); ?>
		<?php wc2_item_sku_table_data_e( $id ); ?>
<?php do_action( 'wc2_action_item_sku_box' ); ?>
	</table>
	<table>
<?php
		if( 0 < count($sku_meta_column) ) :
			foreach( (array)$sku_meta_column as $key => $column ) :
				wc2_item_sku_edit_field_e( $key, $column, (string)$id );
			endforeach;
		endif;
?>
		<?php do_action( 'wc2_action_item_sku_meta_box' ); ?>
	</table>
	<div>
		<input type="button" name="delete_sku[<?php echo $id; ?>]" class="delete_sku button" id="delete_sku_<?php echo $id; ?>" value="<?php esc_attr_e(__( 'Delete' )); ?>" onclick="wc2_item_sku.del(<?php echo $id; ?>);" />
		<input type="hidden" name="sku_id[<?php echo $id; ?>]" id="sku_id_<?php echo $id; ?>" value="<?php echo $id; ?>" />
		<input type="hidden" name="sku_sort[<?php echo $id; ?>]" id="sku_sort_<?php echo $id; ?>" value="<?php echo $id; ?>" />
	</div>
	</li><!-- end item_sku_<?php echo $id; ?> -->
<?php
		$html = ob_get_contents();
		ob_end_clean();
	}
	die( $html );
}

add_action( 'admin_footer', 'wc2_item_edit_js' );
function wc2_item_edit_js() {
	global $post_type, $pagenow;

	//if( ITEM_POST_TYPE != $post_type or $pagenow != 'edit.php' ) return;
	if( ITEM_POST_TYPE != $post_type or $pagenow != 'edit.php' or array_key_exists( 'page', $_GET ) ) return;

	$stock_status = wc2_get_option( 'stock_status' );
	$display_status = array(
		'publish'=>__('Published'),
		'future'=>__('Scheduled'),
		'draft'=>__('Draft'),
		'pending'=>__('Pending Review'),
		'private'=>__('Private'),
		//'trash'=>__('Trash'),
	);
	$search_condition = array_key_exists( 'search_condition', $_GET ) ? $_GET['search_condition'] : '';
	$search_word = array_key_exists( 'search_word', $_GET ) ? $_GET['search_word'] : '';

	ob_start();
?>
<script type="text/javascript">
jQuery(function($) {
	$(document).on( "change", "#search_condition", function() {
		var fld = '';
		switch( $(this).val() ) {
		case "item_code":
		case "item_name":
			fld = '<input name="search_word" id="search_word" type="text" value="<?php esc_attr_e( $search_word ); ?>" />';
			break;
		case "stock_status":
			fld = '<select name="search_word" id="search_word">';
<?php		foreach( $stock_status as $key => $value ) : 
				$selected = ( $key == $search_word ) ? ' selected="selected"' : '';
?>
			fld += '<option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php esc_attr_e( $value ); ?></option>';

<?php		endforeach; ?>
			fld += '</select>';
			break;
		case "display_status":
			fld = '<select name="search_word" id="search_word">';
<?php		foreach( $display_status as $key => $value ) : 
				$selected = ( $key == $search_word ) ? ' selected="selected"' : '';
?>
			fld += '<option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php esc_attr_e( $value ); ?></option>';

<?php		endforeach; ?>
			fld += '</select>';
			break;
		case "stock0":
		default:
		}
		$("#search_field").html( fld );
	});
<?php if( '' != $search_condition ) : ?>
	$("#search_condition").val( "<?php echo $search_condition; ?>" );
	$("#search_condition").trigger( "change" );
<?php endif; ?>
<?php do_action( 'wc2_action_item_edit_js' ); ?>
});
</script>
<?php
	$js = ob_get_contents();
	ob_end_clean();
	echo apply_filters( 'wc2_filter_item_edit_js', $js );
}

add_action( 'admin_footer', 'wc2_item_post_js' );
function wc2_item_post_js() {
	global $post_type, $pagenow;

	if( ITEM_POST_TYPE != $post_type or ( $pagenow != 'post.php' and $pagenow != 'post-new.php' ) ) return;

	$wc2_item = WC2_DB_Item::get_instance();

	$sku_column = array_merge( $wc2_item->get_item_sku_column(), $wc2_item->get_item_sku_meta_column() );

	ob_start();
?>
<script type="text/javascript">
jQuery(function($) {
	wc2_item_sku = {
		add: function() {
			var add_id = parseInt($("#sku_id_max").val())+1;
			var data_obj = {};
			data_obj.action = "wc2_edit_item_ajax";
			data_obj.mode = "add_sku";
			data_obj.add_id = add_id;
<?php
	foreach( (array)$sku_column as $key => $column ) :
		if( $sku_column[$key]['display'] == '' ) :
			switch( $column['type'] ) :
			case TYPE_SELECT:
?>
			if( $("select[name='<?php echo $key; ?>[0]']") != undefined ) {
				data_obj.<?php echo $key; ?> = $("select[name='<?php echo $key; ?>[0]'] option:selected").val();
			}
<?php
				break;
			case TYPE_SELECT_MULTIPLE:
?>
			if( $("select[name='<?php echo $key; ?>[0]']") != undefined ) {
				var mlt_vals = {};
				$("select[name='<?php echo $key; ?>[0]'] option:selected").each( function() {
					mlt_vals[$(this).val()] = $(this).val();
				});
				data_obj.<?php echo $key; ?> = mlt_vals;
			}
<?php
				break;

			case TYPE_TEXT:
			case TYPE_TEXT_Z:
			case TYPE_TEXT_ZK:
			case TYPE_TEXT_A:
			case TYPE_TEXT_I:
			case TYPE_TEXT_F:
			case TYPE_TEXT_P:
?>
			if( $("input[name='<?php echo $key; ?>[0]']") != undefined ) {
				data_obj.<?php echo $key; ?> = $("input[name='<?php echo $key; ?>[0]']").val();
			}
<?php
				break;

			case TYPE_RADIO:
?>
			if( $("input[name='<?php echo $key; ?>[0]']") != undefined ) {
				data_obj.<?php echo $key; ?> = $("input[name='<?php echo $key; ?>[0]'] :checked").val();
			}
<?php
				break;

			case TYPE_CHECK:
?>
			if( $("input[name='<?php echo $key; ?>[0]']") != undefined ) {
				var chk_vals = {};
				$("input[name='<?php echo $key; ?>[0]']:checked").each( function() {
					chk_vals[$(this).val()] = $(this).val();
				});

				data_obj.<?php echo $key; ?> = chk_vals;
			}
<?php
				break;

			case TYPE_TEXTAREA:
?>
			if( $("input[name='<?php echo $key; ?>[0]']") != undefined ) {
				data_obj.<?php echo $key; ?> = $("input[name='<?php echo $key; ?>[0]']").text();
			}
<?php
				break;
			endswitch;
		endif;
	endforeach;
?>
			$.ajax({
				url: ajaxurl,
				type: "POST",
				data: data_obj
			}).done(function( retVal, dataType ) {
				$("#item_sku_update").append( retVal );
				$("#item_sku_"+add_id).css({ 'background-color':'#ff4' });
				$("#item_sku_"+add_id).animate({ 'background-color':'#f9f9f9' }, 2000 );
				$("#sku_id_max").val( add_id );
				wc2_item_sku.add_create();
			}).fail(function( retVal ) {
			});
			return false;
		},
		del: function( id ) {
			if( confirm("<?php _e( '削除します。よろしいですか？', 'wc2' ); ?>") ) {
				//$("#item_sku_"+id).css({ 'background-color':'#f00' });
				//$("#item_sku_"+id).animate({ 'background-color':'#f9f9f9' }, 1000 );
				$("#item_sku_"+id).remove();
			}
		},
		upd: function( id ) {
		},
		add_create: function() {
<?php
	foreach( (array)$sku_column as $key => $column ) :
		if( $sku_column[$key]['display'] == '' ) :
			switch( $column['type'] ) :
			case TYPE_SELECT:
			case TYPE_SELECT_MULTIPLE:
				if( !empty($sku_column[$key]['default']) ) :
?>
			if( $("select[name='<?php echo $key; ?>[0]']") != undefined ) {
				$("select[name='<?php echo $key; ?>[0]'] option[value='<?php esc_attr_e( $sku_column[$key]['default'] ); ?>']").prop( "selected", true );
			}
<?php
				endif;
				break;

			case TYPE_TEXT:
			case TYPE_TEXT_Z:
			case TYPE_TEXT_ZK:
			case TYPE_TEXT_A:
			case TYPE_TEXT_I:
			case TYPE_TEXT_F:
			case TYPE_TEXT_P:
?>
			if( $("input[name='<?php echo $key; ?>[0]']") != undefined ) {
				$("input[name='<?php echo $key; ?>[0]']").val( "" );
			}
<?php
				break;

			case TYPE_RADIO:
			case TYPE_CHECK:
?>
			if( $("input[name='<?php echo $key; ?>[0]']") != undefined ) {
				$("input[name='<?php echo $key; ?>[0]']").attr( "checked", false );
			}
<?php
				break;

			case TYPE_TEXTAREA:
?>
			if( $("input[name='<?php echo $key; ?>[0]']") != undefined ) {
				$("input[name='<?php echo $key; ?>[0]']").text( "" );
			}
<?php
				break;
			endswitch;
		endif;
	endforeach;
?>
		},
		sort: function( id ) {
		}
	};

	$("#item_sku_update").sortable({
		axis: "y",
		tolerance: "pointer",
		forceHelperSize: true,
		forcePlaceholderSize: true,
		revert: 300,
		opacity: 0.6,
		update: function( event, ui ) {
			var arr = $("#item_sku_update").sortable( "toArray" ).toString();
			$("#orders").val( arr );
		}
	});
	$("#item_sku_update").disableSelection();

	wc2_item = {
		changepict: function( code ) {
			$("div#item-select-pict").html( code );
		},
	};
	wc2_item_sku.add_create();
<?php do_action( 'wc2_action_item_post_js' ); ?>
});
</script>
<?php
	$js = ob_get_contents();
	ob_end_clean();
	echo apply_filters( 'wc2_filter_item_post_js', $js );
}

add_filter( 'post_updated_messages', 'wc2_item_updated_messages' );
function wc2_item_updated_messages( $messages ) {
	global $post, $post_ID;
	$messages[ITEM_POST_TYPE] = array(
		0 => '',
		1 => sprintf( __('商品を更新しました <a href="%s">商品詳細ページを見る</a>'), esc_url( get_permalink($post_ID) ) ),
		2 => __('カスタムフィールドを更新しました'),
		3 => __('カスタムフィールドを削除しました'),
		4 => __('商品の更新'),
		5 => isset($_GET['revision']) ? sprintf( __(' %s 前に商品詳細ページを保存しました'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('商品が公開されました <a href="%s">商品詳細ページを見る</a>'), esc_url( get_permalink($post_ID) ) ),
		7 => __('商品詳細ページを保存'),
		8 => sprintf( __('商品詳細ページを送信 <a target="_blank" href="%s">プレビュー</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		9 => sprintf( __('商品を予約投稿しました: <strong>%1$s</strong>. <a target="_blank" href="%2$s">プレビュー</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
		10 => sprintf( __('商品の下書きを更新しました <a target="_blank" href="%s">プレビュー</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	);
	return $messages;
}

add_action( 'restrict_manage_posts', 'wc2_item_post_tag_restrict' );
function wc2_item_post_tag_restrict() {
	global $post_type, $pagenow;

	//if( ITEM_POST_TYPE == $post_type and $pagenow == 'edit.php' ) {
	if( is_post_type_archive( ITEM_POST_TYPE ) ) {
		$search_genre = apply_filters( 'wc2_filter_item_search_genre', 'item' );
		$selected_genre = array_key_exists( $search_genre, $_GET ) ? $_GET[$search_genre] : '';
		$dropdown_options = array(
			'show_option_all' => __( 'All categories' ),
			'hide_empty' => 0,
			'hierarchical' => 1,
			'show_count' => 0,
			'orderby' => 'name',
			'name' => $search_genre,
			'taxonomy' => $search_genre,
			'selected' => $selected_genre
		);
		wp_dropdown_categories( $dropdown_options );

		$search_condition = array(
			'item_code' => __('Item code', 'wc2'),
			'item_name' => __('Item name', 'wc2'),
			'stock0' => __('在庫数０の商品', 'wc2'),
			'stock_status' => __('在庫状態', 'wc2'),
			//'display_status' => '公開状態',
		);
		$search_condition = apply_filters( 'wc2_filter_item_search_condition', $search_condition );
		$selected_condition = array_key_exists( 'search_condition', $_GET ) ? $_GET['search_condition'] : '';
?>
		<select name="search_condition" id="search_condition">
			<option value=""></option>
<?php foreach( (array)$search_condition as $key => $condition ) : ?>
			<option value="<?php esc_attr_e( $key ); ?>"<?php if( $key == $selected_condition ) echo ' selected="selected"'; ?>><?php esc_attr_e( $condition ); ?></option>
<?php endforeach; ?>
		</select>
		<span name="search_field" id="search_field"></span>

<?php
	}
}

add_filter( 'parse_query', 'wc2_todo_convert_restrict' );
function wc2_todo_convert_restrict( $query ) {
	global $post_type, $pagenow;
	if( ITEM_POST_TYPE == $post_type and $pagenow == 'edit.php' ) {
		$search_genre = apply_filters( 'wc2_filter_item_search_genre', 'item' );
		if( array_key_exists( $search_genre, $query->query_vars ) ) {
			$var = $query->query_vars[$search_genre];
			if( isset($var) && $var > 0 )  {
				$term = get_term_by( 'id', $var, $search_genre );
				$query->query_vars[$search_genre] = $term->slug;
			}
		}
	}
	return $query;
}

add_filter( 'posts_fields', 'wc2_item_posts_fields', 10, 2 );
function wc2_item_posts_fields( $fields, $query ) {
	global $post_type, $pagenow, $wpdb;
	if( ITEM_POST_TYPE == $post_type and $pagenow == 'edit.php' ) {
		$wc2_item = WC2_DB_Item::get_instance();
		$fields .= ", {$wc2_item->item_table}.item_code, {$wc2_item->item_table}.item_name ";
	}
	return $fields;
}

add_filter( 'posts_join', 'wc2_item_search_join' );
function wc2_item_search_join( $join ) {
	global $post_type, $pagenow, $wpdb;
	if( ITEM_POST_TYPE == $post_type and $pagenow == 'edit.php' ) {
		$wc2_item = WC2_DB_Item::get_instance();
		$join .= " INNER JOIN {$wc2_item->item_table} ON ( {$wpdb->posts}.ID = {$wc2_item->item_table}.item_post_id ) ";
		$join .= " LEFT JOIN {$wc2_item->item_sku_table} ON ( {$wc2_item->item_table}.ID = {$wc2_item->item_sku_table}.sku_item_id ) ";
	}
	return $join;
}

add_filter( 'posts_where', 'wc2_item_search_where' );
function wc2_item_search_where( $where ) {
	global $post_type, $pagenow, $wpdb;
	if( ITEM_POST_TYPE == $post_type and $pagenow == 'edit.php' ) {
		$wc2_item = WC2_DB_Item::get_instance();
		if( array_key_exists( 'search_condition', $_REQUEST ) ) {
			switch( $_REQUEST['search_condition'] ) {
			case 'item_code':
				if( array_key_exists( 'search_word', $_REQUEST ) ) {
					$search_word = trim($_REQUEST['search_word']);
					if( 0 < strlen($search_word) ) {
						$where .= " AND {$wc2_item->item_table}.item_code LIKE '%{$search_word}%' ";
					}
				}
				break;
			case 'item_name':
				if( array_key_exists( 'search_word', $_REQUEST ) ) {
					$search_word = trim($_REQUEST['search_word']);
					if( 0 < strlen($search_word) ) {
						$where .= " AND {$wc2_item->item_table}.item_name LIKE '%{$search_word}%' ";
					}
				}
				break;
			case 'stock0':
				$where .= " AND {$wc2_item->item_sku_table}.sku_stock = 0 ";
				break;
			case 'stock_status':
				if( array_key_exists( 'search_word', $_REQUEST ) ) {
					$search_word = trim($_REQUEST['search_word']);
					$where .= " AND {$wc2_item->item_sku_table}.sku_status = {$search_word} ";
				}
				break;
			case 'display_status':
				if( array_key_exists( 'search_word', $_REQUEST ) ) {
					$search_word = trim($_REQUEST['search_word']);
					$where .= " AND {$wpdb->posts}.post_status = '{$search_word}' ";
				}
				break;
			}
		}
	}
	return $where;
}

add_filter( 'posts_groupby', 'wc2_search_groupby' );
function wc2_search_groupby( $groupby ) {
	global $post_type, $pagenow, $wpdb;
	if( ITEM_POST_TYPE == $post_type and $pagenow == 'edit.php' ) {
		$mygroupby = "{$wpdb->posts}.ID";

		if( preg_match( "/$mygroupby/", $groupby ) ) {
			return $groupby;
		}

		if( !strlen(trim($groupby)) ) {
			return $mygroupby;
		}
	}
	return $groupby;
}

add_action( 'init', 'wc2_item_init' );
function wc2_item_init() {
	global $posts, $post_type, $pagenow;

	if( is_admin() and array_key_exists( 'post_type', $_GET ) and ITEM_POST_TYPE == $_GET['post_type'] and $pagenow == 'edit.php' ) {
		wp_enqueue_script( 'jquery-ui-dialog' );
	}
}

add_action('wp', 'wc2_item_the_post');
function wc2_item_the_post(){
	global $posts, $pagenow;

	if( is_admin() and array_key_exists( 'post_type', $_GET ) and ITEM_POST_TYPE == $_GET['post_type'] and $pagenow == 'edit.php' ) {

		if( array_key_exists('item_mode', $_GET) && 'dlitemlist' == $_GET['item_mode']){
			check_admin_referer( 'wc2_dlitemlist', 'wc2_nonce' );

			$file_path = apply_filters('wc2_filter_import_item_file_path', WC2_PLUGIN_DIR.'/admin/includes/class-item-import.php' );
			require_once( $file_path );

			wc2_download_item_list();
		}
	}
}

add_filter( 'in_admin_footer', 'wc2_item_operations' );
function wc2_item_operations() {
	global $post_type, $pagenow;
	if( ITEM_POST_TYPE == $post_type and $pagenow == 'edit.php' and !array_key_exists( 'page', $_GET ) ) {
?>
<div id="itemnav">
<?php 
/*
	<input type="button" id="item_import" class="button" value="商品一括登録" />
*/
?>
	<input type="button" id="item_export" class="button" value="商品データ出力" />
</div>
<div id="import_dialog" style="display:none;">
	<p id="dialogExp"></p>
	<form action="<?php echo WC2_ADMIN_URL; ?>" method="post" enctype="multipart/form-data" name="upform" id="upform">
		<input type="file" id="upload" name="import" size="25" />
		<input type="submit" name="importitem" id="importitem" value="<?php _e('登録開始', 'wc2'); ?>" />
		<input type="hidden" name="page" value="itemedit" />
		<input type="hidden" name="action" value="save" />
	</form>
	<p><?php _e('アップロード完了後に表示が更新されます。', 'wc2'); ?></p>
	<p><?php _e('登録状況はログ（welcart2/logs/import_item.log）をご確認ください。<br />ログはアップロードごとに上書き更新されます。', 'wc2'); ?></p>
</div>
<div id="export_dialog" style="display:none;">
	<p id="export-dialogExp"></p>
	<fieldset class="dl-check-option">
		<label for="chk_header"><input class="check_item" id="chk_header" value="chk_header" checked="" type="checkbox"><?php _e('１行目に項目名を追加する', 'wc2'); ?></label>
		<input id="dl_item" name="dl_item" value="<?php _e('Download', 'wc2'); ?>" type="button">
	</fieldset></div>
<script type="text/javascript">
jQuery( function($) {
	$("#import_dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 350,
		modal: true,
		buttons: {
			Cancel: function() {
				$(this).dialog( "close" );
			}
		},
		close: function() {
			$("#uploadcsv").val( "" );
		}
	});
	$("#item_import").click(function() {
		$("#import_dialog").dialog( "option", "title", "<?php _e('Collective registration item', 'wc2'); ?>" );
		$("#import_dialog").dialog( "option", "width", 500 );
		$("#dialogExp").html( "<?php _e( 'Upload prescribed CSV file and perform the collective registration of the article.<br />Please choose a file, and push the registration start.', 'wc2' ); ?>" );
		$("#import_dialog").dialog( "open" );
	});

	$("#export_dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 350,
		modal: true,
		buttons: {
			Cancel: function() {
				$(this).dialog( "close" );
			}
		},
		close: function() {
			$("#uploadcsv").val( "" );
		}
	});
	$("#item_export").click(function() {
		$("#export_dialog").dialog( "option", "title", "<?php _e('商品データ出力', 'wc2'); ?>" );
		$("#export_dialog").dialog( "option", "width", 500 );
		$("#export-dialogExp").html( "<?php _e( '商品データをCSV形式でダウロードできます。', 'wc2' ); ?>" );
		$("#export_dialog").dialog( "open" );
	});
	$("#dl_item").click(function(){
		var args = window.location.search;
		if($("#chk_header").attr('checked')) {
			args += '&chk_header=on';
		}

		location.href = "<?php echo admin_url('edit.php'); ?>"+args+"&item_mode=dlitemlist&noheader=true&ftype=csv&wc2_nonce=<?php echo wp_create_nonce( 'wc2_dlitemlist' ); ?>";
	});
});
</script>
<div class="clear"></div>
<?php
	}
}
