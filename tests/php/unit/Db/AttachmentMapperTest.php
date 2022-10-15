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
class AttachmentMapperTest extends TestCase {

	/** @var IDBConnection */
	private $dbConnection;
	/** @var AttachmentMapper */
	private $attachmentMapper;

	// Data
	private $attachments;
	private $attachmentsById = [];
	private $userId;

	public function setup(): void {
		parent::setUp();

		$this->userId = 'inventory_tester';

		$this->dbConnection = \OC::$server->getDatabaseConnection();
		$this->attachmentMapper = new AttachmentMapper(
			$this->dbConnection
		);
		$this->attachments = [
			$this->createAttachmentEntity(1, 'file1.pdf'),
			$this->createAttachmentEntity(1, 'file2.pdf'),
			$this->createAttachmentEntity(2, 'file3.pdf'),
			$this->createAttachmentEntity(3, 'file4.pdf'),
			$this->createAttachmentEntity(1, 'file5.pdf', 123)
		];
		foreach ($this->attachments as $attachment) {
			$entry = $this->attachmentMapper->insert($attachment);
			$entry->resetUpdatedFields();
			$this->attachmentsById[$entry->getId()] = $entry;
		}
	}

	private function createAttachmentEntity($itemId, $basename, $instanceId = null) {
		$attachment = new Attachment();
		$attachment->setItemid($itemId);
		$attachment->setInstanceid($instanceId);
		$attachment->setBasename($basename);
		$attachment->setCreatedBy($this->userId);
		return $attachment;
	}

	public function testFind() {
		foreach ($this->attachmentsById as $id => $attachment) {
			$this->assertEquals(
				$attachment,
				$this->attachmentMapper->findAttachment($this->userId, $attachment->getItemid(), $id, $attachment->getInstanceid())
			);
		}
	}
	
	public function testFindNotFound() {
		$itemId = 1;
		$attachmentId = 10;
		$this->assertEquals(false, $this->attachmentMapper->findAttachment($this->userId, $itemId, $attachmentId));
	}

	public function testFindAll() {
		$attachmentsByItem = [
			array_slice($this->attachments, 0, 2),
			array_slice($this->attachments, 2, 1),
			array_slice($this->attachments, 3, 1),
			array_slice($this->attachments, 4, 1)
		];
		$this->assertEquals($attachmentsByItem[0], $this->attachmentMapper->findAll($this->userId, 1));
		$this->assertEquals($attachmentsByItem[1], $this->attachmentMapper->findAll($this->userId, 2));
		$this->assertEquals($attachmentsByItem[2], $this->attachmentMapper->findAll($this->userId, 3));
		$this->assertEquals($attachmentsByItem[3], $this->attachmentMapper->findAll($this->userId, 1, 123));
		$this->assertEquals([], $this->attachmentMapper->findAll($this->userId, 5));
	}
	
	public function testFindByName() {
		$itemId = 1;
		$attachmentBasename = 'file1.pdf';
		$this->assertEquals($this->attachments[0], $this->attachmentMapper->findByName($this->userId, $itemId, $attachmentBasename));
	}
	
	public function testFindByNameAndInstance() {
		$itemId = 1;
		$attachmentBasename = 'file5.pdf';
		$instanceId = 123;
		$this->assertEquals($this->attachments[4], $this->attachmentMapper->findByName($this->userId, $itemId, $attachmentBasename, $instanceId));
	}
	
	public function testFindByNameNotFound() {
		$itemId = 1;
		$attachmentBasename = 'file3.pdf';
		$this->assertEquals(false, $this->attachmentMapper->findByName($this->userId, $itemId, $attachmentBasename));
	}

	public function tearDown(): void {
		parent::tearDown();
		foreach ($this->attachments as $attachment) {
			$this->attachmentMapper->delete($attachment);
		}
	}
}
