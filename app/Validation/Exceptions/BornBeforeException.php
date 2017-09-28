<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class BornBeforeException extends ValidationException {

	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'Invalid birth date: too young.'
		],
	];

}