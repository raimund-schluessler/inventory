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

namespace OCA\Inventory\Service;

use OCA\Inventory\Db\Attachment;
use OCA\Inventory\Db\AttachmentMapper;
use OCA\Inventory\Storage\AttachmentStorage;
use OCA\Inventory\BadRequestException;
use OCA\Inventory\InvalidAttachmentType;
use OCA\Inventory\NoPermissionException;
use OCA\Inventory\NotFoundException;
use OCA\Inventory\StatusException;
use OCP\AppFramework\Http\Response;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IL10N;

class AttachmentService {

	private $userId;
	private $attachmentMapper;
	private $attachmentStorage;

	/**
	 * AttachmentService constructor.
	 *
	 * @param $userId
	 * @param AttachmentMapper $attachmentMapper
	 * @param AttachmentStorage $attachmentStorage
	 * @throws \OCP\AppFramework\QueryException
	 */
	public function __construct($userId, AttachmentMapper $attachmentMapper, AttachmentStorage $attachmentStorage) {
		$this->userId = $userId;
		$this->attachmentMapper = $attachmentMapper;
		$this->attachmentStorage = $attachmentStorage;
	}

	/**
	 * Returns a list of all attachments of an item
	 *
	 * @param int $itemID
	 * @return Attachment[]
	 */
	public function getAll($itemID) {

		if (is_numeric($itemID) === false) {
			throw new BadRequestException('Item id must be a number.');
		}

		// Scan for new files in the item folder
		$this->scanItemFolder($itemID);
		// Get attachments from the database
		$attachments = $this->attachmentMapper->findAll($itemID);
		foreach($attachments as &$attachment) {
			$this->attachmentStorage->extendAttachment($attachment);
		}
		return $attachments;
	}

	/**
	 * Scans the item folder for files
	 * 
	 * @param int $itemID
	 */
	private function scanItemFolder(int $itemID) {
		// Get all files from the item folder
		$files = $this->attachmentStorage->listFiles($itemID);
		// Add them to the database if not present already
		foreach ($files as $file) {
			$name = $file['basename'];
			if (!$this->attachmentMapper->findByName($itemID, $name)) {
				$attachment = new Attachment();
				$attachment->setItemid($itemID);
				$attachment->setBasename($name);
				$attachment->setCreatedBy($this->userId);
				$attachment->setLastModified(time());
				$attachment->setCreatedAt(time());
				$this->attachmentMapper->insert($attachment);
			}
		}
	}

	/**
	 * Display the attachment
	 *
	 * @param int $itemID		The item Id
	 * @param int $attachmentID	The attachment Id
	 * @return Response
	 * @throws BadRequestException
	 * @throws NoPermissionException
	 * @throws NotFoundException
	 * @throws \OCP\AppFramework\Db\DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 */
	public function display($itemID, $attachmentID) {

		if (is_numeric($itemID) === false) {
			throw new BadRequestException('Item id must be a number.');
		}

		if (is_numeric($attachmentID) === false) {
			throw new BadRequestException('Attachment id must be a number.');
		}

		$attachment = $this->attachmentMapper->findAttachment($itemID, $attachmentID);
		if ($attachment) {
			try {
				return $this->attachmentStorage->display($attachment);
			} catch (InvalidAttachmentType $e) {
				throw new NotFoundException();
			}
		}
	}
}
