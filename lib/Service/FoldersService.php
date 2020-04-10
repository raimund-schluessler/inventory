<?php
/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2020 Raimund Schlüßler <raimund.schluessler@mailbox.org>
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

use OCP\IConfig;
use OCA\Inventory\Db\Folder;
use OCA\Inventory\Db\FolderMapper;
use OCA\Inventory\Db\ItemMapper;
use OCA\Inventory\BadRequestException;
use OCP\AppFramework\Db\DoesNotExistException;

class FoldersService {

	private $userId;
	private $settings;
	private $AppName;
	private $folderMapper;
	private $itemsService;
	private $itemMapper;

	/**
	 * @param string $userId
	 * @param IConfig $settings
	 * @param string $AppName
	 */
	public function __construct(string $userId, IConfig $settings, string $AppName, FolderMapper $folderMapper, ItemsService $itemsService, ItemMapper $itemMapper) {
		$this->userId = $userId;
		$this->appName = $AppName;
		$this->settings = $settings;
		$this->folderMapper = $folderMapper;
		$this->itemsService = $itemsService;
		$this->itemMapper = $itemMapper;
	}

	/**
	 * Get the folders by path
	 *
	 * @return array
	 */
	public function getByFolder($path):array {
		$parentId = $this->getIdByPath($path);
		return $this->folderMapper->findByParentId($this->userId, $parentId);
	}
	
	/**
	 * Add a folder
	 *
	 */
	public function add($name, $path) {
		$name = trim($name);

		if ($path !== "") {
			$fullPath = $path . "/" . $name;
		} else {
			$fullPath = $name;
		}

		$this->isNameAllowed($name, $fullPath);

		$parentId = $this->getIdByPath($path);
		return $this->folderMapper->add($name, $fullPath, $parentId, $this->userId);
	}

	/**
	 * Rename a folder
	 */
	public function rename($folderId, $newName) {
		$name = trim($name);

		$folder = $this->folderMapper->findFolderById($this->userId, $folderId);

		if ($folder->parentid === -1) {
			$fullPath = $newName;
		} else {
			$parent = $this->folderMapper->findFolderById($this->userId, $folder->parentid);
			$fullPath = $parent->path . '/' . $newName;
		}

		$this->isNameAllowed($newName, $fullPath);

		return $this->renameFolder($folder, $fullPath, $newName);
	}

	private function renameFolder($folder, $newPath, $newName = null) {
		$folder->setPath($newPath);
		if ($newName) {
			$folder->setName($newName);
		}

		// Update all items in this folder
		$items = $this->itemMapper->findByFolderId($this->userId, $folder->id);
		foreach ($items as $item) {
			$item->setPath($newPath);
			$this->itemMapper->update($item);
		}

		// Update all subfolders
		$subFolders = $this->folderMapper->findByParentId($this->userId, $folder->id);
		foreach ($subFolders as $subFolder) {
			$fullPath = $folder->path . '/' . $subFolder->name;
			$this->renameFolder($subFolder, $fullPath);
		}

		// Rename the folder itself
		return $this->folderMapper->update($folder);
	}

	/**
	 * Check that the folder name is valid.
	 *
	 * The names
	 * "item-(\\d+)"
	 * "additem"
	 * "additems"
	 * are not allowed as they interfere with the routing.
	 *
	 * Also the name must not be empty, already exist or contain "/".
	 */
	private function isNameAllowed($name, $fullPath) {
		if (strpos($name, "/")) {
			throw new BadRequestException('"/" is not allowed inside a folder name.');
		}
		if ($name === "") {
			throw new BadRequestException('Folder name cannot be empty.');
		}

		if (preg_match('/item-\d+/', $name) || in_array($name, ['additem', 'additems'])) {
			throw new BadRequestException('This name is not allowed');
		}

		if ($this->doesFolderExist($fullPath)) {
			throw new BadRequestException('Folder ' . $name . ' already exists.');
		}
		return true;
	}
	
	/**
	 * Delete a folder
	 *
	 * @param int $folderId		The id of the folder to delete
	 * @throws DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws BadRequestException
	 */
	public function delete($folderId) {

		if (is_numeric($folderId) === false) {
			throw new BadRequestException('Folder id must be a number.');
		}

		$folder = $this->folderMapper->findFolderById($this->userId, $folderId);

		return $this->deleteFolder($folder);
	}

	private function deleteFolder($folder) {
		// Delete all items in this folder
		$items = $this->itemMapper->findByFolderId($this->userId, $folder->id);
		foreach ($items as $item) {
			$this->itemsService->deleteItem($item);
		}

		// Delete all subfolders
		$subFolders = $this->folderMapper->findByParentId($this->userId, $folder->id);
		foreach ($subFolders as $subFolder) {
			$this->deleteFolder($subFolder);
		}

		// Delete the folder itself
		return $this->folderMapper->delete($folder);
	}

	/**
	 * Move a folder to a new parent
	 *
	 * @param $folderId
	 * @param $newPath
	 * @return Folder
	 * @throws DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws BadRequestException
	 */
	public function move($folderId, $newPath) {

		if (is_numeric($folderId) === false) {
			throw new BadRequestException('Folder id must be a number.');
		}

		$folder = $this->folderMapper->findFolderById($this->userId, $folderId);
		if ($newPath === '') {
			$newParent->id = -1;
			$newParent->path = '';
		} else {
			$newParent = $this->folderMapper->findFolderByPath($this->userId, $newPath);
		}

		// Check that the new parent does not have a child named like this already.
		if ($newParent->path === '') {
			$newFullPath = $folder->name;
		} else {
			$newFullPath = $newParent->path . '/' . $folder->name;
		}
		if ($this->doesFolderExist($newFullPath)) {
			throw new BadRequestException('Could not move "' . $folder->name . '", target exists.');
		}

		return $this->moveFolder($folder, $newParent);
	}

	/**
	 * Get the folders by strings
	 *
	 * @param $searchString
	 * @return array
	 */
	public function findByString($searchString) {
		return $this->folderMapper->findByString($this->userId, $searchString);
	}

	private function moveFolder($folder, $newParent) {
		$oldFullPath = $folder->path;
		if ($newParent->path === '') {
			$newFullPath = $folder->name;
		} else {
			$newFullPath = $newParent->path . '/' . $folder->name;
		}

		$folder->setPath($newFullPath);
		$folder->setParentid($newParent->id);

		// Move all items in this folder
		$items = $this->itemMapper->findByFolderId($this->userId, $folder->id);
		foreach ($items as $item) {
			$item->setPath($newFullPath);
			$this->itemMapper->update($item);
		}

		// Move all subfolders
		$subFolders = $this->getByFolder($oldFullPath);
		foreach ($subFolders as $subFolder) {
			$this->moveFolder($subFolder, $folder);
		}

		// Move the folder itself
		return $this->folderMapper->update($folder);
	}

	private function getIdByPath($path) {
		if ($path === '') {
			$parentId = -1;
		} else {
			$parent = $this->folderMapper->findFolderByPath($this->userId, $path);
			$parentId = $parent->id;
		}
		return $parentId;
	}

	private function doesFolderExist($path) {
		try {
			$folder = $this->folderMapper->findFolderByPath($this->userId, $path);
			return true;
		} catch (DoesNotExistException $e) {
			return false;
		}
	}
}
