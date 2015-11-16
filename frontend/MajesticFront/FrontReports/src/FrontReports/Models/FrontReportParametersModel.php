<?php
namespace FrontReports\Models;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontReportParametersModel extends AbstractCoreAdapter
{
	/**
	 * Container for the Contact System Fields Model
	 * @var \FrontContacts\Models\FrontContactsSystemFieldsModel
	 */
	private $model_front_contacts_system_fields;

	/**
	 * Container for the Links Model
	 * @var \FrontLinks\Models\FrontLinksModel
	 */
	private $model_front_links;

	/**
	 * Container for the Comms Admin Journeys Model
	 * @var \FrontCommsAdmin\Models\FrontJourneysModel
	 */
	private $model_comms_admin_journeys;

	/**
	 * Container for the Comms Admin Comms Model
	 * @var \FrontCommsAdmin\Models\FrontCommsAdminModel
	 */
	private $model_comms_admin_comms;

	/**
	 * Container for the Users Model
	 * @var \FrontUsers\Models\FrontUsersModel
	 */
	private $model_users;

	/**
	 * Container for the Statuses Model
	 * @var \FrontStatuses\Models\FrontContactStatusesModel
	 */
	private $model_statuses;

	/**
	 * Container for the Locations Model
	 * @var \FrontLocations\Models\FrontLocationsModel
	 */
	private $model_locations;

	public function generateFieldContent($field)
	{
		if (method_exists($this, "generate_" . $field))
		{
			$arr = $this->{"generate_" . $field}();
			return $arr;
		}//end if

		return FALSE;
	}//end function

	private function generate_global_source()
	{
		$objSources = $this->getContactsSystemFieldsModel()->fetchDistinctContactSources();

		$element = "<select name=\"#global_source\" class=\"form-control\">";
		$element .= 		"<option value=''>--select--</option>";

		foreach ($objSources as $objSource)
		{
			if ($objSource->source == "")
			{
				continue;
			}//end if

			$element .= "<option value='$objSource->source'>$objSource->source</option>";
		}//end foreach
		$element .= "</select>";
		return $element;
	}//end function

	private function generate_global_reference()
	{
		$objReferences = $this->getContactsSystemFieldsModel()->fetchDistinctContactReferences();

		$element = "<select name=\"#global_reference\" class=\"form-control\">";
		$element .= 		"<option value=''>--select--</option>";

		foreach ($objReferences as $objReference)
		{
			if ($objReference->reference == "")
			{
				continue;
			}//end if

			$element .= "<option value='$objReference->reference'>$objReference->reference</option>";
		}//end foreach
		$element .= "</select>";
		return $element;
	}//end function

	private function generate_global_link()
	{
		$objLinks = $this->getLinksModel()->fetchLinks();

		$element = "<select name=\"#global_link\" class=\"form-control\">";
		$element .= 		"<option value=''>--select--</option>";

		foreach ($objLinks as $objLink)
		{
			if (!is_numeric($objLink->id))
			{
				continue;
			}//end if

			$element .= "<option value='$objLink->id'>$objLink->link</option>";
		}//end foreach
		$element .= "</select>";
		return $element;
	}//end function

