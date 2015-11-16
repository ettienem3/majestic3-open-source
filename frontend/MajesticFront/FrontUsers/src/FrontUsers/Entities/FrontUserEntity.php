<?php
namespace FrontUsers\Entities;

use FrontCore\Adapters\AbstractEntityAdapter;

/**
 * Establish connection between business objects (tables) with database.
 * @author simphiwe
 */

class FrontUserEntity extends AbstractEntityAdapter
{
	private $serviceManager;
	
	/**
	 * Container for the Crypto Model
	 * @var \FrontCore\Models\Security\CryptoModel
	 */
	private $objCrypto;
	
	public function getSecure($key)
	{
		$value = parent::get($key . "_secure");
		
		if (!$value || $value == "")
		{
			return FALSE;
		}//end if
		
		$value = $this->objCrypto->sha1EncryptDecryptValue("decrypt", $value, array());
		return $value;
	}//end function
	
	public function setSecure($key, $value)
	{
		$value = $this->objCrypto->sha1EncryptDecryptValue("encrypt", $value, array());
		parent::set($key . "_secure", $value);
	}//end function
	
	public function setCrypto($objCrypto)
	{
		$this->objCrypto = $objCrypto;
	}//end function
}//end class