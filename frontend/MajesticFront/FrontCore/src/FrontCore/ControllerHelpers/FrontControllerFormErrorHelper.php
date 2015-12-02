<?php
namespace FrontCore\ControllerHelpers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class FrontControllerFormErrorHelper extends AbstractPlugin
{
	/**
	 * Extract form errors from the API and allocate errors to the form itself
	 * @param object $objForm
	 * @param object $request
	 * @param int $code - Optional
	 * @return Form $object
	 */
	public function formatFormErrors($objForm, $request)
	{
		if ($request instanceof \Exception)
		{
			//extract message from exception
			$request = $request->getMessage();
		}//end if

		//set flash message
		$flashMessenger = new \Zend\Mvc\Controller\Plugin\FlashMessenger();
		$flashMessenger->addErrorMessage("Your form could not be processed. Please see below and try again");

		//extract errors from the request return by the API
		$arr_response = explode("||", $request);

		$objResponse = json_decode($arr_response[1]);

		//add api response to flash messages
		if (isset($objResponse->HTTP_RESPONSE_MESSAGE))
		{
			//extract text
			$arr_t = explode(" : ", $objResponse->HTTP_RESPONSE_MESSAGE);
			$flashMessenger->addErrorMessage(array_pop($arr_t));
		}//end if

		if (!is_object($objResponse))
		{
			//errors could not be extracted, append message to first element
			foreach ($objForm->getElements() as $objElement)
			{
				$objForm->get($objElement->getName())->setMessages(array("An unknown error has occured. Form errors could not be loaded"));
			}//end foreach
		} else {
			if (isset($objResponse->data->error_messages))
			{
				foreach ($objResponse->data->error_messages as $field => $objErrors)
				{
					$arr = array();
					foreach ($objErrors as $key => $error)
					{
						$arr[] = $error;
					}//end if

					$objForm->get($field)->setMessages($arr);
				}//end foreach
			}//end if
		}//end if

		return $objForm;
	}//end function
}//end class