<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/********************************************************************
* A notice is an item of information to be shared among members.
* A notice can be created by the 'system' (to inform about the 
* registration of a new member, for example), or by a member (to
* ask questions to the community, for example).
* ******************************************************************/
class Notice extends Model {
	protected $table = 'notice';

	protected $fillable = [
		'member_id',
		'heading',
		'notice'
	];

	public function createTable($container){
		
		$container->db->schema()->create('notice', function($table) {
			$table->increments('id');
			$table->integer('member_id');
			$table->string('heading', 30);
			$table->text('notice');
			$table->timestamps();
		});
	
	}

	private $personName;
	private $memberName;

	private function personAndMemberNames($personId) {
		$person = \App\Models\Person::find($personId);
		$personName = $person->shortName();
		$memberId = $_SESSION['member'];
		$member = \App\Models\Member::find($memberId);
		$memberName = $member->getName();
	}

/************************************************************************
* Notice type should be one of 'New', 'Updated' or 'Deleted'
* Category is one of 'partnership', 'address', 'medical', 'education',
* 'employment', 'pastime', 'politic', 'output', 'story', 'note'
* ***********************************************************************/
	public function makeSystemNotice($noticeType, $category, $personId) {
		Notice::personAndMemberNames($personId);
		$notice = $noticeType . $category . " for " . $this->personName . " (No: " . $personId . ") created by " . $this->memberName . " (member no: " . $memberId . ").";
		\App\Models\Notice::create([
				'member_id' => 0, //a system generated notice
				'heading' => $noticeType . ' ' . $category,
				'notice' => $notice
			]);
	}


}