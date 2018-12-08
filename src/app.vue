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
	<div id="content" class="app-inventory">
		<div id="app-navigation">
			<ul>
				<RouterLink
					v-for="view in views"
					:key="view.id"
					:to="'/' + view.id"
					:class="'icon-' + view.id"
					tag="li"
					active-class="active"
				>
					<a class="sprite">
						<span class="title">
							{{ view.name }}
						</span>
					</a>
				</RouterLink>
			</ul>
		</div>

		<div id="app-content">
			<RouterView class="content-wrapper" />
		</div>

		<Transition name="modal">
			<div v-show="showModal" v-cloak id="app-modal" />
		</Transition>
	</div>
</template>

<script>
import { mapState } from 'vuex'

export default {
	name: 'App',
	data: function() {
		return {
			active: 'items',
			views: [
				{
					name: t('inventory', 'Items'),
					id: 'items'
				},
				{
					name: t('inventory', 'Places'),
					id: 'places'
				},
				{
					name: t('inventory', 'Categories'),
					id: 'categories'
				}
			]
		}
	},
	computed: mapState({
		showModal: state => state.showModal
	})
}
</script>
