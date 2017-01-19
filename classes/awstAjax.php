<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AwstAjax {

    /* function to reveive ajax call*/
    function awst_settings_ajax(){
        $message = array("success");
        $data    = $_POST['data'];

        update_option( 'awSocialTabsPostOptions', $data, true );

        echo json_encode($message);
        die();
    }

    function awst_ajax_like(){

        $liked = false;

        if ( is_user_logged_in() ) {

            $user_ID   = get_current_user_id();
            $postID    = $_POST['post_id'];

            $likes       = get_user_meta($user_ID, 'awst_like', true);
            $postLikes   = get_post_meta($postID, 'awst_like', true);
            $optionLikes = get_option('awst_like', true);

            if(in_array($user_ID, $postLikes)){
                $postLikes = array_diff($postLikes, array($user_ID));
            }else{
                $postLikes[] =  $user_ID;
                $liked = true;

            }
            $likes[] = $_POST['post_id'];

            update_user_meta($user_ID, 'awst_like', $likes);
            update_post_meta($postID, 'awst_like', $postLikes);


            /*update options*/
            if( is_array($optionLikes) ){
                $optionLikes[$postID] = $postLikes;
            }else{
                $optionLikes = array();
                $optionLikes[$postID] = $postLikes;
            }
            update_option( 'awst_like', $optionLikes, true );

            $postmeta    = get_post_meta($postID, 'awst_like', true);
            $totalLiked  = AwstComman::getLikes($postmeta);

            $message['status']    = "success";
            $message['liked']     = $liked;
            $message['message']   = "Post Liked Successfully.";
            $message['likecount'] = $totalLiked;

        } else {
            $message['status']  = "error";
            $message['message'] = "Please login to Like this post.";
        }

        $postLikes = get_post_meta($postID, 'awst_like', true);

        // print_r( $postLikes );

        echo json_encode($message);
        die();

    }

    /*ajax */
    function awst_ajax_rating(){
        // post_id
        // rate_val

         if ( is_user_logged_in() ) {

            /*get the user if currenlty loggedin user.*/
            $user_ID = get_current_user_id();

            /*post parameters*/
            $postID         =   $_POST['post_id'];
            $rating_value   =   $_POST['rate_val'];

            /*get the old data */
            $ratings       = get_user_meta($user_ID, 'awst_ratings', true);
            $postdata      = get_post_meta($postID, 'awst_ratings', true);
            $optionRatings = get_option('awst_ratings', true);

            /* udpate user meta*/
            $ratings[$postID] = $rating_value;
            update_user_meta($user_ID, 'awst_ratings', $ratings);

            /*update post meta */
            $postdata[$user_ID] = $rating_value;
            update_post_meta($postID, 'awst_ratings', $postdata);

            /*update options*/
            if( is_array($optionRatings) ){
                $optionRatings[$postID] = $postdata;
            }else{
                $optionRatings = array();
                $optionRatings[$postID] = $postdata;
            }


            update_option( 'awst_ratings', $optionRatings, true );

            $message['status']  = "success";
            $message['message'] = "Rate Updated Successfully.";
        } else {
            $message['status']  = "error";
            $message['message'] = "Please login to rate this post.";
        }

        $postdata = get_post_meta($postID, 'awst_ratings', true);
        $rating   = AwstComman::getRatings($postdata);

        $message['rating'] = $rating;
        echo json_encode($message);
        die();
    }

    function awst_ajax_review(){

        if ( is_user_logged_in() ) {

            /*get the user if currenlty loggedin user.*/
            $user_ID = get_current_user_id();
            $current_post = $_POST['post_id'];

            /*check if user has posted review.*/

            $my_query = new WP_Query( array(
                'post_type'   => 'awst_review',
                'post_status' => 'publish',
                'post_parent' => $_POST['post_id'],
                'author'      => $user_ID
            ));

            // Get all the current user posts from the query object
            $posts = $my_query->posts;

            if (!empty($posts)){
                $message['status']  = "error";
                $message['message'] = "you have already submitted review.";
                echo json_encode($message);
                die();
            }

            $ReviewPost = get_post( $_POST['post_id'] );
            $ReviewUser = get_user_by( 'ID', $user_ID );
            $title      = $ReviewPost->post_title.' - '.$ReviewUser->data->user_login;

            $optionReviews = array();
            $optionReviews = get_option('awst_reviews', true);


            // Gather post data.
            $reviewArray = array(
                'post_title'    => $title,
                'post_content'  => $_POST['review'],
                'post_status'   => 'publish',
                'post_type'     => 'awst_review',
                'post_parent'   => $_POST['post_id'],
                'post_author'   => $user_ID,
            );

            // Insert the post into the database.
            $review_id     = wp_insert_post( $reviewArray );
            $NewReviewPost = get_post( $review_id );
            $NewReviewUser = get_user_by( 'ID', $NewReviewPost->post_author );

            /*update options */
            if( is_array($optionReviews) ){
                $optionReviews[$current_post] = $user_ID;
            }else{
                $optionReviews = array();
                $optionReviews[$current_post] = $user_ID;
            }

            update_option( 'awst_reviews', $optionReviews, true );



            $message['user']        = $NewReviewUser->data->user_login;
            $message['review']      = $NewReviewPost->post_content;
            $message['review_date'] = date("d F Y", strtotime($NewReviewPost->post_date));

            $message['review_id'] = $review_id;
            $message['status']    = "success";
            $message['message']   = "Review successfully created.";
        } else {
            $message['status']  = "error";
            $message['message'] = "Please login to review this post.";
        }

        echo json_encode($message);
        die();

    }

    function awst_ajax_review_delete(){

        if ( is_user_logged_in() ) {

            /*get the user if currenlty loggedin user.*/
            $user_ID = get_current_user_id();
            $post_ID = $_POST['post_id'];
            wp_delete_post($post_ID);

            $message['status']  = "success";
            $message['message'] = "Review deleted successfully.";
        } else {
            $message['status']  = "error";
            $message['message'] = "Please login to delete this Review.";
        }

        echo json_encode($message);
        die();
    }

    function awst_ajax_review_edit(){
        if ( is_user_logged_in() ) {

            /*get the user if currenlty loggedin user.*/
            $user_ID = get_current_user_id();

            $post_ID      = $_POST['post_id'];
            $post_content = $_POST['post_content'];
            $my_post = array(
                'ID'           => $post_ID,
                'post_content' => $post_content,
            );

            // Update the post into the database
            wp_update_post( $my_post );

            $review_id     = $review_id;
            $NewReviewPost = get_post( $review_id );
            $NewReviewUser = get_user_by( 'ID', $NewReviewPost->post_author );

            $message['user']        = $NewReviewUser->data->user_login;
            $message['review']      = $NewReviewPost->post_content;
            $message['review_date'] = date("d F Y", strtotime($NewReviewPost->post_date));
            $message['review_id']   = $review_id;

            $message['status']  = "success";
            $message['message'] = "Review updated successfully.";

        } else {
            $message['status']  = "error";
            $message['message'] = "Please login to edit this Review.";
        }
        echo json_encode($message);
        die();
    }

}/* class ends here */

?>