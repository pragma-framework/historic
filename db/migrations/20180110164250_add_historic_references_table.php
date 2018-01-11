<?php

use Phinx\Migration\AbstractMigration;

class AddHistoricReferencesTable extends AbstractMigration
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
	public function change()
	{
		if (defined('ORM_ID_AS_UID') && ORM_ID_AS_UID) {
			$strategy = ! defined('ORM_UID_STRATEGY') ? 'php' : ORM_UID_STRATEGY;
			$t = $this->table('historic_references', ['id' => false, 'primary_key' => 'id']);
			switch ($strategy) {
				case 'mysql':
				case 'laravel-uuid':
					$t->addColumn('id', 'uuid')
						->addColumn('action_id',    'uuid')
						->addColumn('ref_type',     'char', ['limit' => 60, 'null' => true])
						->addColumn('ref_id',       'uuid');
					break;
				default:
				case 'php':
					$t->addColumn('id', 'char', ['limit' => 23])
						->addColumn('action_id',    'char', ['limit' => 23])
						->addColumn('ref_type',     'char', ['limit' => 60, 'null' => true])
						->addColumn('ref_id',       'char', ['limit' => 23, 'null' => true]);
					break;
			}
		} else {
			$t = $this->table('historic_references')
				->addColumn('action_id',    'integer')
				->addColumn('ref_type',     'char',     ['limit' => 60, 'null' => true])
				->addColumn('ref_id',       'integer',  ['null' => true]);
		}

		$t->addIndex(['action_id'])
			->addIndex(['ref_type', 'ref_id'])
			->create();
	}
}
