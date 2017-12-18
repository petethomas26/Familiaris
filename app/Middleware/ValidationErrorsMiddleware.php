<?php

namespace App\Middleware;

class ValidationErrorsMiddleware extends Middleware {

	// Not sure what this does; not used
	public function __invoke($request, $response, $next) {
		
		if (isset($_SESSION['errs'])) {
			$this->container->view->getEnvironment()->addGlobal('validationErrors', $_SESSION['errs']);
			unset($_SESSION['errs']);
		}
		
		$response = $next($request, $response);

		return $response;
	}
}