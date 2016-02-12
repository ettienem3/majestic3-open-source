<?php
namespace FrontUsers\Tables;

use FrontCore\Adapters\AbstractCoreAdapter;
use Zend\Db\TableGateway\TableGateway;
use FrontUsers\Entities\FrontUserEntity;
use FrontUsers\Entities\FrontUserCacheSettingsEntity;

class UserCacheSettingsTable extends AbstractCoreAdapter
{
	protected $tableGateway;
	
	/**
	 * Set table name
	 * @var String
	 */
	public static $tableName = "user_cache_settings";
	
	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}//end function
	
	public function get(FrontUserEntity $objUser)
	{
		
	}//end function
	
	public function save(FrontUserEntity $objUser, FrontUserCacheSettingsEntity $objData)
	{
		
	}//end function
}//end class