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
class IteminstanceUuidMapperTest extends MapperTestUtility  {

	/** @var IDBConnection */
	private $dbConnection;
	/** @var IteminstanceUuidMapper */
	private $iteminstanceUuidMapper;

	// Data
	private $iteminstanceUuids;
	private $iteminstanceUuidById = [];

	public function setup(): void {
		parent::setUp();

		$this->dbConnection = \OC::$server->getDatabaseConnection();
		$this->iteminstanceUuidMapper = new IteminstanceUuidMapper(
			$this->dbConnection
		);
		$this->iteminstanceUuids = [
			$this->createIteminstanceUuidEntity('unit_tester_1', 1, '550e8400-e29b-41d4-a716-446655440000'),
			$this->createIteminstanceUuidEntity('unit_tester_1', 1, '550e8400-e29b-41d4-a716-446655440001'),
			$this->createIteminstanceUuidEntity('unit_tester_1', 1, '550e8400-e29b-41d4-a716-446655440002'),
			$this->createIteminstanceUuidEntity('unit_tester_2', 2, '550e8400-e29b-41d4-a716-446655440003')
		];
		foreach ($this->iteminstanceUuids as $iteminstanceUuid) {
			$entry = $this->iteminstanceUuidMapper->insert($iteminstanceUuid);
			$entry->resetUpdatedFields();
			$this->iteminstanceUuidsById[$entry->getId()] = $entry;
		}
	}

	private function createIteminstanceUuidEntity($uid, $instanceId, $uuid) {
		$iteminstanceUuid = new IteminstanceUuid();
		$iteminstanceUuid->setInstanceid($instanceId);
		$iteminstanceUuid->setUuid($uuid);
		$iteminstanceUuid->setUid($uid);
		return $iteminstanceUuid;
	}

	public function testFind() {
		foreach ($this->iteminstanceUuidsById as $id => $iteminstanceUuid) {
			$this->assertEquals([$iteminstanceUuid], $this->iteminstanceUuidMapper->find(
				$iteminstanceUuid->getInstanceid(),
				$iteminstanceUuid->getUuid(),
				$iteminstanceUuid->getUid()
			));
		}
	}
	
	public function testFind_NotFound() {
		$instanceId = 11;
		$uuid = '550e8400-e29b-41d4-a716-446655440005';
		$uid = 'unit_tester_1';
		$this->assertEquals([], $this->iteminstanceUuidMapper->find($instanceId, $uuid, $uid));
	}

	public function testFindByInstanceId() {
		$iteminstanceUuidsByInstanceId = [
			array_slice($this->iteminstanceUuids, 0, 3),
			array_slice($this->iteminstanceUuids, 3, 1)
		];
		$this->assertEquals($iteminstanceUuidsByInstanceId[0], $this->iteminstanceUuidMapper->findByInstanceId(1, 'unit_tester_1'));
		$this->assertEquals($iteminstanceUuidsByInstanceId[1], $this->iteminstanceUuidMapper->findByInstanceId(2, 'unit_tester_2'));
	}
	
	public function testFindByInstanceId_NotFound() {
		$instanceId = 11;
		$uid = 'unit_tester_1';
		$this->assertEquals([], $this->iteminstanceUuidMapper->findByInstanceId($instanceId, $uid));
	}

	public function testFindByString() {
		$uid = 'unit_tester_1';

		$this->assertEquals(array_slice($this->iteminstanceUuids, 0, 1), $this->iteminstanceUuidMapper->findByString($uid, '550e8400-e29b-41d4-a716-446655440000'));
		$this->assertEquals(array_slice($this->iteminstanceUuids, 1, 1), $this->iteminstanceUuidMapper->findByString($uid, '446655440001'));
		$this->assertEquals([], $this->iteminstanceUuidMapper->findByString($uid, '1234865'));
	}

	public function testAddAndFind() {
		$params['instanceid'] = 1;
		$params['uid'] = 'unit_tester_1';
		$params['uuid'] = '550e8400-e29b-41d4-a716-446655440006';

		$entry = $this->iteminstanceUuidMapper->add($params);
		$entry->resetUpdatedFields();

		$iteminstanceUuid = new IteminstanceUuid();
		$iteminstanceUuid->setInstanceid($params['instanceid']);
		$iteminstanceUuid->setUuid($params['uuid']);
		$iteminstanceUuid->setUid($params['uid']);
		$iteminstanceUuid->setId($entry->getId());
		$iteminstanceUuid->resetUpdatedFields();

		$this->assertEquals($iteminstanceUuid, $entry);

		$this->iteminstanceUuids[] = $iteminstanceUuid;
	}

	public function tearDown(): void {
		parent::tearDown();
		foreach ($this->iteminstanceUuids as $iteminstanceUuid) {
			$this->iteminstanceUuidMapper->delete($iteminstanceUuid);
		}
	}

}
