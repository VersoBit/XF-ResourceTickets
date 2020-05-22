<?php

namespace VersoBit\ResourceTickets;

use XF\AddOn\AbstractSetup;
use XF\Db\Schema\Alter;

class Setup extends AbstractSetup
{
	public function install(array $stepParams = [])
	{
        // Add ticket foreign key to resource table
	    $this->schemaManager()->alterTable('xf_rm_resource', function(Alter $table)
        {
            $table->addColumn('ticket_id', 'int')->comment('Points to an automatically-created ticket for the resource update');
            $table->addKey('ticket_id');
        });
	}

	public function upgrade(array $stepParams = [])
	{
		// TODO: Implement upgrade() method.
	}

	public function uninstall(array $stepParams = [])
	{
        // Remove ticket foreign key from resource table
	    $this->schemaManager()->alterTable('xf_rm_resource', function(Alter $table)
        {
            $table->dropColumns('ticket_id');
        });
	}
}