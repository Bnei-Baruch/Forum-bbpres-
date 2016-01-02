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

include_once (BBPRESS_FORUM_DIR . '/php/ForumActionsController.php');
include_once (BBPRESS_FORUM_DIR . '/php/ForumPostIntegrator.php');

wp_enqueue_script ( "angularjs", "https://ajax.googleapis.com/ajax/libs/angularjs/1.4.5/angular.min.js" );
wp_enqueue_script ( "angularjs-sanitize", "https://ajax.googleapis.com/ajax/libs/angularjs/1.4.5/angular-sanitize.js" );

wp_localize_script ( 'bbAjaxForum', 'custom_ajax_vars', array (
		'ajax_url' => admin_url ( 'admin-ajax.php' ) 
) );

add_action ( "init", function () {
	$forumAPI = new ForumActionsController ();
	add_shortcode ( 'forumShortcode', 'RenderForumShortcode' );
} );
function RenderForumShortcode($args) {
	if (! is_user_logged_in ()) {
		return '<h1>test</h1>';
	}
	$str = '<link rel="stylesheet" type="text/css" href="' . BBPRESS_FORUM_URL . '/client/style.css">';
	$str .= '<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">';

	$str .= '<script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>';
	
	//define variabels from PHP
	$str .= '<script type="text/javascript">window.BBPressForumOptions = {"baseUrl": \''.BBPRESS_FORUM_URL.'\'}</script>';
	
	$str .= '<div  ng-app="Forum"  ng-init="$root.postId =' . get_the_ID () . ' "><div ng-include="\'' . BBPRESS_FORUM_URL . '/client/views/ForumMain.tpl.html\'"></div></div>';
	
	// Load angular app file
	$str .= '<script type="text/javascript" src="' . BBPRESS_FORUM_URL . '/client/app.js"></script>';
	
	// Load angular service files
	$str .= '<script type="text/javascript" src="' . BBPRESS_FORUM_URL . '/client/services/httpSvc.js"></script>';
	
	// Load angular controller files
	$str .= '<script type="text/javascript" src="' . BBPRESS_FORUM_URL . '/client/controllers/forumMainCtrl.js"></script>';
	$str .= '<script type="text/javascript" src="' . BBPRESS_FORUM_URL . '/client/controllers/forumTopic.js"></script>';
	$str .= '<script type="text/javascript" src="' . BBPRESS_FORUM_URL . '/client/controllers/forumReply.js"></script>';
	$str .= '<script type="text/javascript" src="' . BBPRESS_FORUM_URL . '/client/controllers/attachments.js"></script>';
	
	return $str;
}