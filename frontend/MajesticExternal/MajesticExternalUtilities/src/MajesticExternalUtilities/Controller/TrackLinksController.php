<?php
namespace MajesticExternalUtilities\Controller;

use FrontCore\Adapters\AbstractCoreActionController;

class TrackLinksController extends AbstractCoreActionController
{
	/**
	 * Container for the External Utilities Model
	 * @var \MajesticExternalUtilities\Models\MajesticExternalUtilitiesModel
	 */
	private $model_external_utilities;

    public function tAction()
    {
        //load ids
        $link_id = $this->params()->fromRoute("link_id", "");
        $comm_id = $this->params()->fromRoute("comm_id", "");

        //check if required ids are set and check if values are encoded
        if ($link_id == "" || $comm_id == "" || is_numeric($link_id) || is_numeric($comm_id))
        {
        	echo "Request could not be processed. Required information is not available, request has been terminated as a result";
        	exit;
        }//end if

        //setup data
        $arr_data = array(
        	"link_id" => $link_id,
        	"comm_id" => $comm_id,
        	"ip_address" => $_SERVER['REMOTE_ADDR'],
        );

        //execute request
        try {
        	$objTrackedLink = $this->getExternalUtilitiesModel()->trackLinkData($arr_data);

        	//redirect browser to location
        	header("location:" . $objTrackedLink->url);
        	exit;
        } catch (\Exception $e) {
        	echo "An unexpected error occurred: " . $e->getMessage();
        	exit;
        }//end catch
    }//end function

    /**
     * Create an instance of the External Utilities Model using the Service Manager
     * @return \MajesticExternalUtilities\Models\MajesticExternalUtilitiesModel
     */
    private function getExternalUtilitiesModel()
    {
    	if (!$this->model_external_utilities)
    	{
    		$this->model_external_utilities = $this->getServiceLocator()->get("MajesticExternalUtilities\Models\MajesticExternalUtilitiesModel");
    	}//end if

    	return $this->model_external_utilities;
    }//end function
}//end class
