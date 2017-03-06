'use strict';

var formTemplateControllers = angular.module('formTemplateControllers', []);

formTemplateControllers.controller('HomeCtrl', [
											'$scope', 
											'$route', 
											'$routeParams', 
											'$window', 
											'FormTemplatePageService', 
											'promiseTracker', 
											function HomeCtrl($scope, $route, $routeParams, $window, FormTemplatePageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.objPageConfig = global_page_config;
	$scope.objRecords = [];
	$scope.progress = promiseTracker();
	
	$scope.init = function () {
		/**
		* Make sure user is logged in
		*/
		userIsLoggedin();
		
		return loadTemplates();
	}; //end function
	
	$scope.toggleTemplateStatus = function (objRecord) {
		objRecord.acrq = 'toggle-template-status';
		
		FormTemplatePageService.post(objRecord,
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to update status', '<p>Data could not be loaded, process failed with response : ' + response.response + '</p>');
						return false;
					}//end if
					
					doMessageAlert('Status updated', '<p>The status for the set record has been updated</p>');
					angular.forEach($scope.objRecords, function (obj, i) {
						if (obj.id == objRecord.id)
						{
							$scope.objRecords[i].active = (1 - objRecord.active);
						}//end if
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	}; 
	
	function loadTemplates(objRequest) {
		if (typeof objRequest == 'undefined')
		{
			var objRequest = {
				acrq: 'load-templates',	
			};
		}//end if
		
		$scope.objRecords = Array();
		var $p = FormTemplatePageService.get(objRequest,
			function success(response) {
				logToConsole(response);
				//check for errors
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					doErrorAlert('Unable to load data', '<p>Data could not be loaded, process failed with response : ' + response.response + '</p>');
					return false;
				}//end if
				
				angular.forEach(response.objData, function (obj, i) {
					$scope.objRecords.push(obj);
				});
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		$scope.progress.addPromise($p);
		return $p;
	}; //end function
}]);

formTemplateControllers.controller('ContentCtrl', [
										'$scope', 
										'$route', 
										'$routeParams', 
										'$window', 
										'FormTemplatePageService', 
										'promiseTracker', 
	function ContentCtrl($scope, $route, $routeParams, $window, FormTemplatePageService, promiseTracker, formlyVersion) {
		$scope.pageContent = global_wait_image;
		$scope.global_wait_image = global_wait_image;
		$scope.objPageConfig = global_page_config;
		$scope.objRecord = false;
		$scope.objRecordAttachedFiles = [];
		$scope.objAvailableFiles = [];
		$scope.objFormsUsingTemplate = [];
		$scope.attachFileModel = {};
		$scope.attachedFileContentModel = {};
		
		$scope.attachTemplateFilePanelState = false;
		$scope.displayFileContentPanelState = false;
		
		$scope.progress = promiseTracker();
		
		$scope.templateAdminForm = {
			fields: [],
			model: {},
			submitForm: function () {
				//check if '#content' tag is set
				if ($scope.templateAdminForm.model.content.search('#content') == -1)
				{
					alert('The #content keyword is not set within the content, it is required.');
					return false;
				}//end if
				
				if ($scope.objRecord == false) {
					//create template
					$scope.templateAdminForm.model.acrq = 'create-template';
				} else {
					//update template
					$scope.templateAdminForm.model.acrq = 'update-template';
				}//end if
				
				var $p = FormTemplatePageService.post($scope.templateAdminForm.model,
						function success(response) {
							logToConsole(response);
							//check for errors
							if (typeof response.error != 'undefined' && response.error == 1)
							{
								doErrorAlert('Unable to save data', '<p>Data could not be loaded, process failed with response : ' + response.response + '</p>');
								return false;
							}//end if
							
							if ($scope.templateAdminForm.model.acrq == 'create-template')
							{
								//reload window to new record
								var url = "#!/content/" + response.objData.id;
								$window.location.href = url;
							}//end if
						},
						function error(errorResponse) {
							logToConsole(errorResponse);
							doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
						}
					);
			
				$scope.progress.addPromise($p);
				return $p;
			}
		};
		
		$scope.init = function () {
			/**
			* Make sure user is logged in
			*/
			userIsLoggedin();
			
			setConfigFormFields();
			
			if ($routeParams.record_id > 0)
			{
				//an existing record is being updated
				var objRequest = {
					acrq: 'load-template',
					id: $routeParams.record_id
				};
				
				var $p = loadRecord(objRequest);
				
				//now load any attached files for the template
				$p.$promise.then(function () {
					loadTemplateFiles();
					loadFormsUsingTemplate();
				});
			}//end if
		}; //end function

	$scope.togglePanel = function (panel, status, id) {
		$scope[panel] = !$scope[panel];
		var flag = $scope[panel];
		
		switch (panel) {
			case 'attachTemplateFilePanelState':
				if (flag == true) {
					//load available files
					if ($scope.objAvailableFiles.length == 0) {
						loadAvailableFiles();
					}//end if
					
					//is a record being updated?
					if (typeof id != 'undefined' && id > 0)
					{
						angular.forEach($scope.objRecordAttachedFiles, function (objFile, i) {
							if (objFile.id == id)
							{							
								//rebuild the model
								$scope.attachFileModel = {
										fk_id_filemanager_content: objFile.fk_id_filemanager_content,
										description: objFile.description,
										active: (objFile.active * 1),
										fk_id_form_templates: objFile.fk_id_form_templates,
										acrq: 'update-attached-template-file',
										id: id,
								};
							}//end if
						});
					}//end if
				};
				break;
				
			case 'displayFileContentPanelState':
				if (flag == true) {
					angular.forEach($scope.objRecordAttachedFiles, function (objFile, i) {
						if (objFile.id == id)
						{		
							objFile.acrq = 'read-template-attached-file';
							var $p = FormTemplatePageService.get(objFile,
									function success(response) {
										logToConsole(response);
										//check for errors
										if (typeof response.error != 'undefined' && response.error == 1)
										{
											doErrorAlert('Unable to read file', '<p>Data could not be loaded, process failed with response : ' + response.response + '</p>');
											return false;
										}//end if
										
										$scope.attachedFileContentModel.file_details = objFile;
										$scope.attachedFileContentModel.file_content = response.objData;
									},
									function error(errorResponse) {
										logToConsole(errorResponse);
										doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
									}
								);
						
							$scope.progress.addPromise($p);
						}//end if
					});					
				} else {
					$scope.attachedFileContentModel = {};
				}//end if
				break;
		}//end switch
		
		if (flag == true)
		{
			doCreateSlidePanel({});
		} else {
			doRemoveSlidePanel({});
		}//end if
	}; //end function
	
	$scope.attachFileToTemplate = function () {
		if ($scope.attach_file_model == false || $scope.objRecord == false)
		{
			return false;
		}//end if

		//where a new file is attached, set some settings
		if (typeof $scope.attachFileModel.acrq == 'undefined')
		{
			$scope.attachFileModel.acrq = 'attach-template-file';	
			$scope.attachFileModel.id = $scope.objRecord.id;
			$scope.attachFileModel.fk_id_form_templates = $scope.objRecord.id;
		}//end if
		
		if (typeof $scope.attachFileModel.description == 'undefined')
		{
			$scope.attachFileModel.description = 'NA';
		}//end if
		
		if (typeof $scope.attachFileModel.active == 'undefined')
		{
			$scope.attachFileModel.active = 0;
		}//end if
		
		var $p = FormTemplatePageService.post($scope.attachFileModel,
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to attach file', '<p>Data could not be loaded, process failed with response : ' + response.response + '</p>');
						return false;
					}//end if
					
					$scope.attachFileModel = {};
					$scope.togglePanel('attachTemplateFilePanelState', false);
					//reload template files
					loadTemplateFiles();
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	
		$scope.progress.addPromise($p);
		return $p;
	}; //end function
	
	$scope.detachFileFromTemplate = function (objFile) {
		if (confirm('Are you sure you want to remove this file?') != true)
		{
			return false;
		}//end if

		objFile.acrq = 'detach-template-file';
		var $p = FormTemplatePageService.post(objFile,
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to attach file', '<p>Data could not be loaded, process failed with response : ' + response.response + '</p>');
						return false;
					}//end if
					
					//remove file from dataset
					angular.forEach($scope.objRecordAttachedFiles, function (objF, i) {
						if (objF.id == objFile.id)
						{
							$scope.objRecordAttachedFiles.splice(i, 1);
						}//end if
					});
					
					doInfoAlert('File detached from Look and Feel', '<p>A file has been removed from the Look and Feel');
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	
		$scope.progress.addPromise($p);
		return $p;
	};
	
	$scope.toggleAttachedFileStatus = function (objFile) {
		objFile.acrq = 'toggle-attached-file-status';
		var $p = FormTemplatePageService.post(objFile,
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to update file status', '<p>Data could not be loaded, process failed with response : ' + response.response + '</p>');
						return false;
					}//end if
					
					//update file in dataset
					angular.forEach($scope.objRecordAttachedFiles, function (objF, i) {
						if (objF.id == objFile.id)
						{
							$scope.objRecordAttachedFiles[i].active = (1 - (objF.active * 1));
						}//end if
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	
		$scope.progress.addPromise($p);
		return $p;
	}; //end function
	
	$scope.loadFormsUsingTemplate = function () {
		return loadFormsUsingTemplate();
	};
	
	$scope.clearFormCaches = function () {
		angular.forEach($scope.objFormsUsingTemplate, function (objForm, i) {
			if (objForm.active == 1)
			{
				var $p = jQuery.ajax({
					url: '/forms/bf/' + objForm.id + '?cache_clear=1',
				});
				$scope.progress.addPromise($p);
			}//end if
		});
	};
	
	function loadRecord(objRequest) {
		var $p = FormTemplatePageService.get(objRequest,
					function success(response) {
						logToConsole(response);
						//check for errors
						if (typeof response.error != 'undefined' && response.error == 1)
						{
							doErrorAlert('Unable to update status', '<p>Data could not be loaded, process failed with response : ' + response.response + '</p>');
							return false;
						}//end if
						
						$scope.objRecord = response.objData;
						$scope.templateAdminForm.model = response.objData;
					},
					function error(errorResponse) {
						logToConsole(errorResponse);
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					}
				);
		
		$scope.progress.addPromise($p);
		return $p;
	};
	
	function loadFormsUsingTemplate() {
		if (typeof $scope.objRecord.id == 'undefined')
		{
			return true;
		}//end if
		
		var objRequest = {
				acrq: 'load-forms-using-template',
				id: $scope.objRecord.id
			};
			
		$scope.objFormsUsingTemplate = Array();
		var $p = FormTemplatePageService.get(objRequest,
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load data', '<p>Data could not be loaded, process failed with response : ' + response.response + '</p>');
						return false;
					}//end if
					
					angular.forEach(response.objData, function (objForm, i) {
						if (typeof objForm.id != 'undefined')
						{
							$scope.objFormsUsingTemplate.push(objForm);
						}//end if
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	
		$scope.progress.addPromise($p);
		return $p;
	};
	
	function loadTemplateFiles() {
		var objRequest = {
			acrq: 'load-template-files',
			id: $scope.objRecord.id
		};
		
		$scope.objRecordAttachedFiles = Array();
		var $p = FormTemplatePageService.get(objRequest,
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to update status', '<p>Data could not be loaded, process failed with response : ' + response.response + '</p>');
						return false;
					}//end if
					
					angular.forEach(response.objData, function (objFile, i) {
						if (typeof objFile.id != 'undefined')
						{
							$scope.objRecordAttachedFiles.push(objFile);
						}//end if
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	
		$scope.progress.addPromise($p);
		return $p;
	};
	
	function loadAvailableFiles() {
		var objRequest = {
			acrq: 'load-available-files',	
		};
		
		var $p = FormTemplatePageService.get(objRequest,
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load available files', '<p>Data could not be loaded, process failed with response : ' + response.response + '</p>');
						return false;
					}//end if
					
					angular.forEach(response.objData, function (objFile, i) {
						if (typeof objFile.id != 'undefined')
						{
							$scope.objAvailableFiles.push(objFile);
						}//end if
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	
		$scope.progress.addPromise($p);
		return $p;
	};
	
	function setConfigFormFields() {
		var arr_fields = [
			{
				key: 'template',
				defaultValue: '',
        		'type': 'input',
        		'templateOptions': {
        			'type': 'input',
        			'label': 'Title for Look and Feel',
        			'placeholder': 'Enter a title for the Look and Feel',
        			'title': 'Add a title for easier identification',
        			'maxlength': 50,
        			'required': true
        		},
        		'validation': {
        			'show': true,
        		}	
			},
			
			{
				key: 'content',
				'type': 'tinymce',
				'defaultValue': '<h2>Enter your layout content here.</h2>#content<p style="color: red;">The \'<strong>&#35;content</strong>\' keyword must be present within the content when saved. This is where the form will be appear.</p>',
           		'data': {
           			'tinymceOption': angularTinymceConfig()
           		},
        		'templateOptions': {
        			'type': 'textarea',
        			'label': 'Set Look and Feel content',
        			'placeholder': 'Add content here',
        			'title': 'Add Look and Feel content',
        			'required': true,
        		},
        		'validation': {
        			'show': true,
        		}	
			},
			
			{
				key: 'active',
				'defaultValue': 1,
			   'type': 'checkbox',
			   'templateOptions': {
				   'type': 'checkbox',
				   'label': 'Active',
				   'title': 'Set Look and Feel Status',
				   'checkboxCheckedValue': 1,
				   'uncheckboxCheckedValue': 0
			   },
			   'validation': {'show': true},
			   'ngModelAttrs': {
				   'checkboxCheckedValue': {'attribute': 'ng-true-value'},
				   'uncheckboxCheckedValue': {'attribute': 'ng-false-value'},
			   }
			},
		];
		
		$scope.templateAdminForm.fields = arr_fields;
	}; //end function
}]);

