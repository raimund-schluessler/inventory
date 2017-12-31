<?php
/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2017 Raimund Schlüßler raimund.schluessler@mailbox.org
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
use OCA\Inventory\Service\ItemsService;


class ItemsController extends Controller {

	private $itemsService;
	public $appName;

	use Response;

	public function __construct($AppName, IRequest $request, ItemsService $itemsService){
		parent::__construct($AppName, $request);
		$this->appName = $AppName;
		$this->itemsService = $itemsService;
	}

	/**
	 * @NoAdminRequired
	 */
	public function getAll(){
		return $this->generateResponse(function () {
			return ['items' => $this->itemsService->getAll()];
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function get($itemID){
		return $this->generateResponse(function () use ($itemID) {
			return ['item' => $this->itemsService->get($itemID)];
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function getRelated($itemID){
		return $this->generateResponse(function () use ($itemID) {
			return ['items' => $this->itemsService->getRelated($itemID)];
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function getParent($itemID){
		return $this->generateResponse(function () use ($itemID) {
			return ['items' => $this->itemsService->getParent($itemID)];
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function enlist($item){
		return $this->generateResponse(function () use ($item) {
			return $this->itemsService->enlist($item);
		});
	}
}
