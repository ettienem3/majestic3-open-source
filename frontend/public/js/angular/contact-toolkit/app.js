'use strict';

if (typeof global_page_config !== "undefined")
{
	globalPageConfig(global_page_config);
}//end if

var frontEndContactToolkitApp = angular.module('frontEndContactToolkitApp', ['ngRoute', 
                                                                             'ngSanitize', 
                                                                             'ngAnimate', 
                                                                             'formly', 
                                                                             'formlyBootstrap', 
                                                                             'contactToolkitControllers', 
                                                                             'contactToolkitAppServices', 
                                                                             'ajoslin.promise-tracker', 
                                                                             'angularUtils.directives.dirPagination']);

frontEndContactToolkitApp.config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
	var tstamp = Math.floor(Date.now() / 1000);
	$routeProvider.when('/', {
		templateUrl: '/js/angular/contact-toolkit/partials/main.html?t=' + tstamp,
		controller: 'HomeCtrl'
	})
	.when('/toolkit/data', {
		templateUrl: '/js/angular/contact-toolkit/partials/data.html?t=' + tstamp,
		controller: 'ContactDataCtrl'
	})
	.when('/toolkit/comments', {
		templateUrl: '/js/angular/contact-toolkit/partials/comments.html?t=' + tstamp,
		controller: 'CommentsCtrl'
	})
	.when('/toolkit/forms-completed', {
		templateUrl: '/js/angular/contact-toolkit/partials/forms-completed.html?t=' + tstamp,
		controller: 'FormsCompletedCtrl'
	})
	.when('/toolkit/viral', {
		templateUrl: '/js/angular/contact-toolkit/partials/viral.html?t=' + tstamp,
		controller: 'ContactViralCtrl'
	})
	.when('/toolkit/journeys', {
		templateUrl: '/js/angular/contact-toolkit/partials/journeys.html?t=' + tstamp,
		controller: 'JourneysCtrl'
	})
	.when('/toolkit/statuses', {
		templateUrl: '/js/angular/contact-toolkit/partials/statuses.html?t=' + tstamp,
		controller: 'StatusesCtrl'
	})
	.when('/toolkit/to-do', {
		templateUrl: '/js/angular/contact-toolkit/partials/tasks.html?t=' + tstamp,
		controller: 'TasksCtrl'
	})
	.when('/toolkit/trackers', {
		templateUrl: '/js/angular/contact-toolkit/partials/trackers.html?t=' + tstamp,
		controller: 'TrackersCtrl'
	})
	.when('/toolkit/journey-history/:contact_id/:journey_id', {
		templateUrl: '/js/angular/contact-toolkit/partials/journey-history.html?t=' + tstamp,
		controller: 'JourneyHistoryCtrl'
	})
	.when('/toolkit/journey-episode-history/:contact_id/:journey_id', {
		templateUrl: '/js/angular/contact-toolkit/partials/journey-episodes-history.html?t=' + tstamp,
		controller: 'JourneyHistoryCtrl'
	});
	
	$locationProvider.html5Mode(false).hashPrefix('!');
}]);

/**
 * Create html injection filter
 */
frontEndContactToolkitApp.filter("sanitize", ['$sce', function($sce) {
	  return function(htmlCode){
	    return $sce.trustAsHtml(htmlCode);
	  }
}]);

