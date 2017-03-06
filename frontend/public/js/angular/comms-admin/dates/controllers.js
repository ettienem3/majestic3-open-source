'use strict';

var journeyDatesControllers = angular.module('journeyDatesControllers', []);

journeyDatesControllers.controller('HomeCtrl', ['$scope', '$route', '$routeParams', '$window', 'JourneyDatesPageService', 'promiseTracker', function HomeCtrl($scope, $route, $routeParams, $window, JourneyDatesPageService, promiseTracker) {
	$scope.objPageConfig = global_page_config;
	$scope.global_wait_image = global_wait_image;
	$scope.objRecords = Array();

	//set progress indicators
	$scope.progress = {
			load_records_progress: promiseTracker(),
			load_admin_form: promiseTracker(),
			process_form_submit: promiseTracker(),
	};
	
	//set form
	$scope.objForm = {
		fields: [],
		model: {},
		form: {},
		submitForm: function () {
			//disbale submit button
			$scope.objForm.form.$invalid = true;
			
			var objRequest = $scope.objForm.model;
			//add all fields to request
			angular.forEach($scope.objForm.fields, function (objField, i) {
				if (typeof objRequest[objField.key] == 'undefined')
				{
					objRequest[objField.key] = '';
				}//end if
			});
			
			if (typeof objRequest.id != 'undefined' && objRequest.id > 0)
			{
				//update record
				objRequest.acrq = 'edit-record';
			} else {
				//create record
				objRequest.acrq = 'create-record';
			}//end if
			
			var $p = JourneyDatesPageService.post(objRequest, 
				function success(response) {
					logToConsole(response);
					$scope.objForm.form.$invalid = false;
					
					if (typeof response.error != 'undefined')
					{
						doErrorAlert('Unable to save changes', '<p>' + response.response + '</p>');
						return false;
					}//end if
					
					if (objRequest.acrq == 'edit-record')
					{
						//update the record
						angular.forEach($scope.objRecords, function (objRecord, i) {
							if (objRecord.id == objRequest.id)
							{
								angular.forEach(response.objData, function(value, field) {
									$scope.objRecords[i][field] = value;
								});
							}//end if
						});
						
						//close the form
						$scope.togglePanel('editRecord', false);
					} else {
						$scope.objRecords.push(response.objData);
						//close the form
						$scope.togglePanel('createRecord', false);
					}//end if
					
					doMessageAlert('Changes saved', '<p>Your changes have been saved</p>');
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					$scope.objForm.form.$invalid = false;
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
			$scope.progress.process_form_submit.addPromise($p);
		}
	};
	
	$scope.loadRecords = function()  {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		return loadRecords();
	};
	function loadRecords() 
	{
		//clear current data
		$scope.objRecords = Array();
		var objRequest = {
				acrq: 'list-records'
		};
		
		var $p = JourneyDatesPageService.get(objRequest,
			function success(response) {
				logToConsole(response);
				
				angular.forEach(response.objData, function (objRecord, i) {
					$scope.objRecords.push(objRecord);
				});
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		//set tracker
		$scope.progress.load_records_progress.addPromise($p);
	}//end function
	
	$scope.toggleRecordStatus = function (objRecord) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		return toggleRecordStatus(objRecord);
	};
	function toggleRecordStatus(objRecord) 
	{
		objRecord.acrq = 'toggle-record-status'
		var $p = JourneyDatesPageService.post(objRecord,
			function success(response) {
				logToConsole(response);

				if (typeof response.error != 'undefined')
				{
					doErrorAlert('Unable to complete request', '<p>' + response.response + '</p>');
					return false;
				}//end if
				
				objRecord.active = response.objData.active;
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
	}//end function
	
	$scope.deleteRecord = function (objRecord) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		return deleteRecord(objRecord);
	};
	function deleteRecord(objRecord)
	{
		if (confirm('Are you sure you wish to remove this record?') != true)
		{
			return false;
		}//end if
		
		objRecord.acrq = 'delete-record';
		var $p = JourneyDatesPageService.post(objRecord,
				function success(response) {
				logToConsole(response);
	
				if (typeof response.error != 'undefined')
				{
					doErrorAlert('Unable to complete request', '<p>' + response.response + '</p>');
					return false;
				}//end if
				
				//remove record from table
				angular.forEach($scope.objRecords, function (objR, i) {
					if (objRecord.id == objR.id)
					{
						$scope.objRecords.splice(i, 1);
					}//end if
				});
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		$scope.progress.load_records_progress.addPromise($p);
	}//end function
	
	
	$scope.togglePanel = function (panel, status, objRecord) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope[panel] = status;
		
		if ($scope[panel] == true)
		{
			switch (panel)
			{
				case 'createRecord':
					//load admin form
					loadAdminForm();
					
					//clear model if any data is set
					$scope.objForm.model = {};
					break;
				
				case 'editRecord':
					//load admin form
					loadAdminForm();
					
					//allocate data to model
					objRecord.fk_journey_id = Number(objRecord.fk_journey_id);
					objRecord.fk_field_custom_id = Number(objRecord.fk_field_custom_id);
					objRecord.active = Number(objRecord.active);
					$scope.objForm.model = objRecord;
					break;
			}//end switch
			
			doCreateSlidePanel({});
		} else {
			doRemoveSlidePanel({});
		}//end if
	}; //end function
	
	function loadAdminForm() 
	{
		//check if form has been loaded already
		if ($scope.objForm.fields.length > 0)
		{
			return;
		}//end if
		
		var objRequest = {acrq: 'load-admin-form'};
		var $p = JourneyDatesPageService.post(objRequest,
			function success(response) {
				logToConsole(response);

				//allocate fields to the form
				$scope.objForm.fields = response.objForm;
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		$scope.progress.load_admin_form.addPromise($p);
	}; //end function
}]);