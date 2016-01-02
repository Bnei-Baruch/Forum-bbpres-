<?php
class ForumPostIntegrator {
	public function addPost() {
	}
	public static function getData($post) {
		//$user = get_user_by("id", $post->post_author );
		$userData = false;
		$user = get_user_by("id", 30 );
		if ($user ) {
			$user = $userData = array (
					'isCurrentUser' => $user->ID == get_current_user_id (),
					'url' => bp_core_get_user_domain ( $user->ID ),
					'avatar' => bp_core_fetch_avatar ( array (
							'item_id' => $user->ID,
							'height' => $imgSize,
							'width' => $imgSize 
					) ),
					'name' => $user->data->display_name 
			);
		}
		$return = array (
				'autor' => $user,
				'type' => $post->post_type,
				// 'attachmentList' => self::getAttachmentList($post->ID),
				'title' => $post->post_name,
				'id' => $post->ID,
				'likes' => 0,
				'createDate' => strtotime ($post->post_date),
				'contentShort' => mb_substr ( $post->post_content, 0, 500 ),
				'content' => $post->post_content,
				'like' => get_post_meta ( $post->ID, 'likes', true ),
				'isLiked' => get_post_meta ( $post->ID, 'like_' . $autorId, true ) 
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
	private static function getAttachmentList($postId) {
		$attachList = get_post_meta ( $postId, 'attaches', true );
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

?>