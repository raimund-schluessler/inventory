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

namespace OCA\Inventory\Controller;

use OCA\Inventory\Service\AttachmentService;
use OCP\AppFramework\Controller;
use OCP\IRequest;

class AttachmentController extends Controller {

	private $attachmentService;

	/**
	 * AttachmentController constructor.
	 *
	 * @param $AppName
	 * @param AttachmentService $attachmentService
	 */
	public function __construct($AppName, IRequest $request, AttachmentService $attachmentService) {
		parent::__construct($AppName, $request);
		$this->attachmentService = $attachmentService;
	}

	/**
	 * Returns a list with all attachments for an item
	 *
	 * @param int $itemID
	 * @NoAdminRequired
	 * @return Attachment[]
	 */
	public function getAll($itemID) {
		return $this->attachmentService->getAll($itemID);
	}

	/**
	 * Returns a list with all attachments for an instance
	 *
	 * @param int $itemID
	 * @param int $instanceID
	 * @NoAdminRequired
	 * @return Attachment[]
	 */
	public function getInstance($itemID, $instanceID) {
		return $this->attachmentService->getAll($itemID, $instanceID);
	}

	/**
	 * Displays an attachment for an item
	 *
	 * @param int $itemID
	 * @param int $attachmentID
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 * @return \OCP\AppFramework\Http\Response
	 * @throws \OCA\Inventory\NotFoundException
	 */
	public function display($itemID, $attachmentID) {
		return $this->attachmentService->display($itemID, $attachmentID);
	}

	/**
	 * Displays an attachment for an instance
	 *
	 * @param int $itemID
	 * @param int $instanceID
	 * @param int $attachmentID
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 * @return \OCP\AppFramework\Http\Response
	 * @throws \OCA\Inventory\NotFoundException
	 */
	public function displayInstance($itemID, $instanceID, $attachmentID) {
		return $this->attachmentService->display($itemID, $attachmentID, $instanceID);
	}

	/**
	 * @NoAdminRequired
	 */
	public function create($itemID, $instanceID = null) {
		return $this->attachmentService->create(
			$itemID,
			$this->request->getParam('data'),
			$instanceID
		);
	}

	/**
	 * @NoAdminRequired
	 */
	public function update($itemID, $attachmentID, $instanceID = null) {
		return $this->attachmentService->update(
			$itemID,
			$attachmentID,
			$this->request->getParam('data'),
			$instanceID
		);
	}

	/**
	 * Link a file as attachment
	 * 
	 * @param int $itemID
	 * @param string $attachment
	 * @param int $instanceID
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 * @return \OCP\AppFramework\Http\Response
	 */
	public function link($itemID, $attachment) {
		return $this->attachmentService->link(
			$itemID,
			$attachment,
			null
		);
	}

	/**
	 * Link a file as attachment
	 * 
	 * @param int $itemID
	 * @param string $attachment
	 * @param int $instanceID
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 * @return \OCP\AppFramework\Http\Response
	 */
	public function linkInstance($itemID, $attachment, $instanceID = null) {
		return $this->attachmentService->link(
			$itemID,
			$attachment,
			$instanceID
		);
	}

	/**
	 * Delete an attachment
	 * 
	 * @param int $itemID
	 * @param int $attachmentID
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 * @return \OCP\AppFramework\Http\Response
	 */
	public function delete($itemID, $attachmentID) {
		return $this->attachmentService->unlink(
			$itemID,
			$attachmentID,
			null,
			true
		);
	}

	/**
	 * Delete an attachment
	 * 
	 * @param int $itemID
	 * @param int $attachmentID
	 * @param int $instanceID
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 * @return \OCP\AppFramework\Http\Response
	 */
	public function deleteInstance($itemID, $attachmentID, $instanceID = null) {
		return $this->attachmentService->unlink(
			$itemID,
			$attachmentID,
			$instanceID,
			true
		);
	}

	/**
	 * Unlink an attachment
	 * 
	 * @param int $itemID
	 * @param int $attachmentID
	 * @param int $instanceID
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 * @return \OCP\AppFramework\Http\Response
	 */
	public function unlink($itemID, $attachmentID) {
		return $this->attachmentService->unlink(
			$itemID,
			$attachmentID,
			null
		);
	}

	/**
	 * Unlink an attachment
	 * 
	 * @param int $itemID
	 * @param int $attachmentID
	 * @param int $instanceID
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 * @return \OCP\AppFramework\Http\Response
	 */
	public function unlinkInstance($itemID, $attachmentID, $instanceID = null) {
		return $this->attachmentService->unlink(
			$itemID,
			$attachmentID,
			$instanceID
		);
	}
}
