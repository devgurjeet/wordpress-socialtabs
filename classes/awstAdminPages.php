<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include('awstListPage.php');
include('awstUserLikesPage.php');
include('awstUserRatingsPage.php');
include('awstUserReviewPage.php');

class AwstAdminPages {

    function plugin_homepage(){
        $awstListPage = new AwstListPage();
        $awstListPage->prepare_items();
        ?>
            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
                <div class="awst_header">
                    <h2 class="aw_awst_header_h2">Awst Post List</h2>
                    <div class="clear"></div>
                </div>
                <?php $awstListPage->display(); ?>
            </div>
        <?php
    }


    /*function to show the page. */
    function awst_settings(){

        $seletedOptions     =   get_option('awSocialTabsPostOptions', true);

        $allowedPostTypes   = AwstConfig::getAllowedPostTypes();

        $args = array(
           'public'   => true,
        );

        $output = 'names'; // names or objects, note names is the default
        $operator = 'and'; // 'and' or 'or'

        $post_types = get_post_types( $args, $output, $operator );
        $post_types[] = 'activity';
        $post_types[] = 'comment';
        $post_types[] = 'activity_update';



        echo  '<h1 class="awst_heading">Aw Social Tabs Settings</h1>';

        echo '<table class="awst_table">';
            echo '<thead class="awst_thead">';
                echo '<tr>';
                    echo '<th class="awst_th">';
                        echo "<h2>Post Type</h2>";
                    echo '</th>';
                    echo '<th colspan="3" class="awst_th3">';
                        echo "<h2>Capabilities</h2>";
                    echo '</th>';
                echo '</tr>';
            echo '</thead>';

            echo '<tbody>';
            foreach ( $post_types  as $post_type ) {

                // if( !($allowedPostTypes[$post_type]) ){
                //     continue;
                // }

                $post_like   = $post_type.'_like';
                $post_rate   = $post_type.'_rate';
                $post_review = $post_type.'_review';

                echo '<tr>';
                    echo '<td class="awst_td">';
                        echo '<h3>' . $post_type . '</h3>';
                    echo '</td>';
                    echo '<td>';
                        echo "<h3>";
                            echo "Like";
                            echo "&nbsp;";
                            echo '<input class="element" type="checkbox" '.(in_array("$post_like", $seletedOptions) ? "checked" : "").' name="'.$post_like.'" value="'.$post_like.'" />';
                        echo "</h3>";
                    echo '</td>';
                    echo '<td>';
                        echo "<h3>";
                            echo "Rate";
                            echo "&nbsp;";
                            echo '<input class="element"  type="checkbox" '.(in_array($post_rate, $seletedOptions) ? "checked" : "").' name="'.$post_rate.'" value="'.$post_rate.'" />';
                        echo "</h3>";
                    echo '</td>';
                    echo '<td>';
                        echo "<h3>";
                            echo "Review";
                            echo "&nbsp;";
                            echo '<input class="element" type="checkbox" '.(in_array($post_review, $seletedOptions) ? "checked" : "").' name="'.$post_review.'" value="'.$post_review.'" />';
                        echo "</h3>";
                    echo '</td>';
                echo '</tr>';
            }

                echo '<tr>';
                    echo '<td colspan="4" class="awst_td4">';
                        echo '<p id="awSuccess">Successfully Updated</p>';
                        echo '<h3><input id="save_setting_btn" class="button button-primary awst_btn" type="submit" value="Save Changes" /></h3>';
                    echo '</td>';
                echo '</tr>';

            echo '</tbody>';

        echo '</table>';

    }


    /* function to get the users that liked posts. */
    public function awst_likes() {
        $postID  = $_GET['id'];
        $title   = get_the_title($postID);

        $postmeta    = get_post_meta($postID, 'awst_like', true);
        $totalLiked  = AwstComman::getLikes($postmeta);

        $backUrl = AwstComman::getAdminUrl('awsocialtabs');

        $awstUserLikesPage = new AwstUserLikesPage();
        $awstUserLikesPage->prepare_items();
        ?>
            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
                <div class="awst_header">
                    <h2 class="aw_awst_header_h2">Awst user Likes</h2>
                    <h3 class="aw_awst_header_h3"><a href="<?php echo $backUrl;?>">Back to list</a></h3>

                    <h2 class="aw_awst_header_h2_title"><?php echo $title; ?></h2>
                    <h3 class="aw_awst_header_h3"><span>Total Likes: <?php echo $totalLiked; ?></span></h3>
                    <div class="clear"></div>
                </div>

                <?php $awstUserLikesPage->display(); ?>
            </div>
        <?php
    }

    /* function to get the list of the post's rating user lists*/
    public function awst_ratings() {
        $postID   = $_GET['id'];
        $title    = get_the_title($postID);

        $postdata = get_post_meta($postID, 'awst_ratings', true);
        $rating   = AwstComman::getStars($postID);

        $backUrl  = AwstComman::getAdminUrl('awsocialtabs');

        $awstUserRatingsPage = new AwstUserRatingsPage();
        $awstUserRatingsPage->prepare_items();
        ?>
            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
                <div class="awst_header">
                    <h2 class="aw_awst_header_h2">Awst User Ratings</h2>
                    <h3 class="aw_awst_header_h3"><a href="<?php echo $backUrl;?>">Back to list</a></h3>

                    <h2 class="aw_awst_header_h2_title"><?php echo $title; ?></h2>
                    <h3 class="aw_awst_header_h3"><?php echo $rating;?></h3>
                    <div class="clear"></div>
                </div>

                <?php $awstUserRatingsPage->display(); ?>
            </div>
        <?php
    }

    public function awst_review() {
        $postID   = $_GET['id'];
        $get_post = get_post($postID);
        $post_author = $get_post->post_author;

        $title    = get_the_title($postID);

        //$postdata = get_post_meta($postID, 'awst_review', true);
        $review   = AwstComman::getUserreviews($post_author);

        $backUrl  = AwstComman::getAdminUrl('awsocialtabs');

        $awstUserReviewPage = new AwstUserReviewPage();
        $awstUserReviewPage->prepare_items();
        ?>
            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
                <div class="awst_header">
                    <h2 class="aw_awst_header_h2">Awst User Review</h2>
                    <h3 class="aw_awst_header_h3"><a href="<?php echo $backUrl;?>">Back to list</a></h3>

                    <h2 class="aw_awst_header_h2_title"><?php echo $title; ?></h2>
                    <h3 class="aw_awst_header_h3"><?php //print_r($review); ?></h3>
                    <div class="clear"></div>
                </div>

                <?php $awstUserReviewPage->display(); ?>
            </div>
        <?php
    }

    function create_custom_comments() {
       register_post_type( 'awst_review',
        array(
          'labels'          => array(
            'name'          => __( 'Reviews' ),
            'singular_name' => __( 'Review' ),
            'add_new_item'  => __( 'Add New Review' ),
            'new_item'      => __( 'New Review' ),
            'edit_item'     => __( 'Edit Review' ),
            'view_item'     => __( 'View Review' ),
            'all_items'     => __( 'All Reviews' ),
            'search_items'  => __( 'Search Reviews' ),
          ),
          'public' => true,
          'has_archive' => true,
          'supports' => array('author')
        )
      );
    }
}/* class ends here */

?>