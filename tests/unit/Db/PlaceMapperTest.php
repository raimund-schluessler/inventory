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
class PlaceMapperTest extends MapperTestUtility  {

	/** @var IDBConnection */
	private $dbConnection;
	/** @var PlaceMapper */
	private $placeMapper;

	// Data
	private $places;
	private $placesById = [];

	public function setup(){
		parent::setUp();

		$this->dbConnection = \OC::$server->getDatabaseConnection();
		$this->placeMapper = new PlaceMapper(
			$this->dbConnection
		);
		$this->places = [
			$this->createPlaceEntity('Living Room', null),
			$this->createPlaceEntity('Kitchen', 1),
			$this->createPlaceEntity('Basement', 1),
			$this->createPlaceEntity('Bathroom', 1)
		];
		foreach ($this->places as $place) {
			$entry = $this->placeMapper->insert($place);
			$entry->resetUpdatedFields();
			$this->placesById[$entry->getId()] = $entry;
		}
	}

	private function createPlaceEntity($name, $parentId) {
		$place = new Place();
		$place->setUid('unit_tester_1');
		$place->setName($name);
		$place->setParentid($parentId);
		return $place;
	}

	public function testFind() {
		$uid = 'unit_tester_1';
		foreach ($this->placesById as $id => $place) {
			$this->assertEquals($place, $this->placeMapper->findPlace($place->getId(), $uid));
		}
	}
	
	public function testFindNotFound() {
		$placeId = 10;
		$uid = 'unit_tester_1';
		$this->assertEquals(false, $this->placeMapper->findPlace($placeId, $uid));
	}
	
	public function testFindCategoryByName() {
		$name = 'Living Room';
		$uid = 'unit_tester_1';
		$this->assertEquals($this->places[0], $this->placeMapper->findPlaceByName($name, $uid));
	}
	
	public function testFindByNameNotFound() {
		$name = 'Bedroom';
		$uid = 'unit_tester_1';
		$this->assertEquals(false, $this->placeMapper->findPlaceByName($name, $uid));
	}

	public function testAddAndFind() {
		$name = 'Python';
		$uid = 'unit_tester_1';
		$parentId = null;

		$entry = $this->placeMapper->add($name, $uid, $parentId);
		$entry->resetUpdatedFields();

		$place = new Place();
		$place->setUid($uid);
		$place->setName($name);
		$place->setParentid($parentId);
		$place->setId($entry->getId());
		$place->resetUpdatedFields();

		$this->assertEquals($place, $entry);

		$this->places[] = $place;
	}

	public function tearDown() {
		parent::tearDown();
		foreach ($this->places as $place) {
			$this->placeMapper->delete($place);
		}
	}

}
