<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**************************************************************
* A member (user) can save/delete short-cuts to Person records
* A favourite is a short-cut
* *************************************************************/
class Favourite extends Model {
	protected $table = 'favourite';

	protected $fillable = [
		'member_id',
		'person_id',
	];

	public function createTable($container) {
		$container->db->schema()->create('favourite', function($table) {
			$table->increments('id');
			$table->integer('member_id');
			$table->integer('person_id');
			$table->timestamps();
		});
	}

}