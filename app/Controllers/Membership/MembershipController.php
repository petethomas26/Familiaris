<?php

namespace App\Controllers\Membership;

use App\Controllers\Controller;

class MembershipController extends Controller {

	public function membership($request, $response) {
		
		return $this->container->view->render($response, 'home.twig');
	}
}