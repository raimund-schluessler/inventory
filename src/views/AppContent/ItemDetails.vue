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
				<NcBreadcrumbs>
					<NcBreadcrumb v-for="(crumb, index) in breadcrumbs"
						:key="crumb.path"
						:title="crumb.title"
						:to="crumb.path">
						<template #icon>
							<component :is="breadcrumbIcon"
								v-if="index === 0"
								:size="20" />
						</template>
					</NcBreadcrumb>
				</NcBreadcrumbs>
				<NcActions v-if="item && !loadingItem"
					container="#controls"
					:boundaries-element="boundaries">
					<NcActionButton :close-after-click="true" @click="openModal">
						<template #icon>
							<Plus :size="20" />
						</template>
						{{ t('inventory', 'Link items') }}
					</NcActionButton>
					<NcActionButton :close-after-click="true"
						@click="upload">
						<template #icon>
							<Upload :size="20" />
						</template>
						{{ t('inventory', 'Upload image') }}
					</NcActionButton>
					<NcActionButton icon="icon-rename"
						:close-after-click="true"
						@click="startEditingItem">
						<template #icon>
							<Pencil :size="20" />
						</template>
						{{ t('inventory', 'Edit item') }}
					</NcActionButton>
					<NcActionButton @click="removeItem">
						<template #icon>
							<Delete :size="20" />
						</template>
						{{ t('inventory', 'Delete item') }}
					</NcActionButton>
				</NcActions>
			</div>
			<NcEmptyContent v-if="loadingItem || !item">
				<template #icon>
					<NcLoadingIcon v-if="loadingItem" />
					<Magnify v-else />
				</template>
				<span v-if="loadingItem">{{ t('inventory', 'Loading item from server.') }}</span>
				<span v-else>{{ t('inventory', 'Item not found!') }}</span>
			</NcEmptyContent>
			<div v-else id="itemdetails">
				<div class="paragraph paragraph--images"
					@dragover.prevent="!isDraggingOver && (isDraggingOver = true)"
					@dragleave.prevent="isDraggingOver && (isDraggingOver = false)"
					@drop.prevent="handleDropImages">
					<div class="item_images">
						<img v-if="item.images.length > 0" :src="imageSrc">
						<InventoryIcon v-else />
					</div>
				</div>
				<div class="paragraph paragraph--properties">
					<h3>
						<span>{{ t('inventory', 'Properties') }}</span>
					</h3>
					<div v-click-outside="stopEditingItem" class="table table--properties" :class="{ 'table--editing': editingItem }">
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
									<NcSelect v-if="editingItem"
										:value="editedItem.tags"
										taggable
										label="name"
										:placeholder="t('inventory', 'Select tags')"
										:multiple="true"
										:close-on-select="false"
										:tag-placeholder="t('inventory', 'Add this as a new tag')"
										@input="setTags">
										<template #no-options>
											{{ t('inventory', 'No tag available. Create one!') }}
										</template>
									</NcSelect>
									<TagList v-else :tags="item.tags" />
								</div>
								<div v-else class="wrapper">
									<span :class="{ 'visibility-hidden': editingItem }">{{ item[itemProperty.key] }}</span>
									<NcActions v-if="itemProperty.key === 'gtin' && item[itemProperty.key] && !editingItem"
										class="button--gtin"
										:boundaries-element="boundaries">
										<NcActionButton :close-after-click="true" @click="openBarcode(item[itemProperty.key], 'ean13', 'includetext guardwhitespace')">
											<template #icon>
												<Barcode :size="20" />
											</template>
											{{ t('inventory', 'Show GTIN') }}
										</NcActionButton>
									</NcActions>
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
							<Attachments :attachments="item.attachments"
								:item-id="String(item.id)"
								:loading-attachments="loadingAttachments(item.id)"
								class="column column--attachments" />
						</div>
						<div v-if="editingItem" class="row">
							<div class="column column--width-2 column--actions">
								<NcActions>
									<NcActionButton class="button--save" @click="saveItem">
										<template #icon>
											<Check :size="20" />
										</template>
										{{ t('inventory', 'Save changes') }}
									</NcActionButton>
								</NcActions>
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
							:filter-only="true"
							@selected-items-changed="selectedRelatedChanged"
							@unlink="unlink('related')" />
					</div>
				</div>
			</div>
			<RelationModal :modal-open.sync="modalOpen" :link="link" :item-id="id" />
		</div>
		<form id="edit_item" method="POST" />
		<NcModal v-if="showBarcode"
			class="qrcode-modal"
			size="small"
			@close="closeBarcode">
			<div>
				<canvas ref="canvas" class="qrcode" />
			</div>
		</NcModal>
		<input ref="localAttachments"
			type="file"
			style="display: none;"
			@change="handleUploadFile">
	</div>
</template>

<script>
import EntityTable from '../../components/EntityTable/EntityTable.vue'
import Attachments from '../../components/Attachments.vue'
import RelationModal from '../../components/RelationModal.vue'
import ItemInstances from '../../components/ItemInstances.vue'
import InventoryIcon from '../../components/InventoryIcon.vue'
import TagList from '../../components/TagList.vue'
import focus from '../../directives/focus.vue'
import showBarcode from '../../mixins/showBarcode.js'
import { encodePath } from '../../utils/encodePath.js'

