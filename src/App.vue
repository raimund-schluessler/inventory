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
				<AppNavigationItem
					v-for="view in views"
					:key="view.id"
					:to="`/${view.id}/`"
					:title="view.name">
					<template #icon>
						<component
							:is="view.icon"
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
import AppNavigationSettings from './components/AppNavigation/AppNavigationSettings'

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
}
</script>
