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
'use strict';

if (!OCA.Inventory) {
	/**
	 * @namespace OCA.Inventory
	 */
	OCA.Inventory = {};
}

import App from './app.vue';

import Vue from 'vue';
import Vuex from 'vuex';
import VueRouter from 'vue-router';

import router from './components/TheRouter.js';
import store from './store';

Vue.prototype.t = t;
Vue.prototype.n = n;
Vue.prototype.OC = OC;
Vue.prototype.OCA = OCA;

OCA.Inventory.App = new Vue({
	el: '.app-inventory',
	router,
	store,
	data: function () {
		return {
			searchString: ''
		};
	},
	mounted: function() {
		var version = OC.config.version.split('.');

		if (version[0] >= 14) {
			OC.Search = new OCA.Search(this.filter, this.cleanSearch);
		} else {
			OCA.Inventory.Search = {
				attach: function (search) {
					search.setFilter('inventory', this.filter);
				}
			};

			OC.Plugins.register('OCA.Search', OCA.Inventory.Search);
		}
	},
	beforeMount() {
		this.$store.dispatch('loadItems');
	},
	methods: {
		filter(query) {
			this.searchString = query;
		},
		cleanSearch() {
			this.searchString = '';
		}
	},
	render: h => h(App)
});
