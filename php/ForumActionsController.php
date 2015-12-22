<?php
class ForumActionsController {
	private $buddypress_id = - 1;
	private $forumId = - 1;
	private $actionsNameList = array (
			'GetForumId',
			'GetTopicsByForum',
			'GetRepliesByTopic',
			'GetTranslationList',
			'AddPost',
			'EditPost',
			'RemovePost',
			'UpdateAttachList' 
	);
	
	/**
	 * init AJAX actions
	 */
	public function __construct() {
		foreach ( $this->actionsNameList as $action ) {
			add_action ( 'wp_ajax_Forum_' . $action, function () {
				$method = str_replace("Forum_", "", $_REQUEST['action']);
				$result = $this->$method();
				wp_die ( json_encode ( $result ) );
			}, 99 );
		}
	}
	/**
	 * Define, if need, and return forum id (by $_REQUEST['postId'])
	 * @return int - id of current forum
	 */
	private function defineForumId() {
		if($this->forumId != -1)
			return $this->forumId;
		
		$buddypress_id = get_post_meta ( $_REQUEST['postId'], 'buddypress_id', true );
		$forumList = bbp_get_group_forum_ids ( $buddypress_id );
		$forumId = ! empty ( $forumList ) ? $forumList [0] : null;
		$this->forumId = $forumId;
		return $forumId;
	}
	/**
	 * get current forum id
	 */
	public function GetForumId() {
		$forumId = $this->defineForumId();
		return $forumId;
	}
	
	/**
	 * Get current forum topics
	 *
	 * @return json:list of topics
	 */
	public function GetTopicsByForum() {

		$forumId = $_POST ['forumId'];
		
		$return = array ();
		$loadFrom = empty ( $_POST ['from'] ) ? 0 : $_POST ['from'];
		$loadTo = $loadFrom + 20;
		$param = array (
				'post_parent' => $forumId,
				'post_type' => 'topic',
				'post_status' => 'publish',
				'orderby' => 'date' 
		);
		
		$querty = new WP_Query ( $param );
		if (! have_posts ( $querty ))
			$this->_die ();
		
		while ( the_post () ) {
			$post = next_post ();
			$topicId = bbp_get_topic_id ();
			/**
			 * *******************
			 * need build request by $post
			 *
			 * ***************************
			 */
			$topic = new ForumBbpAjaxIntegratorPost ( $topicId, 'topic' );
			
			$returnItem = $topic->getPostData ();
			$returnItem ['replyList'] = $this->getReplyList ( $topicId );
			$return [] = $returnItem;
		}
		return $return;
	}
	/**
	 *
	 * @param int $topicId:
	 *        	parent topic id (-1 = all)
	 * @return json:list of replies
	 */
	public function GetRepliesByTopic($topicId = -1) {
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
	/**
	 * *
	 * Heppend on Error.
	 * Stop run any php and return to brouser JSON
	 *
	 * @param string $msg:
	 *        	error message
	 */
	private function _die($msg = "error") {
		$return = array (
				'status' => 0,
				'errorMessage' => $msg 
		);
		wp_die ( $return );
	}
	/**
	 *
	 * @param string $forObject:
	 *        	bool, false if was called from AJAX
	 * @return array:List of taraslations sentances
	 */
	public static function GetTranslationList($forObject = false) {
		$return = array (
				'dislike' => __ ( 'Dislike', 'qode' ),
				'like' => __ ( 'Like', 'qode' ),
				'save' => __ ( 'Save', 'qode' ),
				'cancel' => __ ( 'Cancel', 'qode' ),
				'enterText' => __ ( 'Введите текст сообщения...', 'qode' ) 
		);
		
		if ($forObject)
			return $return;
		else
			self::_die ( $return );
	}
}