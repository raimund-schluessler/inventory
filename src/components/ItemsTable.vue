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
				<th :id="'headerSelection-' + _uid" class="column-selection">
					<input :id="'select_all_items-' + _uid" v-model="allVisibleItemsSelected" class="select-all checkbox"
						type="checkbox"
					>
					<label :for="'select_all_items-' + _uid">
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
				<th>
					<a class="categories sort columntitle" data-sort="categories">
						<span>{{ t('inventory', 'Categories') }}</span>
						<span class="sort-indicator hidden icon-triangle-s" />
					</a>
					<Dropdown v-if="showDropdown">
						<li>
							<a href="#/items/additem">
								<span class="icon icon-bw icon-plus" />
								<span class="label">
									{{ t('inventory', 'Add single item') }}
								</span>
							</a>
						</li>
						<li>
							<a href="#/items/additems">
								<span class="icon icon-bw icon-plus" />
								<span class="label">
									{{ t('inventory', 'Add multiple items') }}
								</span>
							</a>
						</li>
						<li v-if="selectedItemIDs.length">
							<a @click="removeItems">
								<span class="icon icon-bw icon-trash" />
								<span class="label">
									{{ t('inventory', 'Delete selected items') }}
								</span>
							</a>
						</li>
					</Dropdown>
				</th>
				<th />
			</tr>
		</thead>
		<tbody>
			<tr v-for="item in filteredItems"
				:key="item.id" :class="{ selected: isSelected(item) }" class="handler"
				@click.ctrl="selectItem(item)"
			>
				<td class="selection">
					<input :id="'select-item-' + item.id + '-' + _uid" :value="item.id" :checked="isSelected(item)"
						class="selectCheckBox checkbox" type="checkbox"
					>
					<label :for="'select-item-' + item.id + '-' + _uid" @click.prevent="selectItem(item)">
						<span class="hidden-visually">
							{{ t('inventory', 'Select') }}
						</span>
					</label>
				</td>
				<td>
					<a :href="itemRoute(item)" @click.ctrl.prevent>
						<div class="thumbnail-wrapper">
							<div :style="{ backgroundImage: 'url(' + getIconUrl(item) + ')' }" class="thumbnail default" />
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
				<td>
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
import Dropdown from './Dropdown.vue'
import ItemStatusDisplay from './ItemStatusDisplay'
import searchQueryParser from 'search-query-parser'
import { mapActions } from 'vuex'

export default {
	components: {
		Dropdown: Dropdown,
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
		showDropdown: {
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
			selectedItemIDs: []
		}
	},
	computed: {
		allVisibleItemsSelected: {
			set(select) {
				if (select) {
					// add all filteredItems to selectedItemIDs
					for (var i = 0; i < this.filteredItems.length; i++) {
						var index = this.selectedItemIDs.indexOf(this.filteredItems[i].id)
						if (index === -1) {
							this.selectedItemIDs.push(this.filteredItems[i].id)
						}
					}
				} else {
					// remove all filteredItems from selectedItemIDs
					for (i = 0; i < this.filteredItems.length; i++) {
						index = this.selectedItemIDs.indexOf(this.filteredItems[i].id)
						if (index !== -1) {
							this.selectedItemIDs.splice(index, 1)
						}
					}
				}
				this.$emit('selectedItemIDsChanged', this.selectedItemIDs)
			},
			get() {
				for (var i = 0; i < this.filteredItems.length; i++) {
					var index = this.selectedItemIDs.indexOf(this.filteredItems[i].id)
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
		}
	},
	methods: {
		...mapActions([
			'deleteItems'
		]),

		getIconUrl: function(item) {
			if (!item.iconurl) {
				let color = '000'
				if (OCA.Accessibility) {
					color = (OCA.Accessibility.theme === 'themedark' ? 'fff' : '000')
				}
				return OC.generateUrl('svg/inventory/item_' + item.icon + '?color=' + color)
			} else {
				return item.iconurl
			}
		},
		selectItem: function(item) {
			if (this.isSelected(item)) {
				var index = this.selectedItemIDs.indexOf(item.id)
				if (index !== -1) {
					this.selectedItemIDs.splice(index, 1)
				}
			} else {
				this.selectedItemIDs.push(item.id)
			}
			this.$emit('selectedItemIDsChanged', this.selectedItemIDs)
		},
		isSelected: function(item) {
			return this.selectedItemIDs.includes(item.id)
		},
		itemRoute(item) {
			const itemStatus = item.syncstatus ? item.syncstatus.type : null
			return (this.mode === 'selection' || itemStatus === 'unsynced') ? null : '#/items/' + item.id
		},
		async removeItems() {
			await this.deleteItems(this.selectedItemIDs)
			this.selectedItemIDs = []
		},
	}
}
</script>
