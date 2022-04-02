/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 *
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

import Folder from '../models/folder'

import Axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

const state = {
	folders: {},
	loading: false,
}

const getters = {
	/**
	 * Returns all folders
	 *
	 * @param {object} state The store data
	 * @return {Array} The folders
	 */
	getFoldersByFolder: (state) => Object.values(state.folders),

	/**
	 * Returns whether we currently load folders from the server
	 *
	 * @param {object} state The store data
	 * @return {boolean} Are we loading folders
	 */
	loadingFolders: (state) => {
		return state.loading
	},
}

const mutations = {
	/**
	 * Sets the folders
	 *
	 * @param {object} state Default state
	 * @param {object} payload The folders object
	 */
	setFolders(state, payload) {
		state.folders = payload.folders
	},

	/**
	 * Adds a folder
	 *
	 * @param {object} state Default state
	 * @param {object} payload The folders object
	 */
	addFolder(state, payload) {
		state.folders.push(payload.folder)
	},

	/**
	 * Deletes a folder
	 *
	 * @param {object} state Default state
	 * @param {object} data Destructuring object
	 * @param {object} data.folder The folders object
	 */
	deleteFolder(state, { folder }) {
		// Find index of folder to update
		const index = state.folders.findIndex(f => f.id === folder.id)
		// Replace folder with new data
		Vue.delete(state.folders, index)
	},

	/**
	 * Updates a folder
	 *
	 * @param {object} state Default state
	 * @param {object} data Destructuring object
	 * @param {object} data.newFolder The folders object
	 */
	updateFolder(state, { newFolder }) {
		// Find index of folder to update
		const index = state.folders.findIndex(folder => folder.id === newFolder.id)
		// Replace folder with new data
		Vue.set(state.folders, index, newFolder)
	},
}

const actions = {
	/**
	 * Requests all folders for a given path
	 *
	 * @param {object} context The store object
	 * @param {object} context.commit The store mutations
	 * @param {object} context.state The store state
	 * @param {string} path The path to look at
	 * @return {Promise}
	 */
	async getFoldersByFolder({ commit, state }, path) {
		state.loading = true
		try {
			commit('setFolders', { folders: [] })
			const response = await Axios.post(generateUrl('apps/inventory/folders'), { path })
			const folders = response.data.map(payload => {
				return new Folder(payload)
			})
			commit('setFolders', { folders })
		} catch {
			console.debug('Could not load the folders.')
		}
		state.loading = false
	},

	async createFolder(context, { name, path }) {
		try {
			const response = await Axios.post(generateUrl('apps/inventory/folders/add'), { name, path })
			const folder = new Folder(response.data)
			context.commit('addFolder', { folder })
		} catch {
			console.debug('Could not create the folder.')
		}
	},

	async moveFolder({ commit }, { folderID, newPath }) {
		try {
			const response = await Axios.patch(generateUrl(`apps/inventory/folders/${folderID}/move`), { path: newPath })
			commit('deleteFolder', { folder: new Folder(response.data) })
		} catch {
			console.debug('Could not move the folder.')
		}
	},

	async deleteFolder(context, folderID) {
		try {
			const response = await Axios.delete(generateUrl(`apps/inventory/folders/${folderID}/delete`))
			context.commit('deleteFolder', { folder: new Folder(response.data) })
		} catch {
			console.debug('Could not delete the folder.')
		}
	},

	async renameFolder(context, { folderID, newName }) {
		try {
			const response = await Axios.patch(generateUrl(`apps/inventory/folders/${folderID}/rename`), { newName })
			context.commit('updateFolder', { newFolder: new Folder(response.data) })
		} catch {
			console.debug('Could not rename the folder.')
		}
	},
}

export default { state, getters, mutations, actions }
