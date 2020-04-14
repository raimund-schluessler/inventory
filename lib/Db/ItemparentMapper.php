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

class ItemparentMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'invtry_parent_map');
	}

	public function findRelation(int $itemID, int $parentID, string $userID) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_parent_map')
			->where(
				$qb->expr()->eq('itemid', $qb->createNamedParameter($itemID, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('parentid', $qb->createNamedParameter($parentID, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($userID, IQueryBuilder::PARAM_STR))
			);

		return $this->findEntity($qb);
	}

	public function find(int $itemID) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_parent_map')
			->where(
				$qb->expr()->eq('parentid', $qb->createNamedParameter($itemID, IQueryBuilder::PARAM_INT))
			)
			->orWhere(
				$qb->expr()->eq('itemid', $qb->createNamedParameter($itemID, IQueryBuilder::PARAM_INT))
			);

		return $this->findEntities($qb);
	}

	public function findSub(int $parentId) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_parent_map')
			->where(
				$qb->expr()->eq('parentid', $qb->createNamedParameter($parentId, IQueryBuilder::PARAM_INT))
			);

		return $this->findEntities($qb);
	}

	public function findSubIDs(int $itemID) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('itemid')
			->from('*PREFIX*invtry_parent_map')
			->where(
				$qb->expr()->eq('parentid', $qb->createNamedParameter($itemID, IQueryBuilder::PARAM_INT))
			);
		$cursor = $qb->execute();
		$subIDs = [];
		while ($row = $cursor->fetch()) {
			array_push($subIDs, $row['itemid']);
		};
		$cursor->closeCursor();
		return $subIDs;
	}

	public function findParent(int $itemID) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_parent_map')
			->where(
				$qb->expr()->eq('itemid', $qb->createNamedParameter($itemID, IQueryBuilder::PARAM_INT))
			);

		return $this->findEntities($qb);
	}

	public function findParentIDs(int $itemID) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('parentid')
			->from('*PREFIX*invtry_parent_map')
			->where(
				$qb->expr()->eq('itemid', $qb->createNamedParameter($itemID, IQueryBuilder::PARAM_INT))
			);
		$cursor = $qb->execute();
		$subIDs = [];
		while ($row = $cursor->fetch()) {
			array_push($subIDs, $row['parentid']);
		};
		$cursor->closeCursor();
		return $subIDs;
	}

	public function add($params) {
		$itemparent = new Itemparent();
		$itemparent->setUid($params['uid']);
		$itemparent->setItemid($params['itemid']);
		$itemparent->setParentid($params['parentid']);
		return $this->insert($itemparent);
	}

	public function deleteRelations($relations) {
		foreach ($relations as $relation) {
			$this->delete($relation);
		}
	}
}
