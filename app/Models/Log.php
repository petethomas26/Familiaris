<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**********************************************************
* A log is a record of a significant event in the life
* of the website (e.g. a new member joins, a successful 
* or failed attempt to sign up or sign in).
* Administrators can view the logged data.
* The intention is that there will be automatic processing
* of log data to uncover problems.
* *********************************************************/
class Log extends Model {

	protected $table = 'log';

	protected $fillable = [
		'date', // the date and time at which the event occurred
		'type', // the type of event
		'email',  // the email address associated with the event, if any
		'name', // the name of the member who initiated the event
		'member_id', // the id of the member who initiated the event
		'invitation_code', // the invitation code associated with a sign up
		'result' // whether or not the event was successful
	];

	// Create the log file if it does not already exist
	public function createTable($container) {
		$container->db->schema()->create('log', function($table) {
			$table->increments('id');
			$table->dateTime('date');
			$table->string('type', 15);
			$table->string('email', 50);
			$table->string('name', 100);
			$table->integer('member_id');
			$table->string('invitation_code', 255);
			$table->string('result',4);
			$table->timestamps();
		});
	}

}