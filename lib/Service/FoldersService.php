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
use OCA\Inventory\BadRequestException;
use OCP\AppFramework\Db\DoesNotExistException;

class FoldersService {

	private $userId;
	private $settings;
	private $AppName;
	private $folderMapper;

	/**
	 * @param string $userId
	 * @param IConfig $settings
	 * @param string $AppName
	 */
	public function __construct(string $userId, IConfig $settings, string $AppName, FolderMapper $folderMapper) {
		$this->userId = $userId;
		$this->appName = $AppName;
		$this->settings = $settings;
		$this->folderMapper = $folderMapper;
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
