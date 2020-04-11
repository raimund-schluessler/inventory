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
	<div id="content" class="app-inventory">
		<AppNavigation>
			<ul>
				<AppNavigationItem
					v-for="view in views"
					:key="view.id"
					:to="`/${view.id}/`"
					:icon="`icon-${view.icon}`"
					:title="view.name" />
			</ul>
			<AppNavigationSettings>
				<TheSettings />
			</AppNavigationSettings>
		</AppNavigation>

		<AppContent>
			<RouterView />
		</AppContent>
	</div>
</template>

<script>
import TheSettings from './components/AppNavigation/TheSettings'

import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationSettings from '@nextcloud/vue/dist/Components/AppNavigationSettings'

export default {
	name: 'App',
	components: {
		AppNavigation,
		AppNavigationItem,
		AppNavigationSettings,
		AppContent,
		TheSettings,
	},
	data: function() {
		return {
			active: 'folders',
			views: [
				{
					name: this.t('inventory', 'Folders'),
					id: 'folders',
					icon: 'folders',
				},
				{
					name: this.t('inventory', 'Places'),
					id: 'places',
					icon: 'places',
				},
				{
					name: this.t('inventory', 'Tags'),
					id: 'tags',
					icon: 'tags',
				},
			],
		}
	},
}
</script>
