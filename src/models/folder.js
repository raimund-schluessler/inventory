/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
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
import Status from './status'

export default class Folder {

	/**
	 * Creates an instance of a Folder
	 *
	 * @param {Array} response The folder payload
	 * @memberof Folder
	 */
	constructor(response) {
		if (typeof response !== 'object') {
			throw new Error('')
		}

		this.response = response

		this.syncstatus = null

		this.initFolder()
	}

	initFolder() {

		// Define properties, so Vue reacts to changes of them
		this._id = this.response.id || 0
		this._uid = this.response.uid || ''
		this._name = this.response.name || ''
		this._path = this.response.path || ''
		this._parentid = this.response._parentid || ''
		if (this.response.syncstatus) {
			this.syncstatus = new Status(this.response.syncstatus.type, this.response.syncstatus.message)
		}
	}

	/**
	 * Update internal data of this folder
	 *
	 * @memberof Folder
	 */
	updateFolder() {
		this.initFolder()
	}

	/**
	 * Return the id
	 *
	 * @readonly
	 * @memberof Folder
	 */
	get id() {
		return this._id
	}

	/**
	 * Set the id
	 *
	 * @param {string} id The new id
	 * @memberof Folder
	 */
	set id(id) {
		this.response.id = id
		this._id = this.response.id || 0
	}

	/**
	 * Return the uid
	 *
	 * @readonly
	 * @memberof Folder
	 */
	get uid() {
		return this._uid
	}

	/**
	 * Return the path
	 *
	 * @readonly
	 * @memberof Folder
	 */
	get path() {
		return this._path
	}

	/**
	 * Return the parent id
	 *
	 * @readonly
	 * @memberof Folder
	 */
	get parentid() {
		return this._parentid
	}

	/**
	 * Return the name
	 *
	 * @readonly
	 * @memberof Folder
	 */
	get name() {
		return this._name
	}

	/**
	 * Set the name
	 *
	 * @param {string} name The new name
	 * @memberof Folder
	 */
	set name(name) {
		this.response.name = name
		this._name = this.response.name || ''
	}

}
