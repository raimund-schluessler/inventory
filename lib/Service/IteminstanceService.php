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
use OCP\IL10N;
use OCA\Inventory\Db\Iteminstance;
use OCA\Inventory\Db\IteminstanceMapper;
use OCA\Inventory\Db\PlaceMapper;
use OCA\Inventory\Db\IteminstanceUuidMapper;
use OCA\Inventory\BadRequestException;
use OCP\AppFramework\Db\DoesNotExistException;

class IteminstanceService {

	private $userId;
	private $AppName;

	/**
	 * @var IL10N
	 */
	private $l10n;
	private $iteminstanceMapper;
	private $placeMapper;
	private $iteminstanceUuidMapper;

	public function __construct($userId, $AppName, IL10N $l10n, IteminstanceMapper $iteminstanceMapper, PlaceMapper $placeMapper,
		IteminstanceUuidMapper $iteminstanceUuidMapper) {
		
		$this->userId = $userId;
		$this->l10n = $l10n;
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
		$instances = $this->iteminstanceMapper->findByItemID($itemid, $this->userId);
		foreach ($instances as $nr => $instance) {
			$instance = $this->getInstanceDetails($instance);
		}
		return $instances;
	}

	/**
	 * Adds an instance to an item
	 * 
	 * @NoAdminRequired
	 * @param $itemID	The item Id
	 * @param $params	The instance parameters
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function add($instance) {

		if ( $instance['count'] && is_numeric($instance['count']) === false ) {
			throw new BadRequestException('Count must be a number.');
		}

		if ( $instance['available'] && is_numeric($instance['available']) === false ) {
			throw new BadRequestException('Available must be a number.');
		}

		if ($instance['available'] === '') {
			$instance['available'] = NULL;
		}

		if ($instance['count'] === '') {
			$instance['count'] = NULL;
		}

		$instance['uid'] = $this->userId;
		if ($instance['place']) {
			try {
				$place = $this->placeMapper->findPlaceByPath($this->userId, $instance['place']);
			} catch (DoesNotExistException $e) {
				$place = $this->placeMapper->add($instance['place'], $instance['place'], $this->userId, -1);
			}
			$instance['placeid'] = $place->id;
		} else {
			$instance['placeid'] = -1;
		}
		$added = $this->iteminstanceMapper->add($instance);
		return $this->getInstanceDetails($added);
	}

	/**
	 * Removes an instance of an item
	 * 
	 * @NoAdminRequired
	 * @param $itemID		The item Id
	 * @param $instanceId	The instance Id
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function delete($itemId, $instanceId) {
		$instance = $this->iteminstanceMapper->find($instanceId, $this->userId);
		// Delete all UUIDs belonging to this instance
		$uuids = $this->iteminstanceUuidMapper->findByInstanceId($instanceId, $this->userId);
		foreach ($uuids as $uuid) {
			$this->iteminstanceUuidMapper->delete($uuid);
		}
		return $this->iteminstanceMapper->delete($instance);
	}

	/**
	 * Edits an instance of an item
	 * 
	 * @NoAdminRequired
	 * @param $itemID	The item Id
	 * @param $params	The instance parameters
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function edit($itemId, $instanceId, $instance) {
		$localInstance = $this->iteminstanceMapper->find($instanceId, $this->userId);
		$localInstance->setComment($instance['comment']);
		$localInstance->setPrice($instance['price']);
		$localInstance->setCount($instance['count']);
		$localInstance->setAvailable($instance['available']);
		$localInstance->setVendor($instance['vendor']);
		$localInstance->setDate($instance['date']);
		$localInstance->setComment($instance['comment']);
		$localInstance->setPlaceid($instance['placeid']);
		$editedInstance = $this->iteminstanceMapper->update($localInstance);
		return $this->getInstanceDetails($editedInstance);
	}

	public function findByString($searchString) {
		// Find instances directly
		$instances = $this->iteminstanceMapper->findByString($this->userId, $searchString);
		// Also find instances with a UUID
		$uuids = $this->iteminstanceUuidMapper->findByString($this->userId, $searchString);
		foreach ($uuids as $uuid) {
			$instances[] = $this->iteminstanceMapper->find($uuid->instanceid, $this->userId);
		}
		return $instances;
	}

	/**
	 * Gets the place details and the UUIDs of an instance
	 * 
	 * @NoAdminRequired
	 * @param $instance	The instance
	 * @return \OCP\AppFramework\Db\Entity
	 */
	function getInstanceDetails($instance) {
		if (!$instance->placeid) {
			$instance->place = null;
		} elseif ($instance->placeid === -1) {
			$instance->place = array(
				'id'	=> -1,
				'name'	=> $this->l10n->t('No place assigned'),
				'parent'=> null,
				'path'	=> ''
			);
		} else {
			try {
				$place = $this->placeMapper->findPlace($this->userId, $instance->placeid);
				$instance->place = array(
					'id'	=> $place->id,
					'name'	=> $place->name,
					'parent'=> $place->parentid,
					'path'	=> $place->path
				);
			} catch (DoesNotExistException $e) {
				$instance->place = null;
			}
		}
		$instance->uuids = $this->iteminstanceUuidMapper->findByInstanceId($instance->id, $this->userId);
		return $instance;
	}

	/**
	 * Deletes all instances of an item
	 */
	public function deleteAllInstancesOfItem($itemId) {
		$instances = $this->iteminstanceMapper->findByItemID($itemId, $this->userId);
		// Delete all UUIDs belonging to the instances
		foreach($instances as $instance) {
			$uuids = $this->iteminstanceUuidMapper->findByInstanceId($instance->id, $this->userId);
			foreach ($uuids as $uuid) {
				$this->iteminstanceUuidMapper->delete($uuid);
			}
		}
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
