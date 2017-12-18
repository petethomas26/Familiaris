<?php

namespace App\Controllers\Auth;

use App\Controllers\Controller;
use Respect\Validation\Validator as v;

use App\Models\Member;

class PasswordController extends Controller {

	public function getChangePassword($request, $response) {
		return $this->container->view->render($response, 'Auth/Password/change.twig');
	}

	public function postChangePassword($request, $response) {
		// Validate passwords: does password_old match the stored password?
		// is new password not empty and contains no whitespace?
		// Hopefully, the user will have entered a strong password being guided by password strength meter
		$validation = $this->container->validator->validate($request, [
			'password_old' => v::noWhitespace()->notEmpty()->matchesPassword($this->container->auth->member()->password),
			'password_new' => v::noWhitespace()->notEmpty()
		]);

		// Has validation faiiled? If so, return to change password form
		if ($validation->failed()) {
			return $response->withRedirect($this->container->router->pathFor('auth.password.change'));
		}

		// Update the stored password
		$this->container->auth->member()->setPassword($request->getParam('password_new'));

		$this->container->flash->addMessage('info', 'Your password has been changed.');

		// Go to home page
		return $response->withRedirect($this->container->router->pathFor('home'));
	}

	public function getRecoverPassword($request, $response) {
		return $this->container->view->render($response, 'Auth/Password/recover.twig');
	}

	public function postRecoverPassword($request, $response) {

		$validation = $this->container->validator->validate($request, [
			'email' => v::noWhitespace()->notEmpty(),
		]);

		if ($validation->failed()) {
			return $response->withRedirect($this->container->router->pathFor('auth.password.recover'));
		}

		$email = $request->getParam('email');

		// Get member with given email address
		$member = \App\Models\Member::where('email', '=', $email)->first();

		if (! $member) {
			$this->container->flash->addMessage('info', 'Could not find that user.');
			return $response->withRedirect($this->container->router->pathFor('auth.password.recover'));
		} else {
			// Create a unique token (a selector and an identifier)
			$selector = bin2hex(openssl_random_pseudo_bytes(16));
			$identifier = openssl_random_pseudo_bytes(32);
			// Store in database Member table together with current time
			$member->setRecoverToken($selector, $identifier);
			
			// Send email
			$to = [$member['email']];
			$name = $member['name'];
			$base_url = $this->container['settings']['baseUrl']; 
			$password_reset = "/auth/password/reset";
			$params = "?selector=" . $selector . "&identifier=" . $identifier;

			$link = $base_url . $password_reset . $params;

			$body = "<p>Hello </p>" . $name . ",</p>";
			$body .= "<p>We received a request to reset your forgotten password. ";
			$body .= "If you did not make this request, please ignore this email.</p>";
			$body .= "<p>Please click the following link to reset your password: </p><p>";
			$body .= $link;
			$body .= "</> Please note that this link will expire in 30 minutes.</p><p>Familiaris Admin</p>";

			$this->sendPasswordRecoverEmail($to, $body) ;

			
			$this->container->flash->addMessage('info', 'We have emailed you instructions to reset your password.');
			return $response->withRedirect($this->container->router->pathFor('home'));
		}
		
	}

	private function sendPasswordRecoverEmail($to, $body) {
		$from = "admin@familiaris.uk"; //"pete.thomas.26@gmail.com";// Make this the email address ; "admin@familiaris.uk";
		$name = "Familiaris Admin";
		$subject = "Password recovery";
		
		$message = $this->mailer($from, $name, $to, $subject, $body);
		
		if ($message === 'OK') {
			$message ="Invitation has been sent.";
		} else {
			$message = "Problem sending invitation; check email address and try again later. ". $message;
		}

		
		return $message;
	}

	public function getResetPassword($request, $response) {
		
		$selector = $request->getParam('selector');
		$identifier = $request->getParam('identifier');
		$identifierHash = hash('sha256', hex2bin($identifier));

		// Retrieve member record using selector
		$member = \App\Models\Member::where('selector', '=', $selector)->first();

		// Has a member been found?
		if (! $member) {
			$this->container->flash->addMessage('info', 'Member not known.');
			return $response->withRedirect($this->container->router->pathFor('home'));
		}

		// Does member record contain a recovery identifier?
		if (! $member->getRecoverIdentifier()) {
			$this->container->flash->addMessage('info', 'Password previously recovered: try recovery again.');
			return $response->withRedirect($this->container->router->pathFor('auth.password.recover'));
		}

		// Reset must be attempted within 30 minutes of start of recovery process
		if ( $member->getElapsedRecoverTime() > 30*60) {
			$member->update([
				'password' => password_hash($request->getParam('password_new'), PASSWORD_DEFAULT, ['cost'=>10]),
				'recover_selector' => null,
				'recover_identifier' => null,
				'recover_time' => 0,
			]);
			$this->container->flash->addMessage('info', 'Recovery link expired: try recovery again.');
			return $response->withRedirect($this->container->router->pathFor('auth.password.recover'));
		}

		// Does the incoming identifier match the one in the database?
		if (! hash_equals($identifierHash, $member->getRecoverIdentifier() )) {
			$this->container->flash->addMessage('info', 'Invalid recovery identifier.');
			return $response->withRedirect($this->container->router->pathFor('home'));
		}

		return $this->container->view->render($response, 'Auth/Password/reset.twig', compact('member'));
	}

	public function postResetPassword($request, $response) {
		// Validate password
		$validation = $this->container->validator->validate($request, [
			'password_new' => v::noWhitespace()->notEmpty(),
			'password_confirm' => v::noWhitespace()->notEmpty()->confirmPassword($request->getParam('password_new')),
		]);

		// Has validation faiiled? If so, return to change password form
		if ($validation->failed()) {
			$this->container->flash->addMessage('error', "The marked field(s) are invalid. Please reenter your details.");
			return $response->withRedirect($this->container->router->pathFor('auth.password.reset'));
		} 

		// Update the stored password and delete recovery details
		$member = $request->getParam('member');
		$member->update([
			'password' => password_hash($request->getParam('password_new'), PASSWORD_DEFAULT, ['cost'=>10]),
			'recover_selector' => null,
			'recover_identifier' => null,
			'recover_time' => 0,
		]);

		// New password. New session

		$this->container->flash->addMessage('info', 'Your password has been reset and you can now sign in.');
	
		return $response->withRedirect($this->container->router->pathFor('home'));
	}
}