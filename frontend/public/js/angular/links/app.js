'use strict';

var frontEndApp = angular.module('frontEndApp', ['ngRoute', 'ngSanitize', 'ngAnimate', 'formly', 'formlyBootstrap', 'linksControllers', 'linksAppServices', 'ajoslin.promise-tracker', 'angularUtils.directives.dirPagination']);

frontEndApp.config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
	var tstamp = Math.floor(Date.now() / 1000);
	$routeProvider.when('/', {
		templateUrl: '/js/angular/links/partials/main.html?t=' + tstamp,
		controller: 'HomeCtrl'
	});
	
	$locationProvider.html5Mode(false).hashPrefix('!');
}]);

/**
 * Create html injection filter
 */
frontEndApp.filter("sanitize", ['$sce', function($sce) {
	  return function(htmlCode){
	    return $sce.trustAsHtml(htmlCode);
	  }
}]);

function loadLinkBehaviourConfigFormFields(action) 
{
	var field_description = {
			key: 'description',
			type: 'input',
			modelOptions: {
				getterSetter: true,
			},
			templateOptions: {
				type: 'input',
				label: 'Short Description',
				placeholder: 'Set a short description for easier identification',
				title: 'Set a short description for easier identification',
				maxlength: 50,
				required: true,
			},
			validation: {
				show: true,
			}
		};
	
	var field_active = {
			key: 'active',
			type: 'checkbox',
			modelOptions: {
				getterSetter: true
			},
			templateOptions: {
				type: 'checkbox',
				label: 'Active',
				title: 'Inactive behaviours will not be applied',
				checkboxCheckedValue: 1,
				uncheckboxCheckedValue: 0,
				default_value: 0
			},
			validation: {
				show: true
			},
			ngModelAttrs: {
				checkboxCheckedValue: {
					attribute: 'ng-true-value',
				},
				uncheckedCheckedValue: {
					attribute: 'ng-false-value'
				}
			}
	};
	
	var field_contact_status = {
			key: 'fk_reg_status_id',
			type: 'select',
			defaultValue: '',
			modelOptions: {
				getterSetter: true
			},
			templateOptions: {
				type: 'select',
				label: 'Contact Status',
				valueProp: 'optionID',
				labelProp: 'optionLabel',
				options: [
					{optionID: '', optionLabel: '--select--'}
				]
			},
			validation: {
				show: true
			}
	};
	
	var field_journey = {
			key: 'fk_journey_id',
			type: 'select',
			defaultValue: '',
			modelOptions: {
				getterSetter: true
			},
			templateOptions: {
				type: 'select',
				label: 'Journey',
				valueProp: 'optionID',
				labelProp: 'optionLabel',
				options: [
					{optionID: '', optionLabel: '--select--'}
				]
			},
			validation: {
				show: true
			}
	};	
	
	var objFormConfigs = {
			linksregistrationstatuschange: [
				field_description,
				field_contact_status,
				//generic field option
				{
					key: 'generic1',
					type: 'checkbox',
					defaultValue: 0,
					modelOptions: {
						getterSetter: true
					},
					templateOptions: {
						type: 'checkbox',
						label: 'Add Contact Status to Comments',
						title: 'Specify if Contact Status Change should be recorded',
						checkboxCheckedValue: 1,
						uncheckboxCheckedValue: 0
					},
					validation: {
						show: true
					},
					ngModelAttrs: {
						checkboxCheckedValue: {
							attribute: 'ng-true-value',
						},
						uncheckedCheckedValue: {
							attribute: 'ng-false-value'
						}
					}
				},
				field_active
			],
			
			linksjourneystart: [
				field_description,
				field_journey,
				//generic field option
				{
					key: 'generic1',
					type: 'checkbox',
					defaultValue: 0,
					modelOptions: {
						getterSetter: true
					},
					templateOptions: {
						type: 'checkbox',
						label: 'Start Journey multiple times',
						title: 'Specify if journey should be started multiple times or only once',
						checkboxCheckedValue: 1,
						uncheckboxCheckedValue: 0
					},
					validation: {
						show: true
					},
					ngModelAttrs: {
						checkboxCheckedValue: {
							attribute: 'ng-true-value',
						},
						uncheckedCheckedValue: {
							attribute: 'ng-false-value'
						}
					}
				},
				field_active,
			],
			
			linksjourneystop: [
				field_description,
				field_journey,
				field_active,
			]
	};
	
	var a = action.replace(/_/g, '');
	return objFormConfigs[a];
}//end function