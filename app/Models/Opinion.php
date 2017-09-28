<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*********************************************************
* The community can be asked their opinions by asking
* them to reply to questions (actually, whether or not 
* they agree with a given statement).
* An opinion records the community's responses by
* storing the number of members who agree and who
* disagree with a statement. 
* There is a closing date by which members' responses
* are recorded. 
* A votes_threshold is the percentage of responders who
* agree with the statement for that statement to be the 
* will of the membership.
* There should be a minimum number of responses for the
* opionion to be deemed valid.
* How is the result communicated to the community?
* ********************************************************/
class Opinion extends Model {
	protected $table = 'opinion';

	protected $fillable = [
		'statement', // A statement
		'end_date', // The date by which members' views have to be submitted
		'votes_threshold', // The percentage of members who must agree with the statement for it to be deemed the view of the community
		'votes_for', // Count of votes cast agreeing with the statement
		'votes_against', // Count of the number of votes disagreeing with the statement
		'turnout' // The percentage of members who must vote for the opionion to be valid
	];

	// Create an opinion table if one does not already exist
	public function createTable($container){
		
			$container->db->schema()->create('opinion', function($table) {
				$table->increments('id');
				$table->string('statement', 255);
				$table->date('end_date', 30);
				$table->integer('votes_threshold');
				$table->integer('votes_for');
				$table->integer('votes_against');
				$table->integer('turnout');
				$table->timestamps();
			});
		
	}
	
}