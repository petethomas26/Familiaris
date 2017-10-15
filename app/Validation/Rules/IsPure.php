<?php

namespace App\Validation\Rules;


use Respect\Validation\Rules\AbstractRule;

class IsPure extends AbstractRule {

	protected $container;

	public  function __construct($container) {
		$this->container = $container;
	}

	public function validate($input) {
		// Purify the input
		$clean_html = $this->container->purifier->purify($input);
		// Checks whether the value contains certain escape sequences
		$sequences = ['&lt;', '&gt;'];
		foreach ($sequences as $sequence) {
			if (strchr($clean_html, $sequence)) {
				return false;
			}
		}
		return true;
	}

}