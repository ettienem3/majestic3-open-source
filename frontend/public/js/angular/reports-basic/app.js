'use strict';

var frontBasicReportViewerApp = angular.module('frontBasicReportViewerApp', ['ngRoute', 
                                                                             'ngSanitize', 
                                                                             'ngAnimate', 
                                                                             'formly', 
                                                                             'formlyBootstrap', 
                                                                             'basicReportsControllers', 
                                                                             'basicReportsAppServices', 
                                                                             'ajoslin.promise-tracker', 
                                                                             'angularUtils.directives.dirPagination']);

frontBasicReportViewerApp.config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
	var tstamp = Math.floor(Date.now() / 1000);
	$routeProvider.when('/', {
		templateUrl: '/js/angular/reports-basic/partials/main.html?t=' + tstamp,
		controller: 'HomeCtrl'
	});
	
	$locationProvider.html5Mode(false).hashPrefix('!');
}]);

/**
 * Create html injection filter
 */
frontBasicReportViewerApp.filter("sanitize", ['$sce', function($sce) {
	  return function(htmlCode){
	    return $sce.trustAsHtml(htmlCode);
	  }
}]);	