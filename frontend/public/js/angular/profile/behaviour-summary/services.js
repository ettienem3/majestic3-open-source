'use strict';

var behaviourAppServices = angular.module('behaviourAppServices', ['ngResource']);

behaviourAppServices.factory('BehaviourSummaryPageService', ['$resource', function ($resource) {
	return $resource('/front/behaviours/summary/ajax-request', {}, {
		get: {method: 'GET', cache: false, isArray: false},
		post: {method: 'POST', cache: false, isArray: false}
	});
}]);