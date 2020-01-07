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

<template functional>
	<tr :class="{ selected: props.isSelected }" class="handler"
		@click.ctrl="selectEntity(props.entity)"
	>
		<td class="selection">
			<input :id="`select-folder-${props.entity.id}-${props.uuid}`" :value="props.entity.id" :checked="props.isSelected"
				class="selectCheckBox checkbox" type="checkbox"
			>
			<label :for="`select-folder-${props.entity.id}-${props.uuid}`" @click.prevent="props.selectEntity(props.entity)">
				<span class="hidden-visually">
					{{ parent.t('inventory', 'Select') }}
				</span>
			</label>
		</td>
		<td colspan="5">
			<RouterLink :to="`/folders${props.entity.path}`" tag="a"
				@click.ctrl.prevent
			>
				<div class="thumbnail-wrapper">
					<div :style="{ backgroundImage: `url(${parent.OC.generateUrl('apps/theming/img/core/filetypes/folder.svg?v=17')})` }"
						class="thumbnail folder"
					/>
				</div>
				<span>{{ props.entity.name }}</span>
			</RouterLink>
		</td>
	</tr>
</template>
