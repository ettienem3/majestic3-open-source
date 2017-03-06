'use strict';

var formTemplateAppServices = angular.module('formTemplateAppServices', ['ngResource']);

formTemplateAppServices.factory('FormTemplatePageService', ['$resource', function ($resource) {
	return $resource('/front/form/templates/ajax-request', {}, {
		get: {method: 'GET', cache: false, isArray: false},
		post: {method: 'POST', cache: false, isArray: false}
	});
}]);