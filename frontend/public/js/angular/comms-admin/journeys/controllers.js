'use strict';

var journeysControllers = angular.module('journeysControllers', []);

journeysControllers.controller('HomeCtrl', [
											'$scope', 
											'$route', 
											'$routeParams', 
											'$window', 
											'JourneysPageService', 
											'promiseTracker', 
											function HomeCtrl($scope, $route, $routeParams, $window, JourneysPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.objPageConfig = global_page_config;
	$scope.objRecords = [];
	$scope.objCurrentRecord = {};
	$scope.objJourneyBehaviours = [];
	$scope.objJourneyAvailableBehaviours = [];
	$scope.objDeleteRecord = false;
	
	$scope.createFormPanelState = false;
	$scope.editFormPanelState = false;
	$scope.deleteFormPanelState = false;
	$scope.filterFormsPanelState = false;
	$scope.journeyStatisticsPanelState = false;
	$scope.journeyBehavioursPanelState = false;
	$scope.updateJourneyConfigFlag = false;
	$scope.messages = false;
	
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
			arr_loaded_fields: [],
			submitForm: function () {
				if (typeof $scope.objCurrentRecord.id == 'undefined')
				{
					//new record
					var objRequest = {
							acrq: 'create-journey',
						};
				} else {
					//update record
					var objRequest = {
							acrq: 'update-journey',
							journey_id: $scope.objCurrentRecord.id,
						};
				}//end if

				
				angular.forEach($scope.adminForm.model, function(value, field) {
					objRequest[field] = value;
				});
				
				//add missing fields
				angular.forEach($scope.adminForm.fields, function(objField, i) {
					if (typeof objRequest[objField.key] == 'undefined')
					{
						objRequest[objField.key] = '';
					}//end if
				});
				
				var $p = JourneysPageService.post(objRequest, 
					function success(response) {
						logToConsole(response);
						
						//check for errors
						if (typeof response.error != 'undefined' && response.error == 1)
						{
							if (typeof response.form_messages != 'undefined')
							{
								handleFormlyFormValidationErrors($scope.adminForm.fields, $scope.adminForm.model, response.form_messages);	
								return false;
							}//end if
						}//end if
						
						if (typeof response.objData != 'undefined' && typeof response.objData.errors != 'undefined')
						{
							handleFormlyFormValidationErrors($scope.adminForm.fields, $scope.adminForm.model, response.objData.errors);	
							return false;
						}//end if
						
						$scope.objCurrentRecord = {};
						$scope.adminForm.model = {};
						doMessageAlert('Operation successfull', '<p>Journey details have been saved</p>');
						
						//close form
						$scope.togglePanel('createFormPanelState', false);
						$scope.togglePanel('editFormPanelState', false);
						
						//reload data
						$scope.refreshRecords();
					},
					function error(errorResponse) {
						logToConsole(errorResponse);
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					}
				);
				
				$scope.progress.addPromise($p);
				$scope.loadJourneyForm.addPromise($p);
			},
			clearModel: function () {
				$scope.adminForm.model = {};
			}
	};
	
	$scope.formJourneyBehaviour = {
			fields: [],
			model: {},
			objSelectedBehaviourDetails: {},
			submitForm: function () {
				if (typeof $scope.formJourneyBehaviour.model.id != 'undefined' && $scope.formJourneyBehaviour.model.id > 0)
				{
					//updating a behaviour
					$scope.formJourneyBehaviour.model.acrq = 'edit-journey-behaviour-action';
				} else {
					//creating a behaviour
					$scope.formJourneyBehaviour.model.acrq = 'create-journey-behaviour-action';	
				}//end if
				
				$scope.formJourneyBehaviour.model.journey_id = $scope.objCurrentRecord.id;
		
				var $p = JourneysPageService.post($scope.formJourneyBehaviour.model,
					function success(response) {
						logToConsole(response);
	
						//check for errors
						if (typeof response.error != 'undefined' && response.error == 1)
						{
							if (typeof response.form_messages != 'undefined')
							{
								handleFormlyFormValidationErrors($scope.formJourneyBehaviour.fields, $scope.formJourneyBehaviour.model, response.form_messages);	
								return false;
							} else {
								doErrorAlert('Unable to save behaviour', '<p>An unknown error has occurred. Request failed with : ' + response.response + '</p>');
								return false
							}//end if
						}//end if

						if (typeof response.objData != 'undefined' && typeof response.objData.errors != 'undefined')
						{
							handleFormlyFormValidationErrors($scope.formJourneyBehaviour.fields, $scope.formJourneyBehaviour.model, response.objData.errors);	
							return false;
						}//end if

						doMessageAlert('Please wait', '<p>Data is being saved, the background page will reload in just a moment</p>');
						
						//clear behaviour list
						$scope.objJourneyAvailableBehaviours = [];
						$scope.updateJourneyConfigFlag = false;
						
						//clear form and model
						$scope.formJourneyBehaviour.model = {};
						$scope.formJourneyBehaviour.fields = [];
						
						setTimeout(function () {
							//update the behaviours table
							$scope.togglePanel('journeyBehavioursPanelState', false);
							$scope.togglePanel('journeyBehavioursPanelState', true, $scope.objCurrentRecord.id);
						}, 2000);
					},
					function error(errorResponse) {
						logToConsole(errorResponse);
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					}
				);
				
				$scope.progress.addPromise($p);
			},
			clearForm: function () {
				$scope.formJourneyBehaviour.fields = [];
				$scope.formJourneyBehaviour.model = {};
				$scope.objSelectedBehaviourDetails = {};
			},
	};
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	$scope.loadJourneyForm = promiseTracker();
	$scope.loadJourneyBehaviourConfigForm = promiseTracker();
	
	//pagination
	$scope.previousPage = 1;
	$scope.currentPage = 1;
	$scope.pageSize = 20;
	
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
		
		var objRequest = {acrq: 'load-journeys', 'qp_limit': $scope.objPageConfig.pagination.qp_limit, 'qp_start': start_number};
		$scope.loadRecords(objRequest);
	};
	
	$scope.loadRecords = function (objRequest) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		return loadRecords(objRequest);
	};

	$scope.refreshRecords = function () {
		$scope.objRecords = Array();
		$scope.loadRecords();
	};
	
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
				if (flag == true)
				{
					setJourneyFormFields();
				} else {
					//clear the form
					$scope.adminForm.model = {};
				}//end if
				break;
				
			case 'editFormPanelState':
				if (flag == true)
				{
					setJourneyFormFields();
				
					if (typeof id == 'undefined')
					{
						doErrorAlert('Required data is not available', '<p>The requested operation cannot be completed. The required data is not available, please contact support for assistance</p>');
						return false;
					}//end if
					
					//load the requested record
					angular.forEach($scope.objRecords, function (objJourney, i) {
						if (objJourney.id == id)
						{
							$scope.objCurrentRecord = objJourney;
							$scope.adminForm.model = $scope.objCurrentRecord;
							$scope.adminForm.model.active = $scope.objCurrentRecord.active * 1;
							$scope.adminForm.model.priority = $scope.objCurrentRecord.priority * 1;
						}//end if
					});
				} else {
					//clear the form
					$scope.adminForm.model = {};
				}//end if
				break;
				
			case 'deleteFormPanelState':
				if (flag == true)
				{
					if (typeof id == 'undefined')
					{
						doErrorAlert('Required data is not available', '<p>The requested operation cannot be completed. The required data is not available, please contact support for assistance</p>');
						return false;
					}//end if
					
					//load the requested record
					angular.forEach($scope.objRecords, function (objJourney, i) {
						if (objJourney.id == id)
						{
							$scope.objCurrentRecord = objJourney;
						}//end if
					});
				}//end if
				break;
				
			case 'journeyStatisticsPanelState':
				if (flag == true)
				{
					loadJourneyStatistics(id);
				}//end if
				break;
				
			case 'journeyBehavioursPanelState':
				if (flag == true)
				{
					loadJourneyBehavioursPanel(id);
				} else {
					//clear data
					$scope.formJourneyBehaviour.fields = [];
					$scope.formJourneyBehaviour.model = {};
					$scope.objSelectedBehaviourDetails = {};
					$scope.objJourneyAvailableBehaviours = [];
				}//end if
				break;
		}//end switch
		
		if (flag == true)
		{
			doCreateSlidePanel({});
		} else {
			doRemoveSlidePanel({});
		}//end if
	}//end function

	$scope.submitToggleStatus = function (id) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var objRequest = {
			acrq: 'toggle-journey-status',
			journey_id: id
		};
		
		var $p = JourneysPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to update Journey', '<p>Request failed with response: ' + response.response + '</p>');
						return false;
					}//end if
					
					//ng repeat is not updating, wtf not?
					angular.element(".journey_" + id + "_status").toggleClass('text-success glyphicon glyphicon-ok text-danger glyphicon glyphicon-remove');
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
			$scope.progress.addPromise($p);
	};
	
	$scope.submitDeleteForm = function (objJourney) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if (confirm('Are you sure? This action cannot be reversed.') == false)
		{
			return false;
		}//end if
		
		var objRequest = {
				'acrq': 'delete-journey',
				'journey_id': objJourney.id
		};
		
		var $p = JourneysPageService.post(objRequest,
			function success(response) {
				logToConsole(response);
				
				//check for errors
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					doErrorAlert('Unable to remove Journey', '<p>Request failed with response: ' + response.response + '</p>');
					return false;
				}//end if
				
				//remove record from list
				angular.forEach($scope.objRecords, function (objRecord, i) {
					if (objRecord.id == objJourney.id)
					{
						$scope.objRecords.splice(i, 1);
					}//end if
				});
				
				$scope.togglePanel('deleteFormPanelState', false);
				doInfoAlert('Operation completed', '<p>The requested Journey has been removed</p>');
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
	};
	
	$scope.setJourneyDisplayDate = function (date) {
		if (date == '')
		{
			return date;
		}//end if
		
		var objDate = new Date(date);
		var html = date;
		if (Date.now() > objDate.getMilliseconds())
		{
			html = '<span class="text-danger bg-danger">' + date + '</span>';
		}//end if
		
		return html;
	};
	
	$scope.loadAvailableJourneyBehaviours = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		//disable behaviour update section if active
		$scope.updateJourneyConfigFlag = false;
		//clear the form
		$scope.formJourneyBehaviour.clearForm();
		
		return loadAvailableJourneyBehaviours();
	};
	
	$scope.loadJourneyBehaviourCreateForm = function (objAction) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		//disable behaviour update section if active
		$scope.updateJourneyConfigFlag = false;
		//clear the form
		$scope.formJourneyBehaviour.clearForm();
		
		$scope.formJourneyBehaviour.objSelectedBehaviourDetails = objAction;
		
		return loadJourneyBehaviourCreateForm(objAction.action);
	}; //end function
	
	$scope.toggleJourneyBehaviourStatus = function (objBehaviour) 
	{
		objBehaviour.acrq = 'toggle-journey-behaviour-action-status';
		var $p = JourneysPageService.post(objBehaviour, 
			function success(response) {
				//check for errors
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					doErrorAlert('Unable to remove Journey Behaviour', '<p>Request failed with response : ' + response.response + '</p>');
					return false;
				}//end if
				
				//update the behaviours table
				$scope.togglePanel('journeyBehavioursPanelState', false);
				$scope.togglePanel('journeyBehavioursPanelState', true, objBehaviour.fk_journey_id);
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		$scope.progress.addPromise($p);
	}; //end function
	
	$scope.updateJourneyBehaviour = function (objBehaviour) 
	{
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		doInfoAlert('Journey Behaviours Issue', '<p>Updating a Journey Behaviour is not available at present. As a work around, delete the behaviour and create a new behaviour.</p>');
		return false;
		
		//activate form section 
		$scope.updateJourneyConfigFlag = true;
		
		//request the behaviour details
		var objRequest = {
			acrq: 'load-journey-behaviour',
			journey_id: objBehaviour.fk_journey_id,
			behaviour_id: objBehaviour.id
		};
		var $p = JourneysPageService.get(objRequest, 
			function success(response) {
				logToConsole(response);
				//check for errors
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					doErrorAlert('Unable to load Journey Behaviour', '<p>Request failed with response : ' + response.response + '</p>');
					return false;
				}//end if
				
				//request behaviour form
				loadJourneyBehaviourCreateForm(response.objData.action);
				
				//do behaviour action specific operations and calculations first
				switch (response.objData.action)
				{
					case '__journey_no_start_time':
						response.objData.content = Number(response.objData.content / 86400);
						break;
				}//end switch
				
				//allocate behaviour data to model
				$scope.formJourneyBehaviour.model = response.objData;
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		$scope.progress.addPromise($p);
		$scope.loadJourneyBehaviourConfigForm.addPromise($p);
	}//end function
	
	$scope.deleteJourneyBehaviour = function (objBehaviour) 
	{
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if (confirm('Are you sure you wish to remove the set behaviour? This action cannot be undone and the effect is immediate') == true)
		{
			objBehaviour.acrq = 'delete-journey-behaviour-action';
			var $p = JourneysPageService.post(objBehaviour, 
				function success(response) {
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to remove Journey Behaviour', '<p>Request failed with response : ' + response.response + '</p>');
						return false;
					}//end if
					
					//update the behaviours table
					$scope.togglePanel('journeyBehavioursPanelState', false);
					$scope.togglePanel('journeyBehavioursPanelState', true, objBehaviour.fk_journey_id);
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
		}//end if
	}; //end function
	
	/**
	 * Load journey data records
	 */
	function loadRecords(objRequest)
	{
		if (typeof objRequest == 'undefined')
		{
			var objRequest = {
					acrq: 'load-journeys',
					'qp_limit': 20
			};
		}//end if
		
		if ($scope.formFilter.applied == true)
		{
			angular.forEach($scope.formFilter.model, function (value, field) {
				objRequest[field] = value;
			});
		}//end if
		
		var $p = JourneysPageService.get(objRequest, 
			function success(response) {
				logToConsole(response);
				
				//check for errors
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					doErrorAlert('Unable to load Journeys', '<p>Request failed with response: ' + response.response + '</p>');
					return false;
				}//end if
				
				$scope.objRecords = Array();
				angular.forEach(response.objData, function (objJourney, i) {
					if (typeof objJourney.id != 'undefined')
					{
						$scope.objRecords.push(objJourney);
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
	}//end function
	
	/**
	 * Set filtering form fields
	 */
	function setFilterFormFields() {
		var arr_fields = [];
		
		//journey title
		var objField = {
				key: 'journeys_journey',
				type: 'input',
				templateOptions: {
					type: 'text',
					label: 'Journey Name / ID',
					title: 'Filter by Journey name or Journey id',
					placeholder: 'e.g Journey X or 210',
				}
			};
		arr_fields.push(objField);
		
		//journey status
		var objField = {
				key: 'journeys_status',
				type: 'radio',
				templateOptions: {
					type: 'radio',
					label: 'Journey Status',
					title: 'Filter by Journey Status',
					valueProp: 'optionID',
					labelProp: 'optionLabel',
					options: [
						          {'optionID': 1, 'optionLabel': 'Active' },
						          {'optionID': 0, 'optionLabel': 'Inactive' },
					          ]
				}
			};
		arr_fields.push(objField);
		
		return arr_fields;
	}; //end function
	
	/**
	 * Set journey admin form fields
	 */
	function setJourneyFormFields()
	{
		if ($scope.adminForm.arr_loaded_fields.length > 0)
		{
			$scope.adminForm.fields = Array();
			$scope.adminForm.fields = $scope.adminForm.arr_loaded_fields;
			return $scope.adminForm.arr_loaded_fields;
		}//end if
		
		var objRequest = {
			acrq: 'load-journey-admin-form',	
		};
		var $p = JourneysPageService.get(objRequest, 
			function success(response) {
				logToConsole(response);
				
				//check for errors
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					doErrorAlert('Unable to load Journeys', '<p>Request failed with response: ' + response.response + '</p>');
					return false;
				}//end if
				
				angular.forEach(response.objData, function(objField, i) {
					switch (objField.key)
					{
						case 'date_expiry':
						    objField.ngModelElAttrs = {
						        'data-provide': 'datepicker',
						        'data-date-format': 'dd M yyyy',
						        'data-date-clear-btn': 'true',
						        'data-date-autoclose': 'true',
						        'data-date-today-highlight': 'true',
						        'data-date-today-btn': 'true',
						        'readonly': 'readonly',
						      }
							break;
					}//end switch
					
					$scope.adminForm.arr_loaded_fields.push(objField);
				});
				
				$scope.adminForm.fields = $scope.adminForm.arr_loaded_fields;
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		$scope.loadJourneyForm.addPromise($p);
	}//end function
	
	function loadJourneyBehavioursPanel(id)
	{
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		//set current record
		var objRequest = {
				acrq: 'load-journey-data',
				journey_id: id
		};
		
		$scope.objCurrentRecord = {};
		var $p = JourneysPageService.get(objRequest, 
			function success(response) {
				logToConsole(response);
				//check for errors
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					doErrorAlert('Unable to load Journey Data', '<p>Request failed with response: ' + response.response + '</p>');
					return false;
				}//end if
				
				$scope.objCurrentRecord = response.objData;
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		$scope.progress.addPromise($p);
		
		var objRequest = {
				acrq: 'load-journey-behaviours',
				journey_id: id
		};
		
		$scope.objJourneyBehaviours = Array();
		var $p = JourneysPageService.get(objRequest, 
			function success(response) {
				logToConsole(response);
				//check for errors
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					doErrorAlert('Unable to load Journey Behaviours', '<p>Request failed with response: ' + response.response + '</p>');
					return false;
				}//end if
				
				angular.forEach(response.objData, function (objBehaviour, i) {
					$scope.objJourneyBehaviours.push(objBehaviour);
				});
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		$scope.progress.addPromise($p);
	}//end function
	
	/**
	 * Load available behaviours to apply to journeys
	 */
	function loadAvailableJourneyBehaviours()
	{
		var objRequest = {
			acrq: 'load-available-journey-behaviours',
			journey_id: $scope.objCurrentRecord.id
		};
		
		$scope.objJourneyAvailableBehaviours = Array();
		var $p = JourneysPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load Journey Behaviours', '<p>Request failed with response: ' + response.response + '</p>');
						return false;
					}//end if
					
					angular.forEach(response.objData, function (objBehaviour, i) {
						$scope.objJourneyAvailableBehaviours.push(objBehaviour);
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
		$scope.progress.addPromise($p);
	}//end function
	
	/**
	 * Load journey create behaviour action config form
	 */
	function loadJourneyBehaviourCreateForm(action)
	{
		var objRequest = {
			acrq: 'load-journey-create-behaviour-form',
			journey_id: $scope.objCurrentRecord.id,
			beh_action: action
		};
		
		$scope.formJourneyBehaviour.fields = Array();
		$scope.formJourneyBehaviour.model = {};
		$scope.formJourneyBehaviour.model.beh_action = action; //save action for later use
		var $p = JourneysPageService.get(objRequest, 
			function success(response) {
				logToConsole(response);
				//check for errors
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					doErrorAlert('Unable to load Journey Behaviour Form', '<p>Request failed with response: ' + response.response + '</p>');
					return false;
				}//end if
				
				var arr_fields = loadJourneyBehaviourConfigFormFields(action);
				angular.forEach(response.objData, function (objField, i) {
					if (typeof arr_fields != 'undefined' && arr_fields != false)
					{
						switch (objField.key)
						{
							case 'fk_form_id':
							case 'fk_journey_id':
							case 'fk_journey_id2':
							case 'fk_reg_status_id':
							case 'fk_fields_custom_id':
								angular.forEach(arr_fields, function (objF, ii) {
									if (objF.key == objField.key)
									{
										arr_fields[ii].templateOptions.options = objField.templateOptions.options;
									}//end if
								});
								break;
						}//end switch
					} else {
						//push fields to form
						$scope.formJourneyBehaviour.fields.push(objField);
					}//end if
				});
				
				if (typeof arr_fields != 'undefined' && arr_fields != false)
				{
					$scope.formJourneyBehaviour.fields = arr_fields;
				}//end if
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		$scope.loadJourneyBehaviourConfigForm.addPromise($p);
	}//end function
	
	function loadJourneyStatistics(id) {
		//clear elements
		angular.element('#_journey_stats_sending_status').html("");
		angular.element('#_contact_status_sending_status').html("");
		angular.element('#_journey_contact_episode_progression').html("");
		angular.element('#_journey_contact_episode_history').html("");
		
		var objRequest = {
			acrq: 'load-journey-statistics',
			journey_id: id,
		};
		
		var $p = JourneysPageService.get(objRequest,
			function success(response) {
				logToConsole(response);
				
				//check for errors
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					doErrorAlert('Unable to load Journey details', '<p>Request failed with response: ' + response.response + '</p>');
					return false;
				}//end if

				/**
				 * Create current sending status pie chart
				 */
				var objChartConfig = {
					chart: {
						renderTo: '_journey_stats_sending_status',
						type: 'pie',
					},
					credits: {
						enabled: false
					},
					title: {
						text: 'Sending Status',
					},
					legend: {
						enabled: true,
					},
			        plotOptions: {
			            pie: {
			                allowPointSelect: true,
			                cursor: 'pointer',
			                dataLabels: {
			                    enabled: true,
			                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
			                    style: {
			                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
			                    }
			                }
			            }
			        },
			        yAxis: {
			        	allowDecimals: false,
			        },
			        series: [{
			        	name: 'Total',
			        	colorByPoint: true,
			        	data: []
			        }]
				};
				
				angular.forEach(response.objData.journey_sending_status, function (objD, i) {
					objChartConfig.series[0].data.push({name: objD.journey_status_status, y: Number(objD.total * 1)});
				});
				
				var objStatusChart = new Highcharts.Chart(objChartConfig);
				
				/**
				 * Create sending status by contact status chart
				 */
				var objChartConfig = {
						chart: {
							renderTo: '_contact_status_sending_status',
							type: 'column',
						},
						credits: {
							enabled: false
						},
						title: {
							text: 'Sending Status by Contact Status',
						},
						legend: {
							enabled: true,
						},
				        yAxis: {
				        	allowDecimals: false,
				        },
				        plotOptions: {
				            column: {
				                allowPointSelect: true,
				                cursor: 'pointer',
				                dataLabels: {
				                    enabled: true,
				                    format: '<b>{point.name}</b>: {point.y}',
				                    style: {
				                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
				                    }
				                }
				            }
				        },
				        series: []
					};
				
				var objSeriesData = {};
				angular.forEach(response.objData.contact_journey_status_breakdown, function (objD, i) {
					var status_id = 'status_' + objD.registration_status_id;
					//create object
					if (typeof objSeriesData[status_id] == "undefined")
					{
						objSeriesData[status_id] = {
							name: objD.registration_status_status + " - " + objD.journey_status_status,
							colorByPoint: true,
							data: []
						};
					}//end if
					
					//add data
					objSeriesData[status_id].data.push({name: objD.registration_status_status + " (" + objD.journey_status_status + ")", y: Number(objD.total * 1)});
				});
				
				angular.forEach(objSeriesData, function (objS, i) {
					objChartConfig.series.push(objS);
				});
				
				var objStatusChart = new Highcharts.Chart(objChartConfig);
				
				/**
				 * Create contact vs episode progression chart
				 */
				var objChartConfig = {
						chart: {
							renderTo: '_journey_contact_episode_progression',
							type: 'line',
						},
						credits: {
							enabled: false
						},
						title: {
							text: 'Contact Episode Progression',
						},
						legend: {
							enabled: true,
						},
				        plotOptions: {
				            line: {
				                allowPointSelect: true,
				                cursor: 'pointer',
				                dataLabels: {
				                    enabled: true,
				                    format: '<b>{point.name}</b>: {point.y} Contacts',
				                    style: {
				                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
				                    }
				                }
				            }
				        },
				        yAxis: {
				        	allowDecimals: false,
				        },
				        series: [{
				        	name: 'Contacts',
				        	data: []
				        }]
					};
				
				angular.forEach(response.objData.journey_contact_episode_progression, function (objD, i) {
					//add data
					objChartConfig.series[0].data.push({
						name: 'Episode ' + objD.queued_episode,
						y: Number(objD.total * 1),
					});
				});
				
				var objChart = new Highcharts.Chart(objChartConfig);	
				
				/**
				 * Create simple history
				 */
				var objChartConfig = {
						chart: {
							renderTo: '_journey_contact_episode_history',
							type: 'column',
						},
						credits: {
							enabled: false
						},
						title: {
							text: 'Episode Send History',
						},
						legend: {
							enabled: true,
						},
				        plotOptions: {
				            line: {
				                allowPointSelect: true,
				                cursor: 'pointer',
				                dataLabels: {
				                    enabled: true,
				                    format: '<b>{point.name}</b>: {point.y} Contacts',
				                    style: {
				                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
				                    }
				                }
				            }
				        },
				        yAxis: {
				        	allowDecimals: false,
				        },
				        series: [{
				        	name: 'Episodes Sent',
				        	data: []
				        }]
					};
				
				angular.forEach(response.objData.journey_contact_episode_history_count, function (objD, i) {
					//add data
					objChartConfig.series[0].data.push({
						name: 'Episode ' + objD.comms_comm_num,
						y: Number(objD.total * 1),
						x: Number(objD.comms_comm_num * 1)
					});
				});
				
				var objChart = new Highcharts.Chart(objChartConfig);					
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		$scope.progress.addPromise($p);
	}; //end function
}]);


journeysControllers.controller('JourneySummaryCtrl', [
														'$scope', 
														'$route', 
														'$routeParams', 
														'$window', 
														'JourneysPageService', 
														'JourneyEpisodesPageService',
														'promiseTracker', 
	function JourneySummaryCtrl($scope, $route, $routeParams, $window, JourneysPageService, JourneyEpisodesPageService, promiseTracker, formlyVersion) {
				
		$scope.pageContent = global_wait_image;
		$scope.global_wait_image = global_wait_image;
		$scope.objPageConfig = global_page_config;
		$scope.pageTitle = '<nav class="navbar navbar-default"><div class="container-fluid"><div class="navbar-header"><span class="navbar-brand"><span style=""><span class="glyphicon glyphicon-volume-down"></span></span>&nbsp; Journey Summary</span></div></div></nav>';
		$scope.journey_id = false;
		$scope.objJourney = {};
		$scope.objJourneyEpisodes = Array();
		$scope.objJourneyBehaviours = Array();
		$scope.objJourneyRelatedBehaviours = Array();
		
		// Inititate the promise tracker to track form submissions.
		$scope.progress = promiseTracker();
		$scope.loadJourneyEpisodes = promiseTracker();
		$scope.loadJourneyBehavioursProgress = promiseTracker();
		$scope.loadJourneyRelatedBehavioursProgress = promiseTracker();
		
		$scope.init = function () {			
			$scope.journey_id = $routeParams.journey_id;
			var objRequest = {
				acrq: 'load-journey-data',
				journey_id: $scope.journey_id
			};
			loadJourneyData(objRequest);
		}; //end function

		$scope.refreshData = function () {
			var objRequest = {
					acrq: 'load-journey-data',
					journey_id: $scope.journey_id
				};
				loadJourneyData(objRequest);
		}; //end function
		
		/**
		 * Request Journey Data
		 */
		function loadJourneyData(objRequest) {
			/**
			 * Make sure user is logged in
			 */
			userIsLoggedin();
			
			$scope.objJourney = {};
			var $p = JourneysPageService.get(objRequest, 
				function success(response) {
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load Journey', '<p>Request failed with response: ' + response.response + '</p>');
						return false;
					}//end if
					
					$scope.objJourney = response.objData;
					
					//format journey expiry date
					if ($scope.objJourney.date_expiry != '' && $scope.objJourney.date_expiry != '0000-00-00')
					{
						var objDate = new Date($scope.objJourney.date_expiry);
						var arr_dates = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
						$scope.objJourney.date_expiry_formatted = objDate.getUTCDate() + " " + objDate.getUTCMonth() + " " + objDate.getUTCFullYear();
					}//end if
					
					//trigger loading journey episode
					loadJourneyEpisodesData();
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
			$scope.progress.addPromise($p);
		};
		
		/**
		 * Request Journey Episodes Data
		 */
		function loadJourneyEpisodesData()
		{
			var objRequest = {
				acrq: 'load-journey-episodes',
				journey_id: $scope.objJourney.id
			};
			
			$scope.objJourneyEpisodes = Array();
			var $p = JourneyEpisodesPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load Journey', '<p>Request failed with response: ' + response.response + '</p>');
						return false;
					}//end if
					
					angular.forEach(response.objData, function (objEpisode, i) {
						if (typeof objEpisode.id != "undefined")
						{
							objEpisode.__status_reason = "";
							objEpisode.__timing_delay = "Calculating...";
							$scope.objJourneyEpisodes.push(objEpisode);
						}//end if
					});
					
					setTimeout(function () {
						calculateEpisodePanelStatus();
						
						//load journey behaviours
						loadJourneyBehaviourData();
						
						//load related behaviours
						loadJourneyRelatedBehaviourData();
					}, 600);
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
			$scope.loadJourneyEpisodes.addPromise($p);
		}//end function
		
		function calculateEpisodePanelStatus()
		{
			var arr_episode_numbers = Array();
			angular.forEach($scope.objJourneyEpisodes, function (objEpisode, i) {
				if (typeof objEpisode.id != "undefined")
				{
					//calculate delay between episodes
					if (objEpisode.send_time > 0)
					{
						var send_days = 0;
						var send_hours = 0;
						var send_minutes = 0;
						var send_time = parseInt(objEpisode.send_time);
						
						if (parseInt(send_time / 86400) >= 1)
						{
							send_days = parseInt(objEpisode.send_time / 86400);
						}//end if
						
						send_time = parseInt(send_time - (send_days * 86400));
						if (parseInt(send_time / 3600) >= 1)
						{
							send_hours = parseInt(send_time / 3600);
						}//end if
						
						send_time = parseInt(send_time - (send_hours * 3600));
						if (parseInt(send_time / 60) >= 1)
						{
							send_minutes = parseInt(send_time / 60);
						}//end if
						
						var result_str = "";
						if (send_days > 0)
						{
							if (send_days == 1)
							{
								result_str = "1 Day, ";
							} else {
								result_str = send_days + " Days, ";
							}//end if
						}//end if
						
						if (send_hours > 0)
						{
							if (send_hours == 1)
							{
								result_str = result_str + "1 Hour, ";
							} else {
								result_str = result_str + send_hours + " Hours, ";
							}//end if
						}//end if
						
						if (send_minutes > 0)
						{
							if (send_minutes == 1)
							{
								result_str = result_str + "1 Minute ";
							} else {
								result_str = result_str + send_minutes + " Minutes ";
							}//end if
						}//end if
						
						if (objEpisode.comm_num == 1)
						{
							objEpisode.__timing_delay = result_str + " after Journey is started";
						} else {
							objEpisode.__timing_delay = result_str + " after Episode " + (objEpisode.comm_num - 1) + " is sent";
						}//end if
					} else {
						if (objEpisode.comm_num == 1)
						{
							objEpisode.__timing_delay = "Immediatly after Journey is started";
						} else {
							objEpisode.__timing_delay = "Immediatly after Episode " + (objEpisode.comm_num - 1) + " is sent";
						}//end if
					}//end if
					
					var status = true;
					
					//check if journey is active
					if ($scope.objJourney.active == 0)
					{
						status = false;
						objEpisode.__status_reason = "Journey is inactive";
					}//end if
					
					//check if journey has expired
					if (status == true && $scope.objJourney.date_expiry != '' && $scope.objJourney.date_expiry != '0000-00-00')
					{
						var objCurrentDate = new Date();
						var objJourneyDate = new Date($scope.objJourney.date_expiry);
						if (objCurrentDate.getMilliseconds() > objJourneyDate.getMilliseconds())
						{
							status = false;
							objEpisode.__status_reason = "Journey has expired";
						}//end if
					}//end if
					
					//check if episode is active
					if (objEpisode.active == 0 && status == true)
					{
						status = false;
						objEpisode.__status_reason = "Episode is inactive";
					}//end if
					
					//check if episode has any content
					if (objEpisode.content == '' && status == true)
					{
						status = false;
						objEpisode.__status_reason = "Episode does not have any content set";
					}//end if
					
					//check if start date has been reached yet
					if (objEpisode.date_start != '' && objEpisode.date_start != '0000-00-00' && status == true)
					{
						var objCurrentDate = new Date();
						var objEpisodeStartDate = new Date(objEpisode.date_start);
						if (objEpisodeStartDate.getMilliseconds() > objCurrentDate.getMilliseconds())
						{
							status = false;
							objEpisode.__status_reason = "Episode start date has not been reached";
						}//end if
					}//end if
					
					if (objEpisode.date_expiry != '' && objEpisode.date_expiry != '0000-00-00' && status == true)
					{
						var objCurrentDate = new Date();
						var objEpisodeExpiryDate = new Date(objEpisode.date_expiry);
						if (objCurrentDate.getMilliseconds() > objEpisodeExpiryDate.getMilliseconds())
						{
							status = false;
							objEpisode.__status_reason = "Episode has expired";
						}//end if
					}//end if
					
					//check episode number in queue
					if (arr_episode_numbers.indexOf(objEpisode.comm_num) > -1 && status == true)
					{
						status = false;
						objEpisode.__status_reason = "An episode already has the set episode number of " + objEpisode.comm_num;
					}//end if
					arr_episode_numbers.push(objEpisode.comm_num);
					
					//update panel class
					if (status == false)
					{
						angular.element('.journey_episode_' + objEpisode.id).toggleClass('panel-primary panel-warning');	
					} else {
						angular.element('.journey_episode_' + objEpisode.id).toggleClass('panel-primary panel-success');
					}//end if
				}//end if
			});
			
			$scope.$digest();
		}//end function
		
		/**
		 * Load journey type specific behaviours attached to the specific journey
		 */
		function loadJourneyBehaviourData()
		{
			/**
			 * Make sure user is logged in
			 */
			userIsLoggedin();
			
			var objRequest = {
					acrq: 'load-journey-behaviours',
					journey_id: $scope.objJourney.id
			};
			
			$scope.objJourneyBehaviours = Array();
			var $p = JourneysPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load Journey Behaviours', '<p>Request failed with response: ' + response.response + '</p>');
						return false;
					}//end if
					
					angular.forEach(response.objData, function (objBehaviour, i) {
						$scope.objJourneyBehaviours.push(objBehaviour);
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
			$scope.loadJourneyBehavioursProgress.addPromise($p);
		}//end function
		
		/**
		 * Load other berhaviours that might influence and deal with this specific journey
		 */
		function loadJourneyRelatedBehaviourData()
		{
			var objRequest = {
					acrq: 'load-journey-related-behaviours',
					journey_id: $scope.objJourney.id
			};
			
			$scope.objJourneyRelatedBehaviours = Array();
			var $p = JourneysPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load Journey Related Behaviours', '<p>Request failed with response: ' + response.response + '</p>');
						return false;
					}//end if
					
					angular.forEach(response.objData, function (objBehaviour, i) {
						$scope.objJourneyRelatedBehaviours.push(objBehaviour);
					});
					
					//trigger build journey diagram function
					loadJourneyFlowDiagram();
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
			$scope.loadJourneyBehavioursProgress.addPromise($p);
		}//end function
		
		function loadJourneyFlowDiagram()
		{
			var objRequest = {
				acrq: 'build-flow-journey-diagram',
				journey_id: $scope.objJourney.id
			};
			
			JourneysPageService.get(objRequest, 
					function success(response) {
						logToConsole(response);
						
						//fail quietly
						buildJourneyDiagram(response.objData);
					},
					function error(errorResponse) {
						logToConsole(errorResponse);
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					}
				);
		}//end function
}]);

journeysControllers.controller('JourneyEpisodesCtrl', [
														'$scope', 
														'$route', 
														'$routeParams', 
														'$window', 
														'JourneysPageService', 
														'JourneyEpisodesPageService',
														'promiseTracker', 
function JourneyEpisodesCtrl($scope, $route, $routeParams, $window, JourneysPageService, JourneyEpisodesPageService, promiseTracker, formlyVersion) {

	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.objPageConfig = global_page_config;
	$scope.pageTitle = '<nav class="navbar navbar-default"><div class="container-fluid"><div class="navbar-header"><span class="navbar-brand"><span style=""><span class="glyphicon glyphicon-volume-down"></span></span>&nbsp; Journey Episodes</span></div></div></nav>';
	$scope.journey_id = false;
	$scope.objJourney = {};
	$scope.objJourneyEpisodes = Array();
	$scope.createJourneyEpisodePanel = false;
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	$scope.loadJourneyEpisodes = promiseTracker();
	
	$scope.createEpisodeForm = {
			model: {},
			fields: [],
			submitForm: function () {
				/**
				 * Make sure user is logged in
				 */
				userIsLoggedin();
				
				$scope.createEpisodeForm.model.acrq = "create-episode";
				$scope.createEpisodeForm.model.journey_id = $scope.objJourney.id;
				
				var $p = JourneyEpisodesPageService.post($scope.createEpisodeForm.model, 
					function success(response) {
						logToConsole(response);
						//check for errors
						if (typeof response.error != 'undefined' && response.error == 1)
						{
							doErrorAlert('Unable to create Episode', '<p>Request failed with response: ' + response.response + '</p>');
							return false;
						}//end if
						
						//dismiss panel
						$scope.togglePanel("createJourneyEpisodePanel", false);
						
						//redirect to the update page
						var url = "#!/episode/" + $scope.objJourney.id + "/" + response.objData.id;
						$window.location.href = url;
					},
					function error(responseError) {
						logToConsole(responseError);
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					}
				);
				$scope.progress.addPromise($p);
			},
			clearForm: function () {
				$scope.createEpisodeForm.model = {};
			}
	};
	
	$scope.init = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.journey_id = $routeParams.journey_id;
		var objRequest = {
				acrq: 'load-journey-data',
				journey_id: $scope.journey_id
		};
		loadJourneyData(objRequest);
	}; //end function

	$scope.refreshData = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var objRequest = {
				acrq: 'load-journey-data',
				journey_id: $scope.objJourney.id
		};
		loadJourneyData(objRequest);
	}; //end function
	
	/**
	 * Activate Journey Episode
	 */
	$scope.activateEpisode = function (objEpisode) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		return toggleEpisodeStatus(objEpisode);
	}; //end function
	
	/**
	 * Deactivate Journey Episode
	 */
	$scope.deactivateEpisode = function (objEpisode) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		return toggleEpisodeStatus(objEpisode);
	}; //end function
	
	/**
	 * Delete Journey Episode
	 */
	$scope.deleteEpisode = function (objEpisode) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		return deleteEpisode(objEpisode);
	}; //end function
	
	/**
	 * Alert user episode is active
	 */
	$scope.alertEpisodeAction = function (c) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		return doEpsiodeActiveAlert(c);
	};
	
	$scope.togglePanel = function (panel, status) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var flag = status;
		$scope[panel] = flag;
		
		if (flag == true) {
			switch (panel) {
				case 'createJourneyEpisodePanel':
					//check if form has been loaded
					if ($scope.createEpisodeForm.fields.length == 0)
					{
						setCreateEpisodeFormFields();
					}//end if
					break;
			}//end switch
			
			doCreateSlidePanel({});
		} else {
			doRemoveSlidePanel({});			
		}//end if
	};
	
	$scope.scrollToEpisode = function (objEpisode) {
	    jQuery('html, body').animate({
	        scrollTop: jQuery('#journey_episode_' + objEpisode.id + '_section').offset().top - 40
	    }, 'slow');
	}; //end function
	
	function loadJourneyData(objRequest) {
		$scope.objJourney = {};
		var $p = JourneysPageService.get(objRequest, 
			function success(response) {
				//check for errors
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					doErrorAlert('Unable to load Journey', '<p>Request failed with response: ' + response.response + '</p>');
					return false;
				}//end if
				
				$scope.objJourney = response.objData;
				
				//format journey expiry date
				if ($scope.objJourney.date_expiry != '' && $scope.objJourney.date_expiry != '0000-00-00')
				{
					var objDate = new Date($scope.objJourney.date_expiry);
					var arr_dates = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
					$scope.objJourney.date_expiry_formatted = objDate.getUTCDate() + " " + objDate.getUTCMonth() + " " + objDate.getUTCFullYear();
				}//end if
				
				//trigger loading journey episode
				loadJourneyEpisodesData();
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		$scope.progress.addPromise($p);
	}; //end function
	
	/**
	 * Request Journey Episodes Data
	 */
	function loadJourneyEpisodesData()
	{
		var objRequest = {
			acrq: 'load-journey-episodes',
			journey_id: $scope.objJourney.id
		};
		
		$scope.objJourneyEpisodes = Array();
		var $p = JourneyEpisodesPageService.get(objRequest, 
			function success(response) {
				logToConsole(response);
				//check for errors
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					doErrorAlert('Unable to load Journey', '<p>Request failed with response: ' + response.response + '</p>');
					return false;
				}//end if
				
				angular.forEach(response.objData, function (objEpisode, i) {
					if (typeof objEpisode.id != "undefined")
					{
						objEpisode.__status_reason = "";
						objEpisode.__timing_delay = "Calculating...";
						$scope.objJourneyEpisodes.push(objEpisode);
					}//end if
				});
				
				setTimeout(function () {
					calculateEpisodePanelStatus();
				}, 600);
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		$scope.loadJourneyEpisodes.addPromise($p);
	}//end function
	
	function calculateEpisodePanelStatus()
	{
		var arr_episode_numbers = Array();
		angular.forEach($scope.objJourneyEpisodes, function (objEpisode, i) {
			if (typeof objEpisode.id != "undefined")
			{
				//calculate delay between episodes
				if (objEpisode.send_time > 0)
				{
					var send_days = 0;
					var send_hours = 0;
					var send_minutes = 0;
					var send_time = parseInt(objEpisode.send_time);
					
					if (parseInt(send_time / 86400) >= 1)
					{
						send_days = parseInt(objEpisode.send_time / 86400);
					}//end if
					
					send_time = parseInt(send_time - (send_days * 86400));
					if (parseInt(send_time / 3600) >= 1)
					{
						send_hours = parseInt(send_time / 3600);
					}//end if
					
					send_time = parseInt(send_time - (send_hours * 3600));
					if (parseInt(send_time / 60) >= 1)
					{
						send_minutes = parseInt(send_time / 60);
					}//end if
					
					var result_str = "";
					if (send_days > 0)
					{
						if (send_days == 1)
						{
							result_str = "1 Day, ";
						} else {
							result_str = send_days + " Days, ";
						}//end if
					}//end if
					
					if (send_hours > 0)
					{
						if (send_hours == 1)
						{
							result_str = result_str + "1 Hour, ";
						} else {
							result_str = result_str + send_hours + " Hours, ";
						}//end if
					}//end if
					
					if (send_minutes > 0)
					{
						if (send_minutes == 1)
						{
							result_str = result_str + "1 Minute ";
						} else {
							result_str = result_str + send_minutes + " Minutes ";
						}//end if
					}//end if
					
					if (objEpisode.comm_num == 1)
					{
						objEpisode.__timing_delay = result_str + " after Journey is started";
					} else {
						objEpisode.__timing_delay = result_str + " after Episode " + (objEpisode.comm_num - 1) + " is sent";
					}//end if
				} else {
					if (objEpisode.comm_num == 1)
					{
						objEpisode.__timing_delay = "Immediatly after Journey is started";
					} else {
						objEpisode.__timing_delay = "Immediatly after Episode " + (objEpisode.comm_num - 1) + " is sent";
					}//end if
				}//end if
				
				var status = true;
				
				//check if journey is active
				if ($scope.objJourney.active == 0)
				{
					status = false;
					objEpisode.__status_reason = "Journey is inactive";
				}//end if
				
				//check if journey has expired
				if (status == true && $scope.objJourney.date_expiry != '' && $scope.objJourney.date_expiry != '0000-00-00')
				{
					var objCurrentDate = new Date();
					var objJourneyDate = new Date($scope.objJourney.date_expiry);
					if (objCurrentDate.getMilliseconds() > objJourneyDate.getMilliseconds())
					{
						status = false;
						objEpisode.__status_reason = "Journey has expired";
					}//end if
				}//end if
				
				//check if episode is active
				if (objEpisode.active == 0 && status == true)
				{
					status = false;
					objEpisode.__status_reason = "Episode is inactive";
				}//end if
				
				//check if episode has any content
				if (objEpisode.content == '' && status == true)
				{
					status = false;
					objEpisode.__status_reason = "Episode does not have any content set";
				}//end if
				
				//check if start date has been reached yet
				if (objEpisode.date_start != '' && objEpisode.date_start != '0000-00-00' && status == true)
				{
					var objCurrentDate = new Date();
					var objEpisodeStartDate = new Date(objEpisode.date_start);
					if (objEpisodeStartDate.getMilliseconds() > objCurrentDate.getMilliseconds())
					{
						status = false;
						objEpisode.__status_reason = "Episode start date has not been reached";
					}//end if
				}//end if
				
				if (objEpisode.date_expiry != '' && objEpisode.date_expiry != '0000-00-00' && status == true)
				{
					var objCurrentDate = new Date();
					var objEpisodeExpiryDate = new Date(objEpisode.date_expiry);
					if (objCurrentDate.getMilliseconds() > objEpisodeExpiryDate.getMilliseconds())
					{
						status = false;
						objEpisode.__status_reason = "Episode has expired";
					}//end if
				}//end if
				
				//check episode number in queue
				if (arr_episode_numbers.indexOf(objEpisode.comm_num) > -1 && status == true)
				{
					status = false;
					objEpisode.__status_reason = "An episode already has the set episode number of " + objEpisode.comm_num;
				}//end if
				arr_episode_numbers.push(objEpisode.comm_num);
				
				//update panel class
				if (status == false)
				{
					angular.element('.journey_episode_' + objEpisode.id).toggleClass('panel-primary panel-warning');	
				} else {
					angular.element('.journey_episode_' + objEpisode.id).toggleClass('panel-primary panel-success');
				}//end if
			}//end if
		});
		
		$scope.$digest();
	}//end function	
	
	/**
	 * Change an episode's status
	 */
	function toggleEpisodeStatus(objEpisode) {
		if (objEpisode.active == 1)
		{
			if (confirm('Are you sure you want to deactivate this episode?') == true)
			{
				//let the process continue
			} else {
				return false;
			}//end if
		}//end if
		
		if (objEpisode.active == 0)
		{
			if (confirm('Are you sure you want to activate this episode?') == true)
			{
				//let the process continue
			} else {
				return false;
			}//end if
		}//end if
		
		var objRequest = {
				acrq: 'toggle-episode-status',
				journey_id: $scope.objJourney.id,
				episode_id: objEpisode.id
		};
		
		var $p = JourneyEpisodesPageService.post(objRequest, 
			function success(response) {
				logToConsole(response);
				//check for errors
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					doErrorAlert('Unable to load Journey Related Behaviours', '<p>Request failed with response: ' + response.response + '</p>');
					return false;
				}//end if
				
				//update episode data in current array
				angular.forEach($scope.objJourneyEpisodes, function (objD, i) {
					if (objD.id == objEpisode.id)
					{
						$scope.objJourneyEpisodes[i].active = response.objData.active;
					}//end if
				});
				
				if (response.objData.active == 1)
				{
					doMessageAlert('Journey Episode Status', '<p>The episode has been activated</p>');
				} else {
					doMessageAlert('Journey Episode Status', '<p>The episode has been deactivated</p>');
				}//end if
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		$scope.progress.addPromise($p);
	}; //end function
	
	/**
	 * Remove a communication from a journey
	 */
	function deleteEpisode(objEpisode) {
		if (confirm("Are you sure you wish to remove this episode? This cannot be undone.") == true)
		{
			var objRequest = {
				'acrq': 'delete-episode',
				journey_id: $scope.objJourney.id,
				episode_id: objEpisode.id
			};
			
			var $p = JourneyEpisodesPageService.post(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to remove Episode', '<p>Request failed with response: ' + response.response + '</p>');
						return false;
					}//end if
					
					//remove data from array
					angular.forEach($scope.objJourneyEpisodes, function (objD, i) {
						if (objEpisode.id == objD.id)
						{
							$scope.objJourneyEpisodes.splice(i, 1);
						}//end if
					});
					
					//reload journey
					var objRequest = {
							acrq: 'load-journey-data',
							journey_id: $scope.objJourney.id
					};
					loadJourneyData(objRequest);
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			$scope.progress.addPromise($p);
		}//end if
	};
	
	/**
	 * Alert user about episode being active
	 */
	function doEpsiodeActiveAlert(c)
	{
		if (typeof c == 'undefined' || c == '')
		{
			c = '<p>This episode is currently activated. As a result, no changes can be made in this state.</p><p>To make changes, first deactivate the episode.</p>';
		}//end if
		
		doInfoAlert('Episode is active', c);
	}; //end function
	
	function setCreateEpisodeFormFields()
	{
		var objRequest = {
			acrq: 'load-episode-admin-form',
			journey_id: $scope.objJourney.id
		};
		
		var $p = JourneyEpisodesPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load Form', '<p>Request failed with response: ' + response.response + '</p>');
						return false;
					}//end if
					
					angular.forEach(response.objData, function(objField, i) {
						switch (objField.key)
						{
							case 'comm_via_id':
							case 'comm_num':
							case 'description':
								
								break;
							
							default:
								//hide field
								objField.hide = true;
								break;
						}//end switch
						
						$scope.createEpisodeForm.fields.push(objField);
					});//end foreach
					
					//set default values
					$scope.createEpisodeForm.model.comm_type_id = 2;
					$scope.createEpisodeForm.model.comm_via_id = 1;
					$scope.createEpisodeForm.model.content = 'Set value';
					$scope.createEpisodeForm.model.subject = 'Set value';
					$scope.createEpisodeForm.model.comm_from = 'sample@mail.com';
					$scope.createEpisodeForm.model.comm_from_name = 'Set value';
					$scope.createEpisodeForm.model.description = 'Set value';
					$scope.createEpisodeForm.model.send_after_hours = 0;
					$scope.createEpisodeForm.model.date_expiry = "";
					$scope.createEpisodeForm.model.priority = 3;
					$scope.createEpisodeForm.model.comm_num = ($scope.objJourney.comms + 1);
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
		$scope.progress.addPromise($p);
	};//end function
}]);

journeysControllers.controller('JourneyEpisodeCtrl', [
														'$scope', 
														'$route', 
														'$routeParams', 
														'$window', 
														'JourneysPageService', 
														'JourneyEpisodesPageService',
														'promiseTracker', 
	function JourneyEpisodeCtrl($scope, $route, $routeParams, $window, JourneysPageService, JourneyEpisodesPageService, promiseTracker, formlyVersion) {
	
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.objPageConfig = global_page_config;
	$scope.pageTitle = '<nav class="navbar navbar-default"><div class="container-fluid"><div class="navbar-header"><span class="navbar-brand"><span style=""><span class="glyphicon glyphicon-volume-down"></span></span>&nbsp; Manage Episode <span id="journey_episode_journey_title"></span></span></div></div></nav>';
	$scope.journey_id = false;
	$scope.objJourney = {};
	$scope.objJourneyEpisode = {};
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	$scope.loadJourneyEpisodes = promiseTracker();
	
	$scope.adminForm = {
			model: {},
			fields: [],
			submitForm: function () {
				/**
				 * Make sure user is logged in
				 */
				userIsLoggedin();
				
				//convert send time to seconds
				var s = 0;
				s = s + ($scope.adminForm.model.send_time_mins * 60) + ($scope.adminForm.model.send_time_hours * 3600) + ($scope.adminForm.model.send_time_days * 86400);
				$scope.adminForm.model.send_time = parseInt(s);
				$scope.adminForm.model.acrq = 'edit-episode';
				$scope.adminForm.model.journey_id = $scope.objJourney.id;
				$scope.adminForm.model.episode_id = $scope.objJourneyEpisode.id;
				
				var $p = JourneyEpisodesPageService.post($scope.adminForm.model, 
					function success(response) {
						logToConsole(response);
						
						//check for errors
						if (typeof response.error != 'undefined' && response.error == 1)
						{
							doErrorAlert('Unable to save Episode', '<p>Request failed with response: ' + response.response + '</p>');
							return false;
						}//end if
						
						//flush the model
						$scope.adminForm.model = {};
						
						//redirect back to journey episodes page
						var url = "#!/episodes/" + $scope.objJourney.id;
						$window.location.href = url;
					},
					function error(errorResponse) {
						logToConsole(errorResponse);
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					}
				);
				
				$scope.progress.addPromise($p);
			}
		};
	
	$scope.init = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.journey_id = $routeParams.journey_id;
		var objRequest = {
			acrq: 'load-journey-data',
			journey_id: $scope.journey_id
		};
		
		loadJourneyData(objRequest);
	}; //end function
	
	$scope.toggleFormSection = function (section) {
		var objFormSections = {
				episode_options: [
					'comm_num',
					'description',
				],
				content_fields: [
					'content',
					'template_id',
				],
				channel_fields: [
					'subject',
					'comm_from',
					'comm_from_name',
					'reply_to',
					'cc',
					'track_opens',
				],
				timing_fields: [
					'send_after',
					//'send_time',
					'send_time_mins',
					'send_time_hours',
					'send_time_days',
					'send_after_hours',
					'date_expiry',
					'priority',
					'send_weekdays',
					'not_send_public_holidays',
				],
				all_fields: []
		};
		
		var arr_visible_fields = objFormSections[section];
		angular.forEach($scope.adminForm.fields, function (objField, i) {
			if (arr_visible_fields.length > 0)
			{
				if (arr_visible_fields.indexOf(objField.key) > -1)
				{
					$scope.adminForm.fields[i].hide = false;
				} else {
					$scope.adminForm.fields[i].hide = true;
				}//end if
			} else {
				//display all fields
				switch (objField.key)
				{
					case 'send_time':
						//ignore these fields
						break;
				
					default:
						$scope.adminForm.fields[i].hide = false;
						break;
				}//end switch
			}//end if
		});
	};
	
	function loadJourneyData(objRequest) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.objJourney = {};
		var $p = JourneysPageService.get(objRequest, 
			function success(response) {
				//check for errors
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					doErrorAlert('Unable to load Journey', '<p>Request failed with response: ' + response.response + '</p>');
					return false;
				}//end if
				
				$scope.objJourney = response.objData;
				
				//update page header
				angular.element('#journey_episode_journey_title').html(' (' + $scope.objJourney.journey + ')');
				
				//format journey expiry date
				if ($scope.objJourney.date_expiry != '' && $scope.objJourney.date_expiry != '0000-00-00')
				{
					var objDate = new Date($scope.objJourney.date_expiry);
					var arr_dates = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
					$scope.objJourney.date_expiry_formatted = objDate.getUTCDate() + " " + objDate.getUTCMonth() + " " + objDate.getUTCFullYear();
				}//end if
				
				//load episode 
				loadEpisodeData();
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		$scope.progress.addPromise($p);
	}; //end function
	
	function loadEpisodeData()
	{
		var objRequest = {
			acrq: 'load-journey-episode',
			journey_id: $scope.objJourney.id,
			episode_id: $routeParams.episode_id
		};
		
		$scope.objJourneyEpisode = {};
		var $p = JourneyEpisodesPageService.get(objRequest, 
				function success(response) {
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load Episode', '<p>Request failed with response: ' + response.response + '</p>');
						return false;
					}//end if
					
					$scope.objJourneyEpisode = response.objData;
					$scope.adminForm.model = $scope.objJourneyEpisode;
					if ($scope.adminForm.model.date_expiry == '0000-00-00')
					{
						$scope.adminForm.model.date_expiry = "";
					}//end if
					
					//set default values for sliders
					$scope.adminForm.model.send_time_mins = 0;
					$scope.adminForm.model.send_time_hours = 0;
					$scope.adminForm.model.send_time_days = 0;
					
					//calculate approriate values for the sliders
					var send_time = parseInt($scope.adminForm.model.send_time);
					if (parseInt(send_time / 86400) >= 1)
					{
						$scope.adminForm.model.send_time_days = (send_time / 86400);
					}//end if

					send_time = send_time - (parseInt($scope.adminForm.model.send_time_days) * 86400);
					if (parseInt(send_time / 3600) >= 1)
					{
						$scope.adminForm.model.send_time_hours = parseInt(send_time / 3600);
					}//end if
					
					send_time = send_time - (parseInt($scope.adminForm.model.send_time_hours) * 3600);
					if (parseInt(send_time / 60) >= 1)
					{
						$scope.adminForm.model.send_time_mins = parseInt(send_time / 60);
					}//end if
					
					//now with all the required data loaded, load form details
					setAdminFormFields();
					
					//switch content section of form
					setTimeout(function () {
						$scope.toggleFormSection('content_fields');
					}, 1500);
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
		$scope.progress.addPromise($p);
	}//end function
	
	function setAdminFormFields()
	{
		if ($scope.adminForm.fields.length > 0)
		{
			return $scope.adminForm.fields;
		}//end if
		
		var objRequest = {
			acrq: 'load-episode-admin-form',
			journey_id: $scope.objJourney.id,
			episode_id: $scope.objJourneyEpisode.id
		};
		var $p = JourneyEpisodesPageService.get(objRequest, 
				function success(response) {
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load Form', '<p>Request failed with response: ' + response.response + '</p>');
						return false;
					}//end if

					angular.forEach(response.objData, function (objField, i) {
						switch (objField.key)
						{
							case 'content':
								objField.type = 'tinymce';
								objField.data = {'tinymceOption': angularTinymceConfig()};
								break;
								
							case 'send_time':
								objField.hide = true; //replaced by slider fields
								break;
								
							case 'date_expiry':
							case 'date_start':
							    objField.ngModelElAttrs = {
							        'data-provide': 'datepicker',
							        'data-date-format': 'dd M yyyy',
							        'data-date-clear-btn': 'true',
							        'data-date-autoclose': 'true',
							        'data-date-today-highlight': 'true',
							        'data-date-today-btn': 'true',
							        'readonly': 'readonly',
							      }
								break;
						}//end switch
						
						$scope.adminForm.fields.push(objField);
					});
					
					//add send time slider fields
					$scope.adminForm.fields.push({
						key: 'send_time_mins',
						type: 'send-time-mins-slider',
						modelOptions: {
							getterSetter: true
						},
						templateOptions: {
							'label': 'Delivery delay (Minutes)',
							'sliderOptions': {
								'floor': 0,
								'ceil': 59,
							},
						},
						validation: {
							show: true
						}
					});
					
					$scope.adminForm.fields.push({
						key: 'send_time_hours',
						type: 'send-time-hours-slider',
						modelOptions: {
							getterSetter: true
						},
						templateOptions: {
							'label': 'Delivery delay (Hours)',
							'sliderOptions': {
								'floor': 0,
								'ceil': 23,
							},
						},
						validation: {
							show: true
						}
					});
					
					$scope.adminForm.fields.push({
						key: 'send_time_days',
						type: 'send-time-days-slider',
						modelOptions: {
							getterSetter: true
						},
						templateOptions: {
							'label': 'Delivery delay (Days)',
							'sliderOptions': {
								'floor': 0,
								'ceil': 180,
							},
						},
						validation: {
							show: true
						}
					});					
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
		$scope.progress.addPromise($p);
	}//end function
}]);