<?php

use App\Middleware\AuthMiddleware;

$app->group('', function() {
	$this->get('/notice/notice/{set}/{no}/{page}', 'NoticeController:getNotices')->setName('notices');
	$this->post('/notice/notice', 'NoticeController:postNotices')->setName('postNotices');
})->add(new AuthMiddleware($container));