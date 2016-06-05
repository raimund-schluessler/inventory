<?php
/**
 * ownCloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2016 Raimund Schlüßler raimund.schluessler@googlemail.com
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

use \OCA\Inventory\Service\ItemsService;
use \OCP\AppFramework\Controller;
use \OCP\IRequest;

class ItemsController extends Controller {

	private $itemsService;

	use Response;

	public function __construct($appName, IRequest $request, ItemsService $itemsService){
		parent::__construct($appName, $request);
		$this->itemsService = $itemsService;
	}

	/**
	 * @NoAdminRequired
	 */
	public function get(){
		return $this->generateResponse(function () {
			return ['items' => $this->itemsService->get()];
		});
	}
}
