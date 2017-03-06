'use strict';

formsAdminControllers.controller('WebFormCtrl', ['$scope', '$rootScope', '$route', '$routeParams', '$window', 'FormAdminPageService', 'promiseTracker', function WebFormCtrl($scope, $rootScope, $route, $routeParams, $window, FormAdminPageService, promiseTracker) {
	$scope.objFormSummaryCache = {};
	
	//formly
	$scope.vm = this;
	$scope.vm.fields = [];
	$scope.vm.model = {};
	
	$scope.vm.submitForm = function () {
		var objData = {};
		
		//gather all fields from the form for controller validation
		angular.forEach($scope.vm.fields, function (objField, i) {
			if (typeof objField.templateOptions.default_value !== 'undefined')
			{
				objData[objField.key] = objField.templateOptions.default_value;
			} else {
				objData[objField.key] = '';
			}//end if
		});
		
		//add data from the model to the form data
		angular.forEach($scope.vm.model, function(v, i) {
			objData[i] = v;
		});

		//submit the data
		if (typeof $rootScope.edit_form_id != 'undefined' && $rootScope.edit_form_id > 0)
		{
			//update a form
			objData.acrq = 'update-form-config-data';
			objData.id = $rootScope.edit_form_id;
		} else {
			//create a new form
			objData.acrq = 'create-web-form';
		}//end if

		$scope.vm.fields.objCustomErrors = Array();
		var $promise = FormAdminPageService.post(objData,
			function success(response) {
				logToConsole(response);
				
				if (typeof $rootScope.edit_form_id != 'undefined' && response.error == 0)
				{
					$scope.vm.model = {};
					delete $rootScope.edit_form_id;
					$rootScope.$emit('closeEditFormPanelState', {});
					$rootScope.$emit('refreshIndexData', {});
				} else {
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						if (typeof response.form_messages != 'undefined')
						{
							handleFormlyFormValidationErrors($scope.vm.fields, $scope.vm.model, response.form_messages);	
							return false;
						}//end if
					}//end if
					
					if (typeof response.objData != 'undefined' && typeof response.objData.errors != 'undefined')
					{
						handleFormlyFormValidationErrors($scope.vm.fields, $scope.vm.model, response.objData.errors);	
						return false;
					}//end if
					
					doMessageAlert('Web form has been created', '<p>The form has been created, you can start allocating fields to the form now.</p>');			
				}//end if
			},
			function error(errorResponse) {
				logToCOnsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		// Track the request and show its progress to the user.
		$scope.progress.addPromise($promise);
	}; //end function
	
	/**
	 * Clear form fields
	 */
	$scope.vm.clearForm = function () {
		//only clear fields available on form from model
		angular.forEach($scope.vm.fields, function(objField, i) {
			if (typeof $scope.vm.model[objField.key] != 'undefined')
			{
				delete($scope.vm.model[objField.key]);
				
				switch (objField.key)
				{
					case 'submit_button':
						$scope.vm.model[objField.key] = 'Submit';
						break;
						
					case 'active':
						$scope.vm.model[objField.key] = 1;
						break;
						
					case 'populate_form':
						$scope.vm.model[objField.key] = 1;
						break;
				}//end if
			}//end if
		});
	};
	
	$rootScope.$on('loadWebFormAdminFields', function () {
		//load form data
		if (typeof $rootScope.edit_form_id != 'undefined' && $rootScope.edit_form_id > 0)
		{
			var form_id = $rootScope.edit_form_id;
			var objRequest = {
					'fid': form_id,
					'acrq': 'load-form-config-data'
			};
			
			var $promise = FormAdminPageService.get(objRequest,
					function success(response) {
						logToConsole(response);
						
						//set some default values
						$scope.vm.model = {
								fk_form_type_id: 1,
								secure_form: 1
						};
						
						angular.forEach(response.objData, function (value, key) {
							switch (key)
							{
								default:
									$scope.vm.model[key] = value;
									break;
							}//end switch
						});
					},
					function error(errorResponse) {
						logToConsole(errorResponse);
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					}
			);
			// Track the request and show its progress to the user.
			$scope.progress.addPromise($promise);
		} else {
			//set some default values
			$scope.vm.model = {
					fk_form_type_id: 1,
					secure_form: 1,
					active: 1,
					populate_form: 1,
					submit_button: 'Submit',
					_notify: 9,
			};
			
			//add remainder advanced fields default values
			angular.forEach(formAdminFormAdvanced(), function (objGroup, i) {
				angular.forEach(objGroup.fieldGroup, function (objField, ii) {
					var value = '';
					if (typeof objField.templateOptions != 'undefined' && typeof objField.templateOptions.defaultValue != 'undefined')
					{
						value = objField.templateOptions.defaultValue;
					}//end if
					
					$scope.vm.model[objField.key] = value;
				});
			});
		}//end if

		//load fields from preset config
		$scope.vm.fields = formAdminFormSimple('__web');
		if ($scope.vm.fields.length > 0)
		{
			$scope.vm.fields.objCustomErrors = Array();
			return;
		}//end if
		
		var $promise = FormAdminPageService.get({acrq: 'load-admin-form-web'}, 
			function success(response) {
				logToConsole(response);
				
				//clear any existing fields
				$scope.vm.fields = [];
				angular.forEach(response.objData, function (objField, id) {
					objField.modelOptions.getterSetter = false;
					
					//remove form type select
					switch (objField.key)
					{
						case 'fk_form_type_id':
						case 'fk_campaign_id':
						case 'secure_form':
						case 'viral_duplicates':
						case 'viral_referrals':
						case 'viral_hide_referrals':
						case 'viral_populate':
							//do not add these fields
							break;
						
						default:
							$scope.vm.fields.push(objField);
							break;
					}//end switch
				});
				$scope.messages = '';
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		// Track the request and show its progress to the user.
		$scope.progress.addPromise($promise);
	});
	
	/**
	 * Load form summary details
	 */
	$rootScope.$on('loadWebFormSummary', function () {
		//is data cached
		if (typeof $scope.objFormSummaryCache[$rootScope.id_web_form_summary] != 'undefined')
		{
			$rootScope.form_summary = $scope.objFormSummaryCache[$rootScope.id_web_form_summary];
			$rootScope.$emit("setFormSummaryData", {});
			return;
		}//end if
		
		var objRequest = {
				acrq: 'load-form-summary',
				fid: $rootScope.id_web_form_summary
		};
		
		FormAdminPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					$rootScope.form_summary = response.objData;
					//cache data
					$scope.objFormSummaryCache[$rootScope.id_web_form_summary] = response.objData;
					$rootScope.$emit("setFormSummaryData", {});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	});	
}]);
