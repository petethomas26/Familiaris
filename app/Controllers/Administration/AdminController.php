<?php

namespace App\Controllers\Administration;

use App\Controllers\Controller;
use App\Models\Member;

class AdminController extends Controller {
  
  public function administration($request, $response) {
		return $this->container->view->render($response, 'Administration/administration.twig');
  }
  
  // Display contact form
  public function contactAdmin($request, $response) {
     return $this->container->view->render($response, 'Administration/contactAdmin.twig');
  }
  
  public function postContactAdmin($request, $response) {
		// Find administrators
		$administrators = \App\Models\Member::where('status', '=', 'administrator')->get();
																
		if (! isset($administrators)) {
			// There are no administrators
			// This is a sad situation and something needs to be done
			$this->container->flash->addMessage('info', "There are no administators; community action is required.");
			return $response->withRedirect($this->container->router->pathFor('knowledgebase')); // community'));
		}
		
		$admins = $administrators->toArray();
		foreach($admins as $administrator) {
			$to[] = $administrator['email'];
		};
    
    

		$subject = $request->getParam('subject');
		$message = $request->getParam('message');
		$memberId = $_SESSION['member'];
		$member = \App\Models\Member::find($memberId);
		$from = $member['email'];
		$name = $member['name'];

		if (isset($member)) {

			if ($this->mailer($from, $name, $to, $subject, $message)) {
				$message ="Message has been sent to administrators";
			} else {
				$message = "Problem sending message to administrators; check email address and try again later.";
			}

		} else {
			$message = "Unknown member reference.";
		}

		$this->container->flash->addMessage('info', $message);
		return $response->withRedirect($this->container->router->pathFor('knowledgebase'));
	}

}