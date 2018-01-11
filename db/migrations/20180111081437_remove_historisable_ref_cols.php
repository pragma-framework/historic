<?php

use Phinx\Migration\AbstractMigration;

class RemoveHistorisableRefCols extends AbstractMigration
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
    public function up()
    {
		$this->table((defined('DB_PREFIX') ? DB_PREFIX : 'pragma_').'historic_actions')
			->removeColumn('historisable_ref_type')
			->removeColumn('historisable_ref_id')
			->save();
    }

    public function down()
    {
		$t = $this->table((defined('DB_PREFIX') ? DB_PREFIX : 'pragma_').'historic_actions');

		if (defined('ORM_ID_AS_UID') && ORM_ID_AS_UID) {
			$strategy = ! defined('ORM_UID_STRATEGY') ? 'php' : ORM_UID_STRATEGY;
			switch ($strategy) {
				case 'mysql':
				case 'laravel-uuid':
					$t->addColumn('historisable_ref_type',  'char', ['limit' => 60, 'null' => true])
						->addColumn('historisable_ref_id',  'uuid');
					break;
				default:
				case 'php':
					$t->addColumn('historisable_ref_type',  'char', ['limit' => 60, 'null' => true])
						->addColumn('historisable_ref_id',  'char', ['limit' => 23, 'null' => true]);
					break;
			}
		} else {
				$t->addColumn('historisable_ref_type',  'char',     ['limit' => 60, 'null' => true])
					->addColumn('historisable_ref_id',  'integer',  ['null' => true]);
		}

		$t->addIndex(['historisable_ref_type', 'historisable_ref_id'])->save();
    }
}
