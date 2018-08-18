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
import { mapState } from 'vuex';
import VueRouter from 'vue-router';

import router from './components/TheRouter.js';
import store from './store';

Vue.prototype.OC = OC;
Vue.prototype.t = t;
Vue.prototype.n = n;

export class App {
	start() {
		OCA.Inventory.App.Vue = new Vue({
			el: '.app-inventory',
			router: router,
			store: store,
			data: {
				active: 'items',
				searchString: '',
				snap: false,
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
				},
				cleanSearch() {
					this.searchString = '';
				},
				toggleSnapperOnButton() {
					this.snap = !this.snap;
					this.showHideSnapper();
				},
				showHideSnapper() {
					if (this.snap) {
						$('#body-user').addClass('snapjs-left');
						$('#app-content').css("transition", "all 0.3s ease 0s");
						$('#app-content').css("transform", "translate3d(300px, 0px, 0px)");
						setTimeout(function(){$('#app-content').css("transition", "none");}, 300);
					} else {
						$('#body-user').removeClass('snapjs-left');
						$('#app-content').css("transition", "all 0.3s ease 0s");
						$('#app-content').css("transform", "none");
						setTimeout(function(){$('#app-content').css("transition", "none");}, 300);
					}
				},
				documentClick: function (e) {
					let el = $('#app-navigation');
					let target = e.target;
					var toggleButton = $("#app-navigation-toggle");
					if (( el !== target) && !$.contains(el[0], target) && (toggleButton[0] !== target)) {
						this.snap = false;
						this.showHideSnapper();
					}
				}
			},
			mounted: function() {
				// re-bind app-navigation-toggle events since Vue removed them
				$('#app-navigation-toggle').click(function() {
					OCA.Inventory.App.Vue.toggleSnapperOnButton();
				});
				$('#app-navigation-toggle').keypress(function(e) {
					if(e.which === 13) {
						OCA.Inventory.App.Vue.toggleSnapperOnButton();
					}
				});
				document.addEventListener('click', this.documentClick);
			},
			computed: mapState({
				showModal: state => state.showModal
			})
		});

		store.dispatch('loadItems');
	}
}
