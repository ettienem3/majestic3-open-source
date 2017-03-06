<?php
namespace FrontCore\Adapters;

class AbstractCoreCustomModulesAdapter extends AbstractCoreAdapter
{
	/**
	 * Container for modules loaded
	 * @var array
	 */
	protected $arr_modules = array();
	
	/**
	 * Load models by reference
	 * @param mixed $class
	 */
	protected function getModel($class)
	{
		if (!isset($this->arr_modules[$class]))
		{
			$this->arr_modules[$class] = $this->getServiceLocator()->get($class);
		}//end if
		
		return $this->arr_modules[$class];
	}//end function
}//end class