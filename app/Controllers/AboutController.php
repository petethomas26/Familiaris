<?php

namespace App\Controllers;

class AboutController extends Controller {

	public function getAbout($request, $response) {
		return $this->container->view->render($response, 'about.twig');
	}

	public function postAbout($request, $response) {

	}


}