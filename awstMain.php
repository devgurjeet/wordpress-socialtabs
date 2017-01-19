<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AwSocialTabs {

    //** Constructor **//
    function __construct() {

        add_action( 'init', array( "AwstAdminPages", "create_custom_comments" ) );

        //** Action to load Assets Css **//
        add_action( 'wp_enqueue_scripts',  array(&$this, 'loadAssectCss') );

        add_action( 'admin_enqueue_scripts',  array(&$this, 'loadAdminAssects') );

        //** Register menu. **//
        add_action('admin_menu', array(&$this, 'register_plugin_menu') );

        /* ajax call to update settings */
        add_action('wp_ajax_awst_settings_ajax', array('AwstAjax', 'awst_settings_ajax'));

        /*ajax call for like functionality*/
        add_action('wp_ajax_awst_ajax_like', array('AwstAjax', 'awst_ajax_like'));
        add_action('wp_ajax_nopriv_awst_ajax_like', array('AwstAjax', 'awst_ajax_like'));

        /*ajax call for rating functionality*/
        add_action('wp_ajax_awst_ajax_rating', array('AwstAjax', 'awst_ajax_rating'));
        add_action('wp_ajax_nopriv_awst_ajax_rating', array('AwstAjax', 'awst_ajax_rating'));

        /*ajax call to delete rating*/
        add_action('wp_ajax_awst_ajax_review_delete', array('AwstAjax', 'awst_ajax_review_delete'));
        add_action('wp_ajax_nopriv_awst_ajax_review_delete', array('AwstAjax', 'awst_ajax_review_delete'));

        /*ajax call to edit rating*/
        add_action('wp_ajax_awst_ajax_review_edit', array('AwstAjax', 'awst_ajax_review_edit'));
        add_action('wp_ajax_nopriv_awst_ajax_review_edit', array('AwstAjax', 'awst_ajax_review_edit'));


        /*review functionality*/
        add_action('wp_ajax_awst_ajax_review', array('AwstAjax', 'awst_ajax_review'));

        add_filter('the_content', array('AwstFrontPages', 'content_filter'),20);

        add_filter('bbp_get_reply_content', array('AwstFrontPages', 'content_filter'),20,1);

        add_filter('bbp_get_topic_content', array('AwstFrontPages', 'content_filter'),20,1);

        add_filter('bbp_get_forum_content', array('AwstFrontPages', 'content_filter'),20,1);

        add_action( 'bp_get_activity_content' , array('AwstFrontPages', 'content_filter'),20,1);

        add_action( 'bp_member_activity_filter_options' , array('AwstFrontPages', 'content_filter') );

        add_action( 'bp_group_activity_filter_options' , array('AwstFrontPages', 'content_filter') );

        add_action( 'mpp_media_meta' , array( 'AwstFrontPages', 'filtermediapress'));

        add_action( 'mpp_gallery_meta' , array( 'AwstFrontPages', 'filtermediapress'));

        //add_filter( 'bp_activity_excerpt_append_text', array('AwstFrontPages', 'activity'), 10);
        add_filter( 'comment_text', array(&$this, 'testCustomData'), 10);

        // add_action('wp_footer', array(&$this, 'wp_footer_action'));
        // add_action('admin_init', array(&$this, 'checkHeaders'));

        add_action( 'bp_activity_entry_meta', array( $this, 'display_favorite_count' ) );
       // add_action( 'bp_activity_entry_meta', array( $this, 'display_thumbs' ) );

        //add_action( 'bp_activity_comment_options',  array(&$this,"display_favorite_count"),10 );
        add_action( 'bp_activity_comment_options',  array(&$this,"commentFilter"),10 );

        add_action( 'wp_head',  array(&$this,"add_site_url"),10 );

    }

    function add_site_url() {
        $site_url = site_url();
        echo '<meta key="awst_site_url" value="'.$site_url.'"/>';
    }

    function commentFilter(){
        global $activities_template;

        $fav_count              =   !empty( $activities_template->activity->favorite_count ) ? $activities_template->activity->favorite_count : 0;
        $id                     =   bp_get_activity_id();
        $activity_get           =   bp_activity_get_specific( array( 'activity_ids' => $id ) );
        $post_id                =   $activity_get['activities'][0]->secondary_item_id;
        $type                   =   get_post_type($post_id);
        $typeOfBlock            =   $activity_get['activities'][0]->type;

        if (((strpos($typeOfBlock, 'comment') !== false) &&  ($type == '')))  {

            $type       =   'comment';
        }else if(((strpos($typeOfBlock, 'activity_update') !== false) &&  ($type == 'page'))) {

            $type       =   'activity_update';
            $post_id    =   bp_get_activity_comment_id();

        }else if(((strpos($typeOfBlock, 'activity_update') !== false) &&  ($type == 'post'))) {

            $type       =   'activity_update';
            $post_id    =   bp_get_activity_comment_id();

        }

        $post_like      =   $type.'_like';
        $post_rate      =   $type.'_rate';
        $post_review    =   $type.'_review';

        $like           =   false;
        $rate           =   false;
        $review         =   false;

        $seletedOptions =   get_option('awSocialTabsPostOptions', true);

        if( in_array($post_like, $seletedOptions )){

            $flag       =   true;
            $user_ID    =   get_current_user_id();
            $postmeta   =   get_post_meta($post_id, 'awst_like', true);
            $isLiked    =   AwstComman::isLiked($postmeta, $user_ID);
            $totalLiked =   AwstComman::getLikes($postmeta);

            $fav_count  =   10;

            if( $post_id !== 0 ):
                if( ((!empty( $fav_count ) ) && $isLiked) ):?>
                    <a class="awst_like"><?php printf( _n( 'AWST LIKE', '<span class="awst_like_btn" id="awst_like_btn_%d"><i data-post-like="true" data-post-id="%d" class="fa fa-thumbs-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_%d">%d</label> Me gusta</span>', $post_id,$post_id,$post_id,$totalLiked ), $post_id,$post_id,$post_id,$totalLiked  );?></a>
                <?php else:?>
                    <a class="awst_like"><?php printf( _n( 'AWST LIKE', '<span class="awst_like_btn" id="awst_like_btn_%d"><i data-post-like="true" data-post-id="%d" class="fa fa-thumbs-o-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_%d">%d</label> Me gusta</span>', $post_id,$post_id,$post_id,$totalLiked ), $post_id,$post_id,$post_id,$totalLiked  );?></a>
                <?php endif;
            endif;
        }




    }

    function display_thumbs() {

        global $activities_template;

        $fav_count  =   !empty( $activities_template->activity->favorite_count ) ? $activities_template->activity->favorite_count : 0;
        $id         =   bp_get_activity_id();
        $post_id    =   $id;
        $totalLiked =   0;

        if((($_REQUEST['action'] === 'post_update' ) && ($post_id !== 0))){?>
            <a class="awst_like"><?php printf( _n( 'AWST LIKE', '<span class="awst_like_btn" id="awst_like_btn_%d"><i data-post-like="true" data-post-id="%d" class="fa fa-thumbs-o-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_%d">%d</label> Me gusta</span>', $post_id,$post_id,$post_id,$totalLiked ), $post_id,$post_id,$post_id,$totalLiked  );?></a>
        <?php
        }

    }

    function display_favorite_count() {

        if (!is_user_logged_in()) {
            echo "<style>.awst_like{ display:none !important;}</style>";
        }
        global $activities_template;

        $fav_count              =   !empty( $activities_template->activity->favorite_count ) ? $activities_template->activity->favorite_count : 0;
        $id                     =   bp_get_activity_id();
        $activity_get           =   bp_activity_get_specific( array( 'activity_ids' => $id ) );
        $post_id                =   $activity_get['activities'][0]->secondary_item_id;
        $type                   =   get_post_type($post_id);
        $typeOfBlock            =   $activity_get['activities'][0]->type;
        //bp_activity_comment_id();

        /*file_put_contents(dirname(__FILE__).'/activity_get.log', print_r($activity_get,true),FILE_APPEND);
        file_put_contents(dirname(__FILE__).'/activity_get.log', print_r("\n",true),FILE_APPEND);

        file_put_contents(dirname(__FILE__).'/type.log', print_r($type,true),FILE_APPEND);
        file_put_contents(dirname(__FILE__).'/type.log', print_r("\n",true),FILE_APPEND);


        file_put_contents(dirname(__FILE__).'/typeOfBlock.log', print_r($typeOfBlock,true),FILE_APPEND);
        file_put_contents(dirname(__FILE__).'/typeOfBlock.log', print_r("\n",true),FILE_APPEND);


        file_put_contents(dirname(__FILE__).'/bp_activity_comment_id.log', print_r(bp_get_activity_comment_id(),true),FILE_APPEND);
        file_put_contents(dirname(__FILE__).'/bp_activity_comment_id.log', print_r("\n",true),FILE_APPEND);
*/

        if (((strpos($typeOfBlock, 'comment') !== false) &&  ($type == '')))  {

            $type       =   'comment';
        }else if(((strpos($typeOfBlock, 'activity_update') !== false) &&  ($type == 'page'))) {

            $type       =   'activity_update';
            $post_id    =   $id;

        }else if(((strpos($typeOfBlock, 'activity_update') !== false) &&  ($type == ''))) {

            $type       =   'activity_update';
            $post_id    =   $id;

        }else if(((strpos($typeOfBlock, 'activity_update') !== false) &&  ($type == 'attachment'))) {

            $type       =   'activity_update';
            $post_id    =   $id;

        }else if(((strpos($typeOfBlock, 'activity_update') !== false) &&  ($type == 'post'))) {

            $act_id     =   bp_get_activity_comment_id();
            $type       =   'activity_update';
            $post_id    =   $act_id;

        }else if(((strpos($typeOfBlock, 'mpp_media_upload') !== false) &&  ($type == 'page'))) {

            $type       =   'activity';
            $post_id    =   $id;

        }else if(((strpos($typeOfBlock, 'mpp_media_upload') !== false) &&  ($type == 'attachment'))) {

            $type       =   'activity';
            $post_id    =   $id;

        }else if(((strpos($typeOfBlock, 'new_blog_comment') !== false) &&  ($type == ''))) {

            $type       =   'activity';
            $post_id    =   $id;
        }

        $post_like      =   $type.'_like';
        $post_rate      =   $type.'_rate';
        $post_review    =   $type.'_review';

        $like           =   false;
        $rate           =   false;
        $review         =   false;

        $seletedOptions =   get_option('awSocialTabsPostOptions', true);

        if( in_array($post_like, $seletedOptions )){

            $flag       =   true;
            $user_ID    =   get_current_user_id();
            $postmeta   =   get_post_meta($post_id, 'awst_like', true);
            $isLiked    =   AwstComman::isLiked($postmeta, $user_ID);
            $totalLiked =   AwstComman::getLikes($postmeta);

            $fav_count  =   10;

            if( $post_id !== 0 ):
                if( ((!empty( $fav_count ) ) && $isLiked) ):?>
                    <a class="awst_like"><?php printf( _n( 'AWST LIKE', '<span class="awst_like_btn" id="awst_like_btn_%d"><i data-post-like="true" data-post-id="%d" class="fa fa-thumbs-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_%d">%d</label> Me gusta</span>', $post_id,$post_id,$post_id,$totalLiked ), $post_id,$post_id,$post_id,$totalLiked  );?></a>
                <?php else:?>
                    <a class="awst_like"><?php printf( _n( 'AWST LIKE', '<span class="awst_like_btn" id="awst_like_btn_%d"><i data-post-like="true" data-post-id="%d" class="fa fa-thumbs-o-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_%d">%d</label> Me gusta</span>', $post_id,$post_id,$post_id,$totalLiked ), $post_id,$post_id,$post_id,$totalLiked  );?></a>
                <?php endif;
            endif;
        }

        if( in_array($post_rate, $seletedOptions )){

            $flag       =    true;

            $user_ID    =    get_current_user_id();

            $postdata   =    get_post_meta($post_id, 'awst_ratings', true);

            $ratings    =    AwstComman::getRatings($postdata);

            $useRated   =    $postdata[$user_ID];

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

            $flag       =    true;

            $reviews    =    AwstComman::getReviews( $post_id );

            $html       =    '<div id="review-list">';
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
            $dataItem =  $content." <div class='awst_block'>".$messageBlock." ".$like." ".$rate." ".$review." ".$clearfix."</div>" ;
        }else{
            return $content;
        }
        ?>
       <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script type="text/javascript">
            jQuery(document).ready(function(){

                var str =jQuery(".activity-comments").find("li").eq(0).attr("id");
                var likeid = str.replace("acomment-",'');
                var span_likeid = "awst_like_btn_"+likeid;
                jQuery(".activity-comments").find("li").eq(0).find(".awst_like").find("span").eq(0).attr("id",span_likeid);
                jQuery(".activity-comments").find("li").eq(0).find(".awst_like").find("span").eq(0).find("i").attr("data-post-id",likeid);
                var class_likeid = "total_likes_"+likeid;
                jQuery(".activity-comments").find("li").eq(0).find(".awst_like").find(".total_like").find("label").attr("class",class_likeid);

            });
        </script> -->
        <?php
    }

    function testCustomData( $comment_text ){

        $post_id        =   get_comment_ID();
        $type           =   'comment';
        $post_like      =   $type.'_like';
        $post_rate      =   $type.'_rate';
        $post_review    =   $type.'_review';

        $like           =   false;
        $rate           =   false;
        $review         =   false;

        $seletedOptions =   get_option('awSocialTabsPostOptions', true);

        if( in_array($post_like, $seletedOptions )){

            $flag = true;

            $user_ID    =   get_current_user_id();
            $postmeta   =   get_post_meta($post_id, 'awst_like', true);
            $isLiked    =   AwstComman::isLiked($postmeta, $user_ID);
            $totalLiked =   AwstComman::getLikes($postmeta);

            if( $isLiked ){
                $like = '<div class="awst_like"><span class="awst_like_btn" id="awst_like_btn_'.$post_id.'"><i data-post-like="true" data-post-id="'.$post_id.'" class="fa fa-thumbs-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_'.$post_id.'">'.$totalLiked.'</label> Me gusta</span></div>';
            }else{
                $like = '<div class="awst_like"><span class="awst_like_btn" id="awst_like_btn_'.$post_id.'"><i data-post-like="true" data-post-id="'.$post_id.'" class="fa fa-thumbs-o-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_'.$post_id.'">'.$totalLiked.'</label> Me gusta</span></div>';
            }

        }

        if( in_array($post_rate, $seletedOptions )){

            $flag       =   true;

            $user_ID    =   get_current_user_id();

            $postdata   =   get_post_meta($post_id, 'awst_ratings', true);

            $ratings    =   AwstComman::getRatings($postdata);

            $useRated   =   $postdata[$user_ID];

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

            $flag     =     true;
            $reviews  =     AwstComman::getReviews( $post_id );

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

    function loadAssectCss(){
        $plugin_url    =   plugin_dir_url( __FILE__ );

        //** Load  Styling. **//
        wp_enqueue_style( 'AwSocialTabs_style', $plugin_url . 'css/awst_style.css' );
        wp_enqueue_style('AwSocialTabs-font-awesome','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css');

        /*load frontend script. */
        wp_enqueue_script( 'awst_custom_script', plugin_dir_url( __FILE__ ) . '/js/awst_custom_script.js', array('jquery'), '1.0.0' );
    }

    function loadAdminAssects( $hook ){
        //** Load  Styling. **//
        $plugin_url = plugin_dir_url( __FILE__ );
        wp_enqueue_style( 'awsocialtabs_style', $plugin_url . 'css/awst_admin_style.css' );
        wp_enqueue_style('awsocialtabs_style_font_awesome','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css');
        /*load admin script. */
        wp_enqueue_script( 'awst_admin_custom_script', plugin_dir_url( __FILE__ ) . '/js/awst_admin_custom_script.js', array('jquery'), '1.0.0' );
    }

    //** Register menu Item. **//
    function register_plugin_menu(){
            add_menu_page( 'Aw Social Tabs', 'Aw Social Tabs', 'manage_options', 'awsocialtabs', array('AwstAdminPages', 'plugin_homepage'), 'dashicons-share', 6 );
            add_submenu_page('awsocialtabs', 'Aw Social Tabs | settings', 'Settings', 'manage_options','awst_settings', array('AwstAdminPages', 'awst_settings'));
            add_submenu_page('', 'Aw Social Tabs | Likes', 'Likes', 'manage_options','awst_likes', array('AwstAdminPages', 'awst_likes'));
            add_submenu_page('', 'Aw Social Tabs | Ratings', 'Ratings', 'manage_options','awst_ratings', array('AwstAdminPages', 'awst_ratings'));
            add_submenu_page('', 'Aw Social Tabs | Review', 'Review', 'manage_options','awst_review', array('AwstAdminPages', 'awst_review'));
    }


    function testComment(){
        print_r('<h1>Test</h1>');
    }

    function activity($content){

        global $activities_template;

        $id             =   bp_get_activity_id();
        $activity_get   =   bp_activity_get_specific( array( 'activity_ids' => $id ));
        $post_id        =   $activity_get['activities'][0]->secondary_item_id;
        $type           =   get_post_type($post_id);
        $typeOfBlock    =   $activity_get['activities'][0]->type;

        $activity_get   =   bp_activity_get_specific( array( 'activity_ids' => $id ) );

        if (((strpos($typeOfBlock, 'comment') !== false) &&  ($type == '')))  {
           $type        =   'comment';
        }

        $post_like      =   $type.'_like';
        $post_rate      =   $type.'_rate';
        $post_review    =   $type.'_review';

        $like           =   false;
        $rate           =   false;
        $review         =   false;

        $seletedOptions =   get_option('awSocialTabsPostOptions', true);

        if( in_array($post_like, $seletedOptions )){

            $flag       =   true;
            $user_ID    =   get_current_user_id();
            $postmeta   =   get_post_meta($post_id, 'awst_like', true);
            $isLiked    =   AwstComman::isLiked($postmeta, $user_ID);
            $totalLiked =   AwstComman::getLikes($postmeta);

            if( $isLiked ){
                $like = '<div class="awst_like"><span class="awst_like_btn" id="awst_like_btn_'.$post_id.'"><i data-post-like="true" data-post-id="'.$post_id.'" class="fa fa-thumbs-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_'.$post_id.'">'.$totalLiked.'</label> Me gusta</span></div>';
            }else{
                $like = '<div class="awst_like"><span class="awst_like_btn" id="awst_like_btn_'.$post_id.'"><i data-post-like="true" data-post-id="'.$post_id.'" class="fa fa-thumbs-o-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_'.$post_id.'">'.$totalLiked.'</label> Me gusta</span></div>';
            }
            print_r($like);
        }

        if( in_array($post_rate, $seletedOptions )){

            $flag       =    true;

            $user_ID    =    get_current_user_id();

            $postdata   =    get_post_meta($post_id, 'awst_ratings', true);

            $ratings    =    AwstComman::getRatings($postdata);

            $useRated   =    $postdata[$user_ID];

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

            $flag       =   true;

            $reviews    =   AwstComman::getReviews( $post_id );

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

    function filtermediapress( $content ){

        $type           =   get_post_type(get_the_ID());
        $post_like      =   $type.'_like';
        $post_rate      =   $type.'_rate';
        $post_review    =   $type.'_review';

        $like           =   false;
        $rate           =   false;
        $review         =   false;

        $seletedOptions =   get_option('awSocialTabsPostOptions', true);

        if( in_array($post_like, $seletedOptions )){

            $flag       =   true;

            $user_ID    =   get_current_user_id();
            $postmeta   =   get_post_meta(get_the_ID(), 'awst_like', true);
            $isLiked    =   AwstComman::isLiked($postmeta, $user_ID);
            $totalLiked =   AwstComman::getLikes($postmeta);

            if( $isLiked ){
                $like = '<div class="awst_like"><span class="awst_like_btn" id="awst_like_btn_'.get_the_ID().'"><i data-post-like="true" data-post-id="'.get_the_ID().'" class="fa fa-thumbs-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_'.get_the_ID().'">'.$totalLiked.'</label> Me gusta</span></div>';
            }else{
                $like = '<div class="awst_like"><span class="awst_like_btn" id="awst_like_btn_'.get_the_ID().'"><i data-post-like="true" data-post-id="'.get_the_ID().'" class="fa fa-thumbs-o-up" aria-hidden="true"></i></span><span class="total_like"><label id="total_likes" class="total_likes_'.get_the_ID().'">'.$totalLiked.'</label> Me gusta</span></div>';
            }
            print_r($like);
        }

        if( in_array($post_rate, $seletedOptions )){

            $flag     =  true;

            $user_ID  =  get_current_user_id();

            $postdata =  get_post_meta(get_the_ID(), 'awst_ratings', true);

            $ratings  =  AwstComman::getRatings($postdata);
            $useRated =  $postdata[$user_ID];

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
            $flag     =   true;
            $reviews  =   AwstComman::getReviews( get_the_ID() );

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
}/*class ends here*/
?>
