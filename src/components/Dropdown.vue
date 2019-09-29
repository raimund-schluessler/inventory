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
	<div class="menu-icon">
		<div v-click-outside="close" class="header-icon icon-more" @click="toggle" />
		<div :class="{ 'open': open }" class="popovermenu">
			<popover-menu :menu="menu" />
		</div>
	</div>
</template>

<script>
import PopoverMenu from 'nextcloud-vue/dist/Components/PopoverMenu'
import ClickOutside from 'vue-click-outside'

export default {
	components: {
		PopoverMenu,
	},
	directives: {
		ClickOutside,
	},
	props: {
		menu: {
			type: Array,
			required: true,
		}
	},
	data: function() {
		return {
			open: false,
		}
	},
	methods: {
		toggle: function() {
			this.open = !this.open
		},
		close() {
			this.open = false
		},
	}
}
</script>
