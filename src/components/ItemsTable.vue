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
						<a class="name sort columntitle" data-sort="name">
							<span>{{ t('inventory', 'Name') }}</span>
							<span class="sort-indicator icon-triangle-n" />
						</a>
					</div>
				</th>
				<th>
					<div>
						<a class="maker sort columntitle" data-sort="maker">
							<span>{{ t('inventory', 'Maker') }}</span>
							<span class="sort-indicator icon-triangle-n" />
						</a>
					</div>
				</th>
				<th>
					<div>
						<a class="description sort columntitle" data-sort="description">
							<span>{{ t('inventory', 'Description') }}</span>
							<span class="sort-indicator icon-triangle-n" />
						</a>
					</div>
				</th>
				<th class="hide-if-narrow">
					<div>
						<a class="categories sort columntitle" data-sort="categories">
							<span>{{ t('inventory', 'Categories') }}</span>
							<span class="sort-indicator hidden icon-triangle-s" />
						</a>
					</div>
				</th>
				<th>
					<div>
						<Actions v-if="showDropdown">
							<ActionRouter to="/items/additem" icon="icon-add">
								{{ t('inventory', 'Add single item') }}
							</ActionRouter>
							<ActionRouter to="/items/additems" icon="icon-add">
								{{ t('inventory', 'Add multiple items') }}
							</ActionRouter>
							<ActionButton v-if="selectedItems.length" icon="icon-delete"
								:close-after-click="true" @click="removeItems"
							>
								{{ n('inventory', 'Delete item', 'Delete items', selectedItems.length) }}
							</ActionButton>
						</Actions>
						<div v-show="unlink && selectedItems.length" class="unlink" @click="$emit('unlink')">
							<span class="icon icon-bw icon-trash" />
						</div>
					</div>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr v-if="!filteredItems.length">
				<td class="center" colspan="5">
					{{ emptyListMessage }}
				</td>
			</tr>
			<tr v-for="item in filteredItems" v-else
				:key="item.id" :class="{ selected: isSelected(item) }" class="handler"
				@click.ctrl="selectItem(item)"
			>
				<td class="selection">
					<input :id="`select-item-${item.id}-${_uid}`" :value="item.id" :checked="isSelected(item)"
						class="selectCheckBox checkbox" type="checkbox"
					>
					<label :for="`select-item-${item.id}-${_uid}`" @click.prevent="selectItem(item)">
						<span class="hidden-visually">
							{{ t('inventory', 'Select') }}
						</span>
					</label>
				</td>
				<td>
					<a :href="itemRoute(item)" @click.ctrl.prevent>
						<div class="thumbnail-wrapper">
							<div :style="{ backgroundImage: `url(${getIconUrl(item)})` }" class="thumbnail default" />
						</div>
						<span>{{ item.name }}</span>
					</a>
				</td>
				<td>
					<a :href="itemRoute(item)" @click.ctrl.prevent>
						{{ item.maker }}
					</a>
				</td>
				<td>
					<a :href="itemRoute(item)" @click.ctrl.prevent>
						{{ item.description }}
					</a>
				</td>
				<td class="hide-if-narrow">
					<ul class="categories">
						<li v-for="category in item.categories" :key="category.id">
							<span>{{ category.name }}</span>
						</li>
					</ul>
				</td>
				<td>
					<ItemStatusDisplay :item="item" />
				</td>
			</tr>
		</tbody>
	</table>
</template>

<script>
import ItemStatusDisplay from './ItemStatusDisplay'
import searchQueryParser from 'search-query-parser'
import { mapActions } from 'vuex'
import { Actions } from 'nextcloud-vue/dist/Components/Actions'
import { ActionButton } from 'nextcloud-vue/dist/Components/ActionButton'
import { ActionRouter } from 'nextcloud-vue/dist/Components/ActionRouter'

export default {
	components: {
		Actions,
		ActionButton,
		ActionRouter,
		ItemStatusDisplay,
	},
	props: {
		mode: {
			type: String,
			default: 'navigation'
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
			selectedItems: []
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
				return this.items
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
	},
	watch: {
		items: 'checkSelected',
	},
	methods: {
		...mapActions([
			'deleteItems',
			'unlinkItems',
		]),

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
	}
}
</script>
