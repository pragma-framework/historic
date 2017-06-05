<?php

use Phinx\Migration\AbstractMigration;

class AddHistoricActionsTable extends AbstractMigration
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
			if(defined(ORM_ID_AS_UID) && ORM_ID_AS_UID){
				$strategy = defined('ORM_UID_STRATEGY') && ORM_UID_STRATEGY == 'mysql' ? 'mysql' : 'php';
				$t = $this->table('historic_actions', ['id' => false, 'primary_key' => 'id']);
				switch($strategy){
					case 'mysql':
						$t->addColumn('id', 'char', ['limit' => 36])
						  ->addColumn('historisable_type', 'char', ['limit' => 60])
							->addColumn('historisable_id', 'char', ['limit' => 36])
							->addColumn('historisable_ref_type', 'char', ['limit' => 60, 'null' => true])
          		->addColumn('historisable_ref_id', 'char', ['limit' => 36, 'null' => true]);
						break;
					default:
					case 'php':
						$t->addColumn('id', 'char', ['limit' => 23])
							->addColumn('historisable_type', 'char', ['limit' => 60])
							->addColumn('historisable_id', 'char', ['limit' => 23])
							->addColumn('historisable_ref_type', 'char', ['limit' => 60, 'null' => true])
          		->addColumn('historisable_ref_id', 'char', ['limit' => 23, 'null' => true]);
						break;
				}
			}
			else{
				$t = $this->table('historic_actions');
				$t->addColumn('historisable_type', 'char', ['limit' => 60])
					->addColumn('historisable_id', 'integer')
					->addColumn('historisable_ref_type', 'char', ['limit' => 60, 'null' => true])
    			->addColumn('historisable_ref_id', 'integer');
			}

			$t->addColumn('type', 'char', ['limit' => 1, 'default' => 'U'])//U ou D
				->addColumn('deleted_name', 'string')
				->addColumn('created_at', 'datetime')
				->addColumn('created_by', 'string')
				->addIndex(['historisable_type', 'historisable_id'])
				->addIndex(['historisable_ref_type', 'historisable_ref_id'])
				->create();
		}
}
