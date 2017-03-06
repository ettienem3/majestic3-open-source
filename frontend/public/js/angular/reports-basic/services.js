'use strict';

var basicReportsAppServices = angular.module('basicReportsAppServices', ['ngResource']);

basicReportsAppServices.factory('ReportPageService', ['$resource', function ($resource) {
	return $resource('/front/reports/basic/view/ajax-request', {}, {
		get: {method: 'GET', cache: false, isArray: false},
		post: {method: 'POST', cache: false, isArray: false}
	});
}]);

basicReportsAppServices.factory('BasicReportPageService', ['$resource', function ($resource) {
	return $resource('/front/reports/basic/view/ajax-ang-request-basic-reports', {}, {
		get: {method: 'GET', cache: false, isArray: false},
		post: {method: 'POST', cache: false, isArray: false}
	});
}]);