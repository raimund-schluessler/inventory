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

namespace OCA\Inventory\Service;

use OCP\IConfig;
use OCA\Inventory\Db\IteminstanceUuidMapper;
use OCA\Inventory\Db\Place;
use OCA\Inventory\Db\PlaceMapper;
use OCA\Inventory\Db\PlaceUuidMapper;
use OCA\Inventory\Db\ItemMapper;
use OCA\Inventory\Db\IteminstanceMapper;
use OCA\Inventory\BadRequestException;
use OCP\AppFramework\Db\DoesNotExistException;

class PlacesService {
	private $userId;
	private $settings;
	private $AppName;
	private $placeMapper;
	private $placeUuidMapper;
	private $itemMapper;
	private $iteminstanceMapper;
	private $iteminstanceUuidMapper;

	/**
	 * @param string $userId
	 * @param IConfig $settings
	 * @param string $AppName
	 */
	public function __construct(string $userId, IConfig $settings, string $AppName, PlaceMapper $placeMapper, PlaceUuidMapper $placeUuidMapper,
		ItemMapper $itemMapper, IteminstanceMapper $iteminstanceMapper, IteminstanceUuidMapper $iteminstanceUuidMapper) {
		$this->userId = $userId;
		$this->appName = $AppName;
		$this->settings = $settings;
		$this->placeMapper = $placeMapper;
		$this->placeUuidMapper = $placeUuidMapper;
		$this->iteminstanceUuidMapper = $iteminstanceUuidMapper;
		$this->itemMapper = $itemMapper;
		$this->iteminstanceMapper = $iteminstanceMapper;
	}

	/**
	 * Get the places by place
	 *
	 * @return array
	 */
	public function getByPlace($path):array {
		$parentId = $this->getIdByPath($path);
		return $this->placeMapper->findByParentId($this->userId, $parentId);
	}

	/**
	 * Get a place by its path
	 *
	 * @param $path
	 * @return Place
	 * @throws DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws BadRequestException
	 */
	public function get($path) {
		if ($path === '') {
			return [
				'id' => -1,
				'uid' => $this->userId,
				'name' => 'Global',
				'path' => '',
				'parentid' => null,
			];
		}
		$place = $this->placeMapper->findPlaceByPath($this->userId, $path);
		$place->uuids = $this->placeUuidMapper->findByPlaceId($place->id, $this->userId);
		return $place;
	}
	
	/**
	 * Add a place
	 *
	 */
	public function add($name, $path) {
		if ($path !== "") {
			$fullPath = $path . "/" . $name;
		} else {
			$fullPath = $name;
		}

		return $this->create($fullPath);
	}

	public function create($path) {
		// Split the path at the last slash
		$pos = strrpos($path, '/');
		// Get the name of the place and trim whitespaces
		$name = ($pos === false) ? $path : substr($path, $pos + 1);
		$name = trim($name);
		// Get the path of the parent place
		$parentPath = ($pos === false) ? '' : substr($path, 0, $pos);

		// Check if the name is allowed
		$this->isNameAllowed($name, $path);

		// Get the id of the parent place
		if ($parentPath === '') {
			$parentId = -1;
		} else {
			try {
				$parent = $this->placeMapper->findPlaceByPath($this->userId, $parentPath);
			} catch (DoesNotExistException $e) {
				$parent = $this->create($parentPath);
			}
			$parentId = $parent->id;
		}
		return $this->placeMapper->add($name, $path, $this->userId, $parentId);
	}

	/**
	 * Rename a place
	 */
	public function rename($placeId, $newName) {
		$name = trim($name);

		try {
			$place = $this->placeMapper->findPlace($this->userId, $placeId);
		} catch (DoesNotExistException $e) {
			throw new BadRequestException('Place does not exist.');
		}

		if ($place->parentid === -1) {
			$fullPath = $newName;
		} else {
			$parent = $this->placeMapper->findPlace($this->userId, $place->parentid);
			$fullPath = $parent->path . '/' . $newName;
		}

		$this->isNameAllowed($newName, $fullPath);

		return $this->renamePlace($place, $fullPath, $newName);
	}

	private function renamePlace($place, $newPath, $newName = null) {
		$place->setPath($newPath);
		if ($newName) {
			$place->setName($newName);
		}

		// Update all subplaces
		$subPlaces = $this->placeMapper->findByParentId($this->userId, $place->id);
		foreach ($subPlaces as $subPlace) {
			$fullPath = $place->path . '/' . $subPlace->name;
			$this->renamePlace($subPlace, $fullPath);
		}

		// Rename the place itself
		return $this->placeMapper->update($place);
	}

	/**
	 * Check that the place name is valid.
	 *
	 * The names
	 * "item-(\\d+)"
	 * are not allowed as they interfere with the routing.
	 *
	 * Also the name must not be empty, already exist or contain "/".
	 */
	private function isNameAllowed($name, $fullPath) {
		if (strpos($name, "/")) {
			throw new BadRequestException('"/" is not allowed inside a place name.');
		}
		if (strlen($name) === 0) {
			throw new BadRequestException('Place name cannot be empty.');
		}

		if (preg_match('/item-\d+/', $name)) {
			throw new BadRequestException('This name is not allowed');
		}

		if ($this->doesPlaceExist($fullPath)) {
			throw new BadRequestException('Place ' . $name . ' already exists.');
		}
		return true;
	}
	
