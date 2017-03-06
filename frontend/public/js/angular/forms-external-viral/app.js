'use strict';

var formExternalViralApp = angular.module('formExternalViralApp', ['ngRoute', 'ngSanitize', 'ngAnimate', 'formly', 'formlyBootstrap', 'viralFormControllers', 'viralFormPageAppServices', 'ajoslin.promise-tracker', 'angularUtils.directives.dirPagination']);

formExternalViralApp.config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
	var tstamp = Math.floor(Date.now() / 1000);
	$routeProvider.when('/', {
		templateUrl: '/js/angular/forms-external-viral/partials/main.html?t=' + tstamp,
		controller: 'ViralHomeCtrl'
	});
	
	$locationProvider.html5Mode(false).hashPrefix('!');
}]);

/**
 * Create html injection filter
 */
formExternalViralApp.filter("sanitize", ['$sce', function($sce) {
	  return function(htmlCode){
	    return $sce.trustAsHtml(htmlCode);
	  }
}]);