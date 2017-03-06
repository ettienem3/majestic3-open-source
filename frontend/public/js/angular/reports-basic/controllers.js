'use strict';

var basicReportsControllers = angular.module('basicReportsControllers', []);

basicReportsControllers.controller('HomeCtrl', ['$scope', '$route', '$routeParams', '$window', 'ReportPageService', 'BasicReportPageService', 'promiseTracker', function HomeCtrl($scope, $route, $routeParams, $window, ReportPageService, BasicReportPageService, promiseTracker) {
	$scope.objPageConfig = global_page_config;
	$scope.global_wait_image = global_wait_image;
	
	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	//progress flags
	$scope.load_reports_available = false;
	$scope.load_cached_reports_available = false;
	$scope.load_report_params_form = false;
	$scope.load_cached_report_content = false;
	
	//report containers
	$scope.objAvailableReports = [];
	$scope.objAvailableReportsGrouped = {
			HTMLTable: 		{label: 'HTML Table', reports: []},
			HTMLPage: 		{label: 'HTML Page', reports: []},
			SimpleChart: 	{label: 'Simple Chart', reports: []},
			CSVFile: 		{label: 'CSV File', reports: []},
			CodedModel: 	{label: 'Coded Model (CSV)', reports: []},
			JSON: 			{label: 'JSON', reports: []},
	};
	$scope.objAvailableCachedReports = [];
	$scope.objCachedLocalContentReports = {};
	$scope.objCachedLocalReportForms = {};
	$scope.report_generated_content = '...';
	
	//cached reports vars
	$scope.cache_counter_active = false;
	$scope.cache_refresh_countdown = 120;
	
	//report params form
	$scope.reportParams = {
		fields: [],
		model: {},
		form: {},
		options: {},
		report_title: '',
		objReport: false,
		submitForm: function () {
			
			if (typeof $scope.reportParams.model.fk_id_report_datasources == 'undefined')
			{
				doErrorAlert('Datasource is required', '<p>Please select a datasource for this operation.</p>');
				return false;
			}//end if
			
			if ($scope.reportParams.model.fk_id_report_datasources == '')
			{
				doErrorAlert('Datasource is required', '<p>Please select a datasource for this operation</p>');
				return false;
			}//end if
			
			//create request object
			var objRequest = {
				'acrq': 'generate-cached-report',	
				'report_id': $scope.reportParams.objReport.id,
				'report_type': 'report'
			};
			
			//add all form fields to request
			angular.forEach($scope.reportParams.fields, function (objField, i) {
				objRequest[objField.key] = '';
			});
			
			//add user set values
			angular.forEach($scope.reportParams.model, function (value, field) {
				objRequest[field] = value;
			});
			
			//execute request
			generateCachedReport(objRequest);
			
			//close form
			togglePanel('reportParamsFormState', false);
		},
		clearForm: function () {
			$scope.reportParams.model = {};
		}
	};

	$scope.loadInitData = function () {
		listAvailableReports();
		listAvailableCachedReports();
		
		//start refresh counter
		setInterval(function () {
			if ($scope.cache_counter_active == true)
			{
				$scope.cache_refresh_countdown--;
				$scope.$apply();
				
				if ($scope.cache_refresh_countdown < 2)
				{
					$scope.cache_counter_active = false;
					listAvailableCachedReports();
				}//end if
			}//end if
		}, 1000);
	};
	
	$scope.togglePanel = function (panel, status) {
		return togglePanel(panel, status);
	};
	
	function togglePanel(panel, status) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if (typeof status != 'undefined' && (status === false || status === true))
		{
			$scope[panel] = status;
		} else {
			$scope[panel] = !$scope[panel];
		}//end if
		
		var flag = $scope[panel];
		if (flag == true)
		{
			switch (panel)
			{
				case 'reportParamsFormState':
					
					break;
			}//end switch
			
			doCreateSlidePanel({});
		} else {
			doRemoveSlidePanel({});
		}//end if
	};//end function
	
	//list available reports
	$scope.listAvailableReports = function () {
		return listAvailableReports();
	};
	
	$scope.requestReportForm = function (objReport) {
		togglePanel('reportParamsFormState');
		return requestReportForm(objReport);
	}; 
	
	$scope.loadCachedReport = function (objCachedReport) {
		return loadCachedReport(objCachedReport);
	};
	
	$scope.listAvailableCachedReports = function () {
		return listAvailableCachedReports();
	};
	
	$scope.deleteCachedReport = function(objCachedReport) {
		return deleteCachedReport(objCachedReport);
	};
	
	/**
	 * Load a list of basic reports available
	 */
	function listAvailableReports()
	{
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.load_reports_available = true;
		var objRequest = {
				acrq: 'list-basic-reports'
		};
		
		var $p = ReportPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					
					//add reports to the scope object
					angular.forEach(response.objReports, function (objReport, i) {
						$scope.objAvailableReports.push(objReport);
						
						//group reports by type
						var type = objReport.report_generators_name;
						type = type.replace(/[\W_]+/g,"");
						if (typeof $scope.objAvailableReportsGrouped[type] == 'undefined')
						{
							$scope.objAvailableReportsGrouped[type] = {
									label: objReport.report_generators_name,
									reports: []
							};
						}//end if
						$scope.objAvailableReportsGrouped[type].reports.push(objReport);
					});
		
					$scope.load_reports_available = false;
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					$scope.load_reports_available = false;
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);
		
	};//end function
	
	/**
	 * Load a list of cached reports available to this profile
	 */
	function listAvailableCachedReports() {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.load_cached_reports_available = true;
		var objRequest = {
				acrq: 'list-cached-reports'
		};
		
		var $p = BasicReportPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					
					//add reports to the scope object
					$scope.objAvailableCachedReports = Array();
					angular.forEach(response.objReports, function (objReport, i) {
						switch (objReport.cache_status)
						{
							case 'formulating':
							case 'complete':
							case 'archiving':
								objReport.cache_status_pretty = objReport.cache_status;
								break;
							
							default:
								objReport.cache_status_pretty = 'Unknown';
								break;
						}//end switch
						
						//check if downloads were requested for the report
						if (typeof objReport.request_data != 'undefined')
						{
							if (typeof (objReport.request_data.query_data != 'undefined'))
							{
								if (typeof objReport.request_data.query_data.download != 'undefined')
								{
									switch (objReport.request_data.query_data.download)
									{
										case '1': //csv
											objReport.report_generator_name = 'CSV Download';
											break;
											
										case '2': //pdf
											objReport.report_generator_name = 'PDF Download';
											break;
									}//end switch
								}//end if
							}//end if							
						}//end if
						
						$scope.objAvailableCachedReports.push(objReport);
					});
					
					$scope.load_cached_reports_available = false;
					resetCacheCounter();
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					$scope.load_cached_reports_available = false;
					resetCacheCounter();
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);
	}; //end function
	
	//set cache report update loop
	function resetCacheCounter()
	{
		$scope.cache_refresh_countdown = 120;
		$scope.cache_counter_active = true;
	}//end function
	
	function requestReportForm(objReport) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.reportParams.report_title = objReport.display_title;
		$scope.load_report_params_form = true;

		//clear any current fields and values
		$scope.reportParams.fields = Array();
		$scope.reportParams.model = {};
		$scope.reportParams.objReport = objReport;
		
		//reset select element
		angular.element('#select_report_id').val('****');
		
		//check if form has been cached
		var ref = 'form' + objReport.id;
		if (typeof $scope.objCachedLocalReportForms[ref] !== 'undefined')
		{
			$scope.reportParams.fields = $scope.objCachedLocalReportForms[ref].form;
			$scope.load_report_params_form = false;
			return;
		}//end if
		
		var objRequest = {
			'acrq': 'load-report-params-form',
			'report_id': objReport.id,
			'report_params': {type: 'report'},
		};
		
		var $p = ReportPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);		
					
					//add fields to the form
					angular.forEach(response.objForm, function (objField, i) {
						//check for specific fields and add some more details to them
						switch (objField.key)
						{
							case '#global_start_date':
							case '#global_end_date':
							    objField.ngModelElAttrs = {
							        'data-provide': 'datepicker',
							        'data-date-format': 'yyyy-mm-dd',
							        'data-date-clear-btn': 'true',
							        'data-date-autoclose': 'true',
							        'data-date-today-highlight': 'true',
							        'data-date-today-btn': 'true',
							        'readonly': 'readonly',
							      }
								break;
						}//end switch
						
						$scope.reportParams.fields.push(objField);
					});
					
					//add pdf and csv download options
					if (objReport.csv_download == '1' || objReport.pdf_download == '1')
					{
						//add download field option
						var objField = {
							key: 'download_option',
							type: 'select',
							templateOptions: {
								'type': 'select',
								'label': 'Download as File',
								'title': 'Indicate if file should be downloaded as CSV or PDF where available',
								'valueProp': 'optionID',
								'labelProp': 'optionLabel',
								options: [{optionID: 0, optionLabel: 'Normal'}]
							},
							validation: {
								show: true,
							}
						};
						
						if (objReport.csv_download == '1')
						{
							objField.templateOptions.options.push({optionID: 1, optionLabel: 'CSV'});
						}//end if
						
						if (objReport.pdf_download == '1')
						{
							objField.templateOptions.options.push({optionID: 2, optionLabel: 'PDF'});
						}//end if
						
						$scope.reportParams.fields.push(objField);
					}//end if
					
					//cache form
					$scope.objCachedLocalReportForms[ref] = {form: $scope.reportParams.fields};
					$scope.load_report_params_form = false;
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					$scope.load_report_params_form = false;
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);
	}//end function
	
	function generateCachedReport(objRequest)
	{		
		var $p = BasicReportPageService.post(objRequest, 
			function success(response) {
				logToConsole(response);
				
				//trigger reload of cached reports table
				listAvailableCachedReports();
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
	}//end function
	
	function loadCachedReport(objCachedReport) 
	{
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.report_generated_content = '<p>Loading report, this might take a while. Please be patient. <br/><small>If reports takes to long to load, consider adding more restrictive filters or request content as a download.</small></p>' + global_wait_image + '<hr/>';
		$scope.load_cached_report_content = true;
		
		//use jquery to scroll to element, this should be replace with angular at some point
	    jQuery('html, body').animate({
	        scrollTop: jQuery("#container_report_content").offset().top
	    }, 1000);
	    
	    jQuery('#container_report_content').html($scope.report_generated_content);
		
	    //check if content has been cached locally already
//	    var ref = 'ref' + objCachedReport.cache_reference;
//	    if (typeof $scope.objCachedLocalContentReports[ref] !== 'undefined' && objCachedReport.report_generator_id != '3')
//	    {
//	    	jQuery('#container_report_content').html($scope.objCachedLocalContentReports[ref].content);
//	    	$scope.load_cached_report_content = false;
//	    	return;
//	    };
	    
		var objRequest = {
				acrq: 'load-cached-report-content',
				reference: objCachedReport.cache_reference,
				id: objCachedReport.cache_id,
				report_id: objCachedReport.report_id
		};
//http://nicolas.kruchten.com/pivottable/examples/		
		var $p = BasicReportPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					
					if (response.objResponse.download == false)
					{
						//set contents to var
						var content = response.objResponse.content;
						$scope.report_generated_content = content;
					
						//add content to container
						switch (objCachedReport.report_generator_id)
						{
							case '3': //html table
								jQuery('#container_report_content').html(content);
								var element_handle = jQuery('#container_report_content').find('table').attr('id');
								jQuery('#container_report_content').find('table')
									.addClass('table')
									.addClass('table-striped')
									.addClass('table-hover')
									.removeClass('display')
									.removeClass('generic-table');
								
								var table = jQuery("#" + element_handle).DataTable(
									{
										dom: 'Blfrtip', //enables buttons, length, filter, response etc
										buttons: ['csv', 'print'],
										"responsive": true,
										"aaSorting": [], //disable initialsort
										"paging": true,
										"pagingType": "full_numbers",
										"lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
										"ordering": true,
										"info": true,
										'drawCallback': function (settings) {
											jQuery('#container_report_content').find('.dt-button').addClass('btn').addClass('btn-primary').css('margin', '5px');
											jQuery('#container_report_content').find('.paginate_button').addClass('btn').addClass('btn-primary').css('margin', '5px');
										}
									}
								);
								jQuery('#container_report_content').find('.sorting').append(' <span class="glyphicon glyphicon-resize-vertical text-small"></span>');
								

								break;
								
							case '12': //json
								var jsonStr = jQuery("pre").text();
								var jsonObj = JSON.parse(content);
								var jsonPretty = JSON.stringify(jsonObj, null, '\t');

								jQuery('#container_report_content').html('<pre>' + jsonPretty + '</pre>');
								break;
								
							default:
								jQuery('#container_report_content').html(content);
								break;
						}//end switch
						
						//cache generated contents
						var ref = 'ref' + objCachedReport.cache_reference;
						$scope.objCachedLocalContentReports[ref] = {content: jQuery('#container_report_content').html()};
					} else {
						//create iframe to download file
						var url = response.objResponse.content;
						$scope.report_generated_content = '<p>Your download should start shortly, if not, <a href="' + url + '" target="_blank">click here</a></p><iframe src="' + url + '" style="display:none;"></iframe>';
						jQuery('#container_report_content').html($scope.report_generated_content);
					}//end if
					
					$scope.load_cached_report_content = false;
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					$scope.load_cached_report_content = false;
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	}//end function
	
	function deleteCachedReport(objCachedReport)
	{
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if (confirm('Are you sure you want to remove this report?') != true)
		{
			return false;
		}//end if
		
		var objRequest = {
				acrq: 'delete-cached-report',
				id: objCachedReport.cache_id,
				reference: objCachedReport.cache_reference
		};
		
		var $p = ReportPageService.get(objRequest, 
				function success(response) {
					logToConsole(response);
					
					//trigger reload of cached reports table
					listAvailableCachedReports();
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	}//end function
}]);