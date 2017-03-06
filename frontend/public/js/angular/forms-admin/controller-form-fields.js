'use strict';

formsAdminControllers.controller('FormFieldsCtrl', ['$scope', '$rootScope', '$route', '$routeParams', '$window', 'FormAdminPageService', 'promiseTracker', function FormFieldsCtrl($scope, $rootScope, $route, $routeParams, $window, FormAdminPageService, promiseTracker, formlyVersion) {
	
	$scope.form_id = false;
	$scope.objData = false;
	$scope.display_form_field_admin_section = false;
	$scope.form_field_placeholder = false;
	$scope.display_wait_image = false;
	
	//behaviour vars
	$scope.objCurrentFormField = false;
	$scope.display_wait_image_field_behaviours = false;
	$scope.display_form_field_behaviour_admin_section = false;
	$scope.display_form_field_config_form = false;
	$scope.display_wait_image_field_behaviours_form_loading = false;
	$scope.objFormFieldBehaviours = [];
	$scope.objFormFieldBehaviourAvailableActions = [];
	$scope.beh = this; //formly structure
	$scope.beh.fields = [];
	$scope.beh.model = {};
	
	//hook
	$rootScope.$on('administrateFormFields', function () {
		$scope.form_id = $rootScope.id_admin_form_fields;
		$scope.display_form_field_admin_section = false;
		loadFormFields();
	}); //end function
	
	//formly
	$scope.vm = this;
	$scope.vm.fields = [];
	$scope.vm.model = {};
	
	/**
	 * Create or Update a field allocated to a form
	 */
	$scope.vm.submitForm = function () {
		if (typeof $scope.objData.objForm != "undefined")
		{
			var objField = $scope.form_field_placeholder;
	
			angular.forEach($scope.vm.model, function(value, field) {
				objField[field] = value;
			});
	
			$scope.display_form_field_admin_section = false;
	
			if (typeof $scope.vm.model.existing_field != "undefined" && $scope.vm.model.existing_field == true)
			{
				//update field
				//set additional request values
				$scope.vm.model.acrq = 'update-field-allocated-to-form';
				$scope.vm.model.fid = $scope.form_id;
				$scope.vm.model.field_id = objField.id;
				if ($scope.form_field_placeholder.fields_std_id > 0)
				{
					$scope.vm.model.field_type = 'standard';
					$scope.vm.model.field_id = $scope.form_field_placeholder.fields_std_id;
				} else {
					$scope.vm.model.field_type = 'custom';
					$scope.vm.model.field_id = $scope.form_field_placeholder.fields_custom_id;
				}//end if
				
				var $promise = FormAdminPageService.post($scope.vm.model, 
						function success(response) {
							logToConsole(response);
							
							//check repsponse for errors
							if (typeof response.error == 'undefined' || (typeof response.error != 'undefined' && response.error != 1))
							{
								$scope.objData = response.objData;
								//reload form fields
								loadFormFields();
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
								
								doErrorAlert('Field operation failed', '<p>Data could not be saved, an unknown error has occurred</p>');
							}//end if
						},
						function error(errorResponse) {
							logToConsole(errorResponse);
							doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
						}
					);
					
					// Track the request and show its progress to the user.
					$scope.progress.addPromise($promise);
			} else {
				//create a new field
				//set additional request values
				$scope.vm.model.acrq = 'allocate-field-to-form';
				$scope.vm.model.fid = $scope.form_id;
				$scope.vm.model.field_id = objField.id;
				$scope.vm.model.field_type = $scope.form_field_placeholder.field_type_descriptor;
			
				/**
				 * Do some checks first on the new field
				 */
				//populate
				if (typeof $scope.vm.model.populate != 'undefined' && $scope.vm.model.populate != 1)
				{
					if (confirm('Enabled populate option?') == true)
					{
						$scope.vm.model.populate = 1;
					}//end if
				}//end if
				
				//duplicate checks
				if (typeof $scope.vm.model.field_type != 'undefined' && $scope.vm.model.field_type == 'standard')
				{
					switch ($scope.vm.model.field_id)
					{
						case 3: //email address
							if ($scope.vm.model.field_duplicate == 0)
							{
								if (confirm('Duplicate check is not set on this field, enable it now?') == true)
								{
									$scope.vm.model.field_duplicate = 1;
								}//end if
							}//end if
							break;
					}//end switch
				}//end if
				
				var $promise = FormAdminPageService.post($scope.vm.model, 
						function success(response) {
							logToConsole(response);
							//add field to displayed group already
							var objF = response.objData;
							$scope.objData.objFormFields.push(objF);

						},
						function error(errorResponse) {
							logToConsole(errorResponse);
							doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
						}
					);
					
					// Track the request and show its progress to the user.
					$scope.progress.addPromise($promise);
			}//end if
		}//end if
	}; //end function
	
	/**
	 * Close form field admin form
	 */
	$scope.vm.submitCancel = function () {
		$scope.vm.model = {};
		//emit event to have main controller disable the field
		$rootScope.$emit("closeAdministrateFormFields", {});
	}; //end function
	
	/**
	 * Load fields allocated to the loaded form
	 */
	function loadFormFields() {
		$scope.objData = false;
		$scope.display_wait_image = true;
		angular.element('#manageFormFieldsPanelStateTitle').html('');
		
		var $promise = FormAdminPageService.get({acrq: 'load-form-allocated-fields', fid: $scope.form_id}, 
				function success(response) {
					logToConsole(response);
					
					//extract fields into array
					var arr_fields = new Array();
					angular.forEach(response.objData.objFormFields, function (objF, i) {
						arr_fields.push(objF);
					});

					response.objData.objFormFields = arr_fields;
					
					//filter fields based on form type
					switch (response.objData.objForm.form_types_behaviour)
					{
						case '__viral':
							var objStandardFields = Array();
							angular.forEach(response.objData.objStandardFields, function (objField, i) {
								if (objField.viral_field == 1 && typeof objField.id != 'undefined')
								{
									objStandardFields.push(objField);
								}//end if
							});//end foreach
							response.objData.objStandardFields = objStandardFields;
							
							var objCustomFields = Array();
							angular.forEach(response.objData.objCustomFields, function (objField, i) {
								if (objField.fields_types_input_type == 'text')
								{
									objCustomFields.push(objField);
								}//end if
							});
							response.objData.objCustomFields = objCustomFields;
							break;
							
						case '__sales_funnel':
						case '__tracker':
							response.objData.objStandardFields = Array();
							break;
					}//end switch

					$scope.objData = response.objData;
					$scope.objData.selected_std_field = {};
					//create callback to deal with selected item
					$scope.selectStdFieldOnSelect = function (item) {
						angular.forEach($scope.objData.objStandardFields, function (objField, i) {
							//call add field callback
							if (objField.id == item.id)
							{
								$scope.allocateStandardField(objField);
								return;
							}//end if
						});
					}; //end function					
					
					$scope.objData.arr_std_fields = Array();
					angular.forEach($scope.objData.objStandardFields, function (objField, i) {
						if (typeof objField.id != 'undefined' && typeof objField.description != 'undefined')
						{
							objField.display_name = objField.description + ' (' + objField.field + ' [' + objField.fields_types_field_type + '])';
							$scope.objData.arr_std_fields.push(objField);	
						}//end if
					});
					
					$scope.objData.selected_custom_field = {};
					//create callback to deal with selected item
					$scope.selectCustomFieldOnSelect = function (item) {
						angular.forEach($scope.objData.objCustomFields, function (objField, i) {
							//call add field callback
							if (objField.id == item.id)
							{
								$scope.allocateCustomField(objField);
								return;
							}//end if
						});
					}; //end function		
					
					$scope.objData.arr_custom_fields = Array();
					angular.forEach($scope.objData.objCustomFields, function (objField, i) {
						if (typeof objField.id != 'undefined' && typeof objField.description != 'undefined')
						{
							objField.display_name = objField.description + ' (' + objField.field + ' [' + objField.fields_types_field_type + '])';
							$scope.objData.arr_custom_fields.push(objField);	
						}//end if
					});
					
					//update title for panel
					angular.element('#manageFormFieldsPanelStateTitle').html(' : ' + $scope.objData.objForm.form + ' (' + $scope.objData.objForm.form_types_form_type + ')');
					$scope.display_wait_image = false;
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
			// Track the request and show its progress to the user.
			$scope.progress.addPromise($promise);
	}; //end function
	
	/**
	 * Adapt admin form to loaded form type
	 */
	function formatFieldFormToFormType()
	{
		switch ($scope.objData.objForm.behaviour) 
		{
			case '__web':
			case '__sales_funnel':
			case '__tracker':
			case '__viral':
				angular.forEach($scope.vm.fields, function (objField, i) {
					if (typeof $scope.vm.fields[i].expressionProperties == "undefined")
					{
						$scope.vm.fields[i].expressionProperties = {};
					}//end if
					
					switch (objField.key)
					{
						case 'display_on_index':
							$scope.vm.fields[i].hideExpression = function () {return true;};
							break;
					}//end switch
				});
				break;
		}//end switch
	}//end function
	
	/**
	 * Allocate a standard field to the loaded form
	 */
	$scope.allocateStandardField = function (objField) {
		$scope.form_field_placeholder = objField;
		$scope.form_field_placeholder.field_type_descriptor = 'standard';
		$scope.display_form_field_admin_section = false;
		
		//check if form is already loaded, if not, request it.
		if ($scope.vm.fields.length == 0)
		{
			$scope.loadFormFieldsAdminForm($scope.form_field_placeholder);
		} else {
			//update model with new values
			$scope.vm.model = {
					'active': 1,
					'field_duplicate': '0',
					'description': $scope.form_field_placeholder.description,
					'placeholder': $scope.form_field_placeholder.description,
					'css_class': '',
					'css_style_text': '',
					'css_style2': '',
					'mandatory': 0,
					'readonly': 0,
					'default_content': '',
					'populate': 0,
					'hidden': 0,
					'hidden_not_logged_in': 0,
					'display_on_index': 0,
					'field_order': ''
			};
			
			formatFieldFormToFormType();
			$scope.display_form_field_admin_section = true;
			$scope.display_wait_image = false;
		}//end if
	}; //end function
	
	/**
	 * Allocate a custom field to the loaded form
	 */
	$scope.allocateCustomField = function(objField) {
		$scope.form_field_placeholder = objField;
		$scope.form_field_placeholder.field_type_descriptor = 'custom';
		$scope.display_form_field_admin_section = false;
		
		//check if form is already loaded, if not, request it.
		if ($scope.vm.fields.length == 0)
		{
			$scope.loadFormFieldsAdminForm($scope.form_field_placeholder);
		} else {
			//update model with new values
			$scope.vm.model = {
					'active': 1,
					'field_duplicate': '0',
					'description': $scope.form_field_placeholder.description,
					'placeholder': $scope.form_field_placeholder.description,
					'field_type': objField.field_type_descriptor,
					'css_class': '',
					'css_style_text': '',
					'css_style2': '',
					'mandatory': 0,
					'readonly': 0,
					'default_content': '',
					'populate': 0,
					'hidden': 0,
					'hidden_not_logged_in': 0,
					'display_on_index': 0,
					'field_order': ''
			};
			
			formatFieldFormToFormType();
			$scope.display_form_field_admin_section = true;
			$scope.display_wait_image = false;
		}//end if	
	}; //end function
	
	/**
	 * Load admin section for managing a field already allocated to the loaded form
	 */
	$scope.updateFormField = function (objFormField) {
		$scope.form_field_placeholder = objFormField;
		$scope.vm.fields = [];
		$scope.vm.model = {};

		FormAdminPageService.get({acrq: 'load-form-field-admin-form', fid: $scope.form_id}, 
				function success(response) {
					logToConsole(response);
					
					//clear any existing fields
					$scope.vm.fields = [];
					$scope.vm.model = {};
					angular.forEach(response.objData, function (objField, id) {
						objField.modelOptions.getterSetter = false;				
						
						//remove form type select
						switch (objField.key)
						{
							default:
								$scope.vm.fields.push(objField);
								break;
						}//end switch
					});
					
					$scope.vm.model = objFormField;
					$scope.vm.model.existing_field = true;
					$scope.display_form_field_admin_section = true;
					formatFieldFormToFormType();
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	};//end function
	
	/**
	 * Remove a field allocated to the loaded form
	 */
	$scope.removeFormField = function(objField) {
		if (confirm('Are you sure you want to remove this field?') == true)
		{
			//remove field from list
			angular.forEach($scope.objData.objFormFields, function(field, i) {
				if (field.id == objField.id)
				{
					//delete $scope.objData.objFormFields.i;
				}//end if
			})//end loop
			
			//remove the field
			var objData = {
				acrq: 'remove-form-field',
				form_id: $scope.form_id,
				field_id: objField.id
			};
			
			if (objField.fields_std_id > 0)
			{
				objData.field_type = 'standard';
				objData.field_id = objField.fields_std_id;
			} else {
				objData.field_type = 'custom';
				objData.field_id = objField.fields_custom_id;
			}//end if
			
			var $promise = FormAdminPageService.post(objData, 
				function success(response) {
					logToConsole(response);
					
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Remove Form Field', '<p>A problem occurred attempting to remove the field. Response: ' + response.response + '</p>');
						return false;
					}//end if
					
					//remove field from view
			        var index = $scope.objData.objFormFields.indexOf(objField);
			        if (index !== -1) {
			        	$scope.objData.objFormFields.splice(index, 1);
			        }//end if
				}, //end function
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				} //end function
			);
		}//end if
	}; //end function
	
	/**
	 * Activate or deactivate a field allocated to the loaded form
	 */
	$scope.changeFormFieldStatus = function (objField) {
		
		//update field status
		objField.active = parseInt(1 - objField.active);
		objField.acrq = 'update-form-field-status';
		objField.fid = $scope.form_id;
		
		if (objField.fields_custom_id > 0)
		{
			objField.field_type = 'custom';
			objField.field_id = objField.fields_custom_id;
		} else {
			objField.field_type = 'standard';
			objField.field_id = objField.fields_std_id;
		}//end if
		
		FormAdminPageService.post(objField, 
				function success(response) {
					logToConsole(response);

					//update entry in the table...
					angular.element('#form_field_status_indicator_' + objField.id).toggleClass('text-success glyphicon glyphicon-ok text-danger glyphicon glyphicon-remove');
				}, //end function
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				} //end function
			);
	};
	
	/**
	 * Load admin form to allocate fields to form
	 */
	$scope.loadFormFieldsAdminForm = function (objField) {
		$scope.display_wait_image = true;
		FormAdminPageService.get({acrq: 'load-form-field-admin-form', fid: $scope.form_id}, 
				function success(response) {
					logToConsole(response);
					
					//clear any existing fields
					$scope.vm.fields = [];
					$scope.vm.model = {
							'active': 1,
							'field_duplicate': '0',
							'description': objField.description,
							'placeholder': objField.description,
							'field_type': objField.field_type_descriptor,
							'css_class': '',
							'css_style_text': '',
							'css_style2': '',
							'mandatory': 0,
							'readonly': 0,
							'default_content': '',
							'populate': 0,
							'hidden': 0,
							'hidden_not_logged_in': 0,
							'display_on_index': 0,
							'field_order': ''
					};
					angular.forEach(response.objData, function (objField, id) {
						objField.modelOptions.getterSetter = false;				
						
						//remove form type select
						switch (objField.key)
						{
							default:
								$scope.vm.fields.push(objField);
								break;
						}//end switch
					});
					
					formatFieldFormToFormType();
					$scope.display_form_field_admin_section = true;
					$scope.display_wait_image = false;
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					$scope.display_wait_image = false;
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	}; //end fuction
	
	
	/**
	 * Field Behaviours
	 */
	//list behaviour set for field
	$scope.manageFormFieldBehaviours = function (objField) {return loadFieldBehaviours(objField);};
	function loadFieldBehaviours(objField) {
		$scope.objCurrentFormField = objField; //keep track of the current field being worked on
		$scope.display_wait_image_field_behaviours = true;
		$scope.display_form_field_behaviour_admin_section = true;

		if (objField.fields_custom_id > 0)
		{
			var field_id = objField.fields_custom_id;
		} else {
			var field_id = objField.fields_std_id;
		}//end if
		
		//reset data
		$scope.objFormFieldBehaviours = new Array();
		
		//load form field set behaviours
		FormAdminPageService.get({acrq: 'load-form-field-behaviours', 
									'form_id': objField.fk_form_id, 
									'fields_all_id': objField.id, 
									'behaviour': 'form_fields',
									'field_id': field_id
								}, 
				function success(response) {
					logToConsole(response);

					//update field behaviours container
					angular.forEach(response.objData.objBehaviours, function (objBehaviour, i) {
						if (typeof objBehaviour.id != "undefined")
						{
							$scope.objFormFieldBehaviours.push(objBehaviour);
						}//end if
					});
					
					//hide wait image
					$scope.display_wait_image_field_behaviours = false;
				}, //end function
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				} //end function
			);
	}; //end function
	
	//load actions available to field
	$scope.createFormFieldBehaviourActionSelect = function () {
		//request form based on field
		var objField = $scope.objCurrentFormField;
		if (objField.fields_custom_id > 0)
		{
			var field_id = objField.fields_custom_id;
		} else {
			var field_id = objField.fields_std_id;
		}//end if
		
		$scope.display_wait_image_field_behaviours_form_loading = true;
		FormAdminPageService.get({acrq: 'create-form-field-behaviour-admin-form-actions', 
									'form_id': objField.fk_form_id, 
									'fields_all_id': objField.id, 
									'behaviour': 'form_fields',
									'field_id': field_id
								}, 
				function success(response) {
					logToConsole(response);

					//load form
					$scope.objFormFieldBehaviourAvailableActions = [];
					angular.forEach(response.objData.arr_behaviour_actions, function (label, action) {
						var objOption = {
							'value': action,
							'label': label
						};
						
						$scope.objFormFieldBehaviourAvailableActions.push(objOption);
					});
					
					//display the form
					$scope.display_form_field_config_form = true;
					$scope.display_wait_image_field_behaviours_form_loading = false;
				}, //end function
				function error(errorResponse) {
					logToConsole(errorResponse);
					
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					$scope.display_form_field_config_form = false;
					$scope.display_wait_image_field_behaviours_form_loading = false;
				} //end function
			);		
	};
	
	//load form to configure action for field
	$scope.createFormFieldBehaviour = function (objAction) {return createFormFieldBehaviour(objAction);}; 
	function createFormFieldBehaviour(objAction) {
		//request form based on field
		$scope.objCurrentFormField.beh_action = objAction.value;
		var objField = $scope.objCurrentFormField;
		if (objField.fields_custom_id > 0)
		{
			var field_id = objField.fields_custom_id;
		} else {
			var field_id = objField.fields_std_id;
		}//end if

		$scope.display_wait_image_field_behaviours_form_loading = true;
		//load form
		$scope.beh.fields = new Array();
		$scope.beh.model = {};
		if (typeof objAction.model != "undefined")
		{
			$scope.beh.model = objAction.model;
		}//end if
		
		var $promise = FormAdminPageService.get({'acrq': 'create-form-field-behaviour-admin-form', 
									'form_id': objField.fk_form_id, 
									'fields_all_id': objField.id, 
									'behaviour': 'form_fields',
									'field_id': field_id,
									'beh_action': objAction.value
								}, 
				function success(response) {
					logToConsole(response);
					var arr_behaviour_form_fields = orderFormBehaviourFields({'behaviour': 'form_fields', 'behaviour_action': objAction.value}, response.objData.objForm);
					angular.forEach(arr_behaviour_form_fields, function (objElement, i) {
						if (typeof objElement.key != "undefined")
						{
							$scope.beh.fields.push(objElement);
						}//end if
					});
					
					//display the form
					$scope.display_form_field_config_form = true;
					$scope.display_wait_image_field_behaviours_form_loading = false;
				}, //end function
				function error(errorResponse) {
					logToConsole(errorResponse);
					
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					$scope.display_form_field_config_form = false;
					$scope.display_wait_image_field_behaviours_form_loading = false;
				} //end function
			);
		
		return $promise;
	};
	
	//update field behaviour
	$scope.updateFormFieldBehaviour = function (objBehaviour) {
		//convert string values to integers
		angular.forEach(objBehaviour, function (value, key) {
			if (isNumeric(value) == true)
			{
				objBehaviour[key] = parseInt(value);
			}//end if
		});
		
		objBehaviour.behaviour_id = objBehaviour.id;
		var objAction = {
				'value': objBehaviour.action,
				'model': objBehaviour
		};
		
		$scope.objFormFieldBehaviourAvailableActions.push({value: '', label: 'Not Applicable'});
		
		//load form
		createFormFieldBehaviour(objAction);	
	}; //end function
	
	/**
	 * Submit data to create or update behaviour
	 */
	$scope.beh.processFormSubmit = function () {
		var data = $scope.beh.model;

		if (typeof data.behaviour_id == "undefined")
		{
			data.behaviour_id = false;
		}//end if

		data.acrq = 'process-form-field-behaviour-data';
		data.form_id = $scope.objCurrentFormField.fk_form_id;
		data.field_id = $scope.objCurrentFormField.id;
		data.fields_all_id = $scope.objCurrentFormField.id;
		data.beh_action = $scope.objCurrentFormField.beh_action;
		
		//disable form submit and display wait image
		$scope.display_wait_image_field_behaviours_form_loading = true;

		//send request
		FormAdminPageService.post(data, 
				function success(response) {
					logToConsole(response);

					if (typeof response.objData == "undefined")
					{
						doErrorAlert('Unable to complete request', '<p>' + response.response + '</p>');
						return false;
					}//end if
					
					//clear form and model
					$scope.beh.fields = [];
					$scope.beh.model = {};
					$scope.display_wait_image_field_behaviours_form_loading = false;
					//hide the form
					$scope.display_form_field_config_form = false;
					
					//reload field behaviours table
					loadFieldBehaviours($scope.objCurrentFormField);
				},
				function error(errorResponse) {
					logToConsole(response);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					$scope.display_wait_image_field_behaviours_form_loading = false;
				}
			);	
	};
	
	$scope.beh.submitCancel = function () {
		$scope.display_form_field_config_form = false;
		$scope.beh.fields = [];
		$scope.beh.model = {};
	};	
	
	//close field behaviours section
	$scope.closeManageFormFieldBehaviours = function () {
		$scope.display_wait_image_field_behaviours = false;
		$scope.display_form_field_behaviour_admin_section = false;
		//reset data
		$scope.objFormFieldBehaviours = new Array();
	}; //end function
	
	//toggle status for report
	$scope.changeFormFieldBehaviourStatus = function (objBehaviour) {
		///
		jQuery.ajax({
			'url': '/front/behaviours/config/ajax-request/' + objBehaviour.id + '?acrq=update-behaviour-status&behaviour_id=' + objBehaviour.id + '&fields_all_id=' + objBehaviour.fk_fields_all_id + '&behaviour=form_fields'
		})
		.done(function (data) {
			angular.element('#form_behaviour_status_indicator_' + objBehaviour.id).toggleClass('text-success glyphicon glyphicon-ok text-danger glyphicon glyphicon-remove');
		})
		.fail(function () {
			doErrorAlert('Unable to complete request', '<p>Behaviour status could not be updated. An unknown error has occurred.</p>');
		});
	}; //end function
	
	//delete field behaviout
	$scope.removeFormFieldBehaviour = function (objBehaviour) {
		if (confirm('Are you sure you want to remove this behaviour?'))
		{
			FormAdminPageService.get({acrq: 'delete-form-field-behaviour', 'behaviour_id': objBehaviour.id}, 
					function success(response) {
						logToConsole(response);

						//remove behaviour from list
				        var index = $scope.objFormFieldBehaviours.indexOf(objBehaviour);
				        if (index !== -1) {
				        	$scope.objFormFieldBehaviours.splice(index, 1);
				        }//end if
					},
					function error(errorResponse) {
						logToConsole(response);
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					}
				);			
		}//end if
	}; //end function
	
	
	/**
	 * Form field ordering
	 */
    $scope.fieldOrderModel = {
    		active: false,
    		sending_data: false,
    		submitData: function () {
    			var objRequest = {
    					'acrq': 'update-form-field-order',
    					'data': $scope.fieldOrderModel.list,
    					'fid': $scope.form_id
    			};
    			
    			FormAdminPageService.post(objRequest, 
    					function success(response) {
    						logToConsole(response);

    						$scope.fieldOrderModel.sending_data = false;
    						$scope.fieldOrderModel.active = false;
    						//reset list
    						$scope.fieldOrderModel.list = Array();
    						
    						//trigger field reload to display ne order
    						loadFormFields();
    					},
    					function error(errorResponse) {
    						logToConsole(errorResponse);
    			
    						$scope.fieldOrderModel.sending_data = false;
    						$scope.fieldOrderModel.active = false;
    						//reset list
    						$scope.fieldOrderModel.list = Array();
    					}
    				);	
    		},
    		removeField: function (event, index, objField) {
    			//remove item from list
    			$scope.fieldOrderModel.list.splice(index, 1);
    		},
            selected: null,
            list: []
        };
    
    $scope.loadFormFieldOrderData = function (objFormFields) {
    	return loadFormFieldOrderData(objFormFields);
    };
    function loadFormFieldOrderData(objFormFields)
    {
    	//add fields to model
    	$scope.fieldOrderModel.list = Array();
    	angular.forEach(objFormFields, function (objField, i) {
    		if (typeof objField.id != 'undefined')
    		{
    			$scope.fieldOrderModel.list.push({
    				id: objField.id,
    				label: objField.fields_description + ' (' + objField.fields_field + ')'
    			});
    		}//end if
    	});
    	
    	$scope.fieldOrderModel.active = true;
    };//end function	
}]);
