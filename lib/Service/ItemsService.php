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

use OCA\Inventory\Service\IteminstanceService;
use OCA\Inventory\Db\CategoryMapper;
use OCA\Inventory\Db\FolderMapper;
use OCA\Inventory\Db\Item;
use OCA\Inventory\Db\ItemcategoriesMapper;
use OCA\Inventory\Db\IteminstanceMapper;
use OCA\Inventory\Db\ItemparentMapper;
use OCA\Inventory\Db\ItemMapper;
use OCA\Inventory\Db\ItemrelationMapper;
use OCA\Inventory\Db\ItemtypeMapper;
use OCA\Inventory\Db\PlaceMapper;
use OCA\Inventory\Storage\AttachmentStorage;
use OCA\Inventory\BadRequestException;
use OCP\IConfig;
use OCP\IL10N;
use OCP\AppFramework\Db\DoesNotExistException;

class ItemsService {

	private $userId;
	private $AppName;

	/**
	 * @var IL10N
	 */
	private $l10n;
	private $iteminstanceService;

	private $itemMapper;
	private $categoryMapper;
	private $itemCategoriesMapper;
	private $iteminstanceMapper;
	private $itemParentMapper;
	private $itemRelationMapper;
	private $itemtypeMapper;
	private $folderMapper;
	private $placeMapper;

	private $attachmentStorage;

	public function __construct($userId, $AppName, IL10N $l10n, ItemMapper $itemMapper, IteminstanceService $iteminstanceService,
		IteminstanceMapper $iteminstanceMapper, CategoryMapper $categoryMapper, ItemcategoriesMapper $itemcategoriesMapper,
		ItemparentMapper $itemParentMapper, ItemRelationMapper $itemRelationMapper, ItemtypeMapper $itemtypeMapper,
		FolderMapper $folderMapper,PlaceMapper $placeMapper, AttachmentStorage $attachmentStorage) {
		$this->userId = $userId;
		$this->appName = $AppName;
		$this->l10n = $l10n;
		$this->iteminstanceService = $iteminstanceService;
		$this->iteminstanceMapper = $iteminstanceMapper;
		$this->itemMapper = $itemMapper;
		$this->categoryMapper = $categoryMapper;
		$this->itemCategoriesMapper = $itemcategoriesMapper;
		$this->itemParentMapper = $itemParentMapper;
		$this->itemRelationMapper = $itemRelationMapper;
		$this->itemtypeMapper = $itemtypeMapper;
		$this->folderMapper = $folderMapper;
		$this->placeMapper = $placeMapper;
		$this->attachmentStorage = $attachmentStorage;
	}

	/**
	 * get items
	 *
	 * @return array
	 */
	public function getAll() {
		$items = $this->itemMapper->findAll($this->userId);
		foreach ($items as $item) {
			$item = $this->getItemDetails($item);
		}
		return $items;
	}

	/**
	 * get items by folder
	 *
	 * @return array
	 */
	public function getByFolder($path) {
		if ($path === '') {
			$folderId = -1;
		} else {
			$folder = $this->folderMapper->findFolderByPath($this->userId, $path);
			$folderId = $folder->id;
		}
		$items = $this->itemMapper->findByFolderId($this->userId, $folderId);
		foreach ($items as $item) {
			$item = $this->getItemDetails($item);
		}
		return $items;
	}

	/**
	 * get items by place
	 *
	 * @return array
	 */
	public function getByPlace($path) {
		if ($path === '') {
			$placeId = -1;
		} else {
			$place = $this->placeMapper->findPlaceByPath($this->userId, $path);
			$placeId = $place->id;
		}
		// Find all item instances at this place
		$instances = $this->iteminstanceMapper->findByPlaceId($this->userId, $placeId);
		// Find all items to these instances
		$items = [];
		foreach ($instances as $instance) {
			if ($place) {
				$instance->place = array(
					'id'	=> $place->id,
					'name'	=> $place->name,
					'parent'=> $place->parentid,
					'path'	=> $place->path
				);
			} else {
				$instance->place = array(
					'id'	=> -1,
					'name'	=> $this->l10n->t('No place assigned'),
					'parent'=> null,
					'path'	=> ''
				);
			}
			$item = $this->itemMapper->find($instance->itemid, $this->userId);
			$item = $this->getItemDetails($item, false);
			$item->instances[] = $instance;
			$item->isInstance = true;
			$items[] = $item;
		}
		return $items;
	}

