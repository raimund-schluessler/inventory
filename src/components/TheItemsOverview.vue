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
		<ItemsTable :items="items" :folders="folders" :loading="loading"
			:show-dropdown="true" :search-string="$root.searchString"
		/>
	</div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'
import ItemsTable from './ItemsTable.vue'

export default {
	components: {
		ItemsTable: ItemsTable
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
	},
	beforeRouteUpdate(to, from, next) {
		this.getFolders(to.params.path)
		next()
	},
	methods: {
		async getFolders(path) {
			await this.getFoldersByPath(path)
		},

		...mapActions([
			'getFoldersByPath',
		]),
	},
}
</script>
