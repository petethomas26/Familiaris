<?php

namespace App\Install;

use App\Models\Member;
use PDO;

class Install {
	

	// Has system been installed with an administrator?
	public function check($container) {
		$host = $container['settings']['db']['host'];
		$database = $container['settings']['db']['database'];
		$port = $container['settings']['db']['port'];
		$charset = $container['settings']['db']['charset'];
		$username = $container['settings']['db']['username'];
		$password = $container['settings']['db']['password'];
		try {
			$conn = new PDO(
				"mysql:host=$host;port=$port;charset=$charset", 
				$username, 
				$password, 
				array(
		    		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'"
		  		)
		  	) ;
		    // set the PDO error mode to exception
		    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		    // Create database
		    $sql = "CREATE DATABASE IF NOT EXISTS ". $database;
		    $conn->exec($sql);

		    // Ensure that member table exists
		    if (! $container->db->schema()->hasTable('member')) {
				Member::createTable($container);
			};

			// Is there an administrator?
			return (Member::where('status', '=', 'administrator')->first() !== null);
	
		} catch (PDOException $e) {
			return false;
		};

	}

}