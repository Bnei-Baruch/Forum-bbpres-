<?php
/**
 * Plugin Name: bbPressForum
 * Description: bbPress is forum software with a twist from the creators of WordPress.
 * Author:      The Bnei Baruch
 * Version:     1.0
 * Domain Path: /languages/
 */
define ( 'BBPRESS_FORUM_DIR', untrailingslashit ( dirname ( __FILE__ ) ) );
define ( 'BBPRESS_FORUM_URL', untrailingslashit ( plugins_url ( '', __FILE__ ) ) );

// include_once (BBPRESS_FORUM_DIR . '/php/forum-bbpAjaxIntegrator.php');

wp_enqueue_script ( "angularjs", "https://ajax.googleapis.com/ajax/libs/angularjs/1.4.5/angular.min.js" );
wp_localize_script ( 'bbAjaxForum', 'custom_ajax_vars', array (
		'ajax_url' => admin_url ( 'admin-ajax.php' ) 
) );

add_action ( "init", function () {
	add_shortcode ( 'forumShortcode', 'RenderForumShortcode' );
} );
function RenderForumShortcode($args) {
	if (! is_user_logged_in ()) {
		return '<h1>test</h1>';
	}
	$str = '<div  ng-app="Forum" ><div ng-include="\'' . BBPRESS_FORUM_URL . '/client/index.html\'"></div></div>';
	
	return $str;
}