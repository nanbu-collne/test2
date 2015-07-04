<?php
/*************************
		会員一覧
**************************/
?>
<div class="wrap">
	<div class="wc2_admin">
		<div class="member_list">
			<h2><?php _e($this->title.'一覧', 'wc2'); ?>
				<!--新規追加-->
				<!--リファラ-->
				<?php //echo wp_get_referer(); ?>
				<!--リファラ-->
			<?php 
				$member_new_url = add_query_arg( array( 'action' => 'new' ) );
				$member_new_url = remove_query_arg( 'target', $member_new_url );
			?>
				<a href="<?php echo esc_url($member_new_url); ?>" class="add-new-h2"><?php echo esc_html_x('新規追加', 'wc2'); ?></a>
<?php /*				<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'new' ) ) ); ?>" class="add-new-h2"><?php echo esc_html_x('新規追加', 'wc2'); ?></a> */?>
			</h2>
			<div id="aniboxStatus" class="<?php echo esc_attr($status); ?>">
				<div id="anibox" class="clearfix">
					<div class="mes" id="info_message"><?php echo esc_html($message); ?></div>
				</div>
			</div>
			<?php
			if ( array_key_exists( 'search_in', $_REQUEST ) ) {
				$search_column_val = ( 'none' == $search_column_key || '' == $search_column_key ) ? '' : $search_columns[$search_column_key];
				echo sprintf( '<h3>' . __( '[%s] に [%s] を含む検索結果', 'wc2' ) . '</h3>', esc_html( $search_column_val ), esc_html( $search_word ) );
			}
			?>
			<?php //検索 ?>
			<form id="posts-filter" action="" method="get" name="search_form">
				<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
				<div id="search-table" class="tablenav top">
					<div class="alignleft actions bulkactions">
						<select name="search_column" id="search-column">
						    <option value="none"><?php _e('Search Filters', 'wc2'); ?></option>
						<?php
							foreach( $search_columns as $key => $value ) :
								$selected = ( $key == $search_column_key ) ? ' selected="selected"' : ''; ?>
							<option value="<?php esc_attr_e($key); ?>"<?php echo $selected; ?>><?php esc_html_e($value); ?></option>
						<?php endforeach; ?>
				    	</select>
						<span id="search-label"<?php if( $search_word_key != '' ) echo ' style="display:none"'; ?>><?php _e('Keyword', 'wc2'); ?></span>
						<span id="search-field"><input type="text" name="search_word[keyword]" class="search-word" id="search-word-keyword" value="<?php esc_attr_e($search_word); ?>" maxlength="50"<?php if( $search_word_key != '' ) echo ' style="display:none"'; ?> />
							<select name="search_word[mem_rank]" id="search-word-mem_rank"<?php if( $search_column_key != 'mem_rank' ) echo ' style="display:none"'; ?>>
							<?php
								foreach( $rank_type as $status_key => $status_name ) :
									$selected = ( $status_key == $search_word_key ) ? ' selected="selected"' : ''; ?>
								<option value="<?php esc_attr_e($status_key); ?>"<?php echo $selected; ?>><?php esc_html_e($status_name); ?></option>
							<?php endforeach; ?>
							</select>
						</span>
						<input type="submit" name="search_in" id="search-in" class="button" value="<?php _e('Search', 'wc2'); ?>" />
						<input type="submit" name="search_out" id="search-out" class="button" value="<?php _e('Release', 'wc2'); ?>" />
					</div>
				</div>
				<?php wp_nonce_field( 'wc2_member_list', 'wc2_nonce', false ); ?>
			</form>

			<div id="navi-box">
				<div class="alignright actions">
					<input type="button" id="dl_memberlist" class="button" value="<?php _e('Download Member List', 'wc2'); ?>" />
				</div>
			</div><!--navi-box-link-->

			<form id="movies-filter" method="post">
				<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />

				<?php $Member_List_Table->display(); ?>

				<?php wp_nonce_field( 'wc2_member_list', 'wc2_nonce', false ); ?>
			</form>
