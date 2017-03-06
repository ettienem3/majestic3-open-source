'use strict';

var frontEndApp = angular.module('frontEndApp', [
													'ngRoute', 
													'ngSanitize', 
													'ngAnimate', 
													'formly', 
													'formlyBootstrap', 
													'journeysControllers', 
													'journeysAppServices', 
													'ajoslin.promise-tracker', 
													'angularUtils.directives.dirPagination',
                                                    'ui.tinymce',
                                                    'ui.select',
                                                    'rzModule',
												]);

frontEndApp.config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
	var tstamp = Math.floor(Date.now() / 1000);
	$routeProvider.when('/', {
		templateUrl: '/js/angular/comms-admin/journeys/partials/main.html?t=' + tstamp,
		controller: 'HomeCtrl'
	})
	.when('/summary/:journey_id', {
		templateUrl: '/js/angular/comms-admin/journeys/partials/journey_summary.html?t=' + tstamp,
		controller: 'JourneySummaryCtrl'
	})
	.when('/episodes/:journey_id', {
		templateUrl: '/js/angular/comms-admin/journeys/partials/episodes.html?t=' + tstamp,
		controller: 'JourneyEpisodesCtrl'
	})
	.when('/episode/:journey_id/:episode_id', {
		templateUrl: '/js/angular/comms-admin/journeys/partials/episode.html?t=' + tstamp,
		controller: 'JourneyEpisodeCtrl'
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

frontEndApp.config(function(formlyConfigProvider) {
    formlyConfigProvider.setType({
      name: 'tinymce',
      templateUrl: 'textarea-tinymce.html',
      //
      wrapper: ['bootstrapLabel']
    });
});

frontEndApp.config(function(formlyConfigProvider) {
    formlyConfigProvider.setType({
    	  name: 'slider',
    	  template: ['<rzslider rz-slider-model="model[options.key]" rz-slider-options="to.sliderOptions"></rzslider>'].join(' '),
    	  wrapper: ['bootstrapLabel', 'bootstrapHasError']
    });
});

frontEndApp.config(function(formlyConfigProvider) {
    formlyConfigProvider.setType({
    	  name: 'send-time-mins-slider',
    	  templateUrl: 'send-time-mins-slider.html',
    	  wrapper: ['bootstrapLabel', 'bootstrapHasError']
    });
});

frontEndApp.config(function(formlyConfigProvider) {
    formlyConfigProvider.setType({
    	  name: 'send-time-hours-slider',
    	  templateUrl: 'send-time-hours-slider.html',
    	  wrapper: ['bootstrapLabel', 'bootstrapHasError']
    });
});

frontEndApp.config(function(formlyConfigProvider) {
    formlyConfigProvider.setType({
    	  name: 'send-time-days-slider',
    	  templateUrl: 'send-time-days-slider.html',
    	  wrapper: ['bootstrapLabel', 'bootstrapHasError']
    });
});

/**
 * Supplies behaviour form fields config
 * @param action
 * @returns array
 */
function loadJourneyBehaviourConfigFormFields(action)
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
	
	var field_operator = {
			key: 'field_operator',
			type: 'select',
			modelOptions: {
				getterSetter: true
			},
			templateOptions: {
				type: 'select',
				label: 'Field Operation',
				title: 'Set manner in which comparison operation should be used where Behaviour is applied',
				valueProp: 'optionID',
				labelProp: 'optionLabel',
				options: {
					0: {optionID: '', optionLabel: '--select--'}
				}
			},
			validation: {
				show: true
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
	
	var objFormConfigs = {
			altemaildestination: [
				field_description,
				field_active
			],
			
			altemaildestinationhard: [
				field_description,
				field_active
			],
			
			registrationstatuschangejourneyfirstcomm: [
				field_description,

				//contact status
				{
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
						required: true,
						options: [
							{optionID: '', optionLabel: '--select--'}
						]
					},
					validation: {
						show: true
					}	
				},
				
				//add to comments
				{
					key: 'generic1',
					type: 'checkbox',
					defaultValue: 0,
					modelOptions: {
						getterSetter: true
					},
					templateOptions: {
						type: 'checkbox',
						label: 'Add status to comments',
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
				},
				
				field_active
			],
			
			registrationstatuschangejourneylastcomm: [
				field_description,
				//contact status
				{
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
						required: true,
						options: [
							{optionID: '', optionLabel: '--select--'}
						]
					},
					validation: {
						show: true
					}	
				},
				
				//add to comments
				{
					key: 'generic1',
					type: 'checkbox',
					defaultValue: 0,
					modelOptions: {
						getterSetter: true
					},
					templateOptions: {
						type: 'checkbox',
						label: 'Add status to comments',
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
				},
				field_active
			],
			
			journeynostarttime: [
				field_description,
				//number of days field
				{
					key: 'content',
					type: 'slider',
					defaultValue: 1,
					modelOptions: {
						getterSetter: true
					},
					templateOptions: {
						label: 'Number of days',
						sliderOptions: {
							floor: 1,
							ceil: 120
						}
					},
					validation: {
						show: true,
					},
				},
				field_active
			],
			
			journeynostartwebform: [
				field_description,
				//web form field
				{
					key: 'fk_form_id',
					type: 'select',
					modelOptions: {
						getterSetter: true
					},
					templateOptions: {
						type: 'select',
						label: 'Web Form',
						title: 'Prevent a journey from starting where a contact has completed this form',
						valueProp: 'optionID',
						labelProp: 'optionLabel',
						options: {
							0: {optionID: '', optionLabel: '--select--'}
						}
					},
					validation: {
						show: true
					}
				},
				field_active
			],
			
			journeyrestartjourneylastcomm: [
				field_description,
				field_active
			],
			
			journeystartjourneylastcomm: [
				field_description,
				
				//journey id 2
				{
					key: 'fk_journey_id2',
					type: 'select',
					defaultValue: '',
					modelOptions: {
						getterSetter: true
					},
					templateOptions: {
						type: 'select',
						label: 'Start Journey',
						required: true,
						valueProp: 'optionID',
						labelProp: 'optionLabel',
						options: [
							{optionID: '', optionLabel: '--select--'}
						]
					},
					validation: {
						show: true
					}
				},
				
				field_active
			],
			
			journeynostart: [
				field_description,
				//standard field
				{
					key: 'fk_fields_std_id',
					type: 'select',
					defaultValue: '',
					modelOptions: {
						getterSetter: true
					},
					templateOptions: {
						type: 'select',
						label: 'Standard Field',
						valueProp: 'optionID',
						labelProp: 'optionLabel',
						options: [
							{optionID: '', optionLabel: '--select--'},
							{optionID: '27', optionLabel: 'User'},
							{optionID: '22', optionLabel: 'Country'},
							{optionID: '23', optionLabel: 'City'},
							{optionID: '24', optionLabel: 'Province'},
							{optionID: '28', optionLabel: 'Source'},
							{optionID: '29', optionLabel: 'Reference'},
						],
						onChange: function($viewValue, $modelValue, scope) {
							if ($viewValue == '')
							{
								return;
							}//end if
							
							var doDataCall = function (target_url) {
								var $c = jQuery.ajax({
									url: target_url,
									type: "GET",
									dataType: "json"
								})
								.done(function (data) {
									return data;
								})
								.fail(function () {
									doErrorAlert('An unknown problem has occurred', '<p>An unknown problem has occurred and the required data could not be loaded</p>');
								});
								
								return $c;
							};
							
							//update operator and value select fields
							switch ($viewValue)
							{
								case '22': //countries
									var url = "/front/locations/countries/ajax-load-countries";
									var call = doDataCall(url);
									call.then(function (data) {
										//populate value dropdown fields
										var arr_values = [];
										arr_values.push({optionID: '', optionLabel: '--select--'});
										angular.forEach(data, function (objD, i) {
											if (typeof objD.id != 'undefined')
											{
												arr_values.push({optionID: objD.id, optionLabel: objD.country});	
											}//end if
										});
										
										//find field and update options
										angular.forEach(scope.fields, function (objField, i) {
											if (objField.key == 'field_value')
											{
												scope.fields[i].templateOptions.options = arr_values;
											}//end if
										});
										
										doInfoAlert('Fields updated', '<p>Based on your selection, some field options had to be amended. These changes have been completed, you can continue with your current behaviour configuration.</p>')
									});
									break;
									
								case '23': //cities
									var url = "/front/locations/countries/ajax-load-cities";
									var call = doDataCall(url);
									call.then(function (data) {
										//populate value dropdown fields
										var arr_values = [];
										arr_values.push({optionID: '', optionLabel: '--select--'});
										angular.forEach(data, function (objD, i) {
											if (typeof objD.id != 'undefined')
											{
												arr_values.push({optionID: objD.id, optionLabel: objD.city});	
											}//end if
										});
										
										//find field and update options
										angular.forEach(scope.fields, function (objField, i) {
											if (objField.key == 'field_value')
											{
												scope.fields[i].templateOptions.options = arr_values;
											}//end if
										});
										
										doInfoAlert('Fields updated', '<p>Based on your selection, some field options had to be amended. These changes have been completed, you can continue with your current behaviour configuration.</p>')
									});
									break;
									
								case '24': //provinces
									var url = "/front/locations/countries/ajax-load-provinces";
									var call = doDataCall(url);
									call.then(function (data) {
										//populate value dropdown fields
										var arr_values = [];
										arr_values.push({optionID: '', optionLabel: '--select--'});
										angular.forEach(data, function (objD, i) {
											if (typeof objD.id != 'undefined')
											{
												arr_values.push({optionID: objD.id, optionLabel: objD.province});	
											}//end if
										});
										
										//find field and update options
										angular.forEach(scope.fields, function (objField, i) {
											if (objField.key == 'field_value')
											{
												scope.fields[i].templateOptions.options = arr_values;
											}//end if
										});
										
										doInfoAlert('Fields updated', '<p>Based on your selection, some field options had to be amended. These changes have been completed, you can continue with your current behaviour configuration.</p>')
									});
									break;
									
								case '27': //users
									var url = "/front/users/ajax-load-users";
									var call = doDataCall(url);
									call.then(function (data) {
										//populate value dropdown fields
										var arr_values = [];
										arr_values.push({optionID: '', optionLabel: '--select--'});
										angular.forEach(data, function (objD, i) {
											if (typeof objD.id != 'undefined')
											{
												arr_values.push({optionID: objD.id, optionLabel: objD.uname});	
											}//end if
										});
										
										//find field and update options
										angular.forEach(scope.fields, function (objField, i) {
											if (objField.key == 'field_value')
											{
												scope.fields[i].templateOptions.options = arr_values;
											}//end if
										});
										
										doInfoAlert('Fields updated', '<p>Based on your selection, some field options had to be amended. These changes have been completed, you can continue with your current behaviour configuration.</p>')
									});
									break;
									
								case '28': //source
									var url = "/front/contacts/ajax-load-source-list";
									var call = doDataCall(url);
									call.then(function (data) {
										//populate value dropdown fields
										var arr_values = [];
										arr_values.push({optionID: '', optionLabel: '--select--'});
										angular.forEach(data, function (objD, i) {
											if (objD != '')
											{
												arr_values.push({optionID: objD, optionLabel: objD});	
											}//end if
										});
										
										//find field and update options
										angular.forEach(scope.fields, function (objField, i) {
											if (objField.key == 'field_value')
											{
												scope.fields[i].templateOptions.options = arr_values;
											}//end if
										});
										
										doInfoAlert('Fields updated', '<p>Based on your selection, some field options had to be amended. These changes have been completed, you can continue with your current behaviour configuration.</p>')
									});
									break;
									
								case '29': //reference
									var url = "/front/contacts/ajax-load-reference-list";
									var call = doDataCall(url);
									call.then(function (data) {
										//populate value dropdown fields
										var arr_values = [];
										arr_values.push({optionID: '', optionLabel: '--select--'});
										angular.forEach(data, function (objD, i) {
											if (objD != '')
											{
												arr_values.push({optionID: objD, optionLabel: objD});	
											}//end if
										});
										
										//find field and update options
										angular.forEach(scope.fields, function (objField, i) {
											if (objField.key == 'field_value')
											{
												scope.fields[i].templateOptions.options = arr_values;
											}//end if
										});
										
										doInfoAlert('Fields updated', '<p>Based on your selection, some field options had to be amended. These changes have been completed, you can continue with your current behaviour configuration.</p>')
									});
									break;
							}//end switch
						}
					},
					validation: {
						show: true
					},
					expressionProperties: {
						"templateOptions.disabled": "model.fk_form_id || model.fk_reg_status_id || model.fk_fields_custom_id"
					},
					hideExpression: "model.fk_reg_status_id != '' || model.fk_fields_custom_id != '' || model.fk_form_id != ''",
				},
				
				//custom field
				{
					key: 'fk_fields_custom_id',
					type: 'select',
					defaultValue: '',
					modelOptions: {
						getterSetter: true
					},
					templateOptions: {
						type: 'select',
						label: 'Custom Field',
						valueProp: 'optionID',
						labelProp: 'optionLabel',
						options: [
							{optionID: '', optionLabel: '--select--'}
						],
						onChange: function($viewValue, $modelValue, scope) {
							if ($viewValue == '')
							{
								return;
							}//end if
							
							//update operator and value select fields
							//load data
							var configured_url = "/front/form/admin/fields/ajax-load-specified-field-values/0001122333?field_type=custom&include_field_values=1";

							jQuery.ajax({
										url: configured_url.replace("0001122333", $viewValue),
										type: "GET",
										dataType: "json",
									})
									.done(function (data) {
										if (data.fields_types_input_type == "select" || data.fields_types_input_type == "radio")
										{
											var arr_field_values = [];
											arr_field_values.push({optionID: '', optionLabel: '--select--'});
											jQuery.each(data.field_values_data, function(k, v) {
												arr_field_values.push({optionID: k, optionLabel: v});
											});
											
											var arr_operator_options = [
												{optionID: '', optionLabel: '--select--'},
												{optionID: 'equals', optionLabel: 'is equal to'},
												{optionID: 'notequals', optionLabel: 'is not equal to'},
											];
											
											//find field and update options
											angular.forEach(scope.fields, function (objField, i) {
												if (objField.key == 'field_value')
												{
													scope.fields[i].templateOptions.options = arr_field_values;
												}//end if
												
												if (objField.key == 'field_operator')
												{
													scope.fields[i].templateOptions.options = arr_operator_options;
												}//end if
											});
										}//end if
					
										if (data.fields_types_input_type == "checkbox")
										{											
											var arr_operator_options = [
												{optionID: '', optionLabel: '--select--'},
												{optionID: 'checked', optionLabel: 'is checked'},
												{optionID: 'unchecked', optionLabel: 'is not checked'},
											];

											var arr_field_values = [];
											arr_field_values.push({optionID: '', optionLabel: '--select--'});
											
											//find field and update options
											angular.forEach(scope.fields, function (objField, i) {
												if (objField.key == 'field_value')
												{
													scope.fields[i].templateOptions.options = arr_field_values;
												}//end if
												
												if (objField.key == 'field_operator')
												{
													scope.fields[i].templateOptions.options = arr_operator_options;
												}//end if
											});
										}//end if	
										
										doInfoAlert('Fields updated', '<p>Based on your selection, some field options had to be amended. These changes have been completed, you can continue with your current behaviour configuration.</p>')
									})
									.fail(function () {
										doErrorAlert('An unknown problem has occurred', '<p>An unknown problem has occurred and the required data could not be loaded</p>');
									});							
						}
					},
					validation: {
						show: true
					},
					expressionProperties: {
						"templateOptions.disabled": "model.fk_form_id || model.fk_reg_status_id || model.fk_fields_std_id"
					},
					hideExpression: 'model.fk_reg_status_id != "" || model.fk_fields_std_id != "" || model.fk_form_id != ""',
				},
				
				//Contact status
				{
					key: 'fk_reg_status_id',
					type: 'select',
					defaultValue: '',
					modelOptions: {
						getterSetter: true
					},
					templateOptions: {
						type: 'select',
						label: 'Contact Status is set to',
						valueProp: 'optionID',
						labelProp: 'optionLabel',
						options: [
							{optionID: '', optionLabel: '--select--'}
						]
					},
					validation: {
						show: true
					},
					expressionProperties: {
						"templateOptions.disabled": "model.fk_form_id || model.fk_fields_custom_id || model.fk_fields_std_id"
					},
					hideExpression: 'model.fk_fields_custom_id != "" || model.fk_fields_std_id != "" || model.fk_form_id != ""',
				},
				
				//web form completed
				{
					key: 'fk_form_id',
					type: 'select',
					defaultValue: '',
					modelOptions: {
						getterSetter: true
					},
					templateOptions: {
						type: 'select',
						label: 'Web Form has been completed',
						valueProp: 'optionID',
						labelProp: 'optionLabel',
						options: [
								{optionID: '', optionLabel: '--select--'}
							]
					},
					validation: {
						show: true
					},
					expressionProperties: {
						"templateOptions.disabled": "model.fk_reg_status_id || model.fk_fields_custom_id || model.fk_fields_std_id"
					},
					hideExpression: 'model.fk_fields_custom_id !="" || model.fk_fields_std_id != "" || model.fk_reg_status_id != ""',
				},
				
				//field operator
				{
					key: 'field_operator',
					type: 'select',
					defaultValue: '',
					modelOptions: {
						getterSetter: true
					},
					templateOptions: {
						type: 'select',
						label: 'Operator',
						valueProp: 'optionID',
						labelProp: 'optionLabel',
						options: [
							{optionID: '', optionLabel: '--select--'},
							{optionID: 'equals', optionLabel: 'is equal to'},
							{optionID: 'notequals', optionLabel: 'is not equal to'},
						]
					},
					validation: {
						show: true
					},
					expressionProperties: {
						"templateOptions.disabled": "!model.fk_fields_custom_id && !model.fk_fields_std_id"
					},
					hideExpression: '!model.fk_fields_custom_id && !model.fk_fields_std_id',
				},
				
				//field value
				{
					key: 'field_value',
					type: 'select',
					defaultValue: '',
					modelOptions: {
						getterSetter: true
					},
					templateOptions: {
						type: 'select',
						label: 'Value',
						valueProp: 'optionID',
						labelProp: 'optionLabel',
						options: [
							 {optionID: '', optionLabel: '--select--'}
						]
					},
					validation: {
						show: true
					},
					expressionProperties: {
						"templateOptions.disabled": "!model.fk_fields_custom_id && !model.fk_fields_std_id"
					},
					hideExpression: '!model.fk_fields_custom_id && !model.fk_fields_std_id',
				},
				
				field_active
			],
			
			viralnotificationjourney: [
				field_description,
				//referral form field
				{
					key: 'fk_form_id',
					type: 'select',
					modelOptions: {
						getterSetter: true
					},
					templateOptions: {
						type: 'select',
						label: 'Referral Form',
						valueProp: 'optionID',
						labelProp: 'optionLabel',
						options: [
							{optionID: '', optionLabel: '--select--'}
						]
					},
					validation: {
						show: true
					}
				},
				field_active
			],
			
			viralformlink: [
				field_description,
				//referral form field
				{
					key: 'fk_form_id',
					type: 'select',
					modelOptions: {
						getterSetter: true
					},
					templateOptions: {
						type: 'select',
						label: 'Referral Form',
						valueProp: 'optionID',
						labelProp: 'optionLabel',
						options: [
							{optionID: '', optionLabel: '--select--'}
						]
					},
					validation: {
						show: true
					}
				},
				field_active
			]
	};

	var a = action.replace(/_/g, '');
	return objFormConfigs[a];
}//end function
