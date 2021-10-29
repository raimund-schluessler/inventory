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

import Item from '../models/item'
import Folder from '../models/folder'
import Place from '../models/place'
import SyncStatus from '../models/syncStatus'

import Axios from '@nextcloud/axios'
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'

import PQueue from 'p-queue'
import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const state = {
	items: {},
	item: null,
	loadingItems: false,
	loadingItem: false,
	loadingAttachments: [],
	loadingInstanceAttachments: [],
	subItems: {},
	parentItems: {},
	relatedItems: {},
	itemCandidates: [],
	draggedEntities: [],
	searching: false,
	searchResults: [],
}

const getters = {

	getItem: (state) => state.item,

	/**
	 * Returns all items in the store
	 *
	 * @param {object} state The store data
	 * @param {object} getters The store getters
	 * @param {object} rootState The store root state
	 * @return {Array} All items in store
	 */
	getAllItems: (state, getters, rootState) => {
		return Object.values(state.items)
	},

	/**
	 * Returns all parent items in the store
	 *
	 * @param {object} state The store data
	 * @param {object} getters The store getters
	 * @param {object} rootState The store root state
	 * @return {Array} All parent items in store
	 */
	getParentItems: (state, getters, rootState) => {
		return Object.values(state.parentItems)
	},

	/**
	 * Returns all sub items in the store
	 *
	 * @param {object} state The store data
	 * @param {object} getters The store getters
	 * @param {object} rootState The store root state
	 * @return {Array} All sub items in store
	 */
	getSubItems: (state, getters, rootState) => {
		return Object.values(state.subItems)
	},

	/**
	 * Returns all related items in the store
	 *
	 * @param {object} state The store data
	 * @param {object} getters The store getters
	 * @param {object} rootState The store root state
	 * @return {Array} All related items in store
	 */
	getRelatedItems: (state, getters, rootState) => {
		return Object.values(state.relatedItems)
	},

	getItemCandidates: (state) => state.itemCandidates,

	/**
	 * Returns whether we currently load items from the server
	 *
	 * @param {object} state The store data
	 * @return {boolean} Are we loading items
	 */
	loadingItems: (state) => {
		return state.loadingItems
	},

	/**
	 * Returns whether we currently load a single item from the server
	 *
	 * @param {object} state The store data
	 * @return {boolean} Are we loading an item
	 */
	loadingItem: (state) => {
		return state.loadingItem
	},

	/**
	 * Returns whether we currently load attachments of an item
	 *
	 * @param {object} state The store data
	 * @return {boolean} Are we loading an item
	 */
	loadingAttachments: (state) => (itemID) => {
		return state.loadingAttachments.includes(`item-${itemID}`)
	},

	/**
	 * Returns whether we currently load a single item from the server
	 *
	 * @param {object} state The store data
	 * @return {boolean} Are we loading an item
	 */
	loadingInstanceAttachments: (state) => ({ itemID, instanceID }) => {
		return state.loadingInstanceAttachments.includes(`item-${itemID}_instance-${instanceID}`)
	},

	getDraggedEntities: (state) => state.draggedEntities,

	/**
	 * Returns the search results from the server
	 *
	 * @param {object} state The store data
	 * @param {object} getters The store getters
	 * @param {object} rootState The store root state
	 * @return {Array} The results
	 */
	searchResults: (state, getters, rootState) => {
		return state.searchResults.filter(entity => {
			if (entity instanceof Item) {
				return entity.path !== rootState.route.params.path
			}
			if (entity instanceof Folder) {
				// Don't show folders in the same folder
				// or their parent folder (since we are already in the folder)
				return entity.path !== rootState.route.params.path
				&& entity.path !== `${rootState.route.params.path}/${entity.name}`
			}
			return false
		})
	},

	/**
	 * Returns the search results from the server
	 *
	 * @param {object} state The store data
	 * @return {Array} The results
	 */
	searching: (state) => state.searching,
}

