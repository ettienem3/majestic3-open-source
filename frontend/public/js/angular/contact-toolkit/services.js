'use strict';

var contactToolkitAppServices = angular.module('contactToolkitAppServices', ['ngResource']);

contactToolkitAppServices.factory('ContactsPageService', ['$resource', function ($resource) {
	return $resource('/front/contacts/ajax-request', {}, {
		get: {method: 'GET', cache: false, isArray: false},
		post: {method: 'POST', cache: false, isArray: false},
	});
}]);

contactToolkitAppServices.factory('ContactToolkitPageService', ['$resource', function ($resource) {
	return $resource('/front/contact/toolkit/ajax-request/0', {}, {
		//general
		get: {method: 'GET', cache: false, isArray: false},
		post: {method: 'POST', cache: false, isArray: false},
		
		//comments
		getComments: {method: 'GET', cache: false, isArray: false},
		editComment: {method: 'POST', cache: false, isArray: false},
		createComment: {method: 'POST', cache: false, isArray: false},
		deleteComment: {method: 'GET', cache: false, isArray: false},
		
		//forms completed
		getFormsCompleted: {method: 'GET', cache: false, isArray: false},
		
		//journeys
		getContactJourneys: {method: 'GET', cache: false, isArray: false},
		startContactJourney: {method: 'GET', cache: false, isArray: false},
		stopContactJourney: {method: 'GET', cache: false, isArray: false},
		restartContactJourney: {method: 'GET', cache: false, isArray: false},
		
		//statuses
		getContactStatuses: {method: 'GET', cache: false, isArray: false},
		setContactStatus: {method: 'POST', cache: false, isArray: false},
		
		//trackers
		getContactTracker: {method: 'GET', cache: false, isArray: false},
		getContactTrackers: {method: 'GET', cache: false, isArray: false},
		createContactTracker: {method: 'POST', cache: false, isArray: false},
		updateContactTracker: {method: 'POST', cache: false, isArray: false},
		updateContactTrackerStatus: {method: 'POST', cache: false, isArray: false},
		deleteContactTracker: {method: 'GET', cache: false, isArray: false},
		
		//tasks
		getContactTasks: {method: 'GET', cache: false, isArray: false},
		createContactTask: {method: 'POST', cache: false, isArray: false},
		updateContactTask: {method: 'POST', cache: false, isArray: false},
	});
}]);
