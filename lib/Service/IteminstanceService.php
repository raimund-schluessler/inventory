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

namespace OCA\Inventory\Service;

use OCP\IConfig;
use OCA\Inventory\Db\Iteminstance;
use OCA\Inventory\Db\IteminstanceMapper;
use OCA\Inventory\Db\PlaceMapper;
use OCA\Inventory\Db\IteminstanceUuidMapper;
use OCA\Inventory\BadRequestException;

class IteminstanceService {

	private $userId;
	private $AppName;
	private $iteminstanceMapper;
	private $placeMapper;
	private $iteminstanceUuidMapper;

	public function __construct($userId, $AppName, IteminstanceMapper $iteminstanceMapper, PlaceMapper $placeMapper,
		IteminstanceUuidMapper $iteminstanceUuidMapper) {
		
		$this->userId = $userId;
		$this->appName = $AppName;
		$this->iteminstanceMapper = $iteminstanceMapper;
		$this->placeMapper = $placeMapper;
		$this->iteminstanceUuidMapper = $iteminstanceUuidMapper;
	}

	/**
	 * get instances by itemID
	 *
	 * @return array
	 */
	public function getByItemID($itemid) {
		$iteminstances = $this->iteminstanceMapper->findByItemID($itemid, $this->userId);
		foreach ($iteminstances as $nr => $iteminstance) {
			$place = $this->placeMapper->findPlace($iteminstance->placeid, $this->userId);
			if ($place) {
				$iteminstance->place = array(
					'id'	=> $place->id,
					'name'	=> $place->name,
					'parent'=> $place->parentid
				);
			} else{
				$iteminstance->place = null;
			}
			$iteminstance->uuids = $this->iteminstanceUuidMapper->findByInstanceId($iteminstance->id, $this->userId);
		}
		return $iteminstances;
	}

	/**
	 * add an instance
	 */
	public function add($instance) {
		$place = $this->placeMapper->findPlaceByName($instance['place'], $this->userId);
		if (!$place) {
			$place = $this->placeMapper->add($instance['place'], $this->userId);
		}
		$instance['uid'] = $this->userId;
		$instance['placeid'] = $place->id;
		$this->iteminstanceMapper->add($instance);
	}

	/**
	 * Deletes all instances of an item
	 */
	public function deleteAllInstancesOfItem($itemId) {
		$instances = $this->iteminstanceMapper->findByItemID($itemId, $this->userId);
		$this->iteminstanceMapper->deleteInstances($instances);
	}

	/**
	 * Adds an UUID to an item instance
	 */
	public function addUuid($itemID, $instanceID, $uuid) {

		if ( $this->isValidUuid($uuid) === false ) {
			throw new BadRequestException('The given UUID is invalid.');
		}

		
		if( $this->iteminstanceUuidMapper->find($instanceID, $uuid, $this->userId) ) {
			throw new BadRequestException('The given UUID is already set for this instance.');
		}

		$iteminstance = $this->iteminstanceMapper->find($instanceID, $this->userId);
		if ($iteminstance) {
			$params['itemid'] = $itemID;
			$params['instanceid'] = $instanceID;
			$params['uuid'] = $uuid;
			$params['uid'] = $this->userId;
			return $this->iteminstanceUuidMapper->add($params);
		}
	}

	/**
	 * Deletes a UUID from an item instance
	 */
	public function deleteUuid($itemID, $instanceID, $uuid) {
		$uuids = $this->iteminstanceUuidMapper->find($instanceID, $uuid, $this->userId);
		foreach ($uuids as $uuid) {
			$this->iteminstanceUuidMapper->delete($uuid);
		}
	}

	/**
	 * Check if the given string is a valid UUID of version 4, variant 1, RFC 4122/DCE 1.1
	 * @param	string	$uuid	The string to check
	 * @return	boolean
	 */
	function isValidUuid( $uuid ) {
		if (!is_string($uuid) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1)) {
			return false;
		}
		return true;
	}
}
