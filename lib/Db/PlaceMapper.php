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

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class PlaceMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'invtry_places');
	}

	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 */
	public function findPlace(string $uid, int $placeId) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_places')
			->where(
				$qb->expr()->eq('id', $qb->createNamedParameter($placeId, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($uid, IQueryBuilder::PARAM_STR))
			);

		return $this->findEntity($qb);
	}

	public function findPlaceByPath(string $uid, string $path) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_places')
			->where(
				$qb->expr()->eq('path', $qb->createNamedParameter($path, IQueryBuilder::PARAM_STR))
			)
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($uid, IQueryBuilder::PARAM_STR))
			);

		return $this->findEntity($qb);
	}

	/**
	 */
	public function findByParentId(string $uid, int $parentId) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_places')
			->where(
				$qb->expr()->eq('parentid', $qb->createNamedParameter($parentId, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($uid, IQueryBuilder::PARAM_STR))
			);

		return $this->findEntities($qb);
	}

	public function add(string $name, string $path, string $uid, int $parentId = null) {
		$place = new Place();
		$place->setName($name);
		$place->setPath($path);
		$place->setUid($uid);
		$place->setParentid($parentId);
		return $this->insert($place);
	}

	public function findByString(string $uid, string $searchString, $limit = null, $offset = null) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_places')
			->where(
				$qb->expr()->eq('uid', $qb->createNamedParameter($uid, IQueryBuilder::PARAM_STR))
			)
			->andWhere('LOWER(name) LIKE LOWER(:searchString)')
			->orWhere('LOWER(path) LIKE LOWER(:searchString)')
			->setParameter('searchString', '%' . $searchString . '%')
			->setMaxResults($limit)
			->setFirstResult($offset);

		return $this->findEntities($qb);
	}
}
