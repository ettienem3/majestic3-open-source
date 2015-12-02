<?php
namespace FrontProfileSettings\Entities;

use FrontCore\Adapters\AbstractEntityAdapter;

class FrontProfileNativeSettingsProfileEntity extends AbstractEntityAdapter
{
	public function get($key, $default_value = FALSE)
	{
		switch ($key)
		{
			case "profile_logo":
				$path = parent::get($key);
				if (!$path)
				{
					return $default_value;
				}//end if
				
				if (!is_file($path))
				{
					return $default_value;
				}//end if
				
				$content = file_get_contents($path);
				if ($content == "")
				{
					return $default_value;
				}//end if
				
				//determine image type
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$f = finfo_file($finfo, $path);
				
				switch($f)
				{
					case "image/gif":
					case "image/png":
					case "image/jpg":
					case "image/jpeg":
					case "image/svg+xml":
						$src = "<img src=\"data:$f;base64," . base64_encode($content) . "\" alt=\"Home\"/>";
						break;
						
					default:
						return $default_value;
						break;
				}//end switch
	
				return $src;
				break;
		}//end switch
	
		$value = parent::get($key);
		if (!$value || $value == "")
		{
			return $default_value;
		}//end if
		
		return $value;
	}//end functino
}//end class