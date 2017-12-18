<?php

namespace App\Controllers\Knowledgebase;

use App\Controllers\Controller;

class KnowledgebaseController extends Controller {

	public function knowledgebase($request, $response) {
		// Get last (upto) 10 notices from notices db
		$count =  \App\Models\Notice::count();
		if ($count > 10) {
			$skip = $count -10;
			$notices = \App\Models\Notice::offset($skip)->limit(10)->get();
		} else {
			$notices = \App\Models\Notice::get();
		}
		return $this->container->view->render($response, 'Knowledgebase/knowledgebase.twig', compact('notices'));
	}
}