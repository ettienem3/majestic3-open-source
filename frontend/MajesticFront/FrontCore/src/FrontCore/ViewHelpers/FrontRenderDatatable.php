<?php
//http://www.datatables.net/examples/
namespace FrontCore\ViewHelpers;

use Zend\View\Helper\AbstractHelper;

class FrontRenderDatatable extends AbstractHelper
{
	/**
	 * Location of dataTables source JS file
	 * @var string
	 */
	private $_js_file = "https://cdn-aws.majestic3.com/js/vendor/jquery/datatable/jquery.dataTables.js";

	/**
	 * Generated JS to be added
	 * @var string
	 */
	private $_script = "";

	/**
	 * Css id the script should be generated with
	 * @var string
	 */
	private $_css_id;

	/**
	 * Container for options passed along from view script
	 * @var array
	 */
	private $_arr_options;

	public function __invoke($arr_options = array(), $css_id = "dataTable1", $config = "zero")
	{
		$this->_css_id = $css_id;
		$this->_arr_options = $arr_options;

		return self::_generateTable($config);
	}//end function

	private function _generateTable($config)
	{
		//create configs
		switch (strtolower($config))
		{
			case "zero":
				self::_setConfig_zero();
				break;

			case "simple":
				self::_setConfig_simple();
				break;

			case "advanced":
				self::_setConfig_advanced();
				break;

			default:
				throw new \BadMethodCallException("$config is invalid for DataTable configuration");
				break;
		}//end switch

		return self::_generateDataTable();
	}//end function

	private function _setConfig_zero()
	{
		$this->_script = "jQuery(document).ready(function () { jQuery('#$this->_css_id').dataTable(
																{
																	/* Disable initial sort */
																	\"aaSorting\": [],
																	'responsive': true,
																	'bFilter': false,
																	'paging': false
														    	}
														    );
														});";
	}//end function

	private function _setConfig_simple()
	{
		$this->_script = 'jQuery(document).ready(function() {
									jQuery("#' . $this->_css_id . '").dataTable( {
										"responsive": true,
										"aaSorting": [], //disable initialsort
										"bPaginate": false,
										"bLengthChange": false,
										"bFilter": true,
										"bSort": true,
										"bInfo": false,
										"bAutoWidth": false
									} );
								} );';
	}//end function

	private function _setConfig_advanced()
	{
		$this->_script = "jQuery(document).ready(function () { jQuery('#$this->_css_id').dataTable({";


		$this->_script .= "\"aaSorting\":[], //disable initialsort";
		if ($this->_arr_options["pageinate"] === false)
		{
			$this->_script .= '"bPaginate" : false,';
		} else {
			$this->_script .= '"bPaginate" : true,';
		}//end if

		if ($this->_arr_options["lengthchange"] === false)
		{
			$this->_script .= '"bLengthChange" : false, ';
		} else {
			$this->_script .= '"bLengthChange" : true, ';
		}//end if

		if ($this->_arr_options["filter"] === false)
		{
			$this->_script .= '"bFilter" : false, ';
		} else {
			$this->_script .= '"bFilter" : true, ';
		}//end if

		if ($this->_arr_options["sort"] === false)
		{
			$this->_script .= '"bSort" : false, ';
		} else {
			$this->_script .= '"bSort" : true, ';
		}//end if

		if ($this->_arr_options["info"] === false)
		{
			$this->_script .= '"bInfo" : false, ';
		} else {
			$this->_script .= '"bInfo" : true, ';
		}//end if

		if ($this->_arr_options["autowidth"] === false)
		{
			$this->_script .= '"bAutoWidth" : false ';
		} else {
			$this->_script .= '"bAutoWidth" : true ';
		}//end if

		//close js
		$this->_script .= "}); });";
	}//end function

	private function _generateDataTable()
	{
		if ($this->_script == "")
		{
			return;
		}//end if

		$view = $this->getView();

		if ($this->_arr_options["return_script"] === TRUE)
		{
			//include the source file
			$html = "<script type=\"text/javascript\" src=\"" . $this->_js_file . "\"></script>";

			//inlcude the css file
			//$html .= "<link href=\"" . $this->_css_file_page . "\" media=\"screen\" rel=\"stylesheet\" type=\"text/css\">";
			//$html .= "<link href=\"" . $this->_css_file_table . "\" media=\"screen\" rel=\"stylesheet\" type=\"text/css\">";

			//add the script required
			$html .= "<script type=\"text/javascript\">" . $this->_script . "</script>";
			return $html;
		} else {
			$view = $this->getView();
			//include the source file
			$view->headScript()->appendFile($this->_js_file);

			//add the script required
			$view->headScript()->appendScript($this->_script);
		}//end if
	}//end function
}//end class