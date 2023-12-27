/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 *
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

import App from './App.vue'
import router from './router.js'
import store from './store/store.js'

import Vue from 'vue'
import { sync } from 'vuex-router-sync'

sync(store, router)

if (!OCA.Inventory) {
	/**
	 * @namespace OCA.Inventory
	 */
	OCA.Inventory = {}
}

Vue.prototype.$OC = OC
Vue.prototype.$OCA = OCA

const Inventory = new Vue({
	el: '.app-inventory',
	router,
	store,
	render: h => h(App),
})

OCA.Inventory.App = Inventory
