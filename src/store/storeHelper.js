/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2019 Raimund Schlüßler <raimund.schluessler@mailbox.org>
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
'use strict'

/**
 * Sorts items in specified order type
 *
 * @param {Array<Item>} items The items to sort
 * @param {String} sortOrder The sorting order type
 * @param {Boolean} sortDirection The sorting direction
 * @returns {Array}
 */
function sort(items, sortOrder, sortDirection) {
	try {
		var sortedItems = items.sort((itemA, itemB) => {
			return sortAlphabetically(itemA, itemB, sortOrder)
		})
		return sortDirection ? sortedItems.reverse() : sortedItems
	} catch {
		return items
	}
}

/**
 * Comparator to compare two items alphabetically in ascending order
 *
 * @param {Item} itemA The first item
 * @param {Item} itemB The second item
 * @param {String} sortOrder The sorting order type
 * @returns {Integer}
 */
function sortAlphabetically(itemA, itemB, sortOrder) {
	if (itemA.type === 'folder' && itemB.type === 'folder') {
		return itemA.name.toLowerCase().localeCompare(itemB.name.toLowerCase())
	}
	if (itemA.type === 'folder') {
		return -1
	}
	if (itemB.type === 'folder') {
		return 1
	}
	return itemA[sortOrder].toLowerCase().localeCompare(itemB[sortOrder].toLowerCase())
}

export {
	sort,
}
