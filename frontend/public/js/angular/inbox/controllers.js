'use strict';

var inboxControllers = angular.module('inboxControllers', []);

inboxControllers.controller('HomeCtrl', ['$scope', '$route', '$routeParams', '$window', 'InboxPageService', 'promiseTracker', function HomeCtrl($scope, $route, $routeParams, $window, InboxPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.objPageConfig = global_page_config;
	$scope.objRecords = [];
	$scope.inboxMessageContent = '';
	
	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	
	//panels
	$scope.readMessagePanel = false;
	
	//pagination
	$scope.previousPage = 1;
	$scope.currentPage = 1;
	$scope.pageSize = 9;
$scope.objPageConfig.pagination.qp_limit = 9;
	//handle clicks on paginator
	$scope.pageChangeHandler = function (page) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if (page == $scope.previousPage)
		{
			return;
		}//end if
		
		$scope.previousPage = -1;
		
		$scope.pageContent = global_wait_image;
		$scope.objRecords = [];
		
		var start_number = 0;
		if (typeof $scope.objPageConfig.pagination.page_urls !== "undefined" && page > 0)
		{
			if (typeof $scope.objPageConfig.pagination.page_urls[parseInt(page - 1)] !== "undefined")
			{
				start_number  = $scope.objPageConfig.pagination.page_urls[parseInt(page - 1)].next;	
			}//end if
		}//end if
		
		InboxPageService.get({acrq: 'index', 'qp_limit': $scope.objPageConfig.pagination.qp_limit, 'qp_start': start_number}, 
				function success(response) {
					angular.forEach(response.objData, function (obj, i) {
						if (i > -1 && obj.id > 0)
						{
							$scope.objRecords.push(obj);
						}//end if
					});
					
					$scope.pageContent = '';
					$scope.objPageConfig.pagination = response.objData.hypermedia.pagination;
					$scope.objPageConfig.pagination.tpages = Array();
					for (var i = 0; i < response.objData.hypermedia.pagination.pages_total; i++)
					{
						$scope.objPageConfig.pagination.tpages.push({i:i});
					}//end for
				},
				function error(errorResponse) {
					logToConsole(response);
					$scope.pageContent = '';
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	};
	
	//load default content
	$scope.loadRecords = function () {	
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		InboxPageService.get({acrq: 'index', 'qp_limit': 9}, 
			function success(response) {
				angular.forEach(response.objData, function (obj, i) {
					if (i > -1 && obj.id > 0)
					{
						$scope.objRecords.push(obj);
					}//end if
				});
				
				$scope.pageContent = '';
				$scope.objPageConfig.pagination = response.objData.hypermedia.pagination;
				$scope.objPageConfig.pagination.tpages = Array();
				for (var i = 0; i < response.objData.hypermedia.pagination.pages_total; i++)
				{
					$scope.objPageConfig.pagination.tpages.push({i:i});
				}//end for				
			},
			function error(errorResponse) {
				logToConsole(response);
				$scope.pageContent = '';
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
	};	
	
	$scope.togglePanel = function (panel, status) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope[panel] = status;
		
		if (status == true)
		{
			doCreateSlidePanel({});
		} else {
			doRemoveSlidePanel({});
		}//end if
	};
	
	$scope.viewReplyContent = function (id) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		//toggle panel
		$scope.togglePanel('readMessagePanel', true);
		
		//find record
		angular.forEach($scope.objRecords, function (objRecord, i) {
			if (objRecord.id == id)
			{
				var str = '<h5>Reply Content</h5><hr/>';
				str = str + '<p>' + objRecord.inbox_content + '</p><hr/>';
				$scope.inboxMessageContent = str;	// Inititate the promise tracker to track form submissions.
				$scope.progress = promiseTracker();
			};
		});
	};
	
	//load comm content
	$scope.loadCommContent = function (id) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		//toggle panel
		$scope.togglePanel('readMessagePanel', true);
		$scope.inboxMessageContent = '';
		
		//request comm content from api
		var $promise = InboxPageService.get({id: id, acrq: 'load-comm-content'}, 
				function success(response) {
					logToConsole(response);
					
					var objDefaultValues = {
							journeys_journey: {value: 'Data not available'},
							comm_history_tstamp: {value: 'Data not available'},
							comm_history_destination: {value: 'Data not available'},
							comm_history_subject: {value: 'Data not available'},
							comms_reply_to: {value: 'Data not available'},
							comms_content: {value: 'Data not available'},
							comms_comm_from: {value: 'Data not available'},
							comms_comm_from_name: {value: 'Data not available'},
							comm_content_content: {value: 'Data not available, it might be archived'},
							comm_content_source: {value: ''},
							comm_content_bcc: {value: ''},
							comm_content_cc: {value: ''},
							comm_content_subject: {value: 'Data is not available'},
					};
					
					angular.forEach(objDefaultValues, function(obj, i) {
						if (response.objData[i] == '' || response.objData[i] == false || response.objData[i] == null)
						{
							response.objData[i] = obj.value;
						};
					});
					
					//add content to panel
					var header = angular.element('<div></div>').html('<div><strong>Journey: ' + response.objData.journeys_journey + '</strong></div>');
					header.append('<div><strong>Sent on:</strong> ' + response.objData.comm_history_tstamp + '</div><hr/>');
					header.append('<div><strong>From:</strong> ' + response.objData.comm_content_source + '</div>');
					header.append('<div><strong>Reply to:</strong> ' + response.objData.comms_reply_to + '</div>');
					header.append('<div><strong>To:</strong> ' + response.objData.comm_history_destination + '</div>');
					header.append('<div><strong>Cc:</strong> ' + response.objData.comm_content_cc + '</div>');
					header.append('<div><strong>Bcc:</strong> ' + response.objData.comm_content_bcc + '</div>');
					header.append('<div><strong>Subject:</strong> ' + response.objData.comm_content_subject + '</div>');
					
					var body = angular.element('<div></div>').html('<hr/>');
					body.append('<div>' + response.objData.comm_content_content + '</div>');
					
					var message = angular.element('<div></div>').append(header).append(body);
					$scope.inboxMessageContent = message.html();
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					$scope.inboxMessageContent = 'An error has occured and data could not be loaded.';
				}
			);
			
		  // Track the request and show its progress to the user.
		  $scope.progress.addPromise($promise);
	};
	
	$scope.deleteInboxItem = function (id) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		 var r = confirm("Are you sure you want to delete this item?");
		 if (r == true)
		 {
			 InboxPageService.get({id: id, acrq: 'delete-inbox-item'}, 
				function success(response) {
				 	logToConsole(response);
				 	doInfoAlert('Inbox Operation', '<p>Message has been removed.</p>');
			 	},
			 	function error(errorResponse) {
			 		logToConsole(response);
			 		doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			 	}
			 );
		 }//end if
	};	
}]);