<?php
/*--------------------------------
			Import
---------------------------------*/
// Load Importer API
require_once ABSPATH.'wp-admin/includes/admin.php';

if( !class_exists( 'WP_Importer' ) ) {
	$class_wp_importer = ABSPATH.'wp-admin/includes/class-wp-importer.php';
	if( file_exists( $class_wp_importer ) )
		require_once $class_wp_importer;
}

if( class_exists( 'WP_Importer' ) ) {
	class WC2_Importer_Item extends WP_Importer {

		private $encode_type = 0;
		private $values = array();
		private $log = '';
		private $log_line = '';
		private $data_rows = 0;
		private $success = 0;
		private $false = 0;

	 	// User interface wrapper start
		function header() {
			echo '
				<script type="text/javascript" src="'. includes_url() .'js/jquery/jquery.js"></script>
				<script type="text/javascript" src="'. includes_url() .'js/jquery/ui/widget.min.js"></script>
				<script type="text/javascript" src="'. includes_url() .'js/jquery/ui/progressbar.min.js"></script>
				<script type="text/javascript">
					function PG_Set_Max(max_val){
						jQuery("#progress").progressbar({
							value: 0,
							max: max_val,
							// 値の変更をラベルにも反映
							change: function() {
								var pgvalue = jQuery("#progress").progressbar("value");
								var per = Math.floor(100*(pgvalue/max_val));
								jQuery("#loading").text( per + "％ " + pgvalue + "/" + max_val + "件を処理完了" );
							},
							complete: function() {
							}
						});
					}

					function PG_Add(){
						var v = jQuery("#progress").progressbar("value");
						jQuery("#progress").progressbar("value", ++v);

					}
				</script>';
			echo '<div class="wrap">';
			screen_icon();
			echo '<h2>'.__('Import CSV', 'wc2').'</h2>';
			echo '<div id="progress">
				</div>
				<div id="loading">Now Loading ...</div>';
			ob_flush();
			flush();
		}

		// User interface wrapper end
		function footer() {

			$sendback = admin_url( "edit.php?post_type=item&page=import" );
			echo '<a href="'.$sendback.'">'.__( '商品一括登録に戻る', 'wc2' ).'</a>';
			echo '</div>';
		}

		// Import
		function import() {
			if( !current_user_can('import') ){
				echo '<p><strong>'. __('You do not have permission to do that.', 'wc2') .'</strong></p><br />';
				return false;
			}

			list($fname, $fext) = explode('.', $_FILES["import"]["name"], 2);
			if( $fext != 'csv' ) {
				echo '<p><strong>'.__('The file is not supported.', 'wc2').$fname.'.'.$fext.'</strong></p><br />';
				return false;
			}
			$file = wp_import_handle_upload();

			if ( isset( $file['error'] ) ) {
				echo '<p><strong>'.__( 'Sorry, there has been an error.', 'wc2' ).'</strong><br />';
				echo esc_html( $file['error'] ).'</p>';
				return false;
			} else if ( ! file_exists( $file['file'] ) ) {
				echo '<p><strong>'.__( 'Sorry, there has been an error.', 'wc2' ).'</strong><br />';
				printf( __( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'wc2' ), esc_html( $file['file'] ) );
				echo '</p>';
				return false;
			}

			$this->id = (int)$file['id'];
			$this->file = get_attached_file( $this->id );
			$result = $this->process_posts();

			return $result;
		}

		// Process parse csv ind insert posts
		function process_posts() {
			if( !( $fp = fopen( $this->file, "r" ) ) ) {
				echo '<p><strong>'.__( 'Failed to open file.', 'wc2' ).'</strong></p>';
				wp_import_cleanup( $this->id );
				return false;
			}

			global $wpdb;
			$wc2_item = WC2_DB_Item::get_instance();
			
			//all delete
			//$wc2_item->delete_all_item_data();
			//die();

			$err = new WP_Error();

			$sp = ",";
			$lines = array();
			$buf = '';
			while( !feof($fp) ) {
				$temp = fgets( $fp, 10240 );
				if( 0 == strlen($temp) ) continue;
				$num = substr_count( $temp, '"' );
				if( 0 == $num % 2 && '' == $buf ) {
					$lines[] = $temp;
				} elseif( 1 == $num % 2 && '' == $buf ) {
					$buf .= $temp;
				} elseif( 0 == $num % 2 && '' != $buf ) {
					$buf .= $temp;
				} elseif( 1 == $num % 2 && '' != $buf ) {
					$buf .= $temp;
					$lines[] = $buf;
					$buf = '';
				}
			}
			fclose($fp);

			//Post data - fixed
			define( 'COL_POST_ID', 0 );
			define( 'COL_POST_AUTHOR', 1 );
			define( 'COL_POST_CONTENT', 2 );
			define( 'COL_POST_TITLE', 3 );
			define( 'COL_POST_EXCERPT', 4 );
			define( 'COL_POST_STATUS', 5 );
			define( 'COL_POST_COMMENT_STATUS', 6 );
			define( 'COL_POST_PASSWORD', 7 );
			define( 'COL_POST_NAME', 8 );
			define( 'COL_POST_MODIFIED', 9 );
			define( 'COL_POST_CATEGORY', 10 );
			define( 'COL_POST_TAG', 11 );
			define( 'COL_POST_CUSTOM_FIELD', 12 );
			define( 'COL_ITEM_CODE', 13 );
			define( 'COL_ITEM_NAME', 14 );

			$item_base_column = $wc2_item->get_item_base_column();
			$item_meta_column = $wc2_item->get_item_meta_column();
			$item_sku_column = $wc2_item->get_item_sku_column();
			$item_sku_meta_column = $wc2_item->get_item_sku_meta_column();

			$system = wc2_get_option('system');
			$this->encode_type = ( isset($system['csv_encode_type']) ) ? $system['csv_encode_type'] : 0;

			$start_col = 13;
			$sku_start_col = $start_col;
			foreach( (array)$item_base_column as $key => $column ) {
				if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
					$sku_start_col++;
				}
			}
			foreach( (array)$item_meta_column as $key => $column ) {
				if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
					$sku_start_col++;
				}
			}

			$post_status = array( 'publish', 'future', 'draft', 'pending', 'private' );

			$pre_item_code = '';
			$item_id = 0;
			$sku_id = 1;
			$this->data_rows = count($lines);
			$this->success = 0;
			$this->false = 0;

			//Progressbar 処理件数SET
			echo '<script type="text/javascript">PG_Set_Max('.$this->data_rows.');</script>'."\r\n";
			ob_flush();
			flush();

			foreach( $lines as $row => $line ) {
				$datas = array();
				$datas = explode( $sp, trim($line) );
				$this->values = array();
				$buf = '';
				foreach( $datas as $data ) {
					$num = substr_count( $data, '"' );
					if( 0 == $num % 2 && '' == $buf ) {
						if( '"' == substr( $data, 0, 1 ) )
							$data = substr( $data, 1 );
						if( '"' == substr( $data, -1 ) )
							$data = substr( $data, 0, -1 );
						$data = str_replace( array('""'), '"', $data );
						$this->values[] = ( false !== $data ) ? $data : '';
					} elseif( 1 == $num % 2 && '' == $buf ) {
						$buf .= $data;
					} elseif( 0 == $num % 2 && '' != $buf ) {
						$buf .= $sp.$data;
					} elseif( 1 == $num % 2 && '' != $buf ) {
						$buf .= $sp.$data;
						if( '"' == substr( $buf, 0, 1 ) )
							$buf = substr( $buf, 1 );
						if( '"' == substr( $buf, -1 ) )
							$buf = substr( $buf, 0, -1 );
						$buf = str_replace( array('""'), '"', $buf );
						$this->values[] = ( false !== $buf ) ? $buf : '';
						$buf = '';
					}
				}

				if( 'Post ID' == $this->values[COL_POST_ID] ){
					$this->data_rows -= 1;
					$this->progress_add();
					continue;
				}

				if( $pre_item_code == $this->values[COL_ITEM_CODE] && WC2_Utils::is_blank($this->values[COL_POST_ID]) ) {
					$mode = 'add';
					$post_id = '';
				} else {
					$post_id = ( !WC2_Utils::is_blank($this->values[COL_POST_ID]) ) ? (int)$this->values[COL_POST_ID] : '';
					if( $post_id ) {
						$post_ob = get_post($post_id);
						if( !isset($post_ob->ID) || empty($post_ob) ){
							$this->error_log( $row, __( "Post-ID {$post_id} does not exist.", 'wc2' ) );
							$this->create_log();
							$this->progress_add();
							continue;
						}
						$mode = 'upd';
					} else {
						$mode = 'add';
					}
				}
				$wc2_item->set_the_post_id($post_id);

				//data_check
				foreach( $this->values as $key => $val ) {
					$value = trim($val);
					switch( $key ) {
					case COL_POST_ID:
						if( !preg_match("/^[0-9]+$/", $value) && 0 != strlen($value) ) {
							$this->error_log( $row, __( 'A value of the Post-ID is abnormal.', 'wc2' ) );
						}
						break;
					case COL_POST_AUTHOR:
					case COL_POST_COMMENT_STATUS:
					case COL_POST_PASSWORD:
					case COL_POST_NAME:
					case COL_POST_TITLE:
					case COL_POST_CONTENT:
					case COL_POST_EXCERPT:
						break;
					case COL_POST_STATUS:
						if( 0 == strlen($value) || !in_array( $value, $post_status ) ) {
							$this->error_log( $row, __( 'A value of the display status is abnormal.', 'wc2' ) );
						}
						break;
					case COL_POST_MODIFIED:
						if( 'future' == $this->values[COL_POST_STATUS] && ( 0 == strlen($value) || '0000-00-00 00:00:00' == $value ) ) {
							if( preg_match( $date_pattern, $value, $match ) ) {
								if( checkdate($match[2], $match[3], $match[1]) && 
									(0 < $match[4] && 24 > $match[4]) && 
									(0 < $match[5] && 60 > $match[5]) && 
									(0 < $match[6] && 60 > $match[6]) ) {
								} else {
									$this->error_log( $row, __( 'A value of the schedule is abnormal.', 'wc2' ) );
								}
							} else {
								$this->error_log( $row, __( 'A value of the schedule is abnormal.', 'wc2' ) );
							}
						} else if( 0 != strlen($value) && '0000-00-00 00:00:00' != $value ) {
							if( preg_match( "/^[0-9]+$/", substr($value, 0, 4) ) ) {
								if( strtotime($value) === false ) {
									$this->error_log( $row, __( 'A value of the schedule is abnormal.', 'wc2' ) );
								}
							} else {
								$datetime = explode( ' ', $value );
								$date_str = $this->dates_interconv( $datetime[0] ).' '.$datetime[1];
								if( strtotime($date_str) === false ) {
									$this->error_log( $row, __( 'A value of the schedule is abnormal.', 'wc2' ) );
								}
							}
						}
						break;
					case COL_POST_CATEGORY:
						if( 0 == strlen($value) ) {
							$this->error_log( $row, __( 'A category is non-input.', 'wc2' ) );
						}
						break;
					case COL_POST_TAG:
					case COL_POST_CUSTOM_FIELD:
						break;
					case COL_ITEM_CODE:
						if( 0 == strlen($value) ) {
							$this->error_log( $row, __( 'An item code is non-input.', 'wc2' ) );
						} else {
							$post_ids = $wc2_item->get_some_post_ids_by_item_code($value);
							if( 'upd' == $mode ) {
								if( 1 < count($post_ids) ) {
									$this->error_log( $row, __( 'This Item-Code has been duplicated.', 'wc2' ) );
									foreach( $post_ids as $res_val ) {
										$this->error_log( $row, "item_code=".$value.", post_id=".$res_val['item_post_id'] );
									}
								}elseif( 1 === count($post_ids) ){
									if($post_ids[0]['item_post_id'] != $post_id){
										$this->error_log( $row, __( 'This Item-Code has already been used.', 'wc2' ) );
										$this->error_log( $row, "item_code=".$value.", post_id=".$post_ids[0]['item_post_id'] );
									}
								}
							} else if( 'add' == $mode ) {
								if( $value != $pre_item_code ) {
									if( 0 < count($post_ids) ) {
										$this->error_log( $row, __( 'This Item-Code has already been used.', 'wc2' ) );
										foreach( $post_ids as $res_val ) {
											$this->error_log( $row, "item_code=".$value.", post_id=".$res_val['item_post_id'] );
										}
									}
								}
							}
						}
						break;
					case COL_ITEM_NAME:
						if( 0 == strlen($value) ) {
							$this->error_log( $row, __( 'An item name is non-input.', 'wc2' ) );
						}
						break;
					}
				}

				//表示する Item column をエラーチェック
				$check_num = $start_col;
				foreach( $item_base_column as $key => $column ) {
					if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
						$check_column[$key] = $check_num;
						$check_num++;
					}
				}

				foreach( $item_meta_column as $key => $column ) {
					if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
						$check_column[$key] = $check_num;
						$check_num++;
					}
				}

				foreach( $item_sku_column as $key => $column ) {
					if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
						$check_column[$key] = $check_num;
						$check_num++;
					}
				}

				foreach( $item_sku_meta_column as $key => $column ) {
					if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
						$check_column[$key] = $check_num;
						$check_num++;
					}
				}

				foreach( $check_column as $column_key => $data_num){
					switch($column_key){
						case ITEM_PRODUCT_TYPE:
							if( !preg_match("/^[0-9]+$/", $this->values[$data_num]) ) {
								$this->error_log( $row, __( 'A value of the Product type is abnormal.', 'wc2' ) );
							}
							break;

						case ITEM_CHARGES_TYPE:
							if( !preg_match("/^[0-9]+$/", $this->values[$data_num]) ) {
								$this->error_log( $row, __( 'A value of the Billing type is abnormal.', 'wc2' ) );
							}
							break;

						case ITEM_PURCHASE_LIMIT_LOWEST:
							if( !preg_match("/^[0-9]+$/", $this->values[$data_num]) ) {
								$this->error_log( $row, __( 'A value of the Purchase limit(lowest) is abnormal.', 'wc2' ) );
							}
							break;

						case ITEM_PURCHASE_LIMIT_HIGHEST:
							if( !preg_match("/^[0-9]+$/", $this->values[$data_num]) ) {
								$this->error_log( $row, __( 'A value of the Purchase limit(highest) is abnormal.', 'wc2' ) );
							}
							break;

						case ITEM_POINT_RATE:
							if( !preg_match("/^[0-9]+$/", $this->values[$data_num]) ) {
								$this->error_log( $row, __( 'A value of the point rate is abnormal.', 'wc2' ) );
							}
							break;

						case ITEM_QUANTITY_DISCOUNT_NUM1:
							if( !preg_match("/^[0-9]+$/", $this->values[$data_num]) ) {
								$this->error_log( $row, __( 'A value of the Quantity discount1(number) is abnormal.', 'wc2' ) );
							}
							break;

						case ITEM_QUANTITY_DISCOUNT_RATE1:
							if( !preg_match("/^[0-9]+$/", $this->values[$data_num]) ) {
								$this->error_log( $row, __( 'A value of the Quantity discount1(rate) is abnormal.', 'wc2' ) );
							}
							break;

						case ITEM_QUANTITY_DISCOUNT_NUM2:
							if( !preg_match("/^[0-9]+$/", $this->values[$data_num]) ) {
								$this->error_log( $row, __( 'A value of the Quantity discount2(number) is abnormal.', 'wc2' ) );
							}
							break;

						case ITEM_QUANTITY_DISCOUNT_RATE2:
							if( !preg_match("/^[0-9]+$/", $this->values[$data_num]) ) {
								$this->error_log( $row, __( 'A value of the Quantity discount2(rate) is abnormal.', 'wc2' ) );
							}
							break;

						case ITEM_QUANTITY_DISCOUNT_NUM3:
							if( !preg_match("/^[0-9]+$/", $this->values[$data_num]) ) {
								$this->error_log( $row, __( 'A value of the Quantity discount3(number) is abnormal.', 'wc2' ) );
							}
							break;

						case ITEM_QUANTITY_DISCOUNT_RATE3:
							if( !preg_match("/^[0-9]+$/", $this->values[$data_num]) ) {
								$this->error_log( $row, __( 'A value of the Quantity discount3(rate) is abnormal.', 'wc2' ) );
							}
							break;

						case ITEM_SKU_CODE:
							if( 0 == strlen($this->values[$data_num]) ){
								$this->error_log( $row, __('A SKU code is non-input.', 'wc2') );
							}
							break;

						case ITEM_SKU_NAME:
						case ITEM_SKU_UNIT:
							break;
						case ITEM_SKU_STOCK:
							if( !preg_match("/^[0-9]+$/", $this->values[$data_num]) ) {
								$this->error_log( $row, __( 'A value of the SKU stock is abnormal.', 'wc2' ) );
							}
							break;

						case ITEM_SKU_STATUS:
							if( !preg_match("/^[0-9]+$/", $this->values[$data_num]) ) {
								$this->error_log( $row, __( 'A value of the SKU status is abnormal.', 'wc2' ) );
							}
							break;

						case ITEM_SKU_PRICE:
							if( !preg_match("/^[0-9]+$/", $this->values[$data_num]) ) {
								$this->error_log( $row, __( 'A value of the SKU price is abnormal.', 'wc2' ) );
							}
							break;

						case ITEM_SKU_COSTPRICE:
							if( !preg_match("/^[0-9]+$/", $this->values[$data_num]) ) {
								$this->error_log( $row, __( 'A value of the SKU cost price is abnormal.', 'wc2' ) );
							}
							break;

						case ITEM_SKU_LISTPRICE:
							if( !preg_match("/^[0-9]+$/", $this->values[$data_num]) ) {
								$this->error_log( $row, __( 'A value of the SKU list price is abnormal.', 'wc2' ) );
							}
							break;

						case ITEM_SKU_SET_QUANTITY_DISCOUNT:
							if( !preg_match("/^[0-9]+$/", $this->values[$data_num]) ) {
								$this->error_log( $row, __( 'A value of the Quantity discount applies is abnormal.', 'wc2' ) );
							}
							break;

					}
				}

				if( '' != $this->get_log_line() ) {
					$this->create_log();
					$this->progress_add();
					continue;
				}

				$post = array();

				if( $pre_item_code != $this->values[COL_ITEM_CODE] ) {
					$sku_id = 1;

					if( $this->values[COL_POST_MODIFIED] == '' || $this->values[COL_POST_MODIFIED] == '0000-00-00 00:00:00' ) {
						$post['post_date'] = current_time( 'mysql' );
						$post['post_date_gmt'] = current_time( 'mysql', 1 );
						$post['post_modified'] = current_time( 'mysql' );
						$post['post_modified_gmt'] = current_time( 'mysql', 1 );
					} else {
						if( preg_match( "/^[0-9]+$/", substr( $this->values[COL_POST_MODIFIED], 0, 4 ) ) ) {
							$time_data = strtotime( $this->values[COL_POST_MODIFIED] );
						} else {
							$datetime = explode( ' ', $this->values[COL_POST_MODIFIED] );
							$date_str = $this->dates_interconv( $datetime[0] ).' '.$datetime[1];
							$time_data = strtotime( $date_str );
						}
						$post['post_date'] = date( 'Y-m-d H:i:s', $time_data );
						$post['post_date_gmt'] = gmdate( 'Y-m-d H:i:s', $time_data );
						$post['post_modified'] = date( 'Y-m-d H:i:s', $time_data );
						$post['post_modified_gmt'] = gmdate( 'Y-m-d H:i:s', $time_data );
					}
					if( 'publish' == $this->values[COL_POST_STATUS] ) {
						$now = current_time( 'mysql' );
						if( mysql2date( 'U', $post['post_modified'], false ) > mysql2date( 'U', $now, false ) ) {
							$this->values[COL_POST_STATUS] = 'future';
						}
					} elseif( 'future' == $this->values[COL_POST_STATUS] ) {
						$now = current_time( 'mysql' );
						if( mysql2date( 'U', $post['post_modified'], false ) <= mysql2date( 'U', $now, false ) ) {
							$this->values[COL_POST_STATUS] = 'publish';
						}
					}
					$post['ID'] = $post_id;
					$post['post_author'] = ( !WC2_Utils::is_blank($this->values[COL_POST_AUTHOR]) ) ? $this->values[COL_POST_AUTHOR] : 1;
					$post['post_content'] = $this->convert_encoding( $this->values[COL_POST_CONTENT] );
					$post['post_title'] = $this->convert_encoding( $this->values[COL_POST_TITLE] );
					$post['post_excerpt'] = $this->convert_encoding( $this->values[COL_POST_EXCERPT] );
					$post['post_status'] = $this->values[COL_POST_STATUS];
					$post['comment_status'] = ( !WC2_Utils::is_blank($this->values[COL_POST_COMMENT_STATUS]) ) ? $this->values[COL_POST_COMMENT_STATUS] : 'close';
					$post['ping_status'] = 'close';
					$post['post_password'] = ( 'private' == $post['post_status'] ) ? '' : $this->values[COL_POST_PASSWORD];
					$post['post_type'] = ITEM_POST_TYPE;
					$post['post_parent'] = 0;
					$post_name = sanitize_title( $this->convert_encoding( $this->values[COL_POST_NAME] ) );
					$post['post_name'] = wp_unique_post_slug( $post_name, $post_id, $post['post_status'], $post['post_type'], $post['post_parent'] );
					$post['to_ping'] = '';
					$post['pinged'] = '';
					$post['menu_order'] = 0;
					$post['post_content_filtered'] = '';

					if( empty($post['post_name']) && !in_array( $post['post_status'], array( 'draft', 'pending', 'auto-draft' ) ) ) {
						$post['post_name'] = sanitize_title( $post['post_title'], $post_id );
					}

					if( $mode == 'add' ) {
						$post['guid'] = '';
						if( false === $wpdb->insert( $wpdb->posts, $post ) ) {
							$this->error_log( $row, __( 'This data was not registered in the database.', 'wc2' ) );
							$pre_item_code = $this->values[COL_ITEM_CODE];
							$this->create_log();
							$this->progress_add();
							continue;
						}
						$post_id = $wpdb->insert_id;
						$wc2_item->set_the_post_id($post_id);
						$where = array( 'ID' => $post_id );
						$wpdb->update( $wpdb->posts, array( 'guid' => get_permalink( $post_id ) ), $where );

					} elseif( $mode == 'upd' ) {
						$where = array( 'ID' => $post_id );
						if( false === $wpdb->update( $wpdb->posts, $post, $where ) ) {
							$this->error_log( $row, __( 'The data were not registered with a database.', 'wc2' ) );
							$pre_item_code = $this->values[COL_ITEM_CODE];
							$this->create_log();
							$this->progress_add();
							continue;
						}

						//delete all metas of Item
						$meta_key_table = array();
						$cfrows = explode( ';', trim($this->values[COL_POST_CUSTOM_FIELD]) );
						//if( !(1 === count($cfrows) && '' == reset($cfrows)) ) {
						if( '' != reset($cfrows) ){
							foreach( $cfrows as $cf ) {
								list( $meta_key, $meta_value ) = explode( '=', $cf, 2 );
								if( !WC2_Utils::is_blank($meta_key) )
									array_push( $meta_key_table, $this->convert_encoding( $meta_key ) );
							}
						}
						$meta_key_table = apply_filters( 'wc2_filter_importitem_delete_postmeta', $meta_key_table );

						$res = $wc2_item->delete_custome_field_key($meta_key_table);

						if( false === $res ) {
							$this->error_log( $row, __( 'Error : delete postmeta', 'wc2' ) );
							$pre_item_code = $this->values[COL_ITEM_CODE];
							$this->create_log();
							$this->progress_add();
							continue;
						}
						//delete Item revisions
						$res = $wc2_item->delete_item_revision();
						if( false === $res ) {
							$this->error_log( $row, __( 'Error : delete revisions', 'wc2' ) );
							$pre_item_code = $this->values[COL_ITEM_CODE];
							$this->create_log();
							$this->progress_add();
							continue;
						}

						//delete relationships of category
						$res = $wc2_item->delete_term_relationship();
						if( false === $res ) {
							$this->error_log( $row, __( 'Error : delete term_relationships(category)', 'wc2' ) );
							$pre_item_code = $this->values[COL_ITEM_CODE];
							$this->create_log();
							$this->progress_add();
							continue;
						}

						//delete relationships of tag
						//$query = "SELECT term_taxonomy_id, COUNT(*) AS ct FROM {$wpdb->term_relationships} GROUP BY term_taxonomy_id";
						//$relation_data = $wpdb->get_results( $query, ARRAY_A );
						$relation_data = $wc2_item->get_count_term_taxonomy();
						foreach( (array)$relation_data as $relation_rows ) {
							$term_taxonomy_where['term_taxonomy_id'] = $relation_rows['term_taxonomy_id'];
						//	$term_taxonomy_id['term_taxonomy_id'] = $relation_rows['term_taxonomy_id'];
							$term_taxonomy_updatas['count'] = $relation_rows['ct'];
							if( false === $wpdb->update( $wpdb->term_taxonomy, $term_taxonomy_updatas, $term_taxonomy_where ) ) {
								$this->error_log( $row, __( 'Error : delete term_relationships(tag)', 'wc2' ) );
								$pre_item_code = $this->values[COL_ITEM_CODE];
								continue;
							}
						}
					}
					//add term_relationships, edit term_taxonomy

					//category
					$categories = explode( ';', $this->values[COL_POST_CATEGORY] );
					$category_ids = array();
					foreach( (array)$categories as $category ){
						$cat = get_term_by( 'slug', $category, 'item' );
						if( $cat == false ){
							$category = (string)$category;
							$this->error_log( $row, __(sprintf('Since the category slug "%s" does not exist or could not be category registration.', $this->convert_encoding($category) ), 'wc2') );
							continue;
						}
						$category_ids[] = $cat->term_id;
						
					}
					$term_taxonomy_ids = wp_set_post_terms( $post_id, $category_ids, 'item');
					foreach($term_taxonomy_ids as $term_taxonomy_id){
						$wc2_item->term_taxonomy_count_post($term_taxonomy_id);
					}

					//tag
					$tags_concat = str_replace(';', ',', $this->convert_encoding( $this->values[COL_POST_TAG] ) );
					$term_taxonomy_ids = wp_set_post_terms( $post_id, $tags_concat, 'item-tag');
					foreach($term_taxonomy_ids as $term_taxonomy_id){
						$wc2_item->term_taxonomy_count_post($term_taxonomy_id);
					}

					//add custom field
					$cfrows = explode( ';', trim($this->values[COL_POST_CUSTOM_FIELD]) );
					//if( !(1 === count($cfrows) && '' == reset($cfrows)) ) {

					if( '' != reset($cfrows) ) {
						$valstr = '';
						foreach( $cfrows as $cf ) {
							list( $meta_key, $meta_value ) = explode( '=', $cf, 2 );
							if( !WC2_Utils::is_blank($meta_key) ){
								update_post_meta( $post_id, $this->convert_encoding($meta_key), $this->convert_encoding($meta_value));
							}
						}
					}

					$wc2_item->clear_column();

					//Item data set
					$col = $start_col;
					foreach( $item_base_column as $key => $column ) {
						if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
							$wc2_item->set_the_item_value( $key, $this->set_value( $col, $column['type'] ) );
							$col++;
						}
					}
					foreach( $item_meta_column as $key => $column ) {
						if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
							$wc2_item->set_the_item_value( $key, $this->set_value( $col, $column['type'] ) );
							$col++;
						}
					}

					//SKU data set
					$col = $sku_start_col;
					foreach( $item_sku_column as $key => $column ) {
						if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
							$wc2_item->set_the_item_sku_value( $key, $sku_id, $this->set_value( $col, $column['type'] ) );
							$col++;
						}
					}
					foreach( $item_sku_meta_column as $key => $column ) {
						if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
							$wc2_item->set_the_item_sku_value( $key, $sku_id, $this->set_value( $col, $column['type'] ) );
							$col++;
						}
					}

					if( $mode == 'add' ) {
						$wc2_item->add_item_data();

					} elseif( $mode == 'upd' ) {
						$wc2_item->update_item_data();

					}
				} else {//sku登録のみの行

					$sku_id++;
					//SKU data set
					$col = $sku_start_col;
					foreach( $item_sku_column as $key => $column ) {
						if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
							$wc2_item->set_the_item_sku_value( $key, $sku_id, $this->set_value( $col, $column['type'] ) );
							$col++;
						}
					}
					foreach( $item_sku_meta_column as $key => $column ) {
						if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
							$wc2_item->set_the_item_sku_value( $key, $sku_id, $this->set_value( $col, $column['type'] ) );
							$col++;
						}
					}

					$item_id = $wc2_item->get_item_id_by_post_id( $post_id );
					$sku_data = $wc2_item->get_item_sku_data( $item_id, $sku_id );
					if( 0 == $wc2_item->count_sku_data($sku_id) ) {
						if( 0 < $sku_id and '' != $wc2_item->get_the_item_sku_code( $sku_id ) ) {
							$res = $wc2_item->add_item_sku_data( $sku_id, $wc2_item->get_item_sku_data( $item_id, $sku_id ) );
							if( false === $res ) break;
						}
					} else {
						$res = $wc2_item->update_item_sku_data( $sku_id, $wc2_item->get_item_sku_data( $item_id, $sku_id ) );
						if( false === $res ) break;
					}

				}

				if( '' != $this->get_log_line() ) {
					$this->create_log();
				}
			
				//登録成功数加算
				$this->success += 1;
				$pre_item_code = $this->values[COL_ITEM_CODE];
				clean_post_cache( $post_id );
				wp_cache_delete( $post_id, 'posts' );
				wp_cache_delete( $post_id, 'post_meta' );
				clean_object_term_cache( $post_id, 'post' );

				//進捗加算
				$this->progress_add();
			}
			wp_import_cleanup( $this->id );

			$this->false = $this->data_rows - $this->success;
			
			echo '<h3>'.__( 'All Done.', 'wc2' ).'</h3>
				  <h3>' . sprintf( __('Success %d failure %d', 'wc2'), $this->success, $this->false ) . '</h3>';
			if( 0 < strlen($this->log) ) {
				WC2_Utils::wc2_log( $this->log, "import_item.log" );
				echo str_replace( "\n", "<br />", $this->log );
			}
		}

		function set_value( $col, $type ) {
			if( isset($this->values[$col]) ) {
				if( $type == TYPE_TEXT or $type == TYPE_TEXT_Z or $type == TYPE_TEXT_ZK ) {
					$value = $this->convert_encoding( $this->values[$col] );
				} else {
					$value = $this->values[$col];
				}
			} else {
				$value = ( $type == TYPE_TEXT_I or $type == TYPE_TEXT_F or $type == TYPE_TEXT_P ) ? 0 : '';
			}
			return $value;
		}

		function create_log() {
			$this->log .= $this->log_line;
			$this->log_line = '';
		}

		function error_log( $row, $msg ) {
			$this->log_line .= "No.".($row+1)."\t".$msg."\r\n";
		}

		function get_log_line() {
			return $this->log_line;
		}

		function convert_encoding( $str ) {
			if( '' != trim( $str ) ) {
				$str = ( $this->encode_type == 0 ) ? trim( mb_convert_encoding( $str, 'UTF-8', 'SJIS' ) ) : trim( $str );
			}
			return $str;
		}

		function dates_interconv( $date_str ) {
			$base_struc = preg_split( '[/.-]', 'd/m/Y' );
			$date_str_parts = preg_split( '[/.-]', $date_str );
			$date_elements = array();

			$p_keys = array_keys( $base_struc );

			foreach( $p_keys as $p_key ) {
				if( !empty( $date_str_parts[$p_key] ) ) {
					$date_elements[$base_struc[$p_key]] = $date_str_parts[$p_key];
				} else {
					return false;
				}
			}
			$dummy_ts = mktime( 0, 0, 0, $date_elements['m'], $date_elements['d'], $date_elements['Y'] );
			return date( 'Y-m-d', $dummy_ts );
		}

		//Progressbar 進捗状況加算
		function progress_add(){
			echo '<script type="text/javascript">PG_Add('.$this->data_rows.');</script>'."\r\n";
			ob_flush();
			flush();
		}
	}
}

