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

	public function __construct($appName, IRequest $request, AttachmentService $attachmentService) {
		parent::__construct($appName, $request);
		$this->attachmentService = $attachmentService;
	}

	/**
	 * Returns a list with all attachments for an item
	 *
	 * @NoAdminRequired
	 */
	public function getAll($itemID) {
		return $this->attachmentService->getAll($itemID);
	}

	/**
	 * Displays an attachment
	 *
	 * @param $itemID
	 * @param $attachmentID
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 * @return \OCP\AppFramework\Http\Response
	 * @throws \OCA\Inventory\NotFoundException
	 */
	public function display($itemID, $attachmentID) {
		return $this->attachmentService->display($itemID, $attachmentID);
	}
}
