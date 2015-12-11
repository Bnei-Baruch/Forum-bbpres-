(function(app) {
	app.controller('ForumMainCtrl', ForumMainCtrl);

	ForumMainCtrl.$injector = [ 'ForumSVC' ];

	function ForumMainCtrl(forumSVC) {
		var vm = this;
		vm.test = "testststst";
		forumSVC.getForumId.then(function(data) {
			vm.test = data;
		});

		return vm;
	}
}(angular.module("Forum")));