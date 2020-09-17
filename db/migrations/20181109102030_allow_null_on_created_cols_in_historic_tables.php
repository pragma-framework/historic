<?php

use Phinx\Migration\AbstractMigration;

class AllowNullOnCreatedColsInHistoricTables extends AbstractMigration
{
    public function up()
    {
        $t = $this->table('historic_actions');
        $t->changeColumn('created_at', 'datetime', ['null' => true]);
        $t->changeColumn('created_by', 'string', ['null' => true]);
        $t->update();
    }
    public function down()
    {
        $t = $this->table('historic_actions');
        $t->changeColumn('created_at', 'datetime');
        $t->changeColumn('created_by', 'string');
        $t->update();
    }
}
