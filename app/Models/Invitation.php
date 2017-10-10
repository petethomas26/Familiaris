<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**********************************************************
* An invitation is a code that is sent to a prospective
* member to use as a verification that they can join the 
* website.
* An existing member sends an invitation to a prospective
* member (to their email address). The email address and
* verification code are used to verify the new member
* **********************************************************/
class Invitation extends Model {
	protected $table = 'invitation';

	protected $fillable = [
		'email',
		'code',
		'inviter',
		'person_id'
	];

	public function createTable($container) {
		$container->db->schema()->create('invitation', function($table) {
			$table->increments('id');
			$table->string('email', 50);
			$table->string('code', 255);
			$table->integer('inviter');
			$table->integer('person_id');
			$table->timestamps();
		});
	}

}