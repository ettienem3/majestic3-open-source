'use strict';

var goFetchApp = angular.module('goFetchApp', ['ngRoute', 'ngSanitize', 'ngAnimate', 'formly', 'formlyBootstrap', 'goFetchControllers', 'goFetchAppServices', 'ajoslin.promise-tracker', 'angularUtils.directives.dirPagination']);

goFetchApp.config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
	$routeProvider.when('/', {
		templateUrl: '/js/angular/gofetch/partials/main.html',
		controller: 'HomeCtrl'
	})
	.when('/filter', {
		templateUrl: '/js/angular/gofetch/partials/filter.html',
		controller: 'FilterCtrl'
	})	
	.when('/enrich', {
		templateUrl: '/js/angular/gofetch/partials/enrich.html',
		controller: 'EnrichCtrl'
	})
	.when('/target', {
		templateUrl: '/js/angular/gofetch/partials/target.html',
		controller: 'TargetCtrl'
	})
	.when('/channel', {
		templateUrl: '/js/angular/gofetch/partials/channel.html',
		controller: 'ChannelCtrl'
	});
	
	$locationProvider.html5Mode(false).hashPrefix('!');
}]);

/**
 * Create html injection filter
 */
goFetchApp.filter("sanitize", ['$sce', function($sce) {
	  return function(htmlCode){
	    return $sce.trustAsHtml(htmlCode);
	  }
}]);

goFetchApp.filter('range', function() {
	  return function(input, min, max) {
	    min = parseInt(min); //Make string input int
	    max = parseInt(max);
	    for (var i=min; i<max; i++)
	      input.push(i);
	    return input;
	  };
});