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
	<div>
		<div class="itemnavigation">
			<div class="breadcrumb">
				<div data-dir="/" class="crumb svg">
					<a href="#/items">
						<span class="icon icon-items"></span>
					</a>
				</div>
				<div class="crumb svg">
					<a v-bind:href="'#/items/' + item.id">{{ item.description }}</a>
				</div>
			</div>
			<dropdown>
				<li>
					<a>
						<span class="icon icon-plus"></span>
						<span class="label">{{ t('inventory', 'Add item instance') }}</span>
					</a>
				</li>
				<li>
					<a v-on:click="openModal('parent')">
						<span class="icon icon-plus"></span>
						<span class="label">{{ t('inventory', 'Add parent item') }}</span>
					</a>
				</li>
				<li>
					<a v-on:click="openModal('related')">
						<span class="icon icon-plus"></span>
						<span class="label">{{ t('inventory', 'Add related item') }}</span>
					</a>
				</li>
				<li>
					<a v-on:click="openModal('sub')">
						<span class="icon icon-plus"></span>
						<span class="label">{{ t('inventory', 'Add sub item') }}</span>
					</a>
				</li>
			</dropdown>
		</div>
		<div id="itemdetails">
			<div class="item_images">
				<img v-bind:src="OC.imagePath('inventory', 'inventory.svg')"/>
			</div>
			<div>
				<h3>
					<span>{{ t('inventory', 'Properties') }}</span>
				</h3>
				<table class="properties">
					<tbody>
						<tr>
							<td>
								<span>{{ t('inventory', 'Name') }}</span>
							</td>
							<td>
								<span>{{ item.name }}</span>
							</td>
						</tr>
						<tr>
							<td>
								<span>{{ t('inventory', 'Maker') }}</span>
							</td>
							<td>
								<span>{{ item.maker }}</span>
							</td>
						</tr>
						<tr>
							<td>
								<span>{{ t('inventory', 'Description') }}</span>
							</td>
							<td>
								<span>{{ item.description }}</span>
							</td>
						</tr>
						<tr>
							<td>
								<span>{{ t('inventory', 'Item Number') }}</span>
							</td>
							<td>
								<span>{{ item.itemNumber }}</span>
							</td>
						</tr>
						<tr>
							<td>
								<span>{{ t('inventory', 'Link') }}</span>
							</td>
							<td>
								<span><a target="_blank" v-bind:href="item.link">{{ item.link }}</a></span>
							</td>
						</tr>
						<tr>
							<td>
								<span>{{ t('inventory', 'GTIN') }}</span>
							</td>
							<td>
								<span>{{ item.gtin }}</span>
							</td>
						</tr>
						<tr>
							<td>
								<span>{{ t('inventory', 'Details') }}</span>
							</td>
							<td>
								<span>{{ item.details }}</span>
							</td>
						</tr>
						<tr>
							<td>
								<span>{{ t('inventory', 'Comment') }}</span>
							</td>
							<td>
								<span>{{ item.comment }}</span>
							</td>
						</tr>
						<tr>
							<td>
								<span>{{ t('inventory', 'Categories') }}</span>
							</td>
							<td>
								<ul class="categories">
									<li v-for='category in item.categories' :key='category.id'>
										<span>{{ category.name }}</span>
									</li>
								</ul>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<br/>
			<div class="paragraph">
				<h3>
					<span>{{ t('inventory', 'Instances') }}</span>
				</h3>
				<table class="instances">
					<thead>
						<tr>
							<th>
								<span>{{ t('inventory', 'Count') }}</span>
							</th>
							<th>
								<span>{{ t('inventory', 'Available') }}</span>
							</th>
							<th>
								<span>{{ t('inventory', 'Price') }}</span>
							</th>
							<th>
								<span>{{ t('inventory', 'Date') }}</span>
							</th>
							<th>
								<span>{{ t('inventory', 'Vendor') }}</span>
							</th>
							<th>
								<span>{{ t('inventory', 'Place') }}</span>
							</th>
							<th>
								<span>{{ t('inventory', 'Comment') }}</span>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr class="handler"
						v-for="instance in item.instances"
						:key="instance.id">
							<td>{{ instance.count }}</td>
							<td>{{ instance.available }}</td>
							<td>{{ instance.price }}</td>
							<td>{{ instance.date }}</td>
							<td>{{ instance.vendor }}</td>
							<td>{{ getPlace(instance) }}</td>
							<td>{{ instance.comment }}</td>
						</tr>
					</tbody>
				</table>
			</div>
			<br/>
			<div class="paragraph" v-if="parentItems.length">
				<h3>
					<span>{{ t('inventory', 'Parent items') }}</span>
				</h3>
				<items-table v-bind:items="parentItems" v-bind:showDropdown="false" v-bind:searchString="$root.searchString"></items-table>
			</div>
			<div class="paragraph" v-if="subItems.length">
				<h3>
					<span>{{ t('inventory', 'Sub items') }}</span>
				</h3>
				<items-table v-bind:items="subItems" v-bind:showDropdown="false" v-bind:searchString="$root.searchString"></items-table>
			</div>
			<div class="paragraph" v-if="relatedItems.length">
				<h3>
					<span>{{ t('inventory', 'Related items') }}</span>
				</h3>
				<items-table v-bind:items="relatedItems" v-bind:showDropdown="false" v-bind:searchString="$root.searchString"></items-table>
			</div>
		</div>
	</div>
</template>

<script>
	import { mapState } from 'vuex';
	import { mapActions } from 'vuex';
	import ItemsTable from './ItemsTable.vue';
	import Dropdown from './Dropdown.vue';
	import Vue from 'vue';
	import Modal from './Modal.vue';
	import Axios from 'axios';

	export default {
		data: function () {
			return {
				relationType: ""
			}
		},
		props: ['id'],
		created: function () {
			this.loadItem(this.id);
			this.loadSubItems(this.id);
			this.loadParentItems(this.id);
			this.loadRelatedItems(this.id);
		},
		components: {
			'items-table': ItemsTable,
			'dropdown': Dropdown,
			'modal': Modal
		},
		beforeRouteUpdate (to, from, next) {
			this.loadItem(to.params.id);
			this.loadSubItems(to.params.id);
			this.loadParentItems(to.params.id);
			this.loadRelatedItems(to.params.id);
			next();
		},
		computed: mapState({
			item:			state => state.item,
			subItems:		state => state.subItems,
			parentItems:	state => state.parentItems,
			relatedItems:	state => state.relatedItems
		}),
		methods: Object.assign({
				getPlace(instance) {
					if (instance.place) {
						return instance.place.name;
					} else {
						return '';
					}
				},
				openModal: function (relationType) {
					this.relationType = relationType;
					const ModalInstance = new Vue( Object.assign({}, Modal, {
						propsData: { 'link': this.linkItems, 'relationType': this.relationType, 'itemID': this.id },
						store: this.$store
					}));
				},
				linkItems(relationType, itemIDs) {
					this.showModal = false;
					Axios.post(OC.generateUrl('apps/inventory/item/'+ this.item.id + '/link/' + relationType ), {
						itemIDs: itemIDs 
					})
				}
			},
			mapActions(['loadItem']),
			mapActions(['loadSubItems']),
			mapActions(['loadParentItems']),
			mapActions(['loadRelatedItems'])
		)
	}
</script>
