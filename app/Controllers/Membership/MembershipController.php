<?php

namespace App\Controllers\Membership;

use App\Controllers\Controller;

use App\Models\Member;

use App\Models\Address;

use Respect\Validation\Validator as v;

class MembershipController extends Controller {

	/****************************************************************************************
	* Prepares the membership page
	* The membership page can be viewed by visitors who have not yet signed in or signed up.
	* If a visitor is not signed in, they:
	*    may not invite, post a notice, send an email or view notices.
	* If a visitor is signed in, they:
	*    may not sign up (as they must already be signed up in order to have signed in)
	* ***************************************************************************************/
	public function membership($request, $response) {
		// Is visitor signed in?
		$isSignedIn = $this->container->auth->check();

		if ($isSignedIn) {
		
			// Get upto 10 latest Notices from db and pass them to Membership page to be displayed
			$count =  \App\Models\Notice::count();
			if ($count > 10) {
				$skip = $count - 10;
				$notices = \App\Models\Notice::skip($skip)->take(10)->get();
			} else {
				$notices = \App\Models\Notice::get();
			}

			//Get the associated member names
			$memberNames = [];
			foreach ($notices as $notice) {
				$memberId = $notice['member_id'];
				$memberName = ($memberId > 0) ? Member::find($memberId)->value('name') : 'Admin';
				$memberNames[] = $memberName;
			}
		} else {
			$notices = [];
		}
		
		return $this->container->view->render($response, 'Membership/membership.twig', compact('notices', 'memberNames'));
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
		$familiarisWebAddress = "http://localhost:8080/Familiaris/public/"; //https://www.familiaris.uk";
		$familiarisAdminEmailAddress = "admin@familiaris.uk";

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
		$from = "pete.thomas.26@gmail.com";// Make this the email address ; "admin@familiaris.uk";
		$name = "Familiaris Admin";
		$subject = "Invitation to join our family tree website (familiaris)";
		
		$message = $this->mailer($from, $name, $to, $subject, $body);
		
		if ($message === 'OK') {
			$message ="Invitation has been sent.";
		} else {
			$message = "Problem sending invitation; check email address and try again later. ". $message;
		}

		
		return $message;
	}

	/***************************************************************************
	* Causes Notices page to be displayed
	* **************************************************************************/
	public function notice($request, $response) {
		
		return $this->container->view->render($response, 'Membership/notice.twig');
	}

	/****************************************************************************
	* Validates a Notice form and saves it to db
	* **************************************************************************/ 
	public function postNotice($request, $response) {

		$validation = $this->container->validator->validate($request, [
			'heading' => v::notEmpty(),
			'editor' => v::notEmpty()->isPure($this->container)
		]);


		if ($validation->failed()) {
			$this->container->flash->addMessage('error', $validation->firstMessage());
			return $response->withRedirect($this->container->router->pathFor('notice'));
		};

		// Get form elements
		$heading = $request->getParam('heading');
		$body = $request->getParam('editor');
		$memberId = $_SESSION['member'];

		// Create a new model
		$notice = new\App\Models\Notice();
		$notice->member_id = $memberId;
		$notice->heading = $heading;
		$notice->notice = $body;

		// Save model to db
		$notice->save();

		// Display updated membership page
		return $response->withRedirect($this->container->router->pathFor('membership'));
	}


/**************************************
* Search for a memebr
* *************************************/
	public function getFindMember($request, $response) {
		return $this->container->view->render($response, 'Membership/findMember.twig');
	}

	
	public function postFindMember($request, $response) {
		$memberName = $request->getParam('memberName');
		
		$threshold = 1.0; // Similarity measure not currently implemented

		$result = Member::findMembers($memberName, $threshold);

		$count = count($result['members']);

		// message comes from findMembers function
		$this->container->flash->addMessage('info', $result['message']);
		if ($count == 0) {
			// return to member search page
			return $response->withRedirect($this->container->router->pathFor('findMember'));
		} elseif ($count == 1) {
			// go to send message page
			
			$member = $result['members'][0];
			return $response->withRedirect($this->container->router->pathFor('sendMessage', [], $member));
		} else {
			// go to choose member page
			$member = $result['members'];
			return $response->withRedirect($this->container->router->pathFor('chooseMember', [], $result));
		};
		
	}

/***********************************************************************
* Send a message via email to another member
* Member is found using a search
* **********************************************************************/

	public function sendMessage($request, $response) {
		$email = $request->getParam('email');
		$memberId = $_SESSION['member'];
		$member = \App\Models\Member::find($memberId);
		$from = $member['email'];
		$name = $member['name'];
		return $this->container->view->render($response, 'Membership/sendMessage.twig', compact('email', 'from', 'name'));
	}

	/****************************************************
	* Sends an email to some email address which does
	* not have to be the address of a member
	* ***************************************************/

	public function postSendMessage($request, $response) {
		// Validate form
		$validation = $this->container->validator->validate($request, [
			'from' => v::notEmpty()->email(),
			'name' => v::notEmpty(),
			'email' => v::notEmpty()->email(),
			'subject' => v::notEmpty(),
			'editor' => v::notEmpty()->isPure($this->container)
		]);

		if ($validation->failed()) {
			$this->container->flash->addMessage('error', $validation->firstMessage());
			return $response->withRedirect($this->container->router->pathFor('membership'));
		};

		// Get form data
		$fromMemberEmail = $request->getParam('from');
		$from = 'admin@familiaris.uk';
		$name = $request->getParam('name');
		$toEmail = $request->getParam('email');
		$subject = $request->getParam('subject');
		$message = $request->getParam('editor');

		// Add to email subject
      	$subject = $subject . " (Familiaris)";

      	// Add footer to email message
      	$footer = "This message was sent via Familiaris; reply to: " . $fromMemberEmail;
		$message = $message . "\n\n" . $footer;

		// Try to send email; $to must be an array of addresses
		$to[] = $toEmail;

		$result = $this->mailer($from, $name, $to, $subject, $message);
		if ($result === "OK") {
			$message ="Message sent";
			$this->container->flash->addMessage('info', $message);
		} else {
			$message = "Problem sending message; check email address and try again later. " . $result;
			$this->container->flash->addMessage('error', $message);
		}

		return $response->withRedirect($this->container->router->pathFor('membership'));
	}

	/**********************************************************
	* Obtains a list of the names of current members
	* *********************************************************/
	public function listMembers($request, $response) {
		// Get list of names from db
		$members = Member::orderBy('name')->pluck('my_person_id', 'name');

		// Find current/latest town for each member ('Unknown' means no address found)
		$towns = [];
		foreach ($members as $member => $personId) {
			if ($personId === 0) {
				$towns[] = "Unknown";
			} else {
				$address = Address::latestAddress($personId);
				if (isset($address)) {
					$towns[] = $address.town;
				} else {
					$towns[] = "Unknown";
				}
			}
			
		}
		
		return $this->container->view->render($response, 'Membership/listMembers.twig', compact('members', 'towns'));

	}

}