'use strict';

var linksControllers = angular.module('linksControllers', []);

linksControllers.controller('HomeCtrl', ['$scope', '$route', '$routeParams', '$window', 'LinksPageService', 'promiseTracker', function HomeCtrl($scope, $route, $routeParams, $window, LinksPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.objPageConfig = global_page_config;
	$scope.objRecords = [];
	$scope.objDeleteRecord = false;
	$scope.objCurrentRecord = {};
	$scope.objLinkBehaviours = [];
	$scope.objLinkAvailableBehaviours = [];
	$scope.updateLinkBehaviourConfigFlag = false;

	$scope.createFormState = false;
	$scope.editFormState = false;
	$scope.deleteFormState = false;
	$scope.behavioursFormState = false;
	$scope.submitted = false;
	$scope.messages = false;
	
	//pagination
	$scope.previousPage = 1;
	$scope.currentPage = 1;
	$scope.pageSize = 20;
	
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
		if (typeof $scope.objPageConfig.pagination.page_urls !== "undefined" && page > 0)
		{
			if (typeof $scope.objPageConfig.pagination.page_urls[parseInt(page - 1)] !== "undefined")
			{
				start_number  = $scope.objPageConfig.pagination.page_urls[parseInt(page - 1)].next;	
			}//end if
		}//end if
		
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		LinksPageService.get({acrq: 'index', 'qp_limit': $scope.objPageConfig.pagination.qp_limit, 'qp_start': start_number}, 
				function success(response) {
					angular.forEach(response.objData, function (obj, i) {
						if (i > -1)
						{
							$scope.objRecords.push(obj);
						}//end if
					});
					
					$scope.pageContent = '';
					$scope.objPageConfig.pagination = response.objData.hypermedia.pagination;
					$scope.objPageConfig.pagination.tpages = [];
					for (var i = 0; i < response.objData.hypermedia.pagination.pages_total; i++)
					{
						$scope.objPageConfig.pagination.tpages.push({i:1});
					}//end for
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					$scope.pageContent = '';
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	};
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	$scope.loadlinkBehaviourConfigForm = promiseTracker();
	
	//create form
	$scope.vm = this;
	$scope.vm.model = {};
	$scope.vm.env = {
    	      angularVersion: angular.version.full,
    	      formlyVersion: formlyVersion
    	    };
	$scope.vm.formFields = prepareFormFields(global_form_fields);
	$scope.vm.originalFields = angular.copy($scope.vm.formFields);
	
	//behaviours form model
	$scope.objBehaviourConfigForm = {
		model: {},
		fields: [],
		submitForm: function () {
			if (typeof $scope.objBehaviourConfigForm.model.id != 'undefined')
			{
				//update behaviour
				$scope.objBehaviourConfigForm.model.acrq = 'update-link-behaviour';
			} else {
				//create behaviour
				$scope.objBehaviourConfigForm.model.acrq = 'create-link-behaviour';
				$scope.objBehaviourConfigForm.model.fk_links_id = $scope.objCurrentRecord.id;
				$scope.objBehaviourConfigForm.model.beh_action = $scope.objBehaviourConfigForm.objSelectedBehaviourDetails.action;
			}//end if
			
			var $p = LinksPageService.post($scope.objBehaviourConfigForm.model, 
				function success(response) {
					logToConsole(response);
					
					//check of errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Failed to save Link Behaviour', '<p>Request failed with: ' + response.response + '</p>');
						return false;
					}//end if
					
					//all good, reset and reload behaviours
					$scope.objBehaviourConfigForm.model = {};
					$scope.objBehaviourConfigForm.fields = [];
					$scope.objBehaviourConfigForm.objSelectedBehaviourDetails = {},
					$scope.updateLinkBehaviourConfigFlag = false;
					$scope.loadLinkBehaviours();
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
		},
		objSelectedBehaviourDetails: {},
		objAdditionalData: false,
	};
	
	//load default content
	$scope.loadRecords = function () {	
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		LinksPageService.get({acrq: 'index', 'qp_limit': 20}, 
			function success(response) {
				angular.forEach(response.objData, function (obj, i) {
					if (i > -1)
					{
						$scope.objRecords.push(obj);
					}//end if
				});
				
				$scope.pageContent = '';
				$scope.objPageConfig.pagination = response.objData.hypermedia.pagination;
				$scope.objPageConfig.pagination.tpages = [];
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
	}; //end function
	
	$scope.refreshRecords = function () {
		$scope.objRecords = Array();
		$scope.loadRecords();
	}; //end function
	
	$scope.toggleForm = function (form_flag, record_id) {
		switch(form_flag)
		{
			case 'createFormState':
				$scope.createFormState = !$scope.createFormState;
				var flag = $scope.createFormState;
				
				//set form model records
				angular.forEach($scope.vm.formFields, function (objField, i) {
					$scope.vm.model[objField.key] = '';
					$scope.vm.formFields[i].value('');
				});
				break;
				
			case 'editFormState':
				$scope.editFormState = !$scope.editFormState;
				var flag = $scope.editFormState;
				
				if (record_id > -1)
				{
					$scope.record_id = record_id;
					var objRecord = {};
					angular.forEach($scope.objRecords, function (obj, i) {
						if (obj.id == record_id)
						{
							objRecord = obj;
						}//end if
					});
					
					//set form model records
					angular.forEach($scope.vm.formFields, function (objField, i) {
						$scope.vm.model[objField.key] = objRecord[objField.key];
						$scope.vm.formFields[i].value(objRecord[objField.key]);
					});
				}//end if
				break;
				
			case 'deleteFormState':
				$scope.deleteFormState = !$scope.deleteFormState;
				var flag = $scope.deleteFormState;
				
				if (record_id > -1)
				{
					$scope.record_id = record_id;
					angular.forEach($scope.objRecords, function (obj, i) {
						if (obj.id == record_id)
						{
							$scope.objDeleteRecord = obj;
							if (obj.active == "1")
							{
								$scope.objDeleteRecord.status = 'Active';
							} else {
								$scope.objDeleteRecord.status = 'No Active';
							}//end if
							
						}//end if
					});
				}//end if
				break;
				
			case 'behavioursFormState':
				$scope.behavioursFormState = !$scope.behavioursFormState;
				var flag = $scope.behavioursFormState;
				
				if (flag == true)
				{
					loadLinkBehaviours(record_id);
					$scope.objCurrentRecord.id = record_id;
				} else {
					//clear behaviours object
					$scope.objLinkBehaviours = [];
				}//end if
				break;
		}//end switch
		
		if (flag == true)
		{
			doCreateSlidePanel({});
		} else {
			doRemoveSlidePanel({});
		}//end if
	};
	
	//process forms
	$scope.submitCreateForm = function (form) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.submitted = true;

		if (form.$invalid)
		{
			return;
		}//end if

		//extract data
		angular.forEach($scope.vm.formFields, function (objField, i) {
			$scope.vm.model[$scope.vm.formFields[i].key] = $scope.vm.formFields[i].formControl.$modelValue;
		});
		
		$scope.vm.model.acrq = 'create';				
		var $promise = LinksPageService.createRecord($scope.vm.model, 
			function success(response) {
				logToConsole(response);
				$scope.toggleForm('createFormState', false);
				doMessageAlert('Data saved', '<p>Tracking link data has been saved</p>');
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				$scope.toggleForm('createFormState', false);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
	  // Track the request and show its progress to the user.
	  $scope.progress.addPromise($promise);
	}//end function
	
	$scope.submitEditForm = function (form) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.submitted = true;

		if (form.$invalid)
		{
			return;
		}//end if
		
		$scope.vm.model.acrq = 'edit';
		$scope.vm.model.id = $scope.record_id;
		
		if (typeof $scope.vm.model.track_multiple == "undefined")
		{
			$scope.vm.model.track_multiple = "0";
		}//end if
		
		var $promise = LinksPageService.editRecord($scope.vm.model, 
			function success(response) {
				logToConsole(response);
				$scope.toggleForm('editFormState', false);
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				$scope.toggleForm('editFormState', false);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
	  // Track the request and show its progress to the user.
	  $scope.progress.addPromise($promise);
	}//end function
	
	$scope.submitDeleteForm = function (lid) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var $promise = LinksPageService.deleteRecord({id: lid, acrq: 'delete'}, 
				function success(response) {
					logToConsole(response);
					doMessageAlert('Data saved', '<p>Tracking link has been removed</p>');
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
		  // Track the request and show its progress to the user.
		  $scope.progress.addPromise($promise);
	};//end function
	
	$scope.submitToggleStatus = function (lid) {
		var $promise = LinksPageService.get({id: lid, acrq: 'toggleStatus'}, 
				function success(response) {
					logToConsole(response);
					doMessageAlert('Data saved', '<p>Tracking link data saved</p>');
					
					//update record
					angular.forEach($scope.objRecords, function (objLink, i) {
						if (objLink.id == lid)
						{
							$scope.objRecords[i].active = (1 - (objLink.active * 1));
						}//end if
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
		  //Track the request and show its progress to the user.
		  $scope.progress.addPromise($promise);
	}; //end function
	
	$scope.loadLinkBehaviours = function () {
		return loadLinkBehaviours($scope.objCurrentRecord.id);
	};
	
	$scope.loadAvailableLinkBehaviours = function () {
		$scope.objLinkAvailableBehaviours = Array();
		var objRequest = {
			acrq: 'load-link-available-behaviour-actions',
			id: $scope.objCurrentRecord.id
		};
		
		var $p = LinksPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load Behaviour Actions', '<p>Request failed with: ' + response.response + '</p>');
						return false;
					}//end if
					
					angular.forEach(response.objData, function (objB, i) {
						$scope.objLinkAvailableBehaviours.push(objB);
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
		  // Track the request and show its progress to the user.
		  $scope.progress.addPromise($p);
		  return $p;
	};
	
	$scope.loadLinkBehaviourCreateForm = function (objAction) {
		var arr_fields = loadLinkBehaviourConfigFormFields(objAction.action);
		
		if ($scope.objBehaviourConfigForm.objAdditionalData == false)
		{
			var objRequest = {
					acrq: 'load-link-behaviours-additional-data',
			};
			
			var $p = LinksPageService.get(objRequest, 
					function success(response) {
						logToConsole(response);
						
						//check for errors
						if (typeof response.error != 'undefined' && response.error == 1)
						{
							doErrorAlert('Unable to load Behaviour Data', '<p>Request failed with: ' + response.response + '</p>');
							return false;
						}//end if
						
						$scope.objBehaviourConfigForm.objAdditionalData = response.objData;
					},
					function error(errorResponse) {
						logToConsole(errorResponse);
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					}
				);
				
			  // Track the request and show its progress to the user.
			  $scope.progress.addPromise($p);
			  $p.$promise.then(function () {
				  $scope.loadLinkBehaviourCreateForm(objAction);
			  });
		}//end if
	
		//populate fields where required
		if ($scope.objBehaviourConfigForm.objAdditionalData != false)
		{
			switch (objAction.action)
			{
				case '__links_journey_start':
				case '__links_journey_stop':
					//find journeys field
					angular.forEach(arr_fields, function (objField, i) {
						if (objField.key == 'fk_journey_id')
						{
							angular.forEach($scope.objBehaviourConfigForm.objAdditionalData.objJourneys, function (objJourney, ii) {
								arr_fields[i].templateOptions.options.push({
									optionID: objJourney.id,
									optionLabel: objJourney.journey,
								});
							});
						}//end if
					});
					break;
					
				case '__links_registration_status_change':
					//find status field
					angular.forEach(arr_fields, function (objField, i) {
						if (objField.key == 'fk_reg_status_id')
						{
							angular.forEach($scope.objBehaviourConfigForm.objAdditionalData.objStatuses, function (objStatus, ii) {
								arr_fields[i].templateOptions.options.push({
									optionID: objStatus.id,
									optionLabel: objStatus.status,
								});
							});
						}//end if
					});					
					break;
			}//end switch
		}//end if
		
		$scope.objBehaviourConfigForm.fields = arr_fields;
		$scope.objBehaviourConfigForm.objSelectedBehaviourDetails = objAction;
	};
	
	$scope.updateLinkBehaviour = function (objBehaviour) {
		$scope.updateLinkBehaviourConfigFlag = true;
		$scope.objBehaviourConfigForm.model = objBehaviour;
		$scope.objBehaviourConfigForm.objSelectedBehaviourDetails = objBehaviour;
		$scope.loadLinkBehaviourCreateForm({action: objBehaviour.action});
	}; //end function
	
	$scope.toggleLinkBehaviourStatus = function (objBehaviour) {
		objBehaviour.acrq = 'toggle-link-behaviour-status';
		var $p = LinksPageService.post(objBehaviour, 
				function success(response) {
					logToConsole(response);
					
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to update Behaviour', '<p>Request failed with: ' + response.response + '</p>');
						return false;
					}//end if
					
					//reload behaviours
					$scope.loadLinkBehaviours();
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
		  // Track the request and show its progress to the user.
		  $scope.progress.addPromise($p);
		  return $p;
	}; //end function
	
	$scope.deleteLinkBehaviour = function (objBehaviour) {
		if (confirm('Are you sure you want to remove this behaviour?') != true)
		{
			return false;
		}//end if
		
		var objRequest = {
				acrq: 'delete-link-behaviour',
				id: objBehaviour.id
		};
		
		var $p = LinksPageService.post(objRequest, 
				function success(response) {
					logToConsole(response);
					
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to remove Behaviour', '<p>Request failed with: ' + response.response + '</p>');
						return false;
					}//end if
					
					//reload behaviours
					$scope.loadLinkBehaviours();
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
		  // Track the request and show its progress to the user.
		  $scope.progress.addPromise($p);
		  return $p;
	}; //end function
	
	function loadLinkBehaviours(lid) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.objLinkBehaviours = [];
		var objRequest = {
			acrq: 'load-link-behaviours',
			id: lid
		};
		
		var $promise = LinksPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load Behaviours', '<p>Request failed with: ' + response.response + '</p>');
						return false;
					}//end if
					
					angular.forEach(response.objData, function (objB, i) {
						$scope.objLinkBehaviours.push(objB);
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
		  // Track the request and show its progress to the user.
		  $scope.progress.addPromise($promise);
		  return $promise;
	}; //end function
}]);
