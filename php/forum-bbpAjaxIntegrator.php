<?php
class ForumBbpAjaxIntegrator {
	private $buddypress_id = - 1;
	private $forumId = - 1;
	private $translation = array ();
	public function __construct($post_id = -1) {
		// maybe bugs with this
		$post_id = ($post_id != - 1) ? $post_id : $post_id = url_to_postid ( wp_get_referer () );
		$this->buddypress_id = get_post_meta ( $post_id, 'buddypress_id', true );
		$forum_id = bbp_get_group_forum_ids ( $this->buddypress_id );
		$forum_id = ! empty ( $forum_id ) ? $forum_id [0] : null;
		
		$this->forumId = $forum_id;
		$this->translation = $this->_getTranslationList ();
	}
	public function getTopicList() {
		if (is_null ( $_POST ['param'] ) || empty ( $_POST ['param'] ))
			$this->_die ();
		
		$return = array ();
		$loadFrom = empty ( $_POST ['param'] ['from'] ) ? 0 : $_POST ['param'] ['from'];
		$loadTo = empty ( $_POST ['param'] ['to'] ) ? 0 : $_POST ['param'] ['to'];
		$param = array (
				'post_parent' => $this->forumId 
		);
		
		if (! bbp_has_topics ( $param ))
			$this->_die ();
		
		while ( bbp_topics () ) {
			bbp_the_topic ();
			$topicId = bbp_get_topic_id ();
			$topic = new ForumBbpAjaxIntegratorPost ( $topicId, 'topic' );
			
			$returnItem = $topic->getPostData ();
			$returnItem ['replyList'] = $this->getReplyList ( $topicId );
			$return [] = $returnItem;
		}
		wp_die ( json_encode ( $return ) );
	}
	public function getReplyList($topicId = -1) {
		$topicId = ($topicId == - 1) ? $_POST ['param'] ['topicId'] : $topicId;
		$loadFrom = empty ( $_POST ['param'] ['from'] ) ? 0 : $_POST ['param'] ['from'];
		$loadTo = empty ( $_POST ['param'] ['to'] ) ? 0 : $_POST ['param'] ['to'];
		$return = array ();
		$param = array (
				'post_parent' => $topicId, 
				'post_type'=> 'reply'
		);
		if (! bbp_has_replies ( $param ))
			$this->_die ();
		while ( bbp_replies () ) {
			bbp_the_reply ();
			$reply = new ForumBbpAjaxIntegratorPost ( bbp_get_reply_id (), 'reply' );
			$return [] = $reply->getPostData ();
		}
		return $return;
	}
	private function _die($msg = "error") {
		$return = array (
				'status' => 0,
				'errorMessage' => $msg 
		);
		return $return;
		wp_die ();
	}
	private function _getTranslationList() {
		$return = array (
				'dislike' => __ ( 'Dislike', 'qode' ),
				'like' => __ ( 'Like', 'qode' ),
				'save' => __ ( 'Save', 'qode' ),
				'cancel' => __ ( 'Cancel', 'qode' ),
				'enterText' => __ ( 'Введите текст сообщения...', 'qode' ) 
		);
		return $return;
	}
}
class ForumBbpAjaxIntegratorPost {
	private $postId = - 1;
	private $postType;
	private $autorId;
	public function __construct($postId, $type) {
		$this->postId = $postId;
		$this->postType = $type;
		$fooName = 'bbp_get_' . $this->postType . '_author_id';
		$this->autorId = $fooName ($this->postId);
	}
	public function addPost() {
	}
	public function getPostData() {
		$fooName = 'bbp_get_' . $this->postType . '_content';
		$content = $fooName ($this->postId);
		
		$return = array (
				'autor' => array (
						'isCurrentUser' => $this->autorId == get_current_user_id (),
						'url' => bp_core_get_user_domain ( $this->autorId ),
						'avatar' => bp_core_fetch_avatar ( array (
								'item_id' => $autorId,
								'height' => $imgSize,
								'width' => $imgSize 
						) ),
						'name' => bbp_get_reply_author_display_name ( $this->postId ) 
				),
				'type' => $this->postType,
				'attachmentList' => $this->_getAttachmentList (),
				'sContent' => bbp_get_reply_content ( $this->postId ),
				'id' => $this->postId,
				'likes' => 0,
				'sDate' => get_post_time ( 'j F ', false, $this->postId, true ) . __ ( 'at', 'qode' ) . get_post_time ( ' H:i', false, $this->postId, true ),
				'sContentShort' => mb_substr ( $content, 0, 500 ),
				'sContent' => $content,
				'like' => get_post_meta ( $this->postId, 'likes', true ),
				'isLiked' => get_post_meta ( $this->postId, 'like_' . $autorId, true ) 
		);
		
		return $return;
	}
	public function updatePost() {
		$request = array (
				'status' => false,
				'data' => array () 
		);
		$content = $_POST ['content'];
		
		if ($this->autorId != get_current_user_id ()) {
			$request->data->errorMsg = __ ( 'You have not permissions for this.', 'forum' );
			$this->_die ( $request );
		}
		
		$request->data = wp_update_post ( array (
				'ID' => $this->postId,
				'post_content' => $content 
		) );
		$this->_die ( $request );
		
		// update_post_meta ( $_POST ['id'], 'attaches', $_POST ['attaches'] );
	}
	public function deletePost() {
		$request = array (
				'status' => false,
				'data' => array () 
		);
		if ($this->autorId != get_current_user_id ()) {
			$request->data->errorMsg = __ ( 'You have not permissions for this.', 'forum' );
			$this->_die ( $request );
			return;
		}
		$fooName = 'bbp_delete_' . $this->postType;
		$content = $fooName ( $this->postId );
		wp_delete_post ( $this->postId, true );
		$request->status = true;
		$this->_die ( $result );
	}
	private function _getAttachmentList() {
		$attachList = get_post_meta ( $this->postId, 'attaches', true );
		$return = array ();
		foreach ( explode ( ',', $attachList ) as $attachId ) {
			if (empty ( $attachId ))
				continue;
			$attach = new ForumBbpAjaxIntegratorAttachmentList ( $attachId );
			$return [] = $attach->get ();
		}
		return $return;
	}
	private function _die($result) {
		if ($result->status)
			$result->data = $this->getPostData ();
		die ( json_encode ( array (
				'result' => $result 
		) ) );
	}
}
class ForumBbpAjaxIntegratorAttachmentList {
	private $attachId = - 1;
	public function __construct($attachId) {
		$this->attachId = $attachId;
	}
	public function get($postId) {
		return array (
				'urlToFull' => wp_get_attachment_image_src ( $this->attach, 'full' ),
				'img' => wp_get_attachment_image ( $this->attachId, 'thumbnail' ) 
		);
	}
	public function set($postId) {
	}
	public function delete($postId) {
	}
}

