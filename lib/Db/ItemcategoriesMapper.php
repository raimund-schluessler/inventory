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

class ItemcategoriesMapper extends Mapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'invtry_item_category_mapping');
	}

	public function findCategories($itemId, $uid) {
		$sql = 'SELECT * FROM `*PREFIX*invtry_item_category_mapping` ' .
			'WHERE `itemid` = ? AND `uid` = ?';
		return $this->findEntities($sql, [$itemId, $uid]);
	}

	public function add($mapping) {
		$sql = 'INSERT INTO `*PREFIX*invtry_item_category_mapping` (itemid, uid, categoryid)'.
				' Values(?, ?, ?)';
		return $this->execute($sql, array(
			$mapping['itemid'],
			$mapping['uid'],
			$mapping['categoryid'])
		);
	}
}