	/**
	 * Delete a place
	 *
	 * @param int $placeId		The id of the place to delete
	 * @throws DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws BadRequestException
	 */
	public function delete($placeId) {
		if (is_numeric($placeId) === false) {
			throw new BadRequestException('Place id must be a number.');
		}
		try {
			$place = $this->placeMapper->findPlace($this->userId, $placeId);
		} catch (DoesNotExistException $e) {
			throw new BadRequestException('Place did not exist.');
		}

		return $this->deletePlace($place);
	}

	private function deletePlace($place) {
		// Delete all instances in this place
		$instances = $this->iteminstanceMapper->findByPlaceId($this->userId, $place->id);
		foreach ($instances as $instance) {
			$instance = $this->iteminstanceMapper->find($instance->id, $this->userId);
			// Delete all UUIDs belonging to this instance
			$uuids = $this->iteminstanceUuidMapper->findByInstanceId($instance->id, $this->userId);
			foreach ($uuids as $uuid) {
				$this->iteminstanceUuidMapper->delete($uuid);
			}
			$this->iteminstanceMapper->delete($instance);
		}

		// Delete all subplaces
		$subPlaces = $this->placeMapper->findByParentId($this->userId, $place->id);
		foreach ($subPlaces as $subPlace) {
			$this->deletePlace($subPlace);
		}

		// Delete the place itself
		return $this->placeMapper->delete($place);
	}

	/**
	 * Move a place to a new parent
	 *
	 * @param $placeId
	 * @param $newPath
	 * @return Place
	 * @throws DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws BadRequestException
	 */
	public function move($placeId, $newPath) {
		if (is_numeric($placeId) === false) {
			throw new BadRequestException('Place id must be a number.');
		}

		try {
			$place = $this->placeMapper->findPlace($this->userId, $placeId);
		} catch (DoesNotExistException $e) {
			throw new BadRequestException('Place does not exist.');
		}
		if ($newPath === '') {
			$newParent = new Place();
			$newParent->setId(-1);
			$newParent->setPath('');
		} else {
			try {
				$newParent = $this->placeMapper->findPlaceByPath($this->userId, $newPath);
			} catch (DoesNotExistException $e) {
				throw new BadRequestException('New parent place does not exist.');
			}
		}

		// Check that the new parent does not have a child named like this already.
		if ($newParent->path === '') {
			$newFullPath = $place->name;
		} else {
			$newFullPath = $newParent->path . '/' . $place->name;
		}
		if ($this->doesPlaceExist($newFullPath)) {
			throw new BadRequestException('Could not move "' . $place->name . '", target exists.');
		}

		return $this->movePlace($place, $newParent);
	}

	/**
	 * Get the places by strings
	 *
	 * @param $searchString
	 * @return array
	 */
	public function findByString($searchString) {
		$places = $this->placeMapper->findByString($this->userId, $searchString);

		$placeIds = array_map(function ($place) {
			return $place->id;
		}, $places);

		$uuids = $this->placeUuidMapper->findByString($this->userId, $searchString);
		foreach ($uuids as $uuid) {
			// We only add the place if it is not present already
			if (!in_array($uuid->placeid, $placeIds)) {
				$places[] = $this->placeMapper->findPlace($this->userId, $uuid->placeid);
			}
		}

		return $places;
	}

	private function movePlace($place, $newParent) {
		$oldFullPath = $place->path;
		if ($newParent->path === '') {
			$newFullPath = $place->name;
		} else {
			$newFullPath = $newParent->path . '/' . $place->name;
		}

		$place->setPath($newFullPath);
		$place->setParentid($newParent->id);

		// Move all subplaces
		$subPlaces = $this->getByPlace($oldFullPath);
		foreach ($subPlaces as $subPlace) {
			$this->movePlace($subPlace, $place);
		}

		// Move the place itself
		return $this->placeMapper->update($place);
	}

	private function getIdByPath($path) {
		if ($path === '') {
			return -1;
		} else {
			$parent = $this->placeMapper->findPlaceByPath($this->userId, $path);
			return $parent->id;
		}
	}

	private function doesPlaceExist($path) {
		try {
			$place = $this->placeMapper->findPlaceByPath($this->userId, $path);
			return true;
		} catch (DoesNotExistException $e) {
			return false;
		}
	}

	public function setDescription($placeId, $newDescription) {
		$place = $this->placeMapper->findPlace($this->userId, $placeId);
		if ($place) {
			$place->description = $newDescription;
			return $this->placeMapper->update($place);
		}
		throw new BadRequestException('The place to edit does not exist.');
	}

	/**
	 * Adds a UUID to a place
	 */
	public function addUuid($placeId, $uuid) {
		if ($this->isValidUuid($uuid) === false) {
			throw new BadRequestException('The given UUID is invalid.');
		}

		if ($this->placeUuidMapper->find($placeId, $uuid, $this->userId)) {
			throw new BadRequestException('The given UUID is already set for this place.');
		}

		$place = $this->placeMapper->findPlace($this->userId, $placeId);
		if ($place) {
			$params['placeid'] = $placeId;
			$params['uuid'] = $uuid;
			$params['uid'] = $this->userId;
			return $this->placeUuidMapper->add($params);
		}
	}

	/**
	 * Deletes a UUID from a place
	 */
	public function deleteUuid($placeId, $uuid) {
		$uuids = $this->placeUuidMapper->find($placeId, $uuid, $this->userId);
		foreach ($uuids as $uuid) {
			$this->placeUuidMapper->delete($uuid);
		}
	}

	/**
	 * Check if the given string is a valid UUID of version 4, variant 1, RFC 4122/DCE 1.1
	 * @param	string	$uuid	The string to check
	 * @return	boolean
	 */
	private function isValidUuid($uuid) {
		if (!is_string($uuid) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1)) {
			return false;
		}
		return true;
	}
}
