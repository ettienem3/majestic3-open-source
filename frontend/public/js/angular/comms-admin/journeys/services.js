'use strict';

var journeysAppServices = angular.module('journeysAppServices', ['ngResource']);

journeysAppServices.factory('JourneysPageService', ['$resource', function ($resource) {
	return $resource('/front/comms/admin/journeys/ajax-request', {}, {
		get: {method: 'GET', cache: false, isArray: false},
		post: {method: 'POST', cache: false, isArray: false}
	});
}]);

journeysAppServices.factory('JourneyEpisodesPageService', ['$resource', function ($resource) {
	return $resource('/front/comms/admin/comms/0000/ajax-request', {}, {
		get: {method: 'GET', cache: false, isArray: false},
		post: {method: 'POST', cache: false, isArray: false}
	});
}]);