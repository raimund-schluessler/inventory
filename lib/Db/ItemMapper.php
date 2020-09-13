<?php
/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2020 Raimund Schlüßler raimund.schluessler@mailbox.org
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

class ItemMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'invtry_items');
	}

	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 */
	public function find(int $id, string $uid) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_items')
			->where(
				$qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($uid, IQueryBuilder::PARAM_STR))
			);

		return $this->findEntity($qb);
	}

	public function findAll(string $uid, $limit = null, $offset = null) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_items')
			->setMaxResults($limit)
			->setFirstResult($offset)
			->where(
				$qb->expr()->eq('uid', $qb->createNamedParameter($uid, IQueryBuilder::PARAM_STR))
			);

		return $this->findEntities($qb);
	}

	public function findByString(string $uid, string $searchString, $limit = null, $offset = null) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_items')
			->setMaxResults($limit)
			->setFirstResult($offset)
			->andWhere('LOWER(name) LIKE LOWER(:searchString)')
			->orWhere('LOWER(maker) LIKE LOWER(:searchString)')
			->orWhere('LOWER(description) LIKE LOWER(:searchString)')
			->orWhere('LOWER(item_number) LIKE LOWER(:searchString)')
			->orWhere('LOWER(link) LIKE LOWER(:searchString)')
			->orWhere('LOWER(gtin) LIKE LOWER(:searchString)')
			->orWhere('LOWER(details) LIKE LOWER(:searchString)')
			->orWhere('LOWER(comment) LIKE LOWER(:searchString)')
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($uid, IQueryBuilder::PARAM_STR))
			)
			->setParameter('searchString', '%' . $searchString . '%');

		return $this->findEntities($qb);
	}

	public function findByFolderId(string $uid, int $folderid, $limit = null, $offset = null) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_items')
			->setMaxResults($limit)
			->setFirstResult($offset)
			->where(
				$qb->expr()->eq('uid', $qb->createNamedParameter($uid, IQueryBuilder::PARAM_STR))
			)
			->andWhere(
				$qb->expr()->eq('folderid', $qb->createNamedParameter($folderid, IQueryBuilder::PARAM_INT))
			);

		return $this->findEntities($qb);
	}

	public function findItemsByIds(array $itemIds, string $uid, $limit = null, $offset = null) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_items')
			->setMaxResults($limit)
			->setFirstResult($offset)
			->where(
				$qb->expr()->eq('uid', $qb->createNamedParameter($uid, IQueryBuilder::PARAM_STR))
			)
			->andWhere('id IN (:itemIds)')
			->setParameter('itemIds', $itemIds, IQueryBuilder::PARAM_INT_ARRAY);

		return $this->findEntities($qb);
	}

	public function findCandidates(int $itemID, array $excludeIDs, string $uid, $limit = null, $offset = null) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_items')
			->setMaxResults($limit)
			->setFirstResult($offset)
			->where(
				$qb->expr()->eq('uid', $qb->createNamedParameter($uid, IQueryBuilder::PARAM_STR))
			)
			->andWhere('id NOT IN (:exclude)')
			->setParameter('exclude', $excludeIDs, IQueryBuilder::PARAM_INT_ARRAY);

		return $this->findEntities($qb);
	}

	public function add($params) {
		$item = new Item();
		$item->setUid($params['uid']);
		$item->setName($params['name']);
		$item->setMaker($params['maker']);
		$item->setDescription($params['description']);
		$item->setItemNumber($params['item_number']);
		$item->setLink($params['link']);
		$item->setGtin($params['gtin']);
		$item->setDetails($params['details']);
		$item->setComment($params['comment']);
		$item->setType($params['type']);
		$item->setPath($params['path']);
		$item->setFolderid($params['folderid']);
		return $this->insert($item);
	}
}
