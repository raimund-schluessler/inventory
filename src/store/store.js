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

import Vue from 'vue'
import Vuex from 'vuex'
import Item from '../models/item.js'
import Folder from '../models/folder.js'
import PQueue from 'p-queue'
import Status from '../models/status'
import Axios from 'axios'
Axios.defaults.headers.common.requesttoken = OC.requestToken

Vue.use(Vuex)

export default new Vuex.Store({
	state: {
		items: {},
		item: null,
		loadingItems: false,
		subItems: {},
		parentItems: {},
		relatedItems: {},
		itemCandidates: [],
		settings: {},
		folders: {},
		draggedEntities: [],
	},
	mutations: {

		/**
		 * Adds multiple items to the store
		 *
		 * @param {Object} state Default state
		 * @param {Array<Item>} items The items to add
		 */
		addItems(state, items = []) {
			state.items = items.reduce(function(list, item) {
				if (item instanceof Item) {
					Vue.set(list, item.id, item)
				} else {
					console.error('Wrong item object', item)
				}
				return list
			}, state.items)
		},

		/**
		 * Sets the items in the store
		 *
		 * @param {Object} state Default state
		 * @param {Array<Item>} items The items to set
		 */
		setItems(state, items = []) {
			state.items = items.reduce(function(list, item) {
				if (item instanceof Item) {
					Vue.set(list, item.id, item)
				} else {
					console.error('Wrong item object', item)
				}
				return list
			}, [])
		},

		/**
		 * Adds an item to the store
		 *
		 * @param {Object} state Default state
		 * @param {Item} item The item to add
		 */
		addItem(state, item) {
			Vue.set(state.items, item.id, item)
		},

		/**
		 * Deletes an item from the store
		 *
		 * @param {Object} state Default state
		 * @param {Item} item The item to delete
		 */
		deleteItem(state, item) {
			if (state.items[item.id] && item instanceof Item) {
				Vue.delete(state.items, item.id)
			}
		},

		/**
		 * Edits an item in the store
		 *
		 * @param {Object} state Default state
		 * @param {Item} item The item to edit
		 */
		editItem(state, item) {
			if (state.items[item.id] && item instanceof Item) {
				Vue.set(state.items, item.id, item)
			}
			if (state.item.id === item.id) {
				state.item = item
			}
		},

		/**
		 * Unlinks parent items
		 *
		 * @param {Object} state Default state
		 * @param {Item} items The items to unlink
		 */
		unlinkParents(state, items) {
			items.forEach((item) => {
				if (state.parentItems[item.id] && item instanceof Item) {
					Vue.delete(state.parentItems, item.id)
				}
			})
		},

		/**
		 * Unlinks related items
		 *
		 * @param {Object} state Default state
		 * @param {Item} items The items to unlink
		 */
		unlinkRelated(state, items) {
			items.forEach((item) => {
				if (state.relatedItems[item.id] && item instanceof Item) {
					Vue.delete(state.relatedItems, item.id)
				}
			})
		},

		/**
		 * Unlinks sub items
		 *
		 * @param {Object} state Default state
		 * @param {Item} items The items to unlink
		 */
		unlinkSub(state, items) {
			items.forEach((item) => {
				if (state.subItems[item.id] && item instanceof Item) {
					Vue.delete(state.subItems, item.id)
				}
			})
		},

		/**
		 * Adds a UUID to an item instance
		 *
		 * @param {Object} state Default state
		 * @param {Array} instance The item instance
		 * @param {Array} uuid The UUID
		 */
		addUuid(state, { instance, uuid }) {
			instance.uuids.push(uuid)
		},

		/**
		 * Deletes a UUID from an item instance
		 *
		 * @param {Object} state Default state
		 * @param {Array} instance The item instance
		 * @param {Array} uuid The UUID
		 */
		deleteUuid(state, { instance, uuid }) {
			instance.uuids = instance.uuids.filter((localUuid) => {
				return localUuid.uuid !== uuid
			})
		},

		/**
		 * Adds an instance to an item
		 *
		 * @param {Object} state Default state
		 * @param {Array} instance The item instance
		 * @param {Array} uuid The UUID
		 */
		addInstance(state, { item, instance }) {
			item.instances.push(instance)
		},

		/**
		 * Deletes an instance from an item
		 *
		 * @param {Object} state Default state
		 * @param {Array} item The item
		 * @param {Array} instance The item instance
		 */
		deleteInstance(state, { item, instance }) {
			item.instances = item.instances.filter((localInstance) => {
				return localInstance !== instance
			})
		},

		/**
		 * Edits an instance of an item
		 *
		 * @param {Object} state Default state
		 * @param {Array} item The item
		 * @param {Array} instance The new item instance
		 */
		editInstance(state, { item, instance }) {
			item.instances = item.instances.map((localInstance) => {
				return (localInstance.id === instance.id) ? instance : localInstance
			})
		},

		setItem(state, payload) {
			state.item = payload.item
		},

		setAttachments(state, { attachments }) {
			Vue.set(state.item, 'attachments', attachments)
		},

		setInstanceAttachments(state, { instanceID, attachments }) {
			const instance = state.item.instances.find(instance => instance.id === instanceID)
			Vue.set(instance, 'attachments', attachments)
		},

		setSubItems(state, items) {
			state.subItems = items.reduce(function(list, item) {
				if (item instanceof Item) {
					Vue.set(list, item.id, item)
				} else {
					console.error('Wrong item object', item)
				}
				return list
			}, {})
		},
		setParentItems(state, items) {
			state.parentItems = items.reduce(function(list, item) {
				if (item instanceof Item) {
					Vue.set(list, item.id, item)
				} else {
					console.error('Wrong item object', item)
				}
				return list
			}, {})
		},
		setRelatedItems(state, items) {
			state.relatedItems = items.reduce(function(list, item) {
				if (item instanceof Item) {
					Vue.set(list, item.id, item)
				} else {
					console.error('Wrong item object', item)
				}
				return list
			}, {})
		},
		setItemCandidates(state, payload) {
			state.itemCandidates = payload.itemCandidates
		},

		/**
		 * Sets all settings
		 *
		 * @param {Object} state Default state
		 * @param {Object} payload The settings object
		 */
		setSettings(state, payload) {
			state.settings = payload.settings
		},

		/**
		 * Sets a setting value
		 *
		 * @param {Object} state Default state
		 * @param {Object} payload The setting object
		 */
		setSetting(state, payload) {
			state.settings[payload.type] = payload.value
		},

		/**
		 * Adds an item to the store
		 *
		 * @param {Object} state Default state
		 * @param {Object} payload The folders object
		 */
		setFolders(state, payload) {
			state.folders = payload.folders
		},

		/**
		 * Adds an item to the store
		 *
		 * @param {Object} state Default state
		 * @param {Object} payload The folders object
		 */
		addFolder(state, payload) {
			state.folders.push(payload.folder)
		},

		setDraggedEntities(state, entities) {
			state.draggedEntities = entities
		},
	},

	getters: {

		/**
		 * Returns all items in the store
		 *
		 * @param {Object} state The store data
		 * @param {Object} getters The store getters
		 * @param {Object} rootState The store root state
		 * @returns {Array} All items in store
		 */
		getAllItems: (state, getters, rootState) => {
			return Object.values(state.items)
		},

		/**
		 * Returns all parent items in the store
		 *
		 * @param {Object} state The store data
		 * @param {Object} getters The store getters
		 * @param {Object} rootState The store root state
		 * @returns {Array} All parent items in store
		 */
		getParentItems: (state, getters, rootState) => {
			return Object.values(state.parentItems)
		},

		/**
		 * Returns all sub items in the store
		 *
		 * @param {Object} state The store data
		 * @param {Object} getters The store getters
		 * @param {Object} rootState The store root state
		 * @returns {Array} All sub items in store
		 */
		getSubItems: (state, getters, rootState) => {
			return Object.values(state.subItems)
		},

		/**
		 * Returns all related items in the store
		 *
		 * @param {Object} state The store data
		 * @param {Object} getters The store getters
		 * @param {Object} rootState The store root state
		 * @returns {Array} All related items in store
		 */
		getRelatedItems: (state, getters, rootState) => {
			return Object.values(state.relatedItems)
		},

		/**
		 * Returns whether we currently load items from the server
		 *
		 * @param {Object} state The store data
		 * @param {Object} getters The store getters
		 * @param {Object} rootState The store root state
		 * @returns {Boolean} Are we loading items
		 */
		loadingItems: (state, getters, rootState) => {
			return state.loadingItems
		},

		/**
		 * Returns the sort order how to sort tasks
		 *
		 * @param {Object} state The store data
		 * @returns {String} The sort order
		 */
		sortOrder: (state) => state.settings.sortOrder,

		/**
		 * Returns the sort direction how to sort tasks
		 *
		 * @param {Object} state The store data
		 * @returns {String} The sort direction
		 */
		sortDirection: (state) => state.settings.sortDirection,

		/**
		 * Returns all folders
		 *
		 * @param {Object} state The store data
		 * @returns {Array} The folders
		 */
		getFoldersByPath: (state) => Object.values(state.folders),

		getDraggedEntities: (state) => state.draggedEntities,
	},

	actions: {

		async loadItems({ commit, state }) {
			state.loadingItems = true
			const response = await Axios.get(OC.generateUrl('apps/inventory/items'))
			const items = response.data.map(payload => {
				return new Item(payload)
			})
			commit('addItems', items)
			state.loadingItems = false
		},

		async getItemsByPath({ commit, state }, path) {
			state.loadingItems = true
			const response = await Axios.post(OC.generateUrl('apps/inventory/items'), { path })
			const items = response.data.map(payload => {
				return new Item(payload)
			})
			commit('setItems', items)
			state.loadingItems = false
		},

		async createItems(context, items) {
			const queue = new PQueue({ concurrency: 5 })
			items.forEach(async(item) => {
				await queue.add(async() => {
					try {
						const response = await Axios.post(OC.generateUrl('apps/inventory/item/add'), { item: item.response })
						Vue.set(item, 'response', response.data)
						item.updateItem()
						item.syncstatus = new Status('created', 'Successfully created the item.') // eslint-disable-line require-atomic-updates
						context.commit('addItem', item)
					} catch {
						item.syncstatus = new Status('error', 'Item creation failed.') // eslint-disable-line require-atomic-updates
					}
				})
			})
		},

		async getItemById({ commit }, itemID) {
			try {
				const response = await Axios.get(OC.generateUrl(`apps/inventory/item/${itemID}`))
				const item = new Item(response.data)
				commit('setItem', { item })
			} catch {
				commit('setItem', { item: null })
			}
		},

		async getAttachments({ commit }, itemID) {
			try {
				const response = await Axios.get(OC.generateUrl(`apps/inventory/item/${itemID}/attachments`))
				commit('setAttachments', { attachments: response.data })
			} catch {
				commit('setAttachments', { attachments: [] })
			}
		},

		async getInstanceAttachments({ commit }, { itemID, instanceID }) {
			try {
				const response = await Axios.get(OC.generateUrl(`apps/inventory/item/${itemID}/instance/${instanceID}/attachments`))
				commit('setInstanceAttachments', { instanceID, attachments: response.data })
			} catch {
				commit('setInstanceAttachments', { instanceID, attachments: [] })
			}
		},
		async loadSubItems({ commit }, itemID) {
			try {
				const response = await Axios.get(OC.generateUrl(`apps/inventory/item/${itemID}/sub`))
				const subItems = response.data.map(payload => {
					return new Item(payload)
				})
				commit('setSubItems', subItems)
			} catch {
				commit('setSubItems', [])
			}
		},
		async loadParentItems({ commit }, itemID) {
			try {
				const response = await Axios.get(OC.generateUrl(`apps/inventory/item/${itemID}/parent`))
				const parentItems = response.data.map(payload => {
					return new Item(payload)
				})
				commit('setParentItems', parentItems)
			} catch {
				commit('setParentItems', [])
			}
		},
		async loadRelatedItems({ commit }, itemID) {
			try {
				const response = await Axios.get(OC.generateUrl(`apps/inventory/item/${itemID}/related`))
				const relatedItems = response.data.map(payload => {
					return new Item(payload)
				})
				commit('setRelatedItems', relatedItems)
			} catch {
				commit('setRelatedItems', [])
			}
		},
		async loadItemCandidates({ commit }, parameters) {
			try {
				commit('setItemCandidates', { itemCandidates: [] })
				const response = await Axios.get(OC.generateUrl(`apps/inventory/item/${parameters.itemID}/candidates/${parameters.relationType}`))
				const itemCandidates = response.data.map(payload => {
					return new Item(payload)
				})
				commit('setItemCandidates', { itemCandidates })
			} catch {
				commit('setItemCandidates', { itemCandidates: [] })
			}
		},
		async deleteItem({ commit }, item) {
			try {
				await Axios.delete(OC.generateUrl(`apps/inventory/item/${item.id}/delete`))
				commit('deleteItem', item)
			} catch {
				console.debug('Item deletion failed.')
			}
		},
		async deleteItems(context, items) {
			const queue = new PQueue({ concurrency: 5 })
			items.forEach(async(item) => {
				await queue.add(() => context.dispatch('deleteItem', item))
			})
		},
		async editItem({ commit }, item) {
			try {
				const response = await Axios.patch(OC.generateUrl(`apps/inventory/item/${item.id}/edit`), { item: item.response })
				Vue.set(item, 'response', response.data)
				item.updateItem()
				commit('editItem', item)
			} catch {
				console.debug('Item editing failed.')
			}
		},
		async moveItem({ commit }, { itemID, newPath }) {
			try {
				await Axios.patch(OC.generateUrl(`apps/inventory/item/${itemID}/move`), { path: newPath })
				// Vue.set(item, 'response', response.data)
				// item.updateItem()
				// commit('moveItem', item)
			} catch {
				console.debug('Item editing failed.')
			}
		},
		async linkItems(context, { itemID, relation, items }) {
			if (!Array.isArray(items) || !items.length) {
				return
			}
			try {
				// Extract itemIDs from items array
				const itemIDs = items.map((item) => { return item.id })
				await Axios.post(OC.generateUrl(`apps/inventory/item/${itemID}/link/${relation}`), { itemIDs })
				if (relation === 'parent') {
					context.dispatch('loadParentItems', itemID)
				} else if (relation === 'sub') {
					context.dispatch('loadSubItems', itemID)
				} else if (relation === 'related') {
					context.dispatch('loadRelatedItems', itemID)
				}
			} catch {
				console.debug('Linking items failed.')
			}
		},
		async unlinkItems({ commit }, { itemID, relation, items }) {
			if (!Array.isArray(items) || !items.length) {
				return
			}
			try {
				// Extract itemIDs from items array
				const itemIDs = items.map((item) => { return item.id })
				await Axios.post(OC.generateUrl(`apps/inventory/item/${itemID}/unlink/${relation}`), { itemIDs })
				if (relation === 'parent') {
					commit('unlinkParents', items)
				} else if (relation === 'sub') {
					commit('unlinkSub', items)
				} else if (relation === 'related') {
					commit('unlinkRelated', items)
				}
			} catch {
				console.debug('Unlinking items failed.')
			}
		},
		async addInstance({ commit }, { item, instance }) {
			try {
				const response = await Axios.post(OC.generateUrl(`apps/inventory/item/${item.id}/instance/add`), { instance })
				commit('addInstance', { item, instance: response.data })
			} catch {
				console.debug('Creating item instance failed.')
			}
		},
		async deleteInstance({ commit }, { item, instance }) {
			try {
				await Axios.delete(OC.generateUrl(`apps/inventory/item/${item.id}/instance/${instance.id}/delete`))
				commit('deleteInstance', { item, instance })
			} catch {
				console.debug('Deleting item instance failed.')
			}
		},
		async editInstance({ commit }, { item, instance }) {
			try {
				const response = await Axios.patch(OC.generateUrl(`apps/inventory/item/${item.id}/instance/${instance.id}/edit`), { instance })
				commit('editInstance', { item, instance: response.data })
			} catch {
				console.debug('Editing item instance failed.')
			}
		},
		async addUuid({ commit }, { item, instance, uuid }) {
			try {
				const response = await Axios.put(OC.generateUrl(`apps/inventory/item/${item.id}/instance/${instance.id}/uuid/${uuid}`))
				commit('addUuid', { instance, uuid: response.data })
			} catch {
				console.debug('Saving uuid failed.')
			}
		},
		async deleteUuid({ commit }, { item, instance, uuid }) {
			try {
				await Axios.delete(OC.generateUrl(`apps/inventory/item/${item.id}/instance/${instance.id}/uuid/${uuid}`))
				commit('deleteUuid', { instance, uuid })
			} catch {
				console.debug('Uuid deletion failed.')
			}
		},

		/**
		* Writes a setting to the server
		*
		* @param {Object} context The store context
		* @param {Object} payload The setting to save
		* @returns {Promise}
		*/
		async setSetting(context, payload) {
			try {
				context.commit('setSetting', payload)
				await Axios.post(OC.generateUrl('apps/inventory/settings/{type}/{value}', payload), {})
			} catch {
				console.debug('Could not save settings.')
			}
		},

		/**
		 * Requests all app settings from the server
		 *
		 * @param {Object} commit The store mutations
		 * @returns {Promise}
		 */
		async loadSettings({ commit }) {
			try {
				const response = await Axios.get(OC.generateUrl('apps/inventory/settings'))
				commit('setSettings', { settings: response.data })
			} catch {
				console.debug('Could not load settings.')
			}
		},

		/**
		 * Requests all folders for a given path
		 *
		 * @param {Object} context The store context
		 * @param {String} path The path to look at
		 * @returns {Promise}
		 */
		async getFoldersByPath({ commit }, path) {
			try {
				const response = await Axios.post(OC.generateUrl('apps/inventory/folders'), { path })
				const folders = response.data.map(payload => {
					return new Folder(payload)
				})
				commit('setFolders', { folders })
			} catch {
				console.debug('Could not load the folders.')
			}
		},

		async createFolder(context, { name, path }) {
			try {
				const response = await Axios.post(OC.generateUrl('apps/inventory/folders/add'), { name, path })
				const folder = new Folder(response.data)
				context.commit('addFolder', { folder })
			} catch {
				console.debug('Could not create the folder.')
			}
		},

		async moveFolder({ commit }, { folderID, newPath }) {
			try {
				await Axios.patch(OC.generateUrl(`apps/inventory/folders/${folderID}/move`), { path: newPath })
				// Vue.set(item, 'response', response.data)
				// item.updateFolder()
				// commit('moveFolder', item)
			} catch {
				console.debug('Could not move the folder.')
			}
		},

		async deleteFolder(context, folder) {
			try {
				await Axios.delete(OC.generateUrl(`apps/inventory/folders/${folder.id}/delete`))
			} catch {
				console.debug('Could not delete the folder.')
			}
		},
	}
})
