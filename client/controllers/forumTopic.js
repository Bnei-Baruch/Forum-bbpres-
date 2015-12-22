(function(app) {
	app.controller('ForumTopicCtrl', ForumTopicCtrl);

	function ForumTopicCtrl(ForumSVC, $rootScope) {
		var vm = this;
		_init();
		return vm;

		function _init() {
			ForumSVC.getForumId($rootScope.postId).then(function(data) {
				vm.forumId = data;
			});
		}
		function loadMoreTopic() {
			var param = {
				from : vm.topicList.length || 0,
				forumId: vm.forumId
			}
			ForumSVC.getTopicsByForum(param).then(function(data) {
				data.forEach(function(topic){
					/*topic.replyList = []; 
					var replyParam = {
						from : 0,
						topicId: data.id							
					}					
					ForumSVC.getTopicsByForum(replyParam).then(function(data) {
						topic.replyList = data;
					});*/
					vm.topicList.push(topic);					
				});
			});

		}
	}
}(angular.module("Forum")));