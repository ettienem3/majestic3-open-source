<?php
namespace FrontLocations\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController
{
	/**
	 * Container for the Front Locations Model
	 * @var \FrontLocations\Models\FrontLocationsModel
	 */
	protected $model_locations;
	
    public function indexAction()
    {
        return array();
    }//end function
    
    public function countriesAction()
    {
    	$objCountries = $this->getLocationsModel()->fetchCountries($this->params()->fromQuery());
    	return array("objCountries" => $objCountries);
    }//end function
    
    public function ajaxLoadCountriesAction()
    {
    	$objCountries = $this->getLocationsModel()->fetchCountries($this->params()->fromQuery());
    	return new JsonModel((array) $objCountries);
    }//end function
    
    public function provincesAction()
    {
    	$objProvinces = $this->getLocationsModel()->fetchProvinces($this->params()->fromQuery());
    	return array("objProvinces" => $objProvinces);
    }//end function
    
    public function ajaxLoadProvincesAction()
    {
    	$objProvinces = $this->getLocationsModel()->fetchProvinces($this->params()->fromQuery());
    	return new JsonModel((array) $objProvinces);
    }//end function
    
    public function citiesAction()
    {
    	$objCities = $this->getLocationsModel()->fetchCities($this->params()->fromQuery());
    	return array("objCities" => $objCities);
    }//end function
    
    public function ajaxLoadCitiesAction()
    {
    	$objCities = $this->getLocationsModel()->fetchCities($this->params()->fromQuery());
    	return new JsonModel((array) $objCities);
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
