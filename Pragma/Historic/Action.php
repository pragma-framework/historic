<?php
namespace Pragma\Historic;

use Pragma\ORM\Model;

class Action extends Model{
	const TABLE_NAME = 'historic_actions';

	public function __construct(){
		parent::__construct(self::TABLE_NAME);

		if(defined('PRAGMA_HISTORIC_CREATION_HOOK')){
			$this->pushHook('before_save', PRAGMA_HISTORIC_CREATION_HOOK);
		}
	}
}
