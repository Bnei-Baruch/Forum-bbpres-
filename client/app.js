(function() {
	var app = angular.module("Forum", ['ngSanitize']);
	app.config(function ($httpProvider){
		$httpProvider.defaults.headers.post['CONTENT-TYPE'] = 'application/json;charset=UTF-8';
		$httpProvider.defaults.headers.common["Accept"] = "application/json, text/plain, * / *"; 
	})
	.run(function($rootScope){
		$rootScope.baseUrl = window.BBPressForumOptions.baseUrl;
		$rootScope.baseTplUrl = window.BBPressForumOptions.baseUrl + "/client/views/";
		
	});
}());