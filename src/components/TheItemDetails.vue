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
		<div>
			<div id="controls" class="itemnavigation">
				<Breadcrumbs>
					<Breadcrumb v-for="(crumb, index) in breadcrumbs"
						:key="crumb.path"
						:title="crumb.title"
						:to="crumb.path">
						<component
							:is="breadcrumbIcon"
							v-if="index === 0"
							slot="icon"
							:size="24"
							decorative />
					</Breadcrumb>
				</Breadcrumbs>
				<Actions
					v-if="item && !loadingItem"
					container="#controls"
					:boundaries-element="boundaries">
					<ActionButton :close-after-click="true" @click="openModal">
						<Plus slot="icon" :size="24" decorative />
						{{ t('inventory', 'Link items') }}
					</ActionButton>
					<ActionButton
						:close-after-click="true"
						@click="upload">
						<Upload slot="icon" :size="24" decorative />
						{{ t('inventory', 'Upload image') }}
					</ActionButton>
					<ActionButton
						icon="icon-rename"
						:close-after-click="true"
						@click="toggleEditItem">
						<Pencil slot="icon" :size="24" decorative />
						{{ t('inventory', 'Edit item') }}
					</ActionButton>
					<ActionButton @click="removeItem">
						<Delete slot="icon" :size="24" decorative />
						{{ t('inventory', 'Delete item') }}
					</ActionButton>
				</Actions>
			</div>
			<EmptyContent v-if="loadingItem || !item" :icon="loadingItem ? 'icon-loading' : 'icon-search'">
				<span v-if="loadingItem">{{ t('inventory', 'Loading item from server.') }}</span>
				<span v-else>{{ t('inventory', 'Item not found!') }}</span>
			</EmptyContent>
			<div v-else id="itemdetails">
				<div class="paragraph images"
					@dragover.prevent="!isDraggingOver && (isDraggingOver = true)"
					@dragleave.prevent="isDraggingOver && (isDraggingOver = false)"
					@drop.prevent="handleDropImages">
					<div class="item_images" :class="{default: !item.images.length}">
						<div>
							<img v-if="item.images.length > 0" :src="imageSrc">
						</div>
					</div>
				</div>
				<div class="paragraph properties">
					<h3>
						<span>{{ t('inventory', 'Properties') }}</span>
					</h3>
					<div v-click-outside="hideEditItem" class="table table--properties">
						<div v-for="itemProperty in itemProperties" :key="itemProperty.key" class="row">
							<div class="column">
								<span>{{ itemProperty.name }}</span>
							</div>
							<div class="column">
								<div v-if="itemProperty.key === 'link'" class="wrapper">
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
								<div v-else-if="itemProperty.key === 'tags'" class="wrapper">
									<ul class="tags">
										<li v-for="tag in item.tags" :key="tag.id">
											<span>{{ tag.name }}</span>
										</li>
									</ul>
								</div>
								<div v-else class="wrapper">
									<span :class="{ 'visibility-hidden': editingItem }">{{ item[itemProperty.key] }}</span>
									<Actions
										v-if="itemProperty.key === 'gtin' && item[itemProperty.key] && !editingItem "
										:boundaries-element="boundaries">
										<ActionButton :close-after-click="true" @click="openBarcode(item[itemProperty.key], 'ean13', 'includetext guardwhitespace')">
											<Barcode slot="icon" :size="24" decorative />
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
							</div>
						</div>
						<div class="row">
							<div class="column">
								{{ t('inventory', 'Attachments') }}
							</div>
							<Attachments
								:attachments="item.attachments"
								:item-id="String(item.id)"
								:loading-attachments="loadingAttachments(item.id)"
								class="column column--attachments" />
						</div>
						<div v-if="editingItem" class="row">
							<div class="column column--width-2 column--actions">
								<Actions>
									<ActionButton @click="saveItem">
										<Check slot="icon" :size="24" decorative />
										{{ t('inventory', 'Save changes') }}
									</ActionButton>
								</Actions>
							</div>
						</div>
					</div>
				</div>
				<div class="paragraph">
					<h3>
						<span>{{ t('inventory', 'Instances') }}</span>
					</h3>
					<ItemInstances :item="item" :instance-id="instanceId" @open-barcode="(uuid) => openBarcode(uuid)" />
				</div>
				<div v-if="parentItems.length" class="paragraph">
					<h3>
						<span>{{ t('inventory', 'Parent items') }}</span>
					</h3>
					<div>
						<EntityTable :items="parentItems"
							:unlink="true"
							:search-string="$root.searchString"
							:filter-only="true"
							@selected-items-changed="selectedParentsChanged"
							@unlink="unlink('parent')" />
					</div>
				</div>
				<div v-if="subItems.length" class="paragraph">
					<h3>
						<span>{{ t('inventory', 'Sub items') }}</span>
					</h3>
					<div>
						<EntityTable :items="subItems"
							:unlink="true"
							:search-string="$root.searchString"
							:filter-only="true"
							@selected-items-changed="selectedSubChanged"
							@unlink="unlink('sub')" />
					</div>
				</div>
				<div v-if="relatedItems.length" class="paragraph">
					<h3>
						<span>{{ t('inventory', 'Related items') }}</span>
					</h3>
					<div>
						<EntityTable :items="relatedItems"
							:unlink="true"
							:search-string="$root.searchString"
							:filter-only="true"
							@selected-items-changed="selectedRelatedChanged"
							@unlink="unlink('related')" />
					</div>
				</div>
			</div>
			<RelationModal :modal-open.sync="modalOpen" :link="link" :item-id="id" />
		</div>
		<form id="edit_item" method="POST" />
		<Modal v-if="showBarcode"
			id="qrcode-modal"
			size="full"
			@close="closeBarcode">
			<div>
				<canvas ref="canvas" class="qrcode" />
			</div>
		</Modal>
		<input ref="localAttachments"
			type="file"
			style="display: none;"
			@change="handleUploadFile">
	</div>
