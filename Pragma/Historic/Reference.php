<?php
namespace Pragma\Historic;

use Pragma\ORM\Model;

class Reference extends Model{
	const TABLE_NAME = 'historic_references';

	public function __construct(){
		parent::__construct(self::getTableName());
	}

	public static function getTableName(){
		defined('DB_PREFIX') OR define('DB_PREFIX','pragma_');
		return DB_PREFIX.self::TABLE_NAME;
	}
}
