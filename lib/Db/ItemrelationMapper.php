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

class ItemrelationMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'invtry_rel_map');
	}

	public function find(int $itemID, string $userID) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_rel_map')
			->where(
				$qb->expr()->eq('itemid1', $qb->createNamedParameter($itemID, IQueryBuilder::PARAM_INT))
			)
			->orWhere(
				$qb->expr()->eq('itemid2', $qb->createNamedParameter($itemID, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($userID, IQueryBuilder::PARAM_STR))
			);
		return $this->findEntities($qb);
	}

	public function findExactRelation(int $itemID1, int $itemID2, string $userID) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_rel_map')
			->where(
				$qb->expr()->eq('itemid1', $qb->createNamedParameter($itemID1, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('itemid2', $qb->createNamedParameter($itemID2, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($userID, IQueryBuilder::PARAM_STR))
			);

		return $this->findEntity($qb);
	}

	public function findRelation(int $itemID, string $userID) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_rel_map')
			->where(
				$qb->expr()->eq('itemid1', $qb->createNamedParameter($itemID, IQueryBuilder::PARAM_INT))
			)
			->orWhere(
				$qb->expr()->eq('itemid2', $qb->createNamedParameter($itemID, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($userID, IQueryBuilder::PARAM_STR))
			);
		return $this->findEntities($qb);
	}

	public function findRelatedIDs(int $itemID, string $userID) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_rel_map')
			->where(
				$qb->expr()->eq('itemid1', $qb->createNamedParameter($itemID, IQueryBuilder::PARAM_INT))
			)
			->orWhere(
				$qb->expr()->eq('itemid2', $qb->createNamedParameter($itemID, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($userID, IQueryBuilder::PARAM_STR))
			);
		$cursor = $qb->execute();
		$relatedIDs = [];
		while ($row = $cursor->fetch()) {
			$relatedID = ((int)$row['itemid1'] === $itemID) ? $row['itemid2'] : $row['itemid1'];
			$relatedIDs[] = (int)$relatedID;
		};
		$cursor->closeCursor();
		return $relatedIDs;
	}

	public function add($params) {
		$itemrelation = new Itemrelation();
		$itemrelation->setUid($params['uid']);
		$itemrelation->setItemid1($params['itemid1']);
		$itemrelation->setItemid2($params['itemid2']);
		return $this->insert($itemrelation);
	}

	public function deleteRelations($relations) {
		foreach ($relations as $relation) {
			$this->delete($relation);
		}
	}
}