</template>

<script>
import EntityTable from './EntityTable/EntityTable.vue'
import Attachments from './Attachments.vue'
import RelationModal from './RelationModal.vue'
import ItemInstances from './TheItemInstances.vue'
import focus from '../directives/focus.vue'

import { showError } from '@nextcloud/dialogs'
import { formatFileSize } from '@nextcloud/files'
import { generateUrl } from '@nextcloud/router'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import Breadcrumbs from '@nextcloud/vue/dist/Components/Breadcrumbs'
import Breadcrumb from '@nextcloud/vue/dist/Components/Breadcrumb'
import Modal from '@nextcloud/vue/dist/Components/Modal'
import EmptyContent from '@nextcloud/vue/dist/Components/EmptyContent'

import Barcode from 'vue-material-design-icons/Barcode.vue'
import Check from 'vue-material-design-icons/Check.vue'
import Folder from 'vue-material-design-icons/Folder.vue'
import MapMarker from 'vue-material-design-icons/MapMarker.vue'
import Tag from 'vue-material-design-icons/Tag.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Upload from 'vue-material-design-icons/Upload.vue'

import bwipjs from 'bwip-js'
import ClickOutside from 'vue-click-outside'
import { mapActions, mapGetters } from 'vuex'

export default {
	components: {
		Actions,
		ActionButton,
		EntityTable,
		RelationModal,
		ItemInstances,
		Attachments,
		Breadcrumbs,
		Breadcrumb,
		Modal,
		EmptyContent,
		Barcode,
		Check,
		Delete,
		Pencil,
		Plus,
		Upload,
		Folder,
		MapMarker,
		Tag,
	},
	directives: {
		ClickOutside,
		focus,
	},
	beforeRouteUpdate(to, from, next) {
		this.getItem(to.params.id)
		this.loadSubItems(to.params.id)
		this.loadParentItems(to.params.id)
		this.loadRelatedItems(to.params.id)
		next()
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
		instanceId: {
			type: String,
			default: null,
		},
		collection: {
			type: String,
			default: 'folders',
		},
	},
	data() {
		return {
			modalOpen: false,
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
					key: 'tags',
					name: this.t('inventory', 'Tags'),
				},
			],
			isDraggingOver: false,
			maxUploadSize: 16e7,
			// Hack to fix https://github.com/nextcloud/nextcloud-vue/issues/1384
			boundaries: document.querySelector('#content-vue'),
		}
	},
	computed: {
		...mapGetters({
			item: 'getItem',
			parentItems: 'getParentItems',
			subItems: 'getSubItems',
			relatedItems: 'getRelatedItems',
			loadingItem: 'loadingItem',
			loadingAttachments: 'loadingAttachments',
		}),

		breadcrumbs() {
			const path = this.path
			const crumbs = (!path || path === '') ? [] : path.split('/')
			const breadcrumbs = [{ title: t('inventory', 'Items'), path: `/${this.collection}/` }].concat(crumbs.map((crumb, i) => {
				return {
					title: crumb,
					path: `/${this.collection}/` + crumbs.slice(0, i + 1).join('/'),
				}
			}))
			if (this.item && !this.loadingItem) {
				return breadcrumbs.concat([{
					title: this.item.description,
					path: `/${this.collection}/${(this.path) ? this.path + '/' : ''}item-${this.id}${(this.instanceId) ? `/instance-${this.instanceId}` : ''}`,
				}])
			}
			return breadcrumbs
		},

		breadcrumbIcon() {
			if (this.collection === 'folders') {
				return 'Folder'
			}
			if (this.collection === 'places') {
				return 'MapMarker'
			}
			if (this.collection === 'tags') {
				return 'Tag'
			}
			return 'Folder'
		},

		imageSrc() {
			if (this.item.images.length > 0) {
				const img = this.item.images[0]
				return generateUrl(`/core/preview?fileId=${img.fileid}&x=${512}&y=${512}&a=true&v=${img.etag}`)
			}
			return ''
		},
	},
	created() {
		this.getItem(this.id)
		this.loadSubItems(this.id)
		this.loadParentItems(this.id)
		this.loadRelatedItems(this.id)
	},
	methods: {

		upload() {
			this.$refs.localAttachments.click()
		},

		handleDropImages(event) {
			this.isDraggingOver = false
			this.onLocalAttachmentSelected(event.dataTransfer.files[0])
			event.dataTransfer.value = ''
		},

		handleUploadFile(event) {
			this.onLocalAttachmentSelected(event.target.files[0])
			event.target.value = ''
		},

		async onLocalAttachmentSelected(file) {
			if (!['image/jpeg', 'image/png'].includes(file.type)) {
				showError(t('inventory', 'Please select an image file (PNG or JPEG).'))
				event.target.value = ''
				return
			}
			if (this.maxUploadSize > 0 && file.size > this.maxUploadSize) {
				showError(
					t('inventory', 'Failed to upload {name}', { name: file.name }) + ' - '
						+ t('inventory', 'Maximum file size of {size} exceeded', { size: formatFileSize(this.maxUploadSize) })
				)
				event.target.value = ''
				return
			}

			const bodyFormData = new FormData()
			bodyFormData.append('itemId', this.itemId)
			if (this.instanceId) {
				bodyFormData.append('instanceId', this.instanceId)
			}
			bodyFormData.append('file', file)
			try {
				await this.$store.dispatch('uploadImage', {
					itemId: this.item.id,
					formData: bodyFormData,
				})
			} catch (err) {
				if (err.response.data.status === 409) {
					showError(t('inventory', 'An image with this name already exists.'))
				} else {
					showError(err.response.data.message)
				}
			}
		},

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
			await this.getItemById(itemID)
			this.getAttachments(itemID)
			this.item.instances.forEach(instance => {
				this.getInstanceAttachments({ itemID, instanceID: instance.id })
			})
		},

		hideEditItem() {
			if (this.closing) {
				this.editingItem = false
			}
			this.closing = true
		},
		toggleEditItem() {
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
		openModal() {
			this.modalOpen = true
		},
		selectedParentsChanged(items) {
			this.selectedParents = items
		},
		selectedSubChanged(items) {
			this.selectedSub = items
		},
		selectedRelatedChanged(items) {
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

		removeItem() {
			this.deleteItem(this.item)
			this.closeDetails()
		},

		closeDetails() {
			const path = this.$router.currentRoute.path
			this.$router.push(path.substring(0, path.lastIndexOf('/item')))
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
