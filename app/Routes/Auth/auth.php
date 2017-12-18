<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

$app->group('', function() {

	$this->get('/auth/signup', 'AuthController:getSignUp')->setName('auth.signup');

	$this->post('/auth/signup', 'AuthController:postSignUp');

	$this->get('/auth/signin', 'AuthController:getSignIn')->setName('auth.signin');

	$this->post('/auth/signin', 'AuthController:postSignIn');

	$this->get('/auth/install', 'InstallController:getInstall')->setName('auth.install');

	$this->post('/auth/install', 'InstallController:postInstall');

	$this->get('/auth/password/recover', 'PasswordController:getRecoverPassword')->setName('auth.password.recover');

	$this->post('/auth/password/recover', 'PasswordController:postRecoverPassword');

	$this->get('/auth/password/reset', 'PasswordController:getResetPassword')->setName('auth.password.reset');

	$this->post('/auth/password/reset', 'PasswordController:postResetPassword');

})->add(new GuestMiddleware($container));

$app->group('', function() {

	$this->get('/auth/signout', 'AuthController:getSignOut')->setName('auth.signout');

	$this->get('/auth/password/change', 'PasswordController:getChangePassword')->setName('auth.password.change');

	$this->post('/auth/password/change', 'PasswordController:postChangePassword');

})->add(new AuthMiddleware($container));

$app->group('', function() {

	$this->get('/auth/email/change', 'EmailController:getChangeEmail')->setName('auth.email.change');

	$this->post('/auth/email/change', 'EmailController:postChangeEmail');

})->add(new AuthMiddleware($container));

