'use strict';

var goFetchAppServices = angular.module('goFetchAppServices', ['ngResource']);

goFetchAppServices.factory('GoFetchPageService', ['$resource', function ($resource) {
	return $resource('/go-fetch/view/ajax-request', {}, {
		get: {method: 'GET', cache: false, isArray: false},
		post: {method: 'POST', cache: false, isArray: false},
	});
}]);