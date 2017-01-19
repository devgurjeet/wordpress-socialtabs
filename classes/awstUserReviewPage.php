<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class AwstUserReviewPage extends WP_List_Table {

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns  = $this->get_columns();
        $hidden   = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();

        $perPage     = 10;
        $currentPage = $this->get_pagenum();
        $totalItems  = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns() {
        $columns = array(
            'display_name' => 'Name',
            'user_login'   => 'Username',
            'user_email'   => 'Email',
            'review'      => 'Review',
        );
        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns() {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns() {
        return array();
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data() {
        global $wpdb;

        $postID                 = $_GET['id'];
        $sql = "SELECT `post_content`,`post_author` FROM wp_posts WHERE post_parent = $postID AND post_status = 'publish'";
        $get_post = $wpdb->get_results( $sql, 'OBJECT' );
        
        $data = array();

        foreach ( $get_post as $item) {
            $sql      = "SELECT `user_login` , `user_email` , `display_name` FROM `wp_users` WHERE `ID` =".$item->post_author;
            $result = $wpdb->get_results( $sql, 'OBJECT' );
            $result = $result[0];
            $temp['ID'] = $item->ID;
            $temp['review'] = $item->post_content; 
            $temp['user_login'] = $result->user_login;;
            $temp['user_email'] = $result->user_email;
            $temp['display_name'] = $result->display_name;

            $data[] = $temp;
        }

        return $data;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name ) {
        switch( $column_name ) {

            case 'display_name':               
            case 'user_login':               
            case 'user_email':
            case 'review':
                return $item[$column_name];
                
            default:
                return print_r( $item, true ) ;
        }
    }
}
?>