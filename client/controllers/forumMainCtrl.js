(function(app) {
	app.controller('ForumMainCtrl', ForumMainCtrl);
	app.directive('forumEditPost', ForumEditPost);

	function ForumEditPost(ForumSVC, $rootScope) {
		return {
			templateUrl : function() {
				return $rootScope.baseTplUrl + 'editPost.tpl.html';
			},
			scope : {
				post : "="
			},
			link : function($scope, attr, el, ctrl) {

			}
		};
	}
	function ForumMainCtrl(ForumSVC, $rootScope) {
		var vm = this;
		vm.topicList = [];
		vm.loadMoreTopic = loadMoreTopic;
		_init();
		return vm;

		function _init() {
			ForumSVC.getForumId($rootScope.postId).then(function(data) {
				vm.forumId = data;
				loadMoreTopic();
			});
		}
		function loadMoreTopic() {
			var param = {
				from : vm.topicList.length || 0,
				forumId : vm.forumId
			}
			ForumSVC.getTopicsByForum(param).then(function(data) {
				vm.hasMoreTopics = data.hasMoreTopics;
				data.topicList.forEach(function(topic) {
					/*
					 * topic.replyList = []; var replyParam = { from : 0,
					 * topicId: data.id }
					 * ForumSVC.getTopicsByForum(replyParam).then(function(data) {
					 * topic.replyList = data; });
					 */
					vm.topicList.push(topic);
				});
			});

		}
	}
}(angular.module("Forum")));