<!--dialog-->
			<div id="dlMemberListDialog" title="<?php _e('Download Member List', 'wc2'); ?>">
				<p><?php _e('出力したい項目を選択して、ダウンロードを押してください。', 'wc2'); ?></p>
				<input type="button" class="button" id="dl_mem" value="<?php _e('Download', 'wc2'); ?>" />
				<fieldset class="dl-check-option">
					<legend><?php _e($this->title.'情報', 'wc2'); ?></legend>
					<label for="chk_mem[ID]"><input type="checkbox" class="check_member" id="chk_mem[ID]" value="ID" checked="checked" disabled="disabled" /><?php _e('Membership ID', 'wc2'); ?></label>
					<label for="chk_mem[account]"><input type="checkbox" class="check_member" id="chk_mem[account]" value="account" checked="checked" disabled="disabled" /><?php _e('Login account', 'wc2'); ?></label>
			<?php
				$wc2_options = wc2_get_option();

				$hd_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'head' );
				if( !empty($hd_keys) ){
					foreach($hd_keys as $csmb_key){
						$checked = WC2_Funcs::get_checked( $chk_mem, $csmb_key );
						$name = $wc2_options[$csmb_key]['name'];
						echo '<label for="chk_mem['.$csmb_key.']"><input type="checkbox" class="check_member" id="chk_mem['.esc_attr($csmb_key).']" value="'.esc_attr($csmb_key).'"'.$checked.' />'.esc_html($name).'</label>'."\n";
					}
				}
			?>
				<label for="chk_mem[email]"><input type="checkbox" class="check_member" id="chk_mem[email]" value="email"<?php wc2_checked_e($chk_mem, 'email'); ?> /><?php _e('E-mail', 'wc2'); ?></label>&nbsp;
<?php
				$bn_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'beforename' );
				if( !empty($bn_keys) ){
					foreach($bn_keys as $csmb_key){
						$checked = WC2_Funcs::get_checked( $chk_mem, $csmb_key );
						$name = $wc2_options[$csmb_key]['name'];
						echo '<label for="chk_mem['.$csmb_key.']"><input type="checkbox" class="check_member" id="chk_mem['.esc_attr($csmb_key).']" value="'.esc_attr($csmb_key).'"'.$checked.' />'.esc_html($name).'</label>&nbsp;'."\n";
					}
				}
?>
					<label for="chk_mem[name]"><input type="checkbox" class="check_member" id="chk_mem[name]" value="name" checked="checked" disabled="disabled" /><?php _e('Name', 'wc2'); ?></label>&nbsp;
