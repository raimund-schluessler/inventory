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

import Vue from 'vue'
import VueRouter from 'vue-router'

import TheItemsOverview from './TheItemsOverview.vue'
import ItemsNew from './TheItemsCreator.vue'
import TheItemDetails from './TheItemDetails.vue'

import Categories from './Categories.vue'

const routes = [
	// using
	// { path: '/folders', component: TheItemsOverview, alias: '/' },
	// instead of
	{ path: '/', redirect: '/folders/' },
	{
		path: '/folders/:path(.*)?/item-:id(\\d+)',
		component: TheItemDetails,
		props: (route) => ({ path: route.params.path, id: route.params.id, collection: 'folders' }),
	},
	{
		path: '/folders/:path(.*)?/item-:id(\\d+)/instance-:instanceId(\\d+)',
		component: TheItemDetails,
		props: (route) => ({ path: route.params.path, id: route.params.id, instanceId: route.params.instanceId, collection: 'folders' }),
	},
	{ path: '/folders/:path(.*)?/additems', component: ItemsNew },
	{ name: 'folders', path: '/folders/:path(.*)', component: TheItemsOverview, props: { collection: 'folders' } },
	// would also be an option, but it currently does not work
	// reliably with router-link due to
	// https://github.com/vuejs/vue-router/issues/419

	{
		path: '/places/:path(.*)?/item-:id(\\d+)',
		component: TheItemDetails,
		props: (route) => ({ path: route.params.path, id: route.params.id, collection: 'places' }),
	},
	{
		path: '/places/:path(.*)?/item-:id(\\d+)/instance-:instanceId(\\d+)',
		component: TheItemDetails,
		props: (route) => ({ path: route.params.path, id: route.params.id, instanceId: route.params.instanceId, collection: 'places' }),
	},
	{ name: 'places', path: '/places/:path(.*)', component: TheItemsOverview, props: { collection: 'places' } },

	{ path: '/categories', component: Categories },
]

Vue.use(VueRouter)

export default new VueRouter({
	linkActiveClass: 'active',
	routes, // short for `routes: routes`
})
