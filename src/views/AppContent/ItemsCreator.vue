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
		<div id="newItemsView">
			<div class="header">
				{{ t('inventory', 'Add multiple new items (as comma separated list).') }}
				<br>
				{{ t('inventory', 'Expects csv with the fields:') }}
				<span v-for="field in fields" :key="field">
					&lt;{{ field }}&gt;;
				</span>
			</div>
			<div>
				<form id="newItems" @submit.prevent="enlist">
					<textarea v-model="rawInput" />
					{{ t('inventory', 'Items:') }}
					<div>
						<EntityTable :items="items"
							:allow-deletion="false"
							:search-string="$root.searchString" />
					</div>
					<input :value="t('inventory', 'Enlist')" type="submit" :disabled="!canEnlist">
				</form>
			</div>
		</div>
	</div>
</template>

<script>
import EntityTable from '../../components/EntityTable/EntityTable.vue'
import Item from '../../models/item.js'

import { translate as t } from '@nextcloud/l10n'

import Papa from 'papaparse'
import { mapActions } from 'vuex'

export default {
	components: {
		EntityTable,
	},
	props: {
		path: {
			type: String,
			default: '',
		},
		collection: {
			type: String,
			default: 'folders',
		},
	},
	data() {
		return {
			rawInput: '',
			enlisted: false,
			items: [],
			fields: ['name', 'maker', 'description', 'item_number', 'link', 'GTIN', 'details', 'comment', 'type', 'place', 'price', 'count', 'available', 'vendor', 'date', 'tags', 'related'],
		}
	},
	computed: {
		canEnlist() {
			return !this.enlisted && this.items.length
		},
	},
	watch: {
		rawInput(val, oldVal) {
			this.enlisted = false
			const results = Papa.parse(val, { delimiter: ';', newline: '\n' })
			this.items = []
			for (let i = 0; i < results.data.length; i++) {
				const it = results.data[i]
				if (it.length < 15) {
					continue
				}
				let item = {
					id: i,
					name: it[0],
					maker: it[1],
					description: it[2],
					item_number: it[3],
					link: it[4],
					gtin: it[5],
					details: it[6],
					comment: it[7],
					type: it[8],
					instances: [{
						place: it[9],
						price: it[10],
						count: it[11],
						available: it[12],
						vendor: it[13],
						date: it[14],
						comment: '',
					}],
				}
				const c = it[15].split(',')
				const categories = []
				let name
				for (let j = 0; j < c.length; j++) {
					name = String.prototype.trim.apply(c[j])
					if (name.length) {
						categories.push({ name })
					}
				}
				item.syncStatus = {
					type: 'unsynced',
					message: t('inventory', 'The item has not been saved to the server yet.'),
				}
				item.categories = categories
				if (this.collection === 'folders') {
					item.path = this.path
				} else if (this.collection === 'places' && this.path !== '') {
					item.path = ''
					for (let j = 0; j < item.instances.length; j++) {
						item.instances[j].place = this.path + (item.instances[j].place ? '/' + item.instances[j].place : '')
					}
				}
				item = new Item(item)
				this.items.push(item)
			}
		},
	},
	methods: {
		t,

		...mapActions([
			'createItems',
		]),

		async enlist() {
			await this.createItems(this.items)
			this.enlisted = true
		},
	},
}
</script>
