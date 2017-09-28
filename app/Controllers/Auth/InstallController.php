<?php

namespace App\Controllers\Auth;

use App\Controllers\Controller;

use App\Models\Log;

use App\Models\Member;

use App\Models\Notice;

use App\Models\Favourite;

use App\Models\Opinion;

use App\Models\Vote;

use App\Models\Invitation;

use Respect\Validation\Validator as v;



class InstallController extends Controller {

	public function getInstall($request, $response){
		return $this->container->view->render($response, 'Auth/install.twig');
	}


	public function postInstall($request, $response) {

		$validation = $this->container->validator->validate($request, [
			'email' => v::noWhitespace()->notEmpty()->matchesEmail(), 
			'name' => v::notEmpty()->alpha('-'),
			'password' => v::noWhitespace()->notEmpty(),
			'password_confirm' => v::noWhitespace()->notEmpty(),
			'registration_code' => v::noWhitespace()->notEmpty()->matchesRegistration()
		]);

		// Create the log file if it does not already exist
		if (! $this->container->db->schema()->hasTable('log')) {
			Log::createTable($this->container);
		};

		if ($validation->failed()) {
            // Log an invalid attempt to install
            $this->log("install", 
            			$request->getParam('email'),
            			$request->getParam('name'),
            			'none',
            			$request->getParam('registration_code'),
            			"fail");

			$this->container->flash->addMessage('info', "The marked fields are invalid. Please re-enter your detail(s)");
			return $response->withRedirect($this->container->router->pathFor('auth.install'));
		};

		// Create the member table if it does not already exost
		if (! $this->container->db->schema()->hasTable('member')) {
			Member::createTable($this->container);
		};

		// create a new member with administrator status
		$memberName = $request->getParam('name');
		$member = Member::create([
			'email' => $request->getParam('email'),
			'name' => $request->getParam('name'),
			'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT, ['cost'=>10]),
			'status' => 'administrator'
		]);

		// Create a new favourites table if one does not already exist
		if (! $this->container->db->schema()->hasTable('favourite')) {
			Favourite::createTable($this->container);
		};

		// Create a new invitation table if one does not already exist
		if (! $this->container->db->schema()->hasTable('invitation')) {
			Invitation::createTable($this->container);
		};

		// Create a new opionion table if one does not already exist
		if (! $this->container->db->schema()->hasTable('opinion')) {
			Opinion::createTable($this->container);
		};

		// Create a new vote table if one does not already exist
		if (! $this->container->db->schema()->hasTable('vote')) {
			Vote::createTable($this->container);
		};

		// Log a successful install
		$this->log("install", 
        			$request->getParam('email'),
        			$request->getParam('name'),
        			$member['id'],
        			$request->getParam('registration_code'),
        			'ok');

		//Flash message
		$this->container->flash->addMessage('info', "You have been registered as an administrator with member name " . $request->getParam('name') ." and you can now invite others to join the website.");

		//Having successfully signed up, automatically get signed in
		$this->container->auth->attempt($member->email, $request->getParam('password'));

		// Create a notice table if one does not already exist
		if (! $this->container->db->schema()->hasTable('notice')) {
			Notice::createTable($this->container);
		}

		// Create a message and save it to db
		$id = \App\Models\Notice::insertGetId([
			'member_id' => $member['id'],
			'heading' => "New Member",
			'notice' => "Welcome to " . $memberName . " (ref: " . $member['id'] . ") who has just registered as the first administrator."
			]);

		// create a person record
		// create all other tables in database
		//$this->createPersonTables($this->container);
		//return $response->withRedirect($this->container->router->pathFor('createMyPerson'));
		return $response->withRedirect($this->container->router->pathFor('home'));
	}


// Not required in this version. Destined for Person model
	private function createPersonTables($container) {
		// address & address_links
		if (! $container->db->schema()->hasTable('address')) {
			$container->db->schema()->create('address', function($table) {
				$table->increments('id');
				$table->string('houseNo_Name',30);
				$table->string('address_1', 30);
				$table->string('address_2', 30);
				$table->string('town', 30);
				$table->string('postcode', 8);
				$table->date('from_date', 15);
				$table->boolean('unsure_from_date');
				$table->date('to_date', 15);
				$table->boolean('unsure_to_date');
				$table->timestamps();
			});
		};
		if (! $container->db->schema()->hasTable('address_links')) {
			$container->db->schema()->create('address_links', function($table) {
				$table->increments('id');
				$table->integer('person_id');
				$table->integer('address_id');
				$table->boolean('private');
				$table->timestamps();
			});
		};

		// award & award_links
		if (! $container->db->schema()->hasTable('award')) {
			$container->db->schema()->create('award', function($table) {
				$table->increments('id');
				$table->integer('year');
				$table->boolean('unsure');
				$table->string('award', 50);
				$table->string('description', 255);
				$table->timestamps();
			});
		};
		if (! $container->db->schema()->hasTable('award_links')) {
			$container->db->schema()->create('award_links', function($table) {
				$table->increments('id');
				$table->integer('person_id');
				$table->integer('award_id');
				$table->boolean('private');
				$table->timestamps();
			});
		};

		// education & education_links
		if (! $container->db->schema()->hasTable('education')) {
			$container->db->schema()->create('education', function($table) {
				$table->increments('id');
				$table->string('institution', 40);
				$table->integer('start_year');
				$table->boolean('unsure_start_year');
				$table->integer('end_year');
				$table->boolean('unsure_end_year');
				$table->string('qualification', 30);
				$table->string('subject', 40);
				$table->timestamps();
			});
		};
		if (! $container->db->schema()->hasTable('education_links')) {
			$this->container->db->schema()->create('education_links', function($table) {
				$table->increments('id');
				$table->integer('person_id');
				$table->integer('education_id');
				$table->boolean('private');
				$table->timestamps();
			});
		};

		// employment & employment_links
		if (! $container->db->schema()->hasTable('employment')) {
			$container->db->schema()->create('employment', function($table) {
				$table->increments('id');
				$table->string('job_title', 50);
				$table->string('employer', 40);
				$table->string('location', 50);
				$table->integer('start_year');
				$table->boolean('unsure_start_year');
				$table->integer('end_year');
				$table->boolean('unsure_end_year');
				$table->timestamps();
			});
		};
		if (! $container->db->schema()->hasTable('employment_links')) {
			$container->db->schema()->create('employment_links', function($table) {
				$table->increments('id');
				$table->integer('person_id');
				$table->integer('employment_id');
				$table->boolean('private');
				$table->timestamps();
			});
		};

		// lastname
		if (! $container->db->schema()->hasTable('lastname')) {
			$container->db->schema()->create('lastname', function($table) {
				$table->increments('id');
				$table->integer('person_id');
				$table->string('name', 100);
				$table->timestamps();
			});
		};
		
		// medical & medical_links
		if (! $container->db->schema()->hasTable('medical')) {
			$container->db->schema()->create('medical', function($table) {
				$table->increments('id');
				$table->string('condition', 255);
				$table->integer('year');
				$table->boolean('unsure_year');
				$table->string('treatment', 255);
				$table->timestamps();
			});
		};
		if (! $container->db->schema()->hasTable('medical_links')) {
			$container->db->schema()->create('medical_links', function($table) {
				$table->increments('id');
				$table->integer('person_id');
				$table->integer('medical_id');
				$table->boolean('private');
				$table->timestamps();
			});
		};

		// memory & memory_links
		if (! $container->db->schema()->hasTable('memory')) {
			$container->db->schema()->create('memory', function($table) {
				$table->increments('id');
				$table->integer('year');
				$table->boolean('unsure_year');
				$table->string('memory', 255);
				$table->timestamps();
			});
		};
		if (! $container->db->schema()->hasTable('memory_links')) {
			$container->db->schema()->create('memory_links', function($table) {
				$table->increments('id');
				$table->integer('person_id');
				$table->integer('memory_id');
				$table->boolean('private');
				$table->timestamps();
			});
		};

		// military & military_links
		if (! $container->db->schema()->hasTable('military')) {
			$container->db->schema()->create('military', function($table) {
				$table->increments('id');
				$table->string('branch', 100);
				$table->string('rank', 50);
				$table->string('awards', 255);
				$table->string('group', 50);
				$table->date('start_date');
				$table->boolean('unsure_start_date');
				$table->date('end_date');
				$table->boolean('unsure_end_date');
				$table->string('description', 255);
				$table->timestamps();
			});
		};
		if (! $container->db->schema()->hasTable('military_links')) {
			$container->db->schema()->create('military_links', function($table) {
				$table->increments('id');
				$table->integer('person_id');
				$table->integer('military_id');
				$table->boolean('private');
				$table->timestamps();
			});
		};

		// nickname
		if (! $container->db->schema()->hasTable('nickname')) {
			$container->db->schema()->create('nickname', function($table) {
				$table->increments('id');
				$table->integer('person_id');
				$table->string('name', 50);
				$table->timestamps();
			});
		};

		// note & note_links
		if (! $container->db->schema()->hasTable('note')) {
			$container->db->schema()->create('note', function($table) {
				$table->increments('id');
				$table->date('date');
				$table->boolean('unsure_date');
				$table->text('note');
				$table->timestamps();
			});
		};
		if (! $container->db->schema()->hasTable('note_links')) {
			$container->db->schema()->create('note_links', function($table) {
				$table->increments('id');
				$table->integer('person_id');
				$table->integer('note_id');
				$table->boolean('private');
				$table->timestamps();
			});
		};
		

		// output & output_links
		if (! $container->db->schema()->hasTable('output')) {
			$container->db->schema()->create('output', function($table) {
				$table->increments('id');
				$table->string('output', 100);
				$table->string('collaborator', 255);
				$table->string('description', 255);
				$table->integer('year');
				$table->boolean('unsure_year');
				$table->timestamps();
			});
		};
		if (! $container->db->schema()->hasTable('output_links')) {
			$container->db->schema()->create('output_links', function($table) {
				$table->increments('id');
				$table->integer('person_id');
				$table->integer('output_id');
				$table->boolean('private');
				$table->timestamps();
			});
		};

		// parent
		if (! $container->db->schema()->hasTable('parent')) {
			$container->db->schema()->create('parent', function($table) {
				$table->increments('id');
				$table->integer('parent_id');
				$table->integer('child_id');
				$table->timestamps();
			});
		};

		// partnership
		if (! $container->db->schema()->hasTable('partnership')) {
			$container->db->schema()->create('partnership', function($table) {
				$table->increments('id');
				$table->integer('person_1');
				$table->integer('person_2');
				$table->date('marriage_date');
				$table->boolean('unsure_marriage_date');
				$table->date('divorce_date');
				$table->boolean('unsure_divorce_date');
				$table->boolean('private');
				$table->timestamps();
			});
		};

		// pastime & pastime_links
		if (! $container->db->schema()->hasTable('pastime')) {
			$container->db->schema()->create('pastime', function($table) {
				$table->increments('id');
				$table->integer('start_year');
				$table->boolean('unsure_start_year');
				$table->integer('end_year');
				$table->boolean('unsure_end_year');
				$table->string('activity', 40);
				$table->string('club', 40);
				$table->string('description', 50);
				$table->timestamps();
			});
		};
		if (! $container->db->schema()->hasTable('pastime_links')) {
			$container->db->schema()->create('pastime_links', function($table) {
				$table->increments('id');
				$table->integer('person_id');
				$table->integer('pastime_id');
				$table->boolean('private');
				$table->timestamps();
			});
		};

		// people
		if (! $container->db->schema()->hasTable('people')) {
			$container->db->schema()->create('people', function($table) {
				$table->increments('id');
				$table->string('title', 10);
				$table->string('first_name', 50);
				$table->string('middle_name', 50);
				$table->string('last_name', 50);
				$table->boolean('alt_last_name'); // Is this correct?
				$table->string('photo_name', 30);
				$table->string('photo_extension', 10);
				$table->date('date_of_birth');
				$table->boolean('unsure_date_of_birth');
				$table->date('date_of_death');
				$table->boolean('unsure_date_of_death');
				$table->string('birth_location', 100);
				$table->boolean('unsure_place_of_birth');
				$table->string('death_location', 100);
				$table->boolean('unsure_place_of_death');
				$table->string('nationality', 20);
				$table->string('gender', 7);
				$table->string('nat_ins_no', 9);
				$table->integer('passport_no');
				$table->integer('father');
				$table->integer('mother');
				$table->integer('other_father');
				$table->integer('other_mother');
				$table->string('parent_type', 10);
				$table->date('parent_date');
				$table->integer('current_partner');
				$table->integer('partnership');
				$table->integer('address');
				$table->integer('created_by');
				$table->timestamps();
			});
		};
		// photo
		if (! $container->db->schema()->hasTable('photo')) {
			$container->db->schema()->create('photo', function($table) {
				$table->increments('id');
				$table->string('description', 255);
				$table->integer('person_id');
				$table->string('image_name', 50);
				$table->timestamps();
			});
		};

		// political & political_links
		if (! $container->db->schema()->hasTable('political')) {
			$container->db->schema()->create('political', function($table) {
				$table->increments('id');
				$table->string('activity', 255);
				$table->date('start_date');
				$table->boolean('unsure_start_date');
				$table->date('end_date');
				$table->boolean('unsure_end_date');
				$table->text('description');
				$table->timestamps();
			});
		};
		if (! $container->db->schema()->hasTable('political_links')) {
			$container->db->schema()->create('political_links', function($table) {
				$table->increments('id');
				$table->integer('person_id');
				$table->integer('political_id');
				$table->boolean('private');
				$table->timestamps();
			});
		};
		// query
		if (! $container->db->schema()->hasTable('query')) {
			$container->db->schema()->create('query', function($table) {
				$table->increments('id');
				$table->integer('from_member_id');
				$table->string('email', 255);
				$table->string('subject', 255);
				$table->string('query', 510);
				$table->string('response', 510);
				$table->integer('related_query');
				$table->integer('administrator');
				$table->string('status', 20);
				$table->timeStamp('response_date');
				$table->timestamps();
			});
		};

		// service & service_links
		if (! $container->db->schema()->hasTable('service')) {
			$container->db->schema()->create('service', function($table) {
				$table->increments('id');
				$table->string('service', 50);
				$table->string('description', 255);
				$table->date('start_date');
				$table->boolean('unsure_start_date');
				$table->date('end_date');
				$table->boolean('unsure_end_date');
				$table->timestamps();
			});
		};
		if (! $container->db->schema()->hasTable('service_links')) {
			$container->db->schema()->create('service_links', function($table) {
				$table->increments('id');
				$table->integer('person_id');
				$table->integer('service_id');
				$table->boolean('private');
				$table->timestamps();
			});
		};

		
	}
}