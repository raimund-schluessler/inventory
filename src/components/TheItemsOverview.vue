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
			<Breadcrumbs :root-icon="`icon-bw icon-${collection}`" @dropped="moveEntities">
				<Breadcrumb v-for="(crumb, index) in breadcrumbs"
					:key="crumb.path"
					:title="crumb.title"
					:to="crumb.path"
					:disable-drop="index === (breadcrumbs.length - 1)" />
			</Breadcrumbs>
			<Actions default-icon="icon-add" :open.sync="actionsOpen" @close="addingCollection = false">
				<ActionRouter :to="`/${collection}/${($route.params.path) ? $route.params.path + '/' : ''}additems`" icon="icon-add">
					{{ t('inventory', 'Add items') }}
				</ActionRouter>
				<ActionButton v-if="!addingCollection"
					icon="icon-folder"
					@click.prevent.stop="openCollectionInput()">
					{{ addCollectionString }}
				</ActionButton>
				<ActionInput v-if="addingCollection"
					v-tooltip="{
						show: collectionNameError,
						content: errorString,
						trigger: 'manual',
					}"
					:class="{ 'error': collectionNameError }"
					icon="icon-folder"
					@submit="addCollection"
					@input="checkCollectionName">
					{{ collection === 'places' ? t('inventory', 'New Place') : t('inventory', 'New Folder') }}
				</ActionInput>
			</Actions>
		</div>
		<ItemsTable :items="items"
			:collections="collections"
			:collection-type="collection"
			:loading="loading"
			:show-dropdown="true"
			:search-string="$root.searchString" />
	</div>
</template>

<script>
import Item from '../models/item.js'
import { mapGetters, mapActions } from 'vuex'
import ItemsTable from './ItemsTable.vue'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionInput from '@nextcloud/vue/dist/Components/ActionInput'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import ActionRouter from '@nextcloud/vue/dist/Components/ActionRouter'
import Breadcrumbs from '@nextcloud/vue/dist/Components/Breadcrumbs'
import Breadcrumb from '@nextcloud/vue/dist/Components/Breadcrumb'

export default {
	components: {
		ItemsTable: ItemsTable,
		Breadcrumbs,
		Breadcrumb,
		Actions,
		ActionInput,
		ActionRouter,
		ActionButton,
	},
	props: {
		collection: {
			type: String,
			default: 'folders',
		},
	},
	data() {
		return {
			addingCollection: false,
			errorString: null,
			collectionNameError: false,
			actionsOpen: false,
		}
	},
	computed: {
		...mapGetters({
			items: 'getAllItems',
			folders: 'getFoldersByFolder',
			places: 'getPlacesByPlace',
			loading: 'loadingItems',
			draggedEntities: 'getDraggedEntities',
		}),

		breadcrumbs() {
			const path = this.$route.params.path
			const crumbs = (path === '') ? [] : path.split('/')
			return [{ title: t('inventory', 'Items'), path: `/${this.collection}/` }].concat(crumbs.map((crumb, i) => {
				return {
					title: crumb,
					path: `/${this.collection}/${crumbs.slice(0, i + 1).join('/')}`,
				}
			}))
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
	},
	watch: {
		collection(newVal, oldVal) {
			this.loadCollectionsAndItems(this.$route.params.path)
		},
	},
	created() {
		this.loadCollectionsAndItems(this.$route.params.path)
	},
	beforeRouteUpdate(to, from, next) {
		this.loadCollectionsAndItems(to.params.path)
		next()
	},
	methods: {
		...mapActions([
			'createFolder',
			'createPlace',
			'moveItem',
			'moveInstance',
			'moveFolder',
			'movePlace',
		]),

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

		async loadCollectionsAndItems(path) {
			if (this.collection === 'places') {
				await this.getPlacesByPlace(path)
				await this.getItemsByPlace(path)
			} else {
				await this.getFoldersByFolder(path)
				await this.getItemsByFolder(path)
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
