<!--
Nextcloud - Inventory

@author Raimund Schlüßler
@copyright 2020 Raimund Schlüßler <raimund.schluessler@mailbox.org>

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
	<tr :class="{ selected: isSelected }" class="handler"
		@click.ctrl="selectEntity(entity)"
	>
		<td class="selection">
			<input :id="`select-folder-${entity.id}-${uuid}`" :value="entity.id" :checked="isSelected"
				class="selectCheckBox checkbox" type="checkbox"
			>
			<label :for="`select-folder-${entity.id}-${uuid}`" @click.prevent="selectEntity(entity)">
				<span class="hidden-visually">
					{{ t('inventory', 'Select') }}
				</span>
			</label>
		</td>
		<td colspan="5">
			<div>
				<RouterLink :to="`/folders/${entity.path}`" tag="a"
					@click.ctrl.prevent
				>
					<div class="thumbnail-wrapper">
						<div :style="{ backgroundImage: `url(${OC.generateUrl('apps/theming/img/core/filetypes/folder.svg?v=17')})` }"
							class="thumbnail folder"
						/>
					</div>
					<span>{{ entity.name }}</span>
				</RouterLink>
				<Actions>
					<ActionButton icon="icon-rename" @click="renameFolder()">
						{{ t('inventory', 'Rename') }}
					</ActionButton>
					<ActionButton icon="icon-delete" @click="deleteFolder()">
						{{ t('inventory', 'Delete folder') }}
					</ActionButton>
				</Actions>
			</div>
		</td>
	</tr>
</template>

<script>
import { Actions } from '@nextcloud/vue/dist/Components/Actions'
import { ActionButton } from '@nextcloud/vue/dist/Components/ActionButton'

export default {
	components: {
		Actions,
		ActionButton,
	},
	props: {
		entity: {
			type: Object,
			required: true,
		},
		isSelected: {
			type: Boolean,
			default: false,
		},
		selectEntity: {
			type: Function,
			default: () => {}
		},
		uuid: {
			type: Number,
			required: true,
		},
	},
	methods: {
		async renameFolder() {
		},

		async deleteFolder() {
		},
	},
}
</script>
