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
	<transition name="modal">
		<div class="modal-mask" @click="closeModal">
			<div class="modal-wrapper">
				<div class="modal-container">
					<div class="modal-header">
						<span class="title">{{ t('inventory', headerString) }}</span>
						<form class="searchbox" action="#" method="post" role="search" novalidate="">
							<label for="searchbox" class="hidden-visually">
								{{ t('inventory', 'Search') }}
							</label>
							<input id="modalSearchbox" name="query" value="" v-model="searchString" required="" autocomplete="off" type="search">
							<button class="icon-close-white" type="reset" @click="searchString=''">
								<span class="hidden-visually">{{ t('inventory', 'Reset search') }}</span>
							</button>
						</form>
					</div>

					<div class="modal-body">
						<items-table  v-on:selectedItemIDsChanged="selectedItemIDsChanged" v-bind:items="items" v-bind:showDropdown="false" v-bind:searchString="searchString"></items-table>
					</div>

					<div class="modal-footer">
						<slot name="footer">
							<button class="modal-default-button" @click="closeModal">
								Cancel
							</button>
							<button class="modal-default-button" @click="$emit('selectedItems', relationType, selectedItemIDs)">
								Select
							</button>
						</slot>
					</div>
				</div>
			</div>
		</div>
	</transition>
</template>

<script>
	import { mapState } from 'vuex';
	import { mapActions } from 'vuex';
	import ItemsTable from './ItemsTable.vue';
	import Dropdown from './Dropdown.vue';

	export default {
		data: function () {
			return {
				selectedItemIDs: [],
				searchString: ""
			}
		},
		props: [
			'relationType',
			'itemID'
		],
		components: {
			'items-table': ItemsTable,
			'dropdown': Dropdown
		},
		created () {
			this.loadItemCandidates({itemID: this.itemID, relationType: this.relationType});
		},
		methods: Object.assign(
			{
				closeModal: function (event) {
					if (event.target === event.currentTarget) {
						this.$emit('close');
					}
				},
				selectedItemIDsChanged: function(selectedItemIDs) {
					this.selectedItemIDs = selectedItemIDs;
				}
			},
			mapActions(['loadItemCandidates'])
		),
		computed: Object.assign({
			headerString: function () {
				switch(this.relationType) {
					case "parent":
						return "Please select the parent items:";
						break;
					case "related":
						return "Please select the related items:";
						break;
					case "sub":
						return "Please select the subitems:";
						break;
				}
			}},
			mapState({
				items:	function(state) {
					return state.itemCandidates;
				}
			})
		)
	}
</script>
