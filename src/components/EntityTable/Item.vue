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
	<div :class="{ 'row--selected': isSelected, 'row--has-status': entity.syncStatus }"
		class="row handler"
		@click.exact="() => {mode == 'selection' ? selectEntity(entity) : ''}"
		@click.ctrl="selectEntity(entity)">
		<div class="column column--selection">
			<input v-if="showActions"
				:id="`select-item-${entity.id}-${uuid}`"
				:value="entity.id"
				:checked="isSelected"
				class="selectCheckBox checkbox"
				type="checkbox">
			<label :for="`select-item-${entity.id}-${uuid}`" @click.prevent="selectEntity(entity)">
				<span class="hidden-visually">
					{{ t('inventory', 'Select') }}
				</span>
			</label>
		</div>
		<div class="column">
			<component :is="itemRoute ? 'RouterLink': 'a'"
				:to="itemRoute"
				tag="a"
				@click.ctrl.prevent>
				<div class="thumbnail">
					<div :style="{ backgroundImage: `url(${getIconUrl})` }" class="thumbnail__image" :class="{'thumbnail__image--default': !entity.images.length}" />
				</div>
				<div class="text" :class="{'text--singleline': showInstance}">
					<span>{{ entity.name }}</span>
					<span v-if="showInstance" class="details">{{ entity.instances[0].date }}</span>
				</div>
			</component>
		</div>
		<div class="column">
			<component :is="itemRoute ? 'RouterLink': 'a'"
				:to="itemRoute"
				tag="a"
				@click.ctrl.prevent>
				<div class="text" :class="{'text--singleline': showInstance}">
					<span>{{ entity.maker }}</span>
					<span v-if="showInstance" class="details">{{ entity.instances[0].vendor }}</span>
				</div>
			</component>
		</div>
		<div class="column">
			<component :is="itemRoute ? 'RouterLink': 'a'"
				:to="itemRoute"
				tag="a"
				@click.ctrl.prevent>
				<div class="text" :class="{'text--singleline': showInstance}">
					<span>{{ entity.description }}</span>
					<span v-if="showInstance" class="details">{{ t('inventory', '{available} of {count}',
						{ available: entity.instances[0].available, count: entity.instances[0].count }) }}</span>
				</div>
			</component>
		</div>
		<div class="column column--hide">
			<ul class="tags">
				<li v-for="tag in entity.tags" :key="tag.id">
					<span>{{ tag.name }}</span>
				</li>
			</ul>
		</div>
		<div v-if="entity.syncStatus" class="column">
			<ItemStatusDisplay :status="entity.syncStatus" @reset-status="resetStatus(entity)" />
		</div>
	</div>
</template>

<script>
import ItemStatusDisplay from './../ItemStatusDisplay.vue'
import Item from '../../models/item.js'

import { generateUrl } from '@nextcloud/router'

import { mapMutations } from 'vuex'

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
		selectEntity: {
			type: Function,
			default: () => {},
		},
		uuid: {
			type: Number,
			required: true,
		},
		showActions: {
			type: Boolean,
			default: true,
		},
		collection: {
			type: String,
			default: 'folders',
		},
		mode: {
			type: String,
			default: 'navigation',
		},
	},
	computed: {
		getIconUrl() {
			if (this.entity.images.length > 0) {
				const img = this.entity.images[0]
				return generateUrl(`/core/preview?fileId=${img.fileid}&x=${128}&y=${128}&a=false&v=${img.etag}`)
			} else if (this.entity.iconurl) {
				return this.entity.iconurl
			} else {
				let color = '000'
				if (OCA.Accessibility) {
					color = (OCA.Accessibility.theme === 'dark' ? 'fff' : '000')
				}
				return generateUrl(`svg/inventory/item_${this.entity.icon}?color=${color}`)
			}
		},
		/**
		 * Returns the link to the item or instance
		 *
		 * @return {string} The link to show for the item
		 */
		itemRoute() {
			if (this.mode === 'selection' || this.entity.syncStatus?.type === 'unsynced') {
				return null
			}
			let basePath
			const instance = (this.entity.isInstance && this.entity.instances.length > 0) ? this.entity.instances[0] : null
			if (instance) {
				basePath = `/places/${(instance.place.path) ? instance.place.path + '/' : ''}`
			} else {
				basePath = `/folders/${(this.entity.path) ? this.entity.path + '/' : ''}`
			}
			return `${basePath}item-${this.entity.id + (instance ? '/instance-' + instance.id : '')}`
		},
		showInstance() {
			return this.entity.isInstance && this.entity.instances.length > 0
		},
	},
	methods: {
		...mapMutations(['setSyncStatus']),
		resetStatus(entity) {
			if (entity instanceof Item) {
				this.setSyncStatus({ item: entity, status: null })
			}
		},
	},
}
</script>
