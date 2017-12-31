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
						<span>{{ item.item_number }}</span>
					</td>
				</tr>
				<tr>
					<td>
						<span>{{ t('inventory', 'Link') }}</span>
					</td>
					<td>
						<span>{{ item.link }}</span>
					</td>
				</tr>
				<tr>
					<td>
						<span>{{ t('inventory', 'EAN') }}</span>
					</td>
					<td>
						<span>{{ item.ean }}</span>
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
						<td>{{ instance.place.name }}</td>
						<td>{{ instance.comment }}</td>
					</tr>
				</tbody>
			</table>
			<br/>
			<span>{{ t('inventory', 'Related items') }}</span>
			<items-table v-bind:items="relatedItems"></items-table>
			<br/>
			<span>{{ t('inventory', 'Parent items') }}</span>
			<items-table v-bind:items="parentItems"></items-table>
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
			this.loadRelatedItems(this.id);
			this.loadParentItems(this.id);
		},
		components: {
			'items-table': ItemsTable
		},
		beforeRouteUpdate (to, from, next) {
			this.loadItem(to.params.id);
			this.loadRelatedItems(to.params.id);
			this.loadParentItems(to.params.id);
			next();
		},
		computed: mapState({
			item:			state => state.item,
			relatedItems:	state => state.relatedItems,
			parentItems:	state => state.parentItems
		}),
		methods: Object.assign({},
			mapActions(['loadItem']),
			mapActions(['loadRelatedItems']),
			mapActions(['loadParentItems'])
		)
	}
</script>
