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

class ItemparentMapper extends Mapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'invtry_parent_map');
	}

	public function find($itemID) {
		$sql = 'SELECT * FROM `*PREFIX*invtry_parent_map` ' .
			'WHERE `parentid` = ? OR `itemid` = ?';
		return $this->findEntities($sql, [$itemID, $itemID]);
	}

	public function findSub($itemID) {
		$sql = 'SELECT * FROM `*PREFIX*invtry_parent_map` ' .
			'WHERE `parentid` = ?';
		return $this->findEntities($sql, [$itemID]);
	}

	public function findSubIDs($itemID) {
		$sql = 'SELECT itemid FROM `*PREFIX*invtry_parent_map` ' .
			'WHERE `parentid` = ?';
		$stmt =  $this->execute($sql, [$itemID]);
		$subIDs = array();
		while ($row = $stmt->fetch()) {
			array_push($subIDs, $row['itemid']);
		};
		$stmt->closeCursor();
		return $subIDs;
	}

	public function findParent($itemID) {
		$sql = 'SELECT * FROM `*PREFIX*invtry_parent_map` ' .
			'WHERE `itemid` = ?';
		return $this->findEntities($sql, [$itemID]);
	}

	public function findParentIDs($itemID) {
		$sql = 'SELECT parentid FROM `*PREFIX*invtry_parent_map` ' .
			'WHERE `itemid` = ?';
		$stmt =  $this->execute($sql, [$itemID]);
		$subIDs = array();
		while ($row = $stmt->fetch()) {
			array_push($subIDs, $row['parentid']);
		};
		$stmt->closeCursor();
		return $subIDs;
	}

	public function add($mapping) {
		$sql = 'INSERT INTO `*PREFIX*invtry_parent_map` (itemid, parentid, uid)'.
				' Values(?, ?, ?)';
		return $this->execute($sql, array($mapping['itemid'], $mapping['parentid'], $mapping['uid']));
	}

	public function deleteRelations($relations) {
		foreach ($relations as $relation) {
			$this->delete($relation);
		}
	}
}
