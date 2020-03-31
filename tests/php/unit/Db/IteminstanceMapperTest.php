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
class IteminstanceMapperTest extends MapperTestUtility  {

	/** @var IDBConnection */
	private $dbConnection;
	/** @var IteminstanceMapper */
	private $iteminstanceMapper;

	// Data
	private $iteminstances;
	private $iteminstancesById = [];

	public function setup(): void {
		parent::setUp();

		$this->dbConnection = \OC::$server->getDatabaseConnection();
		$this->iteminstanceMapper = new IteminstanceMapper(
			$this->dbConnection
		);
		$this->iteminstances = [
			$this->createIteminstanceEntity('unit_tester_1', 1, 1, '1.00 €', 2, 1, 'Vendor 1', '2019-01-01', 'Nice'),
			$this->createIteminstanceEntity('unit_tester_1', 1, 2, '10.00 €', 2, 2, 'Vendor 2', '2019-01-02', 'Nice'),
			$this->createIteminstanceEntity('unit_tester_1', 2, 3, '0.50 €', 5, 3, 'Vendor 3', '2019-01-03', 'Nice'),
			$this->createIteminstanceEntity('unit_tester_2', 1, 1, '1.00 €', 4, 1, 'Vendor 1', '2019-01-04', 'Very Nice'),
		];
		foreach ($this->iteminstances as $iteminstance) {
			$entry = $this->iteminstanceMapper->insert($iteminstance);
			$entry->resetUpdatedFields();
			$this->iteminstancesById[$entry->getId()] = $entry;
		}
	}

	private function createIteminstanceEntity($uid, $itemId, $placeId, $price, $count, $available, $vendor, $date, $comment) {
		$iteminstance = new Iteminstance();
		$iteminstance->setUid($uid);
		$iteminstance->setItemid($itemId);
		$iteminstance->setPlaceid($placeId);
		$iteminstance->setPrice($price);
		$iteminstance->setCount($count);
		$iteminstance->setAvailable($available);
		$iteminstance->setVendor($vendor);
		$iteminstance->setDate($date);
		$iteminstance->setComment($comment);
		return $iteminstance;
	}

	public function testFind() {
		$this->assertEquals($this->iteminstances[0], $this->iteminstanceMapper->find($this->iteminstances[0]->getId(), 'unit_tester_1'));
	}

	public function testFind_NotFound() {
		$this->expectException(DoesNotExistException::class);

		$this->assertEquals($this->iteminstances[0], $this->iteminstanceMapper->find($this->iteminstances[0]->getId(), 'unit_tester_5'));
	}

	public function testFindByItemId() {
		$iteminstancesByItemId = [
			array_slice($this->iteminstances, 0, 2),
			array_slice($this->iteminstances, 2, 1)
		];
		$this->assertEquals($iteminstancesByItemId[0], $this->iteminstanceMapper->findByItemID(1, 'unit_tester_1'));
		$this->assertEquals($iteminstancesByItemId[1], $this->iteminstanceMapper->findByItemID(2, 'unit_tester_1'));
		$this->assertEquals([], $this->iteminstanceMapper->findByItemID(1, 'unit_tester_3'));
	}

	public function testFindByPlaceId() {
		$iteminstancesByPlaceId = [
			[$this->iteminstances[0]],
			[$this->iteminstances[1]],
			[$this->iteminstances[2]]
		];
		$this->assertEquals($iteminstancesByPlaceId[0], $this->iteminstanceMapper->findByPlaceId('unit_tester_1', 1));
		$this->assertEquals($iteminstancesByPlaceId[1], $this->iteminstanceMapper->findByPlaceId('unit_tester_1', 2));
		$this->assertEquals($iteminstancesByPlaceId[2], $this->iteminstanceMapper->findByPlaceId('unit_tester_1', 3));
		$this->assertEquals([], $this->iteminstanceMapper->findByPlaceId('unit_tester_3', 4));
	}

	public function testAddAndFind() {
		$params['uid'] = 'unit_tester_4';
		$params['itemid'] = 1;
		$params['placeid'] = 4;
		$params['price'] = '0.01 €';
		$params['count'] = 4;
		$params['available'] = null;
		$params['vendor'] = '';
		$params['date'] = '';
		$params['comment'] = 'Comment';

		$entry = $this->iteminstanceMapper->add($params);
		$entry->resetUpdatedFields();

		$iteminstance = new Iteminstance();
		$iteminstance->setUid($params['uid']);
		$iteminstance->setItemid($params['itemid']);
		$iteminstance->setPlaceid($params['placeid']);
		$iteminstance->setPrice($params['price']);
		$iteminstance->setCount($params['count']);
		$iteminstance->setAvailable($params['available']);
		$iteminstance->setVendor($params['vendor']);
		$iteminstance->setDate($params['date']);
		$iteminstance->setComment($params['comment']);
		$iteminstance->setId($entry->getId());
		$iteminstance->resetUpdatedFields();

		$this->assertEquals($iteminstance, $entry);

		$this->iteminstances[] = $iteminstance;
	}

	public function testUpdate() {
		$iteminstance = $this->iteminstances[0];
		$entry = $this->iteminstanceMapper->find($iteminstance->getId(), $iteminstance->getUid());
		$entry->setComment('Important comment');
		
		$this->assertEquals($entry, $this->iteminstanceMapper->update($entry));
	}

	public function tearDown(): void {
		parent::tearDown();
		$this->iteminstanceMapper->deleteInstances($this->iteminstances);
	}

}
