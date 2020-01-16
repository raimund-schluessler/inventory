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
		<table class="itemstable">
			<thead>
				<tr>
					<th :id="`headerSelection-${_uid}`" class="column-selection">
						<input :id="`select_all_items-${_uid}`" v-model="allVisibleItemsSelected" class="select-all checkbox"
							type="checkbox"
						>
						<label :for="`select_all_items-${_uid}`">
							<span class="hidden-visually">
								{{ t('inventory', 'Select all') }}
							</span>
						</label>
					</th>
					<th>
						<div>
							<a class="name sort columntitle" data-sort="name" @click="setSortOrder('name')">
								<span>{{ t('inventory', 'Name') }}</span>
								<span v-show="sortOrder === 'name'"
									:class="sortOrderIcon('name')" class="sort-indicator"
								/>
							</a>
						</div>
					</th>
					<th>
						<div>
							<a class="maker sort columntitle" data-sort="maker" @click="setSortOrder('maker')">
								<span>{{ t('inventory', 'Maker') }}</span>
								<span v-show="sortOrder === 'maker'"
									:class="sortOrderIcon('maker')" class="sort-indicator"
								/>
							</a>
						</div>
					</th>
					<th>
						<div>
							<a class="description sort columntitle" data-sort="description" @click="setSortOrder('description')">
								<span>{{ t('inventory', 'Description') }}</span>
								<span v-show="sortOrder === 'description'"
									:class="sortOrderIcon('description')" class="sort-indicator"
								/>
							</a>
						</div>
					</th>
					<th class="hide-if-narrow">
						<div>
							<a class="categories sort columntitle" data-sort="categories">
								<span>{{ t('inventory', 'Categories') }}</span>
							</a>
						</div>
					</th>
					<th>
						<div>
							<Actions v-if="showDropdown">
								<ActionButton v-if="selectedItems.length" icon="icon-delete"
									:close-after-click="true" @click="removeItems"
								>
									{{ n('inventory', 'Delete item', 'Delete items', selectedItems.length) }}
								</ActionButton>
							</Actions>
							<Actions v-show="unlink && selectedItems.length">
								<ActionButton icon="icon-delete"
									:close-after-click="true" @click="$emit('unlink')"
								>
									{{ n('inventory', 'Unlink item', 'Unlink items', selectedItems.length) }}
								</ActionButton>
							</Actions>
						</div>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr v-if="!filteredItems.length">
					<td class="center" colspan="6">
						{{ emptyListMessage }}
					</td>
				</tr>
				<component :is="entityType(item)" v-for="item in sort(filteredItems, sortOrder, sortDirection)" v-else
					:key="item.id" :entity="item" :is-selected="isSelected(item)"
					:class="{ 'dragged': isDragged(item) }"
					:select-entity="selectItem" :uuid="_uid"
					draggable="true"
					class="entity"
					@selectItem="selectItem"
					@dragstart.native="dragStart(item, $event)"
					@dragend.native="dragEnd"
					@drop.native="dropped(item, $event)"
					@dragover.native="dragOver"
					@dragenter.native="($event) => dragEnter(item, $event)"
					@dragleave.native="dragLeave"
				/>
			</tbody>
		</table>
		<div id="drag-preview">
			<table>
				<tr v-for="item in draggedItems" :key="item.id">
					<td>
						<div class="thumbnail-wrapper">
							<div v-if="entityType(item) === 'Folder'" :style="{ backgroundImage: `url(${OC.generateUrl('apps/theming/img/core/filetypes/folder.svg?v=17')})` }"
								class="thumbnail folder"
							/>
							<div v-else :style="{ backgroundImage: `url(${getIconUrl(item)})` }" class="thumbnail default" />
						</div>
						<span>{{ item.name }}</span>
					</td>
				</tr>
			</table>
		</div>
	</div>
</template>

