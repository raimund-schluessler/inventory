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
use Test\TestCase;

/**
 * @group DB
 */
class ItemtypeMapperTest extends TestCase {

	/** @var IDBConnection */
	private $dbConnection;
	/** @var ItemtypeMapper */
	private $itemtypeMapper;

	// Data
	private $itemtypes;
	private $itemtypesById = [];

	public function setup(): void {
		parent::setUp();

		$this->dbConnection = \OC::$server->getDatabaseConnection();
		$this->itemtypeMapper = new ItemtypeMapper(
			$this->dbConnection
		);
		$this->itemtypes = [
			$this->createItemtypeEntity('unit_tester_1', 'tools', 'tool'),
			$this->createItemtypeEntity('unit_tester_1', 'bike', 'bike'),
			$this->createItemtypeEntity('unit_tester_1', 'books', 'book'),
			$this->createItemtypeEntity('unit_tester_2', 'furniture', 'shelf')
		];
		foreach ($this->itemtypes as $itemtype) {
			$entry = $this->itemtypeMapper->insert($itemtype);
			$entry->resetUpdatedFields();
			$this->itemtypesById[$entry->getId()] = $entry;
		}
	}

	private function createItemtypeEntity($uid, $name, $icon) {
		$itemtype = new Itemtype();
		$itemtype->setUid($uid);
		$itemtype->setName($name);
		$itemtype->setIcon($icon);
		return $itemtype;
	}

	public function testFind() {
		$uid = 'unit_tester_1';
		foreach ($this->itemtypesById as $id => $itemtype) {
			$this->assertEquals($itemtype, $this->itemtypeMapper->find($id, $itemtype->getUid()));
		}
	}
	
	public function testFindNotFound() {
		$this->expectException(DoesNotExistException::class);

		$itemtypeId = 10;
		$uid = 'unit_tester_1';
		$this->itemtypeMapper->find($itemtypeId, $uid);
	}
	
	public function testFindAll() {
		$itemtypesByUser = [
			array_slice($this->itemtypes, 0, 3),
			array_slice($this->itemtypes, 3, 1)
		];
		$this->assertEquals($itemtypesByUser[0], $this->itemtypeMapper->findAll('unit_tester_1'));
		$this->assertEquals($itemtypesByUser[1], $this->itemtypeMapper->findAll('unit_tester_2'));
		$this->assertEquals([], $this->itemtypeMapper->findAll('unit_tester_3'));
	}

	public function testAddAndFind() {
		$params['name'] = 'car';
		$params['uid'] = 'unit_tester_1';
		$params['icon'] = 'car';

		$entry = $this->itemtypeMapper->add($params);
		$entry->resetUpdatedFields();

		$itemtype = new Itemtype();
		$itemtype->setUid($params['uid']);
		$itemtype->setName($params['name']);
		$itemtype->setIcon($params['icon']);
		$itemtype->setId($entry->getId());
		$itemtype->resetUpdatedFields();

		$this->assertEquals($itemtype, $entry);

		$this->itemtypes[] = $itemtype;
	}

	public function tearDown(): void {
		parent::tearDown();
		foreach ($this->itemtypes as $itemtype) {
			$this->itemtypeMapper->delete($itemtype);
		}
	}
}