// add_action ( 'wp_ajax_update_image_only_del', 'update_image_only_del' );
// function update_image_only_del() {
// 	$post_id = $_POST ['post_id'];
// 	$author = get_post ( $post_id );
// 	$author = $author->post_author;
// 	if ($author == get_current_user_id ()) {
// 		wp_delete_attachment ( $post_id, true );
// 		wp_delete_post ( $post_id, true );
// 	}
// }

// add_action ( 'wp_ajax_upload-forum-file', 'upload_forum_file' );
// function delete_attachment_cb() {
// 	$id = $_POST ['id'];
// 	$ptid = $_POST ['ptid'];
// 	$type = $_POST ['type'];
	
// 	if ($type == 'post') {
// 		$meta = get_post_meta ( $ptid, 'attaches', true );
		
// 		$new_meta = preg_replace ( "/$id/ui", '', $meta );
// 		$new_meta = preg_replace ( "/,,/ui", ',', $new_meta );
// 		$new_meta = preg_replace ( "/^,/ui", '', $new_meta );
// 		$new_meta = preg_replace ( "/,$/ui", '', $new_meta );
		
// 		update_post_meta ( $ptid, 'attaches', $new_meta );
// 	} else {
// 		$meta = get_comment_meta ( $ptid, 'attaches', true );
		