<?php
				$applyform = WC2_Funcs::get_apply_addressform($wc2_options['system']['addressform']);
				switch($applyform) {
					case 'JP':
?>
					<label for="chk_mem[kana]"><input type="checkbox" class="check_member" id="chk_mem[kana]" value="kana"<?php wc2_checked_e($chk_mem, 'kana'); ?> /><?php _e('Kana','wc2'); ?></label>&nbsp;
<?php
						break;
				}

				$an_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'aftername' );
				if( !empty($an_keys) ){
					foreach($an_keys as $csmb_key){
						$checked = WC2_Funcs::get_checked( $chk_mem, $csmb_key );
						$name = $wc2_options[$csmb_key]['name'];
						echo '<label for="chk_mem['.$csmb_key.']"><input type="checkbox" class="check_member" id="chk_mem['.esc_attr($csmb_key).']" value="'.esc_attr($csmb_key).'"'.$checked.' />'.esc_html($name).'</label>&nbsp;'."\n";
					}
				}

				switch($applyform) {
					case 'JP':
?>
					<label for="chk_mem[country]"><input type="checkbox" class="check_member" id="chk_mem[country]" value="country"<?php wc2_checked_e($chk_mem, 'country'); ?> /><?php _e('Country', 'wc2'); ?></label>&nbsp;
					<label for="chk_mem[zipcode]"><input type="checkbox" class="check_member" id="chk_mem[zipcode]" value="zipcode"<?php wc2_checked_e($chk_mem, 'zipcode'); ?> /><?php _e('Postal Code', 'wc2'); ?></label>&nbsp;
					<label for="chk_mem[pref]"><input type="checkbox" class="check_member" id="chk_mem[pref]" value="pref" checked="checked" disabled="disabled" /><?php _e('Prefecture', 'wc2'); ?></label>&nbsp;
					<label for="chk_mem[address1]"><input type="checkbox" class="check_member" id="chk_mem[address1]" value="address1" checked="checked" disabled="disabled" /><?php _e('City', 'wc2'); ?></label>&nbsp;
					<label for="chk_mem[address2]"><input type="checkbox" class="check_member" id="chk_mem[address2]" value="address2" checked="checked" disabled="disabled" /><?php _e('Building name, floor, room number', 'wc2'); ?></label>&nbsp;
					<label for="chk_mem[tel]"><input type="checkbox" class="check_member" id="chk_mem[tel]" value="tel"<?php wc2_checked_e($chk_mem, 'tel'); ?> /><?php _e('Phone number', 'wc2'); ?></label>&nbsp;
					<label for="chk_mem[fax]"><input type="checkbox" class="check_member" id="chk_mem[fax]" value="fax"<?php wc2_checked_e($chk_mem, 'fax'); ?> /><?php _e('FAX number', 'wc2'); ?></label>&nbsp;
<?php
					break;
				case 'US':
				default:
?>
					<label for="chk_mem[address2]"><input type="checkbox" class="check_member" id="chk_mem[address2]" value="address2" checked="checked" disabled="disabled" /><?php _e('Building name, floor, room number', 'wc2'); ?></label>&nbsp;
					<label for="chk_mem[address1]"><input type="checkbox" class="check_member" id="chk_mem[address1]" value="address1" checked="checked" disabled="disabled" /><?php _e('City', 'wc2'); ?></label>&nbsp;
					<label for="chk_mem[pref]"><input type="checkbox" class="check_member" id="chk_mem[pref]" value="pref" checked="checked" disabled="disabled" /><?php _e('Prefecture', 'wc2'); ?></label>&nbsp;
					<label for="chk_mem[zipcode]"><input type="checkbox" class="check_member" id="chk_mem[zipcode]" value="zipcode"<?php wc2_checked_e($chk_mem, 'zipcode'); ?> /><?php _e('Postal Code', 'wc2'); ?></label>&nbsp;
					<label for="chk_mem[country]"><input type="checkbox" class="check_member" id="chk_mem[country]" value="country"<?php wc2_checked_e($chk_mem, 'country'); ?> /><?php _e('Country', 'wc2'); ?></label>&nbsp;
					<label for="chk_mem[tel]"><input type="checkbox" class="check_member" id="chk_mem[tel]" value="tel"<?php wc2_checked_e($chk_mem, 'tel'); ?> /><?php _e('Phone number', 'wc2'); ?></label>&nbsp;
					<label for="chk_mem[fax]"><input type="checkbox" class="check_member" id="chk_mem[fax]" value="fax"<?php wc2_checked_e($chk_mem, 'fax'); ?> /><?php _e('FAX number', 'wc2'); ?></label>&nbsp;
<?php
					break;
				}
				$btm_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'bottom' );

				if( !empty($btm_keys) ){
					foreach($btm_keys as $csmb_key){
						$checked = WC2_Funcs::get_checked( $chk_mem, $csmb_key );
						$name = $wc2_options[$csmb_key]['name'];
						echo '<label for="chk_mem['.$csmb_key.']"><input type="checkbox" class="check_member" id="chk_mem['.esc_attr($csmb_key).']" value="'.esc_attr($csmb_key).'"'.$checked.' />'.esc_html($name).'</label>&nbsp;'."\n";
					}
				}
?>
					<label for="chk_mem[rank]"><input type="checkbox" class="check_member" id="chk_mem[rank]" value="rank"<?php wc2_checked_e($chk_mem, 'rank'); ?> /><?php _e('Rank', 'wc2'); ?></label>&nbsp;
					<label for="chk_mem[point]"><input type="checkbox" class="check_member" id="chk_mem[point]" value="point"<?php wc2_checked_e($chk_mem, 'point'); ?> /><?php _e('Membership points','wc2'); ?></label>&nbsp;
					<label for="chk_mem[registered]"><input type="checkbox" class="check_member" id="chk_mem[registered]" value="registered"<?php wc2_checked_e($chk_mem, 'registered'); ?> /><?php _e('Started date','wc2'); ?></label>&nbsp;
<?php
				$oth_keys = WC2_Funcs::get_custom_field_keys( WC2_CSMB, 'other' );
				if( !empty($oth_keys) ){
					foreach($oth_keys as $key => $csmb_key){
						$checked = WC2_Funcs::get_checked( $chk_mem, $csmb_key );
						$name = $wc2_options[$csmb_key]['name'];
						echo '<label for="chk_mem['.$csmb_key.']"><input type="checkbox" class="check_member" id="chk_mem['.esc_attr($csmb_key).']" value="'.esc_attr($csmb_key).'"'.$checked.' />'.esc_html($name).'</label>'."\n";
					}
				}
?>
					<?php //do_action( 'wc2_action_chk_mem', $chk_mem ); ?>
				</fieldset>

			</div><!--dialog-->

		</div><!--member_list-->
	</div><!--wc2_admin-->
</div><!--wrap-->
