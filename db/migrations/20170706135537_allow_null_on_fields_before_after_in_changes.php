<?php
use Phinx\Migration\AbstractMigration;

class AllowNullOnFieldsBeforeAfterInChanges extends AbstractMigration
{
    public function up()
    {
        $t = $this->table('historic_changes');
        $t->changeColumn('before', 'text', ['null' => true]);
        $t->changeColumn('after', 'text', ['null' => true]);
        $t->update();
    }
    public function down()
    {
        $t = $this->table('historic_changes');
        $t->changeColumn('before', 'text');
        $t->changeColumn('after', 'text');
        $t->update();
    }
}
