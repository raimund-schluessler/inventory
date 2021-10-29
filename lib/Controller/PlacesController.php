<?php
/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2020 Raimund Schlüßler <raimund.schluessler@mailbox.org>
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

use \OCA\Inventory\Service\PlacesService;
use \OCP\AppFramework\Controller;
use \OCP\IRequest;

class PlacesController extends Controller {
	private $placesService;

	/**
	 * @param string $AppName
	 * @param IRequest $request an instance of the request
	 * @param PlacesService $placesService
	 */
	public function __construct(string $AppName, IRequest $request, PlacesService $placesService) {
		parent::__construct($AppName, $request);
		$this->placesService = $placesService;
	}

	/**
	 * @NoAdminRequired
	 */
	public function getByPlace($path) {
		return $this->placesService->getByPlace($path);
	}

	/**
	 * @NoAdminRequired
	 * @param $path
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function get($path) {
		return $this->placesService->get($path);
	}

	/**
	 * Adds a place
	 *
	 * @NoAdminRequired
	 * @param $name	The new place name
	 * @param $path	The path to create the place at
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function add($name, $path) {
		return $this->placesService->add($name, $path);
	}

	/**
	 * Deletes a place
	 *
	 * @NoAdminRequired
	 * @param $placeID		The id of the place to delete
	 */
	public function delete($placeID) {
		return $this->placesService->delete($placeID);
	}

	/**
	 * Renames a place
	 *
	 * @NoAdminRequired
	 * @param $placeID		The id of the place to edit
	 * @param $newName		The newName
	 */
	public function rename($placeID, $newName) {
		return $this->placesService->rename($placeID, $newName);
	}

	/**
	 * Moves a place
	 *
	 * @NoAdminRequired
	 * @param $placeID		The id of the place to edit
	 * @param $path			The new path
	 */
	public function move($placeID, $path) {
		return $this->placesService->move($placeID, $path);
	}

	/**
	 * Edit the description of a place
	 *
	 * @NoAdminRequired
	 * @param $placeID		The id of the place to edit
	 * @param $description	The new description of the place
	 */
	public function setDescription($placeID, $description) {
		return $this->placesService->setDescription($placeID, $description);
	}

	/**
	 * @NoAdminRequired
	 * @param $placeID
	 * @param $uuid
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function addUuid($placeID, $uuid) {
		return $this->placesService->addUuid($placeID, $uuid);
	}

	/**
	 * @NoAdminRequired
	 * @param $placeID
	 * @param $uuid
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function deleteUuid($placeID, $uuid) {
		return $this->placesService->deleteUuid($placeID, $uuid);
	}
}
