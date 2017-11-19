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
	<table id="itemstable">
		<thead>
			<tr>
				<th>
					<div>
						<input id="select_all_items" class="select-all checkbox" type="checkbox">
						<label for="select_all_items">
							<span class="hidden-visually">{{ t('inventory', 'Select All') }}</span>
						</label>
						<a class="maker sort columntitle" data-sort="maker">
							<span>{{ t('inventory', 'Maker') }}</span>
							<span class="sort-indicator icon-triangle-n"></span>
						</a>
					</div>
				</th>
				<th>
					<div>
						<a class="name sort columntitle" data-sort="name">
							<span>{{ t('inventory', 'Item') }}</span>
							<span class="sort-indicator icon-triangle-n"></span>
						</a>
					</div>
				</th>
				<th>
					<div>
						<a class="name sort columntitle" data-sort="description">
							<span>{{ t('inventory', 'Description') }}</span>
							<span class="sort-indicator icon-triangle-n"></span>
						</a>
					</div>
				</th>
				<th>
					<a class="size sort columntitle" data-sort="size">
						<span>{{ t('inventory', 'Place') }}</span>
						<span class="sort-indicator hidden icon-triangle-s"></span>
					</a>
				</th>
				<th>
					<a class="size sort columntitle" data-sort="size">
						<span>{{ t('inventory', 'Categories') }}</span>
						<span class="sort-indicator hidden icon-triangle-s"></span>
					</a>
					<a href="#/items/new" id="newItem" ng-show="name == 'items'">
						<span class="icon svg-plus"></span>
						<span class="hidden-visually">{{ t('inventory', 'New') }}</span>
					</a>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="handler"
			v-for="item in items"
			:key="item.id">
				<td><a v-bind:href="'#/items/' + item.id">{{ item.maker }}</a></td>
				<td>{{ item.name }}</td>
				<td>{{ item.description }}</td>
				<td>{{ item.place.name }}</td>
				<td>
					<ul class="categories">
						<li v-for='category in item.categories'>
							<span>{{ category.name }}</span>
						</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
</template>

<script>
	import { mapState } from 'vuex'

	export default {
		props: ['items']
	}
</script>