// 		$new_meta = preg_replace ( "/$id/ui", '', $meta );
// 		$new_meta = preg_replace ( "/,,/ui", ',', $new_meta );
// 		$new_meta = preg_replace ( "/^,/ui", '', $new_meta );
// 		$new_meta = preg_replace ( "/,$/ui", '', $new_meta );
		
// 		update_comment_meta ( $ptid, 'attaches', $new_meta );
// 	}
// }

// add_action ( 'wp_ajax_delete-attachment', 'delete_attachment_cb' );
// function upload_forum_file() {
// 	$id = isset ( $_POST ['id'] ) ? ( int ) $_POST ['id'] : 0;
// 	$type = $_POST ['type'];
// 	$file = $_POST ['file'];
// 	$name = $_POST ['name'];
	
// 	$tmp_img = explode ( ";", $file );
// 	$img_header = explode ( '/', $tmp_img [0] );
// 	$ext = $img_header [1];
	
// 	if (! in_array ( $ext, array (
// 			'png',
// 			'jpg',
// 			'jpeg',
// 			'gif' 
// 	) )) {
// 		die ( json_encode ( array (
// 				'result' => 'ERROR' 
// 		) ) );
// 	}
	
// 	$imgtitle = $name;
// 	$imgtitle .= '.' . $ext;
	
// 	$uploads = wp_upload_dir ( $time = null );
// 	$filename = wp_unique_filename ( $uploads ['path'], $imgtitle );
	
// 	$image_url = $uploads ['url'] . '/' . $filename;
	
// 	file_put_contents ( $uploads ['path'] . '/' . $filename, file_get_contents ( 'data://' . $file ) );
	
// 	$wp_filetype = wp_check_filetype ( $image_url );
// 	$attachment = array (
// 			'guid' => $image_url,
// 			'post_mime_type' => $wp_filetype ['type'],
// 			'post_title' => preg_replace ( '/\.[^.]+$/', '', basename ( $image_url ) ),
// 			'post_content' => '',
// 			'post_status' => 'inherit' 
// 	);
	
// 	$attachment_id = wp_insert_attachment ( $attachment, $uploads ['path'] . '/' . $filename, ($type == 'post') ? $id : 0 );
	
// 	require_once (ABSPATH . 'wp-admin/includes/image.php');
	
// 	$attachment_data = wp_generate_attachment_metadata ( $attachment_id, $uploads ['path'] . '/' . $filename );
	
// 	wp_update_attachment_metadata ( $attachment_id, $attachment_data );
	
// 	$content = '';
	
// 	if ($type == 'post') {
// 		if ($id != 0) {
// 			$meta = get_post_meta ( $id, 'attaches', true );
			
// 			update_post_meta ( $id, 'attaches', (empty ( $meta )) ? $attachment_id : $meta . ',' . $attachment_id );
// 		}
		
// 		$r = wp_get_attachment_image_src ( $attachment_id, 'full' );
		
// 		$content = '<div class="single_topic_single_attachment">
//                 <div class="attachment-image"><a target="_blank" href="' . $r [0] . '">' . wp_get_attachment_image ( $attachment_id, array (
// 				'32',
// 				'32' 
// 		) ) . '</a></div><div class="attachment-controls"><a class="delete-attachment" data-id="' . $attachment_id . '" href="#">Удалить</a></div>
//             </div>';
// 	} else {
// 		if ($id != 0) {
// 			$meta = get_comment_meta ( $id, 'attaches', true );
			
// 			update_comment_meta ( $id, 'attaches', (empty ( $meta )) ? $attachment_id : $meta . ',' . $attachment_id );
// 		}
		
// 		$r = wp_get_attachment_image_src ( $attachment_id, 'full' );
		
// 		$content = '<div class="single_reply_single_attachment">
//                 <div class="attachment-image"><a target="_blank" href="' . $r [0] . '">' . wp_get_attachment_image ( $attachment_id, array (
// 				'32',
// 				'32' 
// 		) ) . '</a></div><div class="attachment-controls"><a class="delete-attachment" data-id="' . $attachment_id . '" href="#">Удалить</a></div>
//             </div>';
// 	}
	
