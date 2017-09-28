<?php

namespace App\Controllers\Auth;

use App\Controllers\Controller;

use App\Models\Member;

use App\Models\Invitation;

use App\Models\Notice;

use Respect\Validation\Validator as v;



class AuthController extends Controller {

	public function getSignOut($request, $response) {
		$this->container->auth->logout();
		return $response->withRedirect($this->container->router->pathFor('home'));
	}

	public function getSignIn($request, $response){
		$isSignedIn = $this->container->auth->check();
		if ($isSignedIn) {
			$this->container->flash->addMessage('error', 'You are already signed in.');
			return $response->withRedirect($this->container->router->pathFor('home'));
		}
		return $this->container->view->render($response, 'Auth/signin.twig');
	}

	public function postSignIn($request, $response) {

		// No real need for validation as this is a simple db look up which either finds the
		// the given email-password combination or not. However, validation might add to
		// the user experience. It is omitted here.

		$authenticated = $this->container->auth->attempt(
			$request->getParam('email'),
			$request->getParam('password')
		);

		if (!$authenticated) {
			$this->log("login", 
            			$request->getParam('email'),
            			'none',
            			'none',
            			'none',
            			"fail");

			$this->container->flash->addMessage('error', 'Could not sign you in with those details.');
			return $response->withRedirect($this->container->router->pathFor('auth.signin'));
		}

		$this->log("login", 
        			$request->getParam('email'),
        			'none',
        			'none',
        			'none',
        			"ok");
		
		$memberName = $this->container->auth->member()->getName();

		$this->container->flash->addMessage('info', 'Hello ' . $memberName . ', welcome to Familiaris');
		return $response->withRedirect($this->container->router->pathFor('home'));

	}

	// Also acts as a system start up mechanism by registering the first administrator
	// If the invitations table does not exist, create the administrator and perform initialisation tasks,
	// otherwise perform normal registration process
	public function getSignUp($request, $response){
		if ($this->container->db->schema()->hasTable('invitation')) {
			return $this->container->view->render($response, 'Auth/signup.twig');
		} else {
			// add new administrator
			return $this->container->view->render($response, 'Auth/install.twig');
		}
	}

	public function postSignUp($request, $response) {

		$email = $request->getParam('email');
		$memberName = $request->getParam('name');
		$invitation = $request->getParam('invitation');
		

		$validation = $this->container->validator->validate($request, [
			'email' => v::noWhitespace()->notEmpty()->emailAvailable(),
			'name' => v::notEmpty()->alpha('-'),
			'password' => v::noWhitespace()->notEmpty(),
			'password_confirm' => v::noWhitespace()->notEmpty()->matchesPassword($request->getParam('password')),
			'invitation' => v::noWhitespace()->notEmpty()->matchesInvitation($request->getParam('email'))
		]);


		if ($validation->failed()) {
			// Log an invalid attempt to sign up
			$email = (isset($email)) ? $email : 'None';
			$memberName = (isset($memberName)) ? "None" : $memberName;
			$invitation = (isset($invitation)) ? "None" : $invitation;
            $this->log("register", 
            			$email,
            			$memberName,
            			'None',
            			$invitation,
            			"fail");

			$this->container->flash->addMessage('error', "The marked fields are invalid. Please re-enter your detail(s)");
			
			return $response->withRedirect($this->container->router->pathFor('auth.signup'));
		};

		// create a membership record
		
		$member = Member::create([
			'email' => $email,
			'name' => $memberName,
			'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT, ['cost'=>10]),
			'status' => 'ordinary',
		]);

		// log successful registration
		
		$this->log("register", 
        			$email,
        			$memberName,
        			$member['id'],
        			$invitation,
        			"ok");


		//Flash message
		$this->container->flash->addMessage('info', "You have been signed up " . $memberName ."; but we need a few more details to add you to the family tree.");

		//Having successfully signed up, automatically get signed in
		$this->container->auth->attempt($member->email, $request->getParam('password'));

		// Create a message and save it to db
		\App\Models\Notice::insert([
			'member_id' => 0, //This is a system message, so member is 0
			'heading' => "New Member",
			'notice' => "Welcome to " . $memberName . " (ref: " . $member['id'] . ") who has just signed up."
			]);

		// create a person record for this member
		//return $response->withRedirect($this->container->router->pathFor('createMyPerson'));

		return $response->withRedirect($this->container->router->pathFor('home'));

	}
}