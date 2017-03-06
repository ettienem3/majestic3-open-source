'use strict';

var testJourneysAppServices = angular.module('testJourneysAppServices', ['ngResource']);

testJourneysAppServices.factory('TestJourneysPageService', ['$resource', function ($resource) {
	return $resource('/front/comms/admin/test-journeys/ajax-request', {}, {
		get: {method: 'GET', cache: false, isArray: false},
		post: {method: 'POST', cache: false, isArray: false}
	});
}]);