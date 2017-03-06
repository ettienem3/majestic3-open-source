'use strict';

var formsAdminControllers = angular.module('formsAdminControllers', []);

formsAdminControllers.controller('HomeCtrl', ['$scope', '$rootScope', '$route', '$routeParams', '$window', 'FormAdminPageService', 'promiseTracker', '$ngConfirm', function HomeCtrl($scope, $rootScope, $route, $routeParams, $window, FormAdminPageService, promiseTracker, $ngConfirm, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.global_profile_config = global_profile_config;
	$scope.objPageConfig = global_page_config;
	$scope.objRecords = [];
	$scope.objFormStatistics = false;
	$scope.objFormStatsChart = false;
	$scope.objAdvancedFormConfig = {};
	$scope.form_stats_show_raw_data = false;
	
	$scope.submitToggleFormStatus = false;
	$scope.createFormPanelState = false;
	$scope.editFormPanelState = false;
	$scope.deleteFormPanelState = false;
	$scope.manageFormBehavioursPanelState = false;
	$scope.manageFormSummaryPanelState = false;
	$scope.manage_form_summary_load_in_progress = false;
	$scope.title_create_form_type = false;
	
	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	//container for form summary data
	$scope.form_summary = {}; //is set by event emitted on root scope
	
	//vars dealing with behaviours
	$scope.objFormBehaviours = []; //carries behaviours configured for a form
	$scope.objBehaviourFormData = {}; //carries full dataset for the form being configured
	$scope.objAvailableBehaviourOptions = []; //behaviour options available to form type is loaded here
	$scope.display_behaviour_button = true; //true displays create behaviour button
	$scope.display_behaviour_button_display_selection = false; //section offering different behaviours available to form type
	$scope.id_form_behaviours_active = false; //keep track of which form we are dealing with
	$scope.type_form_behaviours_active = false;
	$scope.beh_config_form_loading = false;
	$scope.beh = this;
	$scope.beh.model = {};		//model containing options set on form
	$scope.beh.fields = [];		//array containing fields to set behaviour
	
	//vars dealing with filtering forms
	//$scope.formFilter = this;
	$scope.formFilter = {
			applied: false,
			model: {},
			fields: setFilterFormFields()
	};
	
	//vars dealing with advanced form config details
	$scope.formAdvancedConfig = {
		model: {},
		fields: [],
		cached_data: {},
		submitForm: function () {
			//create the request
			var objRequest = angular.copy($scope.formAdvancedConfig.model);
			objRequest.acrq = 'update-form-config-data';
			
			//submit data
			var $p = FormAdminPageService.post(objRequest, 
				function success(response) {
					logToConsole(response);
					
					//check repsponse for errors
					if (typeof response.error == 'undefined' || (typeof response.error != 'undefined' && response.error != 1))
					{
						doMessageAlert('Changes saved', '<p>Requested operation completed successfully</p>');
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
						
						doErrorAlert('Unable to complete request', '<p>Data could not be saved. An unknown error has occurred. Please try again.</p>');			
					}//end if
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
			//scroll to indicator
			angular.element("html, body").animate({ scrollTop: 0 }, 500);
			
			//add status tracker
			$scope.formAdvancedConfig.progress.addPromise($p);
		},
		
		//form sections
		loadFormSection: function (key) {
			//load form config
			var objForm = formAdminFormAdvanced($scope.formAdvancedConfig.model.form_types_behaviour);

			$scope.formAdvancedConfig.fields = Array();
			$scope.formAdvancedConfig.currentSection = key;
			angular.forEach(objForm, function (objSection, i) {
				if (objSection.key == key)
				{
					angular.forEach(objSection.fieldGroup, function (objField, ii) {
						$scope.formAdvancedConfig.fields.push(objField);
					});
				}
			});
			
			//perform some actions based on section
			//@TODO, do this properly at some point
			switch (key)
			{					
				case '_appearance_and_content':
					if (typeof $scope.formAdvancedConfig.cached_data.form_templates == 'undefined')
					{
						jQuery.ajax({
							'url': '/front/form/templates/ajax-index',
						})
						.done(function (data) {
							$scope.formAdvancedConfig.cached_data['form_templates'] = data.objData;
							
							//populate the element
							angular.forEach($scope.formAdvancedConfig.fields, function (objField, i) {
								if (objField.key == 'template_id')
								{
									objField.templateOptions.options = Array();
									objField.templateOptions.options.push({
										'optionID': '',
										'optionLabel': '--select--'
									});
									angular.forEach($scope.formAdvancedConfig.cached_data.form_templates, function (objTemplate, ii) {
										objField.templateOptions.options.push({
											'optionID': objTemplate.id,
											'optionLabel': objTemplate.template
										});
									});
								}//end if
							});
						})
						.fail(function () {
							doErrorAlert('Unable to complete request', '<p>Form Look and Feel data could not be loaded. Please try again.</p>');
						});
					} else {
						//populate the element
						angular.forEach($scope.formAdvancedConfig.fields, function (objField, i) {
							if (objField.key == 'template_id')
							{
								objField.templateOptions.options = Array();
								objField.templateOptions.options.push({
									'optionID': '',
									'optionLabel': '--select--'
								});
								angular.forEach($scope.formAdvancedConfig.cached_data.form_templates, function (objTemplate, ii) {
									objField.templateOptions.options.push({
										'optionID': objTemplate.id,
										'optionLabel': objTemplate.template
									});
								});
							}//end if
						});
					}//end if
					break;
					
				case '_default_values':
					//default user id
					if (typeof $scope.formAdvancedConfig.cached_data.users == 'undefined')
					{
						jQuery.ajax({
							'url': '/front/users/ajax-load-users'
						})
						.done(function (data) {
							$scope.formAdvancedConfig.cached_data['users'] = data;
							
							//populate the element
							angular.forEach($scope.formAdvancedConfig.fields, function (objField, i) {
								if (objField.key == 'default_user_id')
								{
									objField.templateOptions.options = Array();
									objField.templateOptions.options.push({
										'optionID': '',
										'optionLabel': '--select--'
									});
									angular.forEach($scope.formAdvancedConfig.cached_data.users, function (objUser, ii) {
										objField.templateOptions.options.push({
											'optionID': objUser.id,
											'optionLabel': objUser.uname
										});
									});
								}//end if
							});
						})
						.fail(function () {
							doErrorAlert('Users data could not be loaded', '<p>An unknown error has occurred. Please try again.</p>');
						});
					} else {
						//populate the element
						angular.forEach($scope.formAdvancedConfig.fields, function (objField, i) {
							if (objField.key == 'default_user_id')
							{
								objField.templateOptions.options = Array();
								objField.templateOptions.options.push({
									'optionID': '',
									'optionLabel': '--select--'
								});
								angular.forEach($scope.formAdvancedConfig.cached_data.users, function (objUser, ii) {
									objField.templateOptions.options.push({
										'optionID': objUser.id,
										'optionLabel': objUser.uname
									});
								});
							}//end if
						});
					}//end if	
					
					//default contact status
					if (typeof $scope.formAdvancedConfig.cached_data.statuses == 'undefined')
					{
						jQuery.ajax({
							'url': '/front/statuses/ajax-index',
						})
						.done(function (data) {
							$scope.formAdvancedConfig.cached_data['statuses'] = data.objData;
							
							//populate the element
							angular.forEach($scope.formAdvancedConfig.fields, function (objField, i) {
								if (objField.key == 'default_reg_status_id')
								{
									objField.templateOptions.options = Array();
									objField.templateOptions.options.push({
										'optionID': '',
										'optionLabel': '--select--'
									});
									angular.forEach($scope.formAdvancedConfig.cached_data.statuses, function (objStatus, ii) {
										objField.templateOptions.options.push({
											'optionID': objStatus.id,
											'optionLabel': objStatus.status
										});
									});
								}//end if
							});
						})
						.fail(function () {
							doErrorAlert('Status data could not be loaded', '<p>An unknown error has occurred. Please try again.</p>');
						});
					} else {
						//populate the element
						angular.forEach($scope.formAdvancedConfig.fields, function (objField, i) {
							if (objField.key == 'default_reg_status_id')
							{
								objField.templateOptions.options = Array();
								objField.templateOptions.options.push({
									'optionID': '',
									'optionLabel': '--select--'
								});
								angular.forEach($scope.formAdvancedConfig.cached_data.statuses, function (objStatus, ii) {
									objField.templateOptions.options.push({
										'optionID': objStatus.id,
										'optionLabel': objStatus.status
									});
								});
							}//end if
						});
					}//end if
					break;
			}//end switch
			
			if ($scope.formAdvancedConfig.fields.length == 0)
			{
				doErrorAlert('Unable to complete request', '<p>There are no available settings for the selected section.</p>');
				return false;
			}//end if
		},
		
		//progress tracker
		progress: promiseTracker(),
		currentSection: false
	};
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	
	//pagination
	$scope.previousPage = 1;
	$scope.currentPage = 1;
	$scope.pageSize = 20;
	
	//listen for form submit success set data event
	$rootScope.$on('refreshIndexData', function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.previousPage = 0;
		loadPaginator($scope.currentPage);
	});
	
	//handle clicks on paginator
	$scope.pageChangeHandler = function(page) { return loadPaginator(page);};
	function loadPaginator(page) {
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
		
		var objRequest = {acrq: 'index', 'qp_limit': $scope.objPageConfig.pagination.qp_limit, 'qp_start': start_number};
		angular.forEach($scope.formFilter.model, function (value, key) {
			
			switch (key)
			{				
				default:
					objRequest[key] = value;
					break;
			}//end switch
		});
		
		FormAdminPageService.get(objRequest, 
				function success(response) {
					if (typeof response.objData == 'undefined')
					{
						$scope.pageContent = '';
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred and required data could not be loaded. Please try again.</p>');
						return false;
					}//end if
					
					angular.forEach(response.objData, function (obj, i) {
						if (i > -1)
						{
							if (obj.form_types_behaviour == '__sales_funnel')
							{
								obj.form_types_form_type = 'Tracker';
							}//end if
							
							if (obj.form_types_behaviour == '__viral')
							{
								obj.form_types_form_type = 'Referral';
							}//end if
							
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
					logToConsole(response);
					$scope.pageContent = '';
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	};
	
	$rootScope.$on('closeEditFormPanelState', function () {
		$scope['createWebFormPanelState'] = false;
		$scope['createCPPFormPanelState'] = false;
		$scope['createTrackerFormPanelState'] = false;
		$scope['createViralFormPanelState'] = false;
		toggleFormPanelState('editFormPanelState');
		doRemoveSlidePanel({});
	});
	
	$scope.toggleFormPanel = function (panel, id, form_type) {return toggleFormPanelState(panel, id, form_type);};
	function toggleFormPanelState(panel, id, form_type) {		
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope[panel] = !$scope[panel];
		var flag = $scope[panel];
		
		if (flag == true)
		{
			switch (panel)
			{
				case 'createWebFormPanelState':
				    $rootScope.$emit("loadWebFormAdminFields", {});
					break;
					
				case 'createCPPFormPanelState':
				    $rootScope.$emit("loadCPPFormAdminFields", {});
					break;
					
				case 'createTrackerFormPanelState':
		            $rootScope.$emit("loadTrackerFormAdminFields", {});
					break;
					
				case 'createViralFormPanelState':
				    $rootScope.$emit("loadViralFormAdminFields", {});
					break;
					
				case 'editFormPanelState':
					switch(form_type)
					{
						case '__web':
							$rootScope.edit_form_id = id;
							$rootScope.$emit("loadWebFormAdminFields", {});
							//reuse panel
							$scope.createWebFormPanelState = flag;
							angular.element('#createWebFormPanelState').find('.panel-title-text').html('Update Web Form');
							break;
							
						case '__sales_funnel':
						case '__tracker':
							 $rootScope.edit_tracker_id = id;
							 $rootScope.$emit("loadTrackerFormAdminFields", {});
							 //reuse panel
							 $scope.createTrackerFormPanelState = flag;
							 angular.element('#createTrackerFormPanelState').find('.panel-title-text').html('Update Tracker Form');
							break;
							
						case '__cpp':
							$rootScope.edit_cpp_id = id;
							$rootScope.$emit("loadCPPFormAdminFields", {});
							//reuse panel
							$scope.createCPPFormPanelState = flag;
							angular.element('#createCPPFormPanelState').find('.panel-title-text').html('Update Contact Profile Form');
							break;
							
						case '__viral':
							$rootScope.edit_viral_id = id;
							$rootScope.$emit("loadViralFormAdminFields", {});
							//reuse panel
							$scope.createViralFormPanelState = flag;
							angular.element('#createViralFormPanelState').find('.panel-title-text').html('Update Referral Form');
							break;
					}//end switch
					break;
					
				case 'advancedConfigFormPanelState':
					$scope.formAdvancedConfig.model = $scope.objAdvancedFormConfig;
					break;
					
				case 'manageFormFieldsPanelState':
					$rootScope.id_admin_form_fields = id;
					$rootScope.$emit("administrateFormFields", {});
					break;
					
				case 'manageFormSummaryPanelState':
					//clear previous summary data
					$scope.form_summary = {};
					$rootScope.form_summary = {};
					$scope.manage_form_summary_load_in_progress = true;
					
					switch (form_type)
					{
						case '__web':
							$rootScope.id_web_form_summary = id;
							$rootScope.$emit("loadWebFormSummary", {});
							break;
							
						case '__viral':
							$rootScope.id_viral_form_summary = id;
							$rootScope.$emit("loadViralFormSummary", {});
							break;
					}//end switch
					break;
					
				case 'deleteFormPanelState':
					if (confirm('Are you sure you want to remove this form?') == true) 
					{
						var objRequest = {
							'acrq': 'delete-form',
							'fid': id,
						};
						
						//submit data
						var $p = FormAdminPageService.get(objRequest, 
							function success(response) {
								logToConsole(response);
								
								if (typeof response.error != 'undefined' && response.error == 1)
								{
									//handle errors
									doErrorAlert('Unable to complete request', '<p>The form could not be removed, failed with message: ' + response.response + '</p>');
								}//end if
								
								//close the panel
								$scope.toggleFormPanel(panel);
								
								//trigger reload
								$scope.loadRecords();
							},
							function error(errorResponse) {
								logToConsole(errorResponse);
								doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
							}
						);
						
						//add tracker
						$scope.progress.addPromise($p);
					}//end if
					

					break;
					
				case 'manageFormBehavioursPanelState':
					//load form behaviours
					$scope.id_form_behaviours_active = id;
					$scope.loadFormBehaviours(id);
					break;
					
				case 'filterFormsPanelState':
					//filter forms
					setFilterFormFields();

					break;
			}//end switch
			
			doCreateSlidePanel({});
		} else {
			doRemoveSlidePanel({});
			$scope.title_create_form_type = false;
			$scope.createWebFormPanelState = false;
			$scope.createTrackerFormPanelState = false;
			$scope.createCPPFormPanelState = false;
			$scope.editFormPanelState = false;
			
			$scope.manageFormFieldsPanelState = false;
			$scope.manageFormSummaryPanelState = false;
			$scope.form_summary = {};
			$scope.deleteFormPanelState = false;
			$scope.manageFormBehavioursPanelState = false;
			$scope.form_behaviour_config_settings_form = false;
			$scope.beh_config_form_loading = false;
			$scope.objAvailableBehaviourOptions = {};
			$scope.objFormStatistics = false;
			$scope.objFormStatsChart = false;
			//remove current generated chart
			angular.element('#formStatsChart').html('');
			
			//reset panel headers
			angular.element('#createWebFormPanelState').find('.panel-title-text').html('Create Web Form');
			angular.element('#createCPPFormPanelState').find('.panel-title-text').html('Create Contact Profile Form');
			angular.element('#createTrackerFormPanelState').find('.panel-title-text').html('Create Tracker Form');
			angular.element('#createViralFormPanelState').find('.panel-title-text').html('Create Referral Form');
			
			//clear loaded form behaviours
			if ($scope.objFormBehaviours.length > 0)
			{
				$scope.objFormBehaviours = [];
			}//end if
			
			if (panel == 'advancedConfigFormPanelState')
			{
				$scope.formAdvancedConfig.model = {};
				$scope.formAdvancedConfig.fields = Array();
				$scope.formAdvancedConfig.currentSection = false;
			}//end if
		}//end if
	}; //end function
	
	$scope.loadRecords = function () {return loadRecords();};
	function loadRecords() {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var objRequest = {acrq: 'index', 'qp_limit': 20};
		angular.forEach($scope.formFilter.model, function (value, key) {
			switch (key)
			{
				case 'forms_type_id':
				case 'active':
				case 'forms_form':
					objRequest[key] = value;
					break;
			}//end switch
		});
		
		FormAdminPageService.get(objRequest, 
				function success(response) {
					$scope.objRecords = [];
					angular.forEach(response.objData, function (obj, i) {
						if (i > -1)
						{
							if (obj.form_types_behaviour == '__sales_funnel')
							{
								obj.form_types_form_type = 'Tracker';
							}//end if
							
							if (obj.form_types_behaviour == '__viral')
							{
								obj.form_types_form_type = 'Referral';
							}//end if
							
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
		return $scope.loadRecords();
	};
	
	
	/**
	 * Toggle status for form
	 */
	$scope.submitToggleFormStatus = function(objForm) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		objForm.acrq = 'update-form-status';
		objForm.fid = objForm.id;
		FormAdminPageService.post(objForm, 
				function success(response) {
					logToConsole(response);
					if (typeof response.objData !== "undefined")
					{
						angular.element('#form_status_indicator_' + objForm.id).toggleClass('text-success glyphicon glyphicon-ok text-danger glyphicon glyphicon-remove');	
					} else {
						doErrorAlert('Unable to complete request', '<p>Form status could not be updated.</p>');
					}//end if
				},
				function error(errorResponse) {
					logToConsole(response);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);		
	}; //end function
	
	$scope.setFormAdvancedConfigData = function (objRecord) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if (typeof objRecord.hypermedia != 'undefined')
		{
			delete(objRecord.hypermedia);
		}//end if
		
		$scope.objAdvancedFormConfig = objRecord;
	};
	
	/**
	 * Clear cache for form
	 */
	$scope.submitClearFormCache = function(objForm) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		jQuery.ajax({
			'url': '/forms/clear-form-cache?form_id=' + objForm.id,
		})
		.done(function (data) {
			angular.element('#form_clear_cache_' + objForm.id).toggleClass('text-success');
		})
		.fail(function () {
			angular.element('#form_clear_cache_' + objForm.id).toggleClass('text-danger');
			doErrorAlert('Unable to complete request', '<p>A problem occurred while trying to clear saved data for the set form. Please try again.</p>');
		});	
	}; //end function	
	
	/**
	 * Behaviours Section
	 */
	
	/**
	 * Load form behaviours
	 */
	$scope.loadFormBehaviours = function (id) { return loadFormBehaviours(id);};
	function loadFormBehaviours(id) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var $promise = FormAdminPageService.get({acrq: 'load-form-behaviours', 'fid': id}, 
				function success(response) {
					logToConsole(response);
					$scope.objFormBehaviours = new Array();
					$scope.objBehaviourFormData = response.objData.objFormData;
					$scope.type_form_behaviours_active = response.objData.objFormData.form_types_behaviour;
					
					angular.forEach(response.objData.objBehaviours, function(objB, i) {
						if (typeof objB.id != "undefined")
						{
							$scope.objFormBehaviours.push(objB);	
						}//end if
					});
				},
				function error(errorResponse) {
					logToConsole(response);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
		
		$scope.progress.addPromise($promise);
	}; //end function
	
	/**
	 * Set form behaviour status
	 */
	$scope.submitToggleFormBehaviourStatus = function (objBehaviour) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		///
		jQuery.ajax({
			'url': '/front/behaviours/config/ajax-request/' + objBehaviour.id + '?acrq=update-behaviour-status&behaviour_id=' + objBehaviour.id + '&form_id=' + objBehaviour.fk_form_id + '&behaviour=form'
		})
		.done(function (data) {
			angular.element('#form_behaviour_status_indicator_' + objBehaviour.id).toggleClass('text-success glyphicon glyphicon-ok text-danger glyphicon glyphicon-remove');
		})
		.fail(function () {
			doErrorAlert('Unable to complete request', '<p>Behaviour status could not be updated, an unknown error has occurred.</p>');
		});
	}; //end function
	
	/**
	 * Load behaviour config form
	 */
	$scope.loadBehaviourConfigOptions = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		//hide menu if displayed
		$scope.display_behaviour_button_display_selection = false;
		$scope.form_behaviour_config_settings_form = false;
		var $promise = FormAdminPageService.get({acrq: 'load-form-behaviour-options', 'fid': $scope.id_form_behaviours_active, 'behaviour': 'form'}, 
				function success(response) {
					logToConsole(response);
					
					$scope.objAvailableBehaviourOptions = new Array();
					angular.forEach(response.objData.config_form_behaviour_options, function(option, key) {
							var objE = {'key': key, 'label': option};
							$scope.objAvailableBehaviourOptions.push(objE);
					});
					
					//display menu
					$scope.display_behaviour_button_display_selection = true;
				},
				function error(errorResponse) {
					logToConsole(response);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
		
		$scope.progress.addPromise($promise);
	}; //end function
	
	/**
	 * Add a new behaviour to the form
	 */
	$scope.createFormBehaviourOption = function (behaviour_action) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		//load form panel
		$scope.form_behaviour_config_settings_form = true;
		
		//display wait image
		$scope.beh_config_form_loading = true;
		
		//load form
		var $promise = FormAdminPageService.get({acrq: 'load-form-behaviour-config-form', 'fid': $scope.id_form_behaviours_active, 'behaviour': 'form', 'behaviour_action': behaviour_action}, 
				function success(response) {
				logToConsole(response);
				
				$scope.beh.fields = [];
				var arr_config_form_fields = orderFormBehaviourFields({'behaviour': 'form', 'behaviour_action': behaviour_action}, response.objData.objConfigForm);
				angular.forEach(arr_config_form_fields, function (objElement, i) {
					if (typeof objElement.key != "undefined")
					{
						switch (objElement.key)
						{
							default:
								$scope.beh.fields.push(objElement);
								break;
						}//end switch
					}//end if
				});
				
				//add more data to the model
				$scope.beh.model.beh_action = behaviour_action;
				$scope.beh.model.form_id = $scope.id_form_behaviours_active;
				$scope.beh.model.behaviour = 'form';
				
				//hide wait image
				$scope.beh_config_form_loading = false;
			},
			function error(errorResponse) {
				logToConsole(response);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
	}; // end function
	
	/**
	 * Process behaviour config submit
	 */
	$scope.beh.submitForm = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var $objForm = $scope.objBehaviourFormData;
		var data = $scope.beh.model;
		data.setup_complete = 1;
		data.acrq = 'process-form-behaviour-config-form';
		
		if (typeof data.behaviour_id == "undefined")
		{
			data.behaviour_id = false;
		}//end if

		//add any missing fields to the model
		angular.forEach($scope.beh.fields, function (objField, i) {
			if (typeof data[objField.key] == 'undefined') {
				if (typeof objField.defaultValue == 'undefined')
				{
					data[objField.key] = '';
				} else {
					data[objField.key] = objField.defaultValue;
				}//end if
			}//end if
		});
		
		//disable the submit button
		$scope.beh_config_form_loading = true;

		//submit the data
		var $promise = FormAdminPageService.post(data, 
			function success(response) {
				logToConsole(response);
				
				//disable activity indicator
				$scope.beh_config_form_loading = false;
				
				//check repsponse for errors
				if (typeof response.error == 'undefined' || (typeof response.error != 'undefined' && response.error != 1))
				{
					//hide the form
					$scope.form_behaviour_config_settings_form = false;
					$scope.beh.model = {};
					$scope.beh.fields = new Array();
					
					//reload table
					loadFormBehaviours(data.form_id);
				} else {
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						if (typeof response.form_messages != 'undefined')
						{
							handleFormlyFormValidationErrors($scope.beh.fields, $scope.beh.model, response.form_messages);	
							alert('The behaviour could not be saved, please check that all required information has been provided and try again.');
							return false;
						}//end if
					}//end if
					
					if (typeof response.objData != 'undefined' && typeof response.objData.errors != 'undefined')
					{
						handleFormlyFormValidationErrors($scope.beh.fields, $scope.beh.model, response.objData.errors);	
						return false;
					}//end if
					
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');				
				}//end if
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
	}; //end function
	
	/**
	 * Close behaviour config panel
	 */
	$scope.closeBehaviourConfigForm = function() {
		$scope.form_behaviour_config_settings_form = false;
		$scope.beh_config_form_loading = false;
		//reset form
		$scope.beh.model = {};
		$scope.beh.fields = new Array();
	}; //end function
	
	/**
	 * Update form Behaviour
	 */
	$scope.updateFormBehaviour = function (objBehaviour) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		//load form panel
		$scope.display_behaviour_button_display_selection = true;
		$scope.form_behaviour_config_settings_form = true;
		
		//display wait image
		$scope.beh_config_form_loading = true;
		
		//load form
		var $promise = FormAdminPageService.get({acrq: 'load-form-behaviour-config-form', 'fid': $scope.id_form_behaviours_active, 'behaviour': 'form', 'behaviour_action': objBehaviour.action}, 
				function success(response) {
				logToConsole(response);
				
				$scope.beh.fields = [];
				$scope.beh.model = {};
				var arr_config_form_fields = orderFormBehaviourFields({'behaviour': 'form', 'behaviour_action': objBehaviour.action}, response.objData.objConfigForm);
				angular.forEach(arr_config_form_fields, function (objElement, i) {
					if (typeof objElement.key != "undefined")
					{
						$scope.beh.fields.push(objElement);
						$scope.beh.model[objElement.key] = objBehaviour[objElement.key];
					}//end if
				});
				
				//add more data to the model required to create or update settings
				$scope.beh.model.form_id = objBehaviour.fk_form_id;
				$scope.beh.model.behaviour = 'form';
				$scope.beh.model.behaviour_id = objBehaviour.id;
				$scope.beh.model.beh_action = objBehaviour.action;
				
				//add the rest of the behaviour fields
				angular.forEach(objBehaviour, function (value, field) {					
					$scope.beh.model[field] = value;
				});
			
				//hide wait image
				$scope.beh_config_form_loading = false;
			},
			function error(errorResponse) {
				logToConsole(response);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
	};
	
	/**
	 * Delete form behaviour
	 */
	$scope.deleteFormBehaviour = function (objBehaviour) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if (confirm('Are you sure you want to remove this behaviour?'))
		{
			FormAdminPageService.get({acrq: 'delete-form-configured-behaviour', 'behaviour_id': objBehaviour.id}, 
					function success(response) {
						logToConsole(response);

						//remove behaviour from list
				        var index = $scope.objFormBehaviours.indexOf(objBehaviour);
				        if (index !== -1) {
				        	$scope.objFormBehaviours.splice(index, 1);
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
	 * Form statistics
	 */
	$scope.loadFormStatistics = function (objRecord) {return loadFormStatistics(objRecord);};
	function loadFormStatistics(objRecord) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope['formStatisticsPanelState'] = true;
		$scope.objFormStatsChart = false;
		doCreateSlidePanel({});
		
		var objRequest = {
				acrq: 'load-form-statistics',
				fid: objRecord.id
		};
		
		var $promise = FormAdminPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);

					$scope.objFormStatistics = response.objData;
					
					setTimeout(function () {
						//load chart
						var chart = new Highcharts.Chart({
		  			        data: {
		  			            table: 'formStatsChartData'
		  			        },
		  			        chart: {
		  			        	renderTo: 'formStatsChart',
		  			            type: 'bar'
		  			        },
		  			        title: {
		  			            text: 'Data for ' + response.objData.objForm.form
		  			        },
		  			        yAxis: [
			  			                //primary access
			  			                {
					  			            allowDecimals: false,
					  			            title: {
					  			                text: 'Forms Completed'
					  			            }
					  			        }
		  			                ],
		  			        tooltip: {
		  			            formatter: function () {
		  			                return this.point.y + '</b> forms completed during ' + this.point.name.toLowerCase();
		  			            }
		  			        },
		  			        credits: {
		  			        	enabled: false,
		  			        },
						  plotOptions: {
						    series: {
						        pointPadding: 0.2,
						        borderWidth: 0,
						        dataLabels: {
						            enabled: true
						        }
						    },
						    pie: {
						        plotBorderWidth: 0,
						        allowPointSelect: true,
						        cursor: 'pointer',
						        size: '100%',
						        dataLabels: {
						        	enabled: true,
						        	format: '{point.name}: <b>{point.y}</b>'
						        }
						    }
						  }
						});
						$scope.objFormStatsChart = chart;
					}, 2000);
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);	
		
		$scope.progress.addPromise($promise);
	}; //end function
	
	$scope.toggleFormStatisticsChart = function (options) { return toggleFormStatisticsChart(options);};
	function toggleFormStatisticsChart(options) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if ($scope.objFormStatsChart == false)
		{
			return false;
		}//end if

		if (typeof options.chart_type != "undefined")
		{
			var chart_options = $scope.objFormStatsChart.options;
			chart_options.chart.type = options.chart_type;
			chart_options.chart.renderTo = 'formStatsChart'; 
			if (options.chart_type == 'pie')
			{
				chart_options.data.switchRowsAndColumns = true;
				chart_options.data.startRow = 1;
				chart_options.data.endRow = 1;
			} else {
				chart_options.data.switchRowsAndColumns = false;
				chart_options.data.startRow = false;
				chart_options.data.endRow = false;
			}//end if
			
			var chart = new Highcharts.Chart(chart_options);
			$scope.objFormStatsChart = false;
			$scope.objFormStatsChart = chart;
			return false;
		}//end if
		
		if (typeof options.form_stats_show_raw_data != "undefined")
		{
			$scope.form_stats_show_raw_data = !$scope.form_stats_show_raw_data;
		}//end if
	};
	
	/**
	 * Filter forms section
	 */
	$scope.formFilter.submitForm = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.filterFormsPanelState = false;
		doRemoveSlidePanel({});
		$scope.pageChangeHandler(0);
		
		if ($scope.formFilter.model.length > 0)
		{
			$scope.formFilter.applied = true;
		} else {
			$scope.formFilter.applied = false;
		}//end if
	};
	
	$scope.formFilter.clearForm = function () {
		$scope.formFilter.model = {};
		$scope.formFilter.applied = false;
		$scope.filterFormsPanelState = false;
		doRemoveSlidePanel({});
		$scope.pageChangeHandler(0);
	};
	
	function setFilterFormFields() {
		var arr_fields = [];
		
		//form types
		var objField = {
				key: 'forms_type_id',
				type: 'radio',
				templateOptions: {
					type: 'radio',
					label: 'Form Type',
					placeholder: 'Set Form Type',
					title: 'Select specific form type to load',
					valueProp: 'optionID',
					labelProp: 'optionLabel',
					options: []
				}
			};
		
		//add form options based on what user is able to access
		if (typeof global_profile_config !== 'undefined' && typeof global_profile_config.plugins_enabled !== 'undefined')
		{
			if (global_profile_config.plugins_enabled.forms_web == 1)
			{
				objField.templateOptions.options.push({'optionID': 1, 'optionLabel': 'Web' });
			}//end if
			
			if (global_profile_config.plugins_enabled.forms_cpp == 1)
			{
				objField.templateOptions.options.push({'optionID': 5, 'optionLabel': 'Contact Profile' });
			}//end if
			
			if (global_profile_config.plugins_enabled.forms_trackers == 1)
			{
				objField.templateOptions.options.push({'optionID': 3, 'optionLabel': 'Tracker' });
			}//end if
			
			if (global_profile_config.plugins_enabled.forms_viral == 1)
			{
				objField.templateOptions.options.push({'optionID': 2, 'optionLabel': 'Referral' });
			}//end if			
		} else {
			var arr_forms = Array(
			          {'optionID': 1, 'optionLabel': 'Web' },
			          {'optionID': 3, 'optionLabel': 'Tracker' },
			          {'optionID': 5, 'optionLabel': 'Contact Profile'},
			          {'optionID': 2, 'optionLabel': 'Referral'}
			);
			
			objField.templateOptions.options = arr_forms;
		}//end if
		
		arr_fields.push(objField);
		
		//form status
		var objField = {
				key: 'forms_active',
				type: 'radio',
				templateOptions: {
					type: 'radio',
					label: 'Form Status',
					title: 'Filter by form status',
					valueProp: 'optionID',
					labelProp: 'optionLabel',
					options: [
						          {'optionID': 1, 'optionLabel': 'Active' },
						          {'optionID': 0, 'optionLabel': 'Inactive' },
					          ]
				}
			};
		arr_fields.push(objField);
		
		//form title
		var objField = {
				key: 'forms_form',
				type: 'input',
				templateOptions: {
					type: 'test',
					label: 'Form Name / ID',
					title: 'Filter by form name or form id',
					placeholder: 'e.g Web Form X or 210',
				}
			};
		arr_fields.push(objField);		
		
		return arr_fields;
	}; //end function
	
	//listen for form summary set data event
	$rootScope.$on('setFormSummaryData', function () {
		$scope.form_summary = $rootScope.form_summary;
		
		//manipualte form type values
		switch ($scope.form_summary.objForm.form_types_form_type)
		{
			case 'Viral':
				$scope.form_summary.objForm.form_types_form_type = 'Referral';
				break;
				
			case 'Sales Funnel':
				$scope.form_summary.objForm.form_types_form_type = 'Tracker';
				break;
		}//end switch
		
		$scope.manage_form_summary_load_in_progress = false;
	});
	
	//listen for form field admin events
	$rootScope.$on('closeAdministrateFormFields', function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		logToConsole('Closing Form Field Admin Section');
		$scope.toggleFormPanel('manageFormFieldsPanelState', false);
		$scope.manageFormFieldsPanelState = false;
	});
}]);
