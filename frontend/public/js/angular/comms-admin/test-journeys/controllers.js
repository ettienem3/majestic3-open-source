'use strict';

var testJourneysControllers = angular.module('testJourneysControllers', []);

testJourneysControllers.controller('HomeCtrl', [
											'$scope', 
											'$route', 
											'$routeParams', 
											'$window', 
											'TestJourneysPageService', 
											'promiseTracker', 
											function HomeCtrl($scope, $route, $routeParams, $window, TestJourneysPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.objPageConfig = global_page_config;
	$scope.objRecords = [];
	$scope.objJourneys = [];
	$scope.delete_record_id = false;
	
	$scope.createFormPanelState = false;
	$scope.deleteFormPanelState = false;
	$scope.filterFormsPanelState = false;
	$scope.messages = false;
	
	//pagination
	$scope.previousPage = 1;
	$scope.currentPage = 1;
	$scope.pageSize = 20;
	
	$scope.progress = promiseTracker();
	
	$scope.formFilter = {
			applied: false,
			model: {},
			fields: setFilterFormFields(),
			submitForm: function () {
				$scope.formFilter.applied = true;
				$scope.togglePanel('filterFormPanelState', false);
				//reload data
				$scope.refreshRecords();
			},
			clearModel: function () {
				$scope.formFilter.applied = false;
				$scope.formFilter.model = {};
				$scope.togglePanel('filterFormPanelState', false);
				//reload data
				$scope.refreshRecords();
			}
	};
	
	$scope.adminForm = {
			model: {},
			fields: [],
			submitForm: function () {
				$scope.adminForm.model.acrq = "create-test";
				
				var $p = TestJourneysPageService.post($scope.adminForm.model, 
					function success(response) {
						logToConsole(response);
						
						//check for errors
						if (typeof response.error != 'undefined' && response.error == 1)
						{
							if (typeof response.form_messages != 'undefined')
							{
								handleFormlyFormValidationErrors($scope.adminForm.fields, $scope.adminForm.model, response.form_messages);	
								return false;
							} else {
								doErrorAlert('Unable to complete request', '<p>Request failed with response : ' + response.response + '</p>');
								return false;
							}//end if
						}//end if
						
						if (typeof response.objData != 'undefined' && typeof response.objData.errors != 'undefined')
						{
							handleFormlyFormValidationErrors($scope.adminForm.fields, $scope.adminForm.model, response.objData.errors);	
							return false;
						}//end if
						
						$scope.adminForm.model = {};
						doMessageAlert('Operation successfull', '<p>Test details have been saved</p>');
						
						//close form
						$scope.togglePanel('createFormPanelState', false);
						
						//reload data
						$scope.refreshRecords();
					},
					function error(errorResponse) {
						logToConsole(errorResponse);
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					}
				);
			},
			clearModel: function () {
				$scope.adminForm.model = {};
			}
	};
	
	$scope.adminDeleteForm = {
		model: {},
		fields: [],
		submitForm: function () {
			
		}
	};
	
	$scope.init = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.previousPage = 0;
		$scope.pageChangeHandler($scope.currentPage);	
	}; //end function
	
	//handle clicks on paginator
	$scope.pageChangeHandler = function (page) {
		if (page == $scope.previousPage)
		{
			return;
		}//end if
		
		$scope.previousPage = -1;
		
		$scope.pageContent = global_wait_image;
		$scope.objRecords = [];
		
		var start_number = 0;
		if (page > 0)
		{
			start_number = ((page - 1) * $scope.objPageConfig.pagination.qp_limit);
		}//end if
		
		var objRequest = {acrq: 'load-tests', 'qp_limit': $scope.objPageConfig.pagination.qp_limit, 'qp_start': start_number};
		loadRecords(objRequest);
	};
	
	$scope.refreshRecords = function ()
	{
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		var objRequest = {
			acrq: 'load-tests',
		};
		
		return loadRecords(objRequest);
	}//end function
	
	$scope.togglePanel = function (panel, status, id)
	{
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
			case 'createFormPanelState':
				$scope.adminForm.fields = setCreateTestFormFields();
				break;
				
			case 'deleteFormPanelState':
				$scope.delete_record_id = id;
				break;
		}//end switch
		
		if (flag == true)
		{
			doCreateSlidePanel({});
		} else {
			doRemoveSlidePanel({});
		}//end if
	}//end function
	
	$scope.submitDeleteForm = function (operation) {
		var objRequest = {
			acrq: 'delete-test',
		};
		var objTargetRecord = {};
		
		if (operation != '')
		{
			//find record in array
			angular.forEach($scope.objRecords, function (objR, i) {
				if (objR.id == $scope.delete_record_id)
				{
					objTargetRecord = objR;
				}//end if
			});
		}//end if
		
		switch (operation)
		{
			case 'journey':
				objRequest.operation = 'delete-journey';
				objRequest.id = objTargetRecord.fk_journey_id;
				break;
				
			case 'contact':
				objRequest.operation = 'delete-contact';
				objRequest.id = objTargetRecord.fk_reg_id;
				break;
			
			default:
				objRequest.operation = 'delete-test';
				objRequest.id = $scope.delete_record_id;
				break;
		}//end switch
		
		var $p = TestJourneysPageService.post(objRequest, 
			function success(response) {
				logToConsole(response);
				
				//check for errors
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					doErrorAlert('Unable to complete request', '<p>Unable to remove record, failed with response : ' + response.response + '</p>');
				}//end if
				
				doMessageAlert('Operation successfull', '<p>Test details have been removed</p>');
				
				//close form
				$scope.togglePanel('deleteFormPanelState', false);
				
				//reload data
				$scope.refreshRecords();
				$scope.delete_record_id = false;
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				
				//close form
				$scope.togglePanel('deleteFormPanelState', false);
				$scope.delete_record_id = false;
			}
		);
	}; //end function
	
	function loadRecords(objRequest) 
	{
		if (typeof objRequest == 'undefined')
		{
			var objRequest = {
					acrq: 'load-tests',
					'qp_limit': 20
			};
		}//end if
		
		if ($scope.formFilter.applied == true)
		{
			angular.forEach($scope.formFilter.model, function (value, field) {
				objRequest[field] = value;
			});
		}//end if
		
		var $p = TestJourneysPageService.get(objRequest, 
			function success(response) {
				logToConsole(response);
				
				//check for errors
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					doErrorAlert('Unable to load configured tests', '<p>Request failed with response: ' + response.response + '</p>');
					return false;
				}//end if
				
				$scope.objRecords = Array();
				angular.forEach(response.objData, function (objTest, i) {
					if (typeof objTest.id != 'undefined')
					{
						$scope.objRecords.push(objTest);
					}//end if
				});
				
				//update paginator
				$scope.pageContent = '';
				$scope.objPageConfig.pagination = response.objData.hypermedia.pagination;
				$scope.objPageConfig.pagination.tpages = [];
				for (var i = 0; i < response.objData.hypermedia.pagination.pages_total; i++)
				{
					$scope.objPageConfig.pagination.tpages.push({i:i});
				}//end for
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		$scope.progress.addPromise($p);		
	};//end function
	
	function setCreateTestFormFields()
	{
		var arr_form_fields = [
			{
				key: 'journey_id',
				type: 'select',
				defaultValue: '',
				modelOptions: {
					getterSetter: true
				},
				templateOptions: {
					type: 'select',
					label: 'Journey',
					title: 'Select Journey',
					required: true,
					valueProp: 'optionID',
					labelProp: 'optionLabel',
					options: [
						{optionID: '', optionLabel: '--select--'}
					]
				},
				validation: {
					show: true
				}
			},
			{
				key: 'contact_id',
				type: 'input',
				modelOptions: {
					getterSetter: true
				},
				templateOptions: {
					type: 'text',
					required: true,
					label: 'Contact ID',
					title: 'Set Contact',
				},
				validation: {
					show: true
				}
			}
		];
		
		//load journeys
		if ($scope.objJourneys.length == 0)
		{
			//request journeys
			var objRequest = {
				acrq: 'load-journeys',	
			};
			var $p = TestJourneysPageService.get(objRequest, 
					function success(response) {
						logToConsole(response);
						
						//check for errors
						if (typeof response.error != 'undefined' && response.error == 1)
						{
							doErrorAlert('Unable to load journeys', '<p>Request failed with response: ' + response.response + '</p>');
							return false;
						}//end if
						
						$scope.objJourneys = Array();
						angular.forEach(response.objData, function (objJourney, i) {
							if (typeof objJourney.id != 'undefined')
							{
								$scope.objJourneys.push(objJourney);
							}//end if
						});
						
						//populate the form fields
						angular.forEach($scope.objJourneys, function (objJourney, i) {
							arr_form_fields[0].templateOptions.options.push({optionID: objJourney.id, optionLabel: objJourney.id + ' - ' + objJourney.journey});
						})//end foreach
					},
					function error(errorResponse) {
						logToConsole(errorResponse);
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					}
				);
				
				$scope.progress.addPromise($p);				
		} else {
			angular.forEach($scope.objJourneys, function (objJourney, i) {
				arr_form_fields[0].templateOptions.options.push({optionID: objJourney.id, optionLabel: objJourney.id + ' - ' + objJourney.journey});
			})//end foreach
		}//end if
		
		return arr_form_fields;
	}//end function
	
	function setFilterFormFields()
	{
		var arr_form_fields = [
			{
				key: 'journey_id',
				type: 'select',
				defaultValue: '',
				modelOptions: {
					getterSetter: true
				},
				templateOptions: {
					type: 'select',
					label: 'Journey',
					title: 'Select Journey',
					valueProp: 'optionID',
					labelProp: 'optionLabel',
					options: [
						{optionID: '', optionLabel: '--select--'}
					]
				},
				validation: {
					show: true
				}
			},
			{
				key: 'contact_id',
				type: 'input',
				modelOptions: {
					getterSetter: true
				},
				templateOptions: {
					type: 'text',
					label: 'Contact ID',
					title: 'Select Contact',
				},
				validation: {
					show: true
				}
			}
		];
		
		//load journeys
		if ($scope.objJourneys.length == 0)
		{
			//request journeys
			var objRequest = {
				acrq: 'load-journeys',	
			};
			var $p = TestJourneysPageService.get(objRequest, 
					function success(response) {
						logToConsole(response);
						
						//check for errors
						if (typeof response.error != 'undefined' && response.error == 1)
						{
							doErrorAlert('Unable to load journeys', '<p>Request failed with response: ' + response.response + '</p>');
							return false;
						}//end if
						
						$scope.objJourneys = Array();
						angular.forEach(response.objData, function (objJourney, i) {
							if (typeof objJourney.id != 'undefined')
							{
								$scope.objJourneys.push(objJourney);
							}//end if
						});
						
						//populate the form fields
						angular.forEach($scope.objJourneys, function (objJourney, i) {
							arr_form_fields[0].templateOptions.options.push({optionID: objJourney.id, optionLabel: objJourney.id + ' - ' + objJourney.journey});
						})//end foreach
					},
					function error(errorResponse) {
						logToConsole(errorResponse);
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					}
				);
				
				$scope.progress.addPromise($p);				
		} else {
			angular.forEach($scope.objJourneys, function (objJourney, i) {
				arr_form_fields[0].templateOptions.options.push({optionID: objJourney.id, optionLabel: objJourney.id + ' - ' + objJourney.journey});
			})//end foreach
		}//end if
		
		return arr_form_fields;
	}//end function
}]);