<?php
namespace Pragma\Historic;

use Pragma\ORM\Model;

class Action extends Model{
	const TABLE_NAME = 'historic_actions';

	public function __construct(){
		parent::__construct(self::getTableName());

		if(defined('PRAGMA_HISTORIC_CREATION_HOOK')){
			$this->pushHook('before_save', PRAGMA_HISTORIC_CREATION_HOOK);
		}
	}

	public static function getTableName(){
		defined('DB_PREFIX') OR define('DB_PREFIX','pragma_');
		return DB_PREFIX.self::TABLE_NAME;
	}
}
