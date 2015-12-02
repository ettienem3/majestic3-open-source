<?php
namespace FrontCore\ViewHelpers;

use Zend\View\Helper\AbstractHelper;

class FrontRenderSimpleHtmlTable extends AbstractHelper
{

	/**
	 * Default settings for table params
	 * @var various
	 */

	protected $id 				= "dataTable1";
	protected $class			= "data-table mj3-table table table-striped dataTable no-footer";
	protected $width 			= "100%";
	protected $cellpadding 		= "0";
	protected $cellspacing 		= "0";
	protected $border 			= "0";
	protected $data_options 	= FALSE;
	protected $rownumbers 		= FALSE;
	protected $pagination 		= FALSE;
	protected $data_mode		= "columntoggle";
	protected $data_role		= "table";
	
	/**
	 * Collection of headers for the table
	 * @var array
	 */
	private $_arr_headings;

	/**
	 * Data to be populated within the table
	 * @var array
	 */
	private $_arr_data;

	/**
	 * Data to be added to the footer
	 * @var array
	 */
	private $_arr_footer;


	/**
	 * Container for generated html
	 * @var string
	 */
	private $_html;

	public function __invoke($arr_data = array(), $arr_headings = array(), $arr_options = "", $arr_footer = "")
	{
		//assign settings received
		if (is_array($arr_options))
		{
			foreach ($arr_options as $key => $value)
			{
				$this->$key = $value;
			}//end foreach
		}//end if

		$this->_arr_headings = $arr_headings;
		$this->_arr_data = $arr_data;
		$this->_arr_footer = $arr_footer;

		return self::_buildTable();
	}//end function

	public function generate($arr_options = "", $arr_headings = array(), $arr_data = array(), $arr_footer = "")
	{
		return self::__invoke($arr_data, $arr_headings, $arr_options, $arr_footer);
	}//end function

	/**
	 * Create the table structure
	 * @return string
	 */
	private function _buildTable()
	{
		//open the table
		//set table params
		$arr_params = array(
					"id",
					"class",
					"cellspacing",
					"cellpadding",
					"width",
					"border",
					"data_options",
					"rownumbers",
					"pagination",
					"data_mode",
					"data_role",
				);
		
		$table_options = "";
		foreach ($arr_params as $k => $v)
		{			
			if ($this->$v !== FALSE)
			{
				$value = $this->$v;
				
				switch ($v)
				{
					case "data_options":
						$v = "data-options";
						break;
						
					case "data_mode":
						$v = "data-mode";
						break;
						
					case "data_role":
						$v = "data-role";
						break;
				}//end switch
				
				$table_options .= str_replace("_", "", $v) . "=\"$value\" ";
			}//end if
		}//end foreach

		$this->_html = "<table $table_options>";

		//set headers
		$this->_html .= self::_setHeaders();

		//set table body
		$this->_html .= self::_setData();

		//set table footer
		if (is_array($this->_arr_footer))
		{
			$this->_html .= self::_setFooter();
		}//end if

		//close the table
		$this->_html .= "</table>";

		return $this->_html;
	}//end function

	/**
	 * Set headers
	 * @return string
	 */
	private function _setHeaders()
	{
		$html = "<thead>";
		$html 	.= "<tr>";

		if (is_array($this->_arr_headings))
		{
			foreach ($this->_arr_headings as $key => $value)
			{
				if (is_array($value))
				{
					$th_options = "";
					foreach($value as $e => $ev)
					{
						if ($e != "value")
						{
							$th_options .= "$e=\"$ev\" ";
						} else {
							$ev_value = $ev;
						}//end if
					}//end foreach
					
					if (array_key_exists("disable-data-priority", $value))
					{
						$html .= "<th $th_options>$ev_value</th>";
					} else {
						$html .= "<th $th_options data-priority=\"" . ($key + 1) . "\">$ev_value</th>";
					}//end if
				} else {
					$html .= "<th data-priority=\"" . ($key + 1) . "\">$value</th>";
				}//end if
			}//end foreach
		}//end if

		$html 	.= "</tr>";
		$html .= "</thead>";

		return $html;
	}//end function

	/**
	 * Populate the table with data
	 * @return string
	 */
	private function _setData()
	{
		$html = "<tbody>";
		if (is_array($this->_arr_data))
		{
			foreach ($this->_arr_data as $key => $arr_value)
			{
				if (is_array($arr_value))
				{
					$html .= "<tr id=\"tr_$key\">";
					foreach ($arr_value as $k => $v)
					{
						if (is_array($v))
						{
							$td_options = "";
							foreach ($v as $e => $ev)
							{
								if ($e != "value")
								{
									$td_options .= "$e=\"$ev\" ";
								} else {
									$ev_value = $ev;
								}//end if
							}//end foreach

							$html .= "<td $td_options>$ev_value</td>";
						} else {
							$html .= "<td>$v</td>";
						}//end if
					}//end foreach
					$html .= "</tr>";
				}//end if
			}//end foreach
		}//end if
		$html .= "</tbody>";

		return $html;
	}//end function

	/**
	 * Create HTML table footer
	 * @return string
	 */
	private function _setFooter()
	{
		$html = "<tfoot>";
		$html .= "</tfoot>";

		return $html;
	}//end function
}//end function