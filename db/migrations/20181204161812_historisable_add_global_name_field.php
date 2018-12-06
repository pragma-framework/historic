<?php

use Phinx\Migration\AbstractMigration;

class HistorisableAddGlobalNameField extends AbstractMigration
{
    public function change()
    {
        $this->table('historic_actions')
            ->addColumn('global_name', 'string')
            ->update();
    }
}
