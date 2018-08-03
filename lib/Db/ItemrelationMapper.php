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

class ItemrelationMapper extends Mapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'invtry_rel_map');
	}

	public function findRelation($itemID, $userID) {
		$sql = 'SELECT itemid1 FROM `*PREFIX*invtry_rel_map` ' .
			'WHERE `itemid2` = ? AND `uid` = ? ' .
			'UNION ' .
			'SELECT itemid2 FROM `*PREFIX*invtry_rel_map` ' .
			'WHERE `itemid1` = ? AND `uid` = ?';
		return $this->findEntities($sql, [$itemID, $userID, $itemID, $userID]);
	}

	public function add($mapping) {
		$sql = 'INSERT INTO `*PREFIX*invtry_rel_map` (itemid1, itemid2, uid)'.
				' Values(?, ?, ?)';
		return $this->execute($sql, array($mapping['itemid1'], $mapping['itemid2'], $mapping['uid']));
	}
}
