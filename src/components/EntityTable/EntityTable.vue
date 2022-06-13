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
		<div class="entitytable" @dragover="dragOverTable">
			<div :class="{ 'row--has-status': oneEntityHasStatus }" class="row row--header">
				<div :id="`headerSelection-${_uid}`" class="column column--selection">
					<input :id="`select_all_items-${_uid}`"
						:checked="allEntitiesSelected"
						:indeterminate.prop="someEntitiesSelected"
						class="select-all checkbox"
						type="checkbox">
					<label :for="`select_all_items-${_uid}`" @click.prevent="selectEntities">
						<span class="hidden-visually">
							{{ t('inventory', 'Select all') }}
						</span>
					</label>
				</div>
				<div class="column column--name">
					<div class="sort" data-sort="name" @click="setSortOrder('name')">
						<span>{{ t('inventory', 'Name') }}</span>
						<span v-show="sortOrder === 'name'"
							:class="sortOrderIcon"
							class="sort-indicator" />
					</div>
				</div>
				<div class="column">
					<div class="sort" data-sort="maker" @click="setSortOrder('maker')">
						<span>{{ t('inventory', 'Maker') }}</span>
						<span v-show="sortOrder === 'maker'"
							:class="sortOrderIcon"
							class="sort-indicator" />
					</div>
				</div>
				<div class="column">
					<div class="sort" data-sort="description" @click="setSortOrder('description')">
						<span>{{ t('inventory', 'Description') }}</span>
						<span v-show="sortOrder === 'description'"
							:class="sortOrderIcon"
							class="sort-indicator" />
					</div>
				</div>
				<div class="column column--hide">
					<div>
						<span>{{ t('inventory', 'Tags') }}</span>
					</div>
				</div>
				<div class="column column--actions">
					<Actions :boundaries-element="boundaries">
						<ActionButton v-if="allowDeletion && !unlink && selectedItems.length"
							:close-after-click="true"
							@click="removeItems">
							<template #icon>
								<Delete :size="20" />
							</template>
							{{ n('inventory', 'Delete item', 'Delete items', selectedItems.length) }}
						</ActionButton>
						<ActionButton v-if="unlink && selectedItems.length"
							icon="icon-close"
							:close-after-click="true"
							@click="$emit('unlink')">
							<template #icon>
								<Close :size="20" />
							</template>
							{{ n('inventory', 'Unlink item', 'Unlink items', selectedItems.length) }}
						</ActionButton>
					</Actions>
				</div>
			</div>
			<EntityTableRowPlaceholder :placeholder="placeholder('upper')" />
			<component :is="entityType(item)"
				v-for="item in sort(filteredEntities, sortOrder, sortDirection)"
				:key="item.key"
				:entity="item"
				:is-selected="isSelected(item)"
				:collection="collectionType"
				:class="{ 'dragged': isDragged(item) }"
				:select-entity="selectItem"
				:uuid="_uid"
				:mode="mode"
				draggable="true"
				class="entity"
				@selectItem="selectItem"
				@dragstart.native="dragStart(item, $event)"
				@dragend.native="dragEnd"
				@drop.native="dropped(item, $event)"
				@dragover.native="dragOver"
				@dragenter.native="($event) => dragEnter(item, $event)"
				@dragleave.native="dragLeave" />
			<EntityTableRowPlaceholder :placeholder="placeholder('lower')" />
			<div v-if="searchString && !filterOnly" class="row row--search">
				<div class="column" :class="{'column__left': !searching}">
					<span v-if="searching" class="icon-loading" />
					<span>{{ searchMessage }}</span>
				</div>
			</div>
			<component :is="entityType(item)"
				v-for="item in sort(searchResults, sortOrder, sortDirection)"
				:key="`${item.key}_search`"
				:entity="item"
				:is-selected="isSelected(item)"
				:collection="collectionType"
				:class="{ 'dragged': isDragged(item) }"
				:select-entity="selectItem"
				:uuid="_uid"
				:mode="mode"
				:show-actions="false" />
		</div>
		<div id="drag-preview" class="entitytable entitytable--drag-preview">
			<div v-for="entity in draggedEntities" :key="entity.key" class="row">
				<div class="column">
					<a href="" @click.ctrl.prevent>
						<div class="thumbnail">
							<div v-if="entityType(entity) === 'Collection'"
								:style="{ backgroundImage: `url(${generateUrl('apps/theming/img/core/filetypes/folder.svg?v=17')})` }"
								class="thumbnail__image folder" />
							<div v-else
								:style="{ backgroundImage: `url(${getIconUrl(entity)})` }"
								class="thumbnail__image"
								:class="{'thumbnail__image--default': !entity.images.length}" />
						</div>
						<div class="text">
							<span>{{ entity.name }}</span>
						</div>
					</a>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import ItemComponent from './Item.vue'
