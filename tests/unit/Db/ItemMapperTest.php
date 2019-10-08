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
class ItemMapperTest extends MapperTestUtility  {

	/** @var IDBConnection */
	private $dbConnection;
	/** @var ItemMapper */
	private $itemMapper;

	// Data
	private $items;
	private $itemsById = [];

	public function setup(){
		parent::setUp();

		$this->dbConnection = \OC::$server->getDatabaseConnection();
		$this->itemMapper = new ItemMapper(
			$this->dbConnection
		);
		$this->items = [
			$this->createItemEntity('admin', 'Testitem 1', 'Maker 1', 'A new red Item', 123, 'www.item1.de', null, 'Red, big, new', '', null),
			$this->createItemEntity('admin', 'Testitem 2', 'Maker 2', 'A new blue Item', 124, 'www.item2.de', null, 'Blue, small, new', '', null),
			$this->createItemEntity('admin', 'Testitem 3', 'Maker 3', 'A new green Item', 125, 'www.item3.de', '3165140777223', 'Green, big, new', 'Borrowed', null),
			$this->createItemEntity('user1', 'Testitem 4', 'Maker 4', 'A new black Item', 126, 'www.item4.de', null, 'Black, big, old', '', null)
		];
		foreach ($this->items as $item) {
			$entry = $this->itemMapper->insert($item);
			$entry->resetUpdatedFields();
			$this->itemsById[$entry->getId()] = $entry;
		}
	}

	private function createItemEntity($uid, $name, $maker, $description,
		$item_number, $link, $gtin, $details, $comment, $type) {
		$item = new Item();
		$item->setUid($uid);
		$item->setName($name);
		$item->setMaker($maker);
		$item->setDescription($description);
		$item->setItemNumber($item_number);
		$item->setLink($link);
		$item->setGtin($gtin);
		$item->setDetails($details);
		$item->setComment($comment);
		$item->setType($type);
		return $item;
	}

	public function testFind() {
		foreach ($this->itemsById as $id => $item) {
			$this->assertEquals($item, $this->itemMapper->find($id, $item->getUid()));
		}
	}

	public function testFindAll() {
		$itemsByUser = [
			array_slice($this->items, 0, 3),
			array_slice($this->items, 3, 1)
		];
		$this->assertEquals($itemsByUser[0], $this->itemMapper->findAll('admin'));
		$this->assertEquals($itemsByUser[1], $this->itemMapper->findAll('user1'));
		$this->assertEquals([], $this->itemMapper->findAll('user2'));
	}

	public function testFindCandidates() {
	// 	$uid = 'admin';
	// 	$itemId = 1;
	// 	$excludeIds = [];
	// 	$this->itemMapper->findCandidates($itemId, $excludeIds, $uid);
	}

	public function testAddAndFind() {
		$params['uid'] = 'admin';
		$params['name'] = 'Testitem 5';
		$params['maker'] = 'Maker 5';
		$params['description'] = '';
		$params['item_number'] = 123;
		$params['link'] = 'www.item5.de';
		$params['gtin'] = null;
		$params['details'] = null;
		$params['comment'] = null;
		$params['type'] = null;

		$entry = $this->itemMapper->add($params);
		$entry->resetUpdatedFields();

		$item = new Item();
		$item->setUid($params['uid']);
		$item->setName($params['name']);
		$item->setMaker($params['maker']);
		$item->setDescription($params['description']);
		$item->setItemNumber($params['item_number']);
		$item->setLink($params['link']);
		$item->setGtin($params['gtin']);
		$item->setDetails($params['details']);
		$item->setComment($params['comment']);
		$item->setType($params['type']);
		$item->setId($entry->getId());
		$item->resetUpdatedFields();

		$this->assertEquals($item, $entry);
	}

	public function testUpdate() {
		$item = $this->items[0];
		$entry = $this->itemMapper->find($item->getId(), $item->getUid());
		$entry->setName('Item 11');
		
		$this->assertEquals($entry, $this->itemMapper->update($entry));
	}

	public function tearDown() {
		parent::tearDown();
		foreach ($this->items as $item) {
			$this->itemMapper->delete($item);
		}
	}

}