const mutations = {

	/**
	 * Adds multiple items to the store
	 *
	 * @param {object} state Default state
	 * @param {Array<Item>} items The items to add
	 */
	addItems(state, items = []) {
		state.items = items.reduce(function(list, item) {
			if (item instanceof Item) {
				Vue.set(list, item.key, item)
			} else {
				console.error('Wrong item object', item)
			}
			return list
		}, state.items)
	},

	/**
	 * Sets the items in the store
	 *
	 * @param {object} state Default state
	 * @param {Array<Item>} items The items to set
	 */
	setItems(state, items = []) {
		state.items = items.reduce(function(list, item) {
			if (item instanceof Item) {
				Vue.set(list, item.key, item)
			} else {
				console.error('Wrong item object', item)
			}
			return list
		}, [])
	},

	/**
	 * Adds an item to the store
	 *
	 * @param {object} state Default state
	 * @param {Item} item The item to add
	 */
	addItem(state, item) {
		Vue.set(state.items, item.key, item)
	},

	/**
	 * Deletes an item from the store
	 *
	 * @param {object} state Default state
	 * @param {Item} item The item to delete
	 */
	deleteItem(state, item) {
		if (state.items[item.key] && item instanceof Item) {
			Vue.delete(state.items, item.key)
		}
	},

	/**
	 * Edits an item in the store
	 *
	 * @param {object} state Default state
	 * @param {Item} item The item to edit
	 */
	editItem(state, item) {
		if (state.items[item.id] && item instanceof Item) {
			Vue.set(state.items, item.key, item)
		}
		if (state.item.id === item.id) {
			state.item = item
		}
	},

	/**
	 * Unlinks parent items
	 *
	 * @param {object} state Default state
	 * @param {Item} items The items to unlink
	 */
	unlinkParents(state, items) {
		items.forEach((item) => {
			if (state.parentItems[item.key] && item instanceof Item) {
				Vue.delete(state.parentItems, item.key)
			}
		})
	},

	/**
	 * Unlinks related items
	 *
	 * @param {object} state Default state
	 * @param {Item} items The items to unlink
	 */
	unlinkRelated(state, items) {
		items.forEach((item) => {
			if (state.relatedItems[item.key] && item instanceof Item) {
				Vue.delete(state.relatedItems, item.key)
			}
		})
	},

	/**
	 * Unlinks sub items
	 *
	 * @param {object} state Default state
	 * @param {Item} items The items to unlink
	 */
	unlinkSub(state, items) {
		items.forEach((item) => {
			if (state.subItems[item.key] && item instanceof Item) {
				Vue.delete(state.subItems, item.key)
			}
		})
	},

	/**
	 * Adds a UUID to an item instance
	 *
	 * @param {object} state Default state
	 * @param {object} data Destructuring object
	 * @param {Array} data.instance The item instance
	 * @param {Array} data.uuid The UUID
	 */
	addUuid(state, { instance, uuid }) {
		instance.uuids.push(uuid)
	},

	/**
	 * Deletes a UUID from an item instance
	 *
	 * @param {object} state Default state
	 * @param {object} data Destructuring object
	 * @param {Array} data.instance The item instance
	 * @param {Array} data.uuid The UUID
	 */
	deleteUuid(state, { instance, uuid }) {
		instance.uuids = instance.uuids.filter((localUuid) => {
			return localUuid.uuid !== uuid
		})
	},

	/**
	 * Adds an instance to an item
	 *
	 * @param {object} state Default state
	 * @param {object} data Destructuring object
	 * @param {Array} data.item The item
	 * @param {Array} data.instance The item instance
	 */
	addInstance(state, { item, instance }) {
		item.instances.push(instance)
	},

	/**
	 * Deletes an instance from an item
	 *
	 * @param {object} state Default state
	 * @param {object} data Destructuring object
	 * @param {Array} data.item The item
	 * @param {Array} data.instance The item instance
	 */
	deleteInstance(state, { item, instance }) {
		item.instances = item.instances.filter((localInstance) => {
			return localInstance !== instance
		})
	},

	/**
	 * Edits an instance of an item
	 *
	 * @param {object} state Default state
	 * @param {object} data Destructuring object
	 * @param {Array} data.item The item
	 * @param {Array} data.instance The new item instance
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

	createAttachment(state, { attachment }) {
		const index = state.item.attachments.findIndex(a => a.id === attachment.id)
		if (index < 0) {
			state.item.attachments.push(attachment)
		}
	},

	updateAttachment(state, { attachment }) {
		const index = state.item.attachments.findIndex(a => a.id === attachment.id)
		if (index > -1) {
			Vue.set(state.item.attachments, index, attachment)
		}
	},

	deleteAttachment(state, { attachmentId }) {
		const index = state.item.attachments.findIndex(a => a.id === attachmentId)
		if (index > -1) {
			Vue.delete(state.item.attachments, index)
		}
	},

	setInstanceAttachments(state, { instanceID, attachments }) {
		const instance = state.item.instances.find(instance => instance.id === instanceID)
		Vue.set(instance, 'attachments', attachments)
	},

	createInstanceAttachment(state, { attachment, instanceId }) {
		const instance = state.item.instances.find(instance => +instance.id === +instanceId)
		if (instance) {
			const index = instance.attachments.findIndex(a => a.id === attachment.id)
			if (index < 0) {
				instance.attachments.push(attachment)
			}
		}
	},

	updateInstanceAttachment(state, { attachment, instanceId }) {
		const instance = state.item.instances.find(instance => +instance.id === +instanceId)
		if (instance) {
			const index = instance.attachments.findIndex(a => a.id === attachment.id)
			if (index > -1) {
				Vue.set(instance.attachments, index, attachment)
			}
		}
	},

	deleteInstanceAttachment(state, { attachmentId, instanceId }) {
		const instance = state.item.instances.find(instance => +instance.id === +instanceId)
		if (instance) {
			const index = instance.attachments.findIndex(a => a.id === attachmentId)
			if (index > -1) {
				Vue.delete(instance.attachments, index)
			}
		}
	},

	addImage(state, { image }) {
		state.item.images.push(image)
	},

	setSubItems(state, items) {
		state.subItems = items.reduce(function(list, item) {
			if (item instanceof Item) {
				Vue.set(list, item.key, item)
			} else {
				console.error('Wrong item object', item)
			}
			return list
		}, {})
	},
	setParentItems(state, items) {
		state.parentItems = items.reduce(function(list, item) {
			if (item instanceof Item) {
				Vue.set(list, item.key, item)
			} else {
				console.error('Wrong item object', item)
			}
			return list
		}, {})
	},
	setRelatedItems(state, items) {
		state.relatedItems = items.reduce(function(list, item) {
			if (item instanceof Item) {
				Vue.set(list, item.key, item)
			} else {
				console.error('Wrong item object', item)
			}
			return list
		}, {})
	},
	setItemCandidates(state, payload) {
		state.itemCandidates = payload.itemCandidates
	},

	setDraggedEntities(state, entities) {
		state.draggedEntities = entities
	},

	/**
	 * Sets the search results
	 *
	 * @param {object} state Default state
	 * @param {object} entities The search results
	 */
	setSearchResults(state, entities) {
		state.searchResults = entities
	},

	/**
	 * Sets the sync status of an item
	 *
	 * @param {object} state Default state
	 * @param {object} data Destructuring object
	 * @param {object} data.item The item
	 * @param {string} data.status The sync status
	 */
	setSyncStatus(state, { item, status }) {
		Vue.set(item, 'syncStatus', status)
	},
}

