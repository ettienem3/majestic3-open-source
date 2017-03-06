'use strict';

//frontEndFormAdminApp
var frontEndFormAdminApp = angular.module('frontEndFormAdminApp', ['ngRoute', 
                                                                   'ngSanitize', 
                                                                   'ngAnimate', 
                                                                   'ngMessages',
                                                                   'formly', 
                                                                   'formlyBootstrap', 
                                                                   'formsAdminControllers', 
                                                                   'formsAdminAppServices', 
                                                                   'ajoslin.promise-tracker', 
                                                                   'angularUtils.directives.dirPagination', 
                                                                   'ui.tinymce',
                                                                   'ui.select',
                                                                   'dndLists',
                                                                   'cp.ngConfirm'
                                                                   ]);

frontEndFormAdminApp.config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
	var tstamp = Math.floor(Date.now() / 1000);
	$routeProvider.when('/', {
		templateUrl: '/js/angular/forms-admin/partials/main.html?t=' + tstamp,
		controller: 'HomeCtrl'
	});
	
	$locationProvider.html5Mode(false).hashPrefix('!');
}]);

frontEndFormAdminApp.config(function(formlyConfigProvider) {
    formlyConfigProvider.setType({
      name: 'tinymce',
      templateUrl: 'textarea-tinymce.html',
      //
      wrapper: ['bootstrapLabel']
    });
  });

/**
 * Create html injection filter
 */
frontEndFormAdminApp.filter("sanitize", ['$sce', function($sce) {
	  return function(htmlCode){
	    return $sce.trustAsHtml(htmlCode);
	  }
}]);

/**
 * AngularJS default filter with the following expression:
 * "person in people | filter: {name: $select.search, age: $select.search}"
 * performs an AND between 'name: $select.search' and 'age: $select.search'.
 * We want to perform an OR.
 */
frontEndFormAdminApp.filter('propsFilter', function() {
  return function(items, props) {
    var out = [];

    if (angular.isArray(items)) {
      var keys = Object.keys(props);

      items.forEach(function(item) {
        var itemMatches = false;

        for (var i = 0; i < keys.length; i++) {
          var prop = keys[i];
          var text = props[prop].toLowerCase();
          if (typeof item[prop] != 'undefined' && item[prop].toString().toLowerCase().indexOf(text) !== -1) {
            itemMatches = true;
            break;
          }
        }

        if (itemMatches) {
          out.push(item);
        }
      });
    } else {
      // Let the output be the input untouched
      out = items;
    }

    return out;
  };
});

/**
 * Fields for creating or updating web forms
 * Provides the bare minimum fields to create or update a form
 */
