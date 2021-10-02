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

import Place from '../models/place'

import Axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const state = {
	places: {},
	loading: false,
}

const getters = {
	/**
	 * Returns all places
	 *
	 * @param {object} state The store data
	 * @return {Array} The places
	 */
	getPlacesByPlace: (state) => Object.values(state.places),

	/**
	 * Returns whether we currently load places from the server
	 *
	 * @param {object} state The store data
	 * @return {boolean} Are we loading places
	 */
	loadingPlaces: (state) => {
		return state.loading
	},
}

const mutations = {
	/**
	 * Sets the places
	 *
	 * @param {object} state Default state
	 * @param {object} payload The places object
	 */
	setPlaces(state, payload) {
		state.places = payload.places
	},

	/**
	 * Adds a place
	 *
	 * @param {object} state Default state
	 * @param {object} payload The places object
	 */
	addPlace(state, payload) {
		state.places.push(payload.place)
	},

	/**
	 * Deletes a place
	 *
	 * @param {object} state Default state
	 * @param {object} data Destructuring object
	 * @param {object} data.place The places object
	 */
	deletePlace(state, { place }) {
		// Find index of place to update
		const index = state.places.findIndex(f => f.id === place.id)
		// Replace place with new data
		Vue.delete(state.places, index)
	},

	/**
	 * Updates a place
	 *
	 * @param {object} state Default state
	 * @param {object} data Destructuring object
	 * @param {object} data.newPlace The places object
	 */
	updatePlace(state, { newPlace }) {
		// Find index of place to update
		const index = state.places.findIndex(place => place.id === newPlace.id)
		// Replace place with new data
		Vue.set(state.places, index, newPlace)
	},
}

const actions = {
	/**
	 * Requests all places for a given path
	 *
	 * @param {object} context The store object
	 * @param {object} context.commit The store mutations
	 * @param {object} context.state The store state
	 * @param {string} path The path to look at
	 * @return {Promise}
	 */
	async getPlacesByPlace({ commit, state }, path) {
		state.loading = true
		try {
			commit('setPlaces', { places: [] })
			const response = await Axios.post(generateUrl('apps/inventory/places'), { path })
			const places = response.data.map(payload => {
				return new Place(payload)
			})
			commit('setPlaces', { places })
		} catch {
			console.debug('Could not load the places.')
		}
		state.loading = false
	},

	async createPlace(context, { name, path }) {
		try {
			const response = await Axios.post(generateUrl('apps/inventory/places/add'), { name, path })
			const place = new Place(response.data)
			context.commit('addPlace', { place })
		} catch {
			console.debug('Could not create the place.')
		}
	},

	async movePlace({ commit }, { placeID, newPath }) {
		try {
			const response = await Axios.patch(generateUrl(`apps/inventory/places/${placeID}/move`), { path: newPath })
			commit('deletePlace', { place: new Place(response.data) })
		} catch {
			console.debug('Could not move the place.')
		}
	},

	async deletePlace(context, placeID) {
		try {
			const response = await Axios.delete(generateUrl(`apps/inventory/places/${placeID}/delete`))
			context.commit('deletePlace', { place: new Place(response.data) })
		} catch {
			console.debug('Could not delete the place.')
		}
	},

	async renamePlace(context, { placeID, newName }) {
		try {
			const response = await Axios.patch(generateUrl(`apps/inventory/places/${placeID}/rename`), { newName })
			context.commit('updatePlace', { newPlace: new Place(response.data) })
		} catch {
			console.debug('Could not rename the place.')
		}
	},
}

export default { state, getters, mutations, actions }
