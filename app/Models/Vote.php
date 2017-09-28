<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/************************************************************
* A vote associates a member with an opinion. That is, the
* member has cast a vote relating to a specific opinion.
* ***********************************************************/
class Vote extends Model {
	protected $table = 'voting';

	protected $fillable = [
		'member_id',
		'opinion_id',
	];

	public function createTable($container){
		$container->db->schema()->create('voting', function($table) {
			$table->increments('id');
			$table->integer('member_id');
			$table->integer('opinion_id');
			$table->timestamps();
		});
	}
}