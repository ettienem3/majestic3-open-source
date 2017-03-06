'use strict';

var inboxAppServices = angular.module('inboxAppServices', ['ngResource']);

inboxAppServices.factory('InboxPageService', ['$resource', function ($resource) {
	return $resource('/front/inbox/manager/ajax-request', {}, {
		get: {method: 'GET', cache: false, isArray: false},
	});
}]);