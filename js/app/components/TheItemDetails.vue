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
						<span class="icon items"></span>
					</a>
				</div>
			</div>
		</div>
		<div id="itemdetails">
			Itemdetails {{ id }}
			{{ item.maker }}
		</div>
	</div>
</template>

<script>
	import { mapState } from 'vuex'
	import { mapActions } from 'vuex'

	export default {
		props: ['id'],
		created: function () {
			this.loadItem(this.id);
		},
		computed: mapState({
			item: state => state.item
		}),
		methods: Object.assign({},
			mapActions(['loadItem']),
			{
				toggle() {
					this.show = !this.show;
				}
			}
		)
	}
</script>
