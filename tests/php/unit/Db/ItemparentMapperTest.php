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
class ItemparentMapperTest extends MapperTestUtility  {

	/** @var IDBConnection */
	private $dbConnection;
	/** @var ItemparentMapper */
	private $itemparentMapper;

	// Data
	private $itemparents;
	private $itemparentsById = [];

	public function setup(): void {
		parent::setUp();

		$this->dbConnection = \OC::$server->getDatabaseConnection();
		$this->itemparentMapper = new ItemparentMapper(
			$this->dbConnection
		);
		$this->itemparents = [
			$this->createItemparentEntity('unit_tester_1', 3, 1),
			$this->createItemparentEntity('unit_tester_1', 3, 2),
			$this->createItemparentEntity('unit_tester_1', 4, 3),
			$this->createItemparentEntity('unit_tester_2', 6, 5),
		];
		foreach ($this->itemparents as $itemparent) {
			$entry = $this->itemparentMapper->insert($itemparent);
			$entry->resetUpdatedFields();
			$this->itemparentsById[$entry->getId()] = $entry;
		}
	}

	private function createItemparentEntity($uid, $itemId, $parentId) {
		$itemparent = new Itemparent();
		$itemparent->setUid($uid);
		$itemparent->setItemid($itemId);
		$itemparent->setParentid($parentId);
		return $itemparent;
	}

	public function testFindRelation() {
		$itemId = 3;
		$parentId = 1;
		$uid = 'unit_tester_1';
		$this->assertEquals($this->itemparents[0], $this->itemparentMapper->findRelation($itemId, $parentId, $uid));
	}

	public function testFindRelation_NotFound() {
		$this->expectException(DoesNotExistException::class);

		$itemId = 1;
		$parentId = 1;
		$uid = 'unit_tester_1';
		$this->itemparentMapper->findRelation($itemId, $parentId, $uid);
	}

	public function testFind() {
		$relations = array_slice($this->itemparents, 0, 3);
		$this->assertEquals($relations, $this->itemparentMapper->find(3));
		$this->assertEquals([], $this->itemparentMapper->find(8));
	}

	public function testFindSub() {
		$relations = array_slice($this->itemparents, 0, 1);
		$this->assertEquals($relations, $this->itemparentMapper->findSub(1));
		$this->assertEquals([], $this->itemparentMapper->findSub(8));
	}

	public function testFindSubIDs() {
		$this->assertEquals([3], $this->itemparentMapper->findSubIDs(1));
		$this->assertEquals([], $this->itemparentMapper->findSubIDs(8));
	}

	public function testFindParent() {
		$relations = array_slice($this->itemparents, 0, 2);
		$this->assertEquals($relations, $this->itemparentMapper->findParent(3));
		$this->assertEquals([], $this->itemparentMapper->findParent(8));
	}

	public function testFindParentIDs() {
		$this->assertEquals([1, 2], $this->itemparentMapper->findParentIDs(3));
		$this->assertEquals([], $this->itemparentMapper->findParentIDs(8));
	}

	public function testAddAndFind() {
		$params['uid'] = 'unit_tester_4';
		$params['itemid'] = 1;
		$params['parentid'] = 4;

		$entry = $this->itemparentMapper->add($params);
		$entry->resetUpdatedFields();

		$itemparent = new Itemparent();
		$itemparent->setUid($params['uid']);
		$itemparent->setItemid($params['itemid']);
		$itemparent->setParentid($params['parentid']);
		$itemparent->setId($entry->getId());
		$itemparent->resetUpdatedFields();

		$this->assertEquals($itemparent, $entry);

		$this->itemparents[] = $itemparent;
	}

	public function tearDown(): void {
		parent::tearDown();
		$this->itemparentMapper->deleteRelations($this->itemparents);
	}

}
