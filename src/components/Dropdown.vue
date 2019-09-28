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
	<div ref="dropdownMenu" class="dropdown-container">
		<div class="app-navigation-entry-utils" @click="toggle()">
			<div class="app-navigation-entry-utils-menu-button">
				<span v-if="type === 'icon'" class="icon icon-bw icon-more" style="margin: 0 14px;" />
				<button v-else class="dropdown-button">
					<span class="icon icon-bw icon-list" />
				</button>
			</div>
		</div>
		<div :class="{ open:open }" class="app-navigation-entry-menu bubble table-dropdown">
			<ul>
				<slot />
			</ul>
		</div>
	</div>
</template>

<script>
export default {
	props: {
		type: {
			type: String,
			default: 'button',
		}
	},
	data: function() {
		return {
			open: false
		}
	},
	created() {
		document.addEventListener('click', this.documentClick)
	},
	destroyed() {
		document.removeEventListener('click', this.documentClick)
	},
	methods: {
		toggle: function() {
			this.open = !this.open
		},
		documentClick: function(e) {
			const el = this.$refs.dropdownMenu
			const target = e.target
			if ((el !== target) && !el.contains(target)) {
				this.open = false
			}
		}
	}
}
</script>

<style>
.dropdown-container {
	position: relative;
	display: inline-block;
	width: 44px;
}

.dropdown-button {
	width: 44px;
	height: 34px;
	padding: 0;
	margin: 3px 0;
	cursor: pointer;
}
</style>
