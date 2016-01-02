(function(app) {
	app.controller('ForumTopicCtrl', Controller);

	function Controller(ForumSVC, $rootScope, $scope) {
		var vm = this;
		vm.loadMoreReply = loadMoreReply;
		return vm;

		function loadMoreReply() {		
			var param = {
				from : $scope.topic.replyList.length || 0,
				topicId: $scope.topic.id,
				
			}
			ForumSVC.getRepliesByTopic(param).then(function(data) {
				$scope.topic.hasMoreTopics = data.hasMoreTopics;
				data.replyList.forEach(function(reply){
					$scope.topic.replyList.push(reply);					
				});
			});

		}
	}
}(angular.module("Forum")));
