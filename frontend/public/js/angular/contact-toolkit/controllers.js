'use strict';

var contactToolkitControllers = angular.module('contactToolkitControllers', []);

/**
 * Main page controller
 * @param $scope
 * @param $route
 * @param $routeParams
 * @param $window
 * @param ContactToolkitPageService
 * @param promiseTracker
 * @param formlyVersion
 */
contactToolkitControllers.controller('HomeCtrl', ['$scope', '$route', '$routeParams', '$window', 'ContactToolkitPageService', 'promiseTracker', function HomeCtrl($scope, $route, $routeParams, $window, ContactToolkitPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.objPageConfig = globalPageConfig();
	$scope.objRecords = [];
	
	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();

}]);

/**
 * Contact Data Controller
 * Manage items such as source, reference, users and activity for a contact
 * @param $scope
 * @param $route
 * @param $routeParams
 * @param $window
 * @param ContactToolkitPageService
 * @param ContactsPageService
 * @param promiseTracker
 * @param formlyVersion
 */
contactToolkitControllers.controller('ContactDataCtrl', ['$scope', '$rootScope', '$route', '$routeParams', '$window', 'ContactToolkitPageService', 'ContactsPageService', 'promiseTracker', function ContactDataCtrl($scope, $rootScope, $route, $routeParams, $window, ContactToolkitPageService, ContactsPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.objPageConfig = globalPageConfig();
	$scope.cid = global_contact_id;
	$scope.contact_unsubscribed = global_contact_unsubscribed;
	$scope.objContactData = [];
	
	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	//vars dealing with contact statistics
	$scope.objContactChartData = [];
	$scope.objContactJourneyStatsChart = false;
	$scope.objContactCommHistoryStatsChart = false;
	$scope.objContactFormsCompletedStatsChart = false;
	$scope.chart_loading_image = '';
	$scope.show_contact_journey_stats = false;
	$scope.show_contact_journey_stats_table = false;
	$scope.show_contact_comms_stats = false;
	$scope.show_contact_comms_stats_table = false;
	$scope.show_contact_forms_completed_stats = false;
	$scope.show_contact_forms_completed_stats_table = false;
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	
	$scope.formObjects = {
			users: false,
			sources: false,
			references: false,
			statuses: false,
	};
	
	//set model used by forms
	$scope.contactModel = {};
	
	//create form
	$scope.vm = this;
	
	$scope.loadContactData = function () {return loadContactData();};
	function loadContactData() {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.pageContent = global_wait_image;
		
		//request contact data
		var objRequest = {
			acrq: 'load-contact',
			cid: $scope.cid
		};
		ContactsPageService.get(objRequest, 
			function success(response) {
				logToConsole(response);
				$scope.pageContent = '';
				
				angular.forEach(response.objData, function (value, key) {
					$scope.objContactData[key] = value;
				});
				
				//set data for contact model
				$scope.contactModel.user_id = $scope.objContactData.user_id;
				$scope.contactModel.reg_status_id = $scope.objContactData.reg_status_id;
				$scope.contactModel.source = $scope.objContactData.source;
				$scope.contactModel.reference = $scope.objContactData.reference;
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
	}; //end function
	
	$scope.refreshData = function (flag) {
		return loadContactData();
	};
	
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
			doCreateSlidePanel(status);
		} else {
			doRemoveSlidePanel(status);
		}//end if
	}; //end function
	
	$scope.updateContactData = function (data) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		//toggle panel
		$scope.togglePanel(data.panel, true);
		
		//set form statuses
		$scope.contactMetaDataFormSourceReady = false;
		$scope.contactMetaDataFormReferenceReady = false;
		$scope.contactMetaDataFormUserReady = false;
		$scope.contactMetaDataFormStatusReady = false;
		
		//load form to panel...
		switch (data.item)
		{
			case 'source':
				$scope.panel_heading = 'Update Source for Contact';
				if ($scope.formObjects.sources == false)
				{
					//request users
					var $promise = ContactsPageService.get({acrq: 'load-sources'}, 
						function success(response) {
							logToConsole(response);
							$scope.formObjects.sources = Array();
							angular.forEach(response.objData, function (v, id) {
								$scope.formObjects.sources.push({'id': v, 'value': v})
							});
						},
						function error(errorResponse) {
							logToConsole(errorResponse);
							
						}
					);
					
					// Track the request and show its progress to the user.
					$scope.progress.addPromise($promise);
				}//end if
				
				$scope.contactMetaDataFormSourceReady = true;
				break;
			
			case 'reference':
				$scope.panel_heading = 'Update Reference for Contact';
				if ($scope.formObjects.references == false)
				{
					//request users
					var $promise = ContactsPageService.get({acrq: 'load-references'}, 
						function success(response) {
							logToConsole(response);
							$scope.formObjects.references = Array();
							angular.forEach(response.objData, function (v, id) {
								$scope.formObjects.references.push({'id': v, 'value': v})
							});
						},
						function error(errorResponse) {
							logToConsole(errorResponse);
							doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
						}
					);
					
					// Track the request and show its progress to the user.
					$scope.progress.addPromise($promise);
				}//end if
				
				$scope.contactMetaDataFormReferenceReady = true;
				break;
				
			case 'status':
				$scope.panel_heading = 'Change Status for Contact';
				if ($scope.formObjects.statuses == false)
				{
					//request users
					var $promise = ContactsPageService.get({acrq: 'load-statuses'}, 
						function success(response) {
							logToConsole(response);
							$scope.formObjects.statuses = Array();
							angular.forEach(response.objData, function (v, id) {
								$scope.formObjects.statuses.push({'id': v.id, 'value': v.status})
							});
						},
						function error(errorResponse) {
							logToConsole(errorResponse);
							doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
						}
					);
					
					// Track the request and show its progress to the user.
					$scope.progress.addPromise($promise);
				}//end if
				
				$scope.contactMetaDataFormStatusReady = true;
				break;
				
			case 'user':
				$scope.panel_heading = 'Change User for Contact';
				if ($scope.formObjects.users == false)
				{
					//request users
					var $promise = ContactsPageService.get({acrq: 'load-users'}, 
						function success(response) {
							logToConsole(response);
							$scope.formObjects.users = Array();
							angular.forEach(response.objData, function (v, id) {
								$scope.formObjects.users.push({'id': id, 'value': v})
							});
						},
						function error(errorResponse) {
							logToConsole(errorResponse);
							doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
						}
					);
					
					// Track the request and show its progress to the user.
					$scope.progress.addPromise($promise);
				}//end if
				
				$scope.contactMetaDataFormUserReady = true;
				break;
		}//end switch
		
		$scope.form_container_message = '';
		$scope.contactMetaDataFormReady = true;
	};
	
	$scope.formSubmit = function (data) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		data.acrq = 'update-user-meta-data';
		data.cid = $scope.cid;

		var $promise = ContactsPageService.post(data, 
				function success(response) {
					logToConsole(response);

					//refresh data
					$scope.loadContactData();
					doMessageAlert('Changes saved', '<p>Requested changes have been saved</p>');
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
			// Track the request and show its progress to the user.
			$scope.progress.addPromise($promise);
			
			//toggle the panel
			$scope.togglePanel('updateContactMetaDataPanel', false);
			
			//set form statuses
			$scope.contactMetaDataFormSourceReady = false;
			$scope.contactMetaDataFormReferenceReady = false;
			$scope.contactMetaDataFormUserReady = false;
			$scope.contactMetaDataFormStatusReady = false;
	}; //end function	
	
	/**
	 * Contact Statistics section
	 */
	$scope.loadContactStatistics = function (section) {return loadContactStatistics(section);};
	function loadContactStatistics(section) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.chart_loading_image = global_wait_image;
		var objRequest = {
			acrq: 'load-contact-statistics',
			cid: $scope.cid,
			callback: section,
		};

		ContactsPageService.get(objRequest, 
			function success(response) {
				logToConsole(response);

				//save data
				$scope.objContactChartData[section] = new Array();
				
				switch (section)
				{
					case 'loadJourneyDataStats':
						angular.forEach(response.objData, function (objRecord, i) {
							objRecord.next_comm = parseInt(objRecord.next_comm);
							$scope.objContactChartData[section].push(objRecord);
						});
						break;
						
					default:
						$scope.objContactChartData[section] = response.objData;
						break;
				}//end switch

				
				$scope.$emit('contactStatsData:loaded', objRequest);
				$scope.chart_loading_image = '';
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				$scope.chart_loading_image = '';
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
	};	
	
	//listen for contact statistics events
	$scope.$on('contactStatsData:loaded', function (event, data) {
		switch (data.callback) 
		{
			case 'loadJourneyDataStats':
				$scope.show_contact_journey_stats = true;
				setTimeout(function() {chartBuildJourneysChart();}, 2000);
				break;
			
			case 'loadCommsDataStats':
				$scope.show_contact_comms_stats = true;
				setTimeout(function() {chartBuildCommsHistoryChart();}, 2000);
				break;
				
			case 'loadFormsCompletedStats':
				$scope.show_contact_forms_completed_stats = true;
				setTimeout(function() {chartBuildFormsCompletedChart();}, 2000);
				break;
		}//end switch
	});
	
	//build contact journey statistics
	$scope.chartBuildJourneysChart = function (options) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var chart = chartBuildJourneysChart(options);
		$scope.objContactJourneyStatsChart = false;
		$scope.objContactJourneyStatsChart = chart;
	};
	
	function chartBuildJourneysChart(options) {
		//load chart
		var chart_options = {
		        data: {
		            table: 'contactDataJourneyStatsCollection',
		            switchRowsAndColumns: true
		        },
		        chart: {
		        	renderTo: 'contactDataJourneyStatsChart',
		            type: 'column'
		        },
		        noData: {
					useHTML: true
		        },
		        title: {
		            text: 'Journey Statistics',
		            style: {
		                display: 'none'
		            }
		        },
		        yAxis: {
		  			      allowDecimals: false,
		  			      title: {
		  			                text: 'Next Episode'
		  			            }
		  			    },
		        tooltip: {
		            formatter: function () {
		                return 'Episode <b>' + this.point.y + '</b> is due for the <b>' + this.point.name + '</b> journey';
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
		};
		
		if (typeof options != 'undefined')
		{
			if (typeof options.chart_type != 'undefined')
			{
				switch (options.chart_type)
				{
					case 'line':
					case 'spline':
					case 'area':
						chart_options.data.switchRowsAndColumns = false;
						chart_options.chart.type = options.chart_type;
						break;
						
					default:
						chart_options.chart.type = options.chart_type;
						break;
				}//end switch
			}//end if
		}//end if
		
		var chart = new Highcharts.Chart(chart_options);
		return chart;
	}//end function
	
	
	$scope.chartBuildCommsHistoryChart = function (options) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var chart = chartBuildCommsHistoryChart(options);
		$scope.objContactCommHistoryStatsChart = false;
		$scope.objContactCommHistoryStatsChart = chart;
	};
	
	function chartBuildCommsHistoryChart(options)
	{
		//load chart
		var chart_options = {
		        data: {
		            table: 'contactDataCommHistoryStatsCollection'
		        },
		        chart: {
		        	renderTo: 'contactDataCommHistoryStatsChart',
		            type: 'column'
		        },
		        title: {
		            text: 'Communication Statistics',
		            style: {
		                display: 'none'
		            }
		        },
		        yAxis: {
		  			      allowDecimals: false,
		  			      title: {
		  			                text: 'Next Episode'
		  			            }
		  			    },
		        tooltip: {
		            formatter: function () {
		                return 'Episode <b>' + this.point.y + '</b> is due for the <b>' + this.point.name + '</b> journey';
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
		};
		
		if (typeof options != 'undefined')
		{
			if (typeof options.chart_type != 'undefined')
			{
				switch (options.chart_type)
				{
					case 'line':
					case 'spline':
					case 'area':
						chart_options.data.switchRowsAndColumns = false;
						chart_options.chart.type = options.chart_type;
						break;
						
					default:
						chart_options.chart.type = options.chart_type;
						break;
				}//end switch
			}//end if
		}//end if
		
		var chart = new Highcharts.Chart(chart_options);
		return chart;		
	};//end function
	
	$scope.chartBuildFormsCompletedChart = function (options) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var chart = chartBuildFormsCompletedChart(options);
		$scope.objContactFormsCompletedStatsChart = false;
		$scope.objContactFormsCompletedStatsChart = chart;
		angular.element('#toggleFormsCompletedButton').focus();
	};
	function chartBuildFormsCompletedChart(options) {
		//load chart
		var chart_options = {
		        data: {
		            table: 'contactDataFormsCompletedCollection',
		            switchRowsAndColumns: false,
		        },
		        chart: {
		        	renderTo: 'contactDataFormsCompletedStatsChart',
		            type: 'column'
		        },
		        title: {
		            text: 'Forms Completed Statistics',
		            style: {
		                display: 'none'
		            }
		        },
		        yAxis: {
		  			      allowDecimals: false,
		  			      title: {
		  			                text: 'Forms Completed'
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
		};
		
		if (typeof options != 'undefined')
		{
			if (typeof options.chart_type != 'undefined')
			{
				switch (options.chart_type)
				{
					case 'line':
					case 'spline':
					case 'area':
						chart_options.data.switchRowsAndColumns = false;
						chart_options.chart.type = options.chart_type;
						break;
						
					default:
						chart_options.chart.type = options.chart_type;
						break;
				}//end switch
			}//end if
		}//end if
		
		var chart = new Highcharts.Chart(chart_options);
		return chart;		
	};//end function
}]);

/**
 * Comments controller
 * @param $scope
 * @param $route
 * @param $routeParams
 * @param $window
 * @param ContactToolkitPageService
 * @param promiseTracker
 * @param formlyVersion
 */
contactToolkitControllers.controller('CommentsCtrl', ['$scope', '$route', '$routeParams', '$window', 'ContactToolkitPageService', 'promiseTracker', function CommentsCtrl($scope, $route, $routeParams, $window, ContactToolkitPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.objPageConfig = globalPageConfig();
	$scope.objPageConfig.pageTitle = '<h5>Comments</h5>';
	$scope.objRecords = [];
	$scope.contact_unsubscribed = global_contact_unsubscribed;
	$scope.cid = global_contact_id;
	$scope.createCommentPanel = false;
	
	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	$scope.vm = this;
	$scope.vm.comment = {};
	$scope.vm.commentFields = getCommentFormFields();
	$scope.vm.submit = function (form) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.createRecord(form, $scope.vm.comment);
	};
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	  
	//pagination details
	$scope.previousPage = 1;
	$scope.currentPage = 1;
	$scope.pageSize = 20;
	
	//handle clicks on paginator
	$scope.pageChangeHandler = function (page) {
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
		
		ContactToolkitPageService.getComments({acrq: 'list-comments', cid: $scope.cid, 'qp_limit': $scope.objPageConfig.pagination.qp_limit, 'qp_start': start_number}, 
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
		} else {
			doRemoveSlidePanel(status);
		}//end if
	}; //end function
	
	/**
	 * Refresh data
	 */
	$scope.refreshData = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.pageContent = global_wait_image;
		$scope.createCommentPanel = false;
		$scope.objRecords = [];
		$scope.loadRecords();
	}; //end function
	
	/**
	 * Load data list
	 */
	$scope.loadRecords = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		ContactToolkitPageService.getComments({acrq: 'list-comments', cid: $scope.cid, qp_limit: $scope.pageSize}, 
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
	
	/**
	 * Create comment
	 */
	$scope.createRecord = function (form, model) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var $promise = ContactToolkitPageService.createComment({acrq: 'create-comment', cid: $scope.cid, comment: model.comment}, 
				function success(response) {
					logToConsole(response);
					
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to complete request', '<p>' + response.response + '</p>');
						return false;
					}//end if
					
					var objComment = {
							comment: model.comment,
							id: $scope.cid,
							users_uname: 'Logged in user',
							datetime_created: 'Just now'
					};
					
					//append comment to the list
					$scope.objRecords.push(objComment);
					
					//clear form model
					$scope.vm.comment = {};
					
					$scope.togglePanel('createCommentPanel', false);
					$scope.createCommentPanel = false;
					$scope.refreshData();
					doMessageAlert('Changes saved', '<p>The requested changes have been saved</p>');
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
		  
		// Track the request and show its progress to the user.
		$scope.progress.addPromise($promise);
	};
	
	/**
	 * Delete comment
	 */
	$scope.deleteRecord = function (comment_id) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var r = confirm("Are you sure you want to remove this comment?");
		if (r == true) {
			ContactToolkitPageService.deleteComment({acrq: 'delete-comment', cid: $scope.cid, id: comment_id}, 
					function success(response) {
						logToConsole(response);
						
						doInfoMessage('Operation complete', '<p>The requested operation completed successfully</p>');
					},
					function error(errorResponse) {
						logToConsole(errorResponse);
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					}
				);
		}//end if
	}; //end function
	
	function getCommentFormFields() {
		var objFields = [
		         {
		        	 key: 'comment',
		        	 type: 'textarea',
		             templateOptions: {
		                 type: 'textarea',
		                 label: 'Comment',
		                 placeholder: 'Enter comment',
		                 description: 'Enter comment contents'
		               },
			           validation: {
			             show: true
			           },
			           expressionProperties: {
			             "templateOptions.required": "true"
			           }
		         }
		];
		
		return objFields;
	}//end function
	
}]);

/**
 * Forms Completed
 * @param $scope
 * @param $route
 * @param $routeParams
 * @param $window
 * @param ContactToolkitPageService
 * @param promiseTracker
 * @param formlyVersion
 */
contactToolkitControllers.controller('FormsCompletedCtrl', ['$scope', '$route', '$routeParams', '$window', 'ContactToolkitPageService', 'promiseTracker', function FormsCompletedCtrl($scope, $route, $routeParams, $window, ContactToolkitPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.objPageConfig = globalPageConfig();
	$scope.objPageConfig.pageTitle = '<h5>Forms Completed</h5>';
	$scope.objRecords = [];
	$scope.contact_unsubscribed = global_contact_unsubscribed;
	$scope.cid = global_contact_id;
	$scope.cid_encoded = global_contact_id_encoded;

	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	  
	//pagination details
	$scope.previousPage = 1;
	$scope.currentPage = 1;
	$scope.pageSize = 20;
	
	//handle clicks on paginator
	$scope.pageChangeHandler = function (page) {
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
		
		ContactToolkitPageService.getFormsCompleted({acrq: 'list-forms-completed', cid: $scope.cid, 'qp_limit': $scope.objPageConfig.pagination.qp_limit, 'qp_start': start_number}, 
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
			if (panel == 'completeFormPanel' && typeof $scope.objAvailableForms == "undefined")
			{
				$scope.objAvailableForms = Array();
				$scope.loadAvailableForms();
			}//end if
			
			doCreateSlidePanel(status);
		} else {
			doRemoveSlidePanel(status);
		}//end if
	}; //end function
	
	/**
	 * Refresh data
	 */
	$scope.refreshData = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.pageContent = global_wait_image;
		$scope.objRecords = [];
		$scope.loadRecords();
	}; //end function
	
	/**
	 * Load data list
	 */
	$scope.loadRecords = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		ContactToolkitPageService.getFormsCompleted({acrq: 'list-forms-completed', cid: $scope.cid, qp_limit: $scope.pageSize}, 
			function success(response) {
				logToConsole(response);
				$scope.pageContent = '';
				
				angular.forEach(response.objData, function (objRecord, i) {
					if (objRecord.id > -1)
					{
						$scope.objRecords.push(objRecord);
						$scope.cid_encoded = objRecord.reg_id_encoded;
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
	
	$scope.loadAvailableForms = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var $promise = ContactToolkitPageService.getFormsCompleted({acrq: 'list-web-forms'}, 
				function success(response) {
					logToConsole(response);
					$scope.pageContent = '';
					
					angular.forEach(response.objData, function (objRecord, i) {
						if (objRecord.id > -1)
						{
							$scope.objAvailableForms.push(objRecord);
						}//end foreach
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
		
		// Track the request and show its progress to the user.
		$scope.progress.addPromise($promise);
	};
	
	$scope.injectFormHyperLink = function (objRecord) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		switch (objRecord.form_behaviour)
		{
			case '__web':
				var str = '<a href="/forms/bf/' + objRecord.form_id + '/' + objRecord.reg_id_encoded + '" title="View form" target="_blank">' + objRecord.form + '</a>';
				return str;
				break;
			
			default:
				return objRecord.form;
				break;
		}//end switch
	};
	
	$scope.generateFormLink = function (objForm) {
		return '<a href="/forms/bf/' + objForm.id + '/' + $scope.cid_encoded + '" title="Complete ' + objForm.form + ' form" target="_blank">' + objForm.form + '</a>';
	};
}]);

/**
 * Journeys Controller
 * @param $scope
 * @param $route
 * @param $routeParams
 * @param $window
 * @param ContactToolkitPageService
 * @param promiseTracker
 * @param formlyVersion
 */
contactToolkitControllers.controller('JourneysCtrl', ['$scope', '$route', '$routeParams', '$window', 'ContactToolkitPageService', 'promiseTracker', function JourneysCtrl($scope, $route, $routeParams, $window, ContactToolkitPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.objPageConfig = globalPageConfig();
	$scope.objPageConfig.pageTitle = '<h5>Contact Journeys</h5>';
	$scope.objRecords = [];
	$scope.cid = global_contact_id;
	$scope.contact_unsubscribed = global_contact_unsubscribed;
	$scope.cid_encoded = global_contact_id_encoded;

	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	//vars dealing with contact journey and comms history
	$scope.objContactJourneyHistory = {};
	$scope.listJourneysAvailable = false;
	$scope.objJourneysStartAvailable = [];
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	  
	//pagination details
	$scope.previousPage = 1;
	$scope.currentPage = 1;
	$scope.pageSize = 20;
	
	//handle clicks on paginator
	$scope.pageChangeHandler = function (page) {
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
		
		ContactToolkitPageService.getContactJourneys({acrq: 'list-contact-journeys', cid: $scope.cid, 'qp_limit': $scope.objPageConfig.pagination.qp_limit, 'qp_start': start_number}, 
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
		} else {
			doRemoveSlidePanel(status);
		}//end if
	}; //end function
	
	/**
	 * Refresh data
	 */
	$scope.refreshData = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.pageContent = global_wait_image;
		$scope.objRecords = [];
		$scope.loadRecords();
	}; //end function
	
	/**
	 * Load data list
	 */
	$scope.loadRecords = function () {return loadRecords();};
	function loadRecords() {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		ContactToolkitPageService.getContactJourneys({acrq: 'list-contact-journeys', cid: $scope.cid, qp_limit: $scope.pageSize}, 
			function success(response) {
				logToConsole(response);
				$scope.pageContent = '';
				$scope.objRecords = new Array();
				angular.forEach(response.objData, function (objRecord, i) {
					if (objRecord.id > -1)
					{
						$scope.objRecords.push(objRecord);
						$scope.cid_encoded = objRecord.reg_id_encoded;
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
	
	/**
	 * Start a journey for a contact
	 */
	$scope.contactStartJourney = function (journey_id) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		ContactToolkitPageService.startContactJourney({acrq: 'start-contact-journey', cid: $scope.cid, journey_id: journey_id}, 
				function success(response) {
					logToConsole(response);

					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Failed to start Journey', '<p>' + response.response + '</p>');
						return false;
					}//end if
					
					doMessageAlert('Contact Journey', '<p>Journey has been started.</p>');
					
					if ($scope.listJourneysAvailable == true)
					{
						$scope.listJourneysAvailable = false;
						//reload records
						loadRecords();
					}//end if
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred, please check if journey has been started. If not, please try again.</p>');
				}
			);	
	}; //end function
	
	/**
	 * Restart journey for a contact
	 */
	$scope.contactRestartJourney = function (objRecord) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if (confirm('Are you sure you want to restart this journey?') == true)
		{
			ContactToolkitPageService.restartContactJourney({acrq: 'restart-contact-journey', cid: objRecord.reg_id, reg_comm_id: objRecord.reg_comm_id}, 
					function success(response) {
						logToConsole(response);
						if (response.result == false)
						{
							doErrorAlert('Failed to restart Journey', '<p>' + response.error + '</p>');
							return false;
						}//end if
						
						doMessageAlert('Contact Journey', '<p>Journey has been restarted.</p>');
						
						//reload records
						loadRecords();
					},
					function error(errorResponse) {
						logToConsole(errorResponse);
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please check of journey has been restarted, if not, please try again.</p>');
					}
				);	
		}//end if
	}; //end function
	
	/**
	 * Stop a journey for a contact
	 */
	$scope.contactStopJourney = function (objRecord) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		ContactToolkitPageService.stopContactJourney({acrq: 'stop-contact-journey', cid: objRecord.reg_id, reg_comm_id: objRecord.reg_comm_id}, 
				function success(response) {
					logToConsole(response);

					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Failed to stop Journey', '<p>' + response.response + '</p>');
						return false;
					}//end if
					
					doMessageAlert('Contact Journey', '<p>Journey has been stopped.</p>');
					
					//reload records
					loadRecords();
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please check if journey has been stopped. If not, please try again.</p>');
				}
			);		
	}; //end function
	
	/**
	 * Load journey history for contact
	 */
	$scope.loadJourneyRecords = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var objRequest = {
			cid: $routeParams.contact_id,
			journey_id: $routeParams.journey_id,
			acrq: 'contact-journey-history',
		};
		
		$scope.objContactJourneyHistory = {};
		ContactToolkitPageService.getContactJourneys(objRequest, 
				function success(response) {
					logToConsole(response);
					$scope.pageContent = '';
					$scope.objContactJourneyHistory = response.objData;
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
	}; //end function
	
	//load journeys available to start for contact
	$scope.listJourneysAvailableToStart = function(flag) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.listJourneysAvailable = flag;

		if ($scope.objJourneysStartAvailable.length == 0 && $scope.listJourneysAvailable == true)
		{
			$scope.journeySectionContent = $scope.global_wait_image;
			
			var objRequest = {
					cid: $routeParams.contact_id,
					acrq: 'list-journeys-available',
				};
				
				$scope.objContactJourneyHistory = {};
				ContactToolkitPageService.getContactJourneys(objRequest, 
						function success(response) {
							logToConsole(response);
							$scope.journeySectionContent = '';
							
							angular.forEach(response.objData.objJourneys, function (objJourney, i) {
								if (typeof objJourney.id != 'undefined')
								{
									if (objJourney.active == 1)
									{
										$scope.objJourneysStartAvailable.push(objJourney);
									}//end if
								}//end if
							});
						},
						function error(errorResponse) {
							logToConsole(errorResponse);
							doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
						}
					);
		}//end if
	};
}]);

/**
 * Statuses Controller
 * @param $scope
 * @param $route
 * @param $routeParams
 * @param $window
 * @param ContactToolkitPageService
 * @param promiseTracker
 * @param formlyVersion
 */
contactToolkitControllers.controller('StatusesCtrl', ['$scope', '$route', '$routeParams', '$window', 'ContactToolkitPageService', 'promiseTracker', function StatusesCtrl($scope, $route, $routeParams, $window, ContactToolkitPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.objPageConfig = globalPageConfig();
	$scope.objPageConfig.pageTitle = '<h5>Contact Status</h5>';
	$scope.objRecords = [];
	$scope.contact_unsubscribed = global_contact_unsubscribed;
	$scope.cid = global_contact_id;
	$scope.cid_encoded = global_contact_id_encoded;

	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	  
	$scope.vm = {};
	$scope.vm.status = {};
	$scope.vm.objStatusFieldOptions = false;
	$scope.vm.statusFields = getStatusFormFields();
	$scope.vm.submit = function (form) {
		$scope.createRecord(form, $scope.vm.status);
	};
	
	//pagination details
	$scope.previousPage = 1;
	$scope.currentPage = 1;
	$scope.pageSize = 20;
	
	//handle clicks on paginator
	$scope.pageChangeHandler = function (page) {
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
		
		ContactToolkitPageService.getContactStatuses({acrq: 'list-contact-statuses', cid: $scope.cid, 'qp_limit': $scope.objPageConfig.pagination.qp_limit, 'qp_start': start_number}, 
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
		} else {
			doRemoveSlidePanel(status);
		}//end if
	}; //end function
	
	/**
	 * Refresh data
	 */
	$scope.refreshData = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.pageContent = global_wait_image;
		$scope.objRecords = [];
		$scope.loadRecords();
	}; //end function
	
	/**
	 * Load data list
	 */
	$scope.loadRecords = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		ContactToolkitPageService.getContactStatuses({acrq: 'list-contact-statuses', cid: $scope.cid, qp_limit: $scope.pageSize}, 
			function success(response) {
				logToConsole(response);
				$scope.pageContent = '';
				
				angular.forEach(response.objData, function (objRecord, i) {
					if (objRecord.id > -1)
					{
						$scope.objRecords.push(objRecord);
						$scope.cid_encoded = objRecord.reg_id_encoded;
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
	
	$scope.createRecord = function (form, model) {
		/**
		 * Make sure user is logged in	logToConsole(response);
		 */
		userIsLoggedin();
		
		var $promise = ContactToolkitPageService.createComment({acrq: 'update-contact-status', cid: $scope.cid, comment: model.comment, status: model.status}, 
				function success(response) {
					logToConsole(response);
					
					//clear form model
					$scope.vm.status = {};

					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Requested operation failed', '<p>' + response.response + ' (' + response.message + ')</p>');
						return false;
					}//end if
					
					doMessageAlert('Requested changes saved', '<p>Status has been updated</p>');
					
					//close form
					$scope.togglePanel('updateStatusPanel', false);
					
					//trigger update
					$scope.refreshData();
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
		  
		// Track the request and show its progress to the user.
		$scope.progress.addPromise($promise);
	};
	
	$scope.unsubscribeContact = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		return unsubscribeContact();
	};
	
	function unsubscribeContact() {
		if (confirm('Are you sure?') == true)
		{
			var $promise = ContactToolkitPageService.post({acrq: 'unsubscribe-contact', cid: $scope.cid}, 
					function success(response) {
						logToConsole(response);
						
						if (typeof response.error != 'undefined' && response.error == 1)
						{
							doErrorAlert('Unsubscribe error', '<p>Contact operation failed, contact could not be updated as unsubscribed</p>');
							return false;
						}//end if
						
						doMessageAlert('Contact subscription', '<p>Contact has been unsubscribed successfully</p>');
						
						//trigger refresh
						$scope.refreshData();
					},
					function error(errorResponse) {
						logToConsole(errorResponse);
						
						//display error...
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please confirm if contact has been unsubscribed. If not, please try again.</p>');
					}
				);
			  
			// Track the request and show its progress to the user.
			$scope.progress.addPromise($promise);
		}//end if
	};
	
	function getStatusFormFields() {
		var objFields = [
		         {
		        	key: 'status',
		        	type: 'select',
		        	//wrapper: 'loading',
		        	templateOptions: {
		        		type: 'select',
		        		label: 'Status',
		        		description: 'Select Status',
		        		required: true,
		        		placeholder: 'Select new status',
		        		options: [],
		        		valueProp: 'statusId',
		        		labelProp: 'statusValue'
		        	},
		           validation: {
			             show: true
			       },
			       expressionProperties: {
			             "templateOptions.required": "true"
			       },
			       controller: function($scope) {
			           $scope.to.loading = jQuery.get('/front/contact/toolkit/ajax-request/0?acrq=list-available-statuses').then(function(response) {
							var objStatusFieldOptions = Array();
							angular.forEach(response.objData, function (objStatus, i) {
								if (objStatus.id > -1 && typeof objStatus.status !== "undefined")
								{
									objStatusFieldOptions.push({"statusId": parseInt(objStatus.id), "statusValue": objStatus.status});
								}//end if
							});		
			        	   $scope.to.options = objStatusFieldOptions;
			           });
			       }
		         },
		         {
		        	 key: 'comment',
		        	 type: 'textarea',
		             templateOptions: {
		                 type: 'textarea',
		                 label: 'Comment',
		                 placeholder: 'Enter comment',
		                 description: 'Add a comment (optional)'
		               }
		         }
		];
		
		return objFields;
	}//end function
}]);

/**
 * Trackers Controller
 * @param $scope
 * @param $route
 * @param $routeParams
 * @param $window
 * @param ContactToolkitPageService
 * @param promiseTracker
 * @param formlyVersion
 */
contactToolkitControllers.controller('TrackersCtrl', ['$scope', '$route', '$routeParams', '$window', 'ContactToolkitPageService', 'promiseTracker', function TrackersCtrl($scope, $route, $routeParams, $window, ContactToolkitPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.objPageConfig = globalPageConfig();
	$scope.objPageConfig.pageTitle = '<h5>Trackers</h5>';
	$scope.objRecords = [];
	$scope.load_tracker_id = false;
	$scope.tracker_load_messages = false;
	$scope.tracker_form_loaded = false;
	$scope.contact_unsubscribed = global_contact_unsubscribed;
	$scope.cid = global_contact_id;
	$scope.cid_encoded = global_contact_id_encoded;

	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	$scope.objTrackerForm = {
		model: {},
		fields: [],
		form: {},
		tracker_form_loaded: false,
		tracker_load_messages: '',
		tracker_form_id: '',
		submitForm: function () {
			//add any missing fields to the model where required
			angular.forEach($scope.objTrackerForm.fields, function (objField, i) {
				if (typeof $scope.objTrackerForm.model[objField.key] == 'undefined')
				{
					$scope.objTrackerForm.model[objField.key] = '';
				}//end if
			});
			
			//set action
			$scope.objTrackerForm.model.acrq = 'create-contact-tracker';
			$scope.objTrackerForm.model.fid = $scope.objTrackerForm.tracker_form_id;
			$scope.objTrackerForm.model.cid = $scope.cid;
			$scope.objTrackerForm.model.cid_encoded = $scope.cid_encoded;
			
			//submit data
			$promise = ContactToolkitPageService.post($scope.objTrackerForm.model, 
				function success(response) {
					logToConsole(response);
					
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						if (typeof response.form_messages != 'undefined')
						{
							handleFormlyFormValidationErrors($scope.objTrackerForm.fields, $scope.objTrackerForm.model, response.form_messages);	
							return false;
						}//end if
					}//end if
					
					if (typeof response.objData != 'undefined' && typeof response.objData.errors != 'undefined')
					{
						handleFormlyFormValidationErrors($scope.objTrackerForm.fields, $scope.objTrackerForm.model, response.objData.errors);	
						return false;
					}//end if	
					
					if (typeof response.objData == 'undefined')
					{
						doErrorAlert('Tracker could not be created', '<p>An unknown error has occurred. The Tracker could not be created for the contact. An invalid response has been received.</p>');
						return false;
					}//end if
					
					//display success message and close tracker panels
					$scope.togglePanel('completeTrackerPanel', false);
					$scope.togglePanel('createTrackerPanel', false);
					
					doMessageAlert('Contact Tracker', '<p>The tracker has been created for the contact.</p>');
					//refresh data
					$scope.refreshData(true);
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
			$scope.progress.addPromise($promise);
		},
	};
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	  
	//pagination details
	$scope.previousPage = 1;
	$scope.currentPage = 1;
	$scope.pageSize = 20;
	
	//handle clicks on paginator
	$scope.pageChangeHandler = function (page) {
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
		
		ContactToolkitPageService.getContactTrackers({acrq: 'list-contact-trackers', cid: $scope.cid, 'qp_limit': $scope.objPageConfig.pagination.qp_limit, 'qp_start': start_number}, 
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
					logToConsole(errorResponse);
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
		
		if (panel == 'completeTrackerPanel')
		{
			if (status == true) 
			{
				//mode forms list to below disabled layer
				angular.element('#createTrackerPanelContainer').css('z-index', '1037');
				
				//trigger loading of tacker form
				$scope.loadTrackerForm();
			} else {
				//restore form list layer
				angular.element('#createTrackerPanelContainer').css('z-index', '1038');
			}//end if
			
			return;
		}//end if
		
		if (status == true)
		{
			if (panel == 'createTrackerPanel' && typeof $scope.objAvailableForms == "undefined")
			{
				$scope.objAvailableForms = Array();
				$scope.loadAvailableForms();
			}//end if
			
			doCreateSlidePanel(status);
		} else {
			doRemoveSlidePanel(status);
		}//end if
	}; //end function
	
	/**
	 * Refresh data
	 */
	$scope.refreshData = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.pageContent = global_wait_image;
		$scope.objRecords = [];
		$scope.loadRecords();
	}; //end function
	
	/**
	 * Load data list
	 */
	$scope.loadRecords = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		ContactToolkitPageService.getContactTrackers({acrq: 'list-contact-trackers', cid: $scope.cid, qp_limit: $scope.pageSize}, 
			function success(response) {
				logToConsole(response);
				$scope.pageContent = '';
				
				angular.forEach(response.objData, function (objRecord, i) {
					if (objRecord != null && objRecord.id > -1)
					{
						$scope.objRecords.push(objRecord);
						$scope.cid_encoded = objRecord.reg_id_encoded;
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
	
	$scope.loadAvailableForms = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var $promise = ContactToolkitPageService.getFormsCompleted({acrq: 'list-tracker-forms'}, 
				function success(response) {
					logToConsole(response);
					$scope.pageContent = '';
					
					angular.forEach(response.objData, function (objRecord, i) {
						if (objRecord.id > -1)
						{
							$scope.objAvailableForms.push(objRecord);
						}//end foreach
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
		
		// Track the request and show its progress to the user.
		$scope.progress.addPromise($promise);
	};
	
	$scope.loadTrackerForm = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.objTrackerForm.tracker_form_loaded = false;
		$scope.objTrackerForm.tracker_load_messages = 'Requesting form...';

		$scope.getTrackerFormFields($scope.load_tracker_id);
	}; //end function
	
	$scope.getTrackerFormFields = function (form_id) {
		$scope.objTrackerForm.fields = Array();
		$scope.objTrackerForm.tracker_form_id = form_id;
		
		var $promise = ContactToolkitPageService.getContactTrackers({acrq: 'load-tracker-form', fid: form_id},
				function success(response) {
					logToConsole(response);
					
					$scope.tracker_load_messages = 'Generating form...';
					angular.forEach(response.objData, function (objField, i) {
						$scope.objTrackerForm.fields.push(objField);
					});
			
					$scope.objTrackerForm.tracker_form_loaded = true;
					$scope.tracker_load_messages = '';
				},
				function error(errorResponse) {
					logToConsole(response);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
		);	
		
		// Track the request and show its progress to the user.
		$scope.progress.addPromise($promise);
	}; //end function
	
	$scope.injectFormHyperLink = function (objRecord) {
		var str = '<a href="/front/trackers/' + objRecord.reg_id + '/edit/' + objRecord.id + '" title="View Tracker" target="_blank"><span class="glyphicon glyphicon-eye-open"></span></a>';
		return str;
	};
	
	$scope.generateFormLink = function (objForm) {
		return '<a href="" title="Create ' + objForm.form + ' tracker" ng-click="togglePanel(\'completeTrackerPanel\', true)">' + objForm.form + '</a>';
	};
	
	$scope.setLoadTrackerId = function (id) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.load_tracker_id = id;
	};//end function
}]);

/**
 * Tasks Controller
 * @param $scope
 * @param $route
 * @param $routeParams
 * @param $window
 * @param ContactToolkitPageService
 * @param promiseTracker
 * @param formlyVersion
 */
contactToolkitControllers.controller('TasksCtrl', ['$scope', '$route', '$routeParams', '$window', 'ContactToolkitPageService', 'promiseTracker', function TasksCtrl($scope, $route, $routeParams, $window, ContactToolkitPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.objPageConfig = globalPageConfig();
	$scope.objPageConfig.pageTitle = '<h5>To do items</h5>';
	$scope.objRecords = [];
	$scope.contact_unsubscribed = global_contact_unsubscribed;
	$scope.cid = global_contact_id;
	$scope.cid_encoded = global_contact_id_encoded;
	$scope.load_contact_data_indicator = false;
	$scope.load_admin_form_indicator = false;
	
	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	$scope.objTaskForm = {
			fields: [],
			model: {},
			form: this,
			submitForm: function () {
				$scope.load_admin_form_indicator = true;
				
				//make sure all fields are added to the model
				angular.forEach($scope.objTaskForm.fields, function (objField, i) {
					if (typeof $scope.objTaskForm.model[objField.key] == 'undefined')
					{
						$scope.objTaskForm.model[objField.key] = '';
					}//end if
				});
				
				//submit the data
				var objRequest = $scope.objTaskForm.model;
				objRequest.acrq = 'create-contact-task';
				objRequest.cid = $scope.cid;
				
				var $p = ContactToolkitPageService.post(objRequest, 
					function success(response) {
						logToConsole(response);
						
						$scope.load_admin_form_indicator = false;
						
						if (typeof response.objData != 'undefined')
						{
							//all good
							//close form and refresh list
							$scope.togglePanel('createItemFormPanel', false);
							$scope.loadRecords();
						}//end if
					},
					function error(errorResponse) {
						logToConsole(errorResponse);
						
						$scope.load_admin_form_indicator = false;
						if (typeof errorResponse.response != 'undefined')
						{
							doErrorAlert('Unable to complete request', '<p>' + errorResponse.response + '</p>');
							return false;
						}//end if
						
						doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
					}
				);
			},
			clearForm: function () {
				
			}
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
			switch(panel)
			{
				case 'createItemFormPanel':
					$scope.loadTaskAdminForm();
					break;
			}//end switch
			
			doCreateSlidePanel(status);
		} else {
			doRemoveSlidePanel(status);
		}//end if
	}; //end function
	
	$scope.loadRecords = function () {
		return loadRecords();
	};
	function loadRecords(objParams) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope.load_contact_data_indicator = true;
		var objRequest = {
				acrq: 'list-contact-tasks',
				cid: global_contact_id_encoded
		};
	
		if (typeof objParams == 'object')
		{
			angular.forEach(objParams, function (value, key) {
				objRequest[key] = value;
			});
		}//end if
		
		var $p = ContactToolkitPageService.get(objRequest, 
			function success(response) {
				logToConsole(response);
		
				//clear cached data
				$scope.objRecords = Array();
				angular.forEach(response.objData, function (objTask, i) {
					$scope.objRecords.push(objTask);
				});
				
				$scope.load_contact_data_indicator = false;
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				
				$scope.load_contact_data_indicator = false;
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
	}; //end function
	
	$scope.refreshData = function () {
		$scope.objRecords = Array();
		loadRecords();
	};
	
	$scope.filterUserItems = function () {
		$scope.objRecords = Array();
		loadRecords({filter_loggedin_user_items: 1});
	};
	
	$scope.loadTaskAdminForm = function () {
		return loadTaskAdminForm();
	};
	
	function loadTaskAdminForm() {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		//check if form has already been loaded
		if ($scope.objTaskForm.fields.length > 0)
		{
			//make sure model is cleared
			$scope.objTaskForm.model = {};
			return;
		}//end if
		
		$scope.load_admin_form_indicator = true;
		
		var objRequest = {
				acrq: 'load-task-admin-form',
				cid: $scope.cid_encoded
		};
		
		var $p = ContactToolkitPageService.get(objRequest, 
			function success(response) {
				logToConsole(response);
				
				angular.forEach(response.objData, function (objField, i) {
					switch (objField.key)
					{
						case 'datetime_reminder':
						case 'date_email_reminder':
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
						    
						case 'user_id':
							objField.templateOptions.required = true;
							break;
					}//end switch
					
					$scope.objTaskForm.fields.push(objField);
				});
				
				$scope.load_admin_form_indicator = false;
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				
				$scope.load_admin_form_indicator = false;
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
	};
	
	$scope.completeItem = function (objTask) {
		return completeItem(objTask);
	};
	function completeItem(objTask)
	{
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if (confirm('Are you sure this item is now completed?') != true)
		{
			return false;
		}//end if
		
		$scope.load_contact_data_indicator = true;
		
		objTask.acrq = 'complete-user-task';
		objTask.cid = $scope.cid;
		
		var $r = ContactToolkitPageService.post(objTask, 
			function success(response) {
				logToConsole(response);
				$scope.load_contact_data_indicator = false;
				
				if (typeof response.error == 'undefined')
				{
					objTask.complete = 1;
				} else {
					doErrorAlert('Update To Do Item Failed', '<p>' + response.response + '</p>');
				}//end if
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				$scope.load_contact_data_indicator = false;
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
	}//end function
	
	$scope.deleteItem = function (objTask) {
		return deleteItem(objTask);
	};
	function deleteItem(objTask)
	{
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		if (confirm('Are you sure you want to remove this item?') != true)
		{
			return false;
		}//end if
		
		$scope.load_contact_data_indicator = true;
		
		objTask.acrq = 'delete-user-task';
		objTask.cid = $scope.cid;
		
		var $r = ContactToolkitPageService.post(objTask, 
			function success(response) {
				logToConsole(response);
				$scope.load_contact_data_indicator = false;
				
				if (typeof response.error == 'undefined')
				{
					//remove task from list
					angular.forEach($scope.objRecords, function (objRemoveTask, i) {
						if (objRemoveTask.id == objTask.id)
						{
							$scope.objRecords.splice(i, 1);
						}//end if
					});
				} else {
					doErrorAlert('Failed to remove item', '<p>' + response.response + '</p>');
				}//end if
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				$scope.load_contact_data_indicator = false;
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
	}//end function	
}]);

/**
 * Journey History Controller
 * @param $scope
 * @param $route
 * @param $routeParams
 * @param $window
 * @param ContactToolkitPageService
 * @param promiseTracker
 * @param formlyVersion
 */
contactToolkitControllers.controller('JourneyHistoryCtrl', ['$scope', '$route', '$routeParams', '$window', 'ContactToolkitPageService', 'promiseTracker', function JourneyHistoryCtrl($scope, $route, $routeParams, $window, ContactToolkitPageService, promiseTracker, formlyVersion) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.objPageConfig = globalPageConfig();
	$scope.objPageConfig.pageTitle = '<h5>Journey History</h5>';
	$scope.objRecords = [];
	$scope.contact_unsubscribed = global_contact_unsubscribed;
	$scope.cid = global_contact_id;
	$scope.cid_encoded = global_contact_id_encoded;
	
	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	  
	//pagination details
	$scope.previousPage = 1;
	$scope.currentPage = 1;
	$scope.pageSize = 20;
	
	$scope.loadContactJourneyHistory = function (objRecord) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var $promise = ContactToolkitPageService.getContactJourneys({acrq: 'contact-journey-history', cid: objRecord.reg_id, reg_comm_id: objRecord.reg_comm_id}, 
				function success(response) {
					logToConsole(response);

				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
		
		// Track the request and show its progress to the user.
		$scope.progress.addPromise($promise);
	}; //end function
	
	$scope.loadContactJourneyEpisodeHistory = function (objRecord) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var $promise = ContactToolkitPageService.getContactJourneys({acrq: 'contact-journey-episode-history', cid: objRecord.reg_id, reg_comm_id: objRecord.reg_comm_id}, 
				function success(response) {
					logToConsole(response);

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


/**
 * Contact Viral page controller
 * @param $scope
 * @param $route
 * @param $routeParams
 * @param $window
 * @param ContactToolkitPageService
 * @param promiseTracker
 * @param formlyVersion
 */
contactToolkitControllers.controller('ContactViralCtrl', ['$scope', '$route', '$routeParams', '$window', 'ContactToolkitPageService', 'promiseTracker', function ContactViralCtrl($scope, $route, $routeParams, $window, ContactToolkitPageService, promiseTracker) {
	$scope.pageContent = global_wait_image;
	$scope.global_wait_image = global_wait_image;
	$scope.objPageConfig = globalPageConfig();
	$scope.objPageConfig.pageTitle = '<h5>Linked Contacts (Referrals)</h5>';
	$scope.objRecords = [];
	$scope.objLinkedToContactRecords = [];
	$scope.objLinkedContactsCount = {linked: 0, linked_to: 0};
	$scope.contact_unsubscribed = global_contact_unsubscribed;
	$scope.cid = global_contact_id;
	$scope.cid_encoded = global_contact_id_encoded;

	/**
	 * Make sure user is logged in
	 */
	userIsLoggedin();
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	
	//pagination details
	$scope.previousPage = 1;
	$scope.currentPage = 1;
	$scope.pageSize = 20;
	
	//panels
	$scope.togglePanel = function(panel, status) {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		$scope[panel] = status;
		
		if (status == true)
		{
			if (panel == 'completeFormPanel' && typeof $scope.objAvailableForms == "undefined")
			{
				$scope.objAvailableForms = Array();
				$scope.loadAvailableForms();
			}//end if
			
			doCreateSlidePanel(status);
		} else {
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

	$scope.loadAvailableForms = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		var $promise = ContactToolkitPageService.getFormsCompleted({acrq: 'list-viral-forms'}, 
				function success(response) {
					logToConsole(response);
					$scope.pageContent = '';
					
					angular.forEach(response.objData, function (objRecord, i) {
						if (objRecord.id > -1)
						{
							$scope.objAvailableForms.push(objRecord);
						}//end foreach
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Avaiable forms could not be located. Please try again.</p>');
				}
			);
		
		// Track the request and show its progress to the user.
		$scope.progress.addPromise($promise);
	};
	
	$scope.generateFormLink = function (objForm) {
		return '<a href="/forms/vf/' + objForm.id + '/' + $scope.cid_encoded + '" title="Complete ' + objForm.form + ' form" target="_blank">' + objForm.form + '</a>';
	};
	
	/**
	 * Load data list
	 */
	$scope.loadRecords = function () {
		/**
		 * Make sure user is logged in
		 */
		userIsLoggedin();
		
		//load contacts linked to this contact
		ContactToolkitPageService.getFormsCompleted({acrq: 'list-linked-contacts', cid: $scope.cid, qp_limit: $scope.pageSize}, 
			function success(response) {
				logToConsole(response);
				$scope.pageContent = '';
				
				$scope.objRecords = Array();
				$scope.objLinkedContactsCount.linked = 0;
				
				angular.forEach(response.objData, function (objRecord, i) {
					if (objRecord.id > 0 && objRecord.contact_id > 0)
					{
						$scope.objRecords.push(objRecord);
						$scope.objLinkedContactsCount.linked++;
					}//end foreach
				});
			},
			function error(errorResponse) {
				logToConsole(errorResponse);
				doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
			}
		);
		
		//load contacts this contact is linked with
		ContactToolkitPageService.getFormsCompleted({acrq: 'list-linked-to-contacts', cid: $scope.cid, qp_limit: $scope.pageSize}, 
				function success(response) {
					logToConsole(response);
					$scope.pageContent = '';
					
					$scope.objLinkedToContactRecords = Array();
					$scope.objLinkedContactsCount.linked_to = 0;
					
					angular.forEach(response.objData, function (objRecord, i) {
						if (objRecord.id > 0 && objRecord.contact_id > 0)
						{
							$scope.objLinkedToContactRecords.push(objRecord);
							$scope.objLinkedContactsCount.linked_to++;
						}//end foreach
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);		
	};	
}]);