<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class AwstComman {

    public function getRatings( $ratings ){
        $totalRating    =   0;
        $total          =   array_sum($ratings);
        $totalRating    =   $total/count($ratings);
        return  $totalRating;
    }

    public function getLikes( $likes ){
        if($likes){
            return count($likes);
        }else{
            return 0;
        }
    }

    public function isLiked($postmeta, $userID){
        if(in_array($userID, $postmeta)) {
            return true;
        }else{
            return false;
        }
    }

    /* function to get the admin page link */
    function getAdminUrl( $menuSlug, $items = null ) {
        $path = 'admin.php?page='.$menuSlug;

        if( ($items && is_array($items) ) ){
            foreach ($items as $key => $value) {
                $path .= '&'.$key.'='.$value;
            }
        }

        $url  = admin_url($path);
        return $url;
    }

    /*get starts */
    function getStars( $postID ){
        $postdata = get_post_meta($postID, 'awst_ratings', true);
        $rating   = AwstComman::getRatings($postdata);
        return AwstComman::getStarsHtml($rating);
    }

    /* function to get stars given by any user. */
    function getStarsByUser($postID, $userID ) {
        $postdata = get_post_meta($postID, 'awst_ratings', true);
        $rating   = $postdata[$userID];
        return AwstComman::getStarsHtml($rating);
    }
    function getUserreviews($userids) {
        
        $user     = array();
        $user['display_name'] = get_the_author_meta('display_name',$userids);
        $user['user_login'] = get_the_author_meta('user_login',$userids);
        $user['user_email'] = get_the_author_meta('user_email',$userids);
        
        return $user;
    }

    function getUserReview($postids) {
        
        global $wpdb;
        $sql = "SELECT * FROM wp_posts WHERE post_parent = $postids AND post_status = 'publish'";
        $result = $wpdb->get_results( $sql, 'OBJECT' );

        foreach ($result as $value) {
            $content = $value->post_content;
        }
        return $content;
    }


    /*function get star Html */
    function getStarsHtml( $ratings ){
       switch ( $ratings) {
            case 5:
                return '<span class="awst_rate_back">
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star" aria-hidden="true"></i>
                        </span>';
                break;
            case 4.5:
                return '<span class="awst_rate_back">
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star-half" aria-hidden="true"></i>
                        </span>';
                break;
            case 4:
                return '<span class="awst_rate_back">
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                        </span>';
                break;
            case 3.5:
                return '<span class="awst_rate_back">
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star-half" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                        </span>';
                break;
            case 3:
                return '<span class="awst_rate_back">
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                        </span>';
                break;
            case 2.5:
                return '<span class="awst_rate_back">
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star-half" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                        </span>';
                break;
            case 2:
                return '<span class="awst_rate_back">
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                        </span>';
                break;
            case 1.5:
                return '<span class="awst_rate_back">
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star-half" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                        </span>';
                break;
            case 1:
                return '<span class="awst_rate_back">
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                        </span>';
                break;
            case 0.5:
                return '<span class="awst_rate_back">
                            <i class="fa fa-star-half" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                        </span>';
                break;
            default:
                return '<span class="awst_rate_back">
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                        </span>';
                break;
        }

    }

    function getReviews( $post_id ){

        $reviews = get_posts( array(
                                'post_parent'    => $post_id,
                                'post_type'      => 'awst_review',
                                'post_status'    => 'any',
                                'orderby'        => 'post__in',
                                'posts_per_page' => -1,
                                'order'          => 'DESC',
                                'orderby'        => 'ID'
                            ) 
        );

        return $reviews;

    }

}/* class ends here. */
?>