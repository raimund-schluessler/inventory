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
		<div class="modal-mask">
			<div class="modal-wrapper" @click="closeModal">
				<div class="modal-outer-container">
					<div class="modal-container">
						<div class="modal-header">
							{{ t('inventory', headerString) }}
						</div>

						<div class="modal-body">
							<items-table v-bind:items="items" v-bind:showDropdown="false" v-bind:searchString="searchString"></items-table>
						</div>

						<div class="modal-footer">
							<slot name="footer">
								<button class="modal-default-button" @click="closeModal">
									Cancel
								</button>
								<button class="modal-default-button" @click="$emit('selectedItems', relationType, items)">
									Select
								</button>
							</slot>
						</div>
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
				selectedItemIDs: [1, 2, 3, 4],
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

<style>
.modal-mask {
	position: fixed;
	z-index: 9998;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background-color: rgba(0, 0, 0, .5);
	display: table;
	transition: opacity .3s ease;
}

.modal-wrapper {
	display: table-cell;
	vertical-align: middle;
	height: 100%;
}

.modal-outer-container {
	width: calc(100% - 200px);
	height: calc(100% - 200px);
	margin: 0 auto;
}

.modal-container {
	display: flex;
	flex-direction: column;
	height: 100%;
	padding: 20px 30px;
	background-color: #fff;
	border-radius: 2px;
	box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
	transition: all .3s ease;
	font-family: Helvetica, Arial, sans-serif;
}

.modal-header h3 {
	margin-top: 0;
	color: #42b983;
}

.modal-body {
	display: flex;
	flex: 1;
	overflow-y: auto;
}

.modal-footer {
	height: 40px;
}

.modal-default-button {
	float: right;
}

/*
 * The following styles are auto-applied to elements with
 * transition="modal" when their visibility is toggled
 * by Vue.js.
 *
 * You can easily play with the modal transition by editing
 * these styles.
 */

.modal-enter {
	opacity: 0;
}

.modal-leave-active {
	opacity: 0;
}

.modal-enter .modal-container,
.modal-leave-active .modal-container {
	-webkit-transform: scale(1.1);
	transform: scale(1.1);
}
</style>
