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
		@click.ctrl="selectItem()"
	>
		<td class="selection">
			<input :id="`select-item-${entity.id}-${_uid}`" :value="entity.id" :checked="isSelected"
				class="selectCheckBox checkbox" type="checkbox"
			>
			<label :for="`select-item-${entity.id}-${_uid}`" @click.prevent="selectItem()">
				<span class="hidden-visually">
					{{ t('inventory', 'Select') }}
				</span>
			</label>
		</td>
		<td>
			<a :href="itemRoute" @click.ctrl.prevent>
				<div class="thumbnail-wrapper">
					<div :style="{ backgroundImage: `url(${getIconUrl})` }" class="thumbnail default" />
				</div>
				<span>{{ entity.name }}</span>
			</a>
		</td>
		<td>
			<a :href="itemRoute" @click.ctrl.prevent>
				{{ entity.maker }}
			</a>
		</td>
		<td>
			<a :href="itemRoute" @click.ctrl.prevent>
				{{ entity.description }}
			</a>
		</td>
		<td class="hide-if-narrow">
			<ul class="categories">
				<li v-for="category in entity.categories" :key="category.id">
					<span>{{ category.name }}</span>
				</li>
			</ul>
		</td>
		<td>
			<ItemStatusDisplay :item="entity" />
		</td>
	</tr>
</template>

<script>
import ItemStatusDisplay from './ItemStatusDisplay'

export default {
	components: {
		ItemStatusDisplay,
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
	},
	computed: {
		getIconUrl() {
			if (!this.entity.iconurl) {
				let color = '000'
				if (OCA.Accessibility) {
					color = (OCA.Accessibility.theme === 'themedark' ? 'fff' : '000')
				}
				return OC.generateUrl(`svg/inventory/item_${this.entity.icon}?color=${color}`)
			} else {
				return this.entity.iconurl
			}
		},
		itemRoute() {
			const itemStatus = this.entity.syncstatus ? this.entity.syncstatus.type : null
			return (this.mode === 'selection' || itemStatus === 'unsynced') ? null : `#/folders/item-${this.entity.id}`
		},
	},
	methods: {
		selectItem() {
			this.$emit('selectItem', this.entity)
		},
	}
}
</script>
