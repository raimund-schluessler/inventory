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
	<Modal v-if="modalOpen"
		:out-transition="true"
		:size="'full'"
		@close="closeModal"
	>
		<div class="relation-modal-content">
			<div class="header">
				<span class="title">
					{{ headerString }}
				</span>
				<form class="searchbox" action="#" method="post"
					role="search" novalidate=""
				>
					<label for="modalSearchbox" class="hidden-visually">
						{{ t('inventory', 'Search') }}
					</label>
					<input id="modalSearchbox" v-model="searchString" class="icon-search-white"
						name="query" value="" required=""
						autocomplete="off" type="search"
					>
					<button class="icon-close-white" type="reset" @click="searchString=''">
						<span class="hidden-visually">
							{{ t('inventory', 'Reset search') }}
						</span>
					</button>
				</form>
			</div>

			<div class="body">
				<ItemsTable :items="items" :show-dropdown="false" :search-string="searchString"
					:mode="'selection'" @selectedItemsChanged="selectedItemsChanged"
				/>
			</div>

			<div class="footer">
				<span class="item-adding-status">
					{{ statusString }}
				</span>
				<button class="default-button" @click="closeModal">
					{{ t('inventory', 'Cancel') }}
				</button>
				<button class="default-button" @click="selectItems">
					{{ t('inventory', 'Select') }}
				</button>
			</div>
		</div>
	</Modal>
</template>

<script>
import { mapState } from 'vuex'
import ItemsTable from './ItemsTable.vue'
import { Modal } from 'nextcloud-vue'

export default {
	components: {
		ItemsTable: ItemsTable,
		Modal,
	},
	props: {
		modalOpen: {
			type: Boolean,
			default: false
		},
		link: {
			type: Function,
			default: () => {}
		},
		relationType: {
			type: String,
			default: 'parent'
		},
		itemID: {
			type: String,
			default: '0'
		},
	},
	data: function() {
		return {
			selectedItems: [],
			searchString: ''
		}
	},
	computed: {
		headerString: function() {
			switch (this.relationType) {
			case 'parent':
				return this.t('inventory', 'Please select the parent items:')
			case 'related':
				return this.t('inventory', 'Please select the related items:')
			case 'sub':
				return this.t('inventory', 'Please select the sub items:')
			default:
				return this.t('inventory', 'Please select the items:')
			}
		},
		statusString: function() {
			var singular, plural
			switch (this.relationType) {
			case 'parent':
				singular = 'Add %n item as parent item.'
				plural = 'Add %n items as parent items.'
				return this.n('inventory', singular, plural, this.selectedItems.length)
			case 'related':
				singular = 'Add %n item as related item.'
				plural = 'Add %n items as related items.'
				return this.n('inventory', singular, plural, this.selectedItems.length)
			case 'sub':
				singular = 'Add %n item as sub item.'
				plural = 'Add %n items as sub items.'
				return this.n('inventory', singular, plural, this.selectedItems.length)
			default:
				singular = 'Add %n item.'
				plural = 'Add %n items.'
				return this.n('inventory', singular, plural, this.selectedItems.length)
			}
		},
		...mapState({
			items: state => state.itemCandidates
		})
	},
	methods: {
		closeModal: function(event) {
			this.$emit('update:modalOpen', false)
		},
		selectItems: function(event) {
			this.link(this.relationType, this.selectedItems)
			this.closeModal()
		},
		selectedItemsChanged: function(selectedItems) {
			this.selectedItems = selectedItems
		},
	}
}
</script>
