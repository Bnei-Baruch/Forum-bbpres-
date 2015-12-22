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
				'post_type' => 'reply' 
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
		$this->autorId = $fooName ( $this->postId );
	}
	public function addPost() {
	}
	public function getPostData() {
		$fooName = 'bbp_get_' . $this->postType . '_content';
		$content = $fooName ( $this->postId );
		
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