	/**
	 * get items
	 *
	 * @return array
	 */
	public function getCandidates($itemID, $relationType) {

		if ( is_numeric($itemID) === false ) {
			throw new BadRequestException('Item id must be a number.');
		}

		$excludeIDs = array();
		if ($relationType === 'sub') {
			$excludeIDs = $this->itemParentMapper->findSubIDs($itemID);
		} elseif ($relationType === 'parent') {
			$excludeIDs = $this->itemParentMapper->findParentIDs($itemID);
		} elseif ($relationType === 'related') {
			$excludeIDs = $this->itemRelationMapper->findRelatedIDs($itemID, $this->userId);
		}
		
		// add the item itself
		array_push($excludeIDs, $itemID);

		$items = $this->itemMapper->findCandidates($itemID, $excludeIDs, $this->userId);
		foreach ($items as $item) {
			$item = $this->getItemDetails($item);
		}
		return $items;
	}

	/**
	 * Get an item by its ID
	 * 
	 * @param $itemID
	 * @return Item
	 * @throws DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws BadRequestException
	 */
	public function get($itemID) {

		if ( is_numeric($itemID) === false ) {
			throw new BadRequestException('Item id must be a number.');
		}

		$item = $this->itemMapper->find($itemID, $this->userId);
		return $this->getItemDetails($item);
	}

	/**
	 * get subitems
	 *
	 * @return array
	 */
	public function getSub($itemID) {

		if ( is_numeric($itemID) === false ) {
			throw new BadRequestException('Item id must be a number.');
		}

		$relations = $this->itemParentMapper->findSub($itemID);
		$items = [];
		foreach ($relations as $relation) {
			$item = $this->itemMapper->find($relation->itemid, $this->userId);
			$item = $this->getItemDetails($item);
			$items[] = $item;
		}
		return $items;
	}

	/**
	 * get parent items
	 *
	 * @return array
	 */
	public function getParent($itemID) {

		if ( is_numeric($itemID) === false ) {
			throw new BadRequestException('Item id must be a number.');
		}

		$relations = $this->itemParentMapper->findParent($itemID);
		$items = [];
		foreach ($relations as $relation) {
			$item = $this->itemMapper->find($relation->parentid, $this->userId);
			$item = $this->getItemDetails($item);
			$items[] = $item;
		}
		return $items;
	}

	/**
	 * get related items
	 *
	 * @return array
	 */
	public function getRelated($itemID) {

		if ( is_numeric($itemID) === false ) {
			throw new BadRequestException('Item id must be a number.');
		}

		$relatedIDs = $this->itemRelationMapper->findRelatedIDs($itemID, $this->userId);
		$items = [];
		foreach ($relatedIDs as $relatedID) {
			$item = $this->itemMapper->find($relatedID, $this->userId);
			$item = $this->getItemDetails($item);
			$items[] = $item;
		}
		return $items;
	}

	/**
	 * add item
	 *
	 * @return array
	 */
	public function enlist($item) {
		$item['uid'] = $this->userId;

		if ($item['path'] === null) {
			$item['path'] = '';
		}

		if ($item['path'] === '') {
			$item['folderid'] = -1;
		} else {
			$folder = $this->folderMapper->findFolderByPath($this->userId, $item['path']);
			$item['folderid'] = $folder->id;
		}

		$added = $this->itemMapper->add($item);

		foreach ($item['categories'] as $category) {
			$categoryEntity = $this->categoryMapper->findCategoryByName($category['name'], $this->userId);
			if (!$categoryEntity) {
				$categoryEntity = $this->categoryMapper->add($category['name'], $this->userId);
			}
			$mapping = $this->itemCategoriesMapper->add(array(
				'itemid' => $added->id,
				'uid' => $this->userId,
				'categoryid' => $categoryEntity->id
			));
		}

		foreach ($item['instances'] as $instance) {
			$instance['itemid'] = $added->id;
			$this->iteminstanceService->add($instance);
		}
		return $this->get($added->id);
	}

	/**
	 * Delete an item
	 *
	 * @param int $itemId		The id of the item to delete
	 * @return array
	 */
	public function delete($itemId) {

		if ( is_numeric($itemId) === false ) {
			throw new BadRequestException('Item id must be a number.');
		}

		// Find the item by Id
		$item = $this->itemMapper->find($itemId, $this->userId);

		$this->deleteItem($item);
	}

	/**
	 * Delete an item
	 *
	 * @param Item $item		The item to delete
	 */
	public function deleteItem($item) {
		// Delete all instances belonging to this item
		$this->iteminstanceService->deleteAllInstancesOfItem($item->id);

		// Delete all links to categories
		$itemCategories = $this->itemCategoriesMapper->findCategories($item->id, $this->userId);
		$this->itemCategoriesMapper->deleteItemCategories($itemCategories);

		// Delete all relations including this item
		// Parent
		$relations = $this->itemParentMapper->find($item->id);
		$this->itemParentMapper->deleteRelations($relations);
		// Related
		$relations = $this->itemRelationMapper->find($item->id, $this->userId);
		$this->itemRelationMapper->deleteRelations($relations);

		// Delete the item itself
		$this->itemMapper->delete($item);
	}

