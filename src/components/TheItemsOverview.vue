<!--
Nextcloud - Inventory

@author Raimund Schlüßler
@copyright 2017 Raimund Schlüßler <raimund.schluessler@mailbox.org>

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
	<div>
		<div id="controls">
			<Breadcrumbs :path="$route.params.path" />
			<Actions default-icon="icon-add" @close="addingFolder = false">
				<ActionRouter to="/folders/additems" icon="icon-add">
					{{ t('inventory', 'Add items') }}
				</ActionRouter>
				<ActionButton v-if="!addingFolder"
					icon="icon-folder" @click.prevent.stop="openFolderInput()"
				>
					{{ t('inventory', 'New Folder') }}
				</ActionButton>
				<ActionInput v-if="addingFolder"
					:class="{ 'error': folderNameError }"
					v-tooltip="{
						show: folderNameError,
						content: errorString,
						trigger: 'manual',
					}"
					icon="icon-folder"
					@submit="addFolder"
					@input="checkFoldername"
				>
					{{ t('inventory', 'New Folder') }}
				</ActionInput>
			</Actions>
		</div>
		<ItemsTable :items="items" :folders="folders" :loading="loading"
			:show-dropdown="true" :search-string="$root.searchString"
		/>
	</div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'
import ItemsTable from './ItemsTable.vue'
import Breadcrumbs from './Breadcrumbs.vue'
import { Actions } from '@nextcloud/vue/dist/Components/Actions'
import { ActionInput } from '@nextcloud/vue/dist/Components/ActionInput'
import { ActionButton } from '@nextcloud/vue/dist/Components/ActionButton'
import { ActionRouter } from '@nextcloud/vue/dist/Components/ActionRouter'

export default {
	components: {
		ItemsTable: ItemsTable,
		Breadcrumbs,
		Actions,
		ActionInput,
		ActionRouter,
		ActionButton,
	},
	data: function() {
		return {
			addingFolder: false,
			errorString: null,
			folderNameError: false,
		}
	},
	computed: {
		...mapGetters({
			items: 'getAllItems',
			folders: 'getFoldersByPath',
			loading: 'loadingItems',
		}),
	},
	created: function() {
		this.getFolders(this.$route.params.path)
		this.getItems(this.$route.params.path)
	},
	beforeRouteUpdate(to, from, next) {
		this.getFolders(to.params.path)
		this.getItems(to.params.path)
		next()
	},
	methods: {
		async getFolders(path) {
			await this.getFoldersByPath(path)
		},

		async getItems(path) {
			await this.getItemsByPath(path)
		},

		openFolderInput() {
			this.addingFolder = !this.addingFolder
		},

		addFolder(event) {
			const newName = event.target.querySelector('input[type=text]').value
		},

		checkFoldername(event) {
			const newName = event.target.value
			if (newName === '') {
				this.folderNameError = true
				this.errorString = t('inventory', 'Folder name cannot be empty.')
			} else if (newName.includes('/')) {
				this.folderNameError = true
				this.errorString = t('inventory', '"/" is not allowed inside a folder name.')
			} else {
				this.folderNameError = false
				this.errorString = null
			}
		},

		...mapActions([
			'getFoldersByPath',
			'getItemsByPath',
		]),
	},
}
</script>
