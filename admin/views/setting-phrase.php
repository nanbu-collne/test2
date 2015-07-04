<?php
/*************************
		定型文設定
**************************/
?>
<div class="wrap">
	<div class="wc2_admin">
		<div class="mail-setting">
			<h2><?php _e('メール設定','wc2'); ?></h2>
			<div id="aniboxStatus" class="<?php echo $status; ?>">
				<div id="anibox" class="clearfix">
					<img id="info_image" src="<?php echo WC2_PLUGIN_URL; ?>/common/assets/images/list_message_<?php echo $status; ?>.gif" />
					<div class="mes" id="info_message"><?php echo $message; ?></div>
				</div>
			</div>

			<div id="poststuff" class="metabox-holder">
				<div class="wc2tabs" id="phrase-tabs">
					<ul>
						<li><a href="#sendmail-setting"><?php _e('メール送信設定','wc2'); ?></a></li>
						<li><a href="#phrase-setting"><?php _e('メール定型文設定','wc2'); ?></a></li>
						<?php do_action( 'wc2_action_admin_phrase_head' ); ?>
					</ul>

					<div id="sendmail-setting">
						<div class="postbox">
						<h3 class="hndle"><span><?php _e('メール送信設定','wc2'); ?></span></h3>
						<form action="" method="post" name="sendmail-setting-form" id="sendmail-setting-form">
						<div class="inside">
						<table class="form-table">
							<tr>
								<th><?php _e('新規会員登録管理者宛メール','wc2'); ?></th>
								<td class="horizontal">
									<label title="0"><input name="newmem_admin_mail" type="radio" id="newmem_admin_mail_0" value="0"<?php if(0 == $newmem_admin_mail) echo ' checked="checked"'; ?> /><span><?php _e('送信しない','wc2'); ?></span></label>
									<label title="1"><input name="newmem_admin_mail" type="radio" id="newmem_admin_mail_1" value="1"<?php if(1 == $newmem_admin_mail) echo ' checked="checked"'; ?> /><span><?php _e('送信する','wc2'); ?></span></label>
								</td>
							</tr>
							<tr>
								<th><?php _e('会員削除管理者宛メール','wc2'); ?></th>
								<td class="horizontal">
									<label title="0"><input name="delmem_admin_mail" type="radio" id="delmem_admin_mail_0" value="0"<?php if(0 == $delmem_admin_mail) echo ' checked="checked"'; ?> /><span><?php _e('送信しない','wc2'); ?></span></label>
									<label title="1"><input name="delmem_admin_mail" type="radio" id="delmem_admin_mail_1" value="1"<?php if(1 == $delmem_admin_mail) echo ' checked="checked"'; ?> /><span><?php _e('送信する','wc2'); ?></span></label>
								</td>
							</tr>
							<tr>
								<th><?php _e('会員削除お客様宛メール','wc2'); ?></th>
								<td class="horizontal">
									<label title="0"><input name="delmem_customer_mail" type="radio" id="delmem_customer_mail_0" value="0"<?php if(0 == $delmem_customer_mail) echo ' checked="checked"'; ?> /><span><?php _e('送信しない','wc2'); ?></span></label>
									<label title="1"><input name="delmem_customer_mail" type="radio" id="delmem_customer_mail_1" value="1"<?php if(1 == $delmem_customer_mail) echo ' checked="checked"'; ?> /><span><?php _e('送信する','wc2'); ?></span></label>
								</td>
							</tr>
							<tr>
								<th><?php _e('会員編集お客様宛メール','wc2'); ?></th>
								<td class="horizontal">
									<label title="0"><input name="editmem_customer_mail" type="radio" id="editmem_customer_mail_0" value="0"<?php if(0 == $editmem_customer_mail) echo ' checked="checked"'; ?> /><span><?php _e('送信しない','wc2'); ?></span></label>
									<label title="1"><input name="editmem_customer_mail" type="radio" id="editmem_customer_mail_1" value="1"<?php if(1 == $editmem_customer_mail) echo ' checked="checked"'; ?> /><span><?php _e('送信する','wc2'); ?></span></label>
								</td>
							</tr>
						</table>
						<input type="submit" name="wc2_option_update" class="button button-primary" id="update-sendmail" value="<?php _e('Update Settings','wc2'); ?>" />
						</div>
						<?php wp_nonce_field( 'wc2_setting_phrase', 'wc2_nonce', false ); ?>
						</form>
						</div><!--postbox-->
					</div>

					<div id="phrase-setting">
						<div class="postbox">
						<h3 class="hndle"><span><?php _e('メール定型文設定', 'wc2'); ?></span></h3>
						<form action="" method="post" name="phrase-setting-form" id="phrase-setting-form">
						<div class="inside">
						<select name="phrase_select" id="phrase_select">
							<option value=""><?php _e('-- Select --', 'wc2'); ?></option>
							<option value="thankyou"><?php _e('サンキューメール（自動送信）','wc2'); ?></option>
							<option value="order"><?php _e('受注メール（自動送信）','wc2'); ?></option>
							<option value="inquiry"><?php _e('問い合わせ受付メール（自動送信）','wc2'); ?></option>
							<option value="membercomp"><?php _e('入会完了のご連絡メール（自動送信）','wc2'); ?></option>
							<option value="completionmail"><?php _e('発送完了メール（管理画面より送信）','wc2'); ?></option>
							<option value="ordermail"><?php _e('ご注文確認メール（管理画面より送信）','wc2'); ?></option>
							<option value="changemail"><?php _e('ご注文内容変更の確認メール（管理画面より送信）','wc2'); ?></option>
							<option value="receiptmail"><?php _e('ご入金確認のご連絡メール（管理画面より送信）','wc2'); ?></option>
							<option value="estimatemail"><?php _e('お見積メール（管理画面より送信）','wc2'); ?></option>
							<option value="cancelmail"><?php _e('ご注文キャンセルの確認メール（管理画面より送信）','wc2'); ?></option>
							<option value="othermail"><?php _e('その他のメール（管理画面より送信）','wc2'); ?></option>
							<?php do_action( 'wc2_action_admin_phrase_option' ); ?>
						</select>
						<table class="form-table phrase-area">
							<tr>
								<th><?php _e('件名', 'wc2'); ?></th>
								<td><input name="title" id="title" type="text" class="large-text mail-title" value="" /></td>
							</tr>
							<tr>
								<th><?php _e('Header', 'wc2'); ?></th>
								<td><textarea name="header" id="header" class="large-text mail-header"></textarea></td>
							</tr>
							<tr>
								<th><?php _e('Footer', 'wc2'); ?></th>
								<td><textarea name="footer" id="footer" class="large-text mail-footer"></textarea></td>
							</tr>
						</table>
						<input type="button" class="button button-primary" id="update-phrase" value="<?php _e('Update Settings','wc2'); ?>" />
						<div id="loading"></div>
						</div>
						</form>
						</div><!--postbox-->
					</div>

					<?php do_action( 'wc2_action_admin_phrase_form' ); ?>

				</div><!--#phrase-tabs-->
			</div><!--#poststuff-->
		</div><!--.phrase-setting-->
	</div><!--.wc2_admin-->
</div><!--.wrap-->
