<?php
use App\Middleware\AuthMiddleware;

$app->group('', function() {
	$this->get('/help', 'HelpController:getHelp')->setName('help');
})->add(new AuthMiddleware($container));