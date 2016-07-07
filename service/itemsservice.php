<?php
/**
 * ownCloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2016 Raimund Schlüßler raimund.schluessler@googlemail.com
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

use OCP\IConfig;
use \OCA\Inventory\Db\Item;
use \OCA\Inventory\Db\ItemMapper;
use \OCA\Inventory\Db\CategoryMapper;
use \OCA\Inventory\Db\ItemCategoriesMapper;

class ItemsService {

	private $userId;
	private $appName;
    private $itemMapper;
    private $categoryMapper;
    private $itemCategoriesMapper;

	public function __construct($userId, $appName, ItemMapper $itemMapper, CategoryMapper $categoryMapper,
		ItemCategoriesMapper $itemCategoriesMapper) {
		$this->userId = $userId;
		$this->appName = $appName;
        $this->itemMapper = $itemMapper;
        $this->categoryMapper = $categoryMapper;
        $this->itemCategoriesMapper = $itemCategoriesMapper;
	}

	/**
	 * get items
	 *
	 * @return array
	 */
	public function get() {
		$items = $this->itemMapper->findAll();
		foreach ($items as $nr => $item) {
			$categories = $this->itemCategoriesMapper->findCategories($item->id);
			$categoriesNames = array();
			foreach ($categories as $category) {
				$name = $this->categoryMapper->findCategory($category->categoryid);
				$categoriesNames[] = array(
					'id'	=> $category->categoryid,
					'name'	=> $name->name
				);
			}
			$items[$nr]->categories = $categoriesNames;
		}
		return $items;
	}

	/**
	 * add item
	 *
	 * @return array
	 */
	public function enlist($item) {
		$this->itemMapper->add($item);
	}
}
