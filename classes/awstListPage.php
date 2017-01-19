<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class AwstListPage extends WP_List_Table {

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

        usort( $data, array( &$this, 'usort_reorder' ) );

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);



        $this->items = $data;


        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns() {
        $columns = array(
            'post_title'    => 'Title',
            'post_type'     => 'Post Type',
            'post_likes'    => 'Likes',
            'post_Rating'   => 'Ratings',
            'post_reviews'  => 'Reviews',
            'actions'       => 'Action',
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
        $sortable_columns = array(
            'post_likes'  => array('post_likes',true),
            'post_Rating' => array('post_Rating',true),
            'post_reviews'=> array('post_reviews',true)

        );
        return $sortable_columns;
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data() {
        global $wpdb;

        $IDs    = $this->getPostIDs();
        $sql    = "SELECT ID, post_title, post_type FROM  {$wpdb->prefix}posts WHERE `ID` IN('".implode("','",$IDs)."')";
        $result = $wpdb->get_results( $sql, 'OBJECT' );

        $sql_bb    = "SELECT * FROM  {$wpdb->prefix}bp_activity WHERE `id` IN('".implode("','",$IDs)."')";
        $result_bb = $wpdb->get_results( $sql_bb, 'OBJECT' );

        file_put_contents(dirname(__FILE__)."/IDs.log", print_r($IDs, true));
        file_put_contents(dirname(__FILE__)."/sql_bb.log", print_r($sql_bb, true));
        file_put_contents(dirname(__FILE__)."/result_bb.log", print_r($result_bb, true));

        $data   = array();

        foreach ( $result as $item) {

            $title  = get_the_title($item->ID);

            $data1['ID']                = $item->ID;
            $data1['post_title']        = $this->getPostLink( $item->ID, $title );
            $data1['post_type']         = AwstConfig::postTypeLabel( $item->post_type );
            $data1['post_likes_link']   = $this->getTotalLikes( $item->ID );
            $data1['post_likes']        = $this->getTotalLikesCount( $item->ID );
            $data1['post_Rating']       = $this->getAverageRatingCount( $item->ID );
            $data1['post_Rating_link']  = $this->getAverageRating( $item->ID );
            $data1['post_reviews']      = $this->getReviewsCount($item->ID);
            $data1['actions']           = $this->getTotalLikes( $item->ID )."&nbsp; | &nbsp;".$this->getAverageRating( $item->ID ) ."&nbsp; | &nbsp;". $this->getReviews($item->ID);

            $data[] = $data1;
        }

        foreach ( $result_bb as $item) {

            if($item->action == ''){
                $title = "User Activity";
            }else{
                if( $item->type == 'activity_comment'){
                    $title = $item->action." - ".$item->content;
                }else{
                    $title = $item->action;
                }
            }

            $data1['ID']                = $item->id;
            $data1['post_title']        = $title;
            $data1['post_type']         = $item->type;
            $data1['post_likes_link']   = $this->getTotalLikes( $item->id );
            $data1['post_likes']        = $this->getTotalLikesCount( $item->id );
            $data1['post_Rating']       = $this->getAverageRatingCount( $item->id );
            $data1['post_Rating_link']  = $this->getAverageRating( $item->id );
            $data1['post_reviews']      = $this->getReviewsCount($item->id);
            $data1['actions']           = $this->getTotalLikes( $item->id )."&nbsp; | &nbsp;".$this->getAverageRating( $item->id ) ."&nbsp; | &nbsp;". $this->getReviews($item->id);

            $data[] = $data1;
        }
        return $data;
    }

    function usort_reorder( $a, $b ) {
      // If no sort, default to title
      $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'post_likes';
      // If no order, default to asc
      $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
      // Determine sort order
      $result = strcmp( $a[$orderby], $b[$orderby] );
      // Send final sort direction to usort
      return ( $order === 'desc' ) ? $result : -$result;
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

            case 'post_title':
            case 'post_type':
            case 'post_likes':
            case 'post_Rating':
            case 'post_reviews':
            // case 'post_likes_link':
            // case 'post_Rating_link':
            case 'actions':
                return $item[$column_name];
            default:
                return print_r( $item, true ) ;
        }
    }

    function getTotalLikes( $postID ) {
    	$postmeta    = get_post_meta($postID, 'awst_like', true);
        $totalLiked  = AwstComman::getLikes($postmeta);

        /*get the admin url of the admin page.*/
        $args['id']  = $postID;
        $url         = AwstComman::getAdminUrl('awst_likes', $args);

        return '<a href="'.$url.'" title="view Likes" class="action_links">Likes</a>';
    }

    function getTotalLikesCount( $postID ) {
        $postmeta    = get_post_meta($postID, 'awst_like', true);
        $totalLiked  = AwstComman::getLikes($postmeta);

        return $totalLiked;
    }

    function getAverageRating( $postID ) {
    	$postdata = get_post_meta($postID, 'awst_ratings', true);
        $rating   = AwstComman::getRatings($postdata);

        /*get the admin url of the admin page.*/
        $args['id'] = $postID;
        $url = AwstComman::getAdminUrl('awst_ratings', $args);

        return '<a href="'.$url.'" title="view Ratings" class="action_links">Rating</a>';

    }

    function getAverageRatingCount( $postID ) {
        $postdata = get_post_meta($postID, 'awst_ratings', true);
        $rating   = AwstComman::getRatings($postdata);

        return $rating;

    }

    function getReviews( $postID ) {

        $postdata = get_post_meta($postID, 'awst_review', true);
        $review   = AwstComman::getReviews($postID);
        //print_r($review);
        /*get the admin url of the admin page.*/
        $args['id'] = $postID;
        $url = AwstComman::getAdminUrl('awst_review', $args);

        return '<a href="'.$url.'" title="view Reviews" class="action_links">Reviews</a>';
    }

    function getReviewsCount( $postID ) {

        $postdata = get_post_meta($postID, 'awst_review', true);
        $review   = AwstComman::getReviews($postID);

        return count($review);
    }

    function getPostIDs(){

    	$likes   = get_option('awst_like', true);
        $ratings = get_option('awst_ratings', true);
        $review  = get_option('awst_reviews', true);

        $likes       = array_filter($likes);
        $like_key    = array_keys($likes);

        $ratings     = array_filter($ratings);
        $rating_key  = array_keys($ratings);

        $review      = array_filter($review);
        $review_key  = array_keys($review);

        if(!is_array( $like_key )){
            $like_key = array();
        }
        if(!is_array( $rating_key )){
            $rating_key = array();
        }
        if(!is_array( $review_key )){
            $review_key = array();
        }

        $allRecords  = array_merge($like_key, $rating_key, $review_key);
        $finalPosts  = array_unique($allRecords);


        return $finalPosts;
    }

    function getPostLink( $postID, $postTitle ) {

        $url = get_permalink( $postID );

        return '<a href="'.$url.'" title="'.$postTitle.'">'.$postTitle.'</a>';
    }
}
?>