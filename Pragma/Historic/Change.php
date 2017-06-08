<?php
namespace Pragma\Historic;

use Pragma\ORM\Model;

class Change extends Model{
	const TABLE_NAME = 'historic_changes';

	public function __construct(){
		parent::__construct(self::getTableName());
	}

	public static function getTableName(){
		defined('DB_PREFIX') OR define('DB_PREFIX','pragma_');
		return DB_PREFIX.self::TABLE_NAME;
	}
}
