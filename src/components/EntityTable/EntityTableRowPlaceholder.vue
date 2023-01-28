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
License along with this library. If not, see <http://www.gnu.org/licenses/>.

-->

<template>
	<div v-if="placeholder.show" class="row row--empty">
		<div class="column">
			<NcLoadingIcon v-if="placeholder.loading" :size="44" />
			<span>{{ placeholder.message }}</span>
		</div>
	</div>
</template>

<script>
import { NcLoadingIcon } from '@nextcloud/vue'

export default {
	components: {
		NcLoadingIcon,
	},
	props: {
		placeholder: {
			type: Object,
			required: true,
		},
	},
}
</script>