	/**
	 * Edits an instance of an item
	 * 
	 * @NoAdminRequired
	 * @param $itemID	The item Id
	 * @param $item	The item parameters
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function edit($itemId, $item) {

		if ( is_numeric($itemId) === false ) {
			throw new BadRequestException('Item id must be a number.');
		}

		if ( $item['name'] === null || $item['name'] === false ) {
			throw new BadRequestException('Item name must not be empty.');
		}

		// Check that string length does not exceed database column length
		if ( strlen($item['name']) > 100 ) {
			throw new BadRequestException('Item name must not exceed 100 characters.');
		}

		if ( strlen($item['maker']) > 100 ) {
			throw new BadRequestException('Item maker must not exceed 100 characters.');
		}

		if ( strlen($item['link']) > 255 ) {
			throw new BadRequestException('Item link must not exceed 255 characters.');
		}

		if ( strlen($item['comment']) > 65000 ) {
			throw new BadRequestException('Item comment must not exceed 65000 characters.');
		}

		if ( strlen($item['details']) > 65000 ) {
			throw new BadRequestException('Item details must not exceed 65000 characters.');
		}

		if ( strlen($item['description']) > 65000 ) {
			throw new BadRequestException('Item description must not exceed 65000 characters.');
		}

		if ( $item['gtin'] !== null && $item['gtin'] !== '' && strlen($item['gtin']) !== 13 ) {
			throw new BadRequestException('The provided GTIN is invalid.');
		}

		$localItem = $this->itemMapper->find($itemId, $this->userId);
		$localItem->setName($item['name']);
		$localItem->setMaker($item['maker']);
		$localItem->setDescription($item['description']);
		$localItem->setItemNumber($item['itemNumber']);
		$localItem->setLink($item['link']);
		$localItem->setGtin($item['gtin']);
		$localItem->setDetails($item['details']);
		$localItem->setComment($item['comment']);
		$localItem->setType($item['type']);
		$editedItem = $this->itemMapper->update($localItem);
		return $this->getItemDetails($editedItem);
	}

	public function findByString($searchString) {
		// Find items which contain this string directly
		$items = $this->itemMapper->findByString($this->userId, $searchString);

		$itemIds = array_map(function($item) {return $item->id;}, $items);
		// Find instances and corresponding items
		$instances = $this->iteminstanceService->findByString($searchString);
		foreach ($instances as $instance) {
			// We only add the item if it is not present already
			if (!in_array($instance->itemid, $itemIds)) {
				$items[] = $this->itemMapper->find($instance->itemid, $this->userId);
			}
		}

		foreach ($items as $item) {
			$item = $this->getItemDetails($item);
		}
		return $items;
	}

	/**
	 * Gets the of an item
	 * 
	 * @NoAdminRequired
	 * @param $item	The item
	 * @return \OCP\AppFramework\Db\Entity
	 */
	function getItemDetails($item, $getInstances = true) {
		$categories = $this->itemCategoriesMapper->findCategories($item->id, $this->userId);
		$categoriesNames = array();
		foreach ($categories as $category) {
			$name = $this->categoryMapper->findCategory($category->categoryid, $this->userId);
			$categoriesNames[] = array(
				'id'	=> $category->categoryid,
				'name'	=> $name->name
			);
		}
		try {
			$type = $this->itemtypeMapper->find($item->type, $this->userId);
			$item->icon = $type->icon;
			$item->type = $type->name;
		} catch (DoesNotExistException $e) {
			$item->icon = 'default';
			$item->type = 'default';
		}
		$item->categories = $categoriesNames;
		if ($getInstances) {
			$item->instances = $this->iteminstanceService->getByItemID($item->id);
		} else {
			$instances = [];
		}

		$item->images = $this->attachmentStorage->getImages($item->id);

		return $item;
	}

