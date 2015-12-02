<?php
namespace FrontCore\Models;

use Zend\Form\Form;
use Zend\Form\Element;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontCore\Forms\FrontCoreSystemFormBase;
use Zend\Cache\StorageFactory;

class SystemFormsModel extends AbstractCoreAdapter
{
	/**
	 * Container for adding input filters and validators to forms
	 * @var array
	 */
	private $arr_input_filters;

	/**
	 * Get a list of available system forms
	 * @return \FrontCore\Models\ApiRequestModel
	 */
	public function getSystemForms()
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/system");

		//perform the request
		$objForms = $objApiRequest->performGETRequest()->getBody();

		return $objForms;
	}//end function

	/**
	 * Request a system form from the API and create the form object.
	 * This only creates the form object and not an input validators.
	 * @param string $namespace
	 * @param array $arr_form_attributes - Optional
	 * @return \Zend\Form\Form
	 */
	public function getSystemForm($namespace, $arr_form_attributes = NULL, $arr_options = NULL)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		/**
		 * Load form from cache where applicable
		 */
		if (is_null($arr_form_attributes) && is_null($arr_options))
		{
			$objFormData = $this->readFormCache($namespace);

			if (is_object($objFormData) || is_array($objFormData))
			{
				return $this->constructForm($objFormData, $arr_form_attributes);
			}//end if
		}//end if

		$additional_params_string = "";
		if (is_array($arr_options))
		{
			foreach ($arr_options as $key => $value)
			{
				$additional_params_string .= "$key=$value&";
			}//end foreach
			$additional_params_string = rtrim("&" . $additional_params_string, "&");
		}//end if

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/system/load/1?form_namespace=$namespace" . $additional_params_string);

		//load the form
		$objForm = $objApiRequest->performGETRequest(array("id" => time()))->getBody();

		//save form data to cache
		if (is_null($arr_form_attributes) && is_null($arr_options))
		{
			$this->setFormCache($namespace, $objForm->data);
		}//end if

		$objForm = $this->constructForm($objForm->data, $arr_form_attributes);
		return $objForm;
	}//end function

	/**
	 * Allows to create a valid systems form obtained from external source to the System Forms Channel
	 * @param StdClass $objForm
	 * @param array $arr_form_attributes - Optional
	 * @return \Zend\Form\Form
	 */
	public function constructCustomForm($objForm, $arr_form_attributes = NULL)
	{
		$objForm = $this->constructForm($objForm, $arr_form_attributes = NULL);
		return $objForm;
	}//end functoin

	/**
	 * Build a Zend form object from data received
	 * @param object $objFormData
	 * @param array $arr_form_attributes
	 * @return \Zend\Form\Form
	 */
	private function constructForm($objFormData, $arr_form_attributes)
	{
		//set some default values
		if (!isset($arr_form_attributes["name"]))
		{
			$arr_form_attributes["name"] = "form";
		}//end if

		//create the form object
		$objForm = new FrontCoreSystemFormBase($arr_form_attributes["name"]);

		//check if field groups has been set
		if(isset($objFormData->arr_field_groups))
		{
			$objForm->setAttribute("arr_field_groups", $objFormData->arr_field_groups);
			//remove field groups
			unset($objFormData->arr_field_groups);
		}//end if

		foreach ($objFormData as $key => $objElement)
		{
			//create the element
			$element_type = strtolower($objElement->attributes->type);
			$arr_element = (json_decode(json_encode($objElement), true));

			//refine some information
			$arr_element["name"] = $arr_element["attributes"]["name"];
			unset($arr_element["attributes"]["name"]);
			$arr_element["type"] = $arr_element["attributes"]["type"];
			unset($arr_element["attributes"]["type"]);

			//set value from default value
			if (isset($arr_element["attributes"]["default_value"]))
			{
				$arr_element["attributes"]["value"] = $arr_element["attributes"]["default_value"];
				unset($arr_element["attributes"]["default_value"]);
			}//end if

			//apply conditions where required
			switch ($arr_element["type"])
			{
				case "submit":
					$arr_element["attributes"]["value"] = "Submit";
					break;
			}//end swtich

			$objForm->add($arr_element);
		}//end foreach

		return $objForm;
	}//end function

	/**
	 * Load form from cache
	 * @param string $namespace
	 * @return \Zend\Cache\Storage\Adapter\mixed
	 */
	private function readFormCache($namespace)
	{
		//is system form cache enabled?
		if ($this->getServiceLocator()->get("config")["front_end_application_config"]["cache_enabled_system_forms"] !== TRUE)
		{
			return FALSE;
		}//end if

		return $this->setupSystemFormCache()->readCacheItem("system-form-" . str_replace("\\", "-", $namespace));
	}//end function

	/**
	 * Save for to cache
	 * @param string $namespace
	 * @param object $objData
	 */
	private function setFormCache($namespace, $objData)
	{
		//is system form cache enabled?
		if ($this->getServiceLocator()->get("config")["front_end_application_config"]["cache_enabled_system_forms"] !== TRUE)
		{
			return FALSE;
		}//end if

		$this->setupSystemFormCache()->setCacheItem("system-form-" . str_replace("\\", "-", $namespace), $objData, array("ttl" => (60 * 5)));
	}//end function

	/**
	 * Remove an item from the cache
	 * @param string $namespace
	 * @return boolean
	 */
	public function clearFormCache($namespace)
	{
		//is system form cache enabled?
		if ($this->getServiceLocator()->get("config")["front_end_application_config"]["cache_enabled_system_forms"] !== TRUE)
		{
			return FALSE;
		}//end if

		$this->setupSystemFormCache()->clearItem("system-form-" . str_replace("\\", "-", $namespace));
	}//end function

	/**
	 * Create system form cache mechanism
	 * @throws \Exception
	 * @return \FrontCore\Caches\Cache
	 */
	private function setupSystemFormCache()
	{
		if (!$this->cache)
		{
			$cache = $this->getServiceLocator()->get("FrontCore\Caches\Cache");
			$this->cache = $cache;
		}//end if

		return $this->cache;
	}//end function
}//end class