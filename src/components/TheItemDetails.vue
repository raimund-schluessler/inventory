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
		<div v-if="item" class="app-content-details">
			<div id="controls" class="itemnavigation">
				<Breadcrumbs :breadcrumbs="breadcrumbs" :item="item" root-icon="icon-bw icon-items" />
				<Actions>
					<ActionButton icon="icon-add" :close-after-click="true" @click="openModal">
						{{ t('inventory', 'Link items') }}
					</ActionButton>
					<ActionButton icon="icon-rename" :close-after-click="true" @click="toggleEditItem">
						{{ t('inventory', 'Edit item') }}
					</ActionButton>
					<ActionButton icon="icon-delete" @click="removeItem">
						{{ t('inventory', 'Delete item') }}
					</ActionButton>
				</Actions>
			</div>
			<div id="itemdetails">
				<div class="paragraph images">
					<div class="item_images" />
				</div>
				<div class="paragraph properties">
					<h3>
						<span>{{ t('inventory', 'Properties') }}</span>
					</h3>
					<div>
						<table class="properties">
							<tbody v-click-outside="hideEditItem">
								<tr v-for="itemProperty in itemProperties" :key="itemProperty.key">
									<th>
										<span>{{ itemProperty.name }}</span>
									</th>
									<td v-if="itemProperty.key === 'link'">
										<div class="wrapper">
											<span :class="{ 'visibility-hidden': editingItem }">
												<a :href="item.link" target="_blank">
													{{ item.link }}
												</a>
											</span>
											<span v-if="editingItem" class="input">
												<input v-model="editedItem.link"
													type="text"
													:placeholder="itemProperty.name"
													:name="itemProperty.key"
													form="edit_item">
											</span>
										</div>
									</td>
									<td v-else-if="itemProperty.key === 'categories'">
										<div class="wrapper">
											<ul class="categories">
												<li v-for="category in item.categories" :key="category.id">
													<span>{{ category.name }}</span>
												</li>
											</ul>
										</div>
									</td>
									<td v-else>
										<div class="wrapper">
											<span :class="{ 'visibility-hidden': editingItem }">{{ item[itemProperty.key] }}</span>
											<Actions v-if="itemProperty.key === 'gtin' && item[itemProperty.key] && !editingItem ">
												<ActionButton icon="icon-gtin" :close-after-click="true" @click="openBarcode(item[itemProperty.key], 'ean13', 'includetext guardwhitespace')">
													{{ t('inventory', 'Show GTIN') }}
												</ActionButton>
											</Actions>
											<span v-if="editingItem" class="input">
												<input v-model="editedItem[itemProperty.key]"
													v-focus="itemProperty.key === 'name'"
													type="text"
													:placeholder="itemProperty.name"
													:name="itemProperty.key"
													form="edit_item">
											</span>
										</div>
									</td>
								</tr>
								<tr>
									<th>
										{{ t('inventory', 'Attachments') }}
									</th>
									<td class="attachment-list">
										<Attachments :attachments="item.attachments" :item-id="String(item.id)" />
									</td>
								</tr>
								<tr v-if="editingItem">
									<td colspan="2">
										<button type="submit" form="edit_item" class="right">
											{{ t('inventory', 'Save') }}
										</button>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="paragraph">
					<h3>
						<span>{{ t('inventory', 'Instances') }}</span>
					</h3>
					<ItemInstances :item="item" @openBarcode="(uuid) => openBarcode(uuid)" />
				</div>
				<div v-if="parentItems.length" class="paragraph">
					<h3>
						<span>{{ t('inventory', 'Parent items') }}</span>
					</h3>
					<div>
						<ItemsTable :items="parentItems"
							:unlink="true"
							:search-string="$root.searchString"
							@selectedItemsChanged="selectedParentsChanged"
							@unlink="unlink('parent')" />
					</div>
				</div>
				<div v-if="subItems.length" class="paragraph">
					<h3>
						<span>{{ t('inventory', 'Sub items') }}</span>
					</h3>
					<div>
						<ItemsTable :items="subItems"
							:unlink="true"
							:search-string="$root.searchString"
							@selectedItemsChanged="selectedSubChanged"
							@unlink="unlink('sub')" />
					</div>
				</div>
				<div v-if="relatedItems.length" class="paragraph">
					<h3>
						<span>{{ t('inventory', 'Related items') }}</span>
					</h3>
					<div>
						<ItemsTable :items="relatedItems"
							:unlink="true"
							:search-string="$root.searchString"
							@selectedItemsChanged="selectedRelatedChanged"
							@unlink="unlink('related')" />
					</div>
				</div>
			</div>
			<RelationModal :modal-open.sync="modalOpen" :link="link" :item-id="id" />
		</div>
		<div v-else class="app-content-details notice">
			<span v-if="loading">{{ t('inventory', 'Loading item from server.') }}</span>
			<span v-else>{{ t('inventory', 'Item not found!') }}</span>
		</div>
		<form id="edit_item" method="POST" @submit.prevent="saveItem" />
		<Modal v-if="showBarcode"
			id="qrcode-modal"
			size="full"
			@close="closeBarcode">
			<div>
				<canvas ref="canvas" class="qrcode" />
			</div>
		</Modal>
	</div>
