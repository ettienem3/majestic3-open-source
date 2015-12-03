<?php
namespace FrontUsers\Tables;

use FrontUsers\Entities\FrontUserEntity;
use FrontUsers\Entities\FrontUserSettingsEntity;
use FrontCore\Adapters\AbstractCoreAdapter;
use Zend\Db\TableGateway\TableGateway;

class UserSettingsTable extends AbstractCoreAdapter
{
	protected $tableGateway;
	
	/**
	 * Set table name
	 * @var String
	 */
	public static $tableName = "user_settings";
	
	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}//end function
	
	/**
	 * Load settings for user
	 * @param FrontUserEntity $objUser
	 * @return \FrontUsers\Entities\FrontUserSettingsEntity
	 */
	public function selectUserSettings(FrontUserEntity $objUser)
	{
		$objUserSettings = $this->tableGateway->select(array("fk_id_users" => $objUser->get("id")))->current();
		if (!$objUserSettings)
		{
			return FALSE;
		}//end if
		
		$objUserSettings->set("data", unserialize($this->getServiceLocator()->get("FrontCore\Models\Security\CryptoModel")->sha1EncryptDecryptValue("decrypt", $objUserSettings->get("data"), array())));
		return $objUserSettings;
	}//end function
	
	/**
	 * Save settings for user
	 * @param FrontUserSettingsEntity $objSettings
	 */
	public function saveUserSettings(FrontUserSettingsEntity $objSettings)
	{
		//encode the data
		$str = serialize($objSettings->get("data"));
 		$str = $this->getServiceLocator()->get("FrontCore\Models\Security\CryptoModel")->sha1EncryptDecryptValue("encrypt", $str, array()); 
 		
		//set data
		$arr_data = array(
				"data" => $str,
		);
		
		if (!$this->tableGateway->select(array("fk_id_users" => $objSettings->get("fk_id_users")))->current())
		{
			$arr_data["fk_id_users"] = $objSettings->get("fk_id_users");
			$this->tableGateway->insert($arr_data);
		} else {
			$this->tableGateway->update($arr_data, array("fk_id_users" => $objSettings->get("fk_id_users")));
		}//end if
	}//end function
	
	public function deleteUserSettings($id)
	{
		$this->tableGateway->delete(array("fk_id_users" => $id));
	}//end function
}//end class