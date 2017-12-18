<?php

namespace App\Middleware;

class GuestMiddleware extends Middleware {

	//Gaurd against authorized user performing task i.e. only guests can perform the action
	public function __invoke($request, $response, $next) {

		if ($this->container->auth->check()) {
			return $response->withRedirect($this->container->router->pathFor('home'));

		}

		$response = $next($request, $response);

		return $response;
	}
}