<?php

namespace App\Controllers\Notice;

use App\Controllers\Controller;

use App\Models\Notice;

use App\Models\Person;

use Respect\Validation\Validator as v;

class NoticeController extends Controller {

	
/*************************************************************
* Gets 10 notices from db to be displayed on the Notices page
* ************************************************************/
public function getNotices($request, $response, $args) {
	$set = $args['set'];
	$no = $args['no'];
	$page = $args['page'];

	$listLength = 10;

	if ($set == 0) {
		$no = \App\Models\Notice::count();
	}
	$left = $no - $set*$listLength; 
	$lim = ($left > $listLength) ? $listLength : $left;
	$off = $no - ($set+1)*$listLength ;
	$off = ($off < 1) ? 0 : $off;
	if ($left > 0) {
		$notices = \App\Models\Notice::offset($off)->limit($lim)->get();
		$nots = [];
		foreach ($notices as $notice) {
			$entry['date'] = $notice['created_at'];
			$memberId = $notice['member_id'];
			$entry['memberId'] = $memberId;
			if ($memberId === 0) {
				$entry['memberName'] = 'System';
			} else {
				$member = \App\Models\Member::find($memberId);
				if ($member !== null) {
					$entry['memberName'] = $member['name'];
				} else {
					$entry['memberName'] = "unknown member";
				};
			};
			$entry['heading'] = $notice['heading'];
			$entry['notice'] = $notice['notice'];
			$nots[] = $entry;
		};
		$set++;
	};

	return $this->container->view->render($response, 'Notice/notices.twig', compact('nots', 'set', 'no', 'page'));
}

public function postNotices($request, $response, $args) {
	
	
	return $response->withRedirect($this->container->router->pathFor('knowledgebase'));
}

   /**************************************************
	* Search for notices containing the given text
	* *************************************************/
	public function searchNotices($request, $response) {

		$validation = $this->container->validator->validate($request, [
			'search_term' => v::notEmpty()->isPure($this->container),
		]);


		if ($validation->failed()) {
			$nots = [];
			$searchTerm = $request->getParam('search_term');
			return  $this->container->view->render($response, 'Notice/searchResults.twig', compact('nots', 'searchTerm'));
		};

		$searchTerm = $request->getParam('search_term');

		// Get all notices
		$notices = \App\Models\Notice::all();
		
		// Set found results
		$nots = [];
		// Work through all notices
		foreach($notices as $notice) {
			$memberId = $notice['member_id'];
			$member = \App\Models\Member::find($memberId);
			$memberName = $member['name'];

			$entry['date'] = $notice['date'];
			$entry['memberName'] = $memberName;
			$entry['heading'] = $notice['heading'];
			$entry['notice'] = $notice['notice'];

			// Is search term in body of notice?
			$result = stristr($entry['notice'], $searchTerm);
			if ($result) {
				$nots[] = $entry;
			} else {
				// Is search term in member name of notice?
				$result = stristr($entry['memberName'], $searchTerm);
				if ($result) {
					$nots[] = $entry;
				} else {
					// Is search term in heading of notice?
					$result = stristr($entry['heading'], $searchTerm);
					if ($result) {
						$nots[] = $entry;
					}
				}
			}
		}
		
		return $this->container->view->render($response, 'Notice/searchResults.twig', compact('nots', 'searchTerm'));
	}



}
