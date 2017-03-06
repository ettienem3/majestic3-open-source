<?php
namespace FrontFormAdmin\Entities;

use FrontCore\Adapters\AbstractEntityAdapter;
use Zend\ServiceManager\ServiceLocatorInterface;

class FrontFormAdminFormEntity extends AbstractEntityAdapter
{
	/**
	 * Service Locater Instance
	 * @var object
	 */
	protected $serviceLocater;

	/**
	 * Stores field collection for a form if any are set
	 * @var object
	 */
	protected $objFormFieldEntities;

	public function getServiceLocator()
	{
		if (!$this->serviceLocater)
		{
			$this->setServiceLocator(\FrontCore\Factories\FrontCoreServiceProviderFactory::getInstance());
		}//end if

		return $this->serviceLocator;
	}//end function

	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}//end function

	/**
	 * Create a new FormFieldEntity
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFormFieldEntity
	 */
	protected function getFormFieldEntityObjectInstance()
	{
		return $this->getServiceLocator()->get("FrontFormAdmin\Entities\FrontFormAdminFormFieldEntity");
	}//end function

	/**
	 * Getter for Form Field entities
	 */
	public function getFormFieldEntities()
	{
		return $this->objFormFieldEntities;
	}//end function

	/**
	 * (non-PHPdoc)
	 * Overwrite the set function seeing forms might have field entities
	 * attached to them. This require that form data be stripped and entity created as normal.
	 * Fields are created as seperate entities and stored within objFormFieldEntities.
	 * @see \FrontCore\Adapters\AbstractEntityAdapter::set()
	 */
	public function set($key, $value = NULL)
	{
		//is data being received from a form?
		if (is_array($key) && isset($key["submit"]))
		{
			parent::set($key);
		}//end if

		if ((is_object($key) || is_array($key)) && $value === NULL && (isset($key->form) || (is_array($key) && isset($key["form"]))))
		{
			//convert array to object where required
			if (is_array($key))
			{
				$key = (object) $key;
			}//end if

			//set field data first
			//check for any fields
			if ($key->fields !== NULL)
			{
				foreach ($key->fields as $field)
				{
					//create entitity and save attach to form
					$entity_field = $this->getFormFieldEntityObjectInstance();
					//set data
					$entity_field->set($field);
					$arr[] = $entity_field;
				}//end foreach

				//assign data to form entitiy
				$this->objFormFieldEntities = (object) $arr;

				//remove field data from data
				unset($key->fields);
			}//end if

			//strip form data
			if (is_array($key->form) || is_object($key->form))
			{
				parent::set($key->form);
			} else {
				parent::set($key);
			}//end if
		} else {
			parent::set($key, $value);
		}//end if
	}//end function
}//end class