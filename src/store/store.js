/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2017 Raimund Schlüßler <raimund.schluessler@mailbox.org>
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
import PQueue from 'p-queue'
import Status from '../models/status'
import Axios from 'axios'
Axios.defaults.headers.common.requesttoken = OC.requestToken

Vue.use(Vuex)

export default new Vuex.Store({
	state: {
		items: {},
		item: [],
		subItems: [],
		parentItems: [],
		relatedItems: [],
		itemCandidates: []
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

		setItem(state, payload) {
			state.item = payload.item
		},
		setSubItems(state, payload) {
			state.subItems = payload.subItems
		},
		setParentItems(state, payload) {
			state.parentItems = payload.parentItems
		},
		setRelatedItems(state, payload) {
			state.relatedItems = payload.relatedItems
		},
		setItemCandidates(state, payload) {
			state.itemCandidates = payload.itemCandidates
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
		 * Returns all items with the given id in the store
		 *
		 * @param {Object} state The store data
		 * @param {Object} getters The store getters
		 * @param {Object} rootState The store root state
		 * @param {Array} itemIDs Array containing the itemIDs
		 * @returns {Array} All items in store
		 */
		getItemsByIDs: (state, getters, rootState) => (itemIDs) => {
			if (!Array.isArray(itemIDs)) {
				return []
			}
			return getters.getAllItems
				.filter(item => {
					return itemIDs.includes(item.id)
				})
		},
	},

	actions: {

		async loadItems({ commit }) {
			const response = await Axios.get(OC.generateUrl('apps/inventory/items'))
			const items = response.data.data.items.map(payload => {
				const item = new Item(payload)
				return item
			})
			commit('addItems', items)
		},

		async createItems(context, items) {
			const queue = new PQueue({ concurrency: 5 })
			items.forEach(async(item) => {
				await queue.add(async() => {
					try {
						const response = await Axios.post(OC.generateUrl('apps/inventory/item/add'), { item: item.response })
						if (response.data.status === 'success') {
							Vue.set(item, 'response', response.data.data)
							item.updateItem()
							item.syncstatus = new Status('created', 'Successfully created the item.') // eslint-disable-line require-atomic-updates
							context.commit('addItem', item)
						} else {
							throw Error
						}
					} catch {
						item.syncstatus = new Status('error', 'Item creation failed.') // eslint-disable-line require-atomic-updates
					}
				})
			})
		},

		async loadItem({ commit }, itemID) {
			const response = await Axios.get(OC.generateUrl('apps/inventory/item/' + itemID))
			const item = new Item(response.data.data.item)
			commit('setItem', { item })
		},
		async loadSubItems({ commit }, itemID) {
			const response = await Axios.get(OC.generateUrl('apps/inventory/item/' + itemID + '/sub'))
			const subItems = response.data.data.items.map(payload => {
				const item = new Item(payload)
				return item
			})
			commit('setSubItems', { subItems })
		},
		async loadParentItems({ commit }, itemID) {
			const response = await Axios.get(OC.generateUrl('apps/inventory/item/' + itemID + '/parent'))
			const parentItems = response.data.data.items.map(payload => {
				const item = new Item(payload)
				return item
			})
			commit('setParentItems', { parentItems })
		},
		async loadRelatedItems({ commit }, itemID) {
			const response = await Axios.get(OC.generateUrl('apps/inventory/item/' + itemID + '/related'))
			const relatedItems = response.data.data.items.map(payload => {
				const item = new Item(payload)
				return item
			})
			commit('setRelatedItems', { relatedItems })
		},
		async loadItemCandidates({ commit }, parameters) {
			const response = await Axios.get(OC.generateUrl('apps/inventory/item/' + parameters.itemID + '/candidates/' + parameters.relationType))
			const itemCandidates = response.data.data.items.map(payload => {
				const item = new Item(payload)
				return item
			})
			commit('setItemCandidates', { itemCandidates })
		},
		async deleteItem({ commit }, item) {
			const response = await Axios.delete(OC.generateUrl('apps/inventory/item/' + item.id + '/delete'))
			if (response.data.status === 'success') {
				commit('deleteItem', item)
			}
		},
		async deleteItems(context, itemIDs) {
			const queue = new PQueue({ concurrency: 5 })
			const items = context.getters.getItemsByIDs(itemIDs)
			items.forEach(async(item) => {
				await queue.add(() => context.dispatch('deleteItem', item))
			})
		},
	}
})