<script>
import ItemComponent from './Item'
import Item from '../models/item.js'
import Folder from './Folder'
import searchQueryParser from 'search-query-parser'
import { mapActions } from 'vuex'
import { Actions } from '@nextcloud/vue/dist/Components/Actions'
import { ActionButton } from '@nextcloud/vue/dist/Components/ActionButton'
import { sort } from '../store/storeHelper'

export default {
	components: {
		ItemComponent,
		Folder,
		Actions,
		ActionButton,
	},
	props: {
		mode: {
			type: String,
			default: 'navigation'
		},
		folders: {
			type: Array,
			default: () => [],
			required: false,
		},
		items: {
			type: Array,
			default: () => [],
			required: true
		},
		loading: {
			type: Boolean,
			default: false,
			required: false
		},
		showDropdown: {
			type: Boolean,
			default: false
		},
		unlink: {
			type: Boolean,
			default: false
		},
		searchString: {
			type: String,
			default: ''
		}
	},
	data: function() {
		return {
			selectedItems: [],
			draggedItems: [],
		}
	},
	computed: {
		allVisibleItemsSelected: {
			set(select) {
				if (select) {
					// add all filteredItems to selectedItems
					for (var i = 0; i < this.filteredItems.length; i++) {
						var index = this.selectedItems.indexOf(this.filteredItems[i])
						if (index === -1) {
							this.selectedItems.push(this.filteredItems[i])
						}
					}
				} else {
					// remove all filteredItems from selectedItems
					for (i = 0; i < this.filteredItems.length; i++) {
						index = this.selectedItems.indexOf(this.filteredItems[i])
						if (index !== -1) {
							this.selectedItems.splice(index, 1)
						}
					}
				}
				this.$emit('selectedItemsChanged', this.selectedItems)
			},
			get() {
				for (var i = 0; i < this.filteredItems.length; i++) {
					var index = this.selectedItems.indexOf(this.filteredItems[i])
					if (index === -1) {
						return false
					}
				}
				if (!Array.isArray(this.filteredItems) || !this.filteredItems.length) {
					return false
				}
				return true
			}
		},
		filteredItems() {
			if (!this.searchString) {
				return this.items.concat(this.folders)
			}

			var options = { keywords: ['maker', 'name', 'description', 'categories', 'itemNumber', 'gtin', 'details', 'comment'] }

			var searchQueryObj = searchQueryParser.parse(this.searchString, options)
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

			return this.items.filter(item => {
				var keyword
				var found = false
				for (var i = 0; i < options.keywords.length; i++) {
					keyword = options.keywords[i]
					// check if keywords were given, if yes, check if value is found
					if (Object.prototype.hasOwnProperty.call(searchQueryObj, keyword)) {
						if (!item[keyword]) {
							return false
						}
						if (keyword === 'categories') {
							found = false
							for (var jj = 0; jj < item.categories.length; jj++) {
								if (item.categories[jj].name.toLowerCase().indexOf(searchQueryObj[keyword].toLowerCase()) > -1) {
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
					for (jj = 0; jj < searchQueryObj.searchTerms.length; jj++) {
						found = false
						for (i = 0; i < options.keywords.length; i++) {
							keyword = options.keywords[i]
							if (!item[keyword]) {
								continue
							}
							if (keyword === 'categories') {
								for (var kk = 0; kk < item.categories.length; kk++) {
									if (item.categories[kk].name.toLowerCase().indexOf(searchQueryObj.searchTerms[jj].toLowerCase()) > -1) {
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
		},
		emptyListMessage() {
			if (this.loading) {
				return this.t('inventory', 'Loading items from server.')
			} else if (this.searchString && this.items.length) {
				return this.t('inventory', 'No item found.')
			} else {
				return this.t('inventory', 'The item list is empty.')
			}
		},
		sortOrder: {
			get() {
				return this.$store.state.settings.sortOrder
			},
			set(order) {
				this.$store.dispatch('setSetting', { type: 'sortOrder', value: order })
			}
		},
		sortDirection: {
			get() {
				return this.$store.state.settings.sortDirection
			},
			set(direction) {
				this.$store.dispatch('setSetting', { type: 'sortDirection', value: +direction })
			}
		},
	},
	watch: {
		items: 'checkSelected',
	},
	methods: {
		...mapActions([
			'deleteItems',
			'unlinkItems',
		]),

		entityType(entity) {
			return (entity instanceof Item) ? 'ItemComponent' : 'Folder'
		},

		/**
		 * Check for every item in the selectedItems array
		 * whether it is still in the items array.
		 * If not, remove from selected.
		 */
		checkSelected: function() {
			const before = this.selectedItems.length
			this.selectedItems = this.selectedItems.filter((selected) => {
				return (this.items.indexOf(selected) > -1)
			})
			if (before !== this.selectedItems.length) {
				this.$emit('selectedItemsChanged', this.selectedItems)
			}
		},

		getIconUrl: function(item) {
			if (!item.iconurl) {
				let color = '000'
				if (OCA.Accessibility) {
					color = (OCA.Accessibility.theme === 'themedark' ? 'fff' : '000')
				}
				return OC.generateUrl(`svg/inventory/item_${item.icon}?color=${color}`)
			} else {
				return item.iconurl
			}
		},
		selectItem: function(item) {
			if (this.isSelected(item)) {
				var index = this.selectedItems.indexOf(item)
				if (index !== -1) {
					this.selectedItems.splice(index, 1)
				}
			} else {
				this.selectedItems.push(item)
			}
			this.$emit('selectedItemsChanged', this.selectedItems)
		},
		isSelected: function(item) {
			return this.selectedItems.includes(item)
		},
		itemRoute(item) {
			const itemStatus = item.syncstatus ? item.syncstatus.type : null
			return (this.mode === 'selection' || itemStatus === 'unsynced') ? null : `#/items/${item.id}`
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
		sortOrderIcon(order) {
			if (order === this.sortOrder) {
				return this.sortDirection ? 'icon-triangle-s' : 'icon-triangle-n'
			} else {
				return 'icon-triangle-n'
			}
		},

		/**
		 * Drag and drop handlers
		 */

		/**
		 * Handler for starting the drag operation
		 *
		 * @param {Object} entity	The dragged item or folder
		 * @param {Object} e		The dragStart event
		 */
		dragStart(entity, e) {
			if (this.selectedItems.length > 0) {
				// We want a copy, not a reference
				this.draggedItems = sort([...this.selectedItems], this.sortOrder, this.sortDirection)
			} else {
				this.draggedItems.push(entity)
			}
			e.dataTransfer.setData('text/plain', 'dragging')
			const dragHelper = document.getElementById('drag-preview')
			e.dataTransfer.setDragImage(dragHelper, 10, 10)
		},
		dragEnd(e) {
			this.draggedItems = []
			const folders = document.querySelectorAll('tr.entity')
			folders.forEach((f) => { f.classList.remove('over') })
		},
		dropped(entity, e) {
			e.stopPropagation()
			e.preventDefault()
			if (this.entityType(entity) === 'Folder' && !this.isDragged(entity)) {
				console.debug('Dropped ' + this.draggedItems.length + ' entities onto ' + entity.name)
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
			if (this.isDragged(entity) || this.entityType(entity) !== 'Folder') {
				return
			}
			// Get the correct element, in case we hover a child.
			if (e.target.closest) {
				const target = e.target.closest('tr.entity')
				if (target.classList && target.classList.contains('entity')) {
					const folders = document.querySelectorAll('tr.entity')
					folders.forEach((f) => { f.classList.remove('over') })
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
				const target = e.target.closest('tr.entity')
				if (target.contains(e.relatedTarget)) {
					return
				}
				if (target.classList && target.classList.contains('entity')) {
					target.classList.remove('over')
				}
			}
		},
		isDragged: function(item) {
			return this.draggedItems.includes(item)
		},
	}
}
</script>
