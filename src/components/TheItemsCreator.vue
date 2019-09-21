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
		{{ t('inventory', 'Add multiple new items (as comma separated list).') }}
		<br>
		{{ t('inventory', 'Expects csv with the fields:') }}
		<span v-for="field in fields" :key="field">
			&lt;{{ field }}&gt;;
		</span>
		<form id="newItems" @submit.prevent="enlist">
			<textarea v-model="rawInput" />
			{{ t('inventory', 'Items:') }}
			<div>
				<ItemsTable :items="items" :show-dropdown="false" :search-string="$root.searchString" />
			</div>
			<input :value="t('inventory', 'Enlist')" type="submit" :disabled="!canEnlist">
		</form>
	</div>
</template>

<script>
import Axios from 'axios'
import Papa from 'papaparse'
import ItemsTable from './ItemsTable.vue'
import Item from '../models/item'
import Status from '../models/status'
import PQueue from 'p-queue'

export default {
	components: {
		ItemsTable: ItemsTable
	},
	data: function() {
		return {
			rawInput: '',
			enlisted: false,
			items: [],
			fields: ['name', 'maker', 'description', 'item_number', 'link', 'GTIN', 'details', 'comment', 'type', 'place', 'price', 'count', 'available', 'vendor', 'date', 'categories', 'related']
		}
	},
	computed: {
		canEnlist() {
			return !this.enlisted && this.items.length
		}
	},
	watch: {
		rawInput: function(val, oldVal) {
			this.enlisted = false
			var results = Papa.parse(val, { delimiter: ';', newline: '\n' })
			this.items = []
			for (var i = 0; i < results.data.length; i++) {
				var it = results.data[i]
				if (it.length < 15) {
					continue
				}
				var item = {
					id: i,
					name:	it[0],
					maker:	it[1],
					description:	it[2],
					item_number:	it[3],
					link:	it[4],
					gtin:	it[5],
					details:	it[6],
					comment:	it[7],
					type:	it[8],
					instances: [{
						place:	it[9],
						price:	it[10],
						count:	it[11],
						available: it[12],
						vendor:	it[13],
						date:	it[14],
						comment:	''
					}]
				}
				var c = it[15].split(',')
				var categories = []
				var name
				for (var j = 0; j < c.length; j++) {
					name = String.prototype.trim.apply(c[j])
					if (name.length) {
						categories.push({ name })
					}
				}
				item.categories = categories
				item = new Item(item)
				this.items.push(item)
			}
		}
	},
	methods: {
		async enlist() {
			const queue = new PQueue({ concurrency: 5 })
			this.items.forEach(async(item) => {
				await queue.add(async() => {
					const response = await Axios.post(OC.generateUrl('apps/inventory/item/add'), { item: item.response })
					if (response.data.status === 'success') {
						item.syncstatus = new Status('created', 'Successfully created the item.') // eslint-disable-line require-atomic-updates
					} else {
						item.syncstatus = new Status('error', 'Item creation failed.') // eslint-disable-line require-atomic-updates
					}
				})
			})
			this.enlisted = true
		}
	}
}
</script>
