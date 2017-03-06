'use strict';

var contactsPageAppServices = angular.module('contactsPageAppServices', ['ngResource']);

contactsPageAppServices.factory('ContactsPageService', ['$resource', function ($resource) {
	return $resource('/front/contacts/ajax-request', {}, {
		get: {method: 'GET', cache: false, isArray: false},
		post: {method: 'POST', cache: false, isArray: false},
	});
}]);