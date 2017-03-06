'use strict';

var basicJourneyDatesAppServices = angular.module('basicJourneyDatesAppServices', ['ngResource']);

basicJourneyDatesAppServices.factory('JourneyDatesPageService', ['$resource', function ($resource) {
	return $resource('/front/comms/admin/dates/ajax-request', {}, {
		get: {method: 'GET', cache: false, isArray: false},
		post: {method: 'POST', cache: false, isArray: false}
	});
}]);