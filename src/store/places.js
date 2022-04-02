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

import Place from '../models/place.js'
import router from '../router.js'
import { encodePath } from '../utils/encodePath.js'

import Axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

const state = {
	places: {},
	place: null,
	runningPlacesRequests: 0,
	loadingPlace: false,
}

const getters = {

	getPlace: (state) => state.place,

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
		return state.runningPlacesRequests > 0
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
	 * Set the loading state of the places
	 *
	 * @param {object} state Default state
	 * @param {boolean} loading Whether we load places
	 */
	setLoadingPlaces(state, loading) {
		if (loading) {
			state.runningPlacesRequests++
		} else {
			state.runningPlacesRequests--
		}
	},

	setPlace(state, payload) {
		state.place = payload.place
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
		delete state.places[index]
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
		state.places[index] = newPlace
	},

	/**
	 * Renames a place
	 *
	 * @param {object} state Default state
	 * @param {object} data Destructuring object
	 * @param {object} data.place The place
	 * @param {object} data.newName The new name
	 * @param {object} data.newPath The new path
	 */
	renamePlace(state, { place, newName, newPath }) {
		place.name = newName
		place.path = newPath
	},

	/**
	 * Set the description of a place
	 *
	 * @param {object} state Default state
	 * @param {object} data Destructuring object
	 * @param {object} data.place The place
	 * @param {object} data.description The new description
	 */
	setPlaceDescription(state, { place, description }) {
		place.description = description
	},

	/**
	 * Adds a UUID to a place
	 *
	 * @param {object} state Default state
	 * @param {object} data Destructuring object
	 * @param {Array} data.place The place
	 * @param {Array} data.uuid The UUID
	 */
	addUuidToPlace(state, { place, uuid }) {
		place.uuids.push(uuid)
	},

	/**
	 * Deletes a UUID from a place
	 *
	 * @param {object} state Default state
	 * @param {object} data Destructuring object
	 * @param {Array} data.place The place
	 * @param {Array} data.uuid The UUID
	 */
	deleteUuidFromPlace(state, { place, uuid }) {
		place.uuids = place.uuids.filter((localUuid) => {
			return localUuid.uuid !== uuid
		})
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
	async getPlacesByPlace({ commit, state }, { path, signal = null }) {
		commit('setLoadingPlaces', true)
		try {
			commit('setPlaces', { places: [] })
			const response = await Axios.post(generateUrl('apps/inventory/places'), { path }, { signal })
			const places = response.data.map(payload => {
				return new Place(payload)
			})
			commit('setPlaces', { places })
		} catch (error) {
			console.debug('Could not load the places.')
			throw error
		} finally {
			commit('setLoadingPlaces', false)
		}
	},

	async getPlaceByPath({ commit }, path) {
		state.loadingPlace = true
		try {
			const response = await Axios.post(generateUrl('apps/inventory/place'), { path })
			const place = new Place(response.data)
			commit('setPlace', { place })
		} catch {
			commit('setPlace', { place: null })
		}
		state.loadingPlace = false
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

	async renamePlace(context, { place, newName }) {
		try {
			const response = await Axios.patch(generateUrl(`apps/inventory/places/${place.id}/rename`), { newName })
			const newPlace = new Place(response.data)
			// If the place is loaded, rename it
			if (context.state.place?.path === place.path) {
				context.commit('renamePlace', { place: context.state.place, newName: newPlace.name, newPath: newPlace.path })
			}
			context.commit('updatePlace', { newPlace })

			// If the place is currently open, we have to update the current route
			if (router.currentRoute.value.name === 'placeDetails') {
				router.push(`/places/${encodePath(newPlace.path)}/&details`)
			} else if (['placeChildDetails', 'placeRootDetails'].includes(router.currentRoute.value.name)) {
				router.push(`/places/${encodePath(newPlace.path.replace(newPlace.name, ''))}&details/${encodePath(newPlace.path)}`)
			}
		} catch {
			console.debug('Could not rename the place.')
		}
	},

	async setPlaceDescription(context, { place, description }) {
		try {
			await Axios.post(generateUrl(`apps/inventory/place/${place.id}/description`), { description })
			context.commit('setPlaceDescription', { place, description })
		} catch {
			console.debug('Could not set the description of the place.')
		}
	},

	async addUuidToPlace({ commit }, { place, uuid }) {
		try {
			const newUuid = await Axios.post(generateUrl(`apps/inventory/place/${place.id}/uuid/add`), { uuid })
			commit('addUuidToPlace', { place, uuid: newUuid.data })
		} catch {
			console.debug('Saving uuid failed.')
		}
	},

	async deleteUuidFromPlace({ commit }, { place, uuid }) {
		try {
			await Axios.post(generateUrl(`apps/inventory/place/${place.id}/uuid/delete`), { uuid })
			commit('deleteUuidFromPlace', { place, uuid })
		} catch {
			console.debug('Uuid deletion failed.')
		}
	},
}

export default { state, getters, mutations, actions }
