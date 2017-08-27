/**
 * Nextcloud Inventory - v0.0.1
 *
 * Copyright (c) 2017 - Raimund Schlüßler <raimund.schluessler@mailbox.org>
 *
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING file
 *
 */


if (!OCA.Inventory) {
	/**
	 * @namespace OCA.Inventory
	 */
	OCA.Inventory = {};
}
/**
* @namespace
*/

$(document).ready(function () {

	const Items = { template: '<div>items</div>' }
	const Places = { template: '<div>places</div>' }
	const Categories = { template: '<div>categories</div>' }

	const routes = [
		// using
		// { path: '/items', component: Items, alias: '/' },
		// instead of
		{ path: '/', redirect: '/items' },
		{ path: '/items', component: Items},
		// would also be an option, but it currently does not work
		// reliably with router-link due to
		// https://github.com/vuejs/vue-router/issues/419
		{ path: '/places', component: Places },
		{ path: '/categories', component: Categories },
	]

	const router = new VueRouter({
		routes, // short for `routes: routes`
	})

	OCA.Inventory.App = new Vue({
		el: '#app',
		router,
		data: {
			active: 'items',
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
		}
	})
});
