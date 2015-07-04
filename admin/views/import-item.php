<?php
if( isset($_GET['import']) ) :
	$file_path = apply_filters('wc2_filter_import_item_file_path', WC2_PLUGIN_DIR.'/admin/includes/class-item-import.php' );
	require_once( $file_path );

	wc2_import_item();

else:
	$bytes = wp_max_upload_size();
	$size = size_format( $bytes );
	$action = add_query_arg( 'import', 1 );
?>
<div class="wrap">
<h2><?php _e( '商品一括登録', 'wc2' ); ?></h2>
</div>
<div class="upload-plugin">
<p class="install-help"><?php _e( 'CSV 形式のファイルをアップロードして登録します。', 'wc2' ); ?></p>
<form method="post" enctype="multipart/form-data" class="wp-upload-form" action="<?php echo $action; ?>" >
	<?php do_action( 'wc2_action_import_item_field' ); ?>
	<label class="screen-reader-text" for="upload"><?php _e( '商品データファイル', 'wc2' ); ?></label>
	<input type="file" id="upload" name="import" size="25" />
	<input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>" />
	<input type="submit" name="import_item" class="button action" value="<?php _e( '登録', 'wc2' ); ?>" />
</form>
</div>
<?php
endif;
?>

