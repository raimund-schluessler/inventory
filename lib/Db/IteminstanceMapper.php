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

class IteminstanceMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'invtry_item_instances');
	}

	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 */
	public function find(int $id, string $uid) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_item_instances')
			->where(
				$qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($uid, IQueryBuilder::PARAM_STR))
			);
		return $this->findEntity($qb);
	}

	public function findByItemID(int $itemid, string $uid, $limit = null, $offset = null) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_item_instances')
			->setMaxResults($limit)
			->setFirstResult($offset)
			->where(
				$qb->expr()->eq('itemid', $qb->createNamedParameter($itemid, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($uid, IQueryBuilder::PARAM_STR))
			);

		return $this->findEntities($qb);
	}

	public function findByPlaceId(string $uid, int $placeId, $limit = null, $offset = null) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_item_instances')
			->setMaxResults($limit)
			->setFirstResult($offset)
			->where(
				$qb->expr()->eq('placeid', $qb->createNamedParameter($placeId, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($uid, IQueryBuilder::PARAM_STR))
			);

		return $this->findEntities($qb);
	}

	public function findByString(string $uid, string $searchString, $limit = null, $offset = null) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_item_instances')
			->setMaxResults($limit)
			->setFirstResult($offset)
			->andWhere('LOWER(vendor) LIKE LOWER(:searchString)')
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($uid, IQueryBuilder::PARAM_STR))
			)
			->setParameter('searchString', '%' . $searchString . '%');

		return $this->findEntities($qb);
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

	public function deleteInstances(array $instances) {
		foreach ($instances as $instance) {
			$this->delete($instance);
		}
	}
}
