<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AwstConfig {

    public function getAllowedPostTypes() {
        $allowedPostTypes   = array(
            'post'              => 'Wordpress Post',
            'sfwd-courses'      => 'Learndash course',
            'sfwd-lessons'      => 'Learndash lesson',
            'sfwd-topic'        => 'Learndash topic',
            'sfwd-quiz'         => 'Learndash quiz',
            'sfwd-certificates' => 'Learndash certificate',
        );

        return $allowedPostTypes;
    }

    public function postTypeLabel( $postType ) {

        $postTypes     = AwstConfig::getAllowedPostTypes();

        $postTypeLabel = $postTypes[$postType];

        if( $postTypeLabel ){
            return $postTypeLabel;
        }else{
            return $postType;
        }
    }


    public function getPostTypes() {
        $postTypes = AwstConfig::getAllowedPostTypes();
        return array_keys($postTypes);
    }


}/*class ends here*/

?>