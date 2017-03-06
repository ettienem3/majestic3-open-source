'use strict';

var frontEndApp = angular.module('frontEndFormTemplateAdminApp', [
													'ngRoute', 
													'ngSanitize', 
													'ngAnimate', 
													'formly', 
													'formlyBootstrap', 
													'formTemplateControllers', 
													'formTemplateAppServices', 
													'ajoslin.promise-tracker', 
													'angularUtils.directives.dirPagination',
                                                    'ui.tinymce',
												]);

frontEndApp.config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
	var tstamp = Math.floor(Date.now() / 1000);
	$routeProvider.when('/', {
		templateUrl: '/js/angular/forms-admin-templates/partials/main.html?t=' + tstamp,
		controller: 'HomeCtrl'
	})
	.when('/content/:record_id', {
		templateUrl: '/js/angular/forms-admin-templates/partials/content.html?t=' + tstamp,
		controller: 'ContentCtrl'
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

frontEndApp.config(function(formlyConfigProvider) {
    formlyConfigProvider.setType({
      name: 'tinymce',
      templateUrl: 'textarea-tinymce.html',
      //
      wrapper: ['bootstrapLabel']
    });
});