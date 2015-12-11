(function() {
	var app = angular.module("Forum", []);
	app.controller('ForumMainCtrl', ForumMainCtrl);

	ForumMainCtrl.$injector = ['ForumSVC'];

	function ForumMainCtrl(forumSVC) {
		var vm = this;
		forumSVC.getForumId.then(function(data){
			vm.test = data;			
		});

		return vm;
	}
}());