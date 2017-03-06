'use strict';

var viralFormPageAppServices = angular.module('viralFormPageAppServices', ['ngResource']);

viralFormPageAppServices.factory('ViralFormPageAppServices', ['$resource', function ($resource) {
	return $resource('/forms/vf-ajax-request', {}, {
		get: {method: 'GET', cache: false, isArray: false},
		post: {method: 'POST', cache: false, isArray: false},
	});
}]);