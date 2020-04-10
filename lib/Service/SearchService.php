<?php
/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2020 Raimund Schlüßler raimund.schluessler@mailbox.org
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

namespace OCA\Inventory\Service;

class SearchService {

	private $userId;
	private $AppName;
	private $itemsService;
	private $foldersService;

	public function __construct($userId, $AppName, ItemsService $itemsService, FoldersService $foldersService) {
		$this->userId = $userId;
		$this->appName = $AppName;
		$this->itemsService = $itemsService;
		$this->foldersService = $foldersService;
	}

	/**
	 * Find items by searchString
	 *
	 * @param $searchString
	 * @return array<Item, Folder>
	 */
	public function findByString($searchString) {
		// We don't need to search for an empty string
		$results = [
			'folders' => [],
			'items' => []
		];
		if ($searchString === '') {
			return $results;
		}

		$items = $this->itemsService->findByString($searchString);

		$folders = $this->foldersService->findByString($searchString);

		$results['items'] = $items;
		$results['folders'] = $folders;

		return $results;
	}
}
