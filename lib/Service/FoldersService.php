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
	private $itemMapper;

	/**
	 * @param string $userId
	 * @param IConfig $settings
	 * @param string $AppName
	 */
	public function __construct(string $userId, IConfig $settings, string $AppName, FolderMapper $folderMapper, ItemMapper $itemMapper) {
		$this->userId = $userId;
		$this->appName = $AppName;
		$this->settings = $settings;
		$this->folderMapper = $folderMapper;
		$this->itemMapper = $itemMapper;
	}

	/**
	 * Get the folders by path
	 *
	 * @return array
	 */
	public function getByPath($path):array {
		$parentId = $this->getIdByPath($path);
		return $this->folderMapper->findByParentId($this->userId, $parentId);
	}
	
	/**
	* Add a folder
	*
	*/
	public function add($name, $path) {
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

		$name = trim($name);

		if ( strpos($name, "/") ) {
			throw new BadRequestException('"/" is not allowed inside a folder name.');
		}
		if ( $name === "" ) {
			throw new BadRequestException('Folder name cannot be empty.');
		}

		if (preg_match('/item-\d+/', $name) || in_array($name, array('additem', 'additems'))) {
			throw new BadRequestException('This name is not allowed');
		}

		if ($path !== "") {
			$fullPath = $path . "/" . $name;
		} else {
			$fullPath = $name;
		}
		if ($this->doesFolderExist($fullPath)) {
			throw new BadRequestException('Folder ' . $name . ' already exists.');
		}

		$parentId = $this->getIdByPath($path);
		return $this->folderMapper->add($name, $fullPath, $parentId, $this->userId);
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

		if ( is_numeric($folderId) === false ) {
			throw new BadRequestException('Folder id must be a number.');
		}

		$folder = $this->folderMapper->findFolderById($this->userId, $folderId);
		$newParent = $this->folderMapper->findFolderByPath($this->userId, $newPath);

		// Check that the new parent does not have a child named like this already.

		return $this->moveFolder($folder, $newParent);
	}

	private function moveFolder($folder, $newParent) {
		$oldFullPath = $folder->path;
		$newFullPath = $newParent->path . '/' . $folder->name;

		$folder->setPath($newFullPath);
		$folder->setParentid($newParent->id);

		// Move all items in this folder
		$items = $this->itemMapper->findByFolderId($this->userId, $folder->id);
		foreach ($items as $item) {
			$item->setPath($newFullPath);
			$this->itemMapper->update($item);
		}

		// Move all subfolders
		$subFolders = $this->getByPath($oldFullPath);
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
