<?php

use Phinx\Migration\AbstractMigration;

class AddHistoricChangesTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(){
        $t = $this->table('historic_changes', ['id' => false, 'primary_key' => 'id']);
        $t->addColumn('id', 'char', ['limit' => 23])
          ->addColumn('action_id', 'char', ['limit' => 23])
          ->addColumn('field', 'string')
          ->addColumn('before', 'text')
          ->addColumn('after', 'text')
          ->addIndex(['action_id'])
          ->create();
    }
}
