/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 *
 * @copyright 2020 Raimund Schlüßler <raimund.schluessler@mailbox.org>
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
import SyncStatus from './syncStatus.js'

export default class Place {

	/**
	 * Creates an instance of a Place
	 *
	 * @param {Array} response The place payload
	 * @memberof Place
	 */
	constructor(response) {
		if (typeof response !== 'object') {
			throw new Error('')
		}

		this.response = response

		this.syncStatus = null

		this.initPlace()
	}

	initPlace() {

		// Define properties, so Vue reacts to changes of them
		this._id = this.response.id || 0
		this._uid = this.response.uid || ''
		this._name = this.response.name || ''
		this._path = this.response.path || ''
		this._uuids = this.response.uuids || []
		this._parentid = this.response._parentid || ''
		this._description = this.response.description || ''
		if (this.response.syncStatus) {
			this.syncStatus = new SyncStatus(this.response.syncStatus.type, this.response.syncStatus.message)
		}
	}

	/**
	 * Update internal data of this place
	 *
	 * @memberof Place
	 */
	updatePlace() {
		this.initPlace()
	}

	/**
	 * Return the key
	 *
	 * @readonly
	 * @memberof Item
	 */
	get key() {
		return `place_${this._id}`
	}

	/**
	 * Return the id
	 *
	 * @readonly
	 * @memberof Place
	 */
	get id() {
		return this._id
	}

	/**
	 * Set the id
	 *
	 * @param {string} id The new id
	 * @memberof Place
	 */
	set id(id) {
		this.response.id = id
		this._id = this.response.id || 0
	}

	/**
	 * Return the uid
	 *
	 * @readonly
	 * @memberof Place
	 */
	get uid() {
		return this._uid
	}

	/**
	 * Return the path
	 *
	 * @readonly
	 * @memberof Place
	 */
	get path() {
		return this._path
	}

	/**
	 * Set the path (important for renaming the place)
	 *
	 * @param {string} path The new path
	 * @memberof Place
	 */
	set path(path) {
		this.response.path = path
		this._path = this.response.path || ''
	}

	/**
	 * Return the parent id
	 *
	 * @readonly
	 * @memberof Place
	 */
	get parentid() {
		return this._parentid
	}

	/**
	 * Return the name
	 *
	 * @readonly
	 * @memberof Place
	 */
	get name() {
		return this._name
	}

	/**
	 * Set the name
	 *
	 * @param {string} name The new name
	 * @memberof Place
	 */
	set name(name) {
		this.response.name = name
		this._name = this.response.name || ''
	}

	/**
	 * Return the description
	 *
	 * @readonly
	 * @memberof Place
	 */
	get description() {
		return this._description
	}

	/**
	 * Set the description
	 *
	 * @param {string} description The new description
	 * @memberof Place
	 */
	set description(description) {
		this.response.description = description
		this._description = this.response.description || ''
	}

	/**
	 * Return the assigned UUIDs
	 *
	 * @readonly
	 * @memberof Place
	 */
	get uuids() {
		return this._uuids
	}

	/**
	 * Set the UUIDs
	 *
	 * @param {Array} uuids The UUIDs
	 * @memberof Place
	 */
	set uuids(uuids) {
		this.response.uuids = uuids
		this._uuids = this.response.uuids || []
	}

}
