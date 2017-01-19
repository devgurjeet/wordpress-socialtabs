<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);
/*
	Plugin Name: Aw Social Tabs
	Plugin URI:
	Description: This plugin add like, Rate and Review capabilities to posts.
	Version: 1.0.0
	Author: G0947
	Author URI:
	License:
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* Include external classes */
include('classes/config/awstConfig.php');
include('classes/awstAjax.php');
include('classes/awstComman.php');
include('classes/awstAdminPages.php');
include('classes/awstFrontPages.php');
include('awstMain.php');

/*  create plugin object. */
new AwSocialTabs;
?>