const actions = {

	async loadItems({ commit, state }) {
		state.loadingItems = true
		const response = await Axios.get(generateUrl('apps/inventory/items'))
		const items = response.data.map(payload => {
			return new Item(payload)
		})
		commit('addItems', items)
		state.loadingItems = false
	},

	async getItemsByFolder({ commit, state }, path) {
		state.loadingItems = true
		commit('setItems', [])
		const response = await Axios.post(generateUrl('apps/inventory/items/folder'), { path })
		const items = response.data.map(payload => {
			return new Item(payload)
		})
		commit('setItems', items)
		state.loadingItems = false
	},

	async getItemsByPlace({ commit, state }, path) {
		state.loadingItems = true
		commit('setItems', [])
		const response = await Axios.post(generateUrl('apps/inventory/items/place'), { path })
		const items = response.data.map(payload => {
			return new Item(payload)
		})
		commit('setItems', items)
		state.loadingItems = false
	},

	async createItems(context, items) {
		const queue = new PQueue({ concurrency: 5 })
		items.forEach(async (item) => {
			await queue.add(async () => {
				try {
					const response = await Axios.post(generateUrl('apps/inventory/item/add'), { item: item.response })
					Vue.set(item, 'response', response.data)
					item.updateItem()
					item.syncStatus = new SyncStatus('success', 'Successfully created the item.') // eslint-disable-line require-atomic-updates
					context.commit('addItem', item)
				} catch {
					item.syncStatus = new SyncStatus('error', 'Item creation failed.') // eslint-disable-line require-atomic-updates
				}
			})
		})
	},

	async getItemById({ commit }, itemID) {
		state.loadingItem = true
		try {
			const response = await Axios.get(generateUrl(`apps/inventory/item/${itemID}`))
			const item = new Item(response.data)
			commit('setItem', { item })
		} catch {
			commit('setItem', { item: null })
		}
		state.loadingItem = false
	},

	async getAttachments({ commit }, itemID) {
		state.loadingAttachments.push(`item-${itemID}`)
		try {
			const response = await Axios.get(generateUrl(`apps/inventory/item/${itemID}/attachments`))
			commit('setAttachments', { attachments: response.data })
		} catch {
			commit('setAttachments', { attachments: [] })
		}
		state.loadingAttachments = state.loadingAttachments.filter(entry => entry !== `item-${itemID}`)
	},

	async getInstanceAttachments({ commit }, { itemID, instanceID }) {
		state.loadingInstanceAttachments.push(`item-${itemID}_instance-${instanceID}`)
		try {
			const response = await Axios.get(generateUrl(`apps/inventory/item/${itemID}/instance/${instanceID}/attachments`))
			commit('setInstanceAttachments', { instanceID, attachments: response.data })
		} catch {
			commit('setInstanceAttachments', { instanceID, attachments: [] })
		}
		state.loadingInstanceAttachments = state.loadingInstanceAttachments.filter(entry => entry !== `item-${itemID}_instance-${instanceID}`)
	},

	async createAttachment({ commit }, { itemId, formData, instanceId }) {
		if (instanceId) {
			const response = await Axios.post(generateUrl(`apps/inventory/item/${itemId}/instance/${instanceId}/attachment/create`), formData)
			commit('createInstanceAttachment', { itemId, attachment: response.data, instanceId })
		} else {
			const response = await Axios.post(generateUrl(`apps/inventory/item/${itemId}/attachment/create`), formData)
			commit('createAttachment', { itemId, attachment: response.data })
		}
	},

	async updateAttachment({ commit }, { itemId, attachmentId, formData, instanceId }) {
		if (instanceId) {
			const response = await Axios.post(generateUrl(`apps/inventory/item/${itemId}/instance/${instanceId}/attachment/${attachmentId}/update`), formData)
			commit('updateInstanceAttachment', { itemId, attachment: response.data, instanceId })
		} else {
			const response = await Axios.post(generateUrl(`apps/inventory/item/${itemId}/attachment/${attachmentId}/update`), formData)
			commit('updateAttachment', { itemId, attachment: response.data })
		}
	},

	async deleteAttachment({ commit }, { itemId, attachmentId, instanceId }) {
		if (instanceId) {
			const response = await Axios.delete(generateUrl(`apps/inventory/item/${itemId}/instance/${instanceId}/attachment/${attachmentId}/delete`))
			commit('deleteInstanceAttachment', { itemId, attachmentId, instanceId })
			return response
		} else {
			const response = await Axios.delete(generateUrl(`apps/inventory/item/${itemId}/attachment/${attachmentId}/delete`))
			commit('deleteAttachment', { itemId, attachmentId })
			return response
		}
	},

	async linkAttachment({ commit }, { itemId, attachment, instanceId }) {
		if (instanceId) {
			const response = await Axios.post(generateUrl(`apps/inventory/item/${itemId}/instance/${instanceId}/attachment/link`), { attachment })
			commit('createInstanceAttachment', { itemId, attachment: response.data, instanceId })
		} else {
			const response = await Axios.post(generateUrl(`apps/inventory/item/${itemId}/attachment/link`), { attachment })
			commit('createAttachment', { itemId, attachment: response.data })
		}
	},

	async unlinkAttachment({ commit }, { itemId, attachmentId, instanceId }) {
		if (instanceId) {
			const response = await Axios.delete(generateUrl(`apps/inventory/item/${itemId}/instance/${instanceId}/attachment/${attachmentId}/unlink`))
			commit('deleteInstanceAttachment', { itemId, attachmentId, instanceId })
			return response
		} else {
			const response = await Axios.delete(generateUrl(`apps/inventory/item/${itemId}/attachment/${attachmentId}/unlink`))
			commit('deleteAttachment', { itemId, attachmentId })
			return response
		}
	},

	async getAttachmentFolder() {
		try {
			return loadState('inventory', 'attachmentFolder')
		} catch (error) {
			return ''
		}
	},

	async uploadImage({ commit }, { itemId, formData }) {
		const response = await Axios.post(generateUrl(`apps/inventory/item/${itemId}/image/upload`), formData)
		commit('addImage', { itemId, image: response.data })
	},

	async setAttachmentFolder(context, { path }) {
		return Axios.post(generateUrl('apps/inventory/settings/attachmentFolder/set'), { path })
	},

	async loadSubItems({ commit }, itemID) {
		try {
			const response = await Axios.get(generateUrl(`apps/inventory/item/${itemID}/sub`))
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
			const response = await Axios.get(generateUrl(`apps/inventory/item/${itemID}/parent`))
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
			const response = await Axios.get(generateUrl(`apps/inventory/item/${itemID}/related`))
			const relatedItems = response.data.map(payload => {
				return new Item(payload)
			})
			commit('setRelatedItems', relatedItems)
		} catch {
			commit('setRelatedItems', [])
		}
	},
	async loadItemCandidates({ commit }, parameters) {
		state.loadingItems = true
		try {
			commit('setItemCandidates', { itemCandidates: [] })
			const response = await Axios.get(generateUrl(`apps/inventory/item/${parameters.itemID}/candidates/${parameters.relationType}`))
			const itemCandidates = response.data.map(payload => {
				return new Item(payload)
			})
			commit('setItemCandidates', { itemCandidates })
		} catch {
			commit('setItemCandidates', { itemCandidates: [] })
		}
		state.loadingItems = false
	},
	async deleteItem({ commit }, item) {
		try {
			await Axios.delete(generateUrl(`apps/inventory/item/${item.id}/delete`))
			commit('deleteItem', item)
		} catch {
			console.debug('Item deletion failed.')
		}
	},
	async deleteItems(context, items) {
		const queue = new PQueue({ concurrency: 5 })
		items.forEach(async (item) => {
			await queue.add(() => context.dispatch('deleteItem', item))
		})
		await queue.onIdle()
	},
	async editItem({ commit }, item) {
		try {
			const response = await Axios.patch(generateUrl(`apps/inventory/item/${item.id}/edit`), { item: item.response })
			Vue.set(item, 'response', response.data)
			item.updateItem()
			commit('editItem', item)
		} catch {
			console.debug('Item editing failed.')
		}
	},
	async moveItem({ commit }, { itemID, newPath }) {
		try {
			const response = await Axios.patch(generateUrl(`apps/inventory/item/${itemID}/move`), { path: newPath })
			const item = new Item(response.data)
			commit('deleteItem', item)
		} catch {
			console.debug('Item editing failed.')
		}
	},
	async moveItemByUuid({ commit }, { uuid, newPath }) {
		try {
			return await Axios.patch(generateUrl('apps/inventory/item/move'), { newPath, uuid })
		} catch {
			console.debug('Moving the item by UUID failed.')
		}
	},
	async moveInstance({ commit }, { itemID, instanceID, newPath }) {
		try {
			const response = await Axios.patch(generateUrl(`apps/inventory/item/${itemID}/instance/${instanceID}/move`), { path: newPath })
			const item = new Item(response.data)
			commit('deleteItem', item)
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
			await Axios.post(generateUrl(`apps/inventory/item/${itemID}/link/${relation}`), { itemIDs })
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
			await Axios.post(generateUrl(`apps/inventory/item/${itemID}/unlink/${relation}`), { itemIDs })
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
			const response = await Axios.post(generateUrl(`apps/inventory/item/${item.id}/instance/add`), { instance })
			commit('addInstance', { item, instance: response.data })
		} catch {
			console.debug('Creating item instance failed.')
		}
	},
	async deleteInstance({ commit }, { item, instance }) {
		try {
			await Axios.delete(generateUrl(`apps/inventory/item/${item.id}/instance/${instance.id}/delete`))
			commit('deleteInstance', { item, instance })
		} catch {
			console.debug('Deleting item instance failed.')
		}
	},
	async editInstance({ commit }, { item, instance }) {
		try {
			const response = await Axios.patch(generateUrl(`apps/inventory/item/${item.id}/instance/${instance.id}/edit`), { instance })
			commit('editInstance', { item, instance: response.data })
		} catch {
			console.debug('Editing item instance failed.')
		}
	},
	async addUuid({ commit }, { item, instance, uuid }) {
		try {
			const response = await Axios.put(generateUrl(`apps/inventory/item/${item.id}/instance/${instance.id}/uuid/${uuid}`))
			commit('addUuid', { instance, uuid: response.data })
		} catch {
			console.debug('Saving uuid failed.')
		}
	},
	async deleteUuid({ commit }, { item, instance, uuid }) {
		try {
			await Axios.delete(generateUrl(`apps/inventory/item/${item.id}/instance/${instance.id}/uuid/${uuid}`))
			commit('deleteUuid', { instance, uuid })
		} catch {
			console.debug('Uuid deletion failed.')
		}
	},

	async search({ commit, state }, searchString) {
		try {
			commit('setSearchResults', [])
			state.searching = true
			const response = await Axios.post(generateUrl('apps/inventory/search'), { searchString })
			const items = response.data.items.map(item => {
				return new Item(item)
			})
			const folders = response.data.folders.map(folder => {
				return new Folder(folder)
			})
			state.searching = false
			commit('setSearchResults', folders.concat(items))
		} catch {
			console.debug('Searching on the server failed.')
		}
	},

	async searchByUUID({ commit }, searchString) {
		try {
			const response = await Axios.post(generateUrl('apps/inventory/search'), { searchString })
			const items = response.data.items.map(item => {
				return new Item(item)
			})
			const places = response.data.places.map(place => {
				return new Place(place)
			})
			const folders = response.data.folders.map(folder => {
				return new Folder(folder)
			})
			return items.concat(places, folders)
		} catch {
			return []
		}
	},
}

export default { state, getters, mutations, actions }
