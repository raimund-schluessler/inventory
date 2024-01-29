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

import ItemsNew from './views/AppContent/ItemsCreator.vue'
import ItemDetails from './views/AppContent/ItemDetails.vue'
import ItemsOverview from './views/AppContent/ItemsOverview.vue'
import Tags from './views/AppContent/Tags.vue'
import AppSidebar from './views/AppSidebar.vue'

import { getRootUrl, generateUrl } from '@nextcloud/router'

import { h } from 'vue'
import { createWebHistory, createRouter, RouterView } from 'vue-router'

const webRootWithIndexPHP = getRootUrl() + '/index.php'
const doesURLContainIndexPHP = window.location.pathname.startsWith(webRootWithIndexPHP)
const base = generateUrl('apps/inventory', {}, {
	noRewrite: doesURLContainIndexPHP,
})

const routes = [
	{ path: '/', redirect: '/folders/' },
	{
		path: '/folders',
		component: { render: () => h(RouterView) },
		children: [
			{
				path: '/folders',
				component: ItemsOverview,
				props: (route) => ({ path: route.params.path, collection: 'folders' }),
			},
			{
				path: '/folders/',
				component: { render: () => h(RouterView) },
				children: [
					{
						path: '/folders/',
						component: ItemsOverview,
						props: (route) => ({ path: route.params.path, collection: 'folders' }),
					},
					{
						path: '/folders/item-:id(\\d+)',
						component: ItemDetails,
						props: (route) => ({ path: route.params.path, id: route.params.id, collection: 'folders' }),
					},
					{
						path: '/folders/item-:id(\\d+)/instance-:instanceId(\\d+)',
						component: ItemDetails,
						props: (route) => ({ path: route.params.path, id: route.params.id, instanceId: route.params.instanceId, collection: 'folders' }),
					},
					{
						path: '/folders/&additems',
						component: ItemsNew,
						props: (route) => ({ path: route.params.path, collection: 'folders' }),
					},
					{
						path: '/folders/:path(.*?)',
						component: { render: () => h(RouterView) },
						children: [
							{
								path: '/folders/:path(.*?)',
								component: ItemsOverview,
								props: (route) => ({ path: route.params.path, collection: 'folders' }),
							},
							{
								path: '/folders/:path(.*?)/item-:id(\\d+)',
								component: ItemDetails,
								props: (route) => ({ path: route.params.path, id: route.params.id, collection: 'folders' }),
							},
							{
								path: '/folders/:path(.*?)/item-:id(\\d+)/instance-:instanceId(\\d+)',
								component: ItemDetails,
								props: (route) => ({ path: route.params.path, id: route.params.id, instanceId: route.params.instanceId, collection: 'folders' }),
							},
							{
								path: '/folders/:path(.*?)/&additems',
								component: ItemsNew,
								props: (route) => ({ path: route.params.path, collection: 'folders' }),
							},
						],
					},
				],
			},
		],
	},
	{
		path: '/places',
		components: {
			default: { render: () => h(RouterView, { name: 'default' }) },
			AppSidebar: { render: () => h(RouterView, { name: 'AppSidebar' }) },
		},
		children: [
			{
				path: '/places',
				component: ItemsOverview,
				props: (route) => ({ path: route.params.path, collection: 'places' }),
			},
			{
				path: '/places/',
				components: {
					default: { render: () => h(RouterView, { name: 'default' }) },
					AppSidebar: { render: () => h(RouterView, { name: 'AppSidebar' }) },
				},
				children: [
					{
						path: '/places/',
						component: ItemsOverview,
						props: (route) => ({ path: route.params.path, collection: 'places' }),
					},
					{
						path: '/places/item-:id(\\d+)',
						component: ItemDetails,
						props: (route) => ({ path: route.params.path, id: route.params.id, collection: 'places' }),
					},
					{
						path: '/places/item-:id(\\d+)/instance-:instanceId(\\d+)',
						component: ItemDetails,
						props: (route) => ({ path: route.params.path, id: route.params.id, instanceId: route.params.instanceId, collection: 'places' }),
					},
					{
						path: '/places/&details',
						components: { default: ItemsOverview, AppSidebar },
						props: {
							default: (route) => ({ path: route.params.path, collection: 'places' }),
							AppSidebar: (route) => ({ path: route.params.path, collection: 'places', folder: route.params.folder }),
						},
					},
					{
						path: '/places/&details/:folder(.*?)',
						components: { default: ItemsOverview, AppSidebar },
						name: 'placeRootDetails',
						props: {
							default: (route) => ({ path: route.params.path, collection: 'places' }),
							AppSidebar: (route) => ({ path: route.params.path, collection: 'places', folder: route.params.folder }),
						},
					},
					{
						path: '/places/&additems',
						component: ItemsNew,
						props: (route) => ({ path: route.params.path, collection: 'places' }),
					},
					{
						path: '/places/:path(.*?)',
						components: {
							default: { render: () => h(RouterView, { name: 'default' }) },
							AppSidebar: { render: () => h(RouterView, { name: 'AppSidebar' }) },
						},
						children: [
							{
								path: '/places/:path(.*?)',
								component: ItemsOverview,
								props: (route) => ({ path: route.params.path, collection: 'places' }),
							},
							{
								path: '/places/:path(.*?)/item-:id(\\d+)',
								component: ItemDetails,
								props: (route) => ({ path: route.params.path, id: route.params.id, collection: 'places' }),
							},
							{
								path: '/places/:path(.*?)/item-:id(\\d+)/instance-:instanceId(\\d+)',
								component: ItemDetails,
								props: (route) => ({ path: route.params.path, id: route.params.id, instanceId: route.params.instanceId, collection: 'places' }),
							},
							{
								path: '/places/:path(.*?)/&details',
								components: { default: ItemsOverview, AppSidebar },
								name: 'placeDetails',
								props: {
									default: (route) => ({ path: route.params.path, collection: 'places' }),
									AppSidebar: (route) => ({ path: route.params.path, collection: 'places', folder: route.params.folder }),
								},
							},
							{
								path: '/places/:path(.*?)/&details/:folder(.*?)',
								components: { default: ItemsOverview, AppSidebar },
								name: 'placeChildDetails',
								props: {
									default: (route) => ({ path: route.params.path, collection: 'places' }),
									AppSidebar: (route) => ({ path: route.params.path, collection: 'places', folder: route.params.folder }),
								},
							},
							{
								path: '/places/:path(.*?)/&additems',
								component: ItemsNew,
								props: (route) => ({ path: route.params.path, collection: 'places' }),
							},
						],
					},
				],
			},
		],
	},
	{
		path: '/tags',
		component: Tags,
		props: (route) => ({ path: route.params.path }),
		children: [
			{
				path: '/tags',
				component: Tags,
				props: (route) => ({ path: route.params.path }),
			},
			{
				path: '/tags/',
				component: Tags,
				props: (route) => ({ path: route.params.path }),
				children: [
					{
						path: '/tags/',
						component: Tags,
						props: (route) => ({ path: route.params.path }),
					},
					{
						path: '/tags/:path(.*?)',
						component: Tags,
						props: (route) => ({ path: route.params.path }),
						children: [
							{
								path: '/tags/:path(.*?)',
								component: Tags,
								props: (route) => ({ path: route.params.path }),
							},
						],
					},
				],
			},
		],
	},
]

const router = createRouter({
	history: createWebHistory(base),
	routes,
})

export default router
