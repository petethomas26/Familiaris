<?php

namespace App\Controllers\Knowledgebase;

use App\Controllers\Controller;

class KnowledgebaseController extends Controller {

	public function knowledgebase($request, $response) {
		
		return $this->container->view->render($response, 'home.twig');
	}
}