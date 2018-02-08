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
		<form id="newItems" v-on:submit.prevent="enlist">
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
	import Axios from 'axios';
	import Papa from 'papaparse';
	import ItemsTable from './ItemsTable.vue';

	export default {
		data: function () {
			return {
				rawInput: 'RawData',
				parsedItems: [],
				fields: ['name', 'maker', 'description', 'item_number', 'link', 'GTIN', 'details', 'comment', 'type', 'place', 'price', 'count', 'available', 'vendor', 'date', 'categories', 'related']
			}
		},
		components: {
			'items-table': ItemsTable
		},
		methods: {
			enlist() {
				for (i = 0; i < this.parsedItems.length; i++) {
					Axios.post(OC.generateUrl('apps/inventory/item/add'), {
						item: this.parsedItems[i]
					})
				}
				// this.parsedItems = [];
			}
		},
		watch: {
			rawInput: function(val, oldVal) {
				var results = Papa.parse(val, {delimiter: ";", newline: "\n"});
				this.parsedItems = [];
				for (i = 0; i < results.data.length; i++) {
					var it = results.data[i];
					var item = {
						'name':			it[0],
						'maker':		it[1],
						'description':	it[2],
						'item_number':	it[3],
						'link':			it[4],
						'gtin':			it[5],
						'details':		it[6],
						'comment':		it[7],
						'type':			it[8],
						'instances': [{
							'place':	it[9],
							'price':	it[10],
							'count':	it[11],
							'available':it[12],
							'vendor':	it[13],
							'date':		it[14],
							'comment':	''
						}]
					}
					var c = it[15].split(','), categories = [], name;
					for (var j = 0; j < c.length; j++) {
						name = String.prototype.trim.apply(c[j]);
						if (name.length) {
							categories.push({'name': name});
						}
					}
					item.categories = categories;
					this.parsedItems.push(item);
				}
			}
		}
	}
</script>
