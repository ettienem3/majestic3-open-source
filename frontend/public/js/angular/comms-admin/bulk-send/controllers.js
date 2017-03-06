'use strict';

var bulkSendControllers = angular.module('bulkSendControllers', []);

bulkSendControllers.controller('HomeCtrl', [
											'$scope', 
											'$route', 
											'$routeParams', 
											'$window', 
											'BulkSendPageService', 
											'promiseTracker', 
											function HomeCtrl($scope, $route, $routeParams, $window, BulkSendPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.objPageConfig = global_page_config;
	$scope.objRecords = [];
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	
	$scope.init = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.loadRecords();
	}; //end function
	
	$scope.loadRecords = function (objRequest) {
		return loadRecords(objRequest);
	};
	
	$scope.setRequestStatus = function (objRequest) {
		//check if request was cancelled
		if (objRequest.datetime_cancelled != '0000-00-00 00:00:00')
		{
			return '<span class="text-danger">Cancelled</span>';
		}//end if
		
		//check if request has been started (already approved)
		if (objRequest.datetime_started != '0000-00-00 00:00:00' && objRequest.datetime_ended == '0000-00-00 00:00:00')
		{
			return '<span class="text-info">Busy processing</span>';
		}//end if
		
		if (objRequest.datetime_ended != '0000-00-00 00:00:00')
		{
			return '<span class="text-success">Completed</span>';
		}//end if
		
		if (objRequest.datetime_approved != '0000-00-00 00:00:00' && objRequest.datetime_approved_admin == '0000-00-00 00:00:00')
		{
			return '<span class="text-warning">Final Approval Pending</span>';
		}//end if
		
		if (objRequest.datetime_approved_admin != '0000-00-00 00:00:00')
		{
			return '<span class="text-success">Approved</span>';
		}//end if
		
		return '<span class="text-danger">Pending Approval</span>';
	};
	
	/**
	 * Retrieve a list of pending requests
	 */
	function loadRecords(objRequest) {
		if(typeof objRequest == 'undefined')
		{
			var objRequest = {
				acrq: 'load-pending-requests',	
			};
		}//end if
		
		$scope.objRecords = Array();
		var $p = BulkSendPageService.get(objRequest, 
			function success(response) {
				logToConsole(response);
				//check for errors
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					doErrorAlert('Unable to load pending requests', '<p>Requests could not be loaded, process failed with response : ' + response.response + '</p>');
				}//end if
				
				angular.forEach(response.objData, function (objR, i) {
					$scope.objRecords.push(objR);
				});
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		$scope.progress.addPromise($p);
	}; //end function
}]);

/**
 * Create a new bulk send request
 */
