<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class IsPureException extends ValidationException {

	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'Invalid text: Contains forbidden characters.'
		],
	];

}