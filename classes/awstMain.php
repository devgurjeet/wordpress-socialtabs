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

        /*review functionality*/
        add_action('wp_ajax_awst_ajax_review', array('AwstAjax', 'awst_ajax_review'));

        add_filter('the_content', array('AwstFrontPages', 'content_filter'),20);

        add_filter('bbp_get_reply_content', array('AwstFrontPages', 'content_filter'),20,1);

        add_filter('bbp_get_topic_content', array('AwstFrontPages', 'content_filter'),20,1);

        add_filter('bbp_get_forum_content', array('AwstFrontPages', 'content_filter'),20,1);

        add_action( 'bp_get_activity_content' , array('AwstFrontPages', 'content_filter'),20,1);

        add_action( 'bp_member_activity_filter_options' , array('AwstFrontPages', 'content_filter') );

        add_action( 'bp_group_activity_filter_options' , array('AwstFrontPages', 'content_filter') );

        add_action( 'mpp_media_meta' , array('AwstFrontPages', 'filtermediapress'));
        add_action( 'mpp_gallery_meta' , array('AwstFrontPages', 'filtermediapress'));

        add_filter( 'bp_activity_excerpt_append_text', array('AwstFrontPages', 'activity'), 10);
        add_filter( 'comment_text', array('AwstFrontPages', 'comment_text_filter'), 10);

    }

    function loadAssectCss(){
         $plugin_url = plugin_dir_url( __FILE__ );

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

}/*class ends here*/
?>