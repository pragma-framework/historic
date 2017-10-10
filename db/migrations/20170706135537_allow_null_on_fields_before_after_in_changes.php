<?php
use Phinx\Migration\AbstractMigration;

class AllowNullOnFieldsBeforeAfterInChanges extends AbstractMigration{
	public function change(){
		$t = $this->table('historic_changes');
		$t->changeColumn('before', 'text', ['null' => true]);
		$t->changeColumn('after', 'text', ['null' => true]);
		$t->update();
	}
}
