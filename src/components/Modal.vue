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
	<div class="modal-mask" @click="closeModal">
		<div class="modal-wrapper">
			<div class="modal-container">
				<div class="modal-header">
					<span class="title">
						{{ t('inventory', headerString) }}
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

				<div class="modal-body">
					<ItemsTable :items="items" :show-dropdown="false" :search-string="searchString"
						:mode="'selection'" @selectedItemIDsChanged="selectedItemIDsChanged"
					/>
				</div>

				<div class="modal-footer">
					<slot name="footer">
						<span class="item-adding-status">
							{{ statusString }}
						</span>
						<button class="modal-default-button" @click="closeModal">
							Cancel
						</button>
						<button class="modal-default-button" @click="selectItems">
							Select
						</button>
					</slot>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { mapActions, mapState } from 'vuex'
import ItemsTable from './ItemsTable.vue'

export default {
	components: {
		'ItemsTable': ItemsTable
	},
	props: {
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
		}
	},
	data: function() {
		return {
			selectedItemIDs: [],
			searchString: ''
		}
	},
	computed: Object.assign({
		headerString: function() {
			return 'Please select the ' + this.relationType + ' items:'
		},
		statusString: function() {
			var singular = 'Add %n item as ' + this.relationType + ' item.'
			var plural = 'Add %n items as ' + this.relationType + ' items.'
			return n('inventory', singular, plural, this.selectedItemIDs.length)
		},
		showModal: {
			set(show) {
				this.$store.commit('setShowModal', show)
			},
			get() {
				return this.$store.state.showModal
			}
		} },
	mapState({
		items: state => state.itemCandidates
	})
	),
	created() {
		this.showModal = true
		const modalContainer = document.createElement('div')
		document.getElementById('app-modal').appendChild(modalContainer)
		this.$mount(modalContainer)
		this.loadItemCandidates({ itemID: this.itemID, relationType: this.relationType })
	},
	methods: Object.assign(
		{
			closeModal: function(event) {
				if (event === undefined || event.target === event.currentTarget) {
					this.showModal = false
					this.$el.remove()
					// this.$el.innerHTML = '' // remove inner content
					this.$destroy() // cleanup in component
				}
			},
			selectItems: function(event) {
				this.link(this.relationType, this.selectedItemIDs)
				this.closeModal()
			},
			selectedItemIDsChanged: function(selectedItemIDs) {
				this.selectedItemIDs = selectedItemIDs
			}
		},
		mapActions(['loadItemCandidates'])
	)
}
</script>
