<?php
/*****************************
*
* 新規会員登録・会員情報編集
*
******************************/

?>
<div class="wrap">
	<h2><?php echo esc_html( $title ); ?>
	<?php
	$member_list_url = add_query_arg( array( 'action' => 'list' ) );
	$member_list_url = remove_query_arg( 'target', $member_list_url );
	?>
	<a href="<?php echo esc_url( $member_list_url ); ?>" class="add-new-h2"><?php echo esc_html_x($this->title.'一覧', 'wc2'); ?></a>
	<?php if( 'new' != $member_action ) : ?>
	<?php 
	$member_new_url = add_query_arg( array( 'action' => 'new' ) );
	$member_new_url = remove_query_arg( 'target', $member_new_url );
	?>
	<a href="<?php echo esc_url( $member_new_url ); ?>" class="add-new-h2"><?php echo esc_html_x('新規追加', 'wc2'); ?></a>
	<?php endif; ?>
	</h2>
	<div id="poststuff" class="wc2_admin">
		<div class="member_post">
		<?php if( 'new' == $member_action ) : ?>
			<form action="<?php echo add_query_arg( 'action', $oa ); ?>" method="post" name="newpost" class="newform"/>
		<?php else : ?>
			<form action="<?php echo add_query_arg( array( 'action' => $oa, 'target' => $data['ID'] ) ); ?>" method="post" name="editpost" class="editform" />
		<?php endif; ?>
				<div id="aniboxStatus" class="<?php echo $status; ?>">
					<div id="anibox" class="clearfix">
						<div class="mes" id="info_message"><?php echo $message; ?></div>
					</div>
				</div>
				<div class="error_message">
					<?php
						if( array() != $this->error_message ){
							$row = '<ul>';
							foreach($this->error_message as $err_key => $err_val){
								$row .= '<li>' . esc_html($err_val) . '</li>';
							}
							$row .= '</ul>';
							echo $row;
						}
					?>
				</div>
				<div class="postbox info_head">
					<h3><?php _e($this->title.'情報', 'wc2'); ?></h3>
					<div class="inside clearfix">
						<div class="wc2_member_info wc2_member_info_primary">
							<table>
								<tr>
									<th class="label"><?php _e('Membership ID', 'wc2'); ?></th>
									<td class="col1"><div class="rod long"><?php echo esc_html($data['ID']); ?></div></td>
								</tr>
								<tr>
									<th class="label"><span class="required"><?php _e('*', 'wc2'); ?></span><?php _e('Login account', 'wc2'); ?></th>
									<td class="col1"><input name="member[account]" type="text" class="text long" value="<?php echo esc_attr($data['account']); ?>" /></td>
								</tr>

								<?php if( 'new' == $member_action ) : ?>
								<tr>
									<th class="label"><span class="required"><?php _e('*', 'wc2'); ?></span><?php _e('Login password', 'wc2'); ?></th>
									<td><input name="member[passwd]" type="text" class="text long" value="<?php echo esc_attr($data['passwd']); ?>" /></td>
								</tr>
								<?php endif; ?>
								<tr>
									<th class="label"><?php _e('Rank', 'wc2'); ?></th>
									<td class="col1">
										<select name="member[rank]">
								<?php
										foreach( $rank_type as $rk => $rv ){
											$selected = ($rk == $data['rank']) ? ' selected="selected"' : '';
								?>
											<option value="<?php echo esc_attr($rk); ?>"<?php echo $selected; ?>><?php echo esc_html($rv); ?></option>
								<?php	} ?>
									</td>
								</tr>
								<tr>
									<th class="label"><?php _e('Holdings points', 'wc2'); ?></th>
									<td class="col1"><input name="member[point]" type="number" min="0" class="text right short num" value="<?php echo esc_attr($data['point']); ?>" /></td>
								</tr>
								<tr>
									<th class="label"><?php _e('Started date', 'wc2'); ?></th>
									<td class="col1"><div class="rod long"><?php echo esc_html($data['registered']); ?></div></td>
								</tr>
								
								<?php do_action('wc2_action_member_info_primary', $data, $member_action); ?>
							</table>
						</div>
						
						<div class="wc2_member_info wc2_member_info_secondary">
							<table>
								<?php echo wc2_get_addressform( $data, 'member' ); ?>

								<?php do_action('wc2_action_member_info_secondary', $data, $member_action); ?>
							</table>
						</div>
						
						<div class="wc2_member_info wc2_member_info_tertiary">
							<table>
								<?php echo wc2_custom_field_input($data, 'member', 'other'); ?>

								<?php do_action('wc2_action_member_info_tertiary', $data, $member_action); ?>
							</table>
						</div>
					</div>

					<div id="major-publishing-actions" class="ordernavi">
					<?php if( 'new' == $member_action ) : ?>
						<input name="addButton" id="mem_addButton" class="upButton button button-primary" type="submit" value="<?php _e('To register', 'wc2'); ?>" />
					<?php else : ?>
						<input name="upButton" id="mem_upButton" class="mem_upButton button button-primary" type="submit" value="<?php _e('To update', 'wc2'); ?>" />
					<?php endif; ?>
					</div>

				<?php wp_nonce_field( 'wc2_member_post', 'wc2_nonce', false ); ?>
				</div>
			</form>

			<?php if( 'edit' == $member_action ) : ?>
			<div class="wc2tabs" id="mem-ui-tab">
				<ul>
					<li><a href="#mem_tab1"><span><?php _e('Purchase history', 'wc2'); ?></span></a></li>
					<?php do_action('wc2_action_member_post_tab_title'); ?>
				</ul>
				<div id="mem_tab1">
					<?php wc2_member_history_rows_e(); ?>
				</div>

				<?php do_action('wc2_action_member_post_tab_content', $data); ?>

			</div>
			<?php endif;?>
		</div><!--memeber_post-->
	</div><!--wc2_admin-->
</div><!--wrap-->


