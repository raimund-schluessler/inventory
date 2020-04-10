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

namespace OCA\Inventory\Controller;

use \OCA\Inventory\Service\FoldersService;
use \OCP\AppFramework\Controller;
use \OCP\IRequest;

class FoldersController extends Controller {

	private $foldersService;

	/**
	 * @param string $AppName
	 * @param IRequest $request an instance of the request
	 * @param FoldersService $foldersService
	 */
	public function __construct(string $AppName, IRequest $request, FoldersService $foldersService) {
		parent::__construct($AppName, $request);
		$this->foldersService = $foldersService;
	}

	/**
	 * @NoAdminRequired
	 */
	public function getByFolder($path) {
		return $this->foldersService->getByFolder($path);
	}

	/**
	 * Adds a folder
	 *
	 * @NoAdminRequired
	 * @param $name	The new folder name
	 * @param $path	The path to create the folder at
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function add($name, $path) {
		return $this->foldersService->add($name, $path);
	}

	/**
	 * Deletes a folder
	 *
	 * @NoAdminRequired
	 * @param $folderID		The id of the folder to delete
	 */
	public function delete($folderID) {
		return $this->foldersService->delete($folderID);
	}

	/**
	 * Renames a folder
	 *
	 * @NoAdminRequired
	 * @param $folderID		The id of the folder to edit
	 * @param $newName		The newName
	 */
	public function rename($folderID, $newName) {
		return $this->foldersService->rename($folderID, $newName);
	}

	/**
	 * Moves a folder
	 *
	 * @NoAdminRequired
	 * @param $folderID		The id of the folder to edit
	 * @param $path			The new path
	 */
	public function move($folderID, $path) {
		return $this->foldersService->move($folderID, $path);
	}
}
