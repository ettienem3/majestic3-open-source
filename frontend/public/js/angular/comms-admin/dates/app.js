'use strict';

var frontJourneyDatesApp = angular.module('frontJourneyDatesApp', ['ngRoute', 
                                                                             'ngSanitize', 
                                                                             'ngAnimate', 
                                                                             'formly', 
                                                                             'formlyBootstrap', 
                                                                             'journeyDatesControllers', 
                                                                             'basicJourneyDatesAppServices', 
                                                                             'ajoslin.promise-tracker', 
                                                                             'angularUtils.directives.dirPagination']);

frontJourneyDatesApp.config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
	var tstamp = Math.floor(Date.now() / 1000);
	$routeProvider.when('/', {
		templateUrl: '/js/angular/comms-admin/dates/partials/main.html?t=' + tstamp,
		controller: 'HomeCtrl'
	});
	
	$locationProvider.html5Mode(false).hashPrefix('!');
}]);

/**
 * Create html injection filter
 */
frontJourneyDatesApp.filter("sanitize", ['$sce', function($sce) {
	  return function(htmlCode){
	    return $sce.trustAsHtml(htmlCode);
	  }
}]);	