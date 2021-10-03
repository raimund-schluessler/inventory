<!--
Nextcloud - Inventory

@author Raimund Schlüßler
@copyright 2021 Raimund Schlüßler <raimund.schluessler@mailbox.org>

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
	<LoadingIcon :size="size" />
</template>

<script>
import LoadingIcon from 'vue-material-design-icons/Loading'

export default {
	name: 'Loading',
	components: {
		LoadingIcon,
	},
	props: {
		size: {
			type: Number,
			default: 20,
		},
	},
}
</script>

<style lang="scss" scoped>
.material-design-icon svg {
	animation-iteration-count: infinite;
	animation-duration: 1s;
	animation-name: spin;
}
@keyframes spin {
	0% {
		transform: rotate(0deg);
	}
	100% {
		transform: rotate(360deg);
	}
}
</style>
