'use strict';

var formsAdminAppServices = angular.module('formsAdminAppServices', ['ngResource']);

formsAdminAppServices.factory('FormAdminPageService', ['$resource', function ($resource) {
	return $resource('/front/form/admin/form/ajax-request', {}, {
		get: {method: 'GET', cache: false, isArray: false},
		post: {method: 'POST', cache: false, isArray: false},
	});
}]);