// 	die ( json_encode ( array (
// 			'result' => 'OK',
// 			'id' => $attachment_id,
// 			'content' => $content 
// 	) ) );
// }

// add_action ( 'wp_ajax_custom_bbp_topic_create', 'custom_bbp_topic_create' );
// function custom_bbp_topic_create() {
// 	$topic_id = bbp_insert_topic ( array (
// 			'post_parent' => ( int ) $_POST ['bbp_forum_id'],
// 			'post_content' => $_POST ['content'],
// 			'post_title' => (mb_strlen ( $_POST ['content'] ) > 100) ? mb_substr ( $_POST ['content'], 0, 100 ) . '...' : $_POST ['content'] 
// 	), 
// 			// 'comment_status' => 'closed',
// 			// 'menu_order' => 0,
// 			array (
// 					'forum_id' => ( int ) $_POST ['bbp_forum_id'] 
// 			) );
	
// 	echo print_r ( $topic_id, true );
// 	$isAttahced = update_post_meta ( $topic_id, 'attaches', $_POST ['attaches'] );
// 	do_action ( 'nbbi_insert_any', $topic_id );
// 	if (! is_numeric ( $isAttahced ))
// 		echo $isAttahced;
// }

// add_action ( 'wp_ajax_custom_bbp_reply_create', 'custom_bbp_reply_create' );
// function custom_bbp_reply_create() {
// 	$reply_id = bbp_insert_reply ( array (
// 			'post_parent' => $_POST ['bbp_topic_id'], // topic ID
// 			'post_content' => $_POST ['content'],
// 			'post_title' => (mb_strlen ( $_POST ['content'] ) > 100) ? mb_substr ( $_POST ['content'], 0, 100 ) . '...' : $_POST ['content'] 
// 	), array (
// 			'forum_id' => $_POST ['bbp_forum_id'],
// 			'topic_id' => $_POST ['bbp_topic_id'] 
// 	) );
// 	echo $reply_id;
// 	update_comment_meta ( $reply_id, 'attaches', $_POST ['attaches'] );
	
// 	do_action ( 'nbbi_insert_any', $reply_id );
// }
// function filter_content_tags($content) {
	
// 	// $content = nl2br(strip_tags(trim($content), '<br>'));
// 	$content = nl2br ( esc_html ( trim ( $content ) ), '<br>' );
	
// 	return $content;
// }

// add_filter ( 'bbp_get_reply_content', 'filter_content_tags', 0 );
// add_filter ( 'bbp_get_topic_content', 'filter_content_tags', 0 );
// function remove_topic_custom() {
// 	$post = get_post ( $_POST ['id'] );
	
// 	if ($post->post_author == get_current_user_id ()) {
// 		bbp_delete_topic ( $_POST ['id'] );
// 		wp_delete_post ( $_POST ['id'], true );
// 	}
	
// 	die ( json_encode ( array (
// 			'result' => 'OK' 
// 	) ) );
// }
// function remove_reply_custom() {
// 	$post = get_post ( $_POST ['id'] );
	
// 	if ($post->post_author == get_current_user_id ()) {
// 		bbp_delete_reply ( $_POST ['id'] );
// 		wp_delete_post ( $_POST ['id'], true );
// 	}
	
// 	die ( json_encode ( array (
// 			'result' => 'OK' 
// 	) ) );
// }

// add_action ( 'wp_ajax_remove-topic-custom', 'remove_topic_custom' );
// add_action ( 'wp_ajax_remove-reply-custom', 'remove_reply_custom' );
// function update_topic_custom() {
// 	$post = get_post ( $_POST ['id'] );
	
// 	if ($post->post_author == get_current_user_id ()) {
// 		wp_update_post ( array (
// 				'ID' => $_POST ['id'],
// 				'post_content' => $_POST ['content'] 
// 		) );
		
// 		update_post_meta ( $_POST ['id'], 'attaches', $_POST ['attaches'] );
// 	}
	
// 	die ( json_encode ( array (
// 			'result' => 'OK',
// 			'content' => bbp_get_topic_content ( $_POST ['id'] ) 
// 	) ) );
// }
// function update_reply_custom() {
// 	$post = get_post ( $_POST ['id'] );
	
