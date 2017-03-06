<?php
namespace FrontFormAdmin\Entities;

use FrontCore\Adapters\AbstractEntityAdapter;

class FrontFormAdminFormFieldEntity extends AbstractEntityAdapter
{
	/**
	 * Container for the Locations Model
	 * @var \FrontLocations\Models\FrontLocationsModel
	 */
	private $model_locations;

	/**
	 * Container for the cache model
	 * @var \FrontCore\Caches\FrontCachesRedis
	 */
	private $model_cache_redis;

	/**
	 * Set default content options for a field
	 * @param array $arr_params
	 */
	public function setFormFieldDefaulfContent(array $arr_params)
	{
		switch (strtolower($arr_params['field_type']))
		{
			case 'standard':
				$this->setStandardFieldDefaultContent($arr_params);
				break;

			case 'custom':
				$this->setCustomFieldDefaultContent();
				break;
		}//end switch

	}//end function

	private function setStandardFieldDefaultContent($arr_params)
	{	
		switch ($this->get('fields_std_field'))
		{
			case 'country_id':
				if ($this->get('fields_std_input_type') == 'select' || $this->get('fields_std_input_type') == 'radio')
				{
					//check if data is cached
					$cache_id = 'form-field-default-content-countries';
					$arr_countries = $this->getCacheModel()->readCacheItem($cache_id, FALSE);
					if (!$arr_countries)
					{
						//load countries details
						$objCountries = $this->getLocationsModel()->fetchCountries(array(
								'qp_limit' => 'all',
								'qp_disable_hypermedia' => 1,
								'qp_export_fields' => 'id,country,active',
						));
						$arr_countries = array();
						foreach ($objCountries as $objCountry)
						{
							if ($objCountry->active == 1)
							{
								$arr_countries[$objCountry->id] = str_replace("'", "", utf8_encode($objCountry->country));
							}//end if
						}//end foreach
						
						//cache the dataset.
						$this->getCacheModel()->setCacheItem($cache_id, $arr_countries, array('ttl' => 86400));
					}//end if
				}//end if

				$this->set('default_content_replacement', $arr_countries);
				break;

				case 'province_id':
					if ($this->get('fields_std_input_type') == 'select' || $this->get('fields_std_input_type') == 'radio')
					{
						//check if data is cached
						$cache_id = 'form-field-default-content-regions';
						$arr_data = $this->getCacheModel()->readCacheItem($cache_id, FALSE);
						if (!$arr_data)
						{
							//load region details
							$objRegions = $this->getLocationsModel()->fetchProvinces(array(
									'qp_limit' => 'all',
									'qp_disable_hypermedia' => 1,
									'qp_export_fields' => 'id,city,active',
							));

							$arr_data = array();
							foreach ($objRegions as $objData)
							{
								if ($objData->active == 1)
								{
									$arr_data[$objData->id] = str_replace("'", "", utf8_encode($objData->city));
								}//end if
							}//end foreach
						
							//cache the dataset.
							$this->getCacheModel()->setCacheItem($cache_id, $arr_data, array('ttl' => 86400));
						}//end if
					}//end if

					$this->set('default_content_replacement', $arr_data);
					break;

				case 'city_id':
					if ($this->get('fields_std_input_type') == 'select' || $this->get('fields_std_input_type') == 'radio')
					{
						//check if data is cached
						$cache_id = 'form-field-default-content-cities';
						$arr_data = $this->getCacheModel()->readCacheItem($cache_id, FALSE);
						if (!$arr_data)
						{

							//load cities details
							$objCities = $this->getLocationsModel()->fetchCities(array(
									'qp_limit' => 'all',
									'qp_disable_hypermedia' => 1,
									'qp_export_fields' => 'id,city,active',
							));

							$arr_data = array();
							foreach ($objCities as $objData)
							{
								if ($objData->active == 1)
								{
									$arr_data[$objData->id] = str_replace("'", "", utf8_encode($objData->city));
								}//end if
							}//end foreach
							
							//cache the dataset.
							$this->getCacheModel()->setCacheItem($cache_id, $arr_data, array('ttl' => 86400));
						}//end if
					}//end if

					$this->set('default_content_replacement', $arr_data);
					break;
					
				case 'user_id':
					//check if data is cached
					$cache_id = 'form-field-default-content-users';
					$arr_data = $this->getCacheModel()->readCacheItem($cache_id, FALSE);
					if (!$arr_data)
					{
						//load users
						$objUsers = $this->getUsersModel()->fetchUsers(array(
								'qp_limit' => 'all',
								'qp_disable_hypermedia' => 1,
								'qp_export_fields' => 'id,uname,fname,sname,active',
						));
					
						$arr_data = array();
						foreach ($objUsers as $objData)
						{
							if ($objData->active == 1)
							{
								$arr_data[$objData->id] = str_replace("'", "", utf8_encode($objData->uname));
							}//end if
						}//end foreach
						
						//cache the dataset.
						$this->getCacheModel()->setCacheItem($cache_id, $arr_data, array('ttl' => 3600));
					}//end if
					
					$this->set('default_content_replacement', $arr_data);
					break;
		}//end switch
	}//end function

	private function setCustomFieldDefaultContent($arr_params)
	{

	}//end function


	/**
	 * Create an instance of the Locations Model using the Service Manager
	 * @return \FrontLocations\Models\FrontLocationsModel
	 */
	private function getLocationsModel()
	{
		if (!$this->model_locations)
		{
			$this->model_locations = $this->getServiceLocator()->get('FrontLocations\Models\FrontLocationsModel');
		}//end if

		return $this->model_locations;
	}//end function
	
	/**
	 * Create an instance of the Users Model
	 * @return \FrontUsers\Models\FrontUsersModel
	 */
	private function getUsersModel()
	{
		return $this->getServiceLocator()->get('FrontUsers\Models\FrontUsersModel');
	}//end function

	/**
	 * Create an instance of the Cache Model
	 * @return \FrontCore\Caches\FrontCachesRedis
	 */
	private function getCacheModel()
	{
		if (!$this->model_cache_redis)
		{
			$this->model_cache_redis = $this->getServiceLocator()->get('FrontCore\Caches\FrontCachesRedis');
		}//end if

		return $this->model_cache_redis;
	}//end function
}//end class