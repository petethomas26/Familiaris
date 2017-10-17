<?php

use App\Middleware\docMiddleware;
use App\Middleware\GuestMiddleware;

$app->group('', function() {

	$this->get('/doc/createNotice', 'DocController:createNotice')->setName('doc.createNotice');

	$this->get('/doc/listNotices', 'DocController:listNotices')->setName('doc.listNotices');

	$this->get('/doc/login', 'DocController:login')->setName('doc.login');

	$this->get('/doc/logout', 'DocController:logout')->setName('doc.logout');

	$this->get('/doc/changePassword', 'DocController:changePassword')->setName('doc.changePassword');

	$this->get('/doc/sendInvitation', 'DocController:sendInvitation')->setName('doc.sendInvitation');


});
