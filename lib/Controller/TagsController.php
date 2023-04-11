<?php
/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2023 Raimund Schlüßler <raimund.schluessler@mailbox.org>
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

use \OCA\Inventory\Service\TagsService;
use \OCP\AppFramework\Controller;
use \OCP\IRequest;

class TagsController extends Controller {
	private $tagsService;

	/**
	 * @param string $AppName
	 * @param IRequest $request an instance of the request
	 * @param TagsService $tagsService
	 */
	public function __construct(string $AppName, IRequest $request, TagsService $tagsService) {
		parent::__construct($AppName, $request);
		$this->tagsService = $tagsService;
	}

	/**
	 * @NoAdminRequired
	 */
	public function getAll() {
		return $this->tagsService->getAll();
	}
}
