<?php
//WC2_List_Tableクラスの確認
if(!class_exists('WC2_List_Table')){
	require_once( WC2_PLUGIN_DIR . '/admin/includes/class-wc2-list-table.php' );
}

class WC2_Order_List_Table extends WC2_List_Table {

	/*********************************************
	*コンストラクタ設定
	*
	*NOTE: 単数形、複数形のスラッグAjaxの使用許可
	
	◆Redefine

	*********************************************/
	function __construct() {
		parent::__construct( array(
			'singular' => 'order_tag',
			'plural'   => 'order_tags',
			'ajax' => true
		));
	}

	/********************************************
	* スクリーンのopとデフォルトのソートカラムを設定
	*
	* NOTE :
	
	◆Redefine

	*********************************************/
	protected function get_list_info() {
		$list_info = array();
		$list_info['per_page_slug'] = WC2_Order::$per_page_slug;
		$list_info['default_orderby'] = 'order_date';
		return $list_info;
	}

	/*********************************************
	*ソート可能なカラムの設定

	*NOTE: ソートを可能にするカラムのスラッグを配列のkeyとし、
           ソートされた状態のカラムは第二引数をtrueとする

	*********************************************/
	function get_sortable_columns() {
		$columns = $this->get_columns();
		unset($columns['cb']);
		unset($columns['receipt_status']);
		unset($columns['order_status']);
		unset($columns['order_type']);

		$list_info = $this->get_list_info();
		$default_orderby = $list_info['default_orderby'];

		$sortable_columns = array();
		foreach($columns as $key => $name){
			if($default_orderby == $key){
				$sortable_columns[$key] = array( $key, true );
			}else{
				$sortable_columns[$key] = array( $key, false );
			}
		}
		return $sortable_columns;
	}

	/*********************************************
	*テーブルの各カラムのkeyと表示タイトルを設定
	*
	*NOTE: 

	◆Redefine

	*********************************************/
	public static function define_columns() {
		if( wc2_is_membersystem_state() ) {
			$columns = array(
				'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
				'dec_order_id' => __('Order number', 'wc2'),
				'order_date' => __('Order date', 'wc2'),
				'member_id' => __('Membership ID', 'wc2'),
				'name' => __('Name', 'wc2'),
				'pref' => __('Area', 'wc2'),
				'delivery_name' => __('Delivery method', 'wc2'),
				'total_price' => __('Amount', 'wc2'),
				'payment_name' => __('Payment method', 'wc2'),
				'receipt_status' => __('Receipt status', 'wc2'),
				'order_status' => __('Order status', 'wc2'),
				'order_type' => __('Order type', 'wc2'),
				'order_modified' => __('Shipping date', 'wc2')
			);
		} else {
			$columns = array(
				'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
				'dec_order_id' => __('Order number', 'wc2'),
				'order_date' => __('Order date', 'wc2'),
				'name' => __('Name', 'wc2'),
				'pref' => __('Area', 'wc2'),
				'delivery_name' => __('Delivery method', 'wc2'),
				'total_price' => __('Amount', 'wc2'),
				'payment_name' => __('Payment method', 'wc2'),
				'receipt_status' => __('Receipt status', 'wc2'),
				'order_status' => __('Order status', 'wc2'),
				'order_type' => __('Order type', 'wc2'),
				'order_modified' => __('Shipping date', 'wc2')
			);
		}
		$columns = apply_filters( 'wc2_filter_admin_order_list_define_columns', $columns );
		return $columns;
	}

