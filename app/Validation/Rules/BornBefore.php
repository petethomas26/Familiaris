<?php

namespace App\Validation\Rules;

use App\Models\Invitation;

use App\Controllers\Controller;

use Respect\Validation\Rules\AbstractRule;

class BornBefore extends AbstractRule {

	protected $date;

	public  function __construct($date) {
		$this->date = $date;
	}


	public function validate($input) {
		return $input < $this->date;
	}


}