<?php
//WC2_List_Tableクラスの確認
if(!class_exists('WC2_List_Table')){
	require_once( WC2_PLUGIN_DIR . '/admin/includes/class-wc2-list-table.php' );
}

class Member_List_Table extends WC2_List_Table {

	/********************************************
	*コンストラクタ設定
	*
	*NOTE: 単数形、複数形のスラッグAjaxの使用許可
	
	◆Redefine

	*********************************************/
	function __construct(){
		parent::__construct(
						array(
							'singular' => 'member',
							'plural'   => 'members',
							'ajax' => true
							)
						);
	}

	/********************************************
	* スクリーンのopとデフォルトのソートカラムを設定
	*
	* NOTE :

	◆Redefine

	*********************************************/
	protected function get_list_info(){
		$list_info = array();
		$list_info['per_page_slug'] = WC2_Member::$per_page_slug;
		$list_info['default_orderby'] = 'mem_registered';

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
		unset($columns['mem_rank']);

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

	*NOTE:

	◆Redefine

	*********************************************/
	public static function define_columns(){
		if( wc2_is_membersystem_point() ){
	        $columns = array(
	            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
				'ID' => __('Membership ID', 'wc2'),
				'mem_account' => __('Account', 'wc2'),
				'mem_name' => __('Name', 'wc2'),
				'mem_address' => __('Address', 'wc2'),
				'mem_tel' => __('Phone number', 'wc2'),
				'mem_email' => __('E-mail', 'wc2'),
				'mem_rank' => __('Rank', 'wc2'),
				'mem_point' => __('Holdings points', 'wc2'),
				'mem_registered' => __('Started date', 'wc2')
	        );
		}else{
	        $columns = array(
	            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
				'ID' => __('Membership ID', 'wc2'),
				'mem_account' => __('Account', 'wc2'),
				'mem_name' => __('Name', 'wc2'),
				'mem_address' => __('Address', 'wc2'),
				'mem_tel' => __('Phone number', 'wc2'),
				'mem_email' => __('E-mail', 'wc2'),
				'mem_rank'=> __('Rank', 'wc2'),
				'mem_registered' => __('Started date', 'wc2'),
	        );
		}

		do_action('wc2_action_member_list_defaine_columns', $columns);

        return $columns;
    }

	/*********************************************
	*各カラムの描写指定

	*NOTE: カラム特別メソッドによる描写指定がないものはWC2_List_Tableのdefaultの処理に従う。
			function column_スラッグで描写指定可能

	◆Redefine

	*********************************************/
	//チェックボックス
	function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            	$this->_args['singular'],
            	$item['ID']
        );
    }

	//指定カラムに編集と削除リンク表示
    //function column_mem_account( $item ){
    function column_ID( $item ){
        //編集と削除のリンク
        $actions = array(
            'edit'      => sprintf('<a href="%s" id="edit-%s" title="%s を編集する" class="edit_member">' . __('Edit') . '</a>', esc_url( add_query_arg( array( 'action' => 'edit', 'target' => $item['ID'] ) ) ), $item['ID'], $item['ID'] ),
			'delete'    => sprintf('<a href="%s" id="delete-%s" title="%s を削除する" class="delete_member">' . __('Remove') . '</a>',esc_url( add_query_arg( array( 'action' => 'delete', 'target' => $item['ID'], 'wc2_nonce' => wp_create_nonce( 'wc2_member_list' ) ) ) ), $item['ID'], $item['ID'] )
        );
		$actions = apply_filters( 'wc2_filter_admin_member_list_actions', $actions );

        //title
        return sprintf('%1$s %2$s',
             $item['ID'],
             $this->row_actions($actions)
        );
    }

	//氏名
	function column_mem_rank( $item ){
		$rank_type = wc2_get_option('rank_type');
		if( isset( $rank_type[$item['mem_rank']] ) ){
			return $rank_type[$item['mem_rank']];
		}
	}

	/*********************************************
	*条件によって行の<tr>にクラス名を付加(背景変化用)

	*

	◆Redefine

	*********************************************/
/*
	function single_row( $item ) {

		static $row_class = '';
		
		if( '0' == $item['mem_point'] ){
//			$row_class = ( $row_class == '' ? ' class="alternate"' : '' );
			$custom_class = ' class="listline point_zero"';
			echo '<tr' . $custom_class . '>';
			$this->single_row_columns( $item );
			echo '</tr>';
		}else{
//			$row_class = ( $row_class == '' ? ' class="alternate"' : '' );
//			echo '<tr' . $row_class . '>';
			echo '<tr class="listline">';
			$this->single_row_columns( $item );
			echo '</tr>';
		}
	}
*/

	/*********************************************
	*一括操作のアクション設定

	*NOTE:

	◆Redefine

	*********************************************/
	function get_bulk_actions() {
        $actions = array(
            'delete_batch' => __('Bulk Delete', 'wc2')
        );
		$actions = apply_filters('wc2_filter_admin_member_list_bulk_actions', $actions);
        return $actions;
    }

	/*********************************************
	*リストに表示するデータを取得

	*NOTE:

	◆Redefine

	*********************************************/
	function get_list_data( $args ) {
		$wc2_member = WC2_DB_Member::get_instance();

		$results = $wc2_member->get_member_list_data( $args );

		return $results;
	}
}