function formAdminFormSimple(form_type)
{
	var objFields = [];
	
	//title field
	objFields.push({
		'key': 'form',
		'type': 'input',
		'templateOptions': {
			'type': 'input',
			'label': 'Form Title',
			'placeholder': 'Set a title for the form',
			'title': 'Set a title for the form',
			'maxlength': 50,
			'required': true
		},
		'validation': {
			'show': true,
		}
	});
	
	//status field
	objFields.push({
		'key': 'active',
		'type': 'checkbox',
		'templateOptions': {
			'type': 'checkbox',
			'label': 'Active',
			'title': 'Inactive forms are not available for completion',
			'checkboxCheckedValue': 1,
			'uncheckboxCheckedValue': 0,
			'defaultValue': 1
		},
		'validation': {
			'show': true
		},
		'ngModelAttrs': {
			'checkboxCheckedValue': {'attribute': 'ng-true-value'},
			'uncheckedCheckboxValue': {'attribute': 'ng-false-value'}
		}
	});
	
	//populate form field
	objFields.push({
		'key': 'populate_form',
		'type': 'checkbox',
		'templateOptions': {
			'type': 'checkbox',
			'label': 'Populate Form',
			'title': 'Attempt to populate the form where data is made available',
			'checkboxCheckedValue': 1,
			'uncheckboxCheckedValue': 0,
			'defaultValue': 1,
		},
		'validation': {
			'show': true
		},
		'ngModelAttrs': {
			'checkboxCheckedValue': {'attribute': 'ng-true-value'},
			'uncheckedCheckboxValue': {'attribute': 'ng-false-value'}
		},
	});
	
	//submit button field
	objFields.push({
		'key': 'submit_button',
		'type': 'input',
		'templateOptions': {
			'type': 'input',
			'label': 'Submit Button Content',
			'placeholder': 'Set a title for the form',
			'title': 'Set content for the submit button. Default is "Submit"',
			'maxlength': 20,
			'required': true,
			'defaultValue': 'Submit'
		},
		'validation': {
			'show': true,
		}
	});
	
	//duplicate field
	objFields.push({
		'key': 'duplicate_behaviour',
		'type': 'select',
		'templateOptions': {
			'type': 'select',
			'label': 'Form Contact Duplicate Behaviours',
			'placeholder': 'Set a title for the form',
			'title': 'Set a title for the form',
			'maxlength': 50,
			'required': true,
			'valueProp': 'optionID',
			'labelProp': 'optionLabel',
			'options': [
			    {'optionID': '', 'optionLabel': 'Specify form duplicate behaviour'},    
				{'optionID': '__duplicate_fields', 'optionLabel': 'Update first Contact found'},
				{'optionID': '__duplicate_status', 'optionLabel': 'Add new Contact with "Possible duplicate" status'},
				{'optionID': '__duplicate_fail_hard', 'optionLabel': 'Abort process, return error and send notification with data'},
				{'optionID': '__duplicate_fail_soft', 'optionLabel': 'Update first Contact found and send notification with data'},
			]
		},
		'validation': {
			'show': true,
		}
	});
	
	if (typeof form_type != 'undefined')
	{
		//filter our fields based on form type
		var objFormDetailsFields = Array();
		angular.forEach(objFields, function (objF, i) {
			switch(form_type) {
				case '__tracker':
				case '__sales_funnel':
					switch(objF.key) {
						case 'form':
						case 'alias':
						case 'active':
							objFormDetailsFields.push(objF);
							break;
					}//end switch
					break;
					
				case '__cpp':
					switch(objF.key) {
						case 'form':
						case 'alias':
						case 'active':
							objFormDetailsFields.push(objF);
							break;
					}//end switch
					break;
					
				case '__viral':
				case '__referral':
					switch(objF.key) {
						case 'form':
						case 'alias':
						case 'active':
						case 'duplicate_behaviour':
							objFormDetailsFields.push(objF);
							break;
					}//end switch
					break;
					
				default:
					objFormDetailsFields.push(objF);
					break;
			}//end switch
		})//end foreach
		
		return objFormDetailsFields;
	}//end if
	
	return objFields;
};

/**
 * Fields for setting advanced configuration options for web forms
 */
