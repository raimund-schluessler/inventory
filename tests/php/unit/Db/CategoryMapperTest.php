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
use OCP\IGroupManager;
use OCP\IUserManager;
use Test\AppFramework\Db\MapperTestUtility;

/**
 * @group DB
 */
class CategoryMapperTest extends MapperTestUtility  {

	/** @var IDBConnection */
	private $dbConnection;
	/** @var CategoryMapper */
	private $categoryMapper;

	// Data
	private $categories;
	private $categoriesById = [];

	public function setup(): void {
		parent::setUp();

		$this->dbConnection = \OC::$server->getDatabaseConnection();
		$this->categoryMapper = new CategoryMapper(
			$this->dbConnection
		);
		$this->categories = [
			$this->createCategoryEntity('Languages', null),
			$this->createCategoryEntity('Javascript', 1),
			$this->createCategoryEntity('PHP', 1),
			$this->createCategoryEntity('C++', 1)
		];
		foreach ($this->categories as $category) {
			$entry = $this->categoryMapper->insert($category);
			$entry->resetUpdatedFields();
			$this->categoriesById[$entry->getId()] = $entry;
		}
	}

	private function createCategoryEntity($name, $parentId) {
		$category = new Category();
		$category->setUid('admin');
		$category->setName($name);
		$category->setParentid($parentId);
		return $category;
	}

	public function testFind() {
		$uid = 'admin';
		foreach ($this->categoriesById as $id => $category) {
			$this->assertEquals($category, $this->categoryMapper->findCategory($category->getId(), $uid));
		}
	}
	
	public function testFindNotFound() {
		$categoryId = 10;
		$uid = 'admin';
		$this->assertEquals(false, $this->categoryMapper->findCategory($categoryId, $uid));
	}
	
	public function testFindCategoryByName() {
		$name = 'Languages';
		$uid = 'admin';
		$this->assertEquals($this->categories[0], $this->categoryMapper->findCategoryByName($name, $uid));
	}
	
	public function testFindByNameNotFound() {
		$name = 'Ruby';
		$uid = 'admin';
		$this->assertEquals(false, $this->categoryMapper->findCategoryByName($name, $uid));
	}

	public function testAddAndFind() {
		$name = 'Python';
		$uid = 'admin';
		$parentId = null;

		$entry = $this->categoryMapper->add($name, $uid, $parentId);
		$entry->resetUpdatedFields();

		$category = new Category();
		$category->setUid($uid);
		$category->setName($name);
		$category->setParentid($parentId);
		$category->setId($entry->getId());
		$category->resetUpdatedFields();

		$this->assertEquals($category, $entry);

		$this->categories[] = $category;
	}

	public function tearDown(): void {
		parent::tearDown();
		foreach ($this->categories as $category) {
			$this->categoryMapper->delete($category);
		}
	}

}
