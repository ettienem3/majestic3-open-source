<?php
namespace FrontUsers\Tables;

use FrontCore\Adapters\AbstractCoreAdapter;
use Zend\Db\TableGateway\TableGateway;

class UserNativePreferencesTable extends AbstractCoreAdapter
{
	protected $tableGateway;
	
	/**
	 * Set table name
	 * @var String
	 */
	public static $tableName = "user_native_preferences";
	
	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}//end function
	
	/**
	 * Load user preferences
	 * @param string $user_id
	 * @return \FrontUsers\Entities\FrontUserNativePreferencesEntity
	 */
	public function get($user_id)
	{
		return $this->tableGateway->select(array('user_id' => $user_id))->current();
	}//end function
	
	/**
	 * Save native user preferences
	 * @param string $user_id
	 * @param string $data
	 */
	public function save($user_id, $data)
	{
		$objData = $this->tableGateway->select(array('user_id' => $user_id))->current();
		
		if (!$objData)
		{
			//create record
			$this->tableGateway->insert(array('user_id' => $user_id, 'data' => $data));
		} else {
			//update record
			$this->tableGateway->update(array('data' => $data), array('user_id' => $user_id));
		}//end if
	}//end function
	
	/**
	 * Remove user settings
	 * @param string $user_id
	 */
	public function delete($user_id)
	{
		$this->tableGateway->delete(array('user_id' => $user_id));
	}//end function
}//end class