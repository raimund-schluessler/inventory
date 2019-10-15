<?php
/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2019 Raimund Schlüßler raimund.schluessler@mailbox.org
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
use \OCA\Inventory\Db\Attachment;
use OCP\AppFramework\Db\DoesNotExistException;

class AttachmentMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'invtry_attachments');
	}

	public function findAll(int $itemID, int $instanceID = null, $limit = null, $offset = null) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_attachments')
			->setMaxResults($limit)
			->setFirstResult($offset)
			->where(
				$qb->expr()->eq('itemid', $qb->createNamedParameter($itemID, IQueryBuilder::PARAM_INT))
			);
			if ($instanceID === null) {
				$qb->andWhere(
					$qb->expr()->isNull('instanceid')
				);
			} else {
				$qb->andWhere(
					$qb->expr()->eq('instanceid', $qb->createNamedParameter($instanceID, IQueryBuilder::PARAM_INT))
				);
			}
		return $this->findEntities($qb);
	}


	public function findAttachment(int $itemID, int $attachmentID, int $instanceID = null) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_attachments')
			->where(
				$qb->expr()->eq('itemid', $qb->createNamedParameter($itemID, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('id', $qb->createNamedParameter($attachmentID, IQueryBuilder::PARAM_INT))
			);
			if ($instanceID === null) {
				$qb->andWhere(
					$qb->expr()->isNull('instanceid')
				);
			} else {
				$qb->andWhere(
					$qb->expr()->eq('instanceid', $qb->createNamedParameter($instanceID, IQueryBuilder::PARAM_INT))
				);
			}

		try {
			return $this->findEntity($qb);
		} catch (DoesNotExistException $e) {
			return false;
		}
	}

	public function findByName(int $itemID, string $name, int $instanceID = null) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('*PREFIX*invtry_attachments')
			->where(
				$qb->expr()->eq('itemid', $qb->createNamedParameter($itemID, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('basename', $qb->createNamedParameter($name, IQueryBuilder::PARAM_STR))
			);
			if ($instanceID === null) {
				$qb->andWhere(
					$qb->expr()->isNull('instanceid')
				);
			} else {
				$qb->andWhere(
					$qb->expr()->eq('instanceid', $qb->createNamedParameter($instanceID, IQueryBuilder::PARAM_INT))
				);
			}

		try {
			return $this->findEntity($qb);
		} catch (DoesNotExistException $e) {
			return false;
		}
	}
}