bulkSendControllers.controller('RequestCreateCtrl', [
											'$scope', 
											'$route', 
											'$routeParams', 
											'$window', 
											'BulkSendPageService',
											'JourneysPageService',
											'promiseTracker', 
											function RequestApproveCtrl($scope, $route, $routeParams, $window, BulkSendPageService, JourneysPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.request_reconstruct_message = '';
	$scope.objPageConfig = global_page_config;
	$scope.objJourneys = Array();
	$scope.objContactStatuses = Array();
	$scope.objWebForms = Array();
	$scope.objStandardFields = Array();
	$scope.objStandardFieldConfig = {};
	$scope.objCustomFields = Array();
	$scope.objCustomFieldConfig = {};
	$scope.objTrackers = Array();
	$scope.objUsers = Array();
	$scope.objInitialTargetResult = false;
	$scope.objTargetResult = {};
	$scope.objTempValueModel = false;
	$scope.objTempOperatorModel = false;
	
	//container gathering information
	$scope.objBulkRequest = {
			id: false,
			status: 0, //0 = creatring, 1 = approved, 2 = released, 99 = cancelled
			objJourney: {},
			objHasStatuses: [],
			objNotHaveStatuses: [],
			objHasWebForm: [],
			objNotHaveWebForm: [],
			objHasTracker: [],
			objNotHaveTracker: [],
			objHasUser: [],
			objNotHaveUser: [],
			objStandardFields: [],
			objCustomFields: [],
			objOptions: {
				allocate_all: 1,
				allocate_num: 0,
				allocate_new: 0,
			},
	};
	
	//models
	$scope.selectJourneyModelActive = {};
	$scope.selectJourneyModelInactive = {};
	
	$scope.selectJourneyPanelState = false;
	$scope.configureSendSettingsPanelState = false;
	$scope.selectContactStatusPanelState = false;
	$scope.selectContactWebFormPanelState = false;
	$scope.selectContactUsersPanelState = false;
	$scope.selectContactTrackerPanelState = false;
	$scope.selectContactStandardFieldsPanelState = false;
	$scope.selectContactCustomFieldsPanelState = false;
	$scope.selectContactStandardFieldConfigPanelState = false;
	$scope.selectContactCustomFieldConfigPanelState = false;
	$scope.calculateTargetGroupPanelState = false;
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	$scope.progressCalcTotals = promiseTracker();
	
	$scope.init = function () {
		/**
		* Make sure user is logged in
		*/
		userIsLoggedin();

		//check if this is review action
		if (typeof $routeParams.request_id != 'undefined' && $routeParams.request_id > 0)
		{
			$scope.objBulkRequest.id = $routeParams.request_id;
			logToConsole('Start rebuilding the request');
			rebuildRequest();
		} else {
			//normal operation
			loadJourneys();
		}//end if
	}; //end function
	
	$scope.togglePanel = function (panel, status, id) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		//check if request has been created
		if ($scope.objBulkRequest.id != false)
		{
			switch (panel)
			{
				case 'calculateTargetGroupPanelState':
					//allow panel
					break;
					
				default:
					//check request status
					if (isRequestCreated() == false) {return false;}
					break;
			}//end switch
		}//end if
		
		var flag = true;
		if (status == true || status == false)
		{
			$scope[panel] = status;
		} else {
			$scope[panel] = !$scope[panel];
		}//end if
		flag = $scope[panel];
		
		switch (panel)
		{
			case 'selectJourneyPanelState':
				
				break;
				
			case 'selectContactStatusPanelState':
				if ($scope.objContactStatuses.length < 2 && flag == true)
				{
					loadStatuses();
					//wait a second or 2 and then apply data
					setTimeout(function () {
						$scope.$apply();
					}, 3000);
				}//end if
				break;
				
			case 'selectContactWebFormPanelState':
				if ($scope.objWebForms.length < 2 && flag == true)
				{
					loadWebForms();
					//wait a second or 2 and then apply data
					setTimeout(function () {
						$scope.$apply();
					}, 3000);
				}//end if
				break;
				
			case 'selectContactUsersPanelState':
				if ($scope.objUsers.length < 2 && flag == true)
				{
					loadUsers();
					//wait a second or 2 and then apply data
					setTimeout(function () {
						$scope.$apply();
					}, 3000);
				}//end if
				break;
				
			case 'selectContactTrackerPanelState':
				if ($scope.objTrackers.length == 0 && flag == true)
				{
					loadTrackers();
					//wait a second or 2 and then apply data
					setTimeout(function () {
						$scope.$apply();
					}, 3000);
				}//end if
				break;
			
			case 'calculateTargetGroupPanelState':
				if (flag == true)
				{
					fetchTargetGroupTotals();
				}//end if
				break;
				
			case 'selectContactStandardFieldsPanelState':
				if ($scope.objStandardFields.length == 0 && flag == true)
				{
					loadStandardFields();
				}//end if
				break;
				
			case 'selectContactCustomFieldsPanelState':
				if ($scope.objCustomFields.length == 0 && flag == true)
				{
					loadCustomFields();
				}//end if
				break;
		}//end switch
		
		if (flag == true)
		{
			doCreateSlidePanel({});
		} else {
			doRemoveSlidePanel({});
		}//end if
	};//end function

	$scope.setSelectedJourney = function () {
		//check request status
		if (isRequestCreated() == false) {return false;}
		
		var journey_id = false; 
		
		//check active journeys first
		if ($scope.selectJourneyModelActive != "undefined" && $scope.selectJourneyModelActive > 0)
		{
			journey_id = $scope.selectJourneyModelActive;
		}//end if
		
		if ($scope.selectJourneyModelInactive != "undefined" && $scope.selectJourneyModelInactive > 0 && journey_id == false)
		{
			journey_id = $scope.selectJourneyModelInactive;
		}//end if
		
		//reset the models
		$scope.objBulkRequest.objJourney = {};
		$scope.selectJourneyModelActive = {};
		$scope.selectJourneyModelInactive = {};
		
		//find journey in data
		angular.forEach($scope.objJourneys, function (objJourney, i) {
			if (objJourney.id == journey_id)
			{
				$scope.objBulkRequest.objJourney = objJourney;
			}//end if
		});
		
		//change button color
		angular.element('.select_btn_journey').toggleClass('btn-primary btn-success');
		
		//close the panel
		$scope.togglePanel('selectJourneyPanelState', false);
		
		//trigger calculation
		fetchTargetGroupTotals();
	}; //end function
	
	/**
	 * Submit send request for approval
	 */
	$scope.submitRequestForApproval = function () {
		//confirm action
		if (confirm('Are you sure you want to create the request? Changes can no longer be made once the request has been created.') != true)
		{
			return;
		}//end if
		
		if ($scope.objBulkRequest.id == true)
		{
			doErrorAlert('Request has been sent', '<p>The request has been created, further changes are not allowed.</p>');
			return false;
		}//end if
		
		$scope.objBulkRequest.id = true;
		$scope.objBulkRequest.acrq = 'create-send-request';
		$scope.objBulkRequest.journey_id = $scope.objBulkRequest.objJourney.id;
		
		var $p = BulkSendPageService.post($scope.objBulkRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to create request', '<p>Process failed with response : ' + response.response + '</p>');
					}//end if

					//doMessageAlert('Send request submitted', '<p>Your request has been submitted.<br/><a href="/front/comms/bulksend/admin#!/" title="View Requests">Back to requests</a></p>');
					//reload window to update approval steps and data
					var url = '/front/comms/bulksend/admin#!/review/' + response.objData.id;
					$window.location.href = url;
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);
		
		$scope.progress.addPromise($p);
	};
	
	$scope.cancelRequest = function () {
		if (confirm('Are you sure you wish to cancel this request? Once cancelled, it can no longer be updated.') != true)
		{
			return false;
		}//end if
		
		var objRequest = {
				acrq: 'cancel-send-request',
				request_id: $scope.objBulkRequest.id
		};
		
		var $p = BulkSendPageService.post(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to cancel', '<p>Process failed with response : ' + response.response + '</p>');
					}//end if

					$scope.objBulkRequest.status = 99;
					doInfoAlert('Operation has completed', '<p>The Request has been cancelled successfully</p>');
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);
		
		$scope.progress.addPromise($p);
	}; //end function
	
	$scope.approveRequest = function () {
		if (confirm('Are you sure you wish to approve this request? Once approved, it can no longer be updated.') != true)
		{
			return false;
		}//end if
		
		var objRequest = {
				acrq: 'approve-send-request',
				request_id: $scope.objBulkRequest.id
		};
		
		var $p = BulkSendPageService.post(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to cancel', '<p>Process failed with response : ' + response.response + '</p>');
					}//end if

					$scope.objBulkRequest.status = 1;
					doInfoAlert('Operation has completed', '<p>The Request has sent for release.</p>');
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);
		
		$scope.progress.addPromise($p);		
	}; //end function
	
	$scope.finalApproveRequest = function () {
		if (confirm('Are you sure you wish to release this request? Once released, it can no longer be updated. The request will start processing immediatly.') != true)
		{
			return false;
		}//end if
		
		var objRequest = {
				acrq: 'release-send-request',
				request_id: $scope.objBulkRequest.id,
		};
		
		var $p = BulkSendPageService.post(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to cancel', '<p>Process failed with response : ' + response.response + '</p>');
					}//end if

					$scope.objBulkRequest.status = 2;
					doMessageAlert('Operation has completed', '<p>Thank you, the process has been completed. Communications are now being processed in the background and sending will commence as soon as possible.</p>');
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);
		
		$scope.progress.addPromise($p);			
	}; //end function
	
	$scope.duplicateRequest = function () {
		if (confirm('Do you want to create a new request based on this request?') != true)
		{
			return false;
		}//end if
		
		//reset loaded values to duplicate loaded data
		$scope.objBulkRequest.id = false;
		$scope.objBulkRequest.status = 0;
		
	}; //end function
	
	$scope.toggleContactHasStatus = function(objStatus) {
		//check request status
		if (isRequestCreated() == false) {return false;}
		
		var flag_added = false;
		
		//check if status exists
		angular.forEach($scope.objBulkRequest.objHasStatuses, function (objS, i) {
			if (objS.id == objStatus.id)
			{
				//remove from the collection
				$scope.objBulkRequest.objHasStatuses.splice(i, 1);
				flag_added = true;
			}//end if
		});
		
		if (!flag_added)
		{
			//check if status exists
			angular.forEach($scope.objBulkRequest.objNotHaveStatuses, function (objS, i) {
				if (objS.id == objStatus.id)
				{
					//remove from the collection
					$scope.objBulkRequest.objNotHaveStatuses.splice(i, 1);
				}//end if
			});
			
			$scope.objBulkRequest.objHasStatuses.push(objStatus);
		}//end if
		
		//trigger calculation
		fetchTargetGroupTotals();
	}; //end function
	
	$scope.toggleContactNotHasStatus = function(objStatus) {
		//check request status
		if (isRequestCreated() == false) {return false;}
		
		var flag_added = false;
		
		//check if status exists
		angular.forEach($scope.objBulkRequest.objNotHaveStatuses, function (objS, i) {
			if (objS.id == objStatus.id)
			{
				//remove from the collection
				$scope.objBulkRequest.objNotHaveStatuses.splice(i, 1);
				flag_added = true;
			}//end if
		});
		
		if (!flag_added)
		{
			//check if this status is within the has statuses collection
			angular.forEach($scope.objBulkRequest.objHasStatuses, function (objS, i) {
				if (objS.id == objStatus.id)
				{
					//remove from the collection
					$scope.objBulkRequest.objHasStatuses.splice(i, 1);
				}//end if
			});
			
			$scope.objBulkRequest.objNotHaveStatuses.push(objStatus);
		}//end if
		
		//trigger calculation
		fetchTargetGroupTotals();
	}; //end function
	
	$scope.toggleContactHasWebForm = function(objForm) {
		//check request status
		if (isRequestCreated() == false) {return false;}
		
		var flag_added = false;
		
		//check if status exists
		angular.forEach($scope.objBulkRequest.objHasWebForm, function (objF, i) {
			if (objF.id == objForm.id)
			{
				//remove from the collection
				$scope.objBulkRequest.objHasWebForm.splice(i, 1);
				flag_added = true;
			}//end if
		});
		
		if (!flag_added)
		{
			//check if status exists
			angular.forEach($scope.objBulkRequest.objNotHaveWebForm, function (objF, i) {
				if (objF.id == objForm.id)
				{
					//remove from the collection
					$scope.objBulkRequest.objNotHaveWebForm.splice(i, 1);
				}//end if
			});
			
			$scope.objBulkRequest.objHasWebForm.push(objForm);
		}//end if
		
		//trigger calculation
		fetchTargetGroupTotals();
	}; //end function
	
	$scope.toggleContactNotHasWebForm = function(objForm) {
		//check request status
		if (isRequestCreated() == false) {return false;}
		
		var flag_added = false;
		
		//check if status exists
		angular.forEach($scope.objBulkRequest.objNotHaveWebForm, function (objF, i) {
			if (objF.id == objForm.id)
			{
				//remove from the collection
				$scope.objBulkRequest.objNotHaveWebForm.splice(i, 1);
				flag_added = true;
			}//end if
		});
		
		if (!flag_added)
		{
			//check if this status is within the has statuses collection
			angular.forEach($scope.objBulkRequest.objHasWebForm, function (objF, i) {
				if (objF.id == objForm.id)
				{
					//remove from the collection
					$scope.objBulkRequest.objHasWebForm.splice(i, 1);
				}//end if
			});
			
			$scope.objBulkRequest.objNotHaveWebForm.push(objForm);
		}//end if
		
		//trigger calculation
		fetchTargetGroupTotals();
	}; //end function	
	
	$scope.toggleContactHasTracker= function(objForm) {
		//check request status
		if (isRequestCreated() == false) {return false;}
		
		var flag_added = false;
		
		//check if status exists
		angular.forEach($scope.objBulkRequest.objHasTracker, function (objF, i) {
			if (objF.id == objForm.id)
			{
				//remove from the collection
				$scope.objBulkRequest.objHasTracker.splice(i, 1);
				flag_added = true;
			}//end if
		});
		
		if (!flag_added)
		{
			//check if status exists
			angular.forEach($scope.objBulkRequest.objNotHaveTracker, function (objF, i) {
				if (objF.id == objForm.id)
				{
					//remove from the collection
					$scope.objBulkRequest.objNotHaveTracker.splice(i, 1);
				}//end if
			});
			
			$scope.objBulkRequest.objHasTracker.push(objForm);
		}//end if
		
		//trigger calculation
		fetchTargetGroupTotals();
	}; //end function
	
	$scope.toggleContactNotHasTracker = function(objForm) {
		//check request status
		if (isRequestCreated() == false) {return false;}
		
		var flag_added = false;
		
		//check if status exists
		angular.forEach($scope.objBulkRequest.objNotHaveTracker, function (objF, i) {
			if (objF.id == objForm.id)
			{
				//remove from the collection
				$scope.objBulkRequest.objNotHaveTracker.splice(i, 1);
				flag_added = true;
			}//end if
		});
		
		if (!flag_added)
		{
			//check if this status is within the has statuses collection
			angular.forEach($scope.objBulkRequest.objHasTracker, function (objF, i) {
				if (objF.id == objForm.id)
				{
					//remove from the collection
					$scope.objBulkRequest.objHasTracker.splice(i, 1);
				}//end if
			});
			
			$scope.objBulkRequest.objNotHaveTracker.push(objForm);
		}//end if
		
		//trigger calculation
		fetchTargetGroupTotals();
	}; //end function		
	
	$scope.toggleContactHasUser = function(objUser) {
		//check request status
		if (isRequestCreated() == false) {return false;}
		
		var flag_added = false;
		
		//check if status exists
		angular.forEach($scope.objBulkRequest.objHasUser, function (objU, i) {
			if (objU.id == objUser.id)
			{
				//remove from the collection
				$scope.objBulkRequest.objHasUser.splice(i, 1);
				flag_added = true;
			}//end if
		});
		
		if (!flag_added)
		{
			//check if status exists
			angular.forEach($scope.objBulkRequest.objNotHaveUser, function (objU, i) {
				if (objU.id == objUser.id)
				{
					//remove from the collection
					$scope.objBulkRequest.objNotHaveUser.splice(i, 1);
				}//end if
			});
			
			$scope.objBulkRequest.objHasUser.push(objUser);
		}//end if
		
		//trigger calculation
		fetchTargetGroupTotals();
	}; //end function
	
	$scope.toggleContactNotHasUser = function(objUser) {
		//check request status
		if (isRequestCreated() == false) {return false;}
		
		var flag_added = false;
		
		//check if status exists
		angular.forEach($scope.objBulkRequest.objNotHaveUser, function (objU, i) {
			if (objU.id == objUser.id)
			{
				//remove from the collection
				$scope.objBulkRequest.objNotHaveUser.splice(i, 1);
				flag_added = true;
			}//end if
		});
		
		if (!flag_added)
		{
			//check if this status is within the has statuses collection
			angular.forEach($scope.objBulkRequest.objHasUser, function (objU, i) {
				if (objU.id == objUser.id)
				{
					//remove from the collection
					$scope.objBulkRequest.objHasUser.splice(i, 1);
				}//end if
			});
			
			$scope.objBulkRequest.objNotHaveUser.push(objUser);
		}//end if
		
		//trigger calculation
		fetchTargetGroupTotals();
	}; //end function	
	
	$scope.toggleContactStandardFieldConfigPanel = function (objField) {
		//check request status
		if (isRequestCreated() == false) {return false;}
		
		//set field
		$scope.objStandardFieldConfig = objField;
		
		//close the side panel
		$scope.togglePanel('selectContactStandardFieldsPanelState', false);
		
		//display config panel
		$scope.togglePanel('selectContactStandardFieldConfigPanelState', true);
		
		//request field data
		var objRequest = {
			acrq: 'load-standard-field-details',
			field_id: $scope.objStandardFieldConfig.id
		};
		
		var $p = BulkSendPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load Field Data', '<p>Field Data could not be loaded, process failed with response : ' + response.response + '</p>');
					}//end if

					//convert data to proper objects
					var arr_data = Array();
					angular.forEach(response.objData.objField.field_values_data, function (v, k) {
						arr_data.push({
							id: k,
							name: v 
						});
					});
					
					$scope.objStandardFieldConfig.objFieldData = response.objData.objField;
					$scope.objStandardFieldConfig.objFieldData.field_values_data = arr_data;
					
					//save data to fields object for later use
					angular.forEach($scope.objStandardFields, function (objField, i) {
						if (objField.id == $scope.objStandardFieldConfig.id)
						{
							$scope.objStandardFields[i].field_values_data = arr_data;
						}//end if
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);
		
		$scope.progress.addPromise($p);
		
		//trigger calculation
		fetchTargetGroupTotals();
	}; //end function
	
	$scope.toggleContactCustomFieldConfigPanel = function (objField) {
		//check request status
		if (isRequestCreated() == false) {return false;}
		
		//set field
		$scope.objCustomFieldConfig = objField;
		
		//close the side panel
		$scope.togglePanel('selectContactCustomFieldsPanelState', false);
		
		//display config panel
		$scope.togglePanel('selectContactCustomFieldConfigPanelState', true);
		
		//request field data
		var objRequest = {
			acrq: 'load-custom-field-details',
			field_id: $scope.objCustomFieldConfig.id
		};
		
		var $p = BulkSendPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load Field Data', '<p>Field Data could not be loaded, process failed with response : ' + response.response + '</p>');
					}//end if

					$scope.objCustomFieldConfig.objFieldData = response.objData.objField;
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);
		
		$scope.progress.addPromise($p);
		
		//trigger calculation
		fetchTargetGroupTotals();
	}; //end function
	
	$scope.setStandardFieldFilterValue = function (objField, value, operator) {	
		//check request status
		if (isRequestCreated() == false) {return false;}
		
		if (typeof value == 'undefined' && $scope.objTempValueModel != false)
		{
			if (typeof $scope.objTempValueModel == 'object')
			{
				value = $scope.objTempValueModel.id;
			} else {
				value = $scope.objTempValueModel;
			}//end if
		}//end if
		
		if (typeof value == 'undefined')
		{
			switch (objField.field)
			{
				case 'country_id':
					value = "0";
					break;
			}//end switch
		}//end if
		
		if (typeof operator == 'undefined' && $scope.objTempOperatorModel != false)
		{
			operator = $scope.objTempOperatorModel;
		}//end if
		
		var field_exists = false;
		//check if field has been set
		angular.forEach($scope.objBulkRequest.objStandardFields, function (objF, i) {
			if (objF.id == objField.id)
			{
				field_exists = true;
			}//end if
		});
		
		if (!field_exists)
		{
			$scope.objBulkRequest.objStandardFields.push({
				id: objField.id,
				field: objField.field,
				data: objField,
				values: [],
			});
		}//end if
		
		//check operator is set
		if (typeof operator == 'undefined')
		{
			operator = "=";
		}//end if
		
		//find field
		angular.forEach($scope.objBulkRequest.objStandardFields, function (objF, i) {
			if (objF.id == objField.id)
			{
				var value_set = false;
				
				//check if value has already been set
				if (objF.values.length > 0)
				{
					angular.forEach(objF.values, function (objV, i) {
						if (objV.value == value)
						{
							value_set = true;
						}//end if
					});
				}//end if
				
				if (!value_set)
				{
					$scope.objBulkRequest.objStandardFields[i].values.push({
						'value': value,
						'operator': operator,
					});
				}//end if
			}//end if
			console.log($scope.objBulkRequest.objStandardFields[i].values);
		});
		
		//clear model
		$scope.objTempValueModel = false;
		$scope.objTempOperatorModel = false;
		
		//close the panel
		$scope.togglePanel('selectContactStandardFieldConfigPanelState');
		
		//trigger calculation
		fetchTargetGroupTotals();
	};
	
	$scope.readStandardFieldLabel = function (objField, objValue) {
		var label = '';	
		angular.forEach(objField.data.field_values_data, function (objV, i) {
			if ((objV.id * 1) == (objValue.value * 1))
			{
				label = objV.name;
			}//end if
		});
		
		return label;
	}; //end function
	
	$scope.setCustomFieldFilterValue = function (objField, value, operator) {	
		//check request status
		if (isRequestCreated() == false) {return false;}
		
		if (typeof value == 'undefined' && $scope.objTempValueModel != false)
		{
			value = $scope.objTempValueModel;
		}//end if
		
		if (typeof operator == 'undefined' && $scope.objTempOperatorModel != false)
		{
			operator = $scope.objTempOperatorModel;
		}//end if
		
		var field_exists = false;
		//check if field has been set
		angular.forEach($scope.objBulkRequest.objCustomFields, function (objF, i) {
			if (objF.id == objField.id)
			{
				field_exists = true;
			}//end if
		});
		
		if (!field_exists)
		{
			$scope.objBulkRequest.objCustomFields.push({
				id: objField.id,
				field: objField.field,
				data: objField,
				values: [],
			});
		}//end if
		
		//check operator is set
		if (typeof operator == 'undefined')
		{
			operator = "equal";
		}//end if
		
		//find field
		angular.forEach($scope.objBulkRequest.objCustomFields, function (objF, i) {
			if (objF.id == objField.id)
			{
				var value_set = false;
				
				//check if value has already been set
				angular.forEach(objF.values, function (objV, i) {
					if (objV.value == value)
					{
						value_set = true;
					}//end if
				});
				
				if (!value_set)
				{
					$scope.objBulkRequest.objCustomFields[i].values.push({
						'value': value,
						'operator': operator,
					});
				}//end if
			}//end if
		});
		
		//clear model
		$scope.objTempValueModel = false;
		$scope.objTempOperatorModel = false;
		
		//close the panel
		$scope.togglePanel('selectContactCustomFieldConfigPanelState');
		
		//trigger calculation
		fetchTargetGroupTotals();
	};
	
	$scope.deleteCustomFieldFilter = function (objField, index) {
		//check request status
		if (isRequestCreated() == false) {return false;}
		
		$scope.objBulkRequest.objCustomFields.splice(index, 1);
		
		//trigger calculation
		fetchTargetGroupTotals();
	}; //end function
	
	$scope.deleteCustomFieldFilterValue = function (objField, parentIndex, valueIndex) {
		//check request status
		if (isRequestCreated() == false) {return false;}
		
		$scope.objBulkRequest.objCustomFields[parentIndex].values.splice(valueIndex, 1);
		
		//check if values remain, if not, remove the field
		if ($scope.objBulkRequest.objCustomFields[parentIndex].values.length == 0)
		{
			$scope.objBulkRequest.objCustomFields.splice(parentIndex, 1);
			
			//trigger calculation
			fetchTargetGroupTotals();
		}//end if
	};
	
	$scope.deleteStandardFieldFilter = function (objField, index) {
		//check request status
		if (isRequestCreated() == false) {return false;}
		
		$scope.objBulkRequest.objStandardFields.splice(index, 1);
		
		//trigger calculation
		fetchTargetGroupTotals();
	}; //end function
	
	$scope.deleteStandardFieldFilterValue = function (objField, parentIndex, valueIndex) {
		//check request status
		if (isRequestCreated() == false) {return false;}
		
		$scope.objBulkRequest.objStandardFields[parentIndex].values.splice(valueIndex, 1);
		
		//check if values remain, if not, remove the field
		if ($scope.objBulkRequest.objStandardFields[parentIndex].values.length == 0)
		{
			$scope.objBulkRequest.objStandardFields.splice(parentIndex, 1);
			
			//trigger calculation
			fetchTargetGroupTotals();
		}//end if
	};
	
	function fetchTargetGroupTotals() {
		//check if a current request is inprogress
		if ($scope.progressCalcTotals.active() == true && $scope.objBulkRequest.id == false)
		{
			logToConsole('A request is already in progress, request ignored');
			return false;
		}//end if
		
		var objRequest = {
			acrq: 'load-target-group',
			data: $scope.objBulkRequest,
		};
		
		var $p = BulkSendPageService.post(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load summary', '<p>Summary could not be loaded, process failed with response : ' + response.response + '</p>');
					}//end if
					
					$scope.objTargetResult = response.objData;
					
					//run calculate function to update total summary
					formatTargetGroupTotals();
					
					//build charts
					/**
					 * Sources Chart
					 */
					var objConfigSources = {
						chart: {
							renderTo: 'target_by_source',
							type: 'pie',
				            plotBackgroundColor: null,
				            plotBorderWidth: null,
				            plotShadow: false,
						},
						credits: {
							enabled: false
						},
						title: {
							text: 'Target Group by Source',
						},
				        tooltip: {
				            pointFormat: '{series.name}: <b>{point.y}</b>'
				        },
				        plotOptions: {
				            pie: {
				                allowPointSelect: true,
				                cursor: 'pointer',
				                dataLabels: {
				                    enabled: true,
				                    format: '<b>{point.name}</b>: {point.y} ',
				                    style: {
				                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
				                    }
				                }
				            }
				        },
				        series: []
					};
					
					//set data for Source chart
					var arr_data = {
							name: 'Contacts',
							colorByPoint: true,
							data: []
					};
					angular.forEach($scope.objTargetResult.source, function (obj, i) {
						if (obj.registrations_source == '')
						{
							obj.registrations_source = 'Blank';
						}//end if
						
						arr_data.data.push({
							name: obj.registrations_source,
							y: obj.count_contacts * 1,
						});
					});
					objConfigSources.series.push(arr_data);
					
					/**
					 * References Chart
					 */
					var objConfigReferences = {
						chart: {
							renderTo: 'target_by_reference',
							type: 'pie',
				            plotBackgroundColor: null,
				            plotBorderWidth: null,
				            plotShadow: false,
						},
						credits: {
							enabled: false
						},
						title: {
							text: 'Target Group by Reference',
						},
				        tooltip: {
				            pointFormat: '{series.name}: <b>{point.y}</b>'
				        },
				        plotOptions: {
				            pie: {
				                allowPointSelect: true,
				                cursor: 'pointer',
				                dataLabels: {
				                    enabled: true,
				                    format: '<b>{point.name}</b>: {point.y} ',
				                    style: {
				                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
				                    }
				                }
				            }
				        },
				        series: []
					};
					
					//set data for References chart
					var arr_data = {
							name: 'Contacts',
							colorByPoint: true,
							data: []
					};
					angular.forEach($scope.objTargetResult.reference, function (obj, i) {
						if (obj.registrations_reference == '')
						{
							obj.registrations_reference = 'Blank';
						}//end if
						
						arr_data.data.push({
							name: obj.registrations_reference,
							y: obj.count_contacts * 1,
						});
					});
					objConfigReferences.series.push(arr_data);	
					
					/**
					 * Status Chart
					 */
					var objConfigStatuses = {
						chart: {
							renderTo: 'target_by_status',
							type: 'pie',
				            plotBackgroundColor: null,
				            plotBorderWidth: null,
				            plotShadow: false,
						},
						credits: {
							enabled: false
						},
						title: {
							text: 'Target Group by Status',
						},
				        tooltip: {
				            pointFormat: '{series.name}: <b>{point.y}</b>'
				        },
				        plotOptions: {
				            pie: {
				                allowPointSelect: true,
				                cursor: 'pointer',
				                dataLabels: {
				                    enabled: true,
				                    format: '<b>{point.name}</b>: {point.y} ',
				                    style: {
				                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
				                    }
				                }
				            }
				        },
				        series: []
					};
					
					//set data for Status chart
					var arr_data = {
							name: 'Contacts',
							colorByPoint: true,
							data: []
					};
					angular.forEach($scope.objTargetResult.status, function (obj, i) {
						arr_data.data.push({
							name: obj.registration_status_status,
							y: obj.count_contacts * 1,
						});
					});
					objConfigStatuses.series.push(arr_data);	
					
					
					/**
					 * Users Chart
					 */
					var objConfigUsers = {
						chart: {
							renderTo: 'target_by_user',
							type: 'pie',
				            plotBackgroundColor: null,
				            plotBorderWidth: null,
				            plotShadow: false,
						},
						credits: {
							enabled: false
						},
						title: {
							text: 'Target Group by User',
						},
				        tooltip: {
				            pointFormat: '{series.name}: <b>{point.y}</b>'
				        },
				        plotOptions: {
				            pie: {
				                allowPointSelect: true,
				                cursor: 'pointer',
				                dataLabels: {
				                    enabled: true,
				                    format: '<b>{point.name}</b>: {point.y} ',
				                    style: {
				                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
				                    }
				                }
				            }
				        },
				        series: []
					};
					
					//set data for Status chart
					var arr_data = {
							name: 'Contacts',
							colorByPoint: true,
							data: []
					};
					angular.forEach($scope.objTargetResult.status, function (obj, i) {
						arr_data.data.push({
							name: obj.users_uname,
							y: obj.count_contacts * 1,
						});
					});
					objConfigUsers.series.push(arr_data);
					
					//draw charts
					new Highcharts.Chart(objConfigSources);
					new Highcharts.Chart(objConfigReferences);
					new Highcharts.Chart(objConfigStatuses);
					new Highcharts.Chart(objConfigUsers);
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);
		
		$scope.progressCalcTotals.addPromise($p);
		return $p;
	};
	
	function formatTargetGroupTotals() {
		$scope.objTargetResult.objTotals = {};
		angular.forEach($scope.objTargetResult, function (objSegment, i) {
			var total = 0;
			angular.forEach(objSegment, function(objData, ii) {
				total = total + (objData.count_contacts * 1);
			});
			
			switch (i) {
				case 'source':
					$scope.objTargetResult.objTotals.source = {label: 'Source', 'total': total};
					break;
					
				case 'reference':
					$scope.objTargetResult.objTotals.reference = {label: 'Reference', 'total': total};
					break;
					
				case 'status':
					$scope.objTargetResult.objTotals.status = {label: 'Status', 'total': total};
					break;
					
				case 'user':
					$scope.objTargetResult.objTotals.user = {label: 'User', 'total': total};
					break;
			}//end switch
		});
		
		if (!$scope.objInitalTargetResult)
		{
			$scope.objInitalTargetResult = angular.copy($scope.objTargetResult);
		}//end if
	}; //end function
	
	function loadJourneys() {		
		var objRequest = {
			acrq: 'load-journeys',
		};
		
		var $p = JourneysPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load journeys', '<p>Journeys could not be loaded, process failed with response : ' + response.response + '</p>');
					}//end if
					
					angular.forEach(response.objData, function (objJourney, i) {
						if (typeof objJourney.id != 'undefined')
						{
							$scope.objJourneys.push(objJourney);	
						}//end if
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);
		
		$scope.progress.addPromise($p);
		return $p;
	};
	
	function loadStatuses() {		
		var objRequest = {
			acrq: 'load-contact-statuses',
		};
		
		$scope.objContactStatuses = Array();
		var $p = BulkSendPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load statuses', '<p>Statuses could not be loaded, process failed with response : ' + response.response + '</p>');
					}//end if

					angular.forEach(response.objData, function (objStatus, i) {
						if (typeof objStatus.value != 'undefined')
						{
							var objS = {
								name: objStatus.text,								
								id: objStatus.value,
							};
							$scope.objContactStatuses.push(objS);	
						}//end if
					});	
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);
		
		$scope.progress.addPromise($p);
		return $p;
	};	
	
	function loadWebForms() {
		var objRequest = {
				acrq: 'load-web-forms',
			};
			
			$scope.objWebForms = Array();
			var $p = BulkSendPageService.get(objRequest, 
					function success(response) {
						logToConsole(response);
						//check for errors
						if (typeof response.error != 'undefined' && response.error == 1)
						{
							doErrorAlert('Unable to load Web Forms', '<p>Web Forms could not be loaded, process failed with response : ' + response.response + '</p>');
						}//end if

						angular.forEach(response.objData, function (objForm, i) {
							if (typeof objForm.id != 'undefined')
							{
								$scope.objWebForms.push(objForm);	
							}//end if
						});	
					},
					function error(errorResponse) {
						logToConsole(errorResponse);
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					}
			);
			
		$scope.progress.addPromise($p);		
		return $p;
	}; //end function
	
	function loadTrackers() {
		var objRequest = {
				acrq: 'load-trackers',
			};
			
		$scope.objTrackers = Array();
		var $p = BulkSendPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load Trackers', '<p>Trackers could not be loaded, process failed with response : ' + response.response + '</p>');
					}//end if

					angular.forEach(response.objData, function (objForm, i) {
						if (typeof objForm.id != 'undefined')
						{
							$scope.objTrackers.push(objForm);	
						}//end if
					});	
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);
		
		$scope.progress.addPromise($p);	
		return $p;
	}; //end function	
	
	function loadUsers() {
		var objRequest = {
				acrq: 'load-users',
			};
			
		$scope.objUsers = Array();
		var $p = BulkSendPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load Users', '<p>Users could not be loaded, process failed with response : ' + response.response + '</p>');
					}//end if

					angular.forEach(response.objData, function (objUser, i) {
						if (typeof objUser.id != 'undefined' && objUser.active == 1)
						{
							$scope.objUsers.push(objUser);	
						}//end if
					});	
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);
		
		$scope.progress.addPromise($p);	
		return $p;
	}; //end function	
	
	function loadStandardFields() {
		var objRequest = {
				acrq: 'load-standard-fields',
			};
			
		$scope.objStandardFields = Array();
		var $p = BulkSendPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load Users', '<p>Users could not be loaded, process failed with response : ' + response.response + '</p>');
					}//end if

					angular.forEach(response.objData, function (objF, i) {
						if (typeof objF.id != 'undefined')
						{
							//exclude some fields
							switch (objF.field)
							{
								case 'user_id':
								case 'source':
									//ignore field
									break;
									
								default:
									$scope.objStandardFields.push(objF);
									break;
							}//end switch	
						}//end if
					});	
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);
		
		$scope.progress.addPromise($p);	
		return $p;
	}; //end function
	
	function loadCustomFields() {
		var objRequest = {
				acrq: 'load-custom-fields',
			};
			
		$scope.objCustomFields = Array();
		var $p = BulkSendPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load Users', '<p>Users could not be loaded, process failed with response : ' + response.response + '</p>');
					}//end if

					angular.forEach(response.objData, function (objF, i) {
						if (typeof objF.id != 'undefined')
						{
							$scope.objCustomFields.push(objF);	
						}//end if
					});	
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);
		
		$scope.progress.addPromise($p);	
		return $p;
	};//end function
	
	function isRequestCreated()
	{
		if ($scope.objBulkRequest.id == false)
		{
			return true;
		}//end if
		
		doErrorAlert('Request has been created', '<p>The request has been created, as a result, changes can no longer by made.</p>');
		return false;
	}//end function
	
	function rebuildRequest()
	{
		doInfoAlert('Rebuilding Request', '<p>The request is being loaded, please be patient, this could take a while to complete.</p>');
		
		$scope.request_reconstruct_message = "<p>Requesting basic request data....</p>";
		
		var objRequest = {
			acrq: 'load-request-data',
			id: $scope.objBulkRequest.id
		};
		
		BulkSendPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load Request Data', '<p>Request Data could not be loaded, process failed with response : ' + response.response + '</p>');
					}//end if

					$scope.request_reconstruct_message = $scope.request_reconstruct_message + '<p>Requesting Journey Data...</p>';
					var $r = loadJourneys();
					$r.$promise.then(function () {
							$scope.request_reconstruct_message = $scope.request_reconstruct_message + '<p>Requesting Users....</p>';
							var $rr = loadUsers();
							
							$rr.$promise.then(function () {
								$scope.request_reconstruct_message = $scope.request_reconstruct_message + '<p>Requesting Forms....</p>';
								var $rrr = loadWebForms();
								
								$rrr.$promise.then(function () {
									$scope.request_reconstruct_message = $scope.request_reconstruct_message + '<p>Requesting Trackers....</p>';
									var $r = loadTrackers();
									
									$r.$promise.then(function () {
										$scope.request_reconstruct_message = $scope.request_reconstruct_message + '<p>Requesting Statuses....</p>';
										var $r = loadStatuses();
										
										$r.$promise.then(function () {
											$scope.request_reconstruct_message = $scope.request_reconstruct_message + '<p>Requesting Standard Fields....</p>';
											var $r = loadStandardFields();
											
											$r.$promise.then(function () {
												$scope.request_reconstruct_message = $scope.request_reconstruct_message + '<p>Requesting Custom Fields....</p>';
												var $r = loadCustomFields();
												
												$r.$promise.then(function () {
													$scope.request_reconstruct_message = $scope.request_reconstruct_message + '<p>Setting Data....</p>';
													
													//trigger initial calculation to get potential pool size
													var $r = fetchTargetGroupTotals();
													$r.$promise.then(function () {
														formatTargetGroupTotals();
														
														//set data ...
														rebuildData(response.objData);
														
														//trigger calculation after filters have been applied
														fetchTargetGroupTotals();
														formatTargetGroupTotals();
														
														//set request status
														if (response.objData.datetime_approved_admin != '0000-00-00 00:00:00' && response.objData.datetime_cancelled == '0000-00-00 00:00:00')
														{
															$scope.objBulkRequest.status = 2;
														} else {
															if (response.objData.datetime_cancelled == '0000-00-00 00:00:00' && response.objData.datetime_approved != '0000-00-00 00:00:00') 
															{
																$scope.objBulkRequest.status = 1;
															} else {
																if (response.objData.datetime_cancelled != '0000-00-00 00:00:00')
																{
																	$scope.objBulkRequest.status = 99;
																} else {
																	$scope.objBulkRequest.status = 0;
																}//end if
															}//end if
														}//end if
														
														doMessageAlert('Rebuilding complete', '<p>Thank you for your patience, you may continue.</p>');
														$scope.request_reconstruct_message = '';
													});
												})
											});
										})
									});
								});
							});
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);
	}//end if
	
	function rebuildData(objRequest)
	{	
		console.log(objRequest);
		
		//set journey
		angular.forEach($scope.objJourneys, function (objJourney, i) {
			if (objJourney.id == objRequest.fk_journey_id)
			{
				$scope.objBulkRequest.objJourney = objJourney;
			}//end if
		});

		//work through params
		angular.forEach(objRequest.arr_params, function (objParam, i) {
			switch (objParam.field_group)
			{
				case 'contact_status_equals':
					angular.forEach($scope.objContactStatuses, function (objStatus, i) {
						if (objStatus.id == objParam.field_value)
						{
							$scope.objBulkRequest.objHasStatuses.push(objStatus);
						}//end if
					});
					break;
					
				case 'contact_status_not_equals':
					angular.forEach($scope.objContactStatuses, function (objStatus, i) {
						if (objStatus.id == objParam.field_value)
						{
							$scope.objBulkRequest.objNotHaveStatuses.push(objStatus);
						}//end if
					});
					break;
					
				case 'webform_completed':
					angular.forEach($scope.objWebForms, function (objForm, i) {
						if (objForm.id == objParam.field_value)
						{
							$scope.objBulkRequest.objHasWebForm.push(objForm);
						}//end if
					});
					break;
					
				case 'webform_not_completed':
					angular.forEach($scope.objWebForms, function (objForm, i) {
						if (objForm.id == objParam.field_value)
						{
							$scope.objBulkRequest.objNotHaveWebForm.push(objForm);
						}//end if
					});
					break;
					
				case 'tracker_exists':
					angular.forEach($scope.objTrackers, function (objTracker, i) {
						if (objTracker.id == objParam.field_value)
						{
							$scope.objBulkRequest.objHasTracker.push(objTracker);
						}//end if
					});
					break;
					
				case 'tracker_not_exists':
					angular.forEach($scope.objTrackers, function (objTracker, i) {
						if (objTracker.id == objParam.field_value)
						{
							$scope.objBulkRequest.objNotHaveTracker.push(objTracker);
						}//end if
					});
					break;
					
				case 'contact_equals_user':
					angular.forEach($scope.objUsers, function (objUser, i) {
						if (objUser.id == objParam.field_value)
						{
							$scope.objBulkRequest.objHasUser.push(objUser);
						}//end if
					});
					break;
					
				case 'contact_not_equals_user':
					angular.forEach($scope.objUsers, function (objUser, i) {
						if (objUser.id == objParam.field_value)
						{
							$scope.objBulkRequest.objNotHaveUser.push(objUser);
						}//end if
					});
					break;
					
				case 'contact_source_equals':
					
					break;			

				case 'contact_source_not_equals':
					
					break;	
					
				case 'contact_reference_equals':
					
					break;	
					
				case 'contact_reference_not_equals':
					
					break;		
					
				case 'custom_fields_equals':
				case 'custom_fields_not_equals':
					angular.forEach($scope.objCustomFields, function (objField, i) {
						//extract field name
						var arr_f = objParam.table_field.split('.');
						if (objField.field == arr_f[1])
						{
							var f = {
									id: objField.id,
									field: objField.field,
									data: objField,
									values:[{
										value: objParam.field_value,
										operator: objParam.field_operator
									}]
							};
							
							$scope.objBulkRequest.objCustomFields.push(f);
						}//end if
					});
					break;
					
				case 'standard_fields_equals':				
				case 'standard_fields_not_equals':				
					angular.forEach($scope.objStandardFields, function (objField, i) {					
						//extract field name
						var arr_f = objParam.table_field.split('.');
						if (objField.field == arr_f[1])
						{
							//check if standard field values data has been set already
							if (typeof objField.field_values_data == "undefined")
							{
								//request field data
								var objRequest = {
									acrq: 'load-standard-field-details',
									field_id: objField.id
								};
								
								var $p = BulkSendPageService.get(objRequest, 
										function success(response) {
											logToConsole(response);
											//check for errors
											if (typeof response.error != 'undefined' && response.error == 1)
											{
												doErrorAlert('Unable to load Field Data', '<p>Field Data could not be loaded, process failed with response : ' + response.response + '</p>');
											}//end if

											//convert data to proper objects
											var arr_data = Array();
											angular.forEach(response.objData.objField.field_values_data, function (v, k) {
												arr_data.push({
													id: k,
													name: v 
												});
											});
											
											$scope.objStandardFields[i].field_values_data = arr_data;
										},
										function error(errorResponse) {
											logToConsole(errorResponse);
											doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
										}
								);
							}//end if
							
							var f = {
									id: objField.id,
									field: objField.field,
									data: objField,
									values:[{
										value: objParam.field_value,
										operator: objParam.field_operator
									}]
							};
							
							$scope.objBulkRequest.objStandardFields.push(f);
						}//end if
					});					
					break;
			}//end switch
		});
		
		//set allocation options
		$scope.objBulkRequest.objOptions.allocate_all = objRequest.allocate_all;
		$scope.objBulkRequest.objOptions.allocate_new = objRequest.allocate_new;
		$scope.objBulkRequest.objOptions.allocate_num = objRequest.allocate_num;
	}//end function
}]);