// 	if ($post->post_author == get_current_user_id ()) {
// 		wp_update_post ( array (
// 				'ID' => $_POST ['id'],
// 				'post_content' => $_POST ['content'] 
// 		) );
		
// 		update_comment_meta ( $_POST ['id'], 'attaches', $_POST ['attaches'] );
// 	}
	
// 	die ( json_encode ( array (
// 			'result' => 'OK',
// 			'content' => bbp_get_reply_content ( $_POST ['id'] ) 
// 	) ) );
// }

// add_action ( 'wp_ajax_update-topic-custom', 'update_topic_custom' );
// add_action ( 'wp_ajax_update-reply-custom', 'update_reply_custom' );
// function like_custom() {
// 	$check = get_post_meta ( $_POST ['id'], 'like_' . get_current_user_id (), true );
// 	$count = get_post_meta ( $_POST ['id'], 'likes', true );
	
// 	if ($check) {
// 		$count = ( int ) $count - 1;
// 		delete_post_meta ( $_POST ['id'], 'like_' . get_current_user_id () );
// 	} else {
// 		$count = ( int ) $count + 1;
// 		update_post_meta ( $_POST ['id'], 'like_' . get_current_user_id (), '1' );
// 	}
	
// 	update_post_meta ( $_POST ['id'], 'likes', $count );
	
// 	die ( json_encode ( array (
// 			'result' => 'OK',
// 			'count' => $count 
// 	) ) );
// }

// add_action ( 'wp_ajax_like-custom', 'like_custom' );
// function custom_plural_form($n, $form1, $form2, $form5) {
// 	$n = abs ( $n ) % 100;
// 	$n1 = $n % 10;
// 	if ($n > 10 && $n < 20)
// 		return $form5;
// 	if ($n1 > 1 && $n1 < 5)
// 		return $form2;
// 	if ($n1 == 1)
// 		return $form1;
// 	return $form5;
// }
// function load_all_replies() {
// 	$content = '';
	
// 	$replies = get_posts ( $default = array (
// 			'numberposts' => - 1,
// 			'post_type' => bbp_get_reply_post_type (), // Only replies
// 			'post_parent' => $_POST ['id'], // Of this topic
// 			'orderby' => 'date', // Sorted by date
// 			'order' => 'ASC', // Oldest to newest
// 			'ignore_sticky_posts' => true 
// 	) ); // Stickies not supported
	
// 	array_pop ( $replies );
// 	array_pop ( $replies );
// 	array_pop ( $replies );
// 	array_pop ( $replies );
	
// 	ob_start ();
// 	foreach ( $replies as $reply ) {
		
// 		?>
<div class="single_topic_reply" data-id="<?php echo $reply->ID; ?>">
<!-- 	<div class="photo"> -->
		<a href="<?php echo bp_core_get_user_domain($reply->post_author); ?>"><?php echo bp_core_fetch_avatar(array('item_id' => $reply->post_author, 'height' => 32, 'width' => 32)); ?></a>
<!-- 	</div> -->
<!-- 	<div class="content_wrapper"> -->
<!-- 		<div class="reply_content"> -->
<!-- 			<a class="author-link" -->
				href="<?php echo bp_core_get_user_domain($reply->post_author); ?>"><?php echo bbp_get_reply_author_display_name($reply->ID); ?></a><?php echo bbp_get_reply_content($reply->ID); ?>
<!--                 </div> -->
		<div style="display: none" class="reply_content_edit">
			<textarea class="reply_content_edit_textarea"><?php echo get_post_field('post_content', $reply->ID); ?></textarea>
<!-- 			<a href="#" class="smiles_open"></a> -->

<!-- 			<div class="edit_actions"> -->
<!-- 				<a class="cancel" href="#">Отменить</a> -->
<!-- 			</div> -->
<!-- 		</div> -->
                <?php $likes = get_post_meta($reply->ID, 'likes', true); ?>
<!--                 <div class="actions"> -->
			<span class="date"><?php echo get_post_time('j F ', false, $reply->ID, true) . __('at', 'qode') . get_post_time(' H:i', false, $reply->ID, true); ?></span><?php $like = get_post_meta($reply->ID, 'like_' . get_current_user_id(), true); ?>