	/*********************************************
	*各カラムの描写指定
	*
	*NOTE: カラム特別メソッドによる描写指定がないものはWC2_List_Tableのdefaultの処理に従う。
			function column_スラッグで描写指定可能

	◆Redefine

	*********************************************/
	//チェックボックス
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
				$this->_args['singular'],
				$item['ID']
		);
	}

	//指定カラムに編集と削除リンク表示
	function column_dec_order_id( $item ) {
		//編集と削除のリンク
		$actions = array(
			'edit' => sprintf('<a href="%s" id="edit-%s" title="%s を編集する" class="edit-order">' . __('Edit') . '</a>', esc_url( add_query_arg( array( 'action' => 'edit', 'target' => $item['ID'] ) ) ), $item['dec_order_id'], $item['dec_order_id'] ),
			'delete' => sprintf('<a href="%s" id="delete-%s" title="%s を削除する" class="delete-order">' . __('Remove') . '</a>',esc_url( add_query_arg( array( 'action' => 'delete', 'target' => $item['ID'], 'wc2_nonce' => wp_create_nonce( 'wc2_order_list' ) ) ) ), $item['dec_order_id'], $item['dec_order_id'] )
		);
		$actions = apply_filters( 'wc2_filter_admin_order_list_actions', $actions );

		//title
		return sprintf('%1$s %2$s',
			/*$1%s*/ $item['dec_order_id'],
			/*$2%s*/ $this->row_actions($actions)
		);
	}

	function column_member_id( $item ) {
		if( empty($item['member_id']) ) {
			$item['member_id'] = __('Nonmember', 'wc2');
		}
		return $item['member_id'];
	}

	function column_receipt_status( $item ) {
		$receipt_status = wc2_get_option( 'receipt_status' );
		foreach( $receipt_status as $status_value => $status_name ) {
			if( $item['receipt_status'] == $status_value ) {
				$item['receipt_status'] = $status_name;
				break;
			}
		}
		return $item['receipt_status'];
	}

	function column_order_status( $item ) {
//*** LI CUSTOMIZE >>>
		$order_status = $item['order_status'];
		$item['order_status'] = '';
		$flag_check = wc2_get_order_meta_value( $item['ID'], 'flag_check' );
		if( !empty($flag_check) and $flag_check[0]['meta_value'] == 1 ) {
			$item['order_status'] .= '<span class="flag-check"><img src="'.get_template_directory_uri().'/images/admin/icon-jidotsuka.png" alt="jidotsuka"></span>';
		}
		$flag_caution = wc2_get_order_meta_value( $item['ID'], 'flag_caution' );
		if( !empty($flag_caution) and $flag_caution[0]['meta_value'] == 1 ) {
			$item['order_status'] .= '<span class="flag-caution"><img src="'.get_template_directory_uri().'/images/admin/icon-youchui.png" alt="youchui"></span>';
		}
//*** LI CUSTOMIZE <<<
		$management_status = wc2_get_option( 'management_status' );
		foreach( $management_status as $status_value => $status_name ) {
//*** LI CUSTOMIZE >>>
			//if( $item['order_status'] == $status_value ) {
			//	$item['order_status'] = $status_name;
			if( $order_status == $status_value ) {
				$item['order_status'] .= $status_name;
//*** LI CUSTOMIZE <<<
				break;
			}
		}
		return $item['order_status'];
	}

	function column_order_delivery_name( $item ) {
		return $item['delivery_name'];
	}

	function column_total_price( $item ) {
		return wc2_crform( $item['total_price'], true, false );
	}

	/*********************************************
	*一括操作のアクション設定

	*NOTE:

	◆Redefine

	*********************************************/
	function get_bulk_actions() {
		$actions = array(
			'delete_batch' => __('Bulk Delete', 'wc2')
		);
		$actions = apply_filters( 'wc2_filter_admin_order_list_bulk_actions', $actions );
		return $actions;
	}

	/*********************************************
	*リストに表示するデータを取得

	*NOTE:

	◆Redefine

	*********************************************/
	function get_list_data( $args ) {
		$wc2_order = WC2_DB_Order::get_instance();

		if( isset($_REQUEST['search_refine']) ) {//絞り込み検索
			$_SESSION[WC2]['order-list']['search_period'] = $_REQUEST['search_period'];
			$_SESSION[WC2]['order-list']['startdate'] = $_REQUEST['startdate'];
			$_SESSION[WC2]['order-list']['enddate'] = $_REQUEST['enddate'];
		}
		$search_period = ( isset($_SESSION[WC2]['order-list']['search_period']) ) ? $_SESSION[WC2]['order-list']['search_period'] : 3;
		switch( $search_period ) {
		case 0://今月
			$thismonth = date_i18n('Y-m-01 00:00:00');
			$args['where'] = "order_date >= '{$thismonth}' ";
			break;
		case 1://先月
			$thismonth = date_i18n('Y-m-01 00:00:00');
			$lastmonth = date_i18n('Y-m-01 00:00:00', mktime(0, 0, 0, date('m')-1, 1, date('Y')));
			$args['where'] = "order_date >= '{$lastmonth}' AND order_date < '{$thismonth}' ";
			break;
		case 2://過去1週間
			$lastweek = date_i18n('Y-m-d 00:00:00', mktime(0, 0, 0, date('m'), date('d')-7, date('Y')));
			$args['where'] = "order_date >= '{$lastweek}' ";
			break;
		case 3://過去30日間
			$last30 = date_i18n('Y-m-d 00:00:00', mktime(0, 0, 0, date('m'), date('d')-30, date('Y')));
			$args['where'] = "order_date >= '{$last30}' ";
			break;
		case 4://過去90日間
			$last90 = date_i18n('Y-m-d 00:00:00', mktime(0, 0, 0, date('m'), date('d')-90, date('Y')));
			$args['where'] = "order_date >= '{$last90}' ";
			break;
		case 5://期間指定
			if( isset($_REQUEST['startdate']) ) {
				$startdate = $_REQUEST['startdate'];
			} elseif( isset($_SESSION[WC2]['order-list']['startdate']) ) {
				$startdate = $_SESSION[WC2]['order-list']['startdate'];
			} else {
				$startdate = '';
			}
			if( isset($_REQUEST['enddate']) ) {
				$enddate = $_REQUEST['enddate'];
			} elseif( isset($_SESSION[WC2]['order-list']['enddate']) ) {
				$enddate = $_SESSION[WC2]['order-list']['enddate'];
			} else {
				$enddate = '';
			}
			if( '' != $startdate or '' != $enddate ) {
				if( '' == $enddate ) {
					$args['where'] = "order_date >= '{$startdate}'";
				} elseif( '' == $startdate ) {
					$args['where'] = "order_date < '{$enddate}'";
				} else {
					$args['where'] = "order_date >= '{$startdate}' AND order_date < '{$enddate}'";
				}
			}
			break;
		case 6://全て
		default:
			$args['where'] = "";
			break;
		}
		$args = apply_filters( 'wc2_filter_admin_order_list_data_args', $args );
		$results = $wc2_order->get_order_list_data( $args );
		return $results;
	}

	/*********************************************
	*

	*NOTE:

	*********************************************/
	function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? ' class="alternate"' : '' );
		$row_class = apply_filters( 'wc2_filter_admin_order_list_single_row', $row_class, $item );
		echo '<tr' . $row_class . '>';
		$this->single_row_columns( $item );
		echo '</tr>';
	}
}
