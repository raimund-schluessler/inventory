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

import Vue from 'vue';
import Vuex from 'vuex';
import VueRouter from 'vue-router';

import router from './components/TheRouter.js';
import store from './store';

export class App {
	start() {
		Vue.mixin({
			methods: {
				t: function (app, string) {
					return t(app, string);
				}
			}
		});

		OCA.Inventory.App.Vue = new Vue({
			el: '#app',
			router: router,
			store: store,
			data: {
				active: 'items',
				searchString: '',
				views: [
					{
						name: t('inventory', 'Items'),
						id: "items"
					},
					{
						name: t('inventory', 'Places'),
						id: "places"
					},
					{
						name: t('inventory', 'Categories'),
						id: "categories"
					}
				]
			},
			methods: {
				filter(query) {
					this.searchString = query;
				}
			}
		});

		store.dispatch('loadItems');
	}
}
