<?php

use Phinx\Migration\AbstractMigration;

use Pragma\Historic\Action;
use Pragma\Historic\Reference;

class ConvertReferencesToRelation extends AbstractMigration
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
		$actions = $this->fetchAll('SELECT * FROM '.(defined('DB_PREFIX') ? DB_PREFIX : 'pragma_').'historic_actions');

		foreach ($actions as $action) {
			if (!empty($action['historisable_ref_type']) && !empty($action['historisable_ref_type'])) {
				Reference::build([
					'action_id' => $action['id'],
					'ref_type'  => $action['historisable_ref_type'],
					'ref_id'    => $action['historisable_ref_id'],
				])->save();
			}
		}
    }

    public function down()
    {
		$references = $this->fetchAll('SELECT * FROM '.(defined('DB_PREFIX') ? DB_PREFIX : 'pragma_').'historic_references');

		foreach ($references as $ref) {
			$action = Action::find($ref['action_id']);
			$action->historisable_ref_type  = $ref['ref_type'];
			$action->historisable_ref_id    = $ref['ref_id'];
			$action->save();
		}

		$this->execute('DELETE FROM '.(defined('DB_PREFIX') ? DB_PREFIX : 'pragma_').'historic_references');
    }
}
