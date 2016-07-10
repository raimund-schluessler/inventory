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
use \OCA\Inventory\Db\PlacesMapper;

class ItemsService {

	private $userId;
	private $appName;
    private $itemMapper;
    private $categoryMapper;
    private $itemCategoriesMapper;

	public function __construct($userId, $appName, ItemMapper $itemMapper, CategoryMapper $categoryMapper,
		ItemCategoriesMapper $itemCategoriesMapper, PlacesMapper $placesMapper) {
		$this->userId = $userId;
		$this->appName = $appName;
        $this->itemMapper = $itemMapper;
        $this->categoryMapper = $categoryMapper;
        $this->itemCategoriesMapper = $itemCategoriesMapper;
        $this->placesMapper = $placesMapper;
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
			$place = $this->placesMapper->findPlace($item->place);
			if ($place) {
				$item->place = array(
					'id'	=> $place->id,
					'name'	=> $place->name,
					'parent'=> $place->parent
				);
			} else{
				$item->place = null;
			}
		}
		return $items;
	}

	/**
	 * add item
	 *
	 * @return array
	 */
	public function enlist($item) {
		$added = $this->itemMapper->add($item);

		foreach ($item['categories'] as $category) {
			$categoryEntity = $this->categoryMapper->findCategoryByName($category['name']);
			if (!$categoryEntity) {
				$categoryEntity = $this->categoryMapper->add($category['name']);
			}
			$mapping = $this->itemCategoriesMapper->add(array(	'itemid' => $added->id,
																'categoryid' => $categoryEntity->id)
			);
		}
	}
}