	private function generate_global_journey_id()
	{
		$objJourneys = $this->getJourneysModel()->fetchJourneys();


		$element = "<select name=\"#global_link\" class=\"form-control\">";
		$element .= 		"<option value=''>--select--</option>";

		$arr_journeys = array();
		foreach ($objJourneys as $objJourney)
		{
			if (!is_numeric($objJourney->id))
			{
				continue;
			}//end if

			$element .= "<option value='$objJourney->id'>$objJourney->journey</option>";

			//add data for js array
			$arr_journeys[$objJourney->id] = array(
					"id" => $objJourney->id,
					"journey" => str_replace("'", "", $objJourney->journey)
				);
		}//end foreach

		$element .= "</select>";

		//add javascript
		$element .= '<script type="text/javascript">
						var report_data_journeys = "";
						jQuery(document).ready(function () {
							report_data_journeys = jQuery.parseJSON(\'' . json_encode($arr_journeys) . '\');
						});
		 			</script>';

		return $element;
	}//end function

	private function generate_global_comm_id()
	{
 		$objComms = $this->getCommsModel()->fetchCommsAdmin(array("journey_id" => "all"));

 		$element = "<select id=\"helper_global_comm_id\" class=\"form-control\">";
 		$element .= "<option value=''>Select Journey</option></select>";
 		$element .= "<select name=\"#global_comm_id\" class=\"form-control\">";
 		$element .= 		"<option value=''>--select--</option>";

 		$arr_comms = array();
 		$arr_journeys = array();
 		foreach ($objComms as $objComm)
 		{
 			if (!is_numeric($objComm->id))
 			{
 				continue;
 			}//end if

 			$element .= "<option value='$objComm->id'>" . $objComm->comm_num . " " . $objComm->subject . "</option>";

 			$arr_journeys[$objComm->journey_id] = array(
 				"id" => $objComm->journey_id,
 				"journey" => str_replace("'", "", $objComm->journey),
 			);

 			//add data for js array
 			$arr_comms[$objComm->journey_id][] = array(
 				"id" => $objComm->id,
 				"comm_num" => "Number ". $objComm->comm_num,
 				//"subject" => str_replace(array(".", "}", "{", "\\", "'", "(", ")"), "", $objComm->subject),
 			);
 		}//end foreach

 		$element .= "</select>";

 		//add javascript
 		$element .= '<script type="text/javascript">
						var report_data_communications = "";
 						var report_data_journeys = "";

						jQuery(document).ready(function () {
							report_data_communications = jQuery.parseJSON(\'' . json_encode($arr_comms) . '\');
							report_data_journeys = jQuery.parseJSON(\'' . json_encode($arr_journeys) . '\');

							//populate the journeys dropdown
							jQuery.each(report_data_journeys, function (id, obj) {
								jQuery("#helper_global_comm_id").append(jQuery("<option></option>").val(id).text(obj.journey));
							});

							//monitor the journeys dropdown for changes
							jQuery("#helper_global_comm_id").change(function () {
								jQuery("[name=\'#global_comm_id\']").empty();

								jQuery.each(report_data_communications[jQuery(this).val()], function (i, obj) {
									jQuery("[name=\'#global_comm_id\']").append(jQuery("<option></option>").val(obj.id).text(obj.comm_num));
								});
							});
						});
		 			</script>';

 		return $element;
	}//end function

	private function generate_global_comm_status()
	{
		$objStatuses = $this->getCommsModel()->fetchCommunicationStatusList();

		$element = "<select name=\"#global_comm_status\" class=\"form-control\">";
		$element .= 		"<option value=''>--select--</option>";

		foreach ($objStatuses as $objStatus)
		{
			if (!is_numeric($objStatus->id))
			{
				continue;
			}//end if

			$element .= "<option value='$objStatus->id'>" . $objStatus->code . " " . $objStatus->code_desc . "</option>";
		}//end foreach
		$element .= "</select>";
		return $element;
	}//end function

	private function generate_global_start_date()
	{
		$element = "<input type=\"text\" name=\"#global_start_date\" value=\"\" readonly=\"readonly\" class=\"form-control\"/>";

		//add javascript
		$element .= '<script type=\"text/javascript\">
						jQuery(document).ready(function () {
							jQuery("[name=\'#global_start_date\']").datepicker({
								format: "yyyy-mm-dd",
								clearBtn: true,
								todayHighlight: true,
								autoclose: true,
								todayBtn: true
							});
						});
					</script>';

		return $element;
	}//end function

	private function generate_global_end_date()
	{
		$element = "<input type=\"text\" name=\"#global_end_date\" value=\"\" readonly=\"readonly\" class=\"form-control\"/>";

		//add javascript
		$element .= '<script type=\"text/javascript\">
						jQuery(document).ready(function () {
							jQuery("[name=\'#global_end_date\']").datepicker({
								format: "yyyy-mm-dd",
								clearBtn: true,
								todayHighlight: true,
								autoclose: true,
								todayBtn: true
							});
						});
					</script>';

		return $element;
	}//end function

	private function generate_global_year()
	{
		$element = "<input type=\"text\" name=\"#global_year\" value=\"\" readonly=\"readonly\" class=\"form-control\"/>";

		//add javascript
		$element .= '<script type=\"text/javascript\">
						jQuery(document).ready(function () {
							jQuery("[name=\'#global_year\']").datepicker({
								format: "yyyy",
								clearBtn: true,
								todayHighlight: true,
								startView: "year",
								minViewMode: "years",
								autoclose: true
							});
						});
					</script>';

		return $element;
	}//end function

	private function generate_global_year_month()
	{
		$element = "<input type=\"text\" name=\"#global_year\" value=\"\" readonly=\"readonly\" class=\"form-control\"/>";

		//add javascript
		$element .= '<script type=\"text/javascript\">
						jQuery(document).ready(function () {
							jQuery("[name=\'#global_year\']").datepicker({
								format: "yyyy-mm",
								clearBtn: true,
								todayHighlight: true,
								startView: "month",
								minViewMode: "months",
								autoclose: true
							});
						});
					</script>';

		return $element;
	}//end function

	private function generate_global_user_id()
	{
		$objUsers = $this->getUsersModel()->fetchUsers();

		$element = "<select name=\"#global_user_id\" class=\"form-control\">";
		$element .= 		"<option value=''>--select--</option>";

		foreach ($objUsers as $objUser)
		{
			if (!is_numeric($objUser->id))
			{
				continue;
			}//end if

			$element .= "<option value='$objUser->id'>" . $objUser->uname . " (" . $objUser->fname . " " . $objUser->sname . ")</option>";
		}//end foreach
		$element .= "</select>";
		return $element;
	}//end function

	private function generate_global_contact_status()
	{
		$element = $this->generate_global_reg_status_id();
		$element = str_replace("global_reg_status_id", "global_contact_status", $element);
		return $element;
	}//end function

	private function generate_global_reg_status_id()
	{
		$objStatuses = $this->getStatusesModel()->fetchContactStatuses();

		$element = "<select name=\"#global_reg_status_id\" class=\"form-control\">";
		$element .= 	"<option value=''>--select--</option>";

		foreach ($objStatuses as $objStatus)
		{
			if (!is_numeric($objStatus->id))
			{
				continue;
			}//end if

			$element .= "<option value='$objStatus->id'>" . $objStatus->status . "</option>";
		}//end foreach
		$element .= "</select>";
		return $element;
	}//end function

	private function generate_global_country_id()
	{
		$objLocations = $this->getLocationsModel()->fetchCountries();

		$element = "<select name=\"#global_countery_id\" class=\"form-control\">";
		$element .= 	"<option value=''>--select--</option>";

		foreach ($objLocations as $objLocation)
		{
			if (!is_numeric($objLocation->id))
			{
				continue;
			}//end if

			$element .= "<option value='$objLocation->id'>" . $objLocation->country . " (" . $objLocation->code . ")</option>";
		}//end foreach
		$element .= "</select>";

		return $element;
	}//end functionm

	private function generate_global_province_id()
	{
		$objLocations = $this->getLocationsModel()->fetchProvinces();

		$element = "<select name=\"#global_province_id\" class=\"form-control\">";
		$element .= 	"<option value=''>--select--</option>";

		$arr_provinces = array();
		foreach ($objLocations as $objLocation)
		{
			if (!is_numeric($objLocation->id))
			{
				continue;
			}//end if

			$element .= "<option value='$objLocation->id'>" . $objLocation->province . "</option>";

			$arr_provinces[$objLocation->fk_countries_id][] = array(
				"id" => $objProvince->id,
				"province" => $objProvince->province,
			);
		}//end foreach
		$element .= "</select>";

		//add javascript
		$element .= '<script type="text/javascript">
						var report_data_locations_province = "";
						jQuery(document).ready(function () {
							report_data_locations_province = jQuery.parseJSON(\'' . json_encode($arr_provinces) . '\');
						});
		 			</script>';

		return $element;
	}//end function

	/**
	 * Create an instance of the Front Contact System Fields model using the Service Manager
	 * @return \FrontContacts\Models\FrontContactsSystemFieldsModel
	 */
	private function getContactsSystemFieldsModel()
	{
		if (!$this->model_front_contacts_system_fields)
		{
			$this->model_front_contacts_system_fields = $this->getServiceLocator()->get("FrontContacts\Models\FrontContactsSystemFieldsModel");
		}//end if

		return $this->model_front_contacts_system_fields;
	}//end function

	/**
	 * Create an instance of the Front Links Model using the Service Manager
	 * @return \FrontLinks\Models\FrontLinksModel
	 */
	private function getLinksModel()
	{
		if (!$this->model_front_links)
		{
			$this->model_front_links = $this->getServiceLocator()->get("FrontLinks\Models\FrontLinksModel");
		}//end if

		return $this->model_front_links;
	}//end function

	/**
	 * Create an instance of the Journeys Model using the Service Manager
	 * @return \FrontCommsAdmin\Models\FrontJourneysModel
	 */
	private function getJourneysModel()
	{
		if (!$this->model_comms_admin_journeys)
		{
			$this->model_comms_admin_journeys = $this->getServiceLocator()->get("FrontCommsAdmin\Models\FrontJourneysModel");
		}//endif

		return $this->model_comms_admin_journeys;
	}//end function

	/**
	 * Create an instance of the Comms Admin Model using the Service Manager
	 * @return \FrontCommsAdmin\Models\FrontCommsAdminModel
	 */
	private function getCommsModel()
	{
		if (!$this->model_comms_admin_comms)
		{
			$this->model_comms_admin_comms = $this->getServiceLocator()->get("FrontCommsAdmin\Models\FrontCommsAdminModel");
		}//end if

		return $this->model_comms_admin_comms;
	}//end if

	/**
	 * Create an instance of the Users Model using the Service Maznager
	 * @return \FrontUsers\Models\FrontUsersModel
	 */
	private function getUsersModel()
	{
		if (!$this->model_users)
		{
			$this->model_users = $this->getServiceLocator()->get("FrontUsers\Models\FrontUsersModel");
		}//end if

		return $this->model_users;
	}//end function

	/**
	 * Create an instance of the Contact Statuses Model using the Service Manager
	 * @return \FrontStatuses\Models\FrontContactStatusesModel
	 */
	private function getStatusesModel()
	{
		if (!$this->model_statuses)
		{
			$this->model_statuses = $this->getServiceLocator()->get("FrontStatuses\Models\FrontContactStatusesModel");
		}//end if

		return $this->model_statuses;
	}//end function

	/**
	 * Create an instance of the Front Locations Model using the Service Manager
	 * @return \FrontLocations\Models\FrontLocationsModel
	 */
	private function getLocationsModel()
	{
		if (!$this->model_locations)
		{
			$this->model_locations = $this->getServiceLocator()->get("FrontLocations\Models\FrontLocationsModel");
		}//end if

		return $this->model_locations;
	}//end function
}//end class