<!--                     <a class="like" -->
				<?php echo (!empty($like)) ? ' style="display:none"' : ''; ?>
				href="#"><?php _e('Like', 'qode'); ?></a><a class="like dislike"
				<?php echo (empty($like)) ? ' style="display:none"' : ''; ?>
				href="#"><?php _e('Dislike', 'qode'); ?></a>

<!-- 			<div class="like-count" -->
				<?php if (empty($likes)) echo ' style="display:none"'; ?>>
				<i class="like-img"></i><span class="count"><?php echo (int)$likes; ?></span>
<!-- 			</div> -->
<!-- 		</div> -->
<!-- 	</div> -->
            <?php if ($reply->post_author == get_current_user_id()): ?>
<!--                 <a class="addi_actions_open" href="#"></a> -->
	<div class="addi_actions" style="display: none">
<!-- 		<ul> -->
<!-- 			<li><a class="edit_action" href="#">Редактировать</a></li> -->
<!-- 			<li><a class="remove_action" href="#">Удалить</a></li> -->
<!-- 		</ul> -->
<!-- 	</div> -->
            <?php endif; ?>
<!--         </div> -->
// <?php
// 	}
// 	$content = ob_get_contents ();
// 	ob_end_clean ();
	
// 	die ( json_encode ( array (
// 			'result' => 'OK',
// 			'content' => $content 
// 	) ) );
// }

// add_action ( 'wp_ajax_load-all-replies', 'load_all_replies' );
// function load_more_topics() {
// 	$content = '';
	
// 	ob_start ();
	
// 	$forum_id = $_POST ['forum'];
	
// 	if ($topics = bbp_has_topics ( array (
// 			'post_parent' => $forum_id,
// 			'posts_per_page' => 11,
// 			'paged' => $_POST ['list'] 
// 	) )) {
// 		$counter = 0;
// 		while ( bbp_topics () ) :
// 			bbp_the_topic ();
			
// 			if (++ $counter == 12)
// 				break;
			
// 			?>
<!-- <div class="topics_list_single_topic" -->
	id="topic-<?php echo bbp_get_topic_id(); ?>"
	data-bbp_forum_id="<?php echo $forum_id;?>"
	data-id="<?php echo bbp_get_topic_id(); ?>">
<!-- 	<div class="single_topic_header"> -->
<!-- 		<div class="photo"> -->
<!-- 			<a -->
				href="<?php echo bp_core_get_user_domain(bbp_get_topic_author_id()); ?>"><?php echo bp_core_fetch_avatar(array('item_id' => bbp_get_topic_author_id(), 'height' => 40, 'width' => 40)); ?></a>
<!-- 		</div> -->
<!-- 		<div class="info"> -->
<!-- 			<div class="name"> -->
<!-- 				<a -->
					href="<?php echo bp_core_get_user_domain(bbp_get_topic_author_id()); ?>"><?php echo bbp_get_topic_author_display_name(bbp_get_topic_id()); ?></a>
<!-- 			</div> -->
			<div class="date"><?php echo get_post_time('j F ', false, bbp_get_topic_id(), true) . __('at', 'qode') . get_post_time(' H:i', false, bbp_get_topic_id(), true); ?></div>
<!-- 		</div> -->
                    <?php if (bbp_get_topic_author_id() == get_current_user_id()): ?>
<!--                         <a href="#" class="addi_actions_open"></a> -->
		<div class="addi_actions" style="display: none">
<!-- 			<ul> -->
<!-- 				<li><a class="edit_action" href="#">Редактировать</a></li> -->
<!-- 				<li><a class="remove_action" href="#">Удалить</a></li> -->
<!-- 			</ul> -->
<!-- 		</div> -->
                    <?php endif; ?>
<!--                 </div> -->
<!-- 	<div class="single_topic_content"> -->
                    <?php
			
// 			$content = bbp_get_topic_content ();
// 			if (mb_strlen ( $content ) > 500) {
// 				echo '<div class="show">' . mb_substr ( $content, 0, 500 ) . '... <a href="#" class="show_all">' . __ ( 'More', 'qode' ) . '</a></div>';
// 				?>
                        <div class="hide"><?php echo $content; ?></div>
                    <?php
