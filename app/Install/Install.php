<?php

namespace App\Install;

use App\Models\Member;
use PDO;

class Install {
	

	// Has system been installed with an administrator?
	public function check($container) {
		
	    // Ensure that member table exists
	    if (! $container->db->schema()->hasTable('member')) {
			Member::createTable($container);
			return false;
		} else {
			// Is there an administrator?
			return (Member::where('status', '=', 'administrator')->first() !== null);
		}
		
	}

}