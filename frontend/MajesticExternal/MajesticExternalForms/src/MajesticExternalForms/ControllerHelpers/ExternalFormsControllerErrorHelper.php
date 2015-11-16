<?php
namespace MajesticExternalForms\ControllerHelpers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class ExternalFormsControllerErrorHelper extends AbstractPlugin
{
	/**
	 * Extract form errors from the API and allocate errors to the form itself
	 * @param object $objForm
	 * @param object $request
	 * @return Form $object
	 */
	public function formatFormErrors($objForm, $request)
	{
		if ($request instanceof \Exception)
		{
			//extract message from exception
			$request = $request->getMessage();
		}//end if
		
		//extract errors from the request return by the API
		$arr_response = explode("||", $request);

		$objResponse = json_decode(trim($arr_response[1]));

		if (!is_object($objResponse))
		{
			//errors could not be extracted, append message to first element
			foreach ($objForm->getElements() as $objElement)
			{
				$objForm->get($objElement->getName())->setMessages(array("An unknown error has occured. Form errors could not be loaded"));
			}//end foreach
		} else {
			foreach ($objResponse->data as $objField)
			{
				if (!isset($objField->messages))
				{
					continue;
				}//end if
				
				if (count($objField->messages) > 0)
				{		
					$objForm->get($objField->attributes->name)->setMessages((array) $objField->messages);
				}//end if
			}//end foreach
		}//end if
		
		return $objForm;
	}//end function
}//end class