function wc2_import_item() {

	$wc2_importer = new WC2_Importer_Item();

//	require_once( ABSPATH.'wp-admin/admin-header.php' );
	$wc2_importer->header();

	set_time_limit(0);
	$result = $wc2_importer->import();
	echo $result;

//	if( is_wp_error( $result ) )
//		echo $result->get_error_message();

	$wc2_importer->footer();
//	include( ABSPATH.'wp-admin/admin-footer.php' );
}

/*-------------------------------
			Download
---------------------------------*/
function wc2_download_item_list(){
	global $wpdb, $wp_query;

	if( isset($wp_query->request) ){
		list($item_query, $limit) = explode('LIMIT', $wp_query->request);
		$rows = $wpdb->get_results($item_query, ARRAY_A);
	}else{
		return;
	}

	$ext = $_REQUEST['ftype'];
	if($ext == 'csv') {//CSV
		$sp = ";";
		$eq = "=";
		$lf = "\n";
	} else {
		exit();
	}
	$wc2_opt_item = wc2_get_option('wc2_opt_item');
	if(!is_array($wc2_opt_item)){
		$wc2_opt_item = array();
	}
	$wc2_opt_item['chk_header'] = isset($_GET['chk_header']) ? 1: 0;
	$wc2_opt_item['ftype_item'] = $ext;

	update_option('wc2_opt_item', $wc2_opt_item);

	$wc2_item = WC2_DB_Item::get_instance();
	$item_base_column = $wc2_item->get_item_base_column();
	$item_meta_column = $wc2_item->get_item_meta_column();
	$item_sku_column = $wc2_item->get_item_sku_column();
	$item_sku_meta_column = $wc2_item->get_item_sku_meta_column();

	$item_label = $wc2_item->get_item_label();
	$item_label[ITEM_PURCHASE_LIMIT_LOWEST] = __( '購入制限数(最低)', 'wc2' );
	$item_label[ITEM_PURCHASE_LIMIT_HIGHEST] = __( '購入制限数(最大)', 'wc2' );
	$item_label[ITEM_QUANTITY_DISCOUNT_NUM1] = __( '大口割引1(数)', 'wc2' );
	$item_label[ITEM_QUANTITY_DISCOUNT_RATE1] = __( '大口割引1(割引率)', 'wc2' );
	$item_label[ITEM_QUANTITY_DISCOUNT_NUM2] = __( '大口割引2(数)', 'wc2' );
	$item_label[ITEM_QUANTITY_DISCOUNT_RATE2] = __( '大口割引2(割引率)', 'wc2' );
	$item_label[ITEM_QUANTITY_DISCOUNT_NUM3] = __( '大口割引3(数)', 'wc2' );
	$item_label[ITEM_QUANTITY_DISCOUNT_RATE3] = __( '大口割引3(割引率)', 'wc2' );
	$wc2_item->set_item_label($item_label);

	//---------------------- TITLE -----------------------//
	$line = '';
	if( 1 == $wc2_opt_item['chk_header'] ){
		$line .= '"'. __('Post ID', 'wc2') .'"';
		$line .= ',"'. __('Post Author', 'wc2') .'"';
		$line .= ',"'. __('explanation', 'wc2') .'"';
		$line .= ',"'. __('Title', 'wc2') .'"';
		$line .= ',"'. __('excerpt', 'wc2') .'"';
		$line .= ',"'. __('display status', 'wc2') .'"';
		$line .= ',"'. __('Comment Status', 'wc2') .'"';
		$line .= ',"'. __('Post Password', 'wc2') .'"';
		$line .= ',"'. __('Post Name', 'wc2') .'"';
		$line .= ',"'. __('公開日', 'wc2') .'"';
		$line .= ',"'. __('カテゴリー', 'wc2') .'"';
		$line .= ',"'. __('タグ', 'wc2') .'"';
		$line .= ',"'. __('Custom Field', 'wc2') .'"';
//		$line .= ',"'. __('商品コード', 'wc2') .'"';
//		$line .= ',"'. __('商品名', 'wc2') .'"';
		
		foreach($item_base_column as $key => $column){
			if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
				$line .= ',"'. $wc2_item->get_the_item_label($key) .'"';
			}
		}
		foreach( (array)$item_meta_column as $key => $column ) {
			if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
				$line .= ',"'. $wc2_item->get_the_item_label( $key ) .'"';
			}
		}
		foreach( $item_sku_column as $key => $column ) {
			if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
				$line .= ',"'. $wc2_item->get_the_item_label( $key ) .'"';
			}
		}
		foreach( $item_sku_meta_column as $key => $column ) {
			if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
				$line .= ',"'. $wc2_item->get_the_item_label( $key ) .'"';
			}
		}

		//商品オプション

		$line .= $lf;
	}

	set_time_limit( 3600 );
	header( "Content-Type: application/octet-stream" );
	header( "Content-Disposition: attachment; filename=wc2_item_list.csv" );
	mb_http_output( 'pass' );

	@ob_end_flush();
	flush();

	$array_value_types = array(TYPE_CHECK, TYPE_SELECT_MULTIPLE); //セミコロン区切りで出力する

	foreach( (array)$rows as $row ){
		$post_id = $row['ID'];
		$post = get_post( $post_id );
		$wc2_item->set_the_post_id($post_id);
		$item_data = $wc2_item->get_item_data();

		//Post Data
		$line_item = '';
		$line_item  = '"'.$post->ID.'"';
		$line_item .= ',"'.$post->post_author.'"';
		$line_item .= ',"'.wc2_entity_decode( $post->post_content ).'"';
		$line_item .= ',"'.wc2_entity_decode( $post->post_title ).'"';
		$line_item .= ',"'.wc2_entity_decode( $post->post_excerpt ).'"';
		$line_item .= ',"'.$post->post_status.'"';
		$line_item .= ',"'.$post->comment_status.'"';
		$line_item .= ',"'.$post->post_password.'"';
		$line_item .= ',"'.urldecode( $post->post_name ).'"';
		$line_item .= ',"'.$post->post_date.'"';

		//Categories
		$category = '';
		//$cat_ids = get_the_category( $post_id );

		$categories = wp_get_post_terms( $post_id, 'item' );

		if( !empty($categories) ) {
			foreach( $categories as $val ) {
				$category .= $val->term_id.$sp;
			}
			$category = rtrim( $category, $sp );
		}
		$line_item .= ',"'.$category.'"';

		//Tags
		$tag = '';
		$tags = wp_get_post_terms( $post_id, 'item-tag' );
		foreach( $tags as $val ) {
			$tag .= $val->name.$sp;
		}
		$tag = rtrim( $tag, $sp );
		$line_item .= ',"'.$tag.'"';

		//Custom Fields
		$cfield = '';
		$custom_fields = get_post_custom( $post_id );

		if( is_array($custom_fields) && 0 < count($custom_fields) ) {
			foreach( $custom_fields as $cfkey => $cfvalues ) {
				if( is_array($cfvalues) )
					$cfield .= wc2_entity_decode( $cfkey ) . $eq . wc2_entity_decode( $cfvalues[0] ) . $sp;
				else
					$cfield .= wc2_entity_decode( $cfkey ) . $eq . wc2_entity_decode( $cfvalues ) . $sp;
			}
			$cfield = rtrim( $cfield, $sp );
		}
		$line_item .= ',"'.$cfield.'"';

		//Item Data
		foreach($item_base_column as $key => $column){
			if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
				//checkbox, multiselectはセミコロン区切りで出力
				if( in_array( $column['type'], $array_value_types ) ){
					$value = '';
					$value = maybe_unserialize($wc2_item->get_the_item_value($key));
					if( !empty($value) ){
						$concat = '';
						foreach(array($value) as $key => $val){
							$concat = $val.$sp;
						}
						$concat = rtrim($concat, $sp);
						$line_item .= ',"'. $concat .'"';
					}else{
						$line_item .= ',""';
					}
				}else{
					$line_item .= ',"'. $wc2_item->get_the_item_value($key) .'"';
				}
			}
		}
		foreach( $item_meta_column as $key => $column ) {
			if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
				//checkbox, multiselectはセミコロン区切りで出力
				if( in_array( $column['type'], $array_value_types ) ){
					$value = '';
					$value = maybe_unserialize($wc2_item->get_the_item_value($key));
					if( !empty($value) ){
						$concat = '';
						foreach(array($value) as $key => $val){
							$concat = $val.$sp;
						}
						$concat = rtrim($concat, $sp);
						$line_item .= ',"'. $concat .'"';
					}else{
						$line_item .= ',""';
					}
				}else{
					$line_item .= ',"'. $wc2_item->get_the_item_value($key) .'"';
				}
			}
		}

		//Item Options
		$line_options = '';
