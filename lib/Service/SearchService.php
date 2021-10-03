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
	private $foldersService;
	private $itemsService;
	private $placesService;

	public function __construct($userId, $AppName, FoldersService $foldersService, ItemsService $itemsService, PlacesService $placesService) {
		$this->userId = $userId;
		$this->appName = $AppName;
		$this->foldersService = $foldersService;
		$this->itemsService = $itemsService;
		$this->placesService = $placesService;
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
			'places' => [],
			'items' => []
		];
		if (strlen($searchString) === 0) {
			return $results;
		}

		$results['folders'] = $this->foldersService->findByString($searchString);
		$results['items'] = $this->itemsService->findByString($searchString);
		$results['places'] = $this->placesService->findByString($searchString);

		return $results;
	}
}
