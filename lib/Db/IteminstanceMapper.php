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

namespace OCA\Inventory\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;
use \OCA\Inventory\Db\Iteminstance;

class IteminstanceMapper extends Mapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'invtry_item_instances');
	}

	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 */
	public function find($id, $uid) {
		$sql = 'SELECT * FROM `*PREFIX*invtry_item_instances` ' .
			'WHERE `id` = ? AND `uid` = ?';
		return $this->findEntity($sql, [$id, $uid]);
	}

	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 */
	public function findByItemID($itemid, $uid, $limit=null, $offset=null) {
		$sql = 'SELECT * FROM `*PREFIX*invtry_item_instances`' .
			'WHERE `itemid` = ? AND `uid` = ?';
		return $this->findEntities($sql, [$itemid, $uid], $limit, $offset);
	}

	public function add($params) {
		$itemInstance = new Iteminstance();
		$itemInstance->setItemid($params['itemid']);
		$itemInstance->setUid($params['uid']);
		$itemInstance->setPlaceid($params['placeid']);
		$itemInstance->setPrice($params['price']);
		$itemInstance->setCount($params['count']);
		$itemInstance->setAvailable($params['available']);
		$itemInstance->setVendor($params['vendor']);
		$itemInstance->setDate($params['date']);
		$itemInstance->setComment($params['comment']);
		return $this->insert($itemInstance);
	}
}
