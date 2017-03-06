'use strict';

var frontEndBehaviourSummaryApp = angular.module('frontEndBehaviourSummaryApp', [
													'ngRoute', 
													'ngSanitize', 
													'ngAnimate', 
													'formly', 
													'formlyBootstrap', 
													'behaviourControllers', 
													'behaviourAppServices', 
													'ajoslin.promise-tracker', 
												]);

frontEndBehaviourSummaryApp.config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
	var tstamp = Math.floor(Date.now() / 1000);
	$routeProvider.when('/', {
		templateUrl: '/js/angular/profile/behaviour-summary/partials/main.html?t=' + tstamp,
		controller: 'HomeCtrl'
	});
	
	$locationProvider.html5Mode(false).hashPrefix('!');
}]);

/**
 * Create html injection filter
 */
frontEndBehaviourSummaryApp.filter("sanitize", ['$sce', function($sce) {
	  return function(htmlCode){
	    return $sce.trustAsHtml(htmlCode);
	  }
}]);