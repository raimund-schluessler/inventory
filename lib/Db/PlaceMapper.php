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
use \OCA\Inventory\Db\Place;
use OCP\AppFramework\Db\DoesNotExistException;

class PlaceMapper extends Mapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'invtry_places');
	}

	public function findPlace($placeId) {
		$sql = 'SELECT * FROM `*PREFIX*invtry_places` ' .
			'WHERE `id` = ?';
		try {
			return $this->findEntity($sql, [$placeId]);
		} catch (DoesNotExistException $e) {
			return false;
		}
	}

	public function findPlaceByName($placeName) {
		$sql = 'SELECT * FROM `*PREFIX*invtry_places` ' .
			'WHERE `name` = ?';
		try {
			return $this->findEntity($sql, [$placeName]);
		} catch (DoesNotExistException $e) {
			return false;
		}
	}

	public function add($name) {
		$place = new Place();
		$place->setName($name);
		return $this->insert($place);
	}
}
