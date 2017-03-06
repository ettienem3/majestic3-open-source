'use strict';

var bulkSendAppServices = angular.module('bulkSendAppServices', ['ngResource']);

bulkSendAppServices.factory('BulkSendPageService', ['$resource', function ($resource) {
	return $resource('/front/comms/bulksend/admin/ajax-request', {}, {
		get: {method: 'GET', cache: false, isArray: false},
		post: {method: 'POST', cache: false, isArray: false}
	});
}]);

bulkSendAppServices.factory('JourneysPageService', ['$resource', function ($resource) {
	return $resource('/front/comms/admin/journeys/ajax-request', {}, {
		get: {method: 'GET', cache: false, isArray: false}
	});
}]);