function formAdminFormAdvanced(form_type)
{
	var objFields = Array();
	
	var objFormDetailsFields = formAdminFormSimple(form_type);
	objFields.push({
		'key': '_form_details',
		'fieldGroup': objFormDetailsFields
	});
	
	//https://github.com/formly-js/angular-formly/issues/173
	//notifications
	objFields.push({
		'key': '_notifications_section',
		'fieldGroup': [
//causes to much confusion, look at the drop down value rather			
//		               {
//		            	   'key': 'notify_active',
//		            	   'type': 'checkbox',
//		            	   'templateOptions': {
//		            		   'type': 'checkbox',
//		            		   'label': 'Enable form notifications',
//		            		   'title': 'Notifications are sent via email when a form is completed',
//		            		   'checkboxCheckedValue': 1,
//		            		   'uncheckboxCheckedValue': 0,
//		            		   'defaultValue': 0
//		            	   },
//		            	   'validation': {'show': true},
//		            	   'ngModelAttrs': {
//		            		   'checkboxCheckedValue': {'attribute': 'ng-true-value'},
//		            		   'uncheckboxCheckedValue': {'attribute': 'ng-false-value'},
//		            	   }
//		               },
		               {
			           		'key': '_notify',
			        		'type': 'select',
			        		'templateOptions': {
			        			'type': 'select',
			        			'label': 'Notify users on form completion',
			        			'placeholder': 'Set form notifications',
			        			'title': 'Specify user to send form completion notifications to over the default',
			        			'valueProp': 'optionID',
			        			'labelProp': 'optionLabel',
			        			'defaultValue': 9,
			        			'options': [
			        			    {'optionID': '', 'optionLabel': 'Set form notification options'},
			        			    {'optionID': 9, 'optionLabel': 'None'},
			        				{'optionID': 0, 'optionLabel': 'Notify on form submit users'},
			        				{'optionID': 1, 'optionLabel': 'Notify Contacts\'s user only'},
			        				{'optionID': 2, 'optionLabel': 'Notify Contacts\'s user and users set to receive notifcations'},
			        			]
			        		},
			        		'validation': {
			        			'show': true,
			        		}		            	   
		               },
		               {
			           		'key': '_notify_additional',
			        		'type': 'input',
			        		'templateOptions': {
			        			'type': 'input',
			        			'label': 'Include these email addresses (Seperate with ;)',
			        			'placeholder': 'Add additional addresses',
			        			'title': 'Specify additional users to receive form completion notifications in addition to the default or specified on the form. Seperate email addresses with a semi-colon (;)',
			        			'maxlength': 200,
			        			'required': false,
			        			'defaultValue': ''
			        		},
			        		'validation': {
			        			'show': true,
			        		}		            	   
		               },
		               {
			           		'key': '_notify_copy',
			           		'type': 'tinymce',
			           		'data': {
			           			'tinymceOption': angularTinymceConfig()
			           		},
			        		'templateOptions': {
			        			'type': 'textarea',
			        			'label': 'Add this content to the email',
			        			'placeholder': 'Email content to be used within the form completion notification email',
			        			'title': 'Email content to be used within the form completion notification email',
			        			'required': false,
			        			'defaultValue': ''
			        		},
			        		'validation': {
			        			'show': true,
			        		}		            	   
		               }
		 ]
	},
	{
		'key': '_appearance_and_content',
		'fieldGroup': [
			{
				'key': 'template_id',
        		'type': 'select',
        		'templateOptions': {
        			'type': 'select',
        			'label': 'Look and Feel',
        			'placeholder': 'Set Look and Feel for Form',
        			'title': 'Apply a look and feel to the form',
        			'valueProp': 'optionID',
        			'labelProp': 'optionLabel',
        			'defaultValue': '',
        			'options': [
        			       //populate with ajax
        			]
        		},
        		'validation': {
        			'show': true,
        		}
			},
			{
			   'key': 'show_fwd_warn',
			   'type': 'checkbox',
			   'templateOptions': {
				   'type': 'checkbox',
				   'label': 'Show form forward notification',
				   'title': 'Notifications are sent via email when a form is completed',
				   'checkboxCheckedValue': 1,
				   'uncheckboxCheckedValue': 0,
				   'defaultValue': 0
			   },
			   'validation': {'show': true},
			   'ngModelAttrs': {
				   'checkboxCheckedValue': {'attribute': 'ng-true-value'},
				   'uncheckboxCheckedValue': {'attribute': 'ng-false-value'},
			   }
			},
			//content above form
			{
				'key': 'copy',
				'type': 'tinymce',
           		'data': {
           			'tinymceOption': angularTinymceConfig()
           		},
        		'templateOptions': {
        			'type': 'textarea',
        			'label': 'Add content above the form',
        			'placeholder': 'Add content to appear above the form when loaded',
        			'title': 'Add some additional content above the form where required',
        			'required': false,
        			'defaultValue': ''
        		},
        		'validation': {
        			'show': true,
        		}	
			},
			//content below form
			{
				'key': 'copy2',
				'type': 'tinymce',
           		'data': {
           			'tinymceOption': angularTinymceConfig()
           		},
        		'templateOptions': {
        			'type': 'textarea',
        			'label': 'Add content below the form',
        			'placeholder': 'Add content to appear below the form when loaded',
        			'title': 'Add some additional content below the form where required',
        			'required': false,
        			'defaultValue': ''
        		},
        		'validation': {
        			'show': true,
        		}	
			},
			//terms and conditions
			{
				'key': 'terms',
				'type': 'tinymce',
           		'data': {
           			'tinymceOption': angularTinymceConfig()
           		},
        		'templateOptions': {
        			'type': 'textarea',
        			'label': 'Terms and Conditions',
        			'placeholder': 'Add Terms and Conditions where required',
        			'title': 'Add terms and conditions to be displayed to user',
        			'required': false,
        			'defaultValue': ''
        		},
        		'validation': {
        			'show': true,
        		}	
			},
			//form rejected content
			{
				'key': 'reject_copy',
				'type': 'tinymce',
           		'data': {
           			'tinymceOption': angularTinymceConfig()
           		},
        		'templateOptions': {
        			'type': 'textarea',
        			'label': 'Form Rejected Notification',
        			'placeholder': 'Display content to user where a form is rejected, this is only used based on a behaviour preventing the form from being accepted',
        			'title': 'Set form rejected notification content',
        			'required': false,
        			'defaultValue': ''
        		},
        		'validation': {
        			'show': true,
        		}	
			},
			//post submit content
			{
				'key': 'submit_copy',
				'type': 'tinymce',
				'defaultValue': '',
           		'data': {
           			'tinymceOption': angularTinymceConfig()
           		},
        		'templateOptions': {
        			'type': 'textarea',
        			'label': 'Display content after form has been submitted',
        			'placeholder': 'Display content to user where a form has been submitted successfully',
        			'title': 'Set content displayed to a user once a form has been submitted successfully',
        			'required': false,
        		},
        		'validation': {
        			'show': true,
        		}	
			},			
		]
	},
	{
		'key': '_javascript_options',
		'fieldGroup': [
		     {
		    	'key': 'form_js_file',
        		'type': 'input',
        		'templateOptions': {
        			'type': 'input',
        			'label': 'Add Javascript file to form',
        			'placeholder': 'Enter uploaded file name here. The file must exist within My Images and Documents',
        			'title': 'Add some javascript to your form for more flexibiltiy',
        			'maxlength': 150,
        			'required': false,
        			'defaultValue': ''
        		},
        		'validation': {
        			'show': true,
        		}	
		     },
		     {
		    	 'key': 'submit_tracking_script',
	        		'type': 'textarea',
	        		'templateOptions': {
	        			'type': 'textarea',
	        			'label': 'Tracking Javascript',
	        			'placeholder': 'Add tracking javascript here, exclude script tags',
	        			'title': 'Add tracking capabilties to your form such as Google Analytics',
	        			'required': false,
	        			'defaultValue': ''
	        		},
	        		'validation': {
	        			'show': true,
	        		}
		     },
		]
	},
	{
		'key': '_window_options',
		'fieldGroup': [
		     {
		    	   'key': 'redirect_parent',
				   'type': 'checkbox',
				   'templateOptions': {
					   'type': 'checkbox',
					   'label': 'Redirect - Load in parent window',
					   'title': 'Where not set, the redirect option below will load in a new page',
					   'checkboxCheckedValue': 1,
					   'uncheckboxCheckedValue': 0,
					   'defaultValue': 0
				   },
				   'validation': {'show': true},
				   'ngModelAttrs': {
					   'checkboxCheckedValue': {'attribute': 'ng-true-value'},
					   'uncheckboxCheckedValue': {'attribute': 'ng-false-value'},
				   }
		     },
		     {
		    	    'key': 'redirect',
	        		'type': 'input',
	        		'templateOptions': {
	        			'type': 'input',
	        			'label': 'Redirect to another page once a form is submitted',
	        			'placeholder': 'Enter redirect URL destination',
	        			'title': 'Make a browser redirect to another page once a form has been submitted successfully',
	        			'maxlength': 255,
	        			'required': false,
	        			'defaultValue': ''
	        		},
	        		'validation': {
	        			'show': true,
	        		}	
		     }
		 ]
	},
	{
		'key': '_default_values',
		'fieldGroup': [
		      {
		    	    'key': 'default_source',
	        		'type': 'input',
	        		'templateOptions': {
	        			'type': 'input',
	        			'label': 'Set Contact Source',
	        			'placeholder': 'Enter Source for a new Contact (Optional)',
	        			'title': 'Where a new contact is created, use this value as the Source attribute for the contact',
	        			'maxlength': 50,
	        			'required': false,
	        			'defaultValue': ''
	        		},
	        		'validation': {
	        			'show': true,
	        		}			    	  
		      },
		      {
		    	  	'key': 'default_reference',
	        		'type': 'input',
	        		'templateOptions': {
	        			'type': 'input',
	        			'label': 'Set Contact Reference',
	        			'placeholder': 'Enter Reference for a new Contact (Optional)',
	        			'title': 'Where a new contact is created, use this value as the Reference attribute for the contact',
	        			'maxlength': 50,
	        			'required': false,
	        			'defaultValue': ''
	        		},
	        		'validation': {
	        			'show': true,
	        		}			    	  
		      },
		      {
		    	   	'key': 'default_reg_status_id',
	        		'type': 'select',
	        		'templateOptions': {
	        			'type': 'select',
	        			'label': 'Use this status and not the profile default status for new contacts',
	        			'title': 'Set another status to be allocated to new contacts and not use the profile default value',
	        			'valueProp': 'optionID',
	        			'labelProp': 'optionLabel',
	        			'required': false,
	        			'defaultValue': '',
	        			'options': [
	        			       {'optionID': '', 'optionLabel': 'Loading...'}
	        			       //populated via ajax
	        			]
	        		},
	        		'validation': {
	        			'show': true,
	        		}		    	  
		      },
		      {
		    	   	'key': 'default_user_id',
	        		'type': 'select',
	        		'templateOptions': {
	        			'type': 'select',
	        			'label': 'Use this user and not the profile default user for new contacts',
	        			'title': 'Set another user to be allocated to new contacts and not use the profile default value',
	        			'valueProp': 'optionID',
	        			'labelProp': 'optionLabel',
	        			'required': false,
	        			'defaultValue': '',
	        			'options': [
	        			       {'optionID': '', 'optionLabel': 'Loading...'}
	        			       //populated via ajax
	        			]
	        		},
	        		'validation': {
	        			'show': true,
	        		}		    	  
		      },
		      {
		    	   'key': 'user_login_allocate',
				   'type': 'checkbox',
				   'templateOptions': {
					   'type': 'checkbox',
					   'label': 'Allocate contact to logged in user where contacts are created',
					   'title': 'Where not set, the default profile user will be used to allocate to the contact',
					   'checkboxCheckedValue': 1,
					   'uncheckboxCheckedValue': 0,
					   'defaultValue': 0
				   },
				   'validation': {'show': true},
				   'ngModelAttrs': {
					   'checkboxCheckedValue': {'attribute': 'ng-true-value'},
					   'uncheckboxCheckedValue': {'attribute': 'ng-false-value'},
				   }		    	  
		      },
		 ]
	},
	{
		'key': '_form_security',
		'fieldGroup': [
		      {
			  		'key': 'id_required',
					'type': 'checkbox',
					'templateOptions': {
						'type': 'checkbox',
						'label': 'An existing contact is required to load form',
						'title': 'Activate this option where a form should only be made available to known contacts. This will require the contact\'s encoded id in the form url',
						'checkboxCheckedValue': 1,
						'uncheckboxCheckedValue': 0,
						'defaultValue': 0,
					},
					'validation': {
						'show': true
					},
					'ngModelAttrs': {
						'checkboxCheckedValue': {'attribute': 'ng-true-value'},
						'uncheckedCheckboxValue': {'attribute': 'ng-false-value'}
					},
		      },
		      {
		    	  	'key': 'captcha',
				   'type': 'checkbox',
				   'templateOptions': {
					   'type': 'checkbox',
					   'label': 'Enable CAPTCHA on the form',
					   'title': 'Where enabled, an additional field is added to the form to make sure a human is submitting the form',
					   'checkboxCheckedValue': 1,
					   'uncheckboxCheckedValue': 0,
					   'defaultValue': 0
				   },
				   'validation': {'show': true},
				   'ngModelAttrs': {
					   'checkboxCheckedValue': {'attribute': 'ng-true-value'},
					   'uncheckboxCheckedValue': {'attribute': 'ng-false-value'},
				   }
		      },
		      {
		    	   'key': 'id_required',
				   'type': 'checkbox',
				   'templateOptions': {
					   'type': 'checkbox',
					   'label': 'A known contact is required to access the form',
					   'title': 'Where set, a contact\'s id must be provided to access the form',
					   'checkboxCheckedValue': 1,
					   'uncheckboxCheckedValue': 0,
					   'defaultValue': 0
				   },
				   'validation': {'show': true},
				   'ngModelAttrs': {
					   'checkboxCheckedValue': {'attribute': 'ng-true-value'},
					   'uncheckboxCheckedValue': {'attribute': 'ng-false-value'},
				   }		    	  
		      },
		      {
		    	    'key': 'form_password',
	        		'type': 'input',
	        		'templateOptions': {
	        			'type': 'password',
	        			'label': 'Protect form using a password',
	        			'placeholder': 'Enter password here to prevent unauthorized access (Optional)',
	        			'title': 'A form can be protected by setting a password where required to prevent general public access',
	        			'maxlength': 20,
	        			'required': false,
	        			'defaultValue': ''
	        		},
	        		'validation': {
	        			'show': true,
	        		}			    	  
		      },		      
		 ]
	},
	{
		'key': '_viral_section',
		'fieldGroup': [
		       {
		    	   	'key': 'viral_duplicates',
	        		'type': 'radio',
	        		'templateOptions': {
	        			'type': 'radio',
	        			'label': 'Manage existing contacts already referred',
	        			'title': 'Choose action to use where it is found that a contact has been referred already',
	        			'valueProp': 'optionID',
	        			'labelProp': 'optionLabel',
	        			'required': true,
	        			'defaultValue': '',
	        			'options': [
	        			      {'optionID': 0, 'optionLabel': 'Ignore if already referred'},
	        			      {'optionID': 1, 'optionLabel': 'Link to every referrer'},
	        			      {'optionID': 2, 'optionLabel': 'Do not allow referral'},
	        			]
	        		},
	        		'validation': {
	        			'show': true,
	        		}
		       },
		       {
		    	   	'key': 'viral_referrals',
	        		'type': 'input',
	        		'templateOptions': {
	        			'type': 'number',
	        			'label': 'Number of referrals allowed per form',
	        			'placeholder': 'Enter number here. Minimum is 1, Maximum is 30',
	        			'title': 'Set the number of contacts that can be specified on a form',
	        			'required': false,
	        			'defaultValue': 4,
	        			'min': '1',
	        			'max': '30',
	        		},
	        		'validation': {
	        			'show': true,
	        		}
		       }
		 ]
	},
	{
		'key': '_tracker_section',
		'fieldGroup': [
		       {
		    	   
		       },
		 ]
	},
	{
		'key': '_cpp_section',
		'fieldGroup': [
		       {
		    	   
		       },
		]
	});
	
	return objFields;
};