// 			} else {
// 				echo $content;
// 			}
			
// 			?>
<!--                 </div> -->
	<div style="display: none" class="single_topic_content_edit">
		<textarea class="edit_content"><?php echo get_post_field('post_content', bbp_get_topic_id()); ?></textarea>

<!-- 		<div class="edit_actions"> -->
			<button class="cancel"><?php _e('Cancel', 'qode'); ?></button>
			<button class="save"><?php _e('Save', 'qode'); ?></button>
<!-- 		</div> -->
<!-- 	</div> -->
<!-- 	<div class="single_topic_actions"> -->
                    <?php $likes = get_post_meta(bbp_get_topic_id(), 'likes', true); ?>
                    <?php $like = get_post_meta(bbp_get_topic_id(), 'like_' . get_current_user_id(), true); ?><a
<!-- 			class="like" -->
			<?php echo (!empty($like)) ? ' style="display:none"' : ''; ?>
			href="#"><?php _e('Like', 'qode'); ?></a><a class="like dislike"
			<?php echo (empty($like)) ? ' style="display:none"' : ''; ?> href="#"><?php _e('Dislike', 'qode'); ?></a>

<!-- 		<div class="like-count" -->
			<?php if (empty($likes)) echo ' style="display:none"'; ?>>
			<i class="like-img"></i><span class="count"><?php echo (int)$likes; ?></span>
<!-- 		</div> -->
<!-- 	</div> -->
<!-- 	<div class="single_topic_replies_container"> -->
<!-- 		<div class="single_topic_replies"> -->
                        <?php
// 			$replies = get_posts ( $default = array (
// 					'post_type' => bbp_get_reply_post_type (), // Only replies
// 					'post_parent' => bbp_get_topic_id (), // Of this topic
// 					'posts_per_page' => 5, // This many
// 					'orderby' => 'date', // Sorted by date
// 					'order' => 'DESC', // Oldest to newest
// 					'ignore_sticky_posts' => true 
// 			) ); // Stickies not supported
			
// 			$i = count ( $replies );
// 			if ($i == 5) {
// 				$count = new WP_Query ( $default = array (
// 						'numberposts' => - 1,
// 						'post_type' => bbp_get_reply_post_type (), // Only replies
// 						'post_parent' => bbp_get_topic_id (), // Of this topic
// 						'posts_per_page' => 5, // This many
// 						'orderby' => 'date', // Sorted by date
// 						'order' => 'DESC', // Oldest to newest
// 						'ignore_sticky_posts' => true 
// 				) ); // Stickies not supported
				
// 				$count = $count->found_posts - 4;
				?><a href="#" class="load_all_replies"><i class="comments_img"></i>Про�?мотреть
                            еще <?php echo $count . ' ' . custom_plural_form($count, 'комментарий', 'комментари�?', 'комментариев'); ?>
<!--                             </a> -->
                        <?php
// 			}
// 			$replies = array_reverse ( $replies );
// 			array_shift ( $replies );
// 			foreach ( $replies as $reply ) {
				
// 				?>
<!--                             <div class="single_topic_reply" -->
				data-id="<?php echo $reply->ID; ?>">
<!-- 				<div class="photo"> -->
<!-- 					<a -->
						href="<?php echo bp_core_get_user_domain($reply->post_author); ?>"><?php echo bp_core_fetch_avatar(array('item_id' => $reply->post_author, 'height' => 32, 'width' => 32)); ?></a>
<!-- 				</div> -->
<!-- 				<div class="content_wrapper"> -->
<!-- 					<div class="reply_content"> -->
<!-- 						<a class="author-link" -->
							href="<?php echo bp_core_get_user_domain($reply->post_author); ?>"><?php echo bbp_get_reply_author_display_name($reply->ID); ?></a><?php echo bbp_get_reply_content($reply->ID); ?>
<!--                                     </div> -->
					<div style="display: none" class="reply_content_edit">
						<textarea class="reply_content_edit_textarea"><?php echo get_post_field('post_content', $reply->ID); ?></textarea>
