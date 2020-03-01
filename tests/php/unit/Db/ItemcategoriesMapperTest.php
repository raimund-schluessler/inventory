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
class ItemcategoriesMapperTest extends MapperTestUtility  {

	/** @var IDBConnection */
	private $dbConnection;
	/** @var ItemcategoriesMapper */
	private $itemcategoriesMapper;

	// Data
	private $itemcategories;
	private $itemcategoriesById = [];

	public function setup(): void {
		parent::setUp();

		$this->dbConnection = \OC::$server->getDatabaseConnection();
		$this->itemcategoriesMapper = new ItemcategoriesMapper(
			$this->dbConnection
		);
		$this->itemcategories = [
			$this->createItemcategoriesEntity('unit_tester_1', 1, 1),
			$this->createItemcategoriesEntity('unit_tester_1', 1, 2),
			$this->createItemcategoriesEntity('unit_tester_1', 2, 3),
			$this->createItemcategoriesEntity('unit_tester_2', 3, 1)
		];
		foreach ($this->itemcategories as $itemcategory) {
			$entry = $this->itemcategoriesMapper->insert($itemcategory);
			$entry->resetUpdatedFields();
			$this->itemcategoriesById[$entry->getId()] = $entry;
		}
	}

	private function createItemcategoriesEntity($uid, $itemId, $categoryId) {
		$itemcategory = new Itemcategories();
		$itemcategory->setUid($uid);
		$itemcategory->setItemid($itemId);
		$itemcategory->setCategoryid($categoryId);
		return $itemcategory;
	}

	public function testFindCategories() {
		$itemcategoriesByItem = [
			array_slice($this->itemcategories, 0, 2),
			array_slice($this->itemcategories, 3, 1)
		];
		$this->assertEquals($itemcategoriesByItem[0], $this->itemcategoriesMapper->findCategories(1, 'unit_tester_1'));
		$this->assertEquals($itemcategoriesByItem[1], $this->itemcategoriesMapper->findCategories(3, 'unit_tester_2'));
		$this->assertEquals([], $this->itemcategoriesMapper->findCategories(1, 'unit_tester_3'));
	}

	public function testAddAndFind() {
		$params['itemid'] = 1;
		$params['uid'] = 'unit_tester_4';
		$params['categoryid'] = 4;

		$entry = $this->itemcategoriesMapper->add($params);
		$entry->resetUpdatedFields();

		$itemcategory = new Itemcategories();
		$itemcategory->setUid($params['uid']);
		$itemcategory->setCategoryid($params['categoryid']);
		$itemcategory->setItemid($params['itemid']);
		$itemcategory->setId($entry->getId());
		$itemcategory->resetUpdatedFields();

		$this->assertEquals($itemcategory, $entry);

		$this->itemcategories[] = $itemcategory;
	}

	public function tearDown(): void {
		parent::tearDown();
		$this->itemcategoriesMapper->deleteItemCategories($this->itemcategories);
	}

}
