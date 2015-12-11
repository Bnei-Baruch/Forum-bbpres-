(function() {
	var app = angular.module("Forum", []);
	app.controller('ForumMainCtrl', ForumMainCtrl);
	app.factory('ForumSVC', ForumSVC);

	ForumMainCtrl.$injector = ['ForumSVC'];
	ForumSVC.$injector = [ '$http' ];

	function ForumMainCtrl(forumSVC) {
		var vm = this;
		forumSVC.getForumId.then(function(data){
			vm.test = data;			
		});

		return vm;
	}
	function ForumSVC($http) {
		return {
			getForumId : getForumId,
			getTopicsByForum : getTopicsByForum,
			//topic
			getRepliesByTopic : getRepliesByTopic,			
			//reply
			getTranslationList: getTranslationList,
			
			//reply & topic API
			addPost:addPost,
			editPost: editPost,
			removePost: removePost,
			
			//attachment
			updateAttachList: updateAttachList
		};

		function getRepliesByTopic(id) {
			var data = {
				topicId: id
			};
			return _sentToServer('GetTopicsByForum', data);
		}

		function getTopicsByForum(id) {
			var data = {
				forumId: id
			};
			return _sentToServer('GetTopicsByForum', data);
		}
		function getForumId() {
			return _sentToServer('GetForumId', {});
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
			var url = window.ajax_url;
			var defData = {
				action : 'Forum_' + method
			};
			angular.extend(data, defData);
			return $http.post(url, data).then(function(r) {
				return r.data;
			});
		}

	}
}());