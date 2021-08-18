<?php

declare(strict_types=1);

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

namespace OCA\Inventory\Search;

use OCA\Inventory\AppInfo\Application;
use OCA\Inventory\Service\ItemsService;
use OCA\Inventory\Service\FoldersService;
use OCP\App\IAppManager;
use OCP\IL10N;
use OCP\IUser;
use OCP\IURLGenerator;
use OCP\Search\IProvider;
use OCP\Search\ISearchQuery;
use OCP\Search\SearchResult;
use OCP\Search\SearchResultEntry;

class Provider implements IProvider {

	/** @var IAppManager */
	protected $appManager;

	/** @var ItemsService */
	private $itemsService;

	/** @var FoldersService */
	private $foldersService;

	/** @var IL10N */
	private $l10n;

	/** @var IURLGenerator */
	private $urlGenerator;

	/**
	 * ACalendarSearchProvider constructor.
	 *
	 * @param IAppManager $appManager
	 * @param IL10N $l10n
	 * @param IURLGenerator $urlGenerator
	 * @param CalDavBackend $backend
	 */
	public function __construct(IAppManager $appManager,
								ItemsService $itemsService,
								FoldersService $foldersService,
								IL10N $l10n,
								IURLGenerator $urlGenerator) {
		$this->appManager = $appManager;
		$this->itemsService = $itemsService;
		$this->foldersService = $foldersService;
		$this->l10n = $l10n;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return Application::APP_ID;
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): string {
		return $this->l10n->t('Inventory');
	}

	/**
	 * @inheritDoc
	 */
	public function getOrder(string $route, array $routeParameters): int {
		if ($route === 'inventory.page.index') {
			return -1;
		}
		return 50;
	}

	/**
	 * @inheritDoc
	 */
	public function search(IUser $user, ISearchQuery $query): SearchResult {
		if (!$this->appManager->isEnabledForUser('inventory', $user)) {
			return SearchResult::complete($this->getName(), []);
		}

		$items = $this->itemsService->findByString($query->getTerm());
		$formattedItems = \array_map(function (Object $item):SearchResultEntry {
			$itemUrl = $this->getDeepLinkToInventoryItem($item->path, $item->id);

			$thumbnailUrl = empty($item->images)
					? ''
					: $this->urlGenerator->linkToRoute('core.Preview.getPreviewByFileId',
						['x' => 32, 'y' => 32, 'fileId' => $item->images[0]['fileid']]);
			return new SearchResultEntry($thumbnailUrl, $item->name, $this->formatSubline($item->path), $itemUrl, 'icon-inventory', false);
		}, $items);

		$folders = $this->foldersService->findByString($query->getTerm());
		$formattedFolders = \array_map(function (Object $folder):SearchResultEntry {
			$folderUrl = $this->getDeepLinkToInventoryFolder($folder->path);

			return new SearchResultEntry('', $folder->name, $this->formatSubline($folder->path), $folderUrl, 'icon-folder', false);
		}, $folders);


		$formattedResults = array_merge($formattedItems, $formattedFolders);
		$formattedResults = array_slice($formattedResults, (int) $query->getCursor(), $query->getLimit());

		return SearchResult::paginated(
			$this->getName(),
			$formattedResults,
			$query->getCursor() + count($formattedResults)
		);
	}

	/**
	 * @param string $folderPath
	 * @return string
	 */
	protected function getDeepLinkToInventoryFolder(string $folderPath): string {
		return $this->urlGenerator->getAbsoluteURL(
			$this->urlGenerator->linkToRoute('inventory.page.index')
			. '#/folders/'
			. $folderPath
		);
	}

	/**
	 * @param string $folderPath
	 * @param int $itemNr
	 * @return string
	 */
	protected function getDeepLinkToInventoryItem(string $folderPath, int $itemNr): string {
		return $this->urlGenerator->getAbsoluteURL(
			$this->urlGenerator->linkToRoute('inventory.page.index')
			. '#/folders/'
			. $folderPath
			. ($folderPath ? '/' : '')
			. 'item-' . $itemNr
		);
	}

	/**
	 * Format subline for items and folders
	 *
	 * @param string $path
	 * @return string
	 */
	private function formatSubline(string $path): string {
		// Do not show the location if the file is in root
		if ($path === '') {
			return '';
		}

		return $this->l10n->t('in %s', [$path]);
	}
}
