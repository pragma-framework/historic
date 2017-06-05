<?php

use Phinx\Migration\AbstractMigration;

class AddRereferenceToHistoricActions extends AbstractMigration{
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
        $t = $this->table('historic_actions');
        $t->addColumn('historisable_ref_type', 'char', ['limit' => 60, 'after' => 'historisable_id', 'null' => true])
          ->addColumn('historisable_ref_id', 'char', ['limit' => 23, 'after' => 'historisable_ref_type', 'null' => true])
          ->addIndex(['historisable_ref_type', 'historisable_ref_id'])
          ->save();
    }
}
