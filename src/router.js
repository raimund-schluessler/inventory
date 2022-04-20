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

import ItemsNew from './views/AppContent/ItemsCreator.vue'
import ItemDetails from './views/AppContent/ItemDetails.vue'
import ItemsOverview from './views/AppContent/ItemsOverview.vue'
import Tags from './views/AppContent/Tags.vue'
import AppSidebar from './views/AppSidebar.vue'

import Vue from 'vue'
import VueRouter from 'vue-router'

const routes = [
	// using
	// { path: '/folders', component: ItemsOverview, alias: '/' },
	// instead of
	{ path: '/', redirect: '/folders/' },
	{
		path: '/folders/:path(.*)?/item-:id(\\d+)',
		component: ItemDetails,
		props: (route) => ({ path: route.params.path, id: route.params.id, collection: 'folders' }),
	},
	{
		path: '/folders/:path(.*)?/item-:id(\\d+)/instance-:instanceId(\\d+)',
		component: ItemDetails,
		props: (route) => ({ path: route.params.path, id: route.params.id, instanceId: route.params.instanceId, collection: 'folders' }),
	},
	{
		path: '/folders/:path(.*)?/&additems',
		component: ItemsNew,
		props: (route) => ({ path: route.params.path, collection: 'folders' }),
	},
	{
		name: 'folders',
		path: '/folders/:path(.*)',
		component: ItemsOverview,
		props: (route) => ({ path: route.params.path, collection: 'folders' }),
	},
	// would also be an option, but it currently does not work
	// reliably with router-link due to
	// https://github.com/vuejs/vue-router/issues/419

	{
		path: '/places/:path(.*)?/item-:id(\\d+)',
		component: ItemDetails,
		props: (route) => ({ path: route.params.path, id: route.params.id, collection: 'places' }),
	},
	{
		path: '/places/:path(.*)?/item-:id(\\d+)/instance-:instanceId(\\d+)',
		component: ItemDetails,
		props: (route) => ({ path: route.params.path, id: route.params.id, instanceId: route.params.instanceId, collection: 'places' }),
	},
	{
		path: '/places/:path(.*)?/&additems',
		component: ItemsNew,
		props: (route) => ({ path: route.params.path, collection: 'places' }),
	},
	{
		name: 'placesDetails',
		path: '/places/:path(.*)?/&details/:folder(.*)?',
		components: { default: ItemsOverview, sidebar: AppSidebar },
		props: {
			default: (route) => ({ path: route.params.path, collection: 'places' }),
			sidebar: (route) => ({ path: route.params.path, collection: 'places', folder: route.params.folder }),
		},
	},
	{
		name: 'places',
		path: '/places/:path(.*)',
		component: ItemsOverview,
		props: (route) => ({ path: route.params.path, collection: 'places' }),
	},

	{ path: '/tags', component: Tags },
]

Vue.use(VueRouter)

export default new VueRouter({
	linkActiveClass: 'active',
	routes,
})
