<?php
namespace FrontCore\Adapters;

use Zend\Mvc\Controller\AbstractActionController;

class AbstractCoreActionController extends AbstractActionController
{
	private $serviceManager;

	/**
	 *
	 * {@inheritDoc}
	 * @see \Zend\Mvc\Controller\AbstractController::getServiceLocator()
	 * @return Zend\ServiceManager\ServiceManager
	 */
	public function getServiceLocator()
	{
		if (!$this->serviceManager)
		{
			$this->serviceManager = \FrontCore\Factories\FrontCoreServiceProviderFactory::getInstance();
		}//end function

		return $this->serviceManager;
	}//end function
}//end class