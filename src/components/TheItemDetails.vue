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
		<div v-if="item">
			<div class="itemnavigation">
				<div class="breadcrumb">
					<div data-dir="/" class="crumb svg">
						<a href="#/items">
							<span class="icon icon-bw icon-items" />
						</a>
					</div>
					<div class="crumb svg">
						<a :href="'#/items/' + item.id">
							{{ item.description }}
						</a>
					</div>
				</div>
				<Dropdown>
					<li>
						<a>
							<span class="icon icon-bw icon-plus" />
							<span class="label">
								{{ t('inventory', 'Add item instance') }}
							</span>
						</a>
					</li>
					<li>
						<a @click="openModal">
							<span class="icon icon-bw icon-plus" />
							<span class="label">
								{{ t('inventory', 'Link items') }}
							</span>
						</a>
					</li>
					<li>
						<a @click="removeItem()">
							<span class="icon icon-bw icon-trash" />
							<span class="label">
								{{ t('inventory', 'Delete item') }}
							</span>
						</a>
					</li>
				</Dropdown>
			</div>
			<div id="itemdetails">
				<div class="item_images" />
				<div>
					<h3>
						<span>{{ t('inventory', 'Properties') }}</span>
					</h3>
					<table class="properties">
						<tbody>
							<tr>
								<td>
									<span>{{ t('inventory', 'Name') }}</span>
								</td>
								<td>
									<span>{{ item.name }}</span>
								</td>
							</tr>
							<tr>
								<td>
									<span>{{ t('inventory', 'Maker') }}</span>
								</td>
								<td>
									<span>{{ item.maker }}</span>
								</td>
							</tr>
							<tr>
								<td>
									<span>{{ t('inventory', 'Description') }}</span>
								</td>
								<td>
									<span>{{ item.description }}</span>
								</td>
							</tr>
							<tr>
								<td>
									<span>{{ t('inventory', 'Item Number') }}</span>
								</td>
								<td>
									<span>{{ item.itemNumber }}</span>
								</td>
							</tr>
							<tr>
								<td>
									<span>{{ t('inventory', 'Link') }}</span>
								</td>
								<td>
									<span>
										<a :href="item.link" target="_blank">
											{{ item.link }}
										</a>
									</span>
								</td>
							</tr>
							<tr>
								<td>
									<span>{{ t('inventory', 'GTIN') }}</span>
								</td>
								<td>
									<span>{{ item.gtin }}</span>
								</td>
							</tr>
							<tr>
								<td>
									<span>{{ t('inventory', 'Details') }}</span>
								</td>
								<td>
									<span>{{ item.details }}</span>
								</td>
							</tr>
							<tr>
								<td>
									<span>{{ t('inventory', 'Comment') }}</span>
								</td>
								<td>
									<span>{{ item.comment }}</span>
								</td>
							</tr>
							<tr>
								<td>
									<span>{{ t('inventory', 'Categories') }}</span>
								</td>
								<td>
									<ul class="categories">
										<li v-for="category in item.categories" :key="category.id">
											<span>{{ category.name }}</span>
										</li>
									</ul>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<br>
				<div class="paragraph">
					<h3>
						<span>{{ t('inventory', 'Instances') }}</span>
					</h3>
					<table class="instances">
						<thead>
							<tr>
								<th>
									<span>{{ t('inventory', 'Count') }}</span>
								</th>
								<th>
									<span>{{ t('inventory', 'Available') }}</span>
								</th>
								<th>
									<span>{{ t('inventory', 'Price') }}</span>
								</th>
								<th>
									<span>{{ t('inventory', 'Date') }}</span>
								</th>
								<th>
									<span>{{ t('inventory', 'Vendor') }}</span>
								</th>
								<th>
									<span>{{ t('inventory', 'Place') }}</span>
								</th>
								<th>
									<span>{{ t('inventory', 'Comment') }}</span>
								</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="instance in item.instances"
								:key="instance.id"
								class="handler"
							>
								<td>{{ instance.count }}</td>
								<td>{{ instance.available }}</td>
								<td>{{ instance.price }}</td>
								<td>{{ instance.date }}</td>
								<td>{{ instance.vendor }}</td>
								<td>{{ getPlace(instance) }}</td>
								<td>{{ instance.comment }}</td>
							</tr>
						</tbody>
					</table>
				</div>
				<br>
				<div v-if="parentItems.length" class="paragraph">
					<h3>
						<span>{{ t('inventory', 'Parent items') }}</span>
					</h3>
					<ItemsTable :items="parentItems" :unlink="true" :search-string="$root.searchString"
						@selectedItemsChanged="selectedParentsChanged" @unlink="unlink('parent')"
					/>
				</div>
				<div v-if="subItems.length" class="paragraph">
					<h3>
						<span>{{ t('inventory', 'Sub items') }}</span>
					</h3>
					<ItemsTable :items="subItems" :unlink="true" :search-string="$root.searchString"
						@selectedItemsChanged="selectedSubChanged" @unlink="unlink('sub')"
					/>
				</div>
				<div v-if="relatedItems.length" class="paragraph">
					<h3>
						<span>{{ t('inventory', 'Related items') }}</span>
					</h3>
					<ItemsTable :items="relatedItems" :unlink="true" :search-string="$root.searchString"
						@selectedItemsChanged="selectedRelatedChanged" @unlink="unlink('related')"
					/>
				</div>
			</div>
			<RelationModal :modal-open.sync="modalOpen" :link="link" :item-id="id" />
		</div>
		<div v-else class="notice">
			<span v-if="loading">{{ t('inventory', 'Loading item from server.') }}</span>
			<span v-else>{{ t('inventory', 'Item not found!') }}</span>
		</div>
	</div>
