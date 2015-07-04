<?php
//WP_List_Tableクラスの確認
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WC2_List_Table extends WP_List_Table {

	/********************************************
	*テーブルの各カラムのkeyと表示タイトルを返す
		
	*NOTE:

	*********************************************/
	function get_columns(){
		return get_column_headers( get_current_screen() );
	}

	/*********************************************
	*カラムのデフォルトの描写設定

	*NOTE: カラム特別メソッドによる描写指定がないものはこのdefaultの処理に従う。
			function column_スラッグで描写指定可能

	◆Redefine

	*********************************************/
	function column_default( $item, $column_name ){
		if( !array_key_exists( $column_name, $item ) ){
			$item[$column_name] = '';
		}
		return $item[$column_name];
	}

	/*********************************************
	*ソート可能なカラムの設定

	*NOTE: ソートを可能にするカラムのスラッグを配列のkeyとし、
           ソートされた状態のカラムは第二引数をtrueとする

	*********************************************/
	function get_sortable_columns(){
		$columns = $this->get_columns();
		unset($columns['cb']);

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
	*ソート

	*NOTE:

	*********************************************/
	function sort_culum_order_by($per_page){
		$list_info = $this->get_list_info();
		$default_orderby = $list_info['default_orderby'];
		$args = array(
			'posts_per_page' => $per_page,
			'orderby' => $default_orderby,
			'order' => 'DESC',
			'offset' => ( $this->get_pagenum() -1 ) * $per_page);
		if( !empty( $_REQUEST['s'] ) )
			$args['s'] = $_REQUEST['s'];

		if( !empty( $_REQUEST['orderby'] ) ){
			$columns = $this->get_columns();
			unset($columns['cb']);
			foreach($columns as $key => $name){
				if( $key == $_REQUEST['orderby'] ){
					$args['orderby'] = $key;
					break;
				}
			}
		}

		if( !empty( $_REQUEST['order'] ) ) {
			if( 'asc' == strtolower( $_REQUEST['order'] ) )
				$args['order'] = 'ASC';
			elseif( 'desc' == strtolower( $_REQUEST['order'] ) )
				$args['order'] = 'DESC';
		}

		return $args;
	}

	/*********************************************
	*リストの成形

	*NOTE:

	*********************************************/
	function prepare_items() {
		$current_screen = get_current_screen();
		$screen_array = (array)$current_screen;
		$list_info = $this->get_list_info();
		$per_page_slug = $list_info['per_page_slug'];

        $per_page = $this->get_items_per_page($per_page_slug);

		//ソート
		$args = $this->sort_culum_order_by($per_page);

		//現在のページ
		$current_page = $this->get_pagenum();

		//データ
		$all_data = $this->get_list_data($args);
		if( !is_array($all_data) ){
			$all_data = (array)$all_data;
		}

		//ページネーションの処理 現在までのページで表示したデータを除いて表示する
		$data = array_slice($all_data, (($current_page-1)*$per_page), $per_page);

		//表示するデータを格納
		$this->items = $data;
		$total_items = count($all_data);
		$total_pages = ceil( $total_items / $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page'	  => $per_page
		) );
	}
}
