<?php

use App\Middleware\AuthMiddleware;

$app->group('', function() {
  
	$this->get('/administration', 'AdminController:administration')->setName('administration');

	$this->get('/adminisration/contactAdmin', 'AdminController:contactAdmin')->setName('contactAdmin');
    $this->post('/adminisration/postContactAdmin', 'AdminController:postContactAdmin')->setName('postContactAdmin');
  
})->add(new AuthMiddleware($container));