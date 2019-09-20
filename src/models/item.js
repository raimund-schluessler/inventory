/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2018 Raimund Schlüßler <raimund.schluessler@mailbox.org>
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

export default class Item {

	/**
	 * Creates an instance of an Item
	 *
	 * @param {Array} response The item payload
	 * @memberof Item
	 */
	constructor(response) {
		if (typeof response !== 'object') {
			throw new Error('')
		}

		this.response = response

		this.subItems = {}
		this.parentItems = {}
		this.relatedItems = {}

		this.initItem()

		this.syncstatus = null
	}

	initItem() {

		// Define properties, so Vue reacts to changes of them
		this._id = this.response.id || 0
		this._uid = this.response.uid || ''
		this._name = this.response.name || ''
		this._maker = this.response.maker || ''
		this._description = this.response.description || ''
		this._itemNumber = this.response.itemNumber || ''
		this._link = this.response.link || ''
		this._gtin = this.response.gtin || ''
		this._details = this.response.details || ''
		this._comment = this.response.comment || ''
		this._type = this.response.type || ''
		this._icon = this.response.icon || ''
		this._categories = this.response.categories || []
		this._instances = this.response.instances || []
	}

	/**
	 * Return the id
	 *
	 * @readonly
	 * @memberof Item
	 */
	get id() {
		return this._id
	}

	/**
	 * Set the id
	 *
	 * @param {string} id The new id
	 * @memberof Item
	 */
	set id(id) {
		this.response.id = id
		this._id = this.response.id || 0
	}

	/**
	 * Return the uid
	 *
	 * @readonly
	 * @memberof Item
	 */
	get uid() {
		return this._uid
	}

	/**
	 * Return the name
	 *
	 * @readonly
	 * @memberof Item
	 */
	get name() {
		return this._name
	}

	/**
	 * Return the maker
	 *
	 * @readonly
	 * @memberof Item
	 */
	get maker() {
		return this._maker
	}

	/**
	 * Return the description
	 *
	 * @readonly
	 * @memberof Item
	 */
	get description() {
		return this._description
	}

	/**
	 * Return the itemNumber
	 *
	 * @readonly
	 * @memberof Item
	 */
	get itemNumber() {
		return this._itemNumber
	}

	/**
	 * Return the link
	 *
	 * @readonly
	 * @memberof Item
	 */
	get link() {
		return this._link
	}

	/**
	 * Return the gtin
	 *
	 * @readonly
	 * @memberof Item
	 */
	get gtin() {
		return this._gtin
	}

	/**
	 * Return the details
	 *
	 * @readonly
	 * @memberof Item
	 */
	get details() {
		return this._details
	}

	/**
	 * Return the comment
	 *
	 * @readonly
	 * @memberof Item
	 */
	get comment() {
		return this._comment
	}

	/**
	 * Return the type
	 *
	 * @readonly
	 * @memberof Item
	 */
	get type() {
		return this._type
	}

	/**
	 * Return the icon
	 *
	 * @readonly
	 * @memberof Item
	 */
	get icon() {
		return this._icon
	}

	/**
	 * Return the categories
	 *
	 * @readonly
	 * @memberof Item
	 */
	get categories() {
		return this._categories
	}

	/**
	 * Return the instances
	 *
	 * @readonly
	 * @memberof Item
	 */
	get instances() {
		return this._instances
	}

}
