var goFetchControllers = angular.module('goFetchControllers', []);

/**
 * Home page controller
 * @param $scope
 * @param $route
 * @param $routeParams
 * @param $window
 * @param GoFetchPageService
 * @param promiseTracker
 * @param formlyVersion
 */
goFetchControllers.controller('HomeCtrl', ['$scope', '$route', '$routeParams', '$window', 'GoFetchPageService', 'promiseTracker', function HomeCtrl($scope, $route, $routeParams, $window, GoFetchPageService, promiseTracker, formlyVersion) {
	$scope.pageActivity = '';
	$scope.page_content_loaded_flag = false;

	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	/**
	 * Initialize the page on first load
	 */
	$scope.loadMainPageContent = function () { return loadMainPageContent(); };
	function loadMainPageContent() {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if ($scope.page_content_loaded_flag == true)
		{
			return;
		}//end if
		
		$scope.pageActivity = global_wait_image;
		
		//request data
		var $promise = GoFetchPageService.get({'acrq': 'load-home-page-content'},
			function success(response) {
				logToConsole(response);
				$scope.pageActivity = '';
				
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				$scope.pageActivity = '';
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		//mark content as loaded
		$scope.page_content_loaded_flag = true;
	}; //end function
}]);

/**
 * Controller dealing with filtering
 * @param $scope
 * @param $route
 * @param $routeParams
 * @param $window
 * @param GoFetchPageService
 * @param promiseTracker
 * @param formlyVersion
 */
goFetchControllers.controller('FilterCtrl', ['$scope', '$route', '$routeParams', '$window', 'GoFetchPageService', 'promiseTracker', function FilterCtrl($scope, $route, $routeParams, $window, GoFetchPageService, promiseTracker) {
	$scope.pageActivity = '';
	$scope.global_wait_image = global_wait_image;
	$scope.page_content_loaded_flag = false;
	$scope.objPageConfig = {};
	
	//container of contact data
	$scope.contacts_loading = false;
	$scope.objContacts = false;
	
	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	//form model
	$scope.form_fields = {users: Array(), sources: Array(), references: Array(), statuses: Array()};
	$scope.form_filter = {};
	
	//form handlers
	$scope.filterFormReset = function () {
		$scope.form_filter = {};
		$scope.objContacts = {};
		$scope.objPageConfig.pagination.tpages = [];
	}; //end function
	
	$scope.filterFormSubmit = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.objContacts = {};
		$scope.contacts_loading = true;
		var objRequest = {
			'acrq': 'load-profile-contacts',
		};
		
		angular.forEach($scope.form_filter, function(value, field) {
			objRequest[field] = value;
		});
		
		loadContacts(objRequest);
	}; //end function
	
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
		
		$scope.objContacts = {};
		$scope.contacts_loading = true;
		
		var start_number = 0;
		if (typeof $scope.objPageConfig.pagination.page_urls !== "undefined" && page > 0)
		{
			if (typeof $scope.objPageConfig.pagination.page_urls[parseInt(page - 1)] !== "undefined")
			{
				start_number  = $scope.objPageConfig.pagination.page_urls[parseInt(page - 1)].next;	
			}//end if
		}//end if
		
		var objRequest = {
				'acrq': 'load-profile-contacts',
				'qp_limit': $scope.objHypermedia.pagination.qp_limit, 
				'qp_start': start_number
		};
			
		angular.forEach($scope.form_filter, function(value, field) {
			objRequest[field] = value;
		});
		
		//load the data
		loadContacts(objRequest);
	};

	/**
	 * Initialize the page on first load
	 */
	$scope.loadFilterPageContent = function () { return loadDashboardPageContent(); };
	function loadDashboardPageContent() {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if ($scope.page_content_loaded_flag == true)
		{
			return;
		}//end if
		
		$scope.pageActivity = global_wait_image;
		
		//request data
		var $promise = GoFetchPageService.get({'acrq': 'load-filter-page-content'},
			function success(response) {
				logToConsole(response);
				$scope.pageActivity = '';
				
				//save filter form data
				angular.forEach(response.data.form_element_data.users, function (objUser, i) {
					$scope.form_fields.users.push(objUser);
				});
				
				angular.forEach(response.data.form_element_data.sources, function (objData, i) {
					$scope.form_fields.sources.push(objData);
				});
				
				angular.forEach(response.data.form_element_data.references, function (objData, i) {
					$scope.form_fields.references.push(objData);
				});
				
				angular.forEach(response.data.form_element_data.statuses, function (objData, i) {
					$scope.form_fields.statuses.push(objData);
				});
				
				//mark content as loaded
				$scope.page_content_loaded_flag = true;
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				$scope.pageActivity = '';
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);		
	}; //end function
	
	function loadContacts(objRequest) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		GoFetchPageService.get(objRequest,
				function success(response) {
					logToConsole(response);
					$scope.contacts_loading = false;
					$scope.objContacts = response.data.objContacts;
					
					//set pagination details
					$scope.objPageConfig.pagination = response.data.objHypermedia.pagination;
					$scope.objPageConfig.pagination.tpages = [];
					for (var i = 0; i < response.data.objHypermedia.pagination.pages_total; i++)
					{
						$scope.objPageConfig.pagination.tpages.push({i:1});
					}//end for
					$scope.objHypermedia = response.data.objHypermedia;
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					$scope.pageActivity = '';
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	};
}]);

/**
 * Controller dealinf with dashboards and reports
 * @param $scope
 * @param $route
 * @param $routeParams
 * @param $window
 * @param GoFetchPageService
 * @param promiseTracker
 * @param formlyVersion
 */
goFetchControllers.controller('EnrichCtrl', ['$scope', '$route', '$routeParams', '$window', 'GoFetchPageService', 'promiseTracker', '$timeout', function EnrichCtrl($scope, $route, $routeParams, $window, GoFetchPageService, promiseTracker, $timeout) {
	$scope.pageActivity = '';
	$scope.page_content_loaded_flag = false;
	$scope.enrichPageContentActivity = '';
	$scope.enrichPageContent = '<p>' + global_wait_image +'&nbsp; Loading data...</p>';
	$scope.objChartRawData = {
								'sources': false, 
								'references': false, 
								'contact_statuses': false,
								'complete_report': false,
							};
	$scope.generatedCharts = false;
	  
	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	/**
	 * Initialize the page on first load
	 */
	$scope.loadEnrichPageContent = function () { return loadEnrichPageContent(); };
	function loadEnrichPageContent() {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if ($scope.page_content_loaded_flag == true)
		{
			return;
		}//end if
		
		$scope.pageActivity = global_wait_image;
		
		//request data
		var $promise = GoFetchPageService.get({'acrq': 'load-enrich-page-content'},
			function success(response) {
				logToConsole(response);
				$scope.pageActivity = '';
				
				//load complete report
				//loadEnrichChartDataSegments({'acrq': 'load-enrich-data-complete-dataset'});
				
				//load data segments
				var objRequest = {
					'acrq': 'load-enrich-data-sources'	
				};
				loadEnrichChartDataSegments(objRequest);
				
				//load data segments
				objRequest.acrq = 'load-enrich-data-references';
				loadEnrichChartDataSegments(objRequest);
				
				//load data segments
				objRequest.acrq = 'load-enrich-data-status-history';
				loadEnrichChartDataSegments(objRequest);
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				$scope.pageActivity = '';
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		//mark content as loaded
		$scope.page_content_loaded_flag = true;		
	}; //end function
	
	/**
	 * Load chart data segments
	 */
	function loadEnrichChartDataSegments(objRequest) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		//set enrichPageContentActivity value
		switch (objRequest.acrq)
		{
			case 'load-enrich-data-sources':
				$scope.enrichPageContentActivity = '<p>Requesting Sources Data...</p>';
				var datacontainer = 'sources';
				break;
				
			case 'load-enrich-data-references':
				$scope.enrichPageContentActivity = '<p>Requesting References Data...</p>';
				var datacontainer = 'references';
				break;
				
			case 'load-enrich-data-status-history':
				$scope.enrichPageContentActivity = '<p>Requesting Status History Data...</p>';
				var datacontainer = 'contact_statuses';
				break;
				
			case 'load-enrich-data-complete-dataset':
				$scope.enrichPageContentActivity = '<p>Requesting Complete Data Structure</p>';
				var datacontainer = 'complete_report';
				break;				
		}//end switch
		
		//request data
		var $promise = GoFetchPageService.get(objRequest,
			function success(response) {
				logToConsole(response);
				$scope.enrichPageContentActivity = '';
				
				//save data to object
				$scope.objChartRawData[datacontainer] = response.data;
				
				//build chart
				var chart = buildEnrichCompletedChart();
				if (chart !== false)
				{
					//set default values
					var source_series_label = 'Source';
					var reference_series_label = 'Reference';
					var status_series_label = 'Status';
					var dbgrowth_series_label = 'Database Growth';
					var unsubscribes_series_label = 'Unsubscribes';
					
					if (typeof graph_database_activity_options != 'undefined' && typeof graph_database_activity_options.series_labels != 'undefined')
					{
						if (typeof graph_database_activity_options.series_labels.source != 'undefined' && graph_database_activity_options.series_labels.source != '')
						{
							source_series_label = graph_database_activity_options.series_labels.source;
						}//end if
						
						if (typeof graph_database_activity_options.series_labels.reference != 'undefined' && graph_database_activity_options.series_labels.reference != '')
						{
							reference_series_label = graph_database_activity_options.series_labels.reference;
						}//end if
						
						if (typeof graph_database_activity_options.series_labels.status != 'undefined' && graph_database_activity_options.series_labels.status != '')
						{
							status_series_label = graph_database_activity_options.series_labels.status;
						}//end if
						
						if (typeof graph_database_activity_options.series_labels.database_growth != 'undefined' && graph_database_activity_options.series_labels.database_growth != '')
						{
							dbgrowth_series_label = graph_database_activity_options.series_labels.database_growth;
						}//end if
						
						if (typeof graph_database_activity_options.series_labels.unsubscribes != 'undefined' && graph_database_activity_options.series_labels.unsubscribes != '')
						{
							unsubscribes_series_label = graph_database_activity_options.series_labels.unsubscribes;
						}//end if
					}//end if
					
					chart.addSeries({name: source_series_label, stack: 'source', 	data: loadChartSourceData(chart)}, false);
					chart.addSeries({name: reference_series_label, stack: 'reference', data: loadChartReferenceData(chart)}, false);
					chart.addSeries({name: status_series_label, stack: 'status',	data: loadChartStatusData(chart)}, false);
					chart.addSeries({name: dbgrowth_series_label, type: 'spline', color: 'blue', data: loadChartDBGrowthData(chart)}, false);
					chart.addSeries({name: unsubscribes_series_label, type: 'spline', color: 'red', data: loadChartDBUnsubscribeData(chart)}, true);
				}//end if
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				$scope.enrichPageContentActivity = '';
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
	}; //end function
	
	function buildEnrichCompletedChart() {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if ($scope.objChartRawData.sources == false || $scope.objChartRawData.references == false || $scope.objChartRawData.contact_statuses == false)
		{
			return false;
		}//end if
		
		var arr_months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
		
		//load chart
		var chart_options = {
		        chart: {
		        	renderTo: 'enrichChartContainerComplete',
		            type: 'column',
		            zoomType: 'xy',
		            events: {
		            	drilldown: function (e) {
		            		if (!e.seriesOptions && typeof e.point.options.drilldown_callback === 'function') {
		            			var chart = this;
		            			var arr_data = e.point.options.drilldown_callback(e.point.options.name);	            			
		            			chart.addSeriesAsDrilldown(e.point, arr_data);
		            		}//end if
		            	}
		            }
		        },
		        title: {
		            text: 'My Database Activity'
		        },
		        yAxis: {
		        	allowDecimals: false,
		            min: 0,
		            title: {
		                text: 'Count'
		            },
		            stackLabels: {
		                enabled: true,
		                style: {
		                    fontWeight: 'bold',
		                    color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
		                }
		            }
		        },
		        credits: {
		        	enabled: false,
		        },
		        plotOptions: {
		            column: {
		                stacking: 'normal',
		                dataLabels: {
		                    enabled: true,
		                    crop: true,
		                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
		                    style: {
		                        textShadow: '0 0 3px black'
		                    },
		                    formatter: function () {
		                    	if (typeof this.point.col_text !== 'undefined')
		                    	{
		                    		return this.y + '<br/> ' + this.point.col_text;	
		                    	}//end if
		                    	
		                    	return this.y
		                    }
		                }
		            }
		       },
		       xAxis: {
		    	   labels: {
		    		   formatter: function () {
		    			   var objDate = new Date(this.value);
		    			   return arr_months[objDate.getMonth()] + ' ' + objDate.getFullYear();
		    		   }
		    	   }
		       },
		       drilldown: {
		    	   series: []
		       },
		       series: Array()
		};

		chart_options.series = Array();
	
		var chart = new Highcharts.Chart(chart_options);
		$scope.generatedCharts = {
				complete_chart: chart
		};
		
		return chart;
	};//end function
	
	function loadChartSourceData(objChart) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var source_series_label = 'Source';
		if (typeof graph_database_activity_options != 'undefined' && typeof graph_database_activity_options.series_labels != 'undefined')
		{
			if (typeof graph_database_activity_options.series_labels.source != 'undefined' && graph_database_activity_options.series_labels.source != '')
			{
				source_series_label = graph_database_activity_options.series_labels.source;
			}//end if
		}//end if
		
		var ic = 0;
		var arr_colors = ['#7cb5ec', '#434348', '#90ed7d', '#f7a35c', '#8085e9', '#f15c80', '#e4d354', '#2b908f', '#f45b5b', '#91e8e1'];
		var arr_data = Array();
		angular.forEach($scope.objChartRawData.sources, function (objData, i) {
			angular.forEach(objData.data_temp, function(objSourceData, ii) {
				var obj = {
						name: objSourceData.label,
						y: objSourceData.value,
						x: objSourceData.time,
						color: arr_colors[ic],
						col_text: source_series_label + ': ' + objSourceData.label,
						drilldown: true,
						drilldown_series_name: source_series_label,
						drilldown_callback: function (source) {
							var arr_data = Array();
							
							angular.forEach($scope.objChartRawData.sources, function (objData, i) {
								angular.forEach(objData.data_temp, function(objSourceData, ii) {
									if (objSourceData.label == source)
									{
										arr_data.push({
											y: objSourceData.value,
											x: objSourceData.time,
											name: source,
										})
									}//end if
								});
							});
							
							return {name: source, colorByPoint: true, data: arr_data};
						}//end function
				};
				arr_data.push(obj);
				
				ic++;
				if (ic > 9)
				{
					ic = 0;
				}//end if
			});
		});	
		return arr_data;
	};
	
	function loadChartReferenceData() {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var refenence_series_label = 'Reference';
		if (typeof graph_database_activity_options != 'undefined' && typeof graph_database_activity_options.series_labels != 'undefined')
		{
			if (typeof graph_database_activity_options.series_labels.reference != 'undefined' && graph_database_activity_options.series_labels.reference != '')
			{
				refenence_series_label = graph_database_activity_options.series_labels.reference;
			}//end if
		}//end if
		
		var ic = 9;
		var arr_colors = ['#7cb5ec', '#434348', '#90ed7d', '#f7a35c', '#8085e9', '#f15c80', '#e4d354', '#2b908f', '#f45b5b', '#91e8e1'];
		var arr_data = Array();
		angular.forEach($scope.objChartRawData.references, function (objData, i) {
			angular.forEach(objData.data_temp, function(objReferenceData, ii) {			
				var obj = {
						name: objReferenceData.label,
						y: objReferenceData.value,
						x: objReferenceData.time,
						color: arr_colors[ic],
						col_text: refenence_series_label + ': ' + objReferenceData.label,
						drilldown: true,
						drilldown_series_name: refenence_series_label,
						drilldown_callback: function (reference) {
							var arr_data = Array();
							
							angular.forEach($scope.objChartRawData.references, function (objData, i) {
								angular.forEach(objData.data_temp, function(objSourceData, ii) {
									if (objSourceData.label == reference)
									{
										arr_data.push({
											y: objSourceData.value,
											x: objSourceData.time,
											name: reference,
										})
									}//end if
								});
							});
							
							return {name: reference, colorByPoint: true, data: arr_data};
						}
				};
				arr_data.push(obj);
				
				ic--;
				if (ic < 0)
				{
					ic = 9;
				}//end if
			});
		});
		
		return arr_data;
	};
	
	function loadChartStatusData(objChart) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var status_series_label = 'Status';
		if (typeof graph_database_activity_options != 'undefined' && typeof graph_database_activity_options.series_labels != 'undefined')
		{
			if (typeof graph_database_activity_options.series_labels.status != 'undefined' && graph_database_activity_options.series_labels.status != '')
			{
				status_series_label = graph_database_activity_options.series_labels.status;
			}//end if
		}//end if
		
		var ic = 0;
		var arr_colors = ['#f15c80', '#e4d354', '#2b908f', '#f45b5b', '#91e8e1', '#7cb5ec', '#434348', '#90ed7d', '#8085e9', '#f7a35c'];
		var arr_data = Array();
		angular.forEach($scope.objChartRawData.contact_statuses, function (objData, i) {
			angular.forEach(objData.data_temp, function(objStatusData, ii) {
				var obj = {
						name: objStatusData.label,
						y: objStatusData.value,
						x: objStatusData.time,
						color: arr_colors[ic],
						col_text: status_series_label + ': ' + objStatusData.label,
						drilldown: true,
						drilldown_series_name: status_series_label,
						drilldown_callback: function (status) {
							var arr_data = Array();
							
							angular.forEach($scope.objChartRawData.contact_statuses, function (objData, i) {
								angular.forEach(objData.data_temp, function(objSourceData, ii) {
									if (objSourceData.label == status)
									{
										arr_data.push({
											y: objSourceData.value,
											x: objSourceData.time,
											name: status,
										})
									}//end if
								});
							});
							
							return {name: status, colorByPoint: true, data: arr_data};
						}
				};
				arr_data.push(obj);
				
				ic++;
				if (ic > 9)
				{
					ic = 0;
				}//end if
			});
		});
		
		return arr_data;
	};
	
	function loadChartDBGrowthData(chart) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var arr_data = Array();
		angular.forEach($scope.objChartRawData.contact_statuses, function (objData, i) {
			angular.forEach(objData.data_temp, function(objStatusData, ii) {
				var obj = {
						name: objStatusData.label,
						y: objStatusData.value,
						x: objStatusData.time,
						//color: arr_colors[ic],
						//col_text: 'Status: ' + objStatusData.label
				};
				arr_data.push(obj);
			});
		});
		
		return arr_data;
	}; //end function
	
	function loadChartDBUnsubscribeData(chart) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var arr_data = Array();
		angular.forEach($scope.objChartRawData.sources, function (objData, i) {
			angular.forEach(objData.data_temp, function(objStatusData, ii) {
				var obj = {
						name: objStatusData.label,
						y: objStatusData.value,
						x: objStatusData.time,
						//color: arr_colors[ic],
						//col_text: 'Status: ' + objStatusData.label
				};
				arr_data.push(obj);
			});
		});
		
		return arr_data;
	}; //end function
}]);

/**
 * Controller managing communication requests
 * @param $scope
 * @param $route
 * @param $routeParams
 * @param $window
 * @param GoFetchPageService
 * @param promiseTracker
 * @param formlyVersion
 */
goFetchControllers.controller('TargetCtrl', ['$scope', '$route', '$routeParams', '$window', 'GoFetchPageService', 'promiseTracker', function TargetCtrl($scope, $route, $routeParams, $window, GoFetchPageService, promiseTracker, formlyVersion) {
	$scope.pageActivity = '';
	$scope.page_content_loaded_flag = false;
	$scope.objJourneys = {};
	
	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	//container of contact data
	$scope.contacts_loading = false;
	$scope.objContacts = false;
	
	//form model
	$scope.form_fields = {users: Array(), sources: Array(), references: Array(), statuses: Array(), journey: Array()};
	$scope.form_filter = {};
	
	/**
	 * Initialize the page on first load
	 */
	$scope.loadCommunicatePageContent = function () { return loadCommunicatePageContent(); };
	function loadCommunicatePageContent() {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if ($scope.page_content_loaded_flag == true)
		{
			return;
		}//end if
		
		$scope.pageActivity = global_wait_image;
		
		var $promise = GoFetchPageService.get({'acrq': 'load-target-page-content'},
				function success(response) {
					logToConsole(response);
					$scope.pageActivity = '';
					
					$scope.objJourneys = response.data.objJourneys;
					
					//save filter form data
					angular.forEach(response.data.form_element_data.users, function (objUser, i) {
						$scope.form_fields.users.push(objUser);
					});
					
					angular.forEach(response.data.form_element_data.sources, function (objData, i) {
						$scope.form_fields.sources.push(objData);
					});
					
					angular.forEach(response.data.form_element_data.references, function (objData, i) {
						$scope.form_fields.references.push(objData);
					});
					
					angular.forEach(response.data.form_element_data.statuses, function (objData, i) {
						$scope.form_fields.statuses.push(objData);
					});
					
					angular.forEach(response.data.objJourneys, function (objJourney, i) {
						var obj = {
								value: objJourney.id,
								text: objJourney.journey
						};
						
						$scope.form_fields.journey.push(obj);
					});//end foreach
					
					//mark content as loaded
					$scope.page_content_loaded_flag = true;
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					$scope.pageActivity = '';
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);	
	}; //end function
	
	//form handlers
	$scope.filterFormReset = function () {
		$scope.form_filter = {};
		$scope.objContacts = false;
	}; //end function
	
	$scope.filterFormSubmit = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if (typeof $scope.form_filter.journey == 'undefined' || $scope.form_filter.journey < 0)
		{
			doInfoAlert('Select Data', '<p>Please select Journey</p>');
			return false;
		}//end if
		
		$scope.objContacts = false;
		$scope.contacts_loading = true;
		var objRequest = {
			acrq: 'load-target-bulk-request-estimate'
		};
		
		angular.forEach($scope.form_filter, function(value, field) {
			objRequest[field] = value;
		});
		
		loadTargetContacts(objRequest);
	}; //end function
	
	$scope.requestBulkSend = function () { return requestBulkSendRequest(); };
	function requestBulkSendRequest() {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if (!$scope.objContacts)
		{
			console.log('Details are not set, bulk send request aborted');
			return false;
		}//end if
		
		var objRequest = {
				acrq: 'request-bulk-send-request'
		};
			
		angular.forEach($scope.form_filter, function(value, field) {
			objRequest[field] = value;
		});

		GoFetchPageService.post(objRequest,
				function success(response) {
					logToConsole(response);
					if (reponse.data.error == 1)
					{
						doErrorAlert('Unable to complete request', '<p>' + response.response + '</p>');
						return false;
					}//end if
					
					doInfoAlert('Communication Request', '<p>Request has been created</p>');
					//reset filter
					$scope.objContacts = false;
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	}; //end function
	
	function loadTargetContacts(objRequest) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		GoFetchPageService.get(objRequest,
				function success(response) {
					logToConsole(response);
					$scope.contacts_loading = false;
					var objData = response.data.objTargetData;					
					$scope.objContacts = objData;		
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					$scope.pageActivity = '';
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	};
}]);

/**
 * Controller dealing with the about page
 * @param $scope
 * @param $route
 * @param $routeParams
 * @param $window
 * @param GoFetchPageService
 * @param promiseTracker
 * @param formlyVersion
 */
goFetchControllers.controller('ChannelCtrl', ['$scope', '$route', '$routeParams', '$window', 'GoFetchPageService', 'promiseTracker', function ChannelCtrl($scope, $route, $routeParams, $window, GoFetchPageService, promiseTracker, formlyVersion) {
	$scope.pageActivity = '';
	$scope.page_content_loaded_flag = false;
	$scope.objBulkRequests = false;
	$scope.objRequestReport = {objRequest: false, objReport: false};
	
	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	/**
	 * Initialize the page on first load
	 */
	$scope.loadChannelPageContent = function () { return loadChannelPageContent(); };
	function loadChannelPageContent() {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if ($scope.page_content_loaded_flag == true)
		{
			return;
		}//end if
		
		$scope.pageActivity = global_wait_image;
		var objRequest = {
				'acrq': 'load-channel-page-content',
		};
		
		var $promise = GoFetchPageService.get(objRequest,
				function success(response) {
					logToConsole(response);
					$scope.pageActivity = '';
					
					//mark content as loaded
					$scope.page_content_loaded_flag = true;	
					
					//request bulk send requests
					loadBulkSendRequests();
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					$scope.pageActivity = '';
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);	
	}; //end function
	
	function loadBulkSendRequests()
	{
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var objRequest = {
				'acrq': 'load-bulk-send-requests',
		};
		
		GoFetchPageService.get(objRequest,
				function success(response) {
					logToConsole(response);
					$scope.pageActivity = '';
					
					//mark content as loaded
					$scope.page_content_loaded_flag = true;	
					
					$scope.objBulkRequests = response.data.objRequests;
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					$scope.pageActivity = '';
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);	
	};//end function
	
	$scope.viewCommsReport = function (id) { return viewCommsReport(id);};
	function viewCommsReport(id)
	{
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.objRequestReport.objRequest = false;
		$scope.objRequestReport.objReport = false;
		
		var objRequest = {
				'acrq': 'load-comms-report',
				'bulk_send_id': id
		};
		
		GoFetchPageService.get(objRequest,
				function success(response) {
					logToConsole(response);
					$scope.objRequestReport.objRequest = response.data.objRequest;
					$scope.objRequestReport.objReport = response.data.objReport;

				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					
				}
		);
	};//end function
}]);