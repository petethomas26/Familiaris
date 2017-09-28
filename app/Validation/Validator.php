<?php

namespace App\Validation;

use Respect\Validation\Validator as Respect;

use Respect\Validation\Exceptions\NestedValidationException;

class Validator {

	protected $errors;

	public function validate($request, array $rules) {

		foreach ($rules as $field => $rule) {
			try {
				$rule->setName(ucfirst($field))->assert($request->getParam($field));
			} catch (NestedValidationException $e) {
				$this->errors[$field] = $e->getMessages();
				// Replace underscore by space in error messages
				$i = 0;
				foreach ($this->errors[$field] as $error) {
					$this->errors[$field][$i] = str_replace("_", " ", $error);
					$i++;
				}
			}
		}

		$_SESSION['errs'] = $this->errors;

		return $this;
	}

	public function failed() {
		return !empty($this->errors);
	}

	public function firstMessage() {
		return ($this->failed()) ? array_values($this->errors)[0][0]: 'None';
	}

}