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
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use \OCA\Inventory\Db\Place;
use OCP\AppFramework\Db\DoesNotExistException;

class PlaceMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'invtry_places');
	}

	public function findPlace(int $placeId, string $uid) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_places')
			->where(
				$qb->expr()->eq('id', $qb->createNamedParameter($placeId, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($uid, IQueryBuilder::PARAM_STR))
			);

		try {
			return $this->findEntity($qb);
		} catch (DoesNotExistException $e) {
			return false;
		}
	}

	public function findPlaceByName(string $placeName, string $uid) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_places')
			->where(
				$qb->expr()->eq('name', $qb->createNamedParameter($placeName, IQueryBuilder::PARAM_STR))
			)
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($uid, IQueryBuilder::PARAM_STR))
			);

		try {
			return $this->findEntity($qb);
		} catch (DoesNotExistException $e) {
			return false;
		}
	}

	public function add($name, $uid, $parentID=NULL) {
		$place = new Place();
		$place->setName($name);
		$place->setUid($uid);
		$place->setParentid($parentID);
		return $this->insert($place);
	}
}
