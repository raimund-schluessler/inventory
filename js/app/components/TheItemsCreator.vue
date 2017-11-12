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
	<div id="newItemsView">
		Add multiple new items (as comma separated list).

		<form id="newItems" ng-submit="enlist()">
			<textarea v-model="rawInput"></textarea>
			Parsed items:
			<div>
				<items-table v-bind:items="parsedItems"></items-table>
			</div>
			<input type="submit" :value="t('inventory', 'Enlist')">
		</form>
	</div>
</template>

<script>
	import { mapState } from 'vuex';
	import Papa from 'papaparse';
	import ItemsTable from './ItemsTable.vue';

	export default {
		data: function () {
			return {
				rawInput: 'RawData',
				parsedItems: []
			}
		},
		components: {
			'items-table': ItemsTable
		},
		watch: {
			rawInput: function(val, oldVal) {
				var results = Papa.parse(val, {delimiter: ";", newline: "\n"});
				this.parsedItems = [];
				for (i = 0; i < results.data.length; i++) {
					var it = results.data[i];
					var item = {
						'id':			'',
						'maker':		it[0],
						'name':			it[1],
						'description':	it[2],
						'place':		{
							'name': it[3]
						},
						'categories':	it[4].split(',').map(function(s) {
							return {'name':	String.prototype.trim.apply(s)};
						}),
						'price':		it[5],
						'link':			it[6],
						'count':		it[7]
					}
					this.parsedItems.push(item);
				}
			}
		}
	}
</script>