import { showError } from '@nextcloud/dialogs'
import { formatFileSize } from '@nextcloud/files'
import { translate as t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'
import {
	NcActions,
	NcActionButton,
	NcBreadcrumbs,
	NcBreadcrumb,
	NcEmptyContent,
	NcLoadingIcon,
	NcSelect,
} from '@nextcloud/vue'

import Barcode from 'vue-material-design-icons/Barcode.vue'
import Check from 'vue-material-design-icons/Check.vue'
import Folder from 'vue-material-design-icons/Folder.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import MapMarker from 'vue-material-design-icons/MapMarker.vue'
import Tag from 'vue-material-design-icons/Tag.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Upload from 'vue-material-design-icons/Upload.vue'

import bwipjs from 'bwip-js'
import { vOnClickOutside as ClickOutside } from '@vueuse/components'
import { mapActions, mapGetters } from 'vuex'

export default {
	components: {
		NcActions,
		NcActionButton,
		EntityTable,
		RelationModal,
		InventoryIcon,
		ItemInstances,
		TagList,
		Attachments,
		NcBreadcrumbs,
		NcBreadcrumb,
		NcEmptyContent,
		NcLoadingIcon,
		NcSelect,
		Barcode,
		Check,
		Delete,
		Pencil,
		Plus,
		Upload,
		Folder,
		Magnify,
		MapMarker,
		Tag,
	},
	directives: {
		ClickOutside,
		focus,
	},
	mixins: [showBarcode],
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
					name: t('inventory', 'Name'),
				}, {
					key: 'maker',
					name: t('inventory', 'Maker'),
				}, {
					key: 'description',
					name: t('inventory', 'Description'),
				}, {
					key: 'itemNumber',
					name: t('inventory', 'Item number'),
				}, {
					key: 'link',
					name: t('inventory', 'Link'),
				}, {
					key: 'gtin',
					name: t('inventory', 'GTIN'),
				}, {
					key: 'details',
					name: t('inventory', 'Details'),
				}, {
					key: 'comment',
					name: t('inventory', 'Comment'),
				}, {
					key: 'tags',
					name: t('inventory', 'Tags'),
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
					path: `/${this.collection}/` + encodePath(crumbs.slice(0, i + 1).join('/')),
				}
			}))
			if (this.item && !this.loadingItem) {
				return breadcrumbs.concat([{
					title: this.item.description,
					path: `/${this.collection}/${(this.path) ? encodePath(this.path) + '/' : ''}item-${this.id}${(this.instanceId) ? `/instance-${this.instanceId}` : ''}`,
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
			return null
		},
	},
	created() {
		this.getItem(this.id)
		this.loadSubItems(this.id)
		this.loadParentItems(this.id)
		this.loadRelatedItems(this.id)
	},
	methods: {
		t,

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
		 *
		 * @param {string} value The string to show as barcode
		 * @param {string} type The barcode type
		 * @param {string} options The barcode options
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

		setTags(tags) {
			/**
			 * Probably due to a bug in VueSelect,
			 * tags are added as plain string when "options" are empty.
			 * We have to fix this here.
			 */
			this.editedItem.tags = tags.map(tag => {
				if (typeof tag === 'string') {
					return { name: tag }
				}
				return tag
			})
		},

		stopEditingItem($event) {
			if ($event.target.closest('.vs__dropdown-menu')) {
				return
			}
			this.editingItem = false
		},
		startEditingItem() {
			this.editingItem = true
			this.editedItem = this.item.response
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

<style lang="scss" scoped>
#itemdetails {
	padding-top: 10px;
	display: flex;
	flex-wrap: wrap;

	.table {
		white-space: unset;
		width: 100%;

		.row {
			display: grid;
			min-height: 44px;
			border-bottom: 1px solid var(--color-border-dark);
		}

		.column {
			display: flex;
			align-items: center;
			overflow: hidden;
			padding: 0 10px;

			&--actions {
				padding: 0;
			}
		}

		&--editing .column:last-child {
			padding-right: 0;
		}

		&--properties {

			.row {
				grid-template-columns: 110px 1fr;

				.column {
					&--width-2 {
						grid-column-start: 1;
						grid-column-end: 3;
					}

					&--attachments {
						padding-right: 0;
					}

					.wrapper {
						width: 100%;
						display: inline-flex;
						align-items: center;
						flex-wrap: wrap;

						span {
							max-width: 100%;

							a {
								word-wrap: break-word;
							}
						}

						.action-item {
							margin-right: -10px;
						}

						.v-select {
							width: 100%;
						}
					}
				}
			}

			.button {
				&--gtin,
				&--save {
					margin-left: auto;
				}
			}

			.input {
				width: 100%;

				input {
					width: 100%;
				}
			}
		}
	}

	.item_images {
		width: 100%;
		min-width: 200px;
		color: var(--color-placeholder-dark);

		img{
			width: 100%;
		}
		:deep(svg) {
			height: 100%;
			width: 100%;
		}
	}

	.paragraph {
		margin-bottom: 40px;
		width: 100%;

		&--properties {
			width: 100%;
			flex-grow: 1;
		}

		h3 {
			margin: 0 10px;
		}

		@media only screen and (min-width: 500px) {
			> div {
				padding: 0 10px;
			}

			&--properties {
				width: 60%;
			}

			&--images {
				width: 40%;
			}
		}
	}
}

.qrcode-modal .modal-container .qrcode {
	min-width: 200px;
	max-width: 100%;
	width: 400px;
	background-color: white;
	border-radius: var(--border-radius-large);
	padding: 4px;
	box-sizing: border-box;
}

.visibility-hidden {
	visibility: hidden;
	display: block;
	height: 0;
}
</style>
