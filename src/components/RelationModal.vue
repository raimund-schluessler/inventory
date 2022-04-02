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
		size="full"
		class="relation-modal"
		@close="closeModal">
		<div class="content">
			<div class="header">
				<span class="title">
					{{ headerString }}
				</span>
				<Multiselect :value="relationTypes.find( _ => _.type === relationType )"
					:multiple="false"
					:allow-empty="false"
					track-by="type"
					:placeholder="t('inventory', 'Select relation type')"
					label="name"
					:options="relationTypes"
					:close-on-select="true"
					class="multiselect-vue"
					@input="changeRelationType" />
				<form class="searchbox"
					action="#"
					method="post"
					role="search"
					novalidate="">
					<label for="modalSearchbox" class="hidden-visually">
						{{ t('inventory', 'Search') }}
					</label>
					<div class="searchbox-input">
						<input id="modalSearchbox"
							v-model="searchString"
							name="query"
							required=""
							autocomplete="off"
							type="search">
						<Magnify class="search" :size="20" fill-color="var(--color-primary-text)" />
						<button class="close" type="reset" @click="searchString=''">
							<Close :size="20" fill-color="var(--color-primary-text)" />
							<span class="hidden-visually">
								{{ t('inventory', 'Reset search') }}
							</span>
						</button>
					</div>
				</form>
			</div>

			<div class="body">
				<EntityTable :items="items"
					:allow-deletion="false"
					:search-string="searchString"
					:filter-only="true"
					mode="selection"
					@selected-items-changed="selectedItemsChanged" />
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
import EntityTable from './EntityTable/EntityTable'

import { translate as t, translatePlural as n } from '@nextcloud/l10n'
import Modal from '@nextcloud/vue/dist/Components/Modal'
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'

import Magnify from 'vue-material-design-icons/Magnify'
import Close from 'vue-material-design-icons/Close'

import { mapGetters, mapActions } from 'vuex'

export default {
	components: {
		Close,
		EntityTable,
		Magnify,
		Modal,
		Multiselect,
	},
	props: {
		modalOpen: {
			type: Boolean,
			default: false,
		},
		link: {
			type: Function,
			default: () => {},
		},
		itemId: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			selectedItems: [],
			searchString: '',
			relationTypes: [{
				type: 'parent',
				name: t('inventory', 'parent items'),
			}, {
				type: 'sub',
				name: t('inventory', 'sub items'),
			}, {
				type: 'related',
				name: t('inventory', 'related items'),
			}],
			relationType: 'parent',
		}
	},
	computed: {
		...mapGetters({
			items: 'getItemCandidates',
		}),

		headerString() {
			return t('inventory', 'Please select the relation of the items:')
		},
		statusString() {
			let singular, plural
			switch (this.relationType) {
			case 'parent':
				singular = 'Add %n item as parent item.'
				plural = 'Add %n items as parent items.'
				return n('inventory', singular, plural, this.selectedItems.length)
			case 'related':
				singular = 'Add %n item as related item.'
				plural = 'Add %n items as related items.'
				return n('inventory', singular, plural, this.selectedItems.length)
			case 'sub':
				singular = 'Add %n item as sub item.'
				plural = 'Add %n items as sub items.'
				return n('inventory', singular, plural, this.selectedItems.length)
			default:
				singular = 'Add %n item.'
				plural = 'Add %n items.'
				return n('inventory', singular, plural, this.selectedItems.length)
			}
		},
	},
	watch: {
		modalOpen: 'loadItems',
	},
	created() {
		this.loadItems()
	},
	methods: {
		t,

		changeRelationType(relation) {
			this.relationType = relation.type
			this.loadItems()
		},
		closeModal(event) {
			this.searchString = ''
			this.$emit('update:modalOpen', false)
		},
		selectItems(event) {
			this.link(this.relationType, this.selectedItems)
			this.closeModal()
		},
		selectedItemsChanged(selectedItems) {
			this.selectedItems = selectedItems
		},
		async loadItems() {
			if (!this.modalOpen) {
				return
			}
			this.selectedItems = []
			await this.loadItemCandidates({ itemID: this.itemId, relationType: this.relationType })
		},

		...mapActions([
			'loadItemCandidates',
		]),
	},
}
</script>
