<?php
/**
 * ownCloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2016 Raimund Schlüßler raimund.schluessler@googlemail.com
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

class ItemMapper extends Mapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'invtry_items');
	}


	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 */
	public function find($id) {
		$sql = 'SELECT * FROM `*PREFIX*invtry_items` ' .
			'WHERE `id` = ?';
		return $this->findEntity($sql, [$id]);
	}


	public function findAll($limit=null, $offset=null) {
		$sql = 'SELECT * FROM `*PREFIX*invtry_items`';
		return $this->findEntities($sql, [], $limit, $offset);
	}


	public function itemCount($name) {
		$sql = 'SELECT COUNT(*) AS `count` FROM `*PREFIX*invtry_items`';
		$stmt = $this->execute($sql);

		$row = $stmt->fetch();
		$stmt->closeCursor();
		return $row['count'];
	}

	public function add($item) {
		$sql = 'INSERT INTO `*PREFIX*invtry_items` (owner, name, maker, description, place, price, link, count)'.
				' Values(?, ?, ?, ?, ?, ?, ?, ?)';
		return $this->execute($sql, array(	'test',
											$item['name'],
											$item['maker'],
											$item['description'],
											$item['place'],
											$item['price'],
											$item['link'],
											$item['count'])
		);
	}
}