/**
 * Helper to set behaviour form fields in proper order
 * @param objBehaviour
 * @param objFields
 * @returns
 */
function orderFormBehaviourFields(objBehaviour, objFields)
{
	var objBehaviourFields = Array();
	var objFieldConfig = {
		'form': {
			'__user_change': [
			                  						'description',
									                'fk_user_id', 
									                'loggedin', 
									                'active'
			                  ],
			'__registration_status_change': [					
		                  							'description',
		                     						"fk_reg_status_id",
		                    						"generic1",
		                    						"generic2",
		                    						"loggedin",
		                    						'active'
                    						],
			'__source_change': [
          											'description',
													"source",
													"content",
													"loggedin",
													'active'
			                    ],
			'__reference_change': [
             										'description',
													"reference",
													"content",
													"loggedin",
													'active'
			                       ],
			'__sales_funnel_add': [
             										'description',
							   						"fk_sales_funnel_id",
							   						"generic1",
							   						'active'
			                       ],
			'__journey_start': [
          											'description',
													"fk_journey_id",
													"loggedin",
													"generic1",	
													'active'
			                    ],
 			"__journey_stop": [
         											'description',
					 								"fk_journey_id",
					 								"loggedin", 
					 								'active'
 			                   ],
 			"__journey_start_viral_click": [
	                  								'description',
	 			                                	"fk_journey_id",
	 			                                	"loggedin", 
	 			                                	'active'
 			                                ],
 			"__task": [
 													'description',
													"content",
													"generic2",
													"fk_user_id", 	
													'active'
 			           ],
			'__viral_journey_link': [
               										'description',
						 							"fk_journey_id",
						 							'generic1',
						 							'active'
			                         ],
			'__viral_notification_journey': [
		                  							'description',
				         							"fk_journey_id",
				         							'generic1',	
				         							'active'
			                                 ],
 			"__sales_funnel_update": [
                									'description',
			 			                          	"fk_sales_funnel_id",
			 			                          	"generic1",
			 			                          	'active'
 			                          ],
 			"__sales_funnel_status_change": [
		                  								'description',
		 			                                 	"fk_form_id2",
		 			                                 	"field_value",
		 			                                 	'active'
 			                                 ],
 			"__reject_form_override_on_form_timestamp": [
 				                  							'description',
 			                                             	"fk_form_id2",
 			                    							"content", 
 			                    							'active'
 			                                             ],
  			"__reject_form": [
        												'description',
						  			                  	'field_value',
						  			                  	'active'
  			                  ],
		},
		'form_fields': {
			"__journey_start": 						[
				                  						'description',
				                   						"fk_journey_id",
				                   						"loggedin",
				                   						"generic1",
				                   						"field_value",
				                   						'active'
			                   						 ],
			"__journey_stop": 						[
				                  						'description',
				                  						"fk_journey_id",
				                  						"loggedin",
				                  						"field_operator",
				                  						"field_value",
				                  						'active',
			                  						 ],
			"__sales_funnel_add": 					[
				                  						'description',
				                 						"fk_sales_funnel_id",
				                						"generic1",
				                						"field_value",	
				                						'active'
			                      					 ],
			"__task": 								[
				                  						'description',
				          								"content",
				          								"fk_user_id",
	//			          			 					"email",
	//			          			 					"generic1",
				          								"generic2",
				          								"field_value",
				          								"fk_reg_status_id",
				          								'active'
			          								 ],
			"__task_email_blank": 					[
				                  						'description',
				                 						"content",
				                						"fk_user_id",
				                 						//"email",
				                 						//"generic1",
				                						"generic2",
				                						"fk_reg_status_id",
				                						'active'
			                      					 ],
			"__registration_status_change": 		[
				                  						'description',
				                 						"fk_reg_status_id",
				                						"generic1",
				                						"generic2",
				                						"loggedin",
				                						"field_value",
				                						'active'
			                                		 ],
			"__user_change": 						[
				                  						'description',
				                 						"fk_user_id",
				                 						"field_value",
				                 						"generic1",
				                 						'active'
			                 						 ],
			//"__cross_marketing": 					[],
			"__mandatory": 							[
				                  						'description',
				               							"field_operator",
				               							"fk_fields_all_id2",
				               							"field_value",
				               							'active'
			               							 ],
			"__populate": 							[
				                  						'description',
				              							"field_operator",
				              							"fk_fields_all_id2",
				              							"field_value",
				              							'active',
			              							 ],
			"__redirect_post_submit": 				[
				                  						'description',
				                     					"field_value",
				                    					"content",
				                    					'active',
			                          				 ],
			"__reject_form": 						[
				                  						'description',
			                 						 	"field_value",
			                 						 	"content",
			                 						 	'active'
			                 						 ],
			"__resubscribe": 						[
				                  						'description',
			                 						 	"field_value",
			                 						 	'active'
			                 						 ],
			"__email": 								[
				                  						'description',
			           								 	"email",
			           								 	"field_value",
			           								 	'active'
			           								 ],
			"__unsubscribe": 						[
				                  						'description',
			                 						 	"field_value",
			                 						 	'active'
			                 						 ],
			"__sales_funnel_update": 				[
				                  						'description',
				                     					"fk_sales_funnel_id",
				                    					"generic1",
				                    					"field_value",
				                    					'active',
			                         				 ],
			"__hide_value_journey_started": 		[
				                  						'description',
			                     						"fk_journey_id",
			                     						"field_value",
			                     						'active'
			                                		 ],			
		},
	};
	    
	if (typeof objFieldConfig[objBehaviour.behaviour] != 'undefined' && typeof objFieldConfig[objBehaviour.behaviour][objBehaviour.behaviour_action] != 'undefined')
	{
		angular.forEach(objFieldConfig[objBehaviour.behaviour][objBehaviour.behaviour_action], function (field, i) {
			//find field in collection
			angular.forEach(objFields, function (objField, ii) {
				if (objField.key == field)
				{
					objBehaviourFields.push(objField);
					//remove field from collection
					objFields.splice(ii, 1);
				}//end if
			});
		});
		
		//add any remaining fields
		if (typeof objFields != 'undefined' && objFields.length > 0)
		{
			angular.forEach(objFields, function (objField, ii) {
				objBehaviourFields.push(objField);
			});
		}//end if
	} else {
		//return fields as received
		objBehaviourFields = objFields;
	}//end if
	                        
	return objBehaviourFields;
}//end function