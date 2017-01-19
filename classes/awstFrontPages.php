<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class AwstFrontPages {

    function content_filter( $content ){

        $post_id = bbp_get_reply_id();

        $poStTyPE = get_post_type($post_id);

        global $post;
        $flag = false;

        if($poStTyPE == "reply"){
            $post->ID = $post_id;
        } else {
            $post->ID = $post->ID;
        }

        $type = $post->post_type;

        $user_ID            = get_current_user_id();
        $course_meta_key    = "course_".$post->ID."_access_from";
        $is_course_enrolled = get_user_meta($user_ID, $course_meta_key, true);

        /*file_put_contents(dirname(__FILE__)."/course_meta_key.log", print_r( $course_meta_key , true),FILE_APPEND );
        file_put_contents(dirname(__FILE__)."/course_meta_key.log", print_r( "\n" , true),FILE_APPEND );

        file_put_contents(dirname(__FILE__)."/course_meta_key.log", print_r( $is_course_enrolled , true),FILE_APPEND );
        file_put_contents(dirname(__FILE__)."/course_meta_key.log", print_r( "\n" , true),FILE_APPEND );
*/
        $post_like      =   $type.'_like';
        $post_rate      =   $type.'_rate';
        $post_review    =   $type.'_review';

        $like   = false;
        $rate   = false;
        $review = false;

        $seletedOptions     =   get_option('awSocialTabsPostOptions', true);

        if( in_array($post_like, $seletedOptions )){

            $flag = true;

            $user_ID     = get_current_user_id();
            $postmeta    = get_post_meta($post->ID, 'awst_like', true);
            $isLiked     = AwstComman::isLiked($postmeta, $user_ID);
            $totalLiked  = AwstComman::getLikes($postmeta);

            if( $isLiked ){
                $like = '<div class="awst_like"><span class="awst_like_btn" id="awst_like_btn_'.$post->ID.'"><i data-post-like="true" data-post-id="'.$post->ID.'" class="fa fa-thumbs-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_'.$post->ID.'">'.$totalLiked.'</label> Me gusta</span></div>';
            }else{
                $like = '<div class="awst_like"><span class="awst_like_btn" id="awst_like_btn_'.$post->ID.'"><i data-post-like="true" data-post-id="'.$post->ID.'" class="fa fa-thumbs-o-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_'.$post->ID.'">'.$totalLiked.'</label> Me gusta</span></div>';
            }


        }

        if( in_array($post_rate, $seletedOptions )){

            $flag = true;

            $user_ID  = get_current_user_id();

            $postdata = get_post_meta($post->ID, 'awst_ratings', true);

            $ratings  = AwstComman::getRatings($postdata);
            $useRated = $postdata[$user_ID];

            $rate = '<div class="awst_rate">';
                if((is_user_logged_in() && $is_course_enrolled)){
                    for ($i = 1; $i <= 5; $i++) {
                        if( $i <= $useRated ){
                            $rate .= '<span id="star'.$i.'" class="awst_rate_btn"><i class="fa fa-star" aria-hidden="true" data-post-id="'.$post->ID.'" data-rate-id="'.$i.'" ></i></span>';
                        }else{
                            $rate .= '<span id="star'.$i.'" class="awst_rate_btn"><i class="fa fa-star-o" aria-hidden="true" data-post-id="'.$post->ID.'" data-rate-id="'.$i.'" ></i></span>';
                        }
                    }
                }else{
                    for ($i = 1; $i <= 5; $i++) {
                        if( $i <= $useRated ){
                            $rate .= '<span id="star'.$i.'" ><i class="fa fa-star" aria-hidden="true" data-post-id="'.$post->ID.'" data-rate-id="'.$i.'" ></i></span>';
                        }else{
                            $rate .= '<span id="star'.$i.'" ><i class="fa fa-star-o" aria-hidden="true" data-post-id="'.$post->ID.'" data-rate-id="'.$i.'" ></i></span>';
                        }
                    }
                }

            $rate .= '&nbsp;<span class="rating-text">Valuación media: &nbsp;</span> <span class="rating-text" id="average_rating">'.$ratings.'</span>';
            $rate .= '</div>';
        }

        if( in_array($post_review, $seletedOptions )){
            $flag = true;

            $user_ID  = get_current_user_id();
            $reviews  = AwstComman::getReviews( $post->ID );

            $html = '<div id="review-list">';
                $html .= '<ul>';

                foreach ($reviews as $key => $value) {
                    # code...
                    $userdetail = get_user_by( 'ID', $value->post_author );
                    $html .=    '<li>
                                    <div class="review-content">
                                        <span class="review_content_'.$value->ID.'" style="float: left"><i class="fa fa-comment" aria-hidden="true"></i> '.$value->post_content.'</span>';
                                        if($user_ID == $value->post_author ){
                    $html .=                '<span style="float: right;"> <a href="#" style="display: none" class="awst_review_edit" data-item-id="'.$value->ID.'" >Save Changes</a> <a href="#" class="awst_review_edit_show" data-item-id="'.$value->ID.'">Edit</a> &nbsp;|&nbsp; <a href="#" style="color: #FF0000" class="awst_review_delete" data-item-id="'.$value->ID.'">Delete</a></span>';
                                        }
                    $html .=            '<div class="clear"></div>
                                    <p><span class="edit_container" style="display: none"><textarea class="edit_review_box_'.$value->ID.'">'.$value->post_content.'</textarea></span><br /></p>
                                    </div>
                                    <div class="review-detail">
                                        <span class="review-author"><i class="fa fa-user" aria-hidden="true"></i>'.$userdetail->data->user_login.'</span>
                                        <span class="review-date"><i class="fa fa-calendar" aria-hidden="true"></i>'.date("d F Y", strtotime($value->post_date)).'</span>
                                    </div>
                                </li>';
                }

                $html .= '</div>';
            $html .= '</ul>';

            if( (is_user_logged_in() && $is_course_enrolled) ){
                $review = '<div class="awst_review">
                                <h2>'.get_the_title().' Reviews</h2>
                                '.$html.'
                                <textarea id="review_'.$post->ID.'" name="review" placeholder="Leave a review..."></textarea>
                                <span data-post-id="'.$post->ID.'" class="awst_rate_btn_review">Add Review<i class="fa fa-comment" aria-hidden="true"></i></span>
                            </div>';
            }else{
                $review = '<div class="awst_review">
                                <h2>'.get_the_title().' Reviews</h2>
                                '.$html.'
                            </div>';
            }

        }

        $messageBlock = '<div class="err_msg"><div id="awMessageBlock"></div></div>';
        $clearfix = '<div style="clear:both;"></div>';

        if( $flag && $post->ID > 0){
            return $content." <div class='awst_block'>".$messageBlock." ".$like." ".$rate." ".$review." ".$clearfix."</div>" ;
        }else{
            return $content;
        }
    }


    /* function to show functionality on the comment seciton */
    function comment_text_filter( $comment_text ){

        $post_id   = get_comment_ID();
        $type = 'comment';

        $post_like      =   $type.'_like';
        $post_rate      =   $type.'_rate';
        $post_review    =   $type.'_review';

        $like   = false;
        $rate   = false;
        $review = false;

        $seletedOptions = get_option('awSocialTabsPostOptions', true);

        if( in_array($post_like, $seletedOptions )){

            $flag = true;

            $user_ID     = get_current_user_id();
            $postmeta    = get_post_meta($post_id, 'awst_like', true);
            $isLiked     = AwstComman::isLiked($postmeta, $user_ID);
            $totalLiked  = AwstComman::getLikes($postmeta);

            if( $isLiked ){
                $like = '<div class="awst_like"><span class="awst_like_btn" id="awst_like_btn_'.$post_id.'"><i data-post-like="true" data-post-id="'.$post_id.'" class="fa fa-thumbs-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_'.$post_id.'">'.$totalLiked.'</label> Me gusta</span></div>';
            }else{
                $like = '<div class="awst_like"><span class="awst_like_btn" id="awst_like_btn_'.$post_id.'"><i data-post-like="true" data-post-id="'.$post_id.'" class="fa fa-thumbs-o-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_'.$post_id.'">'.$totalLiked.'</label> Me gusta</span></div>';
            }

        }

        if( in_array($post_rate, $seletedOptions )){

            $flag     = true;

            $user_ID  = get_current_user_id();

            $postdata = get_post_meta($post_id, 'awst_ratings', true);

            $ratings  = AwstComman::getRatings($postdata);

            $useRated = $postdata[$user_ID];

            $rate     = '<div class="awst_rate">';
                for ($i = 1; $i <= 5; $i++) {
                    if( $i <= $useRated ){
                        $rate .= '<span id="star'.$i.'" class="awst_rate_btn"><i class="fa fa-star" aria-hidden="true" data-post-id="'.$post_id.'" data-rate-id="'.$i.'" ></i></span>';
                    }else{
                        $rate .= '<span id="star'.$i.'" class="awst_rate_btn"><i class="fa fa-star-o" aria-hidden="true" data-post-id="'.$post_id.'" data-rate-id="'.$i.'" ></i></span>';
                    }
                }
            $rate .= '&nbsp;<span class="rating-text">Valuación media: &nbsp;</span> <span class="rating-text" id="average_rating">'.$ratings.'</span>';
            $rate .= '</div>';
        }

        if( in_array($post_review, $seletedOptions )){

            $flag     = true;

            $reviews  = AwstComman::getReviews( $post_id );

            $html     = '<div id="review-list">';
            $html    .= '<ul>';

                foreach ($reviews as $key => $value) {

                    $userdetail = get_user_by( 'ID', $value->post_author );

                    $html .= '<li><div class="review-content"><i class="fa fa-comment" aria-hidden="true"></i>'.$value->post_content.'</div><div class="review-detail"><span class="review-author"><i class="fa fa-user" aria-hidden="true"></i>'.$userdetail->data->user_login.'</span><span class="review-date"><i class="fa fa-calendar" aria-hidden="true"></i>'.date("d F Y", strtotime($value->post_date)).'</span></div></li>';
                }

            $html .=   '</div>';
            $html .= '</ul>';

            $review = '<div class="awst_review">
            <h2>'.get_the_title().' Reviews</h2>
            '.$html.'
            <textarea id="review_'.$post_id.'" name="review">Leave a Review.....</textarea>
            <span data-post-id="'.$post_id.'" class="awst_rate_btn_review">Add Review<i class="fa fa-comment" aria-hidden="true"></i></span>
            </div>';
        }

        $messageBlock = '<div class="err_msg"><div id="awMessageBlock"></div></div>';
        $clearfix = '<div style="clear:both;"></div>';

        if( $flag ){
            return $comment_text." <div class='awst_block'>".$messageBlock." ".$like." ".$rate." ".$review." ".$clearfix."</div>" ;
        }else{
            return $comment_text;
        }

    }


    /*function to show the like/rate/review functionlity for the buddypress activity */
    function activity($content){

        global $activities_template;
        // file_put_contents(dirname(__FILE__)."/activities_template.log", print_r($activities_template, true),FILE_APPEND );


        $id = bp_get_activity_id();

        $activity_get = bp_activity_get_specific( array( 'activity_ids' => $id ) );
        $post_id      = $activity_get['activities'][0]->secondary_item_id;
        $type         = get_post_type($post_id);
        $typeOfBlock  = $activity_get['activities'][0]->type;

        if (((strpos($typeOfBlock, 'comment') !== false) &&  ($type == '')))  {
            $type = 'comment';
        }else if(((strpos($typeOfBlock, 'activity_update') !== false) &&  ($type == 'page'))) {
            $type = 'activity_update';
            $post_id = $id;
        }

        $post_like      =   $type.'_like';
        $post_rate      =   $type.'_rate';
        $post_review    =   $type.'_review';

        $like   = false;
        $rate   = false;
        $review = false;

        $seletedOptions = get_option('awSocialTabsPostOptions', true);

        if( in_array($post_like, $seletedOptions )){

            $flag = true;

            $user_ID     = get_current_user_id();
            $postmeta    = get_post_meta($post_id, 'awst_like', true);
            $isLiked     = AwstComman::isLiked($postmeta, $user_ID);
            $totalLiked  = AwstComman::getLikes($postmeta);

            if( $isLiked ){
                $like = '<div class="awst_like"><span class="awst_like_btn" id="awst_like_btn_'.$post_id.'"><i data-post-like="true" data-post-id="'.$post_id.'" class="fa fa-thumbs-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_'.$post_id.'">'.$totalLiked.'</label> Me gusta</span></div>';
            }else{
                $like = '<div class="awst_like"><span class="awst_like_btn" id="awst_like_btn_'.$post_id.'"><i data-post-like="true" data-post-id="'.$post_id.'" class="fa fa-thumbs-o-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_'.$post_id.'">'.$totalLiked.'</label> Me gusta</span></div>';
            }

            print_r($like);

        }

        if( in_array($post_rate, $seletedOptions )){

            $flag     = true;

            $user_ID  = get_current_user_id();

            $postdata = get_post_meta($post_id, 'awst_ratings', true);

            $ratings  = AwstComman::getRatings($postdata);

            $useRated = $postdata[$user_ID];

            $rate     = '<div class="awst_rate">';
                for ($i = 1; $i <= 5; $i++) {
                    if( $i <= $useRated ){
                        $rate .= '<span id="star'.$i.'" class="awst_rate_btn"><i class="fa fa-star" aria-hidden="true" data-post-id="'.$post_id.'" data-rate-id="'.$i.'" ></i></span>';
                    }else{
                        $rate .= '<span id="star'.$i.'" class="awst_rate_btn"><i class="fa fa-star-o" aria-hidden="true" data-post-id="'.$post_id.'" data-rate-id="'.$i.'" ></i></span>';
                    }
                }
            $rate .= '&nbsp;<span class="rating-text">Valuación media: &nbsp;</span> <span class="rating-text" id="average_rating">'.$ratings.'</span>';
            $rate .= '</div>';
            print_r($rate);

        }

        if( in_array($post_review, $seletedOptions )){

            $flag     = true;

            $reviews  = AwstComman::getReviews( $post_id );

            $html     = '<div id="review-list">';
            $html    .= '<ul>';

                foreach ($reviews as $key => $value) {

                    $userdetail = get_user_by( 'ID', $value->post_author );

                    $html .= '<li><div class="review-content"><i class="fa fa-comment" aria-hidden="true"></i>'.$value->post_content.'</div><div class="review-detail"><span class="review-author"><i class="fa fa-user" aria-hidden="true"></i>'.$userdetail->data->user_login.'</span><span class="review-date"><i class="fa fa-calendar" aria-hidden="true"></i>'.date("d F Y", strtotime($value->post_date)).'</span></div></li>';
                }

            $html .=   '</div>';
            $html .= '</ul>';

            $review = '<div class="awst_review">
            <h2>'.get_the_title().' Reviews</h2>
            '.$html.'
            <textarea id="review_'.$post_id.'" name="review">Leave a Review.....</textarea>
            <span data-post-id="'.$post_id.'" class="awst_rate_btn_review">Add Review<i class="fa fa-comment" aria-hidden="true"></i></span>
            </div>';
            print_r($review);
        }

        $messageBlock = '<div class="err_msg"><div id="awMessageBlock"></div></div>';
        $clearfix = '<div style="clear:both;"></div>';

        if( $flag ){
            return $content." <div class='awst_block'>".$messageBlock." ".$like." ".$rate." ".$review." ".$clearfix."</div>" ;
        }else{
            return $content;
        }
    }


    /* function to show the like/rate/review functionlity for the mediapress */
    function filtermediapress( $content ) {

        $type = get_post_type(get_the_ID());

        $post_like      =   $type.'_like';
        $post_rate      =   $type.'_rate';
        $post_review    =   $type.'_review';

        $like   = false;
        $rate   = false;
        $review = false;

        $seletedOptions = get_option('awSocialTabsPostOptions', true);

        if( in_array($post_like, $seletedOptions )){

            $flag = true;

            $user_ID     = get_current_user_id();
            $postmeta    = get_post_meta(get_the_ID(), 'awst_like', true);
            $isLiked     = AwstComman::isLiked($postmeta, $user_ID);
            $totalLiked  = AwstComman::getLikes($postmeta);

            if( $isLiked ){
                $like = '<div class="awst_like"><span class="awst_like_btn" id="awst_like_btn_'.get_the_ID().'"><i data-post-like="true" data-post-id="'.get_the_ID().'" class="fa fa-thumbs-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_'.get_the_ID().'">'.$totalLiked.'</label> Me gusta</span></div>';
            }else{
                $like = '<div class="awst_like"><span class="awst_like_btn" id="awst_like_btn_'.get_the_ID().'"><i data-post-like="true" data-post-id="'.get_the_ID().'" class="fa fa-thumbs-o-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_'.get_the_ID().'">'.$totalLiked.'</label> Me gusta</span></div>';
            }

            print_r($like);

        }

        if( in_array($post_rate, $seletedOptions )){

            $flag = true;

            $user_ID  = get_current_user_id();

            $postdata = get_post_meta(get_the_ID(), 'awst_ratings', true);

            $ratings  = AwstComman::getRatings($postdata);
            $useRated = $postdata[$user_ID];

            $rate = '<div class="awst_rate">';
                for ($i = 1; $i <= 5; $i++) {
                    if( $i <= $useRated ){
                        $rate .= '<span id="star'.$i.'" class="awst_rate_btn"><i class="fa fa-star" aria-hidden="true" data-post-id="'.get_the_ID().'" data-rate-id="'.$i.'" ></i></span>';
                    }else{
                        $rate .= '<span id="star'.$i.'" class="awst_rate_btn"><i class="fa fa-star-o" aria-hidden="true" data-post-id="'.get_the_ID().'" data-rate-id="'.$i.'" ></i></span>';
                    }
                }
            $rate .= '&nbsp;<span class="rating-text">Valuación media: &nbsp;</span> <span class="rating-text" id="average_rating">'.$ratings.'</span>';
            $rate .= '</div>';

        }

        if( in_array($post_review, $seletedOptions )){
            $flag = true;

            $reviews  = AwstComman::getReviews( get_the_ID() );

            $html = '<div id="review-list">';
                $html .= '<ul>';

                foreach ($reviews as $key => $value) {
                    # code...
                    $userdetail = get_user_by( 'ID', $value->post_author );
                    $html .= '<li><div class="review-content"><i class="fa fa-comment" aria-hidden="true"></i>'.$value->post_content.'</div><div class="review-detail"><span class="review-author"><i class="fa fa-user" aria-hidden="true"></i>'.$userdetail->data->user_login.'</span><span class="review-date"><i class="fa fa-calendar" aria-hidden="true"></i>'.date("d F Y", strtotime($value->post_date)).'</span></div></li>';

                }

                $html .= '</div>';
            $html .= '</ul>';

            $review = '<div class="awst_review">
            <h2>'.get_the_title().' Reviews</h2>
            '.$html.'
            <textarea id="review_'.get_the_ID().'" name="review">Leave a Review.....</textarea>
            <span data-post-id="'.get_the_ID().'" class="awst_rate_btn_review">Add Review<i class="fa fa-comment" aria-hidden="true"></i></span>
            </div>';
        }

        $messageBlock = '<div class="err_msg"><div id="awMessageBlock"></div></div>';
        $clearfix = '<div style="clear:both;"></div>';

        if( $flag ){
            return $content." <div class='awst_block'>".$messageBlock." ".$like." ".$rate." ".$review." ".$clearfix."</div>" ;
        }else{
            return $content;
        }

    }


} /* class ends here*/

?>
