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
		<div id="controls">
			<NcBreadcrumbs @dropped="moveEntities">
				<NcBreadcrumb v-for="(crumb, index) in breadcrumbs"
					:key="crumb.path"
					:title="crumb.title"
					:to="crumb.path"
					:disable-drop="index === (breadcrumbs.length - 1)">
					<template #icon>
						<component :is="breadcrumbIcon"
							v-if="index === 0"
							:size="20" />
					</template>
				</NcBreadcrumb>
			</NcBreadcrumbs>
			<NcActions :boundaries-element="boundaries"
				:open.sync="actionsOpen"
				@close="addingCollection = false">
				<NcActionButton :close-after-click="true" @click="openQrModal('search')">
					<template #icon>
						<QrcodeScan :size="20" />
					</template>
					{{ t('inventory', 'Scan QR code') }}
				</NcActionButton>
				<NcActionButton v-if="collection === 'places'"
					:close-after-click="true"
					@click="openQrModal('move')">
					<template #icon>
						<QrcodePlus :size="20" />
					</template>
					{{ t('inventory', 'Move items to place') }}
				</NcActionButton>
				<NcActionRouter :to="addItemPath">
					<template #icon>
						<Plus :size="20" />
					</template>
					{{ t('inventory', 'Add items') }}
				</NcActionRouter>
				<NcActionRouter v-if="collection === 'places'"
					:close-after-click="true"
					:to="`/${collection}/${(path) ? encodePath(path) + '/' : ''}&details`">
					<template #icon>
						<InformationOutline :size="20" />
					</template>
					{{ t('inventory', 'Show details') }}
				</NcActionRouter>
				<NcActionButton v-if="!addingCollection"
					@click.prevent.stop="openCollectionInput">
					<template #icon>
						<Folder :size="20" />
					</template>
					{{ addCollectionString }}
				</NcActionButton>
				<NcActionInput v-if="addingCollection"
					:value.sync="newCollectionName"
					:helper-text="collectionNameErrorString"
					:error="collectionNameError"
					@submit="addCollection">
					<template #icon>
						<Folder :size="20" />
					</template>
				</NcActionInput>
			</NcActions>
		</div>
		<EntityTable :items="items"
			:collections="collections"
			:collection-type="collection" />
		<!-- qrcode -->
		<QrScanModal :qr-modal-open.sync="qrModalOpen" :status-string="statusMessage" @recognized-qr-code="foundUuid" />
	</div>
</template>

<script>
import Item from '../../models/item.js'
import Place from '../../models/place.js'
import EntityTable from '../../components/EntityTable/EntityTable.vue'
import QrScanModal from '../../components/QrScanModal.vue'
import { encodePath } from '../../utils/encodePath.js'

import { translate as t } from '@nextcloud/l10n'
import {
	NcActions,
	NcActionInput,
	NcActionButton,
	NcActionRouter,
	NcBreadcrumbs,
	NcBreadcrumb,
} from '@nextcloud/vue'

import Folder from 'vue-material-design-icons/Folder.vue'
import InformationOutline from 'vue-material-design-icons/InformationOutline.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import QrcodePlus from 'vue-material-design-icons/QrcodePlus.vue'
import QrcodeScan from 'vue-material-design-icons/QrcodeScan.vue'
import MapMarker from 'vue-material-design-icons/MapMarker.vue'
import Tag from 'vue-material-design-icons/Tag.vue'

import { mapGetters, mapActions } from 'vuex'

