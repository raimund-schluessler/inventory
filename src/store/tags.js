/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 *
 * @copyright 2023 Raimund Schlüßler <raimund.schluessler@mailbox.org>
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

import Tag from '../models/tag.js'

import Axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

const state = {
	tags: {},
	loading: false,
	runningTagsRequests: 0,
}

const getters = {
	/**
	 * Returns all tags
	 *
	 * @param {object} state The store data
	 * @return {Array} The tags
	 */
	getTags: (state) => Object.values(state.tags),

	/**
	 * Returns all tags sorted alphabetically
	 *
	 * @param {object} state The store data
	 * @return {Array} The tags sorted
	 */
	getSortedTags: (state) => {
		return [...Object.values(state.tags)].sort((tagA, tagB) => {
			return tagA.name.toLowerCase().localeCompare(tagB.name.toLowerCase())
		})
	},

	/**
	 * Returns whether we currently load tags from the server
	 *
	 * @param {object} state The store data
	 * @return {boolean} Are we loading tags
	 */
	loadingTags: (state) => {
		return state.runningTagsRequests > 0
	},
}

const mutations = {
	/**
	 * Sets the tags
	 *
	 * @param {object} state Default state
	 * @param {object} payload The tags object
	 */
	setTags(state, payload) {
		state.tags = payload.tags
	},

	/**
	 * Set the loading state of the tags
	 *
	 * @param {object} state Default state
	 * @param {boolean} loading Whether we load tags
	 */
	setLoadingTags(state, loading) {
		if (loading) {
			state.runningTagsRequests++
		} else {
			state.runningTagsRequests--
		}
	},
}

const actions = {
	/**
	 * Requests all tags
	 *
	 * @param {object} context The store object
	 * @param {object} context.commit The store mutations
	 * @param {object} context.state The store state
	 * @param {object} signal Additional object
	 * @param {object} signal.signal The signal to possibly cancel the request
	 * @return {Promise}
	 */
	async getTags({ commit, state }, { signal = null }) {
		commit('setLoadingTags', true)
		try {
			commit('setTags', { tags: [] })
			const response = await Axios.post(generateUrl('apps/inventory/tags'), {}, { signal })
			const tags = response.data.map(payload => {
				return new Tag(payload)
			})
			commit('setTags', { tags })
		} catch {
			console.debug('Could not load the tags.')
		}
		commit('setLoadingTags', false)
	},
}

export default { state, getters, mutations, actions }
