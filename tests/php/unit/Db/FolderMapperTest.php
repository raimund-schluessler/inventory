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

namespace OCA\Inventory\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IDBConnection;
use Test\AppFramework\Db\MapperTestUtility;

/**
 * @group DB
 */
class FolderMapperTest extends MapperTestUtility  {

	/** @var IDBConnection */
	private $dbConnection;
	/** @var FolderMapper */
	private $folderMapper;

	// Data
	private $folders;
	private $foldersById = [];

	public function setup(): void {
		parent::setUp();

		$this->dbConnection = \OC::$server->getDatabaseConnection();
		$this->folderMapper = new FolderMapper(
			$this->dbConnection
		);
		$this->folders = [
			$this->createFolderEntity('Living Room', 'Living Room', -1),
			$this->createFolderEntity('Kitchen', 'Kitchen', 1),
			$this->createFolderEntity('Basement', 'Basement', 1),
			$this->createFolderEntity('Bathroom', 'Bathromm', 1)
		];
		foreach ($this->folders as $folder) {
			$entry = $this->folderMapper->insert($folder);
			$entry->resetUpdatedFields();
			$this->foldersById[$entry->getId()] = $entry;
		}
	}

	private function createFolderEntity($name, $path, $parentId) {
		$folder = new Folder();
		$folder->setUid('unit_tester_1');
		$folder->setName($name);
		$folder->setPath($path);
		$folder->setParentid($parentId);
		return $folder;
	}
	
	public function testFindFolderByPath() {
		$name = 'Living Room';
		$uid = 'unit_tester_1';
		$this->assertEquals($this->folders[0], $this->folderMapper->findFolderByPath($uid, $name));
	}
	
	public function testFindFolderByPathNotFound() {
		$this->expectException(DoesNotExistException::class);

		$name = 'Bedroom';
		$uid = 'unit_tester_1';
		$this->folderMapper->findFolderByPath($uid, $name);
	}

	public function testFindFolderById() {
		$uid = 'unit_tester_1';
		foreach ($this->foldersById as $id => $folder) {
			$this->assertEquals($folder, $this->folderMapper->findFolderById($uid, $folder->getId()));
		}
	}
	
	public function testFindFolderNotFound() {
		$this->expectException(DoesNotExistException::class);

		$folderId = 10;
		$uid = 'unit_tester_1';
		$this->folderMapper->findFolderById($uid, $folderId);
	}

	public function testFindByParentId() {
		$uid = 'unit_tester_1';
		$this->assertEquals(array_slice($this->folders, 1, 3), $this->folderMapper->findByParentId($uid, 1));
		$this->assertEquals([], $this->folderMapper->findByParentId($uid, 2));
	}

	public function testAddAndFind() {
		$name = 'Python';
		$path = 'Python';
		$uid = 'unit_tester_1';
		$parentId = -1;

		$entry = $this->folderMapper->add($name, $path, $parentId, $uid);
		$entry->resetUpdatedFields();

		$folder = new Folder();
		$folder->setUid($uid);
		$folder->setName($name);
		$folder->setPath($path);
		$folder->setParentid($parentId);
		$folder->setId($entry->getId());
		$folder->resetUpdatedFields();

		$this->assertEquals($folder, $entry);

		$this->folders[] = $folder;
	}

	public function testFindByString() {
		$uid = 'unit_tester_1';

		$this->assertEquals(array_slice($this->folders, 0, 1), $this->folderMapper->findByString($uid, 'Living Room'));
		$this->assertEquals(array_slice($this->folders, 1, 1), $this->folderMapper->findByString($uid, 'tche'));
		$this->assertEquals([], $this->folderMapper->findByString($uid, 'Hallway'));
	}

	public function tearDown(): void {
		parent::tearDown();
		foreach ($this->folders as $folder) {
			$this->folderMapper->delete($folder);
		}
	}

}
