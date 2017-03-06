'use strict';

var frontEndApp = angular.module('frontEndApp', [
													'ngRoute', 
													'ngSanitize', 
													'ngAnimate', 
													'formly', 
													'formlyBootstrap', 
													'testJourneysControllers', 
													'testJourneysAppServices', 
													'ajoslin.promise-tracker', 
													'angularUtils.directives.dirPagination'
												]);

frontEndApp.config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
	var tstamp = Math.floor(Date.now() / 1000);
	$routeProvider.when('/', {
		templateUrl: '/js/angular/comms-admin/test-journeys/partials/main.html?t=' + tstamp,
		controller: 'HomeCtrl'
	});
	
	$locationProvider.html5Mode(false).hashPrefix('!');
}]);

/**
 * Create html injection filter
 */
frontEndApp.filter("sanitize", ['$sce', function($sce) {
	  return function(htmlCode){
	    return $sce.trustAsHtml(htmlCode);
	  }
}]);