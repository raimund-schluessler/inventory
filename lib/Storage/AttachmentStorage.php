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
use OCA\Inventory\Db\AttachmentMapper;
use OCA\Inventory\Exceptions\ConflictException;
use OCA\Inventory\StatusException;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\EmptyContentSecurityPolicy;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Http\StreamResponse;
use OCP\Files\Folder;
use OCP\Files\IAppData;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IRequest;
use OCP\IL10N;

class AttachmentStorage {

	private $userId;
	private $appData;
	private $rootFolder;
	private $request;
	private $l10n;
	private $attachmentMapper;

	public function __construct($userId, IAppData $appData, IRootFolder $rootFolder,
	IRequest $request, IL10N $l10n, AttachmentMapper $attachmentMapper) {
		$this->userId = $userId;
		$this->appData = $appData;
		$this->rootFolder = $rootFolder;
		$this->request = $request;
		$this->l10n = $l10n;
		$this->attachmentMapper = $attachmentMapper;
	}

	/**
	 * Get the files root folder
	 *
	 * @return \OCP\Files\Node
	 * @throws \Exception
	 */
	private function getRootFolder() {
		$name = $this->userId . '/files';
		return $this->rootFolder->get($name);
	}

	/**
	 * Get the attachment storage folder
	 *
	 * @return \OCP\Files\Node
	 * @throws \Exception
	 */
	private function getBaseFolder() {
		$appDataFolder = $this->getRootFolder();
		try {
			$folder = $appDataFolder->get('inventory');
		} catch (NotFoundException $e) {
			$folder = $appDataFolder->newFolder('inventory');
		}
		return $folder;
	}

	/**
	 * Get the folder
	 *
	 * @param Attachment $attachment
	 * @param boolean $create			Create the folder if it not exists
	 * @return \OCP\Files\Node
	 * @throws \Exception
	 */
	private function getFolder(Attachment $attachment, $create = false) {
		$instanceID = $attachment->getInstanceid();
		if (!$instanceID) {
			return $this->getItemFolder((int)$attachment->getItemid(), $create);
		} else {
			return $this->getInstanceFolder((int)$attachment->getItemid(), (int)$instanceID, $create);
		}
	}

	/**
	 * Get the item folder
	 *
	 * @param int $itemID
	 * @return \OCP\Files\Node
	 * @throws \Exception
	 */
	private function getItemFolder(int $itemID, $create = false) {
		$appDataFolder = $this->getBaseFolder();
		$itemFolderName = 'item-' . (int)$itemID;
		try {
			return $appDataFolder->get($itemFolderName);
		} catch (NotFoundException $e) {
			if ($create) {
				return $appDataFolder->newFolder($itemFolderName);
			}
			throw $e;
		}
	}

	/**
	 * Get the instance folder
	 *
	 * @param int $itemID
	 * @param int $instanceID
	 * @return \OCP\Files\Node
	 * @throws \Exception
	 */
	private function getInstanceFolder(int $itemID, int $instanceID, $create = false) {
		$itemFolder = $this->getItemFolder($itemID, $create);
		$instanceFolderName = 'instances/instance-' . (int)$instanceID;
		try {
			return $itemFolder->get($instanceFolderName);
		} catch (NotFoundException $e) {
			if ($create) {
				return $itemFolder->newFolder($instanceFolderName);
			}
			throw $e;
		}

	}

	/**
	 * Get a handle to the attachment
	 *
	 * @param Attachment $attachment
	 * @return \OCP\Files\Node
	 * @throws \Exception
	 */
	private function getFileFromRootFolder(Attachment $attachment) {#
		if (strpos($attachment->getBasename(), '/') === 0) {
			$folder = $this->getRootFolder();
		} else {
			$folder = $this->getFolder($attachment);
		}
		return $folder->get(ltrim($attachment->getBasename(), '/'));
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

	/**
	 * @return array
	 * @throws StatusException
	 */
	private function getUploadedFile () {
		$file = $this->request->getUploadedFile('file');
		$error = null;
		$phpFileUploadErrors = [
		UPLOAD_ERR_OK => $this->l10n->t('The file was uploaded'),
		UPLOAD_ERR_INI_SIZE => $this->l10n->t('The uploaded file exceeds the upload_max_filesize directive in php.ini'),
		UPLOAD_ERR_FORM_SIZE => $this->l10n->t('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'),
		UPLOAD_ERR_PARTIAL => $this->l10n->t('The file was only partially uploaded'),
		UPLOAD_ERR_NO_FILE => $this->l10n->t('No file was uploaded'),
		UPLOAD_ERR_NO_TMP_DIR => $this->l10n->t('Missing a temporary folder'),
		UPLOAD_ERR_CANT_WRITE => $this->l10n->t('Could not write file to disk'),
		UPLOAD_ERR_EXTENSION => $this->l10n->t('A PHP extension stopped the file upload'),
		];

		if (empty($file)) {
			$error = $this->l10n->t('No file uploaded or file size exceeds maximum of %s', [\OCP\Util::humanFileSize(\OCP\Util::uploadLimit())]);
		}
		if (!empty($file) && array_key_exists('error', $file) && $file['error'] !== UPLOAD_ERR_OK) {
			$error = $phpFileUploadErrors[$file['error']];
		}
		if ($error !== null) {
			throw new StatusException($error);
		}
		return $file;
	}

	/**
	 * @param Attachment $attachment
	 * @throws NotPermittedException
	 * @throws StatusException
	 * @throws ConflictException
	 */
	public function create(Attachment $attachment) {
		$file = $this->getUploadedFile();
		$folder = $this->getFolder($attachment, true);
		$fileName = $file['name'];

		$attachment->setBasename($fileName);
		if ($folder->nodeExists($fileName)) {
			$attachment = $this->attachmentMapper->findByName($attachment->getItemid(), $fileName, $attachment->getInstanceid());
			throw new ConflictException('File already exists.', $attachment);
		}

		$target = $folder->newFile($fileName);
		$content = fopen($file['tmp_name'], 'rb');
		if ($content === false) {
			throw new StatusException('Could not read file.');
		}
		$target->putContent($content);
		if (is_resource($content)) {
			fclose($content);
		}
	}

	/**
	 * This method requires to be used with POST so we can properly get the form data
	 *
	 * @throws \Exception
	 */
	public function update(Attachment $attachment) {
		$file = $this->getUploadedFile();
		$fileName = $file['name'];
		$attachment->setBasename($fileName);

		$target = $this->getFileFromRootFolder($attachment);
		$content = fopen($file['tmp_name'], 'rb');
		if ($content === false) {
			throw new StatusException('Could not read file');
		}
		$target->putContent($content);
		if (is_resource($content)) {
			fclose($content);
		}

		$attachment->setLastModified(time());
	}
}
