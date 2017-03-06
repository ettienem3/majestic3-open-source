<?php
namespace FrontCore\Models;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontCoreModel extends AbstractCoreAdapter
{
	/**
	 * Prevent special charaters from wrecking json output
	 * @param string $value
	 * @return string
	 */
	public static function JSON_STRING_SAFE($value)
	{
		$escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c", "\x27");
		$replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b", "\'");
		$result = str_replace($escapers, $replacements, $value);
	
		//do utf8 conversion
		$result = \UConverter::transcode(utf8_encode($result), 'UTF-8', 'UTF-8');
		return $result;
	}//end function
}//end class