export default {
	components: {
		NcActions,
		NcActionInput,
		NcActionRouter,
		NcActionButton,
		NcBreadcrumb,
		NcBreadcrumbs,
		EntityTable,
		QrScanModal,
		Folder,
		Plus,
		InformationOutline,
		QrcodePlus,
		QrcodeScan,
		MapMarker,
		Tag,
	},
	beforeRouteUpdate(to, from, next) {
		if (to.params.path !== from.params.path) {
			this.loadCollectionsAndItems(to.params.path || '')
		}
		next()
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
			actionsOpen: false,
			addingCollection: false,
			// Hack to fix https://github.com/nextcloud/nextcloud-vue/issues/1384
			boundaries: document.querySelector('#content-vue'),
			newCollectionName: '',
			qrModalOpen: false,
			qrTarget: '',
			statusMessage: '',
			resetStatusTimeout: null,
			abortController: null,
		}
	},
	computed: {
		...mapGetters({
			items: 'getAllItems',
			folders: 'getFoldersByFolder',
			places: 'getPlacesByPlace',
			draggedEntities: 'getDraggedEntities',
		}),

		breadcrumbs() {
			const path = this.path
			const crumbs = (path === '') ? [] : path.split('/')
			return [{ title: t('inventory', 'Items'), path: `/${this.collection}/` }].concat(crumbs.map((crumb, i) => {
				return {
					title: crumb,
					path: `/${this.collection}/${crumbs.slice(0, i + 1).join('/')}`,
				}
			}))
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

		collections() {
			if (this.collection === 'places') {
				return this.places
			}
			return this.folders
		},

		addCollectionString() {
			if (this.collection === 'places') {
				return t('inventory', 'New Place')
			}
			return t('inventory', 'New Folder')
		},
		addItemPath() {
			const encodedPath = encodePath(this.path)
			return `/${this.collection}/${(encodedPath) ? encodedPath + '/' : ''}&additems`
		},

		collectionNameError() {
			return !!this.collectionNameErrorString
		},

		collectionNameErrorString() {
			if (this.newCollectionName === '') {
				return this.collection === 'places'
					? t('inventory', 'Place name cannot be empty.')
					: t('inventory', 'Folder name cannot be empty.')
			}
			if (this.newCollectionName.includes('/')) {
				return this.collection === 'places'
					? t('inventory', '"/" is not allowed inside a place name.')
					: t('inventory', '"/" is not allowed inside a folder name.')
			}
			return ''
		},
	},
	watch: {
		collection(newVal, oldVal) {
			if (newVal !== oldVal) {
				this.loadCollectionsAndItems(this.path)
			}
		},
	},
	created() {
		this.loadCollectionsAndItems(this.path)
	},
	beforeDestroy() {
		// Abort possibly running requests
		this.abortController?.abort()
	},
	methods: {
		t,
		encodePath,

		...mapActions([
			'createFolder',
			'createPlace',
			'moveItem',
			'moveItemByUuid',
			'moveInstance',
			'moveFolder',
			'movePlace',
			'searchByUUID',
		]),

		openQrModal(qrTarget) {
			this.qrTarget = qrTarget
			this.qrModalOpen = true
		},

		async foundUuid(uuid) {
			if (this.qrTarget === 'search') {
				const response = await this.searchByUUID(uuid)
				if (response.length) {
					this.qrModalOpen = false
					const entity = response[0]
					if (entity instanceof Item) {
						this.$router.push(`/folders/${encodePath(entity.path)}/item-${entity.id}`)
					} else if (entity instanceof Place) {
						this.$router.push(`/places/${encodePath(entity.path)}/&details`)
					}
				}
			} else if (this.qrTarget === 'move' && this.collection === 'places') {
				const item = await this.moveItemByUuid({ newPath: this.path, uuid })
				this.setStatusMessage(t('inventory', 'Item "{item}" moved', { item: item?.data?.name }))
			}
		},

		setStatusMessage(message) {
			this.statusMessage = message
			if (this.resetStatusTimeout) {
				clearTimeout(this.resetStatusTimeout)
			}
			this.resetStatusTimeout = setTimeout(
				() => {
					this.statusMessage = ''
				}, 3000
			)
		},

		moveEntities(e, newPath) {
			newPath = newPath.replace(`/${this.collection}/`, '')
			this.draggedEntities.forEach((entity) => {
				if (entity instanceof Item) {
					if (entity.isInstance) {
						this.moveInstance({ itemID: entity.id, instanceID: entity.instances[0].id, newPath })
					} else {
						this.moveItem({ itemID: entity.id, newPath })
					}
				} else {
					if (this.collection === 'places') {
						this.movePlace({ placeID: entity.id, newPath })
					} else {
						this.moveFolder({ folderID: entity.id, newPath })
					}
				}
			})
		},

		loadCollectionsAndItems(path) {
			// Abort possibly running requests from previous paths
			this.abortController?.abort()
			this.abortController = new AbortController()
			if (this.collection === 'places') {
				this.getPlacesByPlace({ path, signal: this.abortController.signal })
				this.getItemsByPlace({ path, signal: this.abortController.signal })
			} else {
				this.getFoldersByFolder({ path, signal: this.abortController.signal })
				this.getItemsByFolder({ path, signal: this.abortController.signal })
			}
		},

		openCollectionInput() {
			this.addingCollection = !this.addingCollection
		},

		async addCollection(event) {
			if (this.collectionNameError) {
				return
			}
			if (this.collection === 'places') {
				await this.createPlace({ name: this.newCollectionName, path: this.path })
			} else {
				await this.createFolder({ name: this.newCollectionName, path: this.path })
			}
			this.addingCollection = false
			this.actionsOpen = false
		},

		...mapActions([
			'getFoldersByFolder',
			'getItemsByFolder',
			'getPlacesByPlace',
			'getItemsByPlace',
		]),
	},
}
</script>
