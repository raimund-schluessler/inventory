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

use OCA\Inventory\AppInfo\Application;
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
	private $AppName;
	private $attachmentMapper;
	private $attachmentStorage;

	/**
	 * AttachmentService constructor.
	 *
	 * @param $userId
	 * @param PermissionService $permissionService
	 * @param Application $application
	 * @throws \OCP\AppFramework\QueryException
	 */
	public function __construct($userId, $AppName, Application $application, AttachmentMapper $attachmentMapper, AttachmentStorage $attachmentStorage) {
		$this->userId = $userId;
		$this->appName = $AppName;
		$this->application = $application;
		$this->attachmentMapper = $attachmentMapper;
		$this->attachmentStorage = $attachmentStorage;
	}

	/**
	 * Returns a list of all attachments
	 * 
	 * @return array
	 */
	public function getAll($itemID) {
		// Scan for new files in the item folder
		$this->scanItemFolder($itemID);
		// Get attachments from the database
		$attachments = $this->attachmentMapper->findAll($itemID);
		foreach($attachments as &$attachment) {
			$this->attachmentStorage->extendAttachment($attachment);
		}
		return $attachments;
	}

	private function scanItemFolder($itemID) {
		// Get all files from the item folder
		$files = $this->attachmentStorage->listFiles($itemID);
		// Add them to the database if not present already
		foreach ($files as $file) {
			$name = $file['basename'];
			if (!$this->attachmentMapper->findByName($itemID, $name)) {
				$attachment = new Attachment();
				$attachment->setItemid($itemID);
				$attachment->setData($name);
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
	 * @param $itemID		The item Id
	 * @param $attachmentID	The attachment Id
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
		try {
			return $this->attachmentStorage->display($attachment);
		} catch (InvalidAttachmentType $e) {
			throw new NotFoundException();
		}
	}
}
