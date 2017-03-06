'use strict';

var frontEndApp = angular.module('frontEndApp', [
													'ngRoute', 
													'ngSanitize', 
													'ngAnimate', 
													'formly', 
													'formlyBootstrap', 
													'bulkSendControllers', 
													'bulkSendAppServices', 
													'ajoslin.promise-tracker', 
													'angularUtils.directives.dirPagination',
                                                    'rzModule',
                                                    'ui.multiselect',
												]);

frontEndApp.config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
	var tstamp = Math.floor(Date.now() / 1000);
	$routeProvider.when('/', {
		templateUrl: '/js/angular/comms-admin/bulk-send/partials/main.html?t=' + tstamp,
		controller: 'HomeCtrl'
	})
	.when('/request', {
		templateUrl: '/js/angular/comms-admin/bulk-send/partials/request.html?t=' + tstamp,
		controller: 'RequestCreateCtrl'
	})
	.when('/review/:request_id', {
		templateUrl: '/js/angular/comms-admin/bulk-send/partials/request.html?t=' + tstamp,
		controller: 'RequestCreateCtrl'
	})
	.when('/history', {
		templateUrl: '/js/angular/comms-admin/bulk-send/partials/history.html?t=' + tstamp,
		controller: 'RequestHistoryCtrl'
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