	/**
	 * add item relations
	 *
	 * @return array
	 */
	public function link($mainItemID, $itemIDs, $relationType) {

		if ( is_numeric($mainItemID) === false ) {
			throw new BadRequestException('Item id must be a number.');
		}

		if ($relationType === 'parent') {
			foreach ($itemIDs as $itemID) {
				if ($itemID === $mainItemID) {
					continue;
				}
				$map = array(
					'parentid' => $itemID,
					'itemid' => $mainItemID,
					'uid' => $this->userId
				);
				try {
					$this->itemParentMapper->add($map);
				} catch (\Exception $e) {

				}
			}
		} elseif ($relationType === 'sub') {
			foreach ($itemIDs as $itemID) {
				if ($itemID === $mainItemID) {
					continue;
				}
				$map = array(
					'parentid' => $mainItemID,
					'itemid' => $itemID,
					'uid' => $this->userId
				);
				try {
					$this->itemParentMapper->add($map);
				} catch (\Exception $e) {

				}
			}
		} elseif ($relationType === 'related') {
			foreach ($itemIDs as $itemID) {
				if ($itemID === $mainItemID) {
					continue;
				}
				// sort IDs so that $itemID1 is smaller than $itemID2
				if ($mainItemID <= $itemID) {
					$itemID1 = $mainItemID;
					$itemID2 = $itemID;
				} else {
					$itemID1 = $itemID;
					$itemID2 = $mainItemID;
				}
				$map = array(
					'itemid1' => $itemID1,
					'itemid2' => $itemID2,
					'uid' => $this->userId
				);
				try {
					$this->itemRelationMapper->add($map);
				} catch (\Exception $e) {

				}
			}
		}
	}

	/**
	 * Remove item relations
	 *
	 * @return array
	 */
	public function unlink($mainItemID, $itemIDs, $relationType) {

		if ( is_numeric($mainItemID) === false ) {
			throw new BadRequestException('Item id must be a number.');
		}

		if ($relationType === 'parent') {
			foreach ($itemIDs as $itemID) {
				if ($itemID === $mainItemID) {
					continue;
				}
				try {
					$relation = $this->itemParentMapper->findRelation($mainItemID, $itemID, $this->userId);
					$this->itemParentMapper->delete($relation);
				} catch (\Exception $e) {

				}
			}
		} elseif ($relationType === 'sub') {
			foreach ($itemIDs as $itemID) {
				if ($itemID === $mainItemID) {
					continue;
				}
				try {
					$relation = $this->itemParentMapper->findRelation($itemID, $mainItemID, $this->userId);
					$this->itemParentMapper->delete($relation);
				} catch (\Exception $e) {

				}
			}
		} elseif ($relationType === 'related') {
			foreach ($itemIDs as $itemID) {
				if ($itemID === $mainItemID) {
					continue;
				}
				// sort IDs so that $itemID1 is smaller than $itemID2
				if ($mainItemID <= $itemID) {
					$itemID1 = $mainItemID;
					$itemID2 = $itemID;
				} else {
					$itemID1 = $itemID;
					$itemID2 = $mainItemID;
				}
				try {
					$relation = $this->itemRelationMapper->findExactRelation($itemID1, $itemID2, $this->userId);
					$this->itemRelationMapper->delete($relation);
				} catch (\Exception $e) {

				}
			}
		}
	}

	/**
	 * Move an item to other folder
	 * 
	 * @param $itemId
	 * @param $newPath
	 * @return Item
	 * @throws DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws BadRequestException
	 */
	public function move($itemId, $newPath) {

		if ( is_numeric($itemId) === false ) {
			throw new BadRequestException('Item id must be a number.');
		}

		if ($newPath === '') {
			$folder->id = -1;
			$folder->path = '';
		} else {
			$folder = $this->folderMapper->findFolderByPath($this->userId, $newPath);
		}
		$item = $this->itemMapper->find($itemId, $this->userId);
		
		$item->setFolderid($folder->id);
		$item->setPath($folder->path);
		$editedItem = $this->itemMapper->update($item);
		return $this->getItemDetails($editedItem);
	}

	/**
	 * Moves an instance to a new place
	 * 
	 * @NoAdminRequired
	 * @param $itemId		The item Id
	 * @param $instanceId	The instance Id
	 * @param $newPath		The path of the new place
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function moveInstance($itemId, $instanceId, $newPath) {

		if ( is_numeric($itemId) === false ) {
			throw new BadRequestException('Item id must be a number.');
		}

		if ( is_numeric($instanceId) === false ) {
			throw new BadRequestException('Instance id must be a number.');
		}

		if ($newPath === '') {
			$place->id = -1;
		} else {
			$place = $this->placeMapper->findPlaceByPath($this->userId, $newPath);
		}
		$instance = $this->iteminstanceMapper->find($instanceId, $this->userId);
		
		$instance->setPlaceid($place->id);
		$instance = $this->iteminstanceMapper->update($instance);

		$item = $this->itemMapper->find($itemId, $this->userId);
		$item = $this->getItemDetails($item, false);
		$item->instances[] = $instance;
		$item->isInstance = true;
		return $item;
	}

	/**
	 * finds items by query
	 *
	 * @return array
	 */
	public function findByQuery($query) {
		// to be done
		// will be necessary once pagination is implemented on the client side
		return [];
	}
}
