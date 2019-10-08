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
class AttachmentMapperTest extends MapperTestUtility  {

	/** @var IDBConnection */
	private $dbConnection;
	/** @var AttachmentMapper */
	private $attachmentMapper;

	// Data
	private $attachments;
	private $attachmentsById = [];

	public function setup(){
		parent::setUp();

		$this->dbConnection = \OC::$server->getDatabaseConnection();
		$this->attachmentMapper = new AttachmentMapper(
			$this->dbConnection
		);
		$this->attachments = [
			$this->createAttachmentEntity(1, 'file1.pdf'),
			$this->createAttachmentEntity(1, 'file2.pdf'),
			$this->createAttachmentEntity(2, 'file3.pdf'),
			$this->createAttachmentEntity(3, 'file4.pdf')
		];
		foreach ($this->attachments as $attachment) {
			$entry = $this->attachmentMapper->insert($attachment);
			$entry->resetUpdatedFields();
			$this->attachmentsById[$entry->getId()] = $entry;
		}
	}

	private function createAttachmentEntity($itemId, $basename) {
		$attachment = new Attachment();
		$attachment->setItemid($itemId);
		$attachment->setBasename($basename);
		$attachment->setCreatedBy('admin');
		return $attachment;
	}

	public function testFind() {
		foreach ($this->attachmentsById as $id => $attachment) {
			$this->assertEquals($attachment, $this->attachmentMapper->findAttachment($attachment->getItemid(), $id));
		}
	}
	
	public function testFindNotFound() {
		$itemId = 1;
		$attachmentId = 10;
		$this->assertEquals(false, $this->attachmentMapper->findAttachment($itemId, $attachmentId));
	}

	public function testFindAll() {
		$attachmentsByItem = [
			array_slice($this->attachments, 0, 2),
			array_slice($this->attachments, 2, 1),
			array_slice($this->attachments, 3, 1)
		];
		$this->assertEquals($attachmentsByItem[0], $this->attachmentMapper->findAll(1));
		$this->assertEquals($attachmentsByItem[1], $this->attachmentMapper->findAll(2));
		$this->assertEquals($attachmentsByItem[2], $this->attachmentMapper->findAll(3));
		$this->assertEquals([], $this->attachmentMapper->findAll(5));
	}
	
	public function testFindByName() {
		$itemId = 1;
		$attachmentBasename = 'file1.pdf';
		$this->assertEquals($this->attachments[0], $this->attachmentMapper->findByName($itemId, $attachmentBasename));
	}
	
	public function testFindByNameNotFound() {
		$itemId = 1;
		$attachmentBasename = 'file3.pdf';
		$this->assertEquals(false, $this->attachmentMapper->findByName($itemId, $attachmentBasename));
	}

	// public function testFindToDelete() {
	// 	$attachmentsToDelete = $this->attachments;
	// 	$attachmentsToDelete[0]->setDeletedAt(1);
	// 	$attachmentsToDelete[2]->setDeletedAt(1);
	// 	$this->attachmentMapper->update($attachmentsToDelete[0]);
	// 	$this->attachmentMapper->update($attachmentsToDelete[2]);
	// 	foreach ($attachmentsToDelete as $attachment) {
	// 		$attachment->resetUpdatedFields();
	// 	}

	// 	$this->assertEquals([$attachmentsToDelete[0]], $this->attachmentMapper->findToDelete(1));
	// 	$this->assertEquals([$attachmentsToDelete[2]], $this->attachmentMapper->findToDelete(2));
	// }

	// public function testIsOwner() {
	// 	$this->cardMapper->expects($this->once())
	// 		->method('isOwner')
	// 		->with('admin', 1)
	// 		->willReturn(true);
	// 	$this->assertTrue($this->attachmentMapper->isOwner('admin', (string)$this->attachments[0]->getId()));
	// }

	// public function testIsOwnerInvalid() {
	// 	$this->cardMapper->expects($this->once())
	// 		->method('isOwner')
	// 		->with('admin', 1)
	// 		->will($this->throwException(new DoesNotExistException('does not exist')));
	// 	$this->assertFalse($this->attachmentMapper->isOwner('admin', (string)$this->attachments[0]->getId()));
	// }

	// public function testFindBoardId() {
	// 	$this->cardMapper->expects($this->any())
	// 		->method('findBoardId')
	// 		->willReturn(123);
	// 	foreach ($this->attachmentsById as $attachment) {
	// 		$this->assertEquals(123, $this->attachmentMapper->findBoardId($attachment->getId()));
	// 	}
	// }

	public function tearDown() {
		parent::tearDown();
		foreach ($this->attachments as $attachment) {
			$this->attachmentMapper->delete($attachment);
		}
	}

}
