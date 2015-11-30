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


include_once (BBPRESS_FORUM_DIR . '/shortcode/forum-bbpAjaxIntegrator.php');
include_once (get_stylesheet_directory () . '/forum/forum-jsTemplates.php');

add_action ( 'wp_ajax_forum_getTopicList', 'test');
function  test(){
	$forum = new ForumBbpAjaxIntegrator ();
	$forum->getTopicList ();
	
}
add_action ( 'wp_ajax_forum_saveEditPost', function () {
	$postItem = new ForumBbpAjaxIntegratorPost ( $_POST ['postItemId'], $_POST ['postItemType'] );
	$postItem->updatePost ();
} );

wp_enqueue_style ( "forumStyle", $forumUrl . "/forumStyle.css" );
wp_enqueue_script ( "filereader", get_stylesheet_directory_uri () . "/js/filereader.js", array (
		'jquery' 
) );
// wp_enqueue_style ( 'jquery-ui-menu');
// wp_enqueue_script ( 'jquery-ui-menu');
wp_enqueue_script ( 'bbAjaxForum', $forumUrl . '/js/script.js', array (
		'filereader' 
) );
wp_localize_script ( 'bbAjaxForum', 'custom_ajax_vars', array (
		'ajax_url' => admin_url ( 'admin-ajax.php' ) 
) );



