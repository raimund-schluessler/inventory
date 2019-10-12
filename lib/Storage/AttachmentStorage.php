<?php
/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2019 Raimund Schlüßler raimund.schluessler@mailbox.org
 * 
 * @author Julius Härtl
 * @copyright 2018 Julius Härtl <jus@bitgrid.net>
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

namespace OCA\Inventory\Storage;

use OC\Security\CSP\ContentSecurityPolicyManager;
use OCA\Inventory\Db\Attachment;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\EmptyContentSecurityPolicy;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Http\StreamResponse;
use OCP\Files\Folder;
use OCP\Files\IAppData;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;

class AttachmentStorage {

	private $userId;
	private $appData;
	private $rootFolder;

	public function __construct($userId, IAppData $appData, IRootFolder $rootFolder) {
		$this->userId = $userId;
		$this->appData = $appData;
		$this->rootFolder = $rootFolder;
	}

	/**
	 * Get the attachment storage root folder
	 *
	 * @return \OCP\Files\Node
	 * @throws \Exception
	 */
	private function getRootFolder() {
		$name = $this->userId . '/files';
		$appDataFolder = $this->rootFolder->get($name);
		try {
			$folder = $appDataFolder->get('inventory');
		} catch (NotFoundException $e) {
			$folder = $appDataFolder->newFolder('inventory');
		}
		return $folder;

	}

	/**
	 * Get the item folder
	 *
	 * @param int $itemID
	 * @return \OCP\Files\Node
	 * @throws \Exception
	 */
	private function getItemFolder(int $itemID) {
		$appDataFolder = $this->getRootFolder();
		$itemFolderName = 'item-' . (int)$itemID;
		return $appDataFolder->get($itemFolderName);
	}

	/**
	 * Get the instance folder
	 *
	 * @param int $itemID
	 * @param int $instanceID
	 * @return \OCP\Files\Node
	 * @throws \Exception
	 */
	private function getInstanceFolder(int $itemID, int $instanceID) {
		$itemFolder = $this->getItemFolder($itemID);
		$instanceFolderName = 'instances/instance-' . (int)$instanceID;
		return $itemFolder->get($instanceFolderName);
	}

	/**
	 * Get a handle to the attachment
	 *
	 * @param Attachment $attachment
	 * @return \OCP\Files\Node
	 * @throws \Exception
	 */
	private function getFileFromRootFolder(Attachment $attachment) {
		$instanceID = $attachment->getInstanceid();
		if (!$instanceID) {
			$folder = $this->getItemFolder((int)$attachment->getItemid());
		} else {
			$folder = $this->getInstanceFolder((int)$attachment->getItemid(), (int)$instanceID);
		}
		return $folder->get($attachment->getBasename());
	}

	/**
	 * List all files in an item or instance folder
	 *
	 * @param int $itemID
	 * @param int $instanceID
	 * @return Array
	 * @throws \Exception
	 */
	public function listFiles(int $itemID, int $instanceID = null) {
		$files = [];
		try {
			if (!$instanceID) {
				$folder = $this->getItemFolder($itemID);
			} else {
				$folder = $this->getInstanceFolder($itemID, $instanceID);
			}
		} catch (NotFoundException $e) {
			return $files;
		}
		$folderContent = $folder->getDirectoryListing();
		foreach($folderContent as $node) {
			// We only want to list files, not folders.
			if ($node->getFileInfo()->getType() !== \OCP\Files\FileInfo::TYPE_FILE) {
				continue;
			}
			$files[] = pathinfo($node->getName());
		}
		return $files;
	}

	/**
	 * Get more information about an attached file
	 *
	 * @param Attachment $attachment
	 * @return Attachment
	 * @throws \Exception
	 */
	public function extendAttachment(Attachment $attachment) {
		$file = $this->getFileFromRootFolder($attachment);
		$attachment->extendedData = [
			'filesize' => $file->getSize(),
			'mimetype' => $file->getMimeType(),
			'info' => pathinfo($file->getName())
		];
		return $attachment;
	}

	/**
	 * Display an atteched file
	 *
	 * @param Attachment $attachment
	 * @return FileDisplayResponse|\OCP\AppFramework\Http\Response|StreamResponse
	 * @throws \Exception
	 */
	public function display(Attachment $attachment) {
		$file = $this->getFileFromRootFolder($attachment);
		if (method_exists($file, 'fopen')) {
			$response = new StreamResponse($file->fopen('r'));
			$response->addHeader('Content-Disposition', 'inline; filename="' . rawurldecode($file->getName()) . '"');
		} else {
			$response = new FileDisplayResponse($file);
		}
		if ($file->getMimeType() === 'application/pdf') {
			// We need those since otherwise chrome won't show the PDF file with CSP rule object-src 'none'
			// https://bugs.chromium.org/p/chromium/issues/detail?id=271452
			$policy = new ContentSecurityPolicy();
			$policy->addAllowedObjectDomain('\'self\'');
			$policy->addAllowedObjectDomain('blob:');
			$response->setContentSecurityPolicy($policy);
		}
		$response->addHeader('Content-Type', $file->getMimeType());
		return $response;
	}
}
