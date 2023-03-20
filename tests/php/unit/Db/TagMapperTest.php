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
class TagMapperTest extends TestCase {
	/** @var IDBConnection */
	private $dbConnection;
	/** @var TagMapper */
	private $tagMapper;

	// Data
	private $tags;
	private $tagsById = [];

	public function setup(): void {
		parent::setUp();

		$this->dbConnection = \OC::$server->getDatabaseConnection();
		$this->tagMapper = new TagMapper(
			$this->dbConnection
		);
		$this->tags = [
			$this->createTagEntity('Languages', null),
			$this->createTagEntity('Javascript', 1),
			$this->createTagEntity('PHP', 1),
			$this->createTagEntity('C++', 1)
		];
		foreach ($this->tags as $tag) {
			$entry = $this->tagMapper->insert($tag);
			$entry->resetUpdatedFields();
			$this->tagsById[$entry->getId()] = $entry;
		}
	}

	private function createTagEntity($name, $parentId) {
		$tag = new Tag();
		$tag->setUid('admin');
		$tag->setName($name);
		$tag->setParentid($parentId);
		return $tag;
	}

	public function testFind() {
		$uid = 'admin';
		foreach ($this->tagsById as $id => $tag) {
			$this->assertEquals($tag, $this->tagMapper->findTag($tag->getId(), $uid));
		}
	}
	
	public function testFindNotFound() {
		$tagId = 10;
		$uid = 'admin';
		$this->assertEquals(false, $this->tagMapper->findTag($tagId, $uid));
	}
	
	public function testFindTagByName() {
		$name = 'Languages';
		$uid = 'admin';
		$this->assertEquals($this->tags[0], $this->tagMapper->findTagByName($name, $uid));
	}
	
	public function testFindByNameNotFound() {
		$name = 'Ruby';
		$uid = 'admin';
		$this->assertEquals(false, $this->tagMapper->findTagByName($name, $uid));
	}

	public function testAddAndFind() {
		$name = 'Python';
		$uid = 'admin';
		$parentId = null;

		$entry = $this->tagMapper->add($name, $uid, $parentId);
		$entry->resetUpdatedFields();

		$tag = new Tag();
		$tag->setUid($uid);
		$tag->setName($name);
		$tag->setParentid($parentId);
		$tag->setId($entry->getId());
		$tag->resetUpdatedFields();

		$this->assertEquals($tag, $entry);

		$this->tags[] = $tag;
	}

	public function tearDown(): void {
		parent::tearDown();
		foreach ($this->tags as $tag) {
			$this->tagMapper->delete($tag);
		}
	}
}
