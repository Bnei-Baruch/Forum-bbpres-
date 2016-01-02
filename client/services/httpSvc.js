(function(app) {

	app.factory('ForumSVC', ForumSVC);
	function ForumSVC($http) {
		return {
			getForumId : getForumId,
			getTopicsByForum : getTopicsByForum,
			// topic
			getRepliesByTopic : getRepliesByTopic,
			// reply
			getTranslationList : getTranslationList,

			// reply & topic API
			addPost : addPost,
			editPost : editPost,
			removePost : removePost,

			// attachment
			updateAttachList : updateAttachList
		};

		function getTopicsByForum(data) {
			var defData = {
				forumId : -1, 
				from: 0
			};
			angular.extend(defData, data);
			return _sentToServer('GetTopicsByForum', data);
		}
		function getRepliesByTopic(data) {
			var defData = {
				topicId : -1, 
				from: 0
			};
			angular.extend(defData, data);
			return _sentToServer('GetRepliesByTopic', data);
		}
		function getForumId(postId) {
			return _sentToServer('GetForumId', {postId: postId});
		}
		function getTranslationList() {
			return _sentToServer('GetTranslationList', {});
		}

		function addPost() {
			return _sentToServer('AddPost', {});
		}
		function editPost() {
			return _sentToServer('EditPost', {});
		}
		function removePost() {
			return _sentToServer('RemovePost', {});
		}
		function updateAttachList() {

			return _sentToServer('UpdateAttachList', {});
		}

		function _sentToServer(method, data) {
			data.action = "Forum_" + method;
			var param = {
					method: "POST",
					url: window.ajaxurl,
					data: data					
			}
			return $http(param, data).then(function(r) {
				return r.data;
			});
		}

	}
}(angular.module("Forum")));