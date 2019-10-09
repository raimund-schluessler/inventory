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
class ItemrelationMapperTest extends MapperTestUtility  {

	/** @var IDBConnection */
	private $dbConnection;
	/** @var ItemrelationMapper */
	private $itemrelationMapper;

	// Data
	private $itemrelations;
	private $itemrelationsById = [];

	public function setup(){
		parent::setUp();

		$this->dbConnection = \OC::$server->getDatabaseConnection();
		$this->itemrelationMapper = new ItemrelationMapper(
			$this->dbConnection
		);
		$this->itemrelations = [
			$this->createItemrelationEntity('unit_tester_1', 3, 1),
			$this->createItemrelationEntity('unit_tester_1', 3, 2),
			$this->createItemrelationEntity('unit_tester_1', 4, 3),
			$this->createItemrelationEntity('unit_tester_2', 6, 5),
		];
		foreach ($this->itemrelations as $itemrelation) {
			$entry = $this->itemrelationMapper->insert($itemrelation);
			$entry->resetUpdatedFields();
			$this->itemrelationsById[$entry->getId()] = $entry;
		}
	}

	private function createItemrelationEntity($uid, $itemId1, $itemId2) {
		$itemrelation = new Itemrelation();
		$itemrelation->setUid($uid);
		$itemrelation->setItemid1($itemId1);
		$itemrelation->setItemid2($itemId2);
		return $itemrelation;
	}

	public function testFind() {
		$itemId = 3;
		$uid = 'unit_tester_1';
		$relations = array_slice($this->itemrelations, 0, 3);
		$this->assertEquals($relations, $this->itemrelationMapper->find($itemId, $uid));
	}

	public function testFindExactRelation() {
		$itemId1 = 3;
		$itemId2 = 2;
		$uid = 'unit_tester_1';
		$this->assertEquals($this->itemrelations[1], $this->itemrelationMapper->findExactRelation($itemId1, $itemId2, $uid));
	}

	public function testFindExactRelation_NotFound() {
		$this->expectException(DoesNotExistException::class);

		$itemId1 = 3;
		$itemId2 = 6;
		$uid = 'unit_tester_1';
		$this->itemrelationMapper->findExactRelation($itemId1, $itemId2, $uid);
	}

	public function testFindRelation() {
		$itemId = 3;
		$uid = 'unit_tester_1';
		$relations = array_slice($this->itemrelations, 0, 3);
		// TODO: Adjust the current implementation to really return the relation and not only part of it.
		$relationsReturned  = [];
		foreach($relations as $relation) {
			$tmp = new Itemrelation();
			$id = ($relation->getItemid1() === $itemId) ? $relation->getItemid2() : $relation->getItemid1();
			$tmp->setItemid1($id);
			$tmp->resetUpdatedFields();
			$relationsReturned[] = $tmp;
		}
		$this->assertEquals($relationsReturned, $this->itemrelationMapper->findRelation($itemId, $uid));
		$this->assertEquals([], $this->itemrelationMapper->findRelation(7, $uid));
	}

	public function testFindRelatedIDs() {
		$itemId = 3;
		$uid = 'unit_tester_1';
		$relations = array_slice($this->itemrelations, 0, 3);
		// TODO: Adjust the current implementation to really return the relation and not only part of it.
		$relatedIDs  = [];
		foreach($relations as $relation) {
			$relatedIDs[] = ($relation->getItemid1() === $itemId) ? $relation->getItemid2() : $relation->getItemid1();
		}
		$this->assertEquals($relatedIDs, $this->itemrelationMapper->findRelatedIDs($itemId, $uid));
		$this->assertEquals([], $this->itemrelationMapper->findRelatedIDs(7, $uid));
	}

	public function testAddAndFind() {
		$params['uid'] = 'unit_tester_4';
		$params['itemid1'] = 1;
		$params['itemid2'] = 4;

		$entry = $this->itemrelationMapper->add($params);
		$entry->resetUpdatedFields();

		$itemrelation = new Itemrelation();
		$itemrelation->setUid($params['uid']);
		$itemrelation->setItemid1($params['itemid1']);
		$itemrelation->setItemid2($params['itemid2']);
		$itemrelation->setId($entry->getId());
		$itemrelation->resetUpdatedFields();

		$this->assertEquals($itemrelation, $entry);

		$this->itemrelations[] = $itemrelation;
	}

	public function tearDown() {
		parent::tearDown();
		$this->itemrelationMapper->deleteRelations($this->itemrelations);
	}

}
