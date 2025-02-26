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
	<div :class="{ 'row--has-status': entity.syncStatus }"
		class="row row--item handler"
		@click.exact="() => {mode == 'selection' ? selectEntity() : ''}"
		@click.ctrl="selectEntity">
		<div class="column column--selection">
			<NcCheckboxRadioSwitch v-if="showActions"
				:aria-label="t('inventory', 'Select')"
				:model-value="isSelected"
				@update:model-value="selectEntity" />
		</div>
		<div class="column">
			<component :is="itemRoute ? 'RouterLink' : 'a'"
				:to="itemRoute"
				@click.ctrl.prevent>
				<div class="thumbnail">
					<img v-if="entity.images.length > 0" :src="imageSrc" class="thumbnail__image">
					<InventoryIcon v-else class="thumbnail__placeholder" />
				</div>
				<div class="text" :class="{'text--singleline': showInstance}">
					<span>{{ entity.name }}</span>
					<span v-if="showInstance" class="details">{{ entity.instances[0].date }}</span>
				</div>
			</component>
		</div>
		<div class="column">
			<component :is="itemRoute ? 'RouterLink' : 'a'"
				:to="itemRoute"
				@click.ctrl.prevent>
				<div class="text" :class="{'text--singleline': showInstance}">
					<span>{{ entity.maker }}</span>
					<span v-if="showInstance" class="details">{{ entity.instances[0].vendor }}</span>
				</div>
			</component>
		</div>
		<div class="column">
			<component :is="itemRoute ? 'RouterLink' : 'a'"
				:to="itemRoute"
				@click.ctrl.prevent>
				<div class="text" :class="{'text--singleline': showInstance}">
					<span>{{ entity.description }}</span>
					<span v-if="showInstance" class="details">{{ t('inventory', '{available} of {count}',
						{ available: entity.instances[0].available, count: entity.instances[0].count }) }}</span>
				</div>
			</component>
		</div>
		<div class="column column--hide">
			<TagList :tags="entity.tags" />
		</div>
		<div v-if="entity.syncStatus" class="column">
			<ItemStatusDisplay :status="entity.syncStatus" @reset-status="resetStatus(entity)" />
		</div>
	</div>
</template>

<script>
import ItemStatusDisplay from './../ItemStatusDisplay.vue'
import InventoryIcon from '../InventoryIcon.vue'
import TagList from './../TagList.vue'
import Item from '../../models/item.js'
import { encodePath } from '../../utils/encodePath.js'

import { translate as t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'
import { NcCheckboxRadioSwitch } from '@nextcloud/vue'

import { mapMutations } from 'vuex'

export default {
	components: {
		NcCheckboxRadioSwitch,
		ItemStatusDisplay,
		InventoryIcon,
		TagList,
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
	emits: [
		'selectEntity',
	],
	computed: {
		imageSrc() {
			if (this.entity.images.length > 0) {
				const img = this.entity.images[0]
				return generateUrl(`/core/preview?fileId=${img.fileid}&x=${128}&y=${128}&a=false&v=${img.etag}`)
			}
			return null
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
				const encodedPath = encodePath(instance.place.path)
				basePath = `/places/${(encodedPath) ? encodedPath + '/' : ''}`
			} else {
				const encodedPath = encodePath(this.entity.path)
				basePath = `/folders/${(encodedPath) ? encodedPath + '/' : ''}`
			}
			return `${basePath}item-${this.entity.id + (instance ? '/instance-' + instance.id : '')}`
		},
		showInstance() {
			return this.entity.isInstance && this.entity.instances.length > 0
		},
	},
	methods: {
		t,

		selectEntity() {
			this.$emit('selectEntity', this.entity)
		},

		...mapMutations(['setSyncStatus']),
		resetStatus(entity) {
			if (entity instanceof Item) {
				this.setSyncStatus({ item: entity, status: null })
			}
		},
	},
}
</script>
