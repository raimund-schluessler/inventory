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
		<items-table v-bind:items="items" v-bind:showDropdown="true" v-bind:searchString="$root.searchString"></items-table>
	</div>
</template>

<script>
	import { mapState } from 'vuex';
	import ItemsTable from './ItemsTable.vue';

	export default {
		computed: mapState({
			items: state => state.items
		}),
		components: {
			'items-table': ItemsTable
		}
	}
</script>
