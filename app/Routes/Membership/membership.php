<?php

use App\Middleware\AuthMiddleware;

$app->get('/membership', 'MembershipController:membership')->setName('membership');

$app->group('', function() {

	$this->get('/membership/member/{id}', 'MembershipController:getMember')->setName('member');

	$this->get('/membership/members', 'MembershipController:getMembers')->setName('members');

	$this->post('/membership', 'MembershipController:postMembership');

	$this->get('/membership/invite', 'MembershipController:invite')->setName('invite');

	$this->post('/membership/invite', 'MembershipController:postInvite');

	$this->get('/membership/sendMessage', 'MembershipController:sendMessage')->setName('sendMessage');

	$this->post('/membership/sendMessage', 'MembershipController:postSendMessage');

	$this->get('/membership/notice', 'MembershipController:notice')->setName('notice');

	$this->post('/membership/notice', 'MembershipController:postNotice')->setName('postNotice');

	$this->get('/membership/findMember', 'MembershipController:getFindMember')->setName('findMember');

	$this->post('/membership/findMember', 'MembershipController:postFindMember')->setName('postFindMember');

	$this->get('/membership/opinion', 'MembershipController:getOpinion')->setName('getOpinion');

	$this->post('/membership/opinion/', 'MembershipController:postOpinion')->setName('postOpinion');

	$this->get('/membership/myMembership', 'MembershipController:getMyMembership')->setName('getMyMembership');

	$this->post('/membership/myMembership/', 'MembershipController:postMyMembership')->setName('postMyMembership');

	$this->get('/membership/inviteChild', 'MembershipController:getInviteChild')->setName('inviteChild');

	$this->post('/membership/inviteChild/', 'MembershipController:postInviteChild')->setName('postInviteChild');

})->add(new AuthMiddleware($container));