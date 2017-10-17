<?php

namespace App\Controllers\Doc;

use App\Controllers\Controller;

use App\Resources\Views;


class DocController extends Controller {

	public function createNotice($request, $response) {
		return $this->container->view->render($response, 'Doc/createNotice.twig');
	}

	public function listNotices($request, $response) {
		return $this->container->view->render($response, 'Doc/listNotices.twig');
	}

	public function login($request, $response) {
		return $this->container->view->render($response, 'Doc/login.twig');
	}

	public function logout($request, $response) {
		return $this->container->view->render($response, 'Doc/logout.twig');
	}

	public function changePassword($request, $response) {
		return $this->container->view->render($response, 'Doc/changePassword.twig');
	}

	public function sendInvitation($request, $response) {
		return $this->container->view->render($response, 'Doc/sendInvitation.twig');
	}


}