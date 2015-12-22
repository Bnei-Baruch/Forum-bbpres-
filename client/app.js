(function() {
	var app = angular.module("Forum", []);
	app.config(function ($httpProvider){
		$httpProvider.defaults.headers.post['CONTENT-TYPE'] = 'application/json;charset=UTF-8';
		$httpProvider.defaults.headers.common["Accept"] = "application/json, text/plain, * / *"; 
	});
}());