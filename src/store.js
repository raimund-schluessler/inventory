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
import Axios from 'axios'
Axios.defaults.headers.common.requesttoken = OC.requestToken

Vue.use(Vuex)

export default new Vuex.Store({
	state: {
		showModal: false,
		items: [],
		item: [],
		subItems: [],
		parentItems: [],
		relatedItems: [],
		itemCandidates: []
	},
	mutations: {
		setItems(state, payload) {
			state.items = payload.items
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
		setShowModal(state, show) {
			state.showModal = show
		}
	},
	actions: {
		async loadItems({ commit }) {
			const response = await Axios.get(OC.generateUrl('apps/inventory/items'))
			commit('setItems', { items: response.data.data.items })
		},
		async loadItem({ commit }, itemID) {
			const response = await Axios.get(OC.generateUrl('apps/inventory/item/' + itemID))
			commit('setItem', { item: response.data.data.item })
		},
		async loadSubItems({ commit }, itemID) {
			const response = await Axios.get(OC.generateUrl('apps/inventory/item/' + itemID + '/sub'))
			commit('setSubItems', { subItems: response.data.data.items })
		},
		async loadParentItems({ commit }, itemID) {
			const response = await Axios.get(OC.generateUrl('apps/inventory/item/' + itemID + '/parent'))
			commit('setParentItems', { parentItems: response.data.data.items })
		},
		async loadRelatedItems({ commit }, itemID) {
			const response = await Axios.get(OC.generateUrl('apps/inventory/item/' + itemID + '/related'))
			commit('setRelatedItems', { relatedItems: response.data.data.items })
		},
		async loadItemCandidates({ commit }, parameters) {
			const response = await Axios.get(OC.generateUrl('apps/inventory/item/' + parameters.itemID + '/candidates/' + parameters.relationType))
			commit('setItemCandidates', { itemCandidates: response.data.data.items })
		}
	}
})
