<?php

use App\Middleware\AuthMiddleware;

$app->get('/knowledgebase', 'KnowledgebaseController:knowledgebase')->setName('knowledgebase');

$app->post('/knowledgebase/person', 'KnowledgebaseController:isPerson')->setName('isperson');

$app->group('', function() {

	$this->get('/knowledgebase/person/{personId}', 'KnowledgebaseController:getPerson')->setName('person');

	$this->get('/knowledgebase/remember/{memberId}/{personId}', 'KnowledgebaseController:rememberPerson')->setName('remember');

	$this->get('/knowledgebase/forget/{memberId}/{personId}', 'KnowledgebaseController:forgetPerson')->setName('forget');
	
	$this->get('/knowledgebase/favourites/{memberId}/{personId}', 'KnowledgebaseController:getFavourites')->setName('favourites');

	$this->get('/knowledgebase/people', 'KnowledgebaseController:getPeople')->setName('people');

	$this->get('/knowledgebase/getMyPerson', 'KnowledgebaseController:getMyPerson')->setName('getMyPerson');

	$this->get('/knowledgebase/findPerson', 'KnowledgebaseController:getFindPerson')->setName('findPerson');

	$this->post('/knowledgebase/simpleFindPerson/{page}/{personId}/{who}/{dob}', 'KnowledgebaseController:findPerson')->setName('simpleFindPerson');

	$this->post('/knowledgebase/getPersonId', 'KnowledgebaseController:getPersonId')->setName('getPersonId');

	$this->post('/knowledgebase/findPerson', 'KnowledgebaseController:postFindPerson')->setName('postFindPerson');

	$this->get('/knowledgebase/createPerson/{personId}', 'KnowledgebaseController:getCreatePerson')->setName('createPerson');

	$this->post('/knowledgebase/createPerson/{personId}', 'KnowledgebaseController:postCreatePerson')->setName('postCreatePerson');

	$this->get('/knowledgebase/createMyPerson', 'KnowledgebaseController:getCreateMyPerson')->setName('createMyPerson');

	$this->get('/knowledgebase/choosePerson', 'KnowledgebaseController:getChoosePerson')->setName('choosePerson');

	$this->get('/knowledgebase/updatePerson', 'KnowledgebaseController:getUpdatePerson')->setName('updatePerson');

	$this->post('/knowledgebase/updatePerson/{personId}', 'KnowledgebaseController:postUpdatePerson')->setName('postUpdatePerson');

	$this->post('/knowledgebase', 'KnowledgebaseController:postPerson');

	$this->post('/knowledgebase/addNickname/{personId}', 'KnowledgebaseController:addNickname')->setName('addNickname');

	$this->get('/knowledgebase/deleteNickname/{personId}/{nicknameId}', 'KnowledgebaseController:deleteNickname')->setName('deleteNickname');

	$this->post('/knowledgebase/addCurrentLastName/{personId}', 'KnowledgebaseController:addCurrentLastName')->setName('addCurrentLastName');
	$this->post('/knowledgebase/addPreviousLastName/{personId}', 'KnowledgebaseController:addPreviousLastName')->setName('addPreviousLastName');
	$this->get('/knowledgebase/deletePreviousLastName/{personId}/{lastNameId}', 'KnowledgebaseController:deletePreviousLastName')->setName('deletePreviousLastName');

})->add(new AuthMiddleware($container));