</template>

<script>
import { mapActions, mapState, mapGetters } from 'vuex'
import ItemsTable from './ItemsTable.vue'
import Dropdown from './Dropdown.vue'
import RelationModal from './RelationModal.vue'

export default {
	components: {
		ItemsTable: ItemsTable,
		Dropdown: Dropdown,
		RelationModal,
	},
	props: {
		id: {
			type: String,
			default: '0'
		}
	},
	data: function() {
		return {
			modalOpen: false,
			loading: false,
			selectedParents: [],
			selectedSub: [],
			selectedRelated: [],
		}
	},
	computed: {
		...mapState({
			item:	state => state.item,
		}),
		...mapGetters({
			parentItems: 'getParentItems',
			subItems: 'getSubItems',
			relatedItems: 'getRelatedItems',
		}),
	},
	created: function() {
		this.loadItem(this.id)
		this.loadSubItems(this.id)
		this.loadParentItems(this.id)
		this.loadRelatedItems(this.id)
	},
	beforeRouteUpdate(to, from, next) {
		this.loadItem(to.params.id)
		this.loadSubItems(to.params.id)
		this.loadParentItems(to.params.id)
		this.loadRelatedItems(to.params.id)
		next()
	},
	methods: {
		async loadItem(itemID) {
			this.loading = true
			await this.getItemById(itemID)
			this.loading = false
		},
		getPlace(instance) {
			if (instance.place) {
				return instance.place.name
			} else {
				return ''
			}
		},
		openModal: function() {
			this.modalOpen = true
		},
		selectedParentsChanged: function(items) {
			this.selectedParents = items
		},
		selectedSubChanged: function(items) {
			this.selectedSub = items
		},
		selectedRelatedChanged: function(items) {
			this.selectedRelated = items
		},
		async link(relation, items) {
			this.linkItems({ itemID: this.item.id, relation, items })
		},
		async unlink(relation) {
			let items = []
			switch (relation) {
			case 'parent':
				items = this.selectedParents
				break
			case 'sub':
				items = this.selectedSub
				break
			case 'related':
				items = this.selectedRelated
				break
			}
			this.unlinkItems({ itemID: this.item.id, relation, items })
		},

		removeItem: function() {
			this.deleteItem(this.item)
			this.closeDetails()
		},

		closeDetails: function() {
			this.$router.push('/items')
		},

		...mapActions([
			'getItemById',
			'loadSubItems',
			'loadParentItems',
			'loadRelatedItems',
			'deleteItem',
			'linkItems',
			'unlinkItems',
		])
	}
}
</script>