<!-- 						<a href="#" class="smiles_open"></a> -->

<!-- 						<div class="edit_actions"> -->
<!-- 							<a class="cancel" href="#">Отменить</a> -->
<!-- 						</div> -->
<!-- 					</div> -->
                                    <?php $likes = get_post_meta($reply->ID, 'likes', true); ?>
<!--                                     <div class="actions"> -->
						<span class="date"><?php echo get_post_time('j F ', false, $reply->ID, true) . __('at', 'qode') . get_post_time(' H:i', false, $reply->ID, true); ?></span><?php $like = get_post_meta($reply->ID, 'like_' . get_current_user_id(), true); ?>
<!--                                         <a class="like" -->
							<?php echo (!empty($like)) ? ' style="display:none"' : ''; ?>
							href="#"><?php _e('Like', 'qode'); ?></a><a class="like dislike"
							<?php echo (empty($like)) ? ' style="display:none"' : ''; ?>
							href="#"><?php _e('Dislike', 'qode'); ?></a>

<!-- 						<div class="like-count" -->
							<?php if (empty($likes)) echo ' style="display:none"'; ?>>
							<i class="like-img"></i><span class="count"><?php echo (int)$likes; ?></span>
<!-- 						</div> -->
<!-- 					</div> -->
<!-- 				</div> -->
                                <?php if ($reply->post_author == get_current_user_id()): ?>
<!--                                     <a class="addi_actions_open" -->
<!-- 					href="#"></a> -->
				<div class="addi_actions" style="display: none">
<!-- 					<ul> -->
<!-- 						<li><a class="edit_action" href="#">Редактировать</a></li> -->
<!-- 						<li><a class="remove_action" href="#">Удалить</a></li> -->
<!-- 					</ul> -->
<!-- 				</div> -->
                                <?php endif; ?>
<!--                             </div> -->
                        <?php
// 			}
// 			$url = (isset ( $_SERVER ['HTTPS'] ) ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
// 			?>
<!--                     </div> -->
<!-- 		<div class="single_topic_reply_form"> -->
<!-- 			<form -->
				action="<?php echo $url; ?>#topic-<?php echo bbp_get_topic_id(); ?>"
				data-bbp_forum_id="<?php echo $forum_id;?>"
				data-bbp_topic_id="<?php echo bbp_get_topic_id(); ?>" method="post">
<!-- 				<div class="photo"> -->
<!-- 					<a -->
						href="<?php echo bp_core_get_user_domain(get_current_user_id()); ?>"><?php echo bp_core_fetch_avatar(array('item_id' => get_current_user_id(), 'height' => 32, 'width' => 32)); ?></a>
<!-- 				</div> -->
<!-- 				<div class="reply-form"> -->
<!-- 					<textarea -->
						placeholder="<?php _e('Введите тек�?т �?ообщени�?...', 'qode'); ?>"
<!-- 						name="content"></textarea> -->
<!-- 					<a href="#" class="smiles_open"></a> -->
<!-- 				</div> -->

<!-- 				<input type="hidden" name="bbp_forum_id" -->
					value="<?php echo $forum_id; ?>"> <input type="hidden"
					name="bbp_topic_id" value="<?php echo bbp_get_topic_id(); ?>"> <input
<!-- 					type="hidden" name="action" value="custom-bbp-reply-create"> <input -->
<!-- 					type="hidden" name="security" -->
					value="<?php echo wp_create_nonce('custom-bbp-reply-create'); ?>">
<!-- 			</form> -->
<!-- 		</div> -->
<!-- 	</div> -->
<!-- </div> -->
// <?php
// 		endwhile
// 		;
// 		if ($counter == 11) {
			?><a class="load_more_topics" href="#"><?php _e('Load more discussions', 'qode'); ?></a>
// <?php
// 		}
// 	}
	
// 	$content = ob_get_contents ();
// 	ob_end_clean ();
	
// 	die ( json_encode ( array (
// 			'result' => 'OK',
// 			'content' => $content 
// 	) ) );
// }

// add_action ( 'wp_ajax_load-more-topics', 'load_more_topics' );