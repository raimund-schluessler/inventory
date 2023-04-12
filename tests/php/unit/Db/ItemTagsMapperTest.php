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

use OCP\IDBConnection;
use Test\TestCase;

/**
 * @group DB
 */
class ItemTagsMapperTest extends TestCase {
	/** @var IDBConnection */
	private $dbConnection;
	/** @var ItemTagsMapper */
	private $itemTagsMapper;

	// Data
	private $itemTags;
	private $itemTagsById = [];

	public function setup(): void {
		parent::setUp();

		$this->dbConnection = \OC::$server->getDatabaseConnection();
		$this->itemTagsMapper = new ItemTagsMapper(
			$this->dbConnection
		);
		$this->itemTags = [
			$this->createItemTagsEntity('unit_tester_1', 1, 1),
			$this->createItemTagsEntity('unit_tester_1', 1, 2),
			$this->createItemTagsEntity('unit_tester_1', 2, 3),
			$this->createItemTagsEntity('unit_tester_2', 3, 1)
		];
		foreach ($this->itemTags as $itemTag) {
			$entry = $this->itemTagsMapper->insert($itemTag);
			$entry->resetUpdatedFields();
			$this->itemTagsById[$entry->getId()] = $entry;
		}
	}

	private function createItemTagsEntity($uid, $itemId, $tagId) {
		$itemTag = new ItemTags();
		$itemTag->setUid($uid);
		$itemTag->setItemid($itemId);
		$itemTag->setCategoryid($tagId);
		return $itemTag;
	}

	public function testFindTags() {
		$itemTagsByItem = [
			array_slice($this->itemTags, 0, 2),
			array_slice($this->itemTags, 3, 1)
		];
		$this->assertEquals($itemTagsByItem[0], $this->itemTagsMapper->findTags(1, 'unit_tester_1'));
		$this->assertEquals($itemTagsByItem[1], $this->itemTagsMapper->findTags(3, 'unit_tester_2'));
		$this->assertEquals([], $this->itemTagsMapper->findTags(1, 'unit_tester_3'));
	}

	public function testAddAndFind() {
		$params['itemid'] = 1;
		$params['uid'] = 'unit_tester_4';
		$params['tagid'] = 4;

		$entry = $this->itemTagsMapper->add($params);
		$entry->resetUpdatedFields();

		$itemTag = new ItemTags();
		$itemTag->setUid($params['uid']);
		$itemTag->setCategoryid($params['tagid']);
		$itemTag->setItemid($params['itemid']);
		$itemTag->setId($entry->getId());
		$itemTag->resetUpdatedFields();

		$this->assertEquals($itemTag, $entry);

		$this->itemTags[] = $itemTag;
	}

	public function testGetItemIdsForTags() {
		$itemIds = $this->itemTagsMapper->getItemIdsForTags([1, 3], 'unit_tester_1');
		$this->assertEquals([1, 2], $itemIds);
		$itemIds = $this->itemTagsMapper->getItemIdsForTags([4], 'unit_tester_1');
		$this->assertEquals([], $itemIds);
		$itemIds = $this->itemTagsMapper->getItemIdsForTags([1, 3], 'unit_tester_3');
		$this->assertEquals([], $itemIds);
	}

	public function tearDown(): void {
		parent::tearDown();
		$this->itemTagsMapper->deleteItemTags($this->itemTags);
	}
}
