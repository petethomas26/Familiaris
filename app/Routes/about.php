<?php

use App\Middleware\AuthMiddleware;

$app->group('', function() {
	$this->get('/about', 'AboutController:getAbout')->setName('about');
});