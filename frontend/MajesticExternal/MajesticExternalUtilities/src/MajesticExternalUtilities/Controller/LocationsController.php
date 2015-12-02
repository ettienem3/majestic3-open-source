<?php
namespace MajesticExternalUtilities\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class LocationsController extends AbstractActionController
{
	/**
	 * Container for the External Utilities Model
	 * @var \MajesticExternalUtilities\Models\MajesticExternalUtilitiesModel
	 */
	private $model_external_utilities;
	
	public function ajaxCountriesAction()
	{
		//request data
		try {
			$objData = $this->getExternalUtilitiesModel()->loadLocations("countries");
			foreach ($objData as $k => $objCity)
			{
				if ($objCity->active != 1)
				{
					continue;	
				}//end if
				
				$arr_data[] = array(
					"id" 			=> $objCity->id,
					"country" 		=> $objCity->country,
					"code" 			=> $objCity->code,
				);
			}//end foreach
			
			echo json_encode(array(
				"error" => 0,
				"response" => $arr_data,
			), JSON_FORCE_OBJECT);
			exit;
		} catch (\Exception $e) {
			echo json_encode(array(
				"error" => 1,
				"response" => $e->getMessage(),
			), JSON_FORCE_OBJECT);
			exit;	
		}//end catch
	}//end function
	
	public function ajaxProvincesAction()
	{
		//request data
		try {
			$objData = $this->getExternalUtilitiesModel()->loadLocations("provinces", (array) $this->params()->fromQuery());
			$arr_data = array();
			foreach ($objData as $k => $objProvince)
			{
				if ($objProvince->active != 1)
				{
					continue;
				}//end if
	
				//load only a specific province
				if (is_numeric($this->params()->fromQuery("province_id", "")))
				{
					$id = $this->params()->fromQuery("province_id");
					if ($objProvince->id == $id)
					{
						//set data
						$arr_data = array();
						$arr_data[] = array(
								"id" 			=> $objProvince->id,
								"country_id" 	=> $objProvince->fk_countries_id,
								"country" 		=> $objProvince->country,
								"province" 		=> $objProvince->province,
								"state"			=> $objProvince->province,
								"code" 			=> $objProvince->code,
						);
						
						//exit loop
						break;
					}//end if
				}//end if
				
				$arr_data[] = array(
						"id" 			=> $objProvince->id,
						"country_id" 	=> $objProvince->fk_countries_id,
						"country" 		=> $objProvince->country,
						"province" 		=> $objProvince->province,
						"state"			=> $objProvince->province,
						"code" 			=> $objProvince->code,
				);
			}//end foreach
				
			echo json_encode(array(
					"error" => 0,
					"response" => $arr_data,
			), JSON_FORCE_OBJECT);
			exit;
		} catch (\Exception $e) {
			echo json_encode(array(
					"error" => 1,
					"response" => $e->getMessage(),
			), JSON_FORCE_OBJECT);
			exit;
		}//end catch
	}//end function
	
	public function ajaxCitiesAction()
	{
		//request data
		try {
			$objData = $this->getExternalUtilitiesModel()->loadLocations("cities", (array) $this->params()->fromQuery());
			$arr_data = array();
			foreach ($objData as $k => $objCity)
			{
				if ($objCity->active != 1)
				{
					continue;
				}//end if

				//load only a specific province
				if (is_numeric($this->params()->fromQuery("city_id", "")))
				{
					$id = $this->params()->fromQuery("city_id");
					if ($objCity->id == $id)
					{
						//set data
						$arr_data = array();
						$arr_data[] = array(
								"id" 			=> $objCity->id,
								"country_id" 	=> $objCity->fk_countries_id,
								"country" 		=> $objCity->country,
								"province_id"	=> $objCity->fk_provinces_id,
								"province" 		=> $objCity->province,
								"state"			=> $objCity->province,
								"city" 			=> $objCity->city,
						);
							
						//exit loop
						break;
					}//end if
				}//end if
				
				$arr_data[] = array(
						"id" 			=> $objCity->id,
						"country_id" 	=> $objCity->fk_countries_id,
						"country" 		=> $objCity->country,
						"province_id"	=> $objCity->fk_provinces_id,
						"province" 		=> $objCity->province,
						"state"			=> $objCity->province,
						"city" 			=> $objCity->city,
				);
			}//end foreach
		
			echo json_encode(array(
					"error" => 0,
					"response" => $arr_data,
			), JSON_FORCE_OBJECT);
			exit;
		} catch (\Exception $e) {
			echo json_encode(array(
					"error" => 1,
					"response" => $e->getMessage(),
			), JSON_FORCE_OBJECT);
			exit;
		}//end catch
	}//end function
	
	/**
	 * Create an instance of the External Utilities Model using the Service Manager
	 * @return \MajesticExternalUtilities\Models\MajesticExternalUtilitiesModel
	 */
	private function getExternalUtilitiesModel()
	{
		if (!$this->model_external_utilities)
		{
			$this->model_external_utilities = $this->getServiceLocator()->get("MajesticExternalUtilities\Models\MajesticExternalUtilitiesModel");
		}//end if
		
		return $this->model_external_utilities;
	}//end function
}//end class