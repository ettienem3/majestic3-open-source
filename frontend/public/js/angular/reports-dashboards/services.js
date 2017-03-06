'use strict';

var dashboardReportsAppServices = angular.module('dashboardReportsAppServices', ['ngResource']);

dashboardReportsAppServices.factory('DashboardReportPageService', ['$resource', function ($resource) {
	return $resource('/front/reports/basic/view/ajax-dashboards-request', {}, {
		get: {method: 'GET', cache: false, isArray: false},
		post: {method: 'POST', cache: false, isArray: false}
	});
}]);