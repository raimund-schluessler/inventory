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
use OCA\Inventory\Service\ItemsService;

class ItemsController extends Controller {

	private $itemsService;
	public $appName;

	public function __construct($AppName, IRequest $request, ItemsService $itemsService) {
		parent::__construct($AppName, $request);
		$this->appName = $AppName;
		$this->itemsService = $itemsService;
	}

	/**
	 * @NoAdminRequired
	 */
	public function getAll() {
		return $this->itemsService->getAll();
	}

	/**
	 * @NoAdminRequired
	 * @param $path
	 */
	public function getByFolder($path) {
		return $this->itemsService->getByFolder($path);
	}

	/**
	 * @NoAdminRequired
	 * @param $path
	 */
	public function getByPlace($path) {
		return $this->itemsService->getByPlace($path);
	}

	/**
	 * @NoAdminRequired
	 * @param $itemID
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function get($itemID) {
		return $this->itemsService->get($itemID);
	}

	/**
	 * @NoAdminRequired
	 */
	public function getSub($itemID) {
		return $this->itemsService->getSub($itemID);
	}

	/**
	 * @NoAdminRequired
	 */
	public function getParent($itemID) {
		return $this->itemsService->getParent($itemID);
	}

	/**
	 * @NoAdminRequired
	 */
	public function getRelated($itemID) {
		return $this->itemsService->getRelated($itemID);
	}

	/**
	 * @NoAdminRequired
	 */
	public function getCandidates($itemID, $relationType) {
		return $this->itemsService->getCandidates($itemID, $relationType);
	}

	/**
	 * @NoAdminRequired
	 */
	public function link($itemID, $relationType, $itemIDs) {
		return $this->itemsService->link($itemID, $itemIDs, $relationType);
	}

	/**
	 * @NoAdminRequired
	 */
	public function unlink($itemID, $relationType, $itemIDs) {
		return $this->itemsService->unlink($itemID, $itemIDs, $relationType);
	}

	/**
	 * @NoAdminRequired
	 */
	public function enlist($item) {
		return $this->itemsService->enlist($item);
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete($itemID) {
		return $this->itemsService->delete($itemID);
	}

	/**
	 * @NoAdminRequired
	 */
	public function edit($itemID, $item) {
		return $this->itemsService->edit($itemID, $item);
	}

	/**
	 * @NoAdminRequired
	 */
	public function move($itemID, $path) {
		return $this->itemsService->move($itemID, $path);
	}

	/**
	 * Moves an instance to a different place
	 *
	 * @NoAdminRequired
	 * @param $itemID		The item Id
	 * @param $instanceID	The instance Id
	 * @param $path			The path of the new place
	 */
	public function moveInstance($itemID, $instanceID, $path) {
		return $this->itemsService->moveInstance($itemID, $instanceID, $path);
	}
}