import Collection from './Collection.vue'
import EntityTableRowPlaceholder from './EntityTableRowPlaceholder.vue'
import Item from '../../models/item.js'
import Folder from '../../models/folder.js'
import Place from '../../models/place.js'
import { sort } from '../../store/storeHelper.js'

import { translate as t, translatePlural as n } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'

import Close from 'vue-material-design-icons/Close'
import Delete from 'vue-material-design-icons/Delete'

import searchQueryParser from 'search-query-parser'
import { mapActions, mapMutations, mapGetters } from 'vuex'

export default {
	components: {
		ItemComponent,
		Collection,
		Actions,
		ActionButton,
		EntityTableRowPlaceholder,
		Close,
		Delete,
	},
	props: {
		mode: {
			type: String,
			default: 'navigation',
		},
		collections: {
			type: Array,
			default: () => [],
		},
		collectionType: {
			type: String,
			default: 'folders',
		},
		items: {
			type: Array,
			default: () => [],
			required: true,
		},
		allowDeletion: {
			type: Boolean,
			default: true,
		},
		unlink: {
			type: Boolean,
			default: false,
		},
		searchString: {
			type: String,
			default: '',
		},
		/*
			Don't initialize a server side serach if true.
		*/
		filterOnly: {
			type: Boolean,
			default: false,
		},
	},
	data() {
		return {
			selectedEntities: [],
			draggedEntities: [],
			// Hack to fix https://github.com/nextcloud/nextcloud-vue/issues/1384
			boundaries: document.querySelector('#content-vue'),
		}
	},
	computed: {
		...mapGetters([
			'searching',
			'searchResults',
			'loadingItems',
			'loadingFolders',
			'loadingPlaces',
		]),

		loadingCollections() {
			return this.loadingFolders || this.loadingPlaces
		},

		allEntitiesSelected() {
			return this.selectedEntities.length === (this.items.length + this.collections.length) && this.selectedEntities.length > 0
		},

		someEntitiesSelected() {
			return !this.allEntitiesSelected && this.selectedEntities.length > 0
		},

		oneEntityHasStatus() {
			return this.filteredEntities.some(item => item.syncStatus)
		},

		selectedItems: {
			set(items) {
				for (let i = 0; i < this.selectedEntities.length; i++) {
					if (this.selectedEntities[i] instanceof Item && !items.includes(this.selectedEntities[i])) {
						this.selectedEntities.splice(i, 1)
						i--
					}
				}
			},
			get() {
				return this.selectedEntities.filter(entity => {
					return (entity instanceof Item)
				})
			},
		},
		filteredEntities() {
			if (!this.searchString) {
				return this.items.concat(this.collections)
			}

			const options = { keywords: ['maker', 'name', 'description', 'tags', 'itemNumber', 'gtin', 'details', 'comment'] }

			let searchQueryObj = searchQueryParser.parse(this.searchString, options)
			// bring into same structure if no keywords were matched
			if (Object.prototype.toString.call(searchQueryObj) === '[object String]') {
				searchQueryObj = { text: searchQueryObj }
			}

			if (Object.prototype.hasOwnProperty.call(searchQueryObj, 'text')) {
				// split strings at whitespace, except when in quotes
				// Note: The regex is not optimal yet and works mostly for English and German.
				// It should rather read something like (/[\p{L}\d]+|"(?:\\"|[^"])+"/g),
				// but \p{L} is not supported in ECMA script by now.
				// Could be overcome by http://xregexp.com/
				searchQueryObj.searchTerms = searchQueryObj.text.match(/[\wäöüß]+|"(?:\\"|[^"])+"/g)
			}

			const filteredCollections = this.collections.filter(collection => {
				for (let jj = 0; jj < searchQueryObj.searchTerms.length; jj++) {
					if (collection.name.toLowerCase().indexOf(searchQueryObj.searchTerms[jj].toLowerCase()) > -1) {
						return true
					}
				}
				return false
			})

			const filteredItems = this.items.filter(item => {
				let keyword
				let found = false
				for (let i = 0; i < options.keywords.length; i++) {
					keyword = options.keywords[i]
					// check if keywords were given, if yes, check if value is found
					if (Object.prototype.hasOwnProperty.call(searchQueryObj, keyword)) {
						if (!item[keyword]) {
							return false
						}
						if (keyword === 'tags') {
							found = false
							for (let jj = 0; jj < item.tags.length; jj++) {
								if (item.tags[jj].name.toLowerCase().indexOf(searchQueryObj[keyword].toLowerCase()) > -1) {
									found = true
									break
								}
							}
							if (!found) {
								return false
							}
						} else {
							if (item[keyword].toLowerCase().indexOf(searchQueryObj[keyword].toLowerCase()) < 0) {
								return false
							}
						}
					}
				}

				// check if text is matched
				if (Object.prototype.hasOwnProperty.call(searchQueryObj, 'searchTerms')) {
					// console.log(searchQueryObj);
					for (let jj = 0; jj < searchQueryObj.searchTerms.length; jj++) {
						found = false
						for (let i = 0; i < options.keywords.length; i++) {
							keyword = options.keywords[i]
							if (!item[keyword]) {
								continue
							}
							if (keyword === 'tags') {
								for (let kk = 0; kk < item.tags.length; kk++) {
									if (item.tags[kk].name.toLowerCase().indexOf(searchQueryObj.searchTerms[jj].toLowerCase()) > -1) {
										found = true
										break
									}
								}
							} else {
								if (item[keyword].toLowerCase().indexOf(searchQueryObj.searchTerms[jj].toLowerCase()) > -1) {
									found = true
									break
								}
							}
						}
						if (!found) {
							return false
						}
					}
				}
				return true
			})

			return filteredItems.concat(filteredCollections)
		},
		upperPlaceholderMessage() {
			if (this.loadingFolders && this.loadingItems) {
				return t('inventory', 'Loading folders and items from server.')
			} else if (this.loadingPlaces && this.loadingItems) {
				return t('inventory', 'Loading places and items from server.')
			} else if (this.loadingFolders) {
				return t('inventory', 'Loading folders from server.')
			} else if (this.loadingPlaces) {
				return t('inventory', 'Loading places from server.')
			} else if (this.searchString && this.items.length && !this.filterOnly) {
				return t('inventory', 'No item found.')
			} else if (this.searchString && this.items.length) {
				return t('inventory', 'No item matches the filter.')
			} else {
				return t('inventory', 'The item list is empty.')
			}
		},
		searchMessage() {
			if (this.searching) {
				return t('inventory', 'Searching in other folders.')
			} else if (!this.searchResults.length) {
				return t('inventory', 'No items found in other folders.')
			} else {
				return t('inventory', 'Found in other folders:')
			}
		},
		sortOrder: {
			get() {
				return this.$store.getters.sortOrder
			},
			set(order) {
				this.$store.dispatch('setSetting', { type: 'sortOrder', value: order })
			},
		},
		sortDirection: {
			get() {
				return this.$store.getters.sortDirection
			},
			set(direction) {
				this.$store.dispatch('setSetting', { type: 'sortDirection', value: +direction })
			},
		},
		sortOrderIcon() {
			return `icon-triangle-${this.sortDirection ? 's' : 'n'}`
		},
	},
	watch: {
		items: 'checkSelected',
		collections: 'checkSelected',
		searchString(newVal, oldVal) {
			if (newVal && !this.filterOnly) {
				this.$store.dispatch('search', newVal)
			}
		},
	},
	methods: {
		t,
		n,

		...mapActions([
			'deleteItems',
			'unlinkItems',
			'moveItem',
			'moveInstance',
			'moveFolder',
			'movePlace',
			'search',
		]),

		...mapMutations([
			'setDraggedEntities',
		]),

		entityKey(entity) {
			const key = `${this.entityType(entity)}_${entity.id}`
			if (entity instanceof Item) {
				return key + `${(this.collectionType === 'places' && entity.instances.length > 0 ? entity.instances[0].id : '')}`
			}
			return key
		},

		selectEntities() {
			/**
			 * If the checkbox is checked, we select all entities which are visible
			 * (all filtered entities).
			 *
			 * Otherwise we deselect ALL entities.
			 */
			if (this.allEntitiesSelected) {
				this.selectedEntities = []
			} else {
				// add all filteredEntities to selectedEntities
				for (let i = 0; i < this.filteredEntities.length; i++) {
					const index = this.selectedEntities.indexOf(this.filteredEntities[i])
					if (index === -1) {
						this.selectedEntities.push(this.filteredEntities[i])
					}
				}
			}
			/**
			 * Emits that the selected items have changed
			 */
			this.$emit('selected-items-changed', this.selectedItems)
		},

		placeholder(position) {
			/**
			 * Upper row placeholder message (lower if sortDirection is true)
			 */
			if ((position === 'upper' && !this.sortDirection) || (position === 'lower' && this.sortDirection)) {
				return {
					show: !(this.filteredEntities.length || this.loadingItems) || this.loadingCollections,
					loading: this.loadingCollections,
					message: this.upperPlaceholderMessage,
				}
			} else {
				return {
					show: !this.loadingCollections && this.loadingItems,
					loading: true,
					message: t('inventory', 'Loading items from server.'),
				}
			}

		},

		generateUrl,

		entityType(entity) {
			return (entity instanceof Item) ? 'ItemComponent' : 'Collection'
		},

		/**
		 * Check for every item in the selectedEntities array
		 * whether it is still in the items array.
		 * If not, remove from selected.
		 */
		checkSelected() {
			const before = this.selectedEntities.length
			this.selectedEntities = this.selectedEntities.filter((entity) => {
				if (entity instanceof Item) {
					return (this.items.indexOf(entity) > -1)
				}
				if (entity instanceof Folder || entity instanceof Place) {
					return (this.collections.indexOf(entity) > -1)
				}
				return true
			})
			if (before !== this.selectedEntities.length) {
				this.$emit('selected-items-changed', this.selectedItems)
			}
		},

		getIconUrl(item) {
			if (item.images.length > 0) {
				const img = item.images[0]
				return generateUrl(`/core/preview?fileId=${img.fileid}&x=${128}&y=${128}&a=false&v=${img.etag}`)
			} else if (item.iconurl) {
				return item.iconurl
			} else {
				let color = '000'
				if (OCA.Accessibility) {
					color = (OCA.Accessibility.theme === 'dark' ? 'fff' : '000')
				}
				return generateUrl(`svg/inventory/item_${item.icon}?color=${color}`)
			}
		},
		selectItem(item) {
			if (this.isSelected(item)) {
				const index = this.selectedEntities.indexOf(item)
				if (index !== -1) {
					this.selectedEntities.splice(index, 1)
				}
			} else {
				this.selectedEntities.push(item)
			}
			this.$emit('selected-items-changed', this.selectedItems)
		},
		isSelected(item) {
			return this.selectedEntities.includes(item)
		},
		async removeItems() {
			await this.deleteItems(this.selectedItems)
			this.selectedItems = []
		},
		sort,
		setSortOrder(order) {
			// If the sort order was already set, toggle the sort direction, otherwise reset it.
			this.sortDirection = (this.sortOrder === order) ? !this.sortDirection : false
			this.sortOrder = order
		},

		/**
		 * Drag and drop handlers
		 */

		/**
		 * Handler for starting the drag operation
		 *
		 * @param {object} entity The dragged item or folder
		 * @param {object} e The dragStart event
		 */
		dragStart(entity, e) {
			if (this.selectedEntities.length > 0) {
				// We want a copy, not a reference
				this.draggedEntities = sort([...this.selectedEntities], this.sortOrder, this.sortDirection)
			} else {
				this.draggedEntities.push(entity)
			}
			this.setDraggedEntities(this.draggedEntities)
			e.dataTransfer.setData('text/plain', 'dragging')
			const dragHelper = document.getElementById('drag-preview')
			e.dataTransfer.setDragImage(dragHelper, 10, 10)
		},
		dragEnd(e) {
			this.draggedEntities = []
			this.setDraggedEntities(this.draggedEntities)
			const collections = document.querySelectorAll('.over')
			collections.forEach((f) => { f.classList.remove('over') })
		},
		dragOverTable(e) {
			const posY = e.clientY
			const pageHeight = document.body.clientHeight
			let halfTableHeight = (pageHeight - 94) / 2
			halfTableHeight = (halfTableHeight > 150) ? 150 : halfTableHeight
			const speed = 15
			if (posY < halfTableHeight + 94) {
				window.scrollBy(0, -1 * speed)
			} else if (pageHeight - posY < halfTableHeight) {
				window.scrollBy(0, speed)
			}
		},
		dropped(targetEntity, e) {
			e.stopPropagation()
			e.preventDefault()
			if (this.entityType(targetEntity) === 'Collection' && !this.isDragged(targetEntity)) {
				this.draggedEntities.forEach((entity) => {
					if (entity instanceof Item) {
						if (entity.isInstance) {
							this.moveInstance({ itemID: entity.id, instanceID: entity.instances[0].id, newPath: targetEntity.path })
						} else {
							this.moveItem({ itemID: entity.id, newPath: targetEntity.path })
						}
					} else {
						if (this.collectionType === 'places') {
							this.movePlace({ placeID: entity.id, newPath: targetEntity.path })
						} else {
							this.moveFolder({ folderID: entity.id, newPath: targetEntity.path })
						}
					}
				})
			}
			return false
		},
		dragOver(e) {
			if (e.preventDefault) {
				e.preventDefault()
			}
			return false
		},
		dragEnter(entity, e) {
			// We don't add the hover state if
			// the entity itself is dragged or is not a folder.
			if (this.isDragged(entity) || this.entityType(entity) !== 'Collection') {
				return
			}
			// Get the correct element, in case we hover a child.
			if (e.target.closest) {
				const target = e.target.closest('.entity')
				if (target.classList && target.classList.contains('entity')) {
					const collections = document.querySelectorAll('.over')
					collections.forEach((f) => { f.classList.remove('over') })
					target.classList.add('over')
				}
			}
		},
		dragLeave(e) {
			// Don't do anything if we leave towards a child element.
			if (e.target.contains(e.relatedTarget)) {
				return
			}
			// Get the correct element, in case we leave directly from a child.
			if (e.target.closest) {
				const target = e.target.closest('.entity')
				if (target.contains(e.relatedTarget)) {
					return
				}
				if (target.classList && target.classList.contains('entity')) {
					target.classList.remove('over')
				}
			}
		},
		isDragged(item) {
			return this.draggedEntities.includes(item)
		},
	},
}
</script>

<style lang="scss" scoped>
.entitytable {
	position: relative;
	white-space: unset;
	width: 100%;
	min-width: 300px;

	&--drag-preview {
		max-width: 150px;
		position: absolute;
		left: -9999px;
		top: -9999px;

		&.entitytable .row {
			max-width: 150px;
			grid-template-columns: 150px;
			background-color: var(--color-border-dark);

			&:first-child {
				border-top: 1px solid var(--color-border-dark);
			}

			.column > a {
				.thumbnail {
					margin-left: 0;
				}

				.text span {
					height: 22px;
					white-space: nowrap;
				}
			}
		}
	}

	::v-deep .row {
		display: grid;
		grid-template-columns: 44px 2fr 1fr 3fr 1fr;
		height: 44px;
		transition: background-color .3s ease;
		border-bottom: 1px solid var(--color-border-dark);
		@media only screen and (max-width: 500px) {
			grid-template-columns: 44px 2fr 1fr 3fr;

			&--has-status {
				grid-template-columns: 44px 2fr 1fr 3fr 44px;
			}

			.column.column--hide {
				display: none;
			}
		}

		&:hover {
			background-color: var(--color-background-dark);
		}

		&--has-status {
			grid-template-columns: 44px 2fr 1fr 3fr 1fr 44px;
		}

		&--header {
			position: sticky;
			top: 94px;
			height: 50px;
			background-color: var(--color-main-background-translucent);
			z-index: 55;

			&:hover {
				background-color: var(--color-main-background-translucent);
			}

			.column {
				display: flex;
				align-items: center;

				&--name > div {
					padding-left: 40px;
				}

				&--actions {
					position: absolute;
					right: 0;
					top: 3px;
				}

				& > div {
					display: inline-flex;
					margin-right: auto;
					overflow: hidden;

					span:not(.sort-indicator) {
						overflow: hidden;
						text-overflow: ellipsis;
					}

					.sort-indicator {
						display: inline-block;
					}
				}
			}
		}

		&--collection {
			grid-template-columns: 44px 1fr 44px;
		}

		&--empty,
		&--search {
			grid-template-columns: 1fr;

			.column {
				justify-content: center;

				&__left {
					justify-content: left;
					padding: 0 14px;
				}

				.icon-loading {
					display: inline-block;
					width: 44px;
				}
			}
		}

		&--search {
			height: 50px;
			font-weight: bold;

			&:hover {
				background-color: unset;
			}
		}

		&--selected {
			background-color: var(--color-background-darker) !important;
		}

		&--deleted {
			text-decoration: line-through;

			.thumbnail {
				opacity: .3 !important;
			}
		}

		.column {
			display: flex;
			align-items: center;
			overflow: hidden;
			text-overflow: ellipsis;

			&--actions {
				overflow: unset;
			}

			&--selection {
				display: inline-flex;
				justify-content: center;

				input[type='checkbox'].checkbox + label::before {
					margin: 0;
					margin-top: 5px;
					vertical-align: unset;
				}
			}

			& > a {
				display: flex;
				align-items: center;
				height: 44px;
				max-width: 100%;

				.thumbnail {
					height: 44px;
					width: 44px;
					margin-left: -4px;

					&__image {
						height: 36px;
						width: 36px;
						margin: 4px;
						background-size: 36px;
						background-repeat: no-repeat;

						&--default {
							opacity: .57;
						}
					}
				}

				.text {
					display: flex;
					height: 44px;
					flex-direction: column;
					align-items: normal;
					justify-content: center;
					overflow: hidden;

					&--singleline span {
						height: 22px;
						white-space: nowrap;
					}

					span {
						overflow: hidden;
						text-overflow: ellipsis;

						&.details {
							font-size: 12px;
							color: var(--color-text-lighter);
						}
					}
				}
			}

			form,
			input {
				width: 100%;
			}
		}
	}
}
</style>
