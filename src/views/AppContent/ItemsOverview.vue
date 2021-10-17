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
			<Breadcrumbs @dropped="moveEntities">
				<Breadcrumb v-for="(crumb, index) in breadcrumbs"
					:key="crumb.path"
					:title="crumb.title"
					:to="crumb.path"
					:disable-drop="index === (breadcrumbs.length - 1)">
					<template #icon>
						<component
							:is="breadcrumbIcon"
							v-if="index === 0"
							:size="20"
							decorative />
					</template>
				</Breadcrumb>
			</Breadcrumbs>
			<Actions
				container="#controls"
				:boundaries-element="boundaries"
				:open.sync="actionsOpen"
				@close="addingCollection = false">
				<ActionButton :close-after-click="true" @click="openQrModal()">
					<template #icon>
						<QrcodeScan :size="20" decorative />
					</template>
					{{ t('inventory', 'Scan QR code') }}
				</ActionButton>
				<ActionRouter :to="addItemPath">
					<template #icon>
						<Plus :size="20" decorative />
					</template>
					{{ t('inventory', 'Add items') }}
				</ActionRouter>
				<ActionButton v-if="!addingCollection"
					@click.prevent.stop="openCollectionInput()">
					<template #icon>
						<Folder :size="20" decorative />
					</template>
					{{ addCollectionString }}
				</ActionButton>
				<ActionInput v-if="addingCollection"
					v-tooltip="{
						show: collectionNameError,
						content: errorString,
						trigger: 'manual',
					}"
					icon=""
					:class="{ 'error': collectionNameError }"
					@submit="addCollection"
					@input="checkCollectionName">
					<template #icon>
						<Folder :size="20" decorative />
					</template>
					{{ collection === 'places' ? t('inventory', 'New Place') : t('inventory', 'New Folder') }}
				</ActionInput>
			</Actions>
		</div>
		<EntityTable :items="items"
			:collections="collections"
			:collection-type="collection"
			:search-string="$root.searchString" />
		<!-- qrcode -->
		<QrScanModal :qr-modal-open.sync="qrModalOpen" @recognized-qr-code="foundUuid" />
	</div>
</template>

<script>
import Item from '../../models/item'
import EntityTable from '../../components/EntityTable/EntityTable'
import QrScanModal from '../../components/QrScanModal'
import { encodePath } from '../../utils/encodePath'

import { translate as t } from '@nextcloud/l10n'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionInput from '@nextcloud/vue/dist/Components/ActionInput'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import ActionRouter from '@nextcloud/vue/dist/Components/ActionRouter'
import Breadcrumbs from '@nextcloud/vue/dist/Components/Breadcrumbs'
import Breadcrumb from '@nextcloud/vue/dist/Components/Breadcrumb'

import Folder from 'vue-material-design-icons/Folder'
import Plus from 'vue-material-design-icons/Plus'
import QrcodeScan from 'vue-material-design-icons/QrcodeScan'
import MapMarker from 'vue-material-design-icons/MapMarker'
import Tag from 'vue-material-design-icons/Tag'

import { mapGetters, mapActions } from 'vuex'

export default {
	components: {
		Actions,
		ActionInput,
		ActionRouter,
		ActionButton,
		Breadcrumb,
		Breadcrumbs,
		EntityTable,
		QrScanModal,
		Folder,
		Plus,
		QrcodeScan,
		MapMarker,
		Tag,
	},
	beforeRouteUpdate(to, from, next) {
		this.loadCollectionsAndItems(to.params.path)
		next()
	},
	props: {
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
			collectionNameError: false,
			errorString: null,
			qrModalOpen: false,
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
			const path = this.$route.params.path
			const crumbs = (path === '') ? [] : path.split('/')
			return [{ title: t('inventory', 'Items'), path: `/${this.collection}/` }].concat(crumbs.map((crumb, i) => {
				return {
					title: crumb,
					path: `/${this.collection}/${encodePath(crumbs.slice(0, i + 1).join('/'))}`,
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
			const encodedPath = encodePath(this.$route.params.path)
			return `/${this.collection}/${(encodedPath) ? encodedPath + '/' : ''}&additems`
		},
	},
	watch: {
		collection(newVal, oldVal) {
			this.loadCollectionsAndItems(this.$route.params.path)
		},
	},
	created() {
		this.loadCollectionsAndItems(this.$route.params.path)
	},
	methods: {
		t,

		...mapActions([
			'createFolder',
			'createPlace',
			'moveItem',
			'moveInstance',
			'moveFolder',
			'movePlace',
			'searchByUUID',
		]),

		openQrModal() {
			this.qrModalOpen = true
		},

		async foundUuid(uuid) {
			const response = await this.searchByUUID(uuid)
			if (response.length) {
				this.qrModalOpen = false
				const item = response[0]
				this.$router.push(`/folders/${item.path}/item-${item.id}`)
			}
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
			if (this.collection === 'places') {
				this.getPlacesByPlace(path)
				this.getItemsByPlace(path)
			} else {
				this.getFoldersByFolder(path)
				this.getItemsByFolder(path)
			}
		},

		openCollectionInput() {
			this.addingCollection = !this.addingCollection
		},

		async addCollection(event) {
			if (this.collectionNameError) {
				return
			}
			const name = event.target.querySelector('input[type=text]').value
			if (this.collection === 'places') {
				await this.createPlace({ name, path: this.$route.params.path })
			} else {
				await this.createFolder({ name, path: this.$route.params.path })
			}
			this.addingCollection = false
			this.actionsOpen = false
		},

		checkCollectionName(event) {
			const newName = event.target.value
			if (newName === '') {
				this.collectionNameError = true
				this.errorString = this.collection === 'places'
					? t('inventory', 'Place name cannot be empty.')
					: t('inventory', 'Folder name cannot be empty.')
			} else if (newName.includes('/')) {
				this.collectionNameError = true
				this.errorString = this.collection === 'places'
					? t('inventory', '"/" is not allowed inside a place name.')
					: t('inventory', '"/" is not allowed inside a folder name.')
			} else {
				this.collectionNameError = false
				this.errorString = null
			}
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
