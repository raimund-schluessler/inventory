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
	<NcContent app-name="inventory">
		<NcAppNavigation>
			<template #list>
				<NcAppNavigationItem v-for="view in views"
					:key="view.id"
					:to="`/${view.id}/`"
					:name="view.name">
					<template #icon>
						<component :is="view.icon"
							:size="20" />
					</template>
				</NcAppNavigationItem>
			</template>
			<template #footer>
				<AppNavigationSettings />
			</template>
		</NcAppNavigation>

		<NcAppContent>
			<RouterView />
		</NcAppContent>

		<RouterView name="AppSidebar" />
	</NcContent>
</template>

<script>
import AppNavigationSettings from './components/AppNavigation/AppNavigationSettings.vue'

import { subscribe, unsubscribe } from '@nextcloud/event-bus'
import { translate as t } from '@nextcloud/l10n'
import {
	NcAppContent,
	NcAppNavigation,
	NcAppNavigationItem,
	NcContent,
} from '@nextcloud/vue'

import Folder from 'vue-material-design-icons/Folder.vue'
import MapMarker from 'vue-material-design-icons/MapMarker.vue'
import TagMultiple from 'vue-material-design-icons/TagMultiple.vue'

export default {
	name: 'App',
	components: {
		NcAppNavigation,
		NcAppNavigationItem,
		NcAppContent,
		NcContent,
		AppNavigationSettings,
		Folder,
		MapMarker,
		TagMultiple,
	},
	data() {
		return {
			active: 'folders',
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
					icon: 'TagMultiple',
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
	beforeUnmount() {
		unsubscribe('nextcloud:unified-search.search', this.filter)
		unsubscribe('nextcloud:unified-search.reset', this.cleanFilter)
	},
	methods: {
		filter({ query }) {
			this.$store.commit('setSearchString', query)
			if (!query) {
				this.$store.commit('setSearchResults', [])
			}
		},
		cleanFilter() {
			this.$store.commit('setSearchString', '')
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
	// Adjustment necessary to use nc/vue@6 with NC25
	position: initial;
}

// Prevent iOS safari from zooming in when focusing input
@media only screen and (max-width: 800px) {
	input {
		font-size: 16px !important;
	}
}
// Adjust app-navigation-toggle position
.app-navigation-toggle-wrapper {
	@media only screen and (max-width: 500px) {
		right: 0 !important;
		top: 0 !important;
	}
}
#controls {
	display: flex;
	padding-left: 52px;
	padding-top: 8px;
	height: 52px;
	position: sticky;
	top: 0;
	background-color: var(--color-main-background-translucent);
	z-index: 1000;

	@media only screen and (max-width: 500px) {
		padding-left: 44px;
		padding-top: 0;
		height: 44px;
	}

	.breadcrumb {
		height: 44px;
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

<style lang="scss" scoped>
// Adjustment necessary to use nc/vue@6 with NC25
#content-vue {
	max-height: 100vh;
}
.app-content {
	// Adjustment necessary to use nc/vue@6 with NC25
	overflow-y: scroll;
}
</style>
