<?php

namespace App\Mail;

class Message {

	protected $mailer;

	public function __construct($mailer) {
		$this->mailer = $mailer;
	}

	// The following code is specific to PHP Mailer
	// Probably needs updating to PHP Mailer 6
	// To use a different mailer, change the routines below


	public function to($address, $name){
		$this->mailer->addAddress($address, $name);
	}

	public function subject($subject){
		$this->mailer->Subject = $subject;
	}

	public function body($body){
		$this->mailer->Body = $body;
	}
}