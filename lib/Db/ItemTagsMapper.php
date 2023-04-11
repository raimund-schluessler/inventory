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
use OCP\AppFramework\Db\DoesNotExistException;

class ItemTagsMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'invtry_cat_map');
	}

	public function findTags(int $itemId, string $uid, $limit = null, $offset = null) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_cat_map')
			->setMaxResults($limit)
			->setFirstResult($offset)
			->where(
				$qb->expr()->eq('itemid', $qb->createNamedParameter($itemId, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($uid, IQueryBuilder::PARAM_STR))
			);

		return $this->findEntities($qb);
	}

	public function findTagMap(int $itemId, int $tagId, string $uid) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_cat_map')
			->where(
				$qb->expr()->eq('itemid', $qb->createNamedParameter($itemId, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('categoryid', $qb->createNamedParameter($tagId, IQueryBuilder::PARAM_INT))
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

	public function getItemIdsForTags($tagIds, string $uid, int $limit = 0, string $offset = ''): array {
		if (!\is_array($tagIds)) {
			$tagIds = [$tagIds];
		}

		$qb = $this->db->getQueryBuilder();

		$qb->selectDistinct('itemid')
			->from('*PREFIX*invtry_cat_map')
			->where(
				$qb->expr()->in('categoryid', $qb->createNamedParameter($tagIds, IQueryBuilder::PARAM_INT_ARRAY))
			)
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($uid, IQueryBuilder::PARAM_STR))
			);

		if ($limit) {
			if (\count($tagIds) !== 1) {
				throw new \InvalidArgumentException('Limit is only allowed with a single tag');
			}

			$qb->setMaxResults($limit)
				->orderBy('itemid', 'ASC');

			if ($offset !== '') {
				$qb->andWhere($qb->expr()->gt('itemid', $qb->createNamedParameter($offset)));
			}
		}

		$itemIds = [];

		$result = $qb->execute();
		while ($row = $result->fetch()) {
			$itemIds[] = $row['itemid'];
		}

		return $itemIds;
	}

	public function add($params) {
		$itemTag = new ItemTags();
		$itemTag->setUid($params['uid']);
		$itemTag->setItemid($params['itemid']);
		$itemTag->setCategoryid($params['tagid']);
		return $this->insert($itemTag);
	}

	public function deleteItemTags($itemTags) {
		foreach ($itemTags as $itemTag) {
			$this->delete($itemTag);
		}
	}
}
