'use strict';

var behaviourControllers = angular.module('behaviourControllers', []);

behaviourControllers.controller('HomeCtrl', [
											'$scope', 
											'$route', 
											'$routeParams', 
											'$window', 
											'BehaviourSummaryPageService', 
											'promiseTracker', 
	function HomeCtrl($scope, $route, $routeParams, $window, BehaviourSummaryPageService, promiseTracker, formlyVersion) 
	{
		$scope.global_wait_image = global_wait_image;
		$scope.objPageConfig = global_page_config;
		$scope.global_arr_action_descriptors = global_arr_action_descriptors;
	
		//behaviour categories
		$scope.objSectionFormFields = [];
		$scope.objSectionForms = [];
		$scope.objSectionTrackers = [];
		$scope.objSectionJourneys = [];
		$scope.objSectionStatuses = [];
		$scope.objSectionLinks = [];
		$scope.objSectionCampaigns = [];
		$scope.objViewBehaviour = {};
		
		$scope.displayBehaviourTargetDataPanel = false;
		
		$scope.progress = promiseTracker();
		
		$scope.init = function () {
			/**
			 * Make sure user is logged in
			 */
			userIsLoggedin();
			
			return loadData();
		}; //end function
		
		$scope.setActionDescription = function (objBehaviour)
		{
			var key = objBehaviour.action.replace(/_/g, '');
			return $scope.global_arr_action_descriptors[key].description;
		}//end function
		
		$scope.setBeahviourSectionDescription = function (objBehaviour)
		{
			switch(objBehaviour.behaviour)
			{
				case '__form':
					return 'Forms';
					break;
					
				case '__form_fields':
					return 'Form Fields';
					break;
					
				case '__journey':
					return 'Journeys';
					break;
					
				case '__reg_status':
					return 'Contact Statuses';
					break;
					
				case '__links':
					return 'Tracking Links';
					break;
					
				case '__campaign':
					return 'Campaigns';
					break;
			}//end switch
		}//end function
		
		$scope.toggleDisplayPanel = function (panel, status, objBehaviour) {
			var flag = true;
			if (status == true || status == false)
			{
				$scope[panel] = status;
			} else {
				$scope[panel] = !$scope[panel];
			}//end if
			flag = $scope[panel];
			
			switch(panel)
			{
				case 'displayBehaviourTargetDataPanel':
					if (flag == true)
					{
						$scope.objViewBehaviour = objBehaviour;
					} else {
						$scope.objViewBehaviour = {};
					}//end if
					break;
			}//end switch
			
			if (flag == true)
			{
				doCreateSlidePanel({});
			} else {
				doRemoveSlidePanel({});
			}//end if
		};
		
		function loadData(objRequest)
		{
			if (typeof objRequest == 'undefined')
			{
				var objRequest = {
					acrq: 'load-overall-summary',	
				};
			}//end if
			
			var $p = BehaviourSummaryPageService.get(objRequest,  
				function success(response) {
					logToConsole(response);
					//check for errors
					if (typeof response.error != 'undefined' && response.error == 1)
					{
						doErrorAlert('Unable to load requested data', '<p>Request failed with response: ' + response.response + '</p>');
					}//end if
					
					//clear current data
					$scope.objSectionFormFields = [];
					$scope.objSectionForms = [];
					$scope.objSectionTrackers = [];
					$scope.objSectionJourneys = [];
					$scope.objSectionStatuses = [];
					$scope.objSectionLinks = [];
					$scope.objSectionCampaigns = [];
					
					angular.forEach(response.objData, function(objBehaviour, i) {
						if (typeof objBehaviour.id != 'undefined')
						{
							switch(objBehaviour.behaviour)
							{
								case '__form_fields':
								case 'form_fields':
								case 'formfields':
									$scope.objSectionFormFields.push(objBehaviour);
									break;
									
								case '__form':
								case 'form':
									$scope.objSectionForms.push(objBehaviour);
									break;
									
								case '__form_sales_funnel':
								case 'form_sales_funnel':
								case 'formsalesfunnel':
								case '__form_tracker':
								case 'form_tracker':
								case 'formtracker':
									$scope.objSectionTrackers.push(objBehaviour);
									break;
									
								case '__journey':
								case 'journey':
									$scope.objSectionJourneys.push(objBehaviour);
									break;
									
								case '__campaign':
								case 'campaign':
									$scope.objSectionCampaigns.push(objBehaviour);
									break;
									
								case '__reg_status':
								case 'reg_status':
								case 'regstatus':
									$scope.objSectionStatuses.push(objBehaviour);
									break;
									
								case '__links':
								case 'links':
									$scope.objSectionLinks.push(objBehaviour);
									break;
							}//end switch
						}//end switch
					});
				},
				function error(errorResponse) {
					logToConsole(errorResponse);
					doErrorAlert('Unable to complete request', '<p>An unknown error has occurred. Please try again.</p>');
				}
			);
			
			$scope.progress.addPromise($p);
			return $p;
		}//end function
	}
]);