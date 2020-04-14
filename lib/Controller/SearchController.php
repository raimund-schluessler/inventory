<?php
/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2020 Raimund Schlüßler raimund.schluessler@mailbox.org
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Inventory\Controller;

use OCP\AppFramework\Controller;
use OCP\IRequest;
use OCA\Inventory\Service\SearchService;

class SearchController extends Controller {
	private $searchService;
	public $appName;

	public function __construct($AppName, IRequest $request, SearchService $searchService) {
		parent::__construct($AppName, $request);
		$this->appName = $AppName;
		$this->searchService = $searchService;
	}

	/**
	 * @NoAdminRequired
	 * @param $search
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function find($searchString) {
		return $this->searchService->findByString($searchString);
	}
}
