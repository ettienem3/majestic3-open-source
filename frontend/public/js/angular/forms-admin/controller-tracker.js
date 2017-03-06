'use strict';

formsAdminControllers.controller('TrackerFormCtrl', ['$scope', '$rootScope', '$route', '$routeParams', '$window', 'FormAdminPageService', 'promiseTracker', function TrackerFormCtrl($scope, $rootScope, $route, $routeParams, $window, FormAdminPageService, promiseTracker) {
	
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
		if (typeof $rootScope.edit_tracker_id != 'undefined' && $rootScope.edit_tracker_id > 0)
		{
			//update a form
			objData.acrq = 'update-form-config-data';
			objData.id = $rootScope.edit_tracker_id;
		} else {
			//create a new form
			objData.acrq = 'create-tracker-form';
		}//end if
		
		var $promise = FormAdminPageService.post(objData,
			function success(response) {
				logToConsole(response);
				
				if (typeof $rootScope.edit_tracker_id != 'undefined' && response.error == 0)
				{
					$scope.vm.model = {};
					delete $rootScope.edit_tracker_id;
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
					
					doMessageAlert('Tracker form has been created', '<p>The form has been created, you can start allocating fields to the form now.</p>');
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
	
	$rootScope.$on('loadTrackerFormAdminFields', function () {
		//load form data
		if (typeof $rootScope.edit_tracker_id != 'undefined' && $rootScope.edit_tracker_id > 0)
		{
			var form_id = $rootScope.edit_tracker_id;
			var objRequest = {
					'fid': form_id,
					'acrq': 'load-form-config-data'
			};
			
			var $promise = FormAdminPageService.get(objRequest,
					function success(response) {
						logToConsole(response);
						
						//set some default values
						$scope.vm.model = {
								fk_form_type_id: 3,
								secure_form: 0,
								template_id: '',
								populate_form: 0,
								show_fwd_warn: '',
								copy: '',
								copy2: '',
								terms: '',
								submit_button: '',
								form_js_file: '',
								submit_copy:  '',
								reject_copy:  '',
								submit_tracking_script:  '',
								redirect:  '',
								redirect_parent:  '',
								default_source:  '',
								duplicate_behaviour:  '',
								default_reference:  '',
								captcha:  '',
								id_required:  '',
								form_password:  '',
								user_login_allocate:  '',
								_notify_copy:  '',
								_notify:  '',
								_notify_additional:  '',
								notify_active:  '',
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
					fk_form_type_id: 3,
					secure_form: 0,
					active: 1,
					template_id: '',
					populate_form: 0,
					show_fwd_warn: '',
					copy: '',
					copy2: '',
					terms: '',
					submit_button: '',
					form_js_file: '',
					submit_copy:  '',
					reject_copy:  '',
					submit_tracking_script:  '',
					redirect:  '',
					redirect_parent:  '',
					default_source:  '',
					duplicate_behaviour:  '',
					default_reference:  '',
					captcha:  '',
					id_required:  '',
					form_password:  '',
					user_login_allocate:  '',
					_notify_copy:  '',
					_notify:  '',
					_notify_additional:  '',
					notify_active:  '',
			};
		}//end if
		
		//load fields from preset config
		$scope.vm.fields = formAdminFormSimple('__sales_funnel');
		if ($scope.vm.fields.length > 0)
		{
			return;
		}//end if
		
		var $promise = FormAdminPageService.get({acrq: 'load-admin-form-tracker'}, 
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
						case 'template_id':
						case 'populate_form':
						case 'show_fwd_warn':
						case 'copy':
						case 'copy2':
						case 'terms':
						case 'submit_button':
						case 'form_js_file':
						case 'submit_copy':
						case 'reject_copy':
						case 'submit_tracking_script':
						case 'redirect':
						case 'redirect_parent':
						case 'default_source':
						case 'duplicate_behaviour':
						case 'default_reference':
						case 'captcha':
						case 'id_required':
						case 'form_password':
						case 'user_login_allocate':
						case '_notify_copy':
						case '_notify':
						case '_notify_additional':
						case 'notify_active':
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
}]);
