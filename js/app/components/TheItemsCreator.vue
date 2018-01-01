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
		Add multiple new items (as comma separated list).<br/>
		Expects csv with the fields:
		<span v-for='field in fields'>
			&lt;{{ field }}&gt;;
		</span>
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
				parsedItems: [],
				fields: ['id', 'name', 'maker', 'description', 'item_number', 'link', 'EAN', 'details', 'place', 'price', 'count', 'available', 'vendor', 'date', 'categories', 'related']
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
						'id':			it[0],
						'name':			it[1],
						'maker':		it[2],
						'description':	it[3],
						'item_number':	it[4],
						'link':			it[5],
						'ean':			it[6],
						'details':		it[7],
						'instances': {
							'place':	it[8],
							'price':	it[9],
							'count':	it[10],
							'available':it[11],
							'vendor':	it[12],
							'date':		it[13],
						},
						'categories':	it[14].split(',').map(function(s) {
							return {'name':	String.prototype.trim.apply(s)};
						}),
						'related':		it[15].split(',').map(function(s) {
							return {'parentid':	String.prototype.trim.apply(s)};
						})
					}
					this.parsedItems.push(item);
					console.log(item);
				}
				console.log(this.parsedItems);
			}
		}
	}
</script>
