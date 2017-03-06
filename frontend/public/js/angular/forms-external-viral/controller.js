'use strict';

var viralFormControllers = angular.module('viralFormControllers', []);

viralFormControllers.controller('ViralHomeCtrl', ['$scope', '$route', '$routeParams', '$window', '$compile', 'ViralFormPageAppServices', 'promiseTracker', function ViralHomeCtrl($scope, $route, $routeParams, $window, $compile, ViralFormPageAppServices, promiseTracker, formlyVersion) {	
	//set some values
	if (typeof global_viral_form_config.objFormRawData.viral_referrals == "undefined" || global_viral_form_config.objFormRawData.viral_referrals == 0)
	{
		$scope.max_form_referrals_allowed = 4;
	} else {
		$scope.max_form_referrals_allowed = global_viral_form_config.objFormRawData.viral_referrals;	
	}//end if
	
	$scope.form_message = '';
	$scope.global_wait_image = global_wait_image;
	
	// Inititate the promise tracker to track form submissions.
	$scope.progress = promiseTracker();
	
	$scope.form_referral_groups = 0;
	$scope.form_fields = global_viral_form_config.objForm;
	
	$scope.vm = this;
	$scope.vm.fields = [];
	$scope.vm.model = {};
	
	$scope.submitForm = function () {
		//add form configured total referrals to the data
		$scope.form_message = 'Processing data ' + $scope.global_wait_image;
		jQuery('html, body').animate({ scrollTop: 0 }, 500);
		
		$scope.vm.model._max_form_referrals_allowed = $scope.max_form_referrals_allowed;
		var objData = $scope.vm.model;
		objData.acrq = 'submit-data';
		objData._form_id = global_viral_form_config.objFormRawData.id;
		objData.reg_id = global_viral_additional_params.reg_id;
	
		var $promise = ViralFormPageAppServices.post(objData,
			function success(response) {
				logToConsole(response);
				if (typeof response.error != 'undefined' && response.error == 1)
				{
					$scope.form_message = response.response;
					return false;
				}//end if
				
				angular.element('#form_submit_message_container').removeClass('alert-danger').removeClass('alert-info').addClass('alert-success');
				$scope.form_message = 'Thank you!';
				
				//clear form values
				$scope.vm.model = {};
				
				//check if any post submit directives has been allocated via custom javascript
				if (typeof referralPostSubmitDirective == 'function') 
				{ 
					referralPostSubmitDirective(
							{
								'contact_id': global_viral_additional_params.reg_id,
								'form_id': global_viral_form_config.objFormRawData.id
							}
						); 
				}//end if
			},
			function errorResponse(errorResponse) {
				logToConsole(errorResponse);
				
				$scope.form_message = 'An unknown error occurred, form could not be submitted, please try again.';
				return false;
			}
		);
		
		// Track the request and show its progress to the user.
		$scope.progress.addPromise($promise);
	}; //end function
	
	//add fields on load
	addFieldGroup();
	
	//add fields on button click
	$scope.addFieldGroup = function() {
		return addFieldGroup();
	}; //end function
	
	//remove fields on button click
	$scope.removeFieldGroup = function (id) {
		return removeFieldGroup(id);
	};//end function
	
	function removeFieldGroup(id) {
		console.log('remove field ' + id);
		angular.forEach($scope.vm.fields, function (objField, i) {
			var arr_field = objField.fieldGroup[0].key.split('_');
			if (arr_field[0] == id)
			{
				$scope.vm.fields.splice(i);
				//update field group count
				$scope.form_referral_groups--;
				
				//remove fields from the model
				$scope.vm.model['_total_field_groups'] = $scope.form_referral_groups;
				angular.forEach($scope.vm.model, function(field, ii) {
					var arr_field = ii.split('_');
					if (arr_field[0] == id)
					{
						delete $scope.vm.model[ii];
					}//end if
				});
			}//end if
		});
		
	}; //end function
	
	function addFieldGroup() {
		//increment the count
		$scope.form_referral_groups++;
		
		//set field group
		//http://angular-formly.com/#/example/bootstrap-specific/advanced-layout
		var objFieldGroup = {
				className: 'row',
				fieldGroup: []
		};
		
		angular.forEach($scope.form_fields, function(objField, i) {
			var field = angular.copy(objField);
			field.key = $scope.form_referral_groups + '_' + field.key;
			
			//add the field
			objFieldGroup.fieldGroup.push(field);
			$scope.vm.model[field.key] = '';
		});
		
		//update field group count
		$scope.vm.model['_total_field_groups'] = $scope.form_referral_groups;
		
		//add field group seperator
		var objFieldGroupSeperator = {
				className: 'section-label',
				template: '<span id="removeFieldGroup_' + $scope.form_referral_groups + '">...</span>',
		};
		objFieldGroup.fieldGroup.push(objFieldGroupSeperator);
		
		//add field group to form
		$scope.vm.fields.push(objFieldGroup);	
		
		//add remove button
		setTimeout(function() {
			var button_html = '<button ng-click="removeFieldGroup(' + $scope.form_referral_groups + ')" class="ng-scope btn btn-danger form-remove-field-group"><span class="glyphicon glyphicon-minus-sign"></span></button>';
			var compiledHtml = $compile(button_html)($scope);
			angular.element('#removeFieldGroup_' + $scope.form_referral_groups).html(compiledHtml);
		}, 1000);
	}; //end function
}]);