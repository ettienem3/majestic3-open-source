<?php
namespace FrontCore\Adapters;

/**
 * This provides a common setup for Entity objects.
 * @author ettiene
 *
 */
abstract class AbstractEntityAdapter extends AbstractCoreAdapter
{
	/**
	 * Container for data
	 * @var object
	 */
	protected $objData = FALSE;

	/**
	 * Flag to indicate if data has been changed since it has been loaded
	 * @var boolean
	 */
	protected $flag_data_changed = FALSE;

	/**
	 * Container for entitry hyper media
	 * @var object
	 */
	protected $objHyperMedia = FALSE;

	/**
	 * Container for the User Date Format Helper
	 * @var \FrontCore\ViewHelpers\FrontFormatUserDateHelper
	 */
	protected $objUserDateFormatHelper;

	/**
	 * Save object hypermedia to seperate section
	 * @param object $objHyperMedia
	 */
	protected function setHyperMedia($objHyperMedia)
	{
		if (is_array($objHyperMedia) && isset($objHyperMedia[0]))
		{
			$objHyperMedia = $objHyperMedia[0];
		}//end if

		$this->objHyperMedia = $objHyperMedia;
	}//end function

	/**
	 * Used to receive data from database resultsets and setup the object
	 * @param array $arr_data
	 */
	public function exchangeArray($arr_data)
	{
		if (!is_array($this->arr_exclude_fields))
		{
			//create an empty array to prevent possible errors
			$this->arr_exclude_fields = array();
		}//end if

		foreach ($arr_data as $key => $value)
		{
			//compare values against preset array should values be excluded from db data
			if (!in_array($key, $this->arr_exclude_fields))
			{
				$arr[$key] = $value;
			}//end if
		}//end foreach

		$this->objData = (object) $arr;

		//encode primary key
		if (!isset($this->objData->id))
		{
			$this->objData->id = '';
		}//end if

		$this->objData->id_encoded = $this->objData->id . "_encoded";
	}//end function

	/**
	 * Returns Entity key data as an array.
	 * This is required by forms, but has other practical applications as well.
	 * @throws \Exception
	 * @return array
	 */
	public function getArrayCopy()
	{
		if (!is_object($this->objData))
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Data is not set", 500);
		}//end if

		//make sure numeric values are not returned in strings
		foreach ($this->objData as $field => $value)
		{
			if (is_numeric($value))
			{
				$this->objData->$field = $value * 1;
			}//end if
		}//end foreach
		
		return (array) $this->objData;
	}//end function

	/**
	 * Return data as array for submission to the API
	 * @return array
	 */
	public function getDataForSubmit()
	{
		return $this->getArrayCopy();
	}//end function

	/**
	 * Check if data has been amended since data has been loaded.
	 * Useful to reduce database writes
	 * @return boolean
	 */
	public function hasDataChanged()
	{
		return $this->flag_data_changed;
	}//end function

	/**
	 * Magic method for requesting params.
	 * Proxies get() function
	 * @param string $key
	 * @return \Core\Adapters\mixed
	 */
	public function __get($key)
	{
		return $this->get($key);
	}//end function

	/**
	 * Getter for Enity object
	 * @param string $key
	 * @throws \Exception
	 * @return mixed
	 */
	public function get($key)
	{
		if (!is_object($this->objData))
		{
			//throw new \Exception(__CLASS__ . " : Data is not set", 500);
			return FALSE;
		}//end if

		if (!isset($this->objData->$key))
		{
			return FALSE;
		}//end if

		return $this->objData->$key;
	}//end function

	/**
	 * Setter for Entity object.
	 * If $key is either an object or array, it will be converted to individual params.
	 * @param string $key
	 * @param mixed $value - If set to NULL, this function expctes $key to be iterable entity
	 */
	public function set($key, $value = NULL)
	{
		if (!is_object($this->objData))
		{
			$this->objData = new \stdClass();
		}//end if

		//set data changed indicator
		$this->flag_api_key_data_changed = TRUE;

		//convert objects and arrays
		if ((is_object($key) || is_array($key)) && $value === NULL)
		{
			foreach ($key as $k => $v)
			{
				if (strtolower($k) == "hypermedia")
				{
					//strip hypermedia from entity data
					$this->setHyperMedia($v);
				} else {
					$this->objData->$k = $v;
				}//end if
			}//end foreach

			return;
		}//end if

		$this->objData->$key = $value;
	}//end function


	/**
	 * Unsetter for entity object
	 * Checks if the value exists, remove the value from memory and returns the value removed
	 * @param string $key
	 * @return mixed
	 */
	public function remove($key)
	{
		$value = $this->get($key);

		//data object does not exist yet
		if ($value === FALSE)
		{
			return FALSE;
		}//end if

		unset($this->objData->$key);

		return $value;
	}//end function

	/**
	 * Request hypermedia data
	 * @param string $key
	 * @throws \Exception
	 */
	public function getHyperMedia($key = NULL)
	{
		if (!is_object($this->objHyperMedia))
		{
			throw new \Exception(__CLASS__ . " : Hypermedia data is not set", 500);
		}//end if

		if ($key === NULL)
		{
			//return the entire object
			return $this->objHyperMedia;
		}//end if

		return $this->objHyperMedia->$key;
	}//end function

	/**
	 * Create an instance of the User Date Format Helper
	 * @return \FrontCore\ViewHelpers\FrontFormatUserDateHelper
	 */
	protected function getUserDateFormatHelper()
	{
		if (!$this->objUserDateFormatHelper)
		{
			$this->objUserDateFormatHelper = $this->getServiceLocator()->get("FrontCore\ViewHelpers\FrontFormatUserDateHelper");
		}//end if

		return $this->objUserDateFormatHelper;
	}//end function
}//end class