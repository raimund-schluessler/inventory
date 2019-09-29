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
use OCA\Inventory\Service\IteminstanceService;


class InstanceController extends Controller {

	private $iteminstanceService;
	public $appName;

	public function __construct($AppName, IRequest $request, IteminstanceService $iteminstanceService) {
		parent::__construct($AppName, $request);
		$this->appName = $AppName;
		$this->iteminstanceService = $iteminstanceService;
	}

	/**
	 * Adds an instance of an item
	 * 
	 * @NoAdminRequired
	 * @param $itemID	The item Id
	 * @param $instance	The instance properties
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function add($itemID, $instance) {
		$instance['itemid'] = $itemID;
		return $this->iteminstanceService->add($instance);
	}

	/**
	 * Removes an instance of an item
	 * 
	 * @NoAdminRequired
	 * @param $itemID		The item Id
	 * @param $instanceID	The instance Id
	 */
	public function delete($itemID, $instanceID) {
		return $this->iteminstanceService->delete($itemID, $instanceID);
	}

	/**
	 * @NoAdminRequired
	 * @param $itemID
	 * @param $instanceID
	 * @param $uuid
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function addUuid($itemID, $instanceID, $uuid) {
		return $this->iteminstanceService->addUuid($itemID, $instanceID, $uuid);
	}

	/**
	 * @NoAdminRequired
	 * @param $itemID
	 * @param $instanceID
	 * @param $uuid
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function deleteUuid($itemID, $instanceID, $uuid) {
		return $this->iteminstanceService->deleteUuid($itemID, $instanceID, $uuid);
	}
}