/*
		$option_meta = usces_get_opts( $post_id, 'sort' );
		foreach($option_meta as $option_value) {
			$value = '';
			if(is_array($option_value['value'])) {
				foreach($option_value['value'] as $k => $v) {
					$v = usces_change_line_break( $v );
					$values = explode("\n", $v);
					foreach($values as $val) {
						$value .= $val.$sp;
					}
				}
				$value = rtrim($value, $sp);
			} else {
				$value = trim($option_value['value']);
				$value = str_replace("\n", ';', $value);
			}

			$line_options .= $td_h.usces_entity_decode($option_value['name'], $ext).$td_f;
			$line_options .= $td_h.$option_value['means'].$td_f;
			$line_options .= $td_h.$option_value['essential'].$td_f;
			$line_options .= $td_h.usces_entity_decode($value, $ext).$td_f;

		}
*/

		//SKU data
		foreach( $item_data['item_sku'] as $skuindex => $skuval ){
			$line_sku = '';
			foreach( $item_sku_column as $key => $column ) {
				if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
					//checkbox, multiselectはセミコロン区切りで出力
					if( in_array( $column['type'], $array_value_types ) ){
						$value = '';
						$value = maybe_unserialize($wc2_item->get_the_item_sku_value($key, $skuindex));
						if( !empty($value) ){
							$concat = '';
							foreach(array($value) as $key => $val){
								$concat = $val.$sp;
							}
							$concat = rtrim($concat, $sp);
							$line_sku .= ',"'. $concat .'"';
						}else{
							$line_sku .= ',""';
						}
					}else{
						$line_sku .= ',"'. $wc2_item->get_the_item_sku_value($key, $skuindex) .'"';
					}
				}
			}
			foreach( $item_sku_meta_column as $key => $column ) {
				if( $column['display'] != 'none' and $column['type'] != TYPE_PARENT ) {
					//checkbox, multiselectはセミコロン区切りで出力
					if( in_array( $column['type'], $array_value_types ) ){
						$value = '';
						$value = maybe_unserialize($wc2_item->get_the_item_sku_value($key, $skuindex));
						if( !empty($value) ){
							$concat = '';
							foreach(array($value) as $key => $val){
								$concat = $val.$sp;
							}
							$concat = rtrim($concat, $sp);
							$line_sku .= ',"'. $concat .'"';
						}else{
							$line_sku .= ',""';
						}
					}else{
						$line_sku .= ',"'. $wc2_item->get_the_item_sku_value($key, $skuindex) .'"';
					}
				}
			}
			$line .= $line_item. $line_sku. $line_options. "\n";
		}

		print(mb_convert_encoding($line, "SJIS-win", "UTF-8"));
		if( ob_get_contents() ) {
			ob_flush();
			flush();
		}

		$line = '';
		wp_cache_flush();
	}
	exit();
}

