<?php
/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2017 Raimund Schlüßler raimund.schluessler@mailbox.org
 *
 * @author Julius Härtl
 * @copyright 2020 Julius Härtl jus@bitgrid.net
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
	 * Returns a list of all attachments of an item or an instance
	 *
	 * @param int $itemID
	 * @param int $instanceID
	 * @return Attachment[]
	 */
	public function getAll($itemID, $instanceID = null) {

		if (is_numeric($itemID) === false) {
			throw new BadRequestException('Item id must be a number.');
		}

		if ($instanceID !== null && is_numeric($instanceID) === false) {
			throw new BadRequestException('Instance id must be a number.');
		}

		// Scan for new files in the item folder
		$this->scanItemFolder($itemID, $instanceID);
		// Get attachments from the database
		$attachments = $this->attachmentMapper->findAll($itemID, $instanceID);
		foreach($attachments as $key => &$attachment) {
			try {
				$this->attachmentStorage->extendAttachment($attachment);
			} catch (\OCP\Files\NotFoundException $e) {
				// Remove the file from the database if not found in storage
				$this->attachmentMapper->delete($attachment);
				unset($attachments[$key]);
			}
		}
		return $attachments;
	}

	/**
	 * Scans the item or instance folder for files
	 * 
	 * @param int $itemID
	 * @param int $instanceID
	 */
	private function scanItemFolder(int $itemID, int $instanceID = null) {
		// Get all files from the item folder
		$files = $this->attachmentStorage->listFiles($itemID, $instanceID);
		// Add them to the database if not present already
		foreach ($files as $file) {
			$name = $file['basename'];
			if (!$this->attachmentMapper->findByName($itemID, $name, $instanceID)) {
				$attachment = new Attachment();
				$attachment->setItemid($itemID);
				$attachment->setInstanceid($instanceID);
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
	 * @param int $instanceID	The instance Id
	 * @return Response
	 * @throws BadRequestException
	 * @throws NoPermissionException
	 * @throws NotFoundException
	 * @throws \OCP\AppFramework\Db\DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 */
	public function display($itemID, $attachmentID, $instanceID = null) {

		if (is_numeric($itemID) === false) {
			throw new BadRequestException('Item id must be a number.');
		}

		if (is_numeric($attachmentID) === false) {
			throw new BadRequestException('Attachment id must be a number.');
		}

		if ($instanceID !== null && is_numeric($instanceID) === false) {
			throw new BadRequestException('Instance id must be a number.');
		}

		$attachment = $this->attachmentMapper->findAttachment($itemID, $attachmentID, $instanceID);
		if ($attachment) {
			try {
				return $this->attachmentStorage->display($attachment);
			} catch (InvalidAttachmentType $e) {
				throw new NotFoundException();
			}
		}
	}

	/**
	 * @param $itemID
	 * @param $data
	 * @param $instanceID
	 * @return Attachment|\OCP\AppFramework\Db\Entity
	 * @throws NoPermissionException
	 * @throws StatusException
	 * @throws BadRequestException
	 */
	public function create($itemID, $data, int $instanceID = null) {

		if (is_numeric($itemID) === false) {
			throw new BadRequestException('Item id must be a number');
		}

		if ($data === false || $data === null) {
			// throw new BadRequestException('data must be provided');
		}

		$attachment = new Attachment();
		$attachment->setItemid($itemID);
		$attachment->setInstanceid($instanceID);
		$attachment->setCreatedBy($this->userId);
		$attachment->setLastModified(time());
		$attachment->setCreatedAt(time());

		try {
			$this->attachmentStorage->create($attachment);
		} catch (InvalidAttachmentType $e) {
			// just store the data
		}
		if ($attachment->getBasename() === null) {
			throw new StatusException($this->l10n->t('No data was provided to create an attachment.'));
		}
		$attachment = $this->attachmentMapper->insert($attachment);

		// extend data so the frontend can use it properly after creating
		try {
			$this->attachmentStorage->extendAttachment($attachment);
		} catch (InvalidAttachmentType $e) {
			// just store the data
		}
		return $attachment;
	}

	/**
	 * Update an attachment with custom data
	 *
	 * @param $itemID
	 * @param $attachmentID
	 * @param $data
	 * @return mixed
	 * @throws \OCA\Deck\NoPermissionException
	 * @throws \OCP\AppFramework\Db\DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws BadRequestException
	 */
	public function update($itemID, $attachmentID, $data, int $instanceID = null) {

		if (is_numeric($itemID) === false) {
			throw new BadRequestException('Item id must be a number');
		}

		if (is_numeric($attachmentID) === false) {
			throw new BadRequestException('Attachment id must be a number');
		}

		if ($data === false || $data === null) {
			//throw new BadRequestException('data must be provided');
		}

		$attachment = $this->attachmentMapper->findAttachment($itemID, $attachmentID, $instanceID);
		try {
			$this->attachmentStorage->update($attachment);
		} catch (InvalidAttachmentType $e) {
			// just update without further action
		}
		$attachment->setLastModified(time());
		$this->attachmentMapper->update($attachment);
		// extend data so the frontend can use it properly after creating
		try {
			$this->attachmentStorage->extendAttachment($attachment);
		} catch (InvalidAttachmentType $e) {
			// just store the data
		}
		return $attachment;
	}

	/**
	 * @param int $itemID
	 * @param string $attachment
	 * @param int $instanceID
	 * @return Attachment|\OCP\AppFramework\Db\Entity
	 * @throws NoPermissionException
	 * @throws StatusException
	 * @throws BadRequestException
	 */
	public function link(int $itemID, string $attachmentPath, int $instanceID = null) {

		if (is_numeric($itemID) === false) {
			throw new BadRequestException('Item id must be a number');
		}

		if ($instanceID !== null && is_numeric($instanceID) === false) {
			throw new BadRequestException('Instance id must be a number.');
		}

		$newAttachment = new Attachment();
		$newAttachment->setItemid($itemID);
		$newAttachment->setInstanceid($instanceID);
		$newAttachment->setCreatedBy($this->userId);
		$newAttachment->setLastModified(time());
		$newAttachment->setCreatedAt(time());
		$newAttachment->setBasename($attachmentPath);

		// Check that the file exists
		try {
			$this->attachmentStorage->extendAttachment($newAttachment);
		} catch (\OCP\Files\NotFoundException $e) {
			throw new BadRequestException('The selected file does not exist.');
		}

		// Check if the same file is already linked
		$attachment = $this->attachmentMapper->findByName(
			$newAttachment->getItemid(),
			$newAttachment->getBasename(),
			$newAttachment->getInstanceid()
		);
		if (!$attachment) {
			$attachment = $this->attachmentMapper->insert($newAttachment);
		}

		return $this->attachmentStorage->extendAttachment($attachment);
	}
}
