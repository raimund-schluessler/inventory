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
	<NcModal v-if="modalOpen"
		:out-transition="true"
		size="large"
		class="relation-modal"
		@close="closeModal">
		<div class="content">
			<div class="header">
				<span class="headline">
					{{ t('inventory', 'Please select the relation of the items:') }}
				</span>
				<NcCheckboxRadioSwitch :checked.sync="relationType"
					value="parent"
					name="relation_type"
					type="radio">
					{{ t('inventory', 'Parent items') }}
				</NcCheckboxRadioSwitch>
				<NcCheckboxRadioSwitch :checked.sync="relationType"
					value="related"
					name="relation_type"
					type="radio">
					{{ t('inventory', 'Related items') }}
				</NcCheckboxRadioSwitch>
				<NcCheckboxRadioSwitch :checked.sync="relationType"
					value="sub"
					name="relation_type"
					type="radio">
					{{ t('inventory', 'Sub items') }}
				</NcCheckboxRadioSwitch>

				<NcTextField :value.sync="searchString"
					:label="t('inventory', 'Filter items')"
					trailing-button-icon="close"
					:show-trailing-button="searchString !== ''"
					@trailing-button-click="searchString=''">
					<Magnify :size="16" />
				</NcTextField>
			</div>

			<div class="body">
				<EntityTable :items="items"
					:allow-deletion="false"
					:filter-only="true"
					mode="selection"
					@selected-items-changed="selectedItemsChanged" />
			</div>

			<div class="footer">
				<span class="item-adding-status">
					{{ statusString }}
				</span>
				<NcButton type="primary" @click="selectItems">
					{{ t('inventory', 'Select') }}
				</NcButton>
				<NcButton @click="closeModal">
					{{ t('inventory', 'Cancel') }}
				</NcButton>
			</div>
		</div>
	</NcModal>
</template>

<script>
import EntityTable from './EntityTable/EntityTable.vue'

import { translate as t, translatePlural as n } from '@nextcloud/l10n'
import {
	NcButton,
	NcCheckboxRadioSwitch,
	NcModal,
	NcTextField,
} from '@nextcloud/vue'

import Magnify from 'vue-material-design-icons/Magnify.vue'

import { mapGetters, mapActions } from 'vuex'

export default {
	components: {
		EntityTable,
		Magnify,
		NcButton,
		NcCheckboxRadioSwitch,
		NcModal,
		NcTextField,
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
	emits: ['update:modalOpen'],
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

		statusString() {
			let singular, plural
			switch (this.relationType) {
			case 'parent':
				singular = 'Add %n parent item.'
				plural = 'Add %n parent items.'
				return n('inventory', singular, plural, this.selectedItems.length)
			case 'related':
				singular = 'Add %n related item.'
				plural = 'Add %n related items.'
				return n('inventory', singular, plural, this.selectedItems.length)
			case 'sub':
				singular = 'Add %n sub item.'
				plural = 'Add %n sub items.'
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
		relationType: 'loadItems',
		searchString(searchString) {
			this.$store.commit('setSearchString', searchString)
		},
	},
	created() {
		this.loadItems()
	},
	methods: {
		t,

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

<style lang="scss" scoped>
.relation-modal :deep(.modal-container) {
		min-height: 85% !important;
		height: 0; // we have to set a height to make the children grow
		min-width: 85% !important;

		@media only screen and (max-width: 512px) {
			height: calc(100% - var(--header-height));
		}
}

.relation-modal .modal-container .content {
	display: flex;
	flex-direction: column;
	height: 100%;

	.header {
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		flex-direction: row;
		padding: 8px;

		.headline {
			line-height: 38px;
			padding: 0 14px;
			padding-left: 6px;
		}

		.checkbox-radio-switch {
			margin: 0 16px;
		}

		.input-field {
			margin-top: var(--default-grid-baseline);
		}
	}

	.body {
		display: flex;
		flex: 1;
		overflow-y: auto;

		> div {
			width: 100%;
		}
	}

	.footer {
		box-sizing: border-box;
		padding: 8px;

		.item-adding-status {
			line-height: 40px;
			padding-left: 6px;
		}

		.button-vue {
			margin-right: var(--default-grid-baseline);
			float: right;
		}
	}
}
</style>
