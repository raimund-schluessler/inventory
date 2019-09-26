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
				<Multiselect
					:value="relationTypes.find( _ => _.type === relationType )"
					:multiple="false"
					:allow-empty="false"
					track-by="type"
					:placeholder="t('tasks', 'Select relation type')"
					label="name"
					:options="relationTypes"
					:close-on-select="true"
					class="multiselect-vue"
					@input="changeRelationType"
				/>
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
					:mode="'selection'" :loading="loading" @selectedItemsChanged="selectedItemsChanged"
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
import { mapState, mapActions } from 'vuex'
import ItemsTable from './ItemsTable.vue'
import { Modal, Multiselect } from 'nextcloud-vue'

export default {
	components: {
		ItemsTable: ItemsTable,
		Modal,
		Multiselect,
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
		itemID: {
			type: String,
			default: '0'
		},
	},
	data: function() {
		return {
			selectedItems: [],
			searchString: '',
			relationTypes: [{
				type: 'parent',
				name: this.t('inventory', 'parent items'),
			}, {
				type: 'sub',
				name: this.t('inventory', 'sub items'),
			}, {
				type: 'related',
				name: this.t('inventory', 'related items'),
			}],
			relationType: 'parent',
			loading: false,
		}
	},
	computed: {
		headerString: function() {
			return this.t('inventory', 'Please select the relation of the items:')
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
	watch: {
		modalOpen: 'loadItems',
	},
	created: function() {
		this.loadItems()
	},
	methods: {
		changeRelationType: function(relation) {
			this.relationType = relation.type
			this.loadItems()
		},
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
		async loadItems() {
			if (!this.modalOpen) {
				return
			}
			this.loading = true
			await this.loadItemCandidates({ itemID: this.itemID, relationType: this.relationType })
			this.loading = false
		},

		...mapActions([
			'loadItemCandidates',
		])
	}
}
</script>
