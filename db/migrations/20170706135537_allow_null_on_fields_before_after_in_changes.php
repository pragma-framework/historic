<?php
use Phinx\Migration\AbstractMigration;

use Pragma\Historic\Change;

class AllowNullOnFieldsBeforeAfterInChanges extends AbstractMigration{
	public function change(){
		$t = $this->table(Change::getTableName());
		$t->changeColumn('before', 'text', ['null' => true])
		$t->changeColumn('after', 'text', ['null' => true]);
		$t->update();
	}
}