</template>

<script>
import { mapActions, mapState, mapGetters } from 'vuex'
import ItemsTable from './ItemsTable.vue'
import Attachments from './Attachments.vue'
import Breadcrumbs from './Breadcrumbs.vue'
import RelationModal from './RelationModal.vue'
import ItemInstances from './TheItemInstances.vue'
import focus from '../directives/focus'
import bwipjs from 'bwip-js'
import ClickOutside from 'vue-click-outside'
import { Actions } from '@nextcloud/vue/dist/Components/Actions'
import { ActionButton } from '@nextcloud/vue/dist/Components/ActionButton'
import { Modal } from '@nextcloud/vue/dist/Components/Modal'

export default {
	components: {
		ItemsTable: ItemsTable,
		Actions,
		ActionButton,
		RelationModal,
		ItemInstances,
		Attachments,
		Breadcrumbs,
		Modal,
	},
	directives: {
		ClickOutside,
		focus,
	},
	props: {
		id: {
			type: String,
			default: '0',
		},
		path: {
			type: String,
			default: '',
		},
	},
	data: function() {
		return {
			modalOpen: false,
			loading: false,
			selectedParents: [],
			selectedSub: [],
			selectedRelated: [],
			editingItem: false,
			editedItem: {},
			closing: true,
			showBarcode: false,
			itemProperties: [
				{
					key: 'name',
					name: this.t('inventory', 'Name'),
				}, {
					key: 'maker',
					name: this.t('inventory', 'Maker'),
				}, {
					key: 'description',
					name: this.t('inventory', 'Description'),
				}, {
					key: 'itemNumber',
					name: this.t('inventory', 'Item number'),
				}, {
					key: 'link',
					name: this.t('inventory', 'Link'),
				}, {
					key: 'gtin',
					name: this.t('inventory', 'GTIN'),
				}, {
					key: 'details',
					name: this.t('inventory', 'Details'),
				}, {
					key: 'comment',
					name: this.t('inventory', 'Comment'),
				}, {
					key: 'categories',
					name: this.t('inventory', 'Categories'),
				},
			],
		}
	},
	computed: {
		...mapState({
			item: state => state.item,
		}),
		...mapGetters({
			parentItems: 'getParentItems',
			subItems: 'getSubItems',
			relatedItems: 'getRelatedItems',
		}),

		breadcrumbs() {
			const path = this.$route.params.path
			const crumbs = (!path || path === '') ? [] : path.split('/')
			return [{ name: t('inventory', 'Items'), path: '/folders/' }].concat(crumbs.map((crumb, i) => {
				return {
					name: crumb,
					path: '/folders/' + crumbs.slice(0, i + 1).join('/'),
				}
			})).concat([{
				name: this.item.description,
				path: `/folders/${(this.item.path) ? this.item.path + '/' : ''}item-${this.item.id}`,
			}])
		},
	},
	created: function() {
		this.getItem(this.id)
		this.loadSubItems(this.id)
		this.loadParentItems(this.id)
		this.loadRelatedItems(this.id)
	},
	beforeRouteUpdate(to, from, next) {
		this.getItem(to.params.id)
		this.loadSubItems(to.params.id)
		this.loadParentItems(to.params.id)
		this.loadRelatedItems(to.params.id)
		next()
	},
	methods: {

		/**
		 * Generate a barcode for the given string
		 * @param {String} value The string to show as barcode
		 * @param {String} type The barcode type
		 * @param {String} options The barcode options
		 */
		openBarcode(value, type = 'qrcode', options = '') {
			if (value.length > 0) {
				this.showBarcode = true
				// We have to wait for the modal to render before
				// drawing on the qr code canvas.
				this.$nextTick(() => {
					bwipjs.toCanvas(this.$refs.canvas, {
						bcid: type,
						scale: 6,
						text: value,
						height: 20,
						includetext: true,
					})
				})
			}
		},

		// reset the current qrcode
		closeBarcode() {
			this.showBarcode = false
		},

		async getItem(itemID) {
			await this.loadItem(itemID)
			this.getAttachments(itemID)
			this.item.instances.forEach(instance => {
				this.getInstanceAttachments({ itemID, instanceID: instance.id })
			})
		},

		hideEditItem: function() {
			if (this.closing) {
				this.editingItem = false
			}
			this.closing = true
		},
		toggleEditItem: function() {
			this.editingItem = !this.editingItem
			if (this.editingItem) {
				this.editedItem = this.item.response
				this.closing = false
			}
		},
		async saveItem() {
			await this.editItem(this.item)
			this.editingItem = false
		},
		async loadItem(itemID) {
			this.loading = true
			await this.getItemById(itemID)
			this.loading = false
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
			'getAttachments',
			'getInstanceAttachments',
			'loadSubItems',
			'loadParentItems',
			'loadRelatedItems',
			'deleteItem',
			'editItem',
			'linkItems',
			'unlinkItems',
		]),
	},
}
</script>
