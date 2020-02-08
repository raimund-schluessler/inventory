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
import Status from './status'

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

		this.syncstatus = null

		this.initItem()
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
		this._attachments = this.response.attachments || []
		this._path = this.response.path || ''
		this._folderid = this.response.folderid || ''
		if (this.response.syncstatus) {
			this.syncstatus = new Status(this.response.syncstatus.type, this.response.syncstatus.message)
		}
	}

	/**
	 * Update internal data of this item
	 *
	 * @memberof Item
	 */
	updateItem() {
		this.initItem()
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
	 * Return the path
	 *
	 * @readonly
	 * @memberof Item
	 */
	get path() {
		return this._path
	}

	/**
	 * Return the folderid
	 *
	 * @readonly
	 * @memberof Item
	 */
	get folderid() {
		return this._folderid
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
	 * Set the name
	 *
	 * @param {string} name The new name
	 * @memberof Item
	 */
	set name(name) {
		this.response.name = name
		this._name = this.response.name || ''
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
	 * Set the maker
	 *
	 * @param {string} maker The new maker
	 * @memberof Item
	 */
	set maker(maker) {
		this.response.maker = maker
		this._maker = this.response.maker || ''
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
	 * Set the description
	 *
	 * @param {string} description The new description
	 * @memberof Item
	 */
	set description(description) {
		this.response.description = description
		this._description = this.response.description || ''
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
	 * Set the itemNumber
	 *
	 * @param {string} itemNumber The new itemNumber
	 * @memberof Item
	 */
	set itemNumber(itemNumber) {
		this.response.itemNumber = itemNumber
		this._itemNumber = this.response.itemNumber || ''
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
	 * Set the link
	 *
	 * @param {string} link The new link
	 * @memberof Item
	 */
	set link(link) {
		this.response.link = link
		this._link = this.response.link || ''
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
	 * Set the gtin
	 *
	 * @param {string} gtin The new gtin
	 * @memberof Item
	 */
	set gtin(gtin) {
		this.response.gtin = gtin
		this._gtin = this.response.gtin || ''
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
	 * Set the details
	 *
	 * @param {string} details The new details
	 * @memberof Item
	 */
	set details(details) {
		this.response.details = details
		this._details = this.response.details || ''
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
	 * Set the comment
	 *
	 * @param {string} comment The new comment
	 * @memberof Item
	 */
	set comment(comment) {
		this.response.comment = comment
		this._comment = this.response.comment || ''
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
	 * Set the categories
	 *
	 * @param {string} categories The new categories
	 * @memberof Item
	 */
	set categories(categories) {
		this.response.categories = categories
		this._categories = this.response.categories || ''
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

	/**
	 * Return the instances
	 *
	 * @param {Array} instances The instances
	 * @memberof Item
	 */
	set instances(instances) {
		this.response.instances = instances
		this._instances = this.response.instances || []
	}

	/**
	 * Return the attachments
	 *
	 * @readonly
	 * @memberof Item
	 */
	get attachments() {
		return this._attachments
	}

	/**
	 * Return the attachments
	 *
	 * @param {Array} attachments The attachments
	 * @memberof Item
	 */
	set attachments(attachments) {
		this.response.attachments = attachments
		this._attachments = this.response.attachments || []
	}

}
