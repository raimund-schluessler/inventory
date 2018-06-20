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
	<div class="dropdown-container" ref="dropdownMenu">
		<div class="app-navigation-entry-utils" v-on:click="toggle()">
			<div class="app-navigation-entry-utils-menu-button">
				<button class="dropdown-button">
					<span class="icon icon-menu"></span>
				</button>
			</div>
		</div>
		<div class="app-navigation-entry-menu bubble table-dropdown" v-bind:class="{ open:open }">
			<ul>
				<slot></slot>
			</ul>
		</div>
	</div>
</template>

<script>
	export default {
		data: function () {
			return {
				open: false
			}
		},
		methods: {
			toggle: function () {
				this.open = !this.open;
			},
			documentClick: function (e) {
				let el = this.$refs.dropdownMenu;
				let target = e.target;
				if (( el !== target) && !el.contains(target)) {
					this.open = false;
				}
			}
		},
		created () {
			document.addEventListener('click', this.documentClick)
		},
		destroyed () {
			document.removeEventListener('click', this.documentClick)
		}
	}
</script>

<style>
.dropdown-container {
	position: absolute;
	top: 8px;
	right: 10px;
}

.dropdown-button {
	background-color: #f7f7f7;
	border: 1px solid #ccc;
	width: 42px;
	height: 34px;
	margin: 0 0 14px 0;
	padding: 0;
	cursor: pointer;
}
</style>
