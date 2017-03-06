'use strict';

var frontDashboardReportViewerApp = angular.module('frontDashboardReportViewerApp', ['ngRoute', 
                                                                             'ngSanitize', 
                                                                             'ngAnimate', 
                                                                             'formly', 
                                                                             'formlyBootstrap', 
                                                                             'dashboardReportsControllers', 
                                                                             'dashboardReportsAppServices', 
                                                                             'ajoslin.promise-tracker', 
                                                                             'angularUtils.directives.dirPagination']);

frontDashboardReportViewerApp.config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
	var tstamp = Math.floor(Date.now() / 1000);
	$routeProvider.when('/', {
		templateUrl: '/js/angular/reports-dashboards/partials/main.html?t=' + tstamp,
		controller: 'HomeCtrl'
	});
	
	$locationProvider.html5Mode(false).hashPrefix('!');
}]);

/**
 * Create html injection filter
 */
frontDashboardReportViewerApp.filter("sanitize", ['$sce', function($sce) {
	  return function(htmlCode){
	    return $sce.trustAsHtml(htmlCode);
	  }
}]);	