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
				<div data-dir="/" class="crumb svg last">
					<a href="#/items">
						<span class="icon icon-items"></span>
					</a>
				</div>
			</div>
		</div>
		<div id="itemdetails">
			<span>{{ t('inventory', 'Properties') }}</span>
			<table>
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
							<li v-for='category in item.categories'>
								<span>{{ category.name }}</span>
							</li>
						</ul>
					</td>
				</tr>
			</table>
			<br/>
			<span>{{ t('inventory', 'Instances') }}</span>
			<table>
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
					:key="item.id">
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
			<br/>
			<span>{{ t('inventory', 'Subitems') }}</span>
			<items-table v-bind:items="subItems"></items-table>
			<br/>
			<span>{{ t('inventory', 'Parent items') }}</span>
			<items-table v-bind:items="parentItems"></items-table>
			<br/>
			<span>{{ t('inventory', 'Related items') }}</span>
			<items-table v-bind:items="relatedItems"></items-table>
		</div>
	</div>
</template>

<script>
	import { mapState } from 'vuex';
	import { mapActions } from 'vuex';
	import ItemsTable from './ItemsTable.vue';

	export default {
		props: ['id'],
		created: function () {
			this.loadItem(this.id);
			this.loadSubItems(this.id);
			this.loadParentItems(this.id);
			this.loadRelatedItems(this.id);
		},
		components: {
			'items-table': ItemsTable
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
			}
			},
			mapActions(['loadItem']),
			mapActions(['loadSubItems']),
			mapActions(['loadParentItems']),
			mapActions(['loadRelatedItems'])
		)
	}
</script>
