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
	private $attachmentStorage;

	/**
	 * AttachmentService constructor.
	 *
	 * @param $userId
	 * @param PermissionService $permissionService
	 * @param Application $application
	 * @throws \OCP\AppFramework\QueryException
	 */
	public function __construct($userId, $AppName, Application $application, AttachmentStorage $attachmentStorage) {
		$this->userId = $userId;
		$this->appName = $AppName;
		$this->application = $application;
		$this->attachmentStorage = $attachmentStorage;
	}

	/**
	 * Returns a list of all attachments
	 * 
	 * @return array
	 */
	public function getAll($itemID) {
		$attachments[] = array(
			'id' => 1,
			'itemId' => 3,
			'type' => 'deck_file',
			'data' => 'uuid.csv',
			'lastModified' => 1570127792,
			'createdAt' => 1570127792,
			'createdBy' => 'admin',
			'deletedAt' => 0,
			'extendedData' => array(
				'filesize' => 38000,
				'mimetype' => 'text/csv',
				'info' => array(
					'dirname' => '.',
					'basename' => 'uuid.csv',
					'extension' => 'csv',
					'filename' => 'uuid',
				)
			)
		);
		$attachments[] = array(
			'id' => 2,
			'itemId' => 3,
			'type' => 'deck_file',
			'data' => 'info.pdf',
			'lastModified' => 1570127792,
			'createdAt' => 1570127792,
			'createdBy' => 'admin',
			'deletedAt' => 0,
			'extendedData' => array(
				'filesize' => 380000,
				'mimetype' => 'application/pdf',
				'info' => array(
					'dirname' => '.',
					'basename' => 'info.pdf',
					'extension' => 'pdf',
					'filename' => 'info',
				)
			)
		);
		return $attachments;
		// return $this->attachmentStorage->listAttachments($itemID);
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

		$attachment = new Attachment();
		$attachment->setItemid(3);
		$attachment->setFilename('uuid.csv');
		$attachment->setFilepath('.');
		$attachment->setType('inventory_file');

		try {
			return $this->attachmentStorage->display($attachment);
		} catch (InvalidAttachmentType $e) {
			throw new NotFoundException();
		}
	}
}
