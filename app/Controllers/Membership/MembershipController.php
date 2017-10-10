<?php

namespace App\Controllers\Membership;

use App\Controllers\Controller;

use App\Models\Member;

use Respect\Validation\Validator as v;

class MembershipController extends Controller {

	public function membership($request, $response) {
		return $this->container->view->render($response, 'Membership/membership.twig');
	}

	public function invite($request, $response) {
		return $this->container->view->render($response, 'Membership/invite.twig');
	}

	public function postInvite($request, $response, $args) {

		$validation = $this->container->validator->validate($request, [
			'first_name' => v::notEmpty()->alpha('-'),
			'last_name' => v::notEmpty()->alpha('-'),
			'email' => v::noWhitespace()->notEmpty()->emailAvailable(),
			'confirm_email' => v::notEmpty()->matchesPreviousEmail($request->getParam('email')),
			'confirm_age' => v::not(v::nullType())
		]);

		if ($validation->failed()) {
			$this->container->flash->addMessage('info', "The marked fields are invalid. Please re-enter your detail(s)");
			return $response->withRedirect($this->container->router->pathFor('invite'));
		};

		$firstName =  $this->standardizeName($request->getParam('first_name'));
		$lastName =  $this->standardizeName($request->getParam('last_name'));
		$toEmail = $request->getParam('email');
		
		$memberId = $_SESSION['member'];
		
		$member = \App\Models\Member::find($memberId);
		$memberName = $member->name;

		// Obtain an invitation code for this member
		// Note: if there is a person record in the system for the invitee, record it in invitation record
		// Not yet implemented, so enter null; unsure why this feature has been thought necessary
		$personId = null;
		$invitationCode = $this->getInvitation($toEmail, $memberId, $personId);

		// Create an invitation email
		$familiarisWebAddress = "familiaris.uk";
		$familiarisAdminEmailAddress = "Familiaris Email Address"; //TO DO

		$body = "<p>Dear " . $firstName . ",</p>" . $memberName . " has suggested that you might like to join Familiaris, a website containing our family tree. Our website contains information about people in our family (including our ancestors).</p><p>If you would like to know more, please take a look at our site: </p><p>" . $familiarisWebAddress . "</p><p>If you would like to join Familiaris, please sign up and enter the following invitation code (you can only view specific details about people if you have received an invitation and have signed up):</p><p>" . $invitationCode . " </p> <p>With best wishes</p><p>Familiaris</p><p>If you have any concerns about this email please email our administrators at " . $familiarisAdminEmailAddress . "</p>";

		//Need to turn the single email address into an array of email addresses for later processinbg
		$to = [$toEmail];
		$message = $this->sendInvitationEmail($to, $body);

		$this->container->flash->addMessage('info', $message);
		return $response->withRedirect($this->container->router->pathFor('membership'));
	}

	private function getInvitation($toEmail, $memberId, $personId) {
		// Create invitation code 
		$invitationCode = Controller::getIdentifier();

		// Create new db invitation record
		$invitation = new \App\Models\Invitation();
		$invitation->email = $toEmail; // email
		$invitation->code = $invitationCode; // invitation code
		$invitation->inviter = $memberId; // member who issued invitation
		$invitation->person_id = $personId; // record of person to whom invitation is being sent, if any

		// Save invitation record
		$invitation->save();

		return $invitationCode;
	}

	private function sendInvitationEmail($to, $body) {
		$from = "petethomas26@zoho.com";// Make this the email address

		$subject = "Invitation to join our family tree website";

		$message = $this->mailer($from, $to, $subject, $body);
		if ($message === 'OK') {
			$message ="Invitation has been sent.";
		} else {
			$message = "Problem sending invitation; check email address and try again later. ". $message;
		}
		return $message;
	}



}