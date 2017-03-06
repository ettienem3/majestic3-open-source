'use strict';

var contactControllers = angular.module('contactControllers', []);

/**
 * Contacts Home Page Controller
 * @param $scope
 * @param $route
 * @param $routeParams
 * @param $window
 * @param InboxPageService
 * @param promiseTracker
 * @param formlyVersion
 */
contactControllers.controller('ContactsHomeCtrl', ['$scope', '$route', '$routeParams', '$window', 'ContactsPageService', 'promiseTracker', function ContactsHomeCtrl($scope, $route, $routeParams, $window, ContactsPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.objPageConfig = globalPageConfig();
	$scope.objRecords = [];

	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	//vars dealing with filtering contacts
	$scope.contactFilter = this;
	$scope.contactFilter.fields = [];
	$scope.contactFilter.model = {};
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	  
	//pagination details
	$scope.previousPage = 1;
	$scope.currentPage = 1;
	$scope.pageSize = 20;
	
	//handle clicks on paginator
	$scope.pageChangeHandler = function (page) { return pageChangeHandler(page);};
	function pageChangeHandler(page) {
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
		
		var objRequest = {
				acrq: 'list-contacts', 
				'qp_limit': $scope.objPageConfig.pagination.qp_limit, 
				'qp_start': start_number
		};
		angular.forEach($scope.contactFilter.model, function (value, key) {
			switch (key)
			{
				case 'regtbl_date_created_start':
				case 'regtbl_date_created_end':
					if (value instanceof Date)
					{
						var date = value.toString();
						objRequest[key] = date;
					}//end if
					break;
					
				default:
					objRequest[key] = value;
					break;
			}//end switch
		});
		
		ContactsPageService.get(objRequest, 
				function success(response) {
					angular.forEach(response.objData, function (obj, i) {
						if (i > -1)
						{
							$scope.objRecords.push(obj);
						}//end if
					});
					
					$scope.pageContent = '';
					
					//deal with pagination
					setupPaginationGlobal($scope, response);
				},
				function error(errorResponse) {
					logToConsole(response);
					$scope.pageContent = '';
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	};
	
	//panels
	$scope.togglePanel = function(panel, status) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope[panel] = status;
		
		if (status == true)
		{			
			doCreateSlidePanel(status);
			switch (panel)
			{
				case 'filterContactsPanelState':
					setContactFilterFields();
					break;
			}//end switch
		} else {
			if (panel == 'contactToolkitPanel')
			{
				//reset panel
				angular.element('#contact_toolkit_section').html('<span ng-bind-html="global_wait_image | sanitize"></span>');
			}//end if
			
			doRemoveSlidePanel(status);
		}//end if
	}; //end function
	
	/**
	 * Refresh data
	 */
	$scope.refreshData = function () {
		$scope.pageContent = global_wait_image;
		$scope.objRecords = [];
		$scope.loadRecords();
	}; //end function
	
	/**
	 * Load data list
	 */
	$scope.loadRecords = function () {return loadRecords();};
	function loadRecords () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var objRequest = {
			acrq: 'list-contacts',
			qp_limit: $scope.pageSize
		};
		
		angular.forEach($scope.contactFilter.model, function (value, key) {
			switch (key)
			{
				case 'regtbl_date_created_start':
				case 'regtbl_date_created_end':
					if (value instanceof Date)
					{
						var date = value.toString();
						objRequest[key] = date;
					}//end if
					break;
					
				default:
					objRequest[key] = value;
					break;
			}//end switch
		});
		
		ContactsPageService.get(objRequest, 
			function success(response) {
				logToConsole(response);
				$scope.pageContent = '';
				
				angular.forEach(response.objData, function (objRecord, i) {
					if (objRecord.id > -1)
					{
						$scope.objRecords.push(objRecord);
					}//end foreach
				});

				//deal with pagination
				setupPaginationGlobal($scope, response);
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
	};	
	
	$scope.refreshRecords = function () {
		$scope.objRecords = Array();
		return $scope.loadRecords();
	};
	
	
	$scope.loadContactDetails = function (objContact, section) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.togglePanel('contactToolkitPanel', true);
		
		var iframe_contact = jQuery('<iframe></iframe>');
		iframe_contact.attr('id', 'mj3-toolkit-iframe')
						.attr('seamless', '')
						.attr('src', '/front/contact/toolkit/app/' + objContact.reg_id_encoded + '#!/toolkit/statuses')
						.css('position', 'absolute')
						.css('height', '1100px')
						.css('width', '100%')
						.css('border', 'none');
		
		//set panel title
		switch (section)
		{
			case 'status':
				$scope.toolkit_panel_title = 'Status details for ' + objContact.fname + ' ' + objContact.sname;
				//set frame opening destination
				iframe_contact.attr('src', '/front/contact/toolkit/app/' + objContact.reg_id_encoded + '#!/toolkit/statuses');
				break;
				
			case 'journeys':
				$scope.toolkit_panel_title = 'Journey details for ' + objContact.fname + ' ' + objContact.sname;
				//set frame opening destination
				iframe_contact.attr('src', '/front/contact/toolkit/app/' + objContact.reg_id_encoded + '#!/toolkit/journeys');
				break;
				
			case 'data':
				$scope.toolkit_panel_title = 'Data details for ' + objContact.fname + ' ' + objContact.sname;
				//set frame opening destination
				iframe_contact.attr('src', '/front/contact/toolkit/app/' + objContact.reg_id_encoded + '#!/toolkit/data');
				break;
		}//end switch
		
		angular.element('#contact_toolkit_section').html('<span style="position: absolute; top: 80px; left: 80px; z-index: 0">' + $scope.global_wait_image + '</span>').append(iframe_contact);
	};
	
	$scope.contactFilter.submitForm = function () {
		$scope.togglePanel('filterContactsPanelState', false);
		$scope.pageContent = global_wait_image;
		$scope.objRecords = [];
		loadRecords();
	};
	
	$scope.contactFilter.clearForm = function () {
		if ($scope.contactFilter.model.length == 0)
		{
			$scope.togglePanel('filterContactsPanelState', false);
			return false;
		}//end if
		
		$scope.togglePanel('filterContactsPanelState', false);
		$scope.contactFilter.model = {};
		$scope.pageContent = global_wait_image;
		$scope.objRecords = [];
		loadRecords();
	};
	
	function setContactFilterFields()
	{
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if ($scope.contactFilter.fields.length == 0)
		{
			var objRequest = {
					acrq: 'load-contact-filter-form-fields',
			};
			
			var $promise = ContactsPageService.get(objRequest, 
					function success(response) {
						logToConsole(response);

						angular.forEach(response.objData, function (objElement, i) {
							if (typeof objElement.key != "undefined")
							{
								switch (objElement.key)
								{
									case 'regtbl_date_created_start':
									case 'regtbl_date_created_end':
										//http://angular-formly.com/#/example/integrations/ui-datepicker
										objElement.type = 'datepicker';
										objElement.templateOptions.datepickerPopup = 'dd-MMMM-yyyy';
										objElement.templateOptions.datepickerOptions = {
											format: 'dd-MMMM-yyyy'	
										};
										break;
								}//end switch
								
								$scope.contactFilter.fields.push(objElement);
							}//end if
						});
					},
					function error(errorResponse) {
						logToConsole(errorResponse);
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					}
				);
			
			$scope.progress.addPromise($promise);
		}//end if
	}//end function
}]);

contactControllers.controller('ContactsViewCtrl', ['$scope', '$rootScope', '$route', '$routeParams', '$window', 'ContactsPageService', 'promiseTracker', function ContactsViewCtrl($scope, $rootScope, $route, $routeParams, $window, ContactsPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.messages = false;
	$scope.objPageConfig = globalPageConfig();
	$scope.objContactData = {};
	$scope.pageContactLoadingContent = '';
	$scope.contactModel = {
			'user_id': false,
			'source': false,
			'reference': false,
			'reg_status_id': false,
	};
	
	$scope.contactMetaDataFormReady = false;
	$scope.form_container_message = 'Loading details...';
	$scope.current_loaded_layout_form_id = false;
	$scope.objCPPFormsList = false;

	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	//create form
	$scope.vm = this;
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	
	//panels
	$scope.togglePanel = function(panel, status) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope[panel] = status;
		
		if (status == true)
		{			
			if (panel == 'changeFormLayoutPanel')
			{
				$scope.loadCPPForms();
			}//end if
			
			doCreateSlidePanel(status);
		} else {
			doRemoveSlidePanel(status);
		}//end if
	}; //end function
	
	$scope.loadContact = function () {return loadContact();};
	function loadContact() {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var $promise = ContactsPageService.get({acrq: 'load-contact', cid: $routeParams.id}, 
			function success(response) {
				logToConsole(response);
				$scope.objContactData = response.objData;
				
				//set contact model data
				$scope.contactModel.user_id = $scope.objContactData.user_id;
				$scope.contactModel.source = $scope.objContactData.source;
				$scope.contactModel.reference = $scope.objContactData.reference;
				$scope.contactModel.reg_status_id = $scope.objContactData.reg_status_id;
				
				$scope.pageContent = '';
				$scope.messages = false;
				
				//now load form fields
				
				setTimeout(function() {
					$scope.loadCPPFormFields();
			      }, 1000);
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
	};
	
	$scope.loadCPPForms = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if ($scope.objCPPFormsList != false)
		{
			return $scope.objCPPFormsList;
		}//end if
		
		var $promise = ContactsPageService.get({acrq: 'load-cpp-form-list'}, 
				function success(response) {
					logToConsole(response);

					var objForms = Array();
					angular.forEach(response.objData, function (form, id) {
						objForms.push({id: id, form: form});
					});
					$scope.objCPPFormsList = objForms;
					
					//remove loader from panel
					angular.element('#change_panels_section').find('.wait-image').remove();
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					$scope.pageContactLoadingContent = '';
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
			// Track the request and show its progress to the user.
			$scope.progress.addPromise($promise);
	};
	
	$scope.loadCPPFormFields = function(id) {
		if (typeof id == "undefined")
		{
			id = '';
			
			if ($scope.current_loaded_layout_form_id != false)
			{
				id = $scope.current_loaded_layout_form_id;
			}//end if
		} else {
			//reset the layout form id
			$scope.current_loaded_layout_form_id = id;
		}//end if
		
		//request form
		$scope.pageContactLoadingContent = global_wait_image;
		var $promise = ContactsPageService.get({acrq: 'load-cpp-form', 'cpp_fid': id}, 
			function success(response) {
				logToConsole(response);
				
				//clear any existing fields
				$scope.vm.fields = [];
				$scope.vm.model = {};
				angular.forEach(response.objData, function (objField, id) {
					objField.modelOptions.getterSetter = false;
					objField.templateOptions.disabled = true;
					$scope.vm.fields.push(objField);
					
					//set model data
					$scope.vm.model[objField.key] = $scope.objContactData[objField.key];
				});
				$scope.pageContactLoadingContent = '';
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		// Track the request and show its progress to the user.
		$scope.progress.addPromise($promise);
	}; //end function
	
	$scope.loadContactToolkit = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if (typeof $scope.contact_toolkit_loaded == "undefined")
		{
			$scope.contact_toolkit_loaded = true;
		} else {
			if ($scope.contact_toolkit_loaded == true)
			{
				//unload the section
				$scope.contact_toolkit_loaded = false;
			} else {
				//reload the section
				$scope.contact_toolkit_loaded = true;
			}//end if
		}//end if
		
		if ($scope.contact_toolkit_loaded == true)
		{
			var iframe_contact = jQuery('<iframe></iframe>');
			iframe_contact.attr('id', 'mj3-toolkit-iframe')
							.attr('seamless', '')
							.attr('src', '/front/contact/toolkit/app/' + $scope.objContactData.reg_id_encoded + '#!/toolkit/journeys')
							.css('position', 'absolute')
							.css('height', '1100px')
							.css('width', '770px')
							.css('border', 'none');
			
			//inject into container
			angular.element('.container-contact-toolkit').html(iframe_contact);
			
			//add some classes to make it look good
			angular.element('.container-contact-main').toggleClass('col-xs-6');
			angular.element('.container-contact-toolkit').toggleClass('col-xs-6');
			
			//update button
			angular.element('.contact-toolkit-toggle-button').toggleClass('btn-success btn-danger').html('<span class="glyphicon glyphicon-list-alt"></span>&nbsp; Less Info');
		} else {
			angular.element('.container-contact-toolkit').html('');
			
			//remove classes added when loaded
			angular.element('.container-contact-main').toggleClass('col-md-8');
			angular.element('.container-contact-toolkit').toggleClass('col-md-4');
			
			//update button
			angular.element('.contact-toolkit-toggle-button').toggleClass('btn-success btn-danger').html('<span class="glyphicon glyphicon-list-alt"></span>&nbsp; More Info');
		}//end if
	}; //end function
}]);

/**
 * Create a new contact controller
 * @param $scope
 * @param $route
 * @param $routeParams
 * @param $window
 * @param ContactsPageService
 * @param promiseTracker
 * @param formlyVersion
 */
contactControllers.controller('ContactsCreateCtrl', ['$scope', '$route', '$routeParams', '$window', 'ContactsPageService', 'promiseTracker', function ContactsCreateCtrl($scope, $route, $routeParams, $window, ContactsPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.objPageConfig = {};
	$scope.objContactData = [];
	$scope.cid = false;
	$scope.cpp_form_id = false;
	$scope.objCPPFormsList = false;
	$scope.messages = false;
	
	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	//initialise the view
	$scope.loadCreateContactView = function () {
		//load cpp forms
		$scope.loadCPPForms();
		
		//set page content
		$scope.objPageConfig = global_page_config;
		$scope.pageContent = $scope.objPageConfig.pageTitle;
	};
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	
	$scope.loading_cpp_forms = false;
	$scope.submitting_contact_data = false;
	
	$scope.contactForm = this;
	$scope.contactForm.model = {};
	$scope.contactForm.fields = [];
	
	$scope.contactForm.submitContact = function () {	
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var objData = $scope.contactForm.model;
		
		//add all fields to the model
		angular.forEach($scope.contactForm.fields, function (objField, i) {
			if (typeof objData[objField.key] == 'undefined')
			{
				objData[objField.key] = '';
			}//end if
		});
		
		objData.acrq = 'create-contact';
		objData.cpp_form_id = $scope.cpp_form_id;
		
		//update user messages section
		angular.element('#messages').toggleClass('alert-info');
		$scope.messages = 'Processing Contact Information';
		$scope.contactForm.form.$invalid = true;
		
	    jQuery('html, body').animate({
	        scrollTop: jQuery("#messages").offset().top
	    }, 1000);
	    
		var $promise = ContactsPageService.post(objData, 
			function success(response) {
				logToConsole(response);
			
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					if (typeof response.form_messages != 'undefined')
					{
						handleFormlyFormValidationErrors($scope.contactForm.fields, $scope.contactForm.model, response.form_messages);	
						return false;
					}//end if
				}//end if
				
				if (typeof response.objData != 'undefined' && typeof response.objData.errors != 'undefined')
				{
					handleFormlyFormValidationErrors($scope.contactForm.fields, $scope.contactForm.model, response.objData.errors);	
					return false;
				}//end if
				
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					//show error to user
					angular.element('#messages').toggleClass('alert-info alert-error');
					$scope.messages = 'Details could not be saved, response: ' . response.response;
					$scope.contactForm.form.$invalid = false;
					return false;
				}//end if
				
				angular.element('#messages').toggleClass('alert-info alert-success');
				$scope.messages = 'Contact Details have been saved, you will be redirected shortly from where you can apply additional information to the contact';
			    
				//set 2 second delay
				setTimeout(function () {
					//redirect to edit page view
					var url = "https://" + $window.location.host + "/front/contacts/app#!/view/" + response.objData.objContact.id;
					$window.location.href = url;
				}, 2000);
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				
				angular.element('#messages').toggleClass('alert-info alert-error');
				$scope.messages = 'Processing Failed, details have not been saved';
				$scope.contactForm.form.$invalid = false;
			}
		);
		
		// Track the request and show its progress to the user.
		$scope.progress.addPromise($promise);
	}; //end function	
	
	//panels
	$scope.togglePanel = function(panel, status) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope[panel] = status;
		
		if (status == true)
		{			
			if (panel == 'changeFormLayoutPanel')
			{
				$scope.loadCPPForms();
			}//end if
			
			doCreateSlidePanel(status);
		} else {
			doRemoveSlidePanel(status);
		}//end if
	}; //end function	
	
	$scope.loadCPPForms = function () {
		if ($scope.objCPPFormsList != false)
		{
			return $scope.objCPPFormsList;
		}//end if
		
		var $promise = ContactsPageService.get({acrq: 'load-cpp-form-list'}, 
				function success(response) {
					logToConsole(response);

					var objForms = Array();
					angular.forEach(response.objData, function (form, id) {
						if ($scope.cpp_form_id == false)
						{
							//load first available form by default
							$scope.cpp_form_id = id;
							loadCPPForm({cpp_form_id: $scope.cpp_form_id});
						}//end if
						
						objForms.push({id: id, form: form});
					});
					$scope.objCPPFormsList = objForms;
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					$scope.pageContactLoadingContent = '';
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
			// Track the request and show its progress to the user.
			$scope.progress.addPromise($promise);
	};	
	
	$scope.loadCPPForm = function (options) {return loadCPPForm(options);};
	function loadCPPForm(options) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var objRequest = {
				acrq: 'load-cpp-form'
		};
		
		if (typeof options.cpp_form_id != "undefined")
		{
			objRequest.cpp_fid = options.cpp_form_id;
			$scope.cpp_form_id = options.cpp_form_id;
		}//end if
		
		//clear current form fields
		$scope.contactForm.fields = new Array();
		var $promise = ContactsPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					
					//set form fields
					$scope.contactForm.model = {};
					$scope.contactForm.fields = response.objData;
					
					//set form values from contact data
					angular.forEach($scope.contactForm.fields, function (objElement, key) { 
						$scope.contactForm.model[objElement.key] = $scope.objContactData[objElement.key];
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
		
		// Track the request and show its progress to the user.
		$scope.progress.addPromise($promise);
	}; //end function	
}]);

contactControllers.controller('ContactsEditCtrl', ['$scope', '$route', '$routeParams', '$window', 'ContactsPageService', 'promiseTracker', function ContactsEditCtrl($scope, $route, $routeParams, $window, ContactsPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.objPageConfig = {};
	$scope.objContactData = [];
	$scope.cid = false;
	$scope.cpp_form_id = false;
	$scope.objCPPFormsList = false;
	$scope.messages = false;
	
	$scope.contactForm = this;
	$scope.contactForm.model = {};
	$scope.contactForm.fields = [];
	
	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	$scope.contactForm.submitContact = function () {	
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var objData = $scope.contactForm.model;
		
		objData.acrq = 'update-contact';
		objData.cid = $scope.cid;
		objData.cpp_form_id = $scope.cpp_form_id;
		
		//add fields that are not set
		angular.forEach($scope.contactForm.fields, function (objField, i) {
			if (typeof objData[objField.key] == 'undefined')
			{
				objData[objField.key] = '';
			}//end if
		});
		
		//update user messages section
		angular.element('#messages').toggleClass('alert-info');
		$scope.messages = 'Processing Contact Information';
		$scope.contactForm.form.$invalid = true;
		
	    jQuery('html, body').animate({
	        scrollTop: 0
	    }, 100);
	    
		var $promise = ContactsPageService.post(objData, 
			function success(response) {
				logToConsole(response);
				
				//update user messages section
				angular.element('#messages').toggleClass('alert-info');
				$scope.messages = '';
				
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					if (typeof response.form_messages != 'undefined')
					{
						//update user messages section
						angular.element('#messages').toggleClass('alert-warning');
						$scope.messages = 'Update operation failed, please check for error messages below';
						
						handleFormlyFormValidationErrors($scope.contactForm.fields, $scope.contactForm.model, response.form_messages);	
						return false;
					}//end if
				}//end if
				
				if (typeof response.objData != 'undefined' && typeof response.objData.errors != 'undefined' && response.objData.errors > 0)
				{
					//update user messages section
					angular.element('#messages').toggleClass('alert-warning');
					$scope.messages = 'Update operation failed, please check for error messages below';
					
					handleFormlyFormValidationErrors($scope.contactForm.fields, $scope.contactForm.model, response.objData.errors);	
					return false;
				}//end if
				
				angular.element('#messages').toggleClass('alert-info alert-success');
				$scope.messages = 'Contact Details have been updated';
				$scope.contactForm.form.$invalid = false;
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				
				angular.element('#messages').toggleClass('alert-info alert-error');
				$scope.messages = 'Processing Failed, details have not been saved';
				$scope.contactForm.form.$invalid = false;
			}
		);
		
		// Track the request and show its progress to the user.
		$scope.progress.addPromise($promise);
	}; //end function
	
	//panels
	$scope.togglePanel = function(panel, status) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope[panel] = status;
		
		if (status == true)
		{			
			if (panel == 'changeFormLayoutPanel')
			{
				$scope.loadCPPForms();
			}//end if
			
			doCreateSlidePanel(status);
		} else {
			doRemoveSlidePanel(status);
		}//end if
	}; //end function	
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	
	$scope.loadContact = function () { 
		return loadContact();
	};
	function loadContact() {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.cid = $routeParams.id;
		
		var objRequest = {
			acrq: 'load-contact',
			cid: $scope.cid
		};
		
		var $promise = ContactsPageService.get(objRequest, 
			function success(response) {
				logToConsole(response);
				
				//set contact data
				$scope.objContactData = {};
				angular.forEach(response.objData, function (value, key) {
					$scope.objContactData[key] = value;
				});
				
				//update page title
				$scope.pageContent = '';
				$scope.objPageConfig.pageTitle = 'Update details for ' + $scope.objContactData.fname + ' ' + $scope.objContactData.sname; 
				
				//now load the cpp form the request
				loadCPPForm({});
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				
				//update page title
				$scope.pageContent = '';
				$scope.objPageConfig.pageTitle = 'An error occurred and process could not be completed, please try again in a few minutes'; 
			}
		);
		
		// Track the request and show its progress to the user.
		$scope.progress.addPromise($promise);
	}; //end function
	
	$scope.loadCPPForm = function (options) {return loadCPPForm(options);};
	function loadCPPForm(options) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var objRequest = {
				acrq: 'load-cpp-form'
		};
		
		if (typeof options.cpp_form_id != "undefined")
		{
			objRequest.cpp_fid = options.cpp_form_id;
			$scope.cpp_form_id = options.cpp_form_id;
		}//end if
		
		//clear current form fields
		$scope.contactForm.fields = new Array();
		var $promise = ContactsPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					
					//set form fields
					$scope.contactForm.model = {};
					$scope.contactForm.fields = response.objData;
					
					//set form values from contact data
					angular.forEach($scope.contactForm.fields, function (objElement, key) { 
						$scope.contactForm.model[objElement.key] = $scope.objContactData[objElement.key];
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
		
		// Track the request and show its progress to the user.
		$scope.progress.addPromise($promise);
	}; //end function	
	
	$scope.loadCPPForms = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if ($scope.objCPPFormsList != false)
		{
			return $scope.objCPPFormsList;
		}//end if
		
		var $promise = ContactsPageService.get({acrq: 'load-cpp-form-list'}, 
				function success(response) {
					logToConsole(response);

					var objForms = Array();
					angular.forEach(response.objData, function (form, id) {
						objForms.push({id: id, form: form});
					});
					$scope.objCPPFormsList = objForms;
					
					//remove loader from panel
					angular.element('#change_panels_section').find('.wait-image').remove();
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					$scope.pageContactLoadingContent = '';
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
			// Track the request and show its progress to the user.
			$scope.progress.addPromise($promise);
	};	
}]);