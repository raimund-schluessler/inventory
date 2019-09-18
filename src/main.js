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

import App from './app'

import Vue from 'vue'

import router from './components/TheRouter'
import store from './store'
import { sync } from 'vuex-router-sync'

// CSP config for webpack dynamic chunk loading
// eslint-disable-next-line
__webpack_nonce__ = btoa(OC.requestToken)

// Correct the root of the app for chunk loading
// OC.linkTo matches the apps folders
// OC.generateUrl ensure the index.php (or not)
// We do not want the index.php since we're loading files
// eslint-disable-next-line
__webpack_public_path__ = OC.linkTo('inventory', 'js/')

sync(store, router)

if (!OCA.Inventory) {
	/**
	 * @namespace OCA.Inventory
	 */
	OCA.Inventory = {}
}

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA

OCA.Inventory.App = new Vue({
	el: '.app-inventory',
	router,
	store,
	data: function() {
		return {
			searchString: ''
		}
	},
	mounted: function() {
		OC.Search = new OCA.Search(this.filter, this.cleanSearch)
	},
	beforeMount() {
		this.$store.dispatch('loadItems')
	},
	methods: {
		filter(query) {
			this.searchString = query
		},
		cleanSearch() {
			this.searchString = ''
		}
	},
	render: h => h(App)
})
