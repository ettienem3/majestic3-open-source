'use strict';

var linksAppServices = angular.module('linksAppServices', ['ngResource']);

linksAppServices.factory('LinksPageService', ['$resource', function ($resource) {
	return $resource('/front/links/ajax-request', {}, {
		get: {method: 'GET', cache: false, isArray: false},
		post: {method: 'POST', cache: false, isArray: false},
		editRecord: {method: 'POST', cache: false, isArray: false},
		createRecord: {method: 'POST', cache: false, isArray: false},
		deleteRecord: {method: 'GET', cache: false, isArray: false},
	});
}]);