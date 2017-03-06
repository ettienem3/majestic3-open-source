'use strict';

var dashboardReportsControllers = angular.module('dashboardReportsControllers', []);

dashboardReportsControllers.controller('HomeCtrl', ['$scope', '$route', '$routeParams', '$window', 'DashboardReportPageService', 'promiseTracker', function HomeCtrl($scope, $route, $routeParams, $window, DashboardReportPageService, promiseTracker) {
	$scope.objPageConfig = global_page_config;
	$scope.global_wait_image = global_wait_image;
	$scope.message = '';
	$scope.objCharts = {
			dashboardMain: {acrq: 'load-dashboard-combined-data'},
			dashboardSources: {acrq: 'load-dashboard-source-data'},
			dashboardReferences: {acrq: 'load-dashboard-reference-data'},
			dashboardUsers: {acrq: 'load-dashboard-user-data'},
			dashboardStatuses: {acrq: 'load-dashboard-status-data'},
			dashboardUnsubscribes: {acrq: 'load-dashboard-unsubscribe-data'},
			dashboardDBGrowth: {acrq: 'load-dashboard-dbgrowth-data'}
	};
	
	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	$scope.objChartRawData = {};
	$scope.objChartConfigTemplate = global_chart_config_template;
	$scope.objCarouselOptions = {
			enabled: 1
	};
	
	$scope.progress = {
			load_chart_data: promiseTracker(),
	};
	
	//initialize the charts
	$scope.loadInitData = function () {
		//initialize charts
		initDashboardMainChart();
		initDashboardSourcesChart();
		initDashboardReferencesChart();
		//initDashboardUsersChart();
		initDashboardStatusesChart();
		initDashboardUnsubscribesChart();
		initDashboardDBGrowthChart();
		
		//now load the overall chart
		setTimeout(function () {
			loadChartData($scope.objCharts.dashboardMain);
		}, 1000);
		
		//wait a few seconds and then load other reports
		setTimeout(function () {
			loadChartData($scope.objCharts.dashboardDBGrowth);
			loadChartData($scope.objCharts.dashboardUnsubscribes);
			
			setTimeout(function () {
				loadChartData($scope.objCharts.dashboardSources);
				loadChartData($scope.objCharts.dashboardReferences);
				loadChartData($scope.objCharts.dashboardStatuses);
			}, 4000);
		}, 4000);
	};
	
	$scope.loadChartData = function (objChart) {
		return loadChartData(objChart);
	};
	
	function loadChartData(objChart)
	{
		$scope.message = 'Requesting Data';
		var objRequest = {
			acrq: objChart.acrq	
		};
		
		var $p = DashboardReportPageService.get(objRequest, 
			function success(response) {
				logToConsole(response);
				$scope.message = 'Rebuilding Chart';
				
				//set data
				$scope.objChartRawData = response.objData;
				
				switch(objRequest.acrq)
				{
					case 'load-dashboard-combined-data':
						objChart.chart.addSeries({name: source_series_label, stack: 'source', 	data: loadChartSourceData(objChart)}, false);
						objChart.chart.addSeries({name: reference_series_label, stack: 'reference', data: loadChartReferenceData(objChart)}, false);
						objChart.chart.addSeries({name: status_series_label, stack: 'status',	data: loadChartStatusData(objChart)}, false);
						objChart.chart.addSeries({name: dbgrowth_series_label, type: 'spline', color: 'blue', data: loadChartDBGrowthData(objChart)}, false);
						objChart.chart.addSeries({name: unsubscribes_series_label, type: 'spline', color: 'red', data: loadChartDBUnsubscribeData(objChart)}, true);
						break;
						
					case 'load-dashboard-source-data':
						objChart.chart.addSeries({name: source_series_label, stack: 'source', 	data: loadChartSourceData(objChart)}, true);
						break;		
						
					case 'load-dashboard-reference-data':
						objChart.chart.addSeries({name: reference_series_label, stack: 'reference', data: loadChartReferenceData(objChart)}, true);
						break;
						
					case 'load-dashboard-user-data':
						
						break;
						
					case 'load-dashboard-status-data':
						objChart.chart.addSeries({name: status_series_label, stack: 'status',	data: loadChartStatusData(objChart)}, true);
						break;
						
					case 'load-dashboard-unsubscribe-data':
						objChart.chart.addSeries({name: unsubscribes_series_label, type: 'spline', color: 'red', data: loadChartDBUnsubscribeData(objChart)}, true);
						break;
						
					case 'load-dashboard-dbgrowth-data':
						objChart.chart.addSeries({name: dbgrowth_series_label, type: 'spline', color: 'blue', data: loadChartDBGrowthData(objChart)}, true);
						break;
				}//end switch
				
				$scope.message = '';
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				$scope.message = 'Request has failed, please try again in a few minutes';
				doErrorAlert('Unable to complete request', '<p>Request has failed, please try again in a few minutes.</p>');
			}
		);
		
		$scope.progress.load_chart_data.addPromise($p);
	};//end function
	
	function initDashboardMainChart() {
		var chart_config = angular.copy($scope.objChartConfigTemplate);
		
		//set target
		chart_config.chart.renderTo = 'dashboardMain';
		chart_config.title.text = 'Overall';
		
		$scope.objCharts.dashboardMain.config = chart_config;
		$scope.objCharts.dashboardMain.chart = new Highcharts.Chart(chart_config);
	};
	
	function initDashboardSourcesChart() {
		var chart_config = angular.copy($scope.objChartConfigTemplate);
		
		//set target
		chart_config.chart.renderTo = 'dashboardSources';
		chart_config.title.text = 'Source Breakdown';
		
		$scope.objCharts.dashboardSources.config = chart_config;
		$scope.objCharts.dashboardSources.chart = new Highcharts.Chart(chart_config);
	};
	
	function initDashboardReferencesChart() {
		var chart_config = angular.copy($scope.objChartConfigTemplate);
		
		//set target
		chart_config.chart.renderTo = 'dashboardReferences';
		chart_config.title.text = 'Reference Breakdown';
		
		$scope.objCharts.dashboardReferences.config = chart_config;
		$scope.objCharts.dashboardReferences.chart = new Highcharts.Chart(chart_config);
	};
	
	function initDashboardUsersChart() {
		var chart_config = angular.copy($scope.objChartConfigTemplate);
		
		//set target
		chart_config.chart.renderTo = 'dashboardUserHistory';
		chart_config.title.text = 'User History';
		
		$scope.objCharts.dashboardUsers.config = chart_config;
		$scope.objCharts.dashboardUsers.chart = new Highcharts.Chart(chart_config);
	};
	
	function initDashboardStatusesChart() {
		var chart_config = angular.copy($scope.objChartConfigTemplate);
		
		//set target
		chart_config.chart.renderTo = 'dashboardStatuses';
		chart_config.title.text = 'Status History';
		
		$scope.objCharts.dashboardStatuses.config = chart_config;
		$scope.objCharts.dashboardStatuses.chart = new Highcharts.Chart(chart_config);
	};
	
	function initDashboardUnsubscribesChart() {
		var chart_config = angular.copy($scope.objChartConfigTemplate);
		
		//set target
		chart_config.chart.renderTo = 'dashboardUnsubscribes';
		chart_config.title.text = 'Unsubscribes';
		
		$scope.objCharts.dashboardUnsubscribes.config = chart_config;
		$scope.objCharts.dashboardUnsubscribes.chart = new Highcharts.Chart(chart_config);
	};
	
	function initDashboardDBGrowthChart() {
		var chart_config = angular.copy($scope.objChartConfigTemplate);
		
		//set target
		chart_config.chart.renderTo = 'dashboardDBGrowth';
		chart_config.title.text = 'Profile Growth';
		
		$scope.objCharts.dashboardDBGrowth.config = chart_config;
		$scope.objCharts.dashboardDBGrowth.chart = new Highcharts.Chart(chart_config);
	};
	
	function loadChartSourceData(objChart) {
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
	
	function loadChartReferenceData(objChart) {
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
						col_text: reference_series_label + ': ' + objReferenceData.label,
						drilldown: true,
						drilldown_series_name: reference_series_label,
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
	};//end function
	
	function loadChartStatusData(objChart) {
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
	};//end function
	
	function loadChartDBGrowthData(ObjChart) {
		var arr_data = Array();
		angular.forEach($scope.objChartRawData.profile_growth, function (objData, i) {
			angular.forEach(objData.data_temp, function(objTData, ii) {
				var obj = {
						name: objTData.label,
						y: objTData.value,
						x: objTData.time,
						//color: arr_colors[ic],
						//col_text: 'Status: ' + objStatusData.label
				};
				arr_data.push(obj);
			});
		});
		
		return arr_data;
	}; //end function
	
	function loadChartDBUnsubscribeData(objChart) {
		var arr_data = Array();
		angular.forEach($scope.objChartRawData.contacts_unsubscribed, function (objData, i) {
			angular.forEach(objData.data_temp, function(objTData, ii) {
				var obj = {
						name: objTData.label,
						y: objTData.value,
						x: objTData.time,
						//color: arr_colors[ic],
						//col_text: 'Status: ' + objStatusData.label
				};
				arr_data.push(obj);
			});
		});
		
		return arr_data;
	}; //end function	
}]);