<?php

namespace App\Controllers\Notice;

use App\Controllers\Controller;

use App\Models\Notice;

use App\Models\Person;

use App\Models\Member;

use Respect\Validation\Validator as v;

use Illuminate\Pagination\LengthAwarePaginator;

class NoticeController extends Controller {

	
/***************************************************************
* Paginates notices from db to be displayed on the Notices page
* *************************************************************/

public function getNotices($request, $response, $args) {

	// get a page of notices
	$notices = Notice::paginate(5)->appends($request->getParams()); 
	//Get the associated member names
	$memberNames = [];
	foreach ($notices as $notice) {
		$memberId = $notice['member_id'];
		$memberName = ($memberId > 0) ? Member::find($memberId)->value('name') : 'Admin';
		$memberNames[] = $memberName;
	}
	// Display the page
	return $this->container->view->render($response, 'Notice/notices.twig', compact('notices', 'memberNames'));
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
