<?php

use Phinx\Migration\AbstractMigration;

class AddGlobalNameOnReference extends AbstractMigration
{
    public function change()
    {
        $this->table('historic_references')
            ->addColumn('ref_global_name', 'string')
            ->update();
    }
}
