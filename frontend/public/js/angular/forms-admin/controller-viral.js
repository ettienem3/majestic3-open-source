'use strict';

formsAdminControllers.controller('ViralFormCtrl', ['$scope', '$rootScope', '$route', '$routeParams', '$window', 'FormAdminPageService', 'promiseTracker', function ViralFormCtrl($scope, $rootScope, $route, $routeParams, $window, FormAdminPageService, promiseTracker, formlyVersion) {
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
		
		if (typeof objData.form_js_file == 'undefined')
		{
			objData.form_js_file = '';
		}//end if
		
		if (typeof objData.form_forward_data_format == 'undefined')
		{
			objData.form_forward_data_format = '';
		}//end if

		//submit the data
		if (typeof $rootScope.edit_viral_id != 'undefined' && $rootScope.edit_viral_id > 0)
		{
			//update a form
			objData.acrq = 'update-form-config-data';
			objData.id = $rootScope.edit_viral_id;
		} else {
			//create a new form
			objData.acrq = 'create-viral-form';
		}//end if
		
		var $promise = FormAdminPageService.post(objData,
			function success(response) {
				logToConsole(response);
				
				if (typeof $rootScope.edit_viral_id != 'undefined' && response.error == 0)
				{
					$scope.vm.model = {};
					delete $rootScope.edit_viral_id;
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
					
					doMessageAlert('Referral form has been created', '<p>The form has been created, you can start allocating fields to the form now.</p>');
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
	
	$rootScope.$on('loadViralFormAdminFields', function () {
		//load form data
		if (typeof $rootScope.edit_viral_id != 'undefined' && $rootScope.edit_viral_id > 0)
		{
			var form_id = $rootScope.edit_viral_id;
			var objRequest = {
					'fid': form_id,
					'acrq': 'load-form-config-data'
			};
			
			var $promise = FormAdminPageService.get(objRequest,
					function success(response) {
						logToConsole(response);
						
						//set some default values
						$scope.vm.model = {
								fk_form_type_id: 2,
								secure_form: 0
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
					fk_form_type_id: 2,
					secure_form: 1,
					active: 1,
			};
		}//end if
		
		//load fields from preset config
		$scope.vm.fields = formAdminFormSimple('__viral');
		if ($scope.vm.fields.length > 0)
		{
			return;
		}//end if
		
		var $promise = FormAdminPageService.get({acrq: 'load-admin-form-viral'}, 
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
							//do not add these fields
							break;
							
						case 'viral_duplicates':
						case 'viral_referrals':
						case 'viral_hide_referrals':
						case 'viral_populate':
							objField.templateOptions.required = true;
							$scope.vm.fields.push(objField);
							break;
							
						default:
							$scope.vm.fields.push(objField);
							break;
					}//end switch
				});
				console.log($scope.vm.fields);
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
	$rootScope.$on('loadViralFormSummary', function () {
		//is data cached
		if (typeof $scope.objFormSummaryCache[$rootScope.id_viral_form_summary] != 'undefined')
		{
			$rootScope.form_summary = $scope.objFormSummaryCache[$rootScope.id_viral_form_summary];
			$rootScope.$emit("setFormSummaryData", {});
			return;
		}//end if
		
		var objRequest = {
				acrq: 'load-form-summary',
				fid: $rootScope.id_viral_form_summary
		};
		
		FormAdminPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					$rootScope.form_summary = response.objData;
					//cache data
					$scope.objFormSummaryCache[$rootScope.id_viral_form_summary] = response.objData;
					$rootScope.$emit("setFormSummaryData", {});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	});
}]);
