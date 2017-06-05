<?php
namespace Pragma\Historic;

use Pragma\ORM\Model;

class Change extends Model{
	const TABLE_NAME = 'historic_changes';

	public function __construct(){
		parent::__construct(self::TABLE_NAME);
	}
}
