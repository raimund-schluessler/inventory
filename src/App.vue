<!--
Nextcloud - Inventory

@author Raimund Schlüßler
@copyright 2020 Raimund Schlüßler <raimund.schluessler@mailbox.org>

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
License as published by the Free Software Foundation; either
version 3 of the License, or any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU AFFERO GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU Affero General Public
License along with this library.  If not, see <http://www.gnu.org/licenses/>.

-->

<template>
	<Content app-name="inventory">
		<AppNavigation>
			<template #list>
				<AppNavigationItem v-for="view in views"
					:key="view.id"
					:to="`/${view.id}/`"
					:title="view.name">
					<template #icon>
						<component :is="view.icon"
							:size="20" />
					</template>
				</AppNavigationItem>
			</template>
			<template #footer>
				<AppNavigationSettings />
			</template>
		</AppNavigation>

		<AppContent>
			<RouterView />
		</AppContent>

		<RouterView name="sidebar" />
	</Content>
</template>

<script>
import AppNavigationSettings from './components/AppNavigation/AppNavigationSettings.vue'

import { subscribe, unsubscribe } from '@nextcloud/event-bus'
import { translate as t } from '@nextcloud/l10n'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import Content from '@nextcloud/vue/dist/Components/Content'

import Folder from 'vue-material-design-icons/Folder'
import MapMarker from 'vue-material-design-icons/MapMarker'
import Tag from 'vue-material-design-icons/Tag'

export default {
	name: 'App',
	components: {
		AppNavigation,
		AppNavigationItem,
		AppContent,
		Content,
		AppNavigationSettings,
		Folder,
		MapMarker,
		Tag,
	},
	data() {
		return {
			active: 'folders',
			searchString: '',
			views: [
				{
					name: t('inventory', 'Folders'),
					id: 'folders',
					icon: 'Folder',
				},
				{
					name: t('inventory', 'Places'),
					id: 'places',
					icon: 'MapMarker',
				},
				{
					name: t('inventory', 'Tags'),
					id: 'tags',
					icon: 'Tag',
				},
			],
		}
	},
	mounted() {
		// Hook to new global event for unified search
		subscribe('nextcloud:unified-search.search', this.filter)
		subscribe('nextcloud:unified-search.reset', this.cleanFilter)
	},
	beforeMount() {
		this.$store.dispatch('loadSettings')
	},
	beforeDestroy() {
		unsubscribe('nextcloud:unified-search.search', this.filter)
		unsubscribe('nextcloud:unified-search.reset', this.cleanFilter)
	},
	methods: {
		filter({ query }) {
			this.searchString = query
			if (!query) {
				this.$store.commit('setSearchResults', [])
			}
		},
		cleanFilter() {
			this.searchString = ''
			this.$store.commit('setSearchResults', [])
		},
	},
}
</script>

<style lang="scss">
// Hack for https://github.com/nextcloud/nextcloud-vue/issues/1384
body {
	min-height: 100%;
	height: auto;
}

// Prevent iOS safari from zooming in when focusing input
@media only screen and (max-width: 800px) {
	input {
		font-size: 16px !important;
	}
}
.folder-icon {
	color: var(--color-primary);
}
#controls {
	padding-left: 48px;
	padding-top: 4px;
	height: 48px;

	.breadcrumb {
		width: calc(100% - 44px);
	}
}
.error input[type='text'] {
	border: 1px solid red !important;
}
.over {
	background-color: var(--color-primary-light);
}
.dragged {
	opacity: .4;
}
</style>
