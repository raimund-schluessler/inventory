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
use OCP\AppFramework\Db\Mapper;
use \OCA\Inventory\Db\Attachment;
use OCP\AppFramework\Db\DoesNotExistException;

class AttachmentMapper extends Mapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'invtry_attachments');
	}

	public function findAll($itemID) {
		$sql = 'SELECT * FROM `*PREFIX*invtry_attachments` ' .
			'WHERE `itemid` = ?';
		return $this->findEntities($sql, [$itemID]);
	}

	public function findAttachment($itemId, $attachmentId) {
		$sql = 'SELECT * FROM `*PREFIX*invtry_attachments` ' .
			'WHERE `id` = ? AND `itemid` = ?';
		try {
			return $this->findEntity($sql, [$attachmentId, $itemId]);
		} catch (DoesNotExistException $e) {
			return false;
		}
	}

	public function findByName($itemID, $name) {
		$sql = 'SELECT * FROM `*PREFIX*invtry_attachments` ' .
			'WHERE `itemid` = ? AND `basename` = ?';
		try {
			return $this->findEntity($sql, [$itemID, $name]);
		} catch (DoesNotExistException $e) {
			return false;
		}
	}
}
