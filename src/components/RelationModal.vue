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
		size="full"
		class="relation-modal"
		@close="closeModal">
		<div class="content">
			<div class="header">
				<span class="title">
					{{ headerString }}
				</span>
				<NcMultiselect :value="relationTypes.find( _ => _.type === relationType )"
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
							value=""
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
	</NcModal>
</template>

<script>
import EntityTable from './EntityTable/EntityTable.vue'

import { translate as t, translatePlural as n } from '@nextcloud/l10n'
import NcModal from '@nextcloud/vue/dist/Components/NcModal'
import NcMultiselect from '@nextcloud/vue/dist/Components/NcMultiselect'

import Magnify from 'vue-material-design-icons/Magnify'
import Close from 'vue-material-design-icons/Close'

import { mapGetters, mapActions } from 'vuex'

export default {
	components: {
		Close,
		EntityTable,
		Magnify,
		NcModal,
		NcMultiselect,
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

<style lang="scss" scoped>
.relation-modal .modal-container {
	height: 85% !important;
	width: 85% !important;

	.content {
		display: flex;
		flex-direction: column;
		height: 100%;
		background-color: var(--color-main-background);
		border-radius: 2px;
		box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
		transition: all .3s ease;
		font-family: Helvetica, Arial, sans-serif;

		.header {
			display: flex;
			align-items: center;
			flex-wrap: wrap;
			flex-direction: row;
			background-color: var(--color-primary);
			padding: 8px;

			.title {
				line-height: 38px;
				padding: 0 20px;
				padding-left: 6px;
				font-size: 14px;
				color: white;
			}

			.multiselect {
				z-index: 100;
				background-color: unset;
				font-size: 14px;

				@media only screen and (max-width: 400px) {
					width: 100%;
				}

				.multiselect__tags {
					align-items: center;

					.multiselect__input {
						padding: 0 !important;
					}
				}
			}

			.searchbox {
				margin-left: auto;

				.searchbox-input input[type='search'] {
					padding-left: 35px;
					z-index: 99;
					font-size: 14px;

					&:not(:valid) ~ .close {
						display: none;
					}

					& ~ .close {
						position: absolute;
						right: 0;top: 0;
						background-color: unset;
						border: none;
						display: inline;
						z-index: 999;
					}

					& ~ .search {
						position: absolute;
						left: 0;
						width: 44px;
						height: 44px;
						display: inline-block;
						padding: 10px;
						box-sizing: border-box;
					}
				}
			}
		}

		.body {
			display: flex;
			flex: 1;
			overflow-y: auto;

			> div {
				width: 100%;
			}

			.row--header {
				top: 0;
			}
		}

		.footer {
			box-sizing: border-box;
			height: 56px;
			padding: 8px;

			.item-adding-status {
				line-height: 40px;
				font-size: 13px;
				padding-left: 20px;
			}

			.default-button {
				float: right;
			}
		}
	}
}
</style>
