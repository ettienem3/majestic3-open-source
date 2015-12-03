<?php
namespace FrontCore\ControllerHelpers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class FrontControllerExceptionErrorHelper extends AbstractPlugin
{
	/**
	 * Extract errors from the API and return legible string
	 * @param object $request
	 * @use $this->frontControllerErrorHelper()->formatErrors($e)
	 * @return string
	 */
	public function formatErrors($request)
	{
		//extract message from exception
		if ($request instanceof \Exception)
		{
			$request = $request->getMessage();
		}//end if

		//extract errors from the request return by the API
		$arr_response = explode("||", $request);

		$objResponse = json_decode($arr_response[1]);
		if (is_object($objResponse) && isset($objResponse->HTTP_RESPONSE_MESSAGE))
		{
			//extract text
			$arr_t = explode(" : ", $objResponse->HTTP_RESPONSE_MESSAGE);
			return array_pop($arr_t);
		}//end if

		$t = array_pop($arr_response);
		return $t;
	}//end function
}//end class