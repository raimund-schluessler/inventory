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

class IteminstanceService {

	private $userId;
	private $AppName;
	private $iteminstanceMapper;
	private $placeMapper;

	public function __construct($userId, $AppName, IteminstanceMapper $iteminstanceMapper, PlaceMapper $placeMapper) {
		$this->userId = $userId;
		$this->appName = $AppName;
		$this->iteminstanceMapper = $iteminstanceMapper;
		$this->placeMapper = $placeMapper;
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
					'parent'=> $place->parent
				);
			} else{
				$iteminstance->place = null;
			}
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
		$instance['placeid'] = $place->id;
		$instance['uid'] = $this->userId;
		$this->iteminstanceMapper->add($instance);
	}
}
