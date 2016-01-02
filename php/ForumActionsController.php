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
				$method = str_replace ( "Forum_", "", $_REQUEST ['action'] );
				$result = $this->$method ();
				wp_die ( json_encode ( $result ) );
			}, 99 );
		}
	}
	/**
	 * Define, if need, and return forum id (by $_REQUEST['postId'])
	 * 
	 * @return int - id of current forum
	 */
	private function defineForumId() {
		if ($this->forumId != - 1)
			return $this->forumId;
		
		$buddypress_id = get_post_meta ( $_REQUEST ['postId'], 'buddypress_id', true );
		$forumList = bbp_get_group_forum_ids ( $buddypress_id );
		$forumId = ! empty ( $forumList ) ? $forumList [0] : null;
		$this->forumId = $forumId;
		return $forumId;
	}
	/**
	 * get current forum id
	 */
	public function GetForumId() {
		$forumId = $this->defineForumId ();
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
		$param = array (
				'posts_per_page' => 1,
				// 'paged'=>$loadFrom/5,
				'paged' => 2,
				'post_parent' => ( string ) $forumId,
				'post_type' => 'topic',
				'post_status' => 'publish',
				'orderby' => 'date' 
		);
		
		$query = new WP_Query ( $param );
		$postList = $query->query ( $param );
		
		foreach ( $postList as $topicPost ) {
			$topic = ForumPostIntegrator::getData ( $topicPost );
			$replyObj = $this->GetRepliesByTopic ( $topicPost->ID );
			$topic ['replyList'] = $replyObj ["replyList"];
			$topic ['hasMoreReplies'] = $replyObj ["hasMoreReplies"];
			$return [] = $topic;
		}
		$param ["paged"] ++;
		$queryNext = new WP_Query ( $param );
		$hasMoreTopics = count ( $query->query ( $param ) ) > 0;
		
		return array (
				"topicList" => $return,
				"hasMoreTopics" => $hasMoreTopics 
		);
	}
	/**
	 *
	 * @param int $topicId:
	 *        	parent topic id (-1 = all)
	 * @return json:list of replies
	 */
	public function GetRepliesByTopic($topicId = -1) {
		$topicId = ($topicId == - 1) ? $_POST ['topicId'] : $topicId;
		if (empty ( $topicId ))
			$this->_die ( "Not defined topic ID." );
		
		$loadFrom = empty ( $_POST ['from'] ) ? 0 : $_POST ['from'];
		$replyList = array ();
		
		$param = array (
				'post_type' => 'reply',
				'posts_per_page' => 5,
				'paged' => $loadFrom / 5,
				'post_parent' => ( string ) $topicId,
				'post_status' => 'publish',
				'orderby' => 'date' 
		);
		$query = new WP_Query ( $param );
		$postList = $query->query ( $param );
		
		foreach ( $postList as $replyPost ) {
			$reply = ForumPostIntegrator::getData ( $replyPost );
			$replyList [] = $reply;
		}
		// check if has more replies
		$param ["paged"] ++;
		$queryNext = new WP_Query ( $param );
		$hasMoreReplies = count ( $query->query ( $param ) ) > 0;
		
		return array (
				"replyList" => $replyList,
				"hasMoreReplies" => $hasMoreReplies 
		);
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