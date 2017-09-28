<?php

use App\Middleware\AuthMiddleware;

$app->group('', function() {
	$this->get('/guide', 'GuideController:getGuide')->setName('guide');
})->add(new AuthMiddleware($container));