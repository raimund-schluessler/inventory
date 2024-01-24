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
	<div :class="{ 'row--deleted': !!deleteTimeout }"
		class="row row--collection handler"
		@click.ctrl="selectEntity">
		<div class="column column--selection">
			<NcCheckboxRadioSwitch v-if="showActions"
				:aria-label="t('inventory', 'Select')"
				:model-value="isSelected"
				data-testid="collection-checkbox"
				@update:model-value="selectEntity" />
		</div>
		<div class="column">
			<RouterLink :to="`/${collection}/${encodePath(entity.path)}`"
				@click.ctrl.prevent>
				<div class="row__icon">
					<Folder :size="38" />
				</div>
				<div class="text">
					<span v-if="!renaming">{{ entity.name }}</span>
				</div>
			</RouterLink>
			<form v-if="renaming" v-click-outside="finishRenaming" @submit.prevent="rename">
				<input v-model="newName"
					v-focus
					@keyup="checkName">
			</form>
		</div>
		<div class="column column--actions">
			<NcActions v-if="!deleteTimeout && showActions"
				:boundaries-element="boundaries">
				<NcActionButton class="startRename"
					:close-after-click="true"
					@click="startRename">
					<template #icon>
						<Pencil :size="20" />
					</template>
					{{ t('inventory', 'Rename') }}
				</NcActionButton>
				<NcActionRouter v-if="collection === 'places'"
					:close-after-click="true"
					container=".row--collection"
					:to="`/${collection}/${($route.params.path) ? encodePath($route.params.path) + '/' : ''}&details/${encodePath(entity.path)}`">
					<template #icon>
						<InformationOutline :size="20" />
					</template>
					{{ t('inventory', 'Show details') }}
				</NcActionRouter>
				<NcActionButton @click="scheduleDelete">
					<template #icon>
						<Delete :size="20" />
					</template>
					{{ deleteString }}
				</NcActionButton>
			</NcActions>
			<NcActions v-if="!!deleteTimeout"
				:boundaries-element="boundaries">
				<NcActionButton @click.prevent.stop="cancelDelete">
					<template #icon>
						<Undo :size="20" />
					</template>
					{{ undoString }}
				</NcActionButton>
			</NcActions>
		</div>
	</div>
</template>

<script>
import focus from '../../directives/focus.vue'
import { encodePath } from '../../utils/encodePath.js'

import { showError } from '@nextcloud/dialogs'
import { translate as t, translatePlural as n } from '@nextcloud/l10n'
import {
	NcActions,
	NcActionButton,
	NcActionRouter,
	NcCheckboxRadioSwitch,
} from '@nextcloud/vue'

import Delete from 'vue-material-design-icons/Delete.vue'
import Folder from 'vue-material-design-icons/Folder.vue'
import InformationOutline from 'vue-material-design-icons/InformationOutline.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Undo from 'vue-material-design-icons/Undo.vue'

import { mapActions } from 'vuex'
import { vOnClickOutside as ClickOutside } from '@vueuse/components'

const CD_DURATION = 7

export default {
	components: {
		NcActions,
		NcActionButton,
		NcActionRouter,
		NcCheckboxRadioSwitch,
		Delete,
		Folder,
		InformationOutline,
		Pencil,
		Undo,
	},
	directives: {
		ClickOutside,
		focus,
	},
	props: {
		entity: {
			type: Object,
			required: true,
		},
		collection: {
			type: String,
			default: 'folders',
		},
		isSelected: {
			type: Boolean,
			default: false,
		},
		showActions: {
			type: Boolean,
			default: true,
		},
	},
	emits: [
		'select-entity',
	],
	data() {
		return {
			// Deleting
			deleteInterval: null,
			deleteTimeout: null,
			countdown: CD_DURATION,
			newName: '',
			renaming: false,
			// Hack to fix https://github.com/nextcloud/nextcloud-vue/issues/1384
			boundaries: document.querySelector('#content-vue'),
		}
	},
	computed: {
		deleteString() {
			if (this.collection === 'places') {
				return t('inventory', 'Delete place')
			}
			return t('inventory', 'Delete folder')
		},
		undoString() {
			if (this.collection === 'places') {
				return n('inventory', 'Deleting the place in {countdown} second', 'Deleting the place in {countdown} seconds', this.countdown, { countdown: this.countdown })
			}
			return n('inventory', 'Deleting the folder in {countdown} second', 'Deleting the folder in {countdown} seconds', this.countdown, { countdown: this.countdown })
		},
	},
	methods: {
		t,

		encodePath,

		...mapActions([
			'renameFolder',
			'renamePlace',
			'deleteFolder',
			'deletePlace',
		]),

		selectEntity() {
			this.$emit('select-entity', this.entity)
		},

		startRename() {
			this.newName = this.entity.name
			this.renaming = true
		},

		finishRenaming() {
			this.renaming = false
		},

		rename() {
			this.renaming = false
			// Don't do anything if the name has not changed
			if (this.newName === this.entity.name) {
				return
			}
			if (this.collection === 'folders') {
				this.renameFolder({ folderID: this.entity.id, newName: this.newName })
			} else if (this.collection === 'places') {
				this.renamePlace({ place: this.entity, newName: this.newName })
			}
		},

		/**
		 * Check if the name is allowed
		 *
		 * @param {object} $event The event
		 */
		checkName($event) {
			if ($event.keyCode === 27) {
				this.finishRenaming()
			}
		},

		/**
		 * Deletes the folder
		 */
		scheduleDelete() {
			this.deleteInterval = setInterval(() => {
				this.countdown--
				if (this.countdown < 0) {
					this.countdown = 0
				}
			}, 1000)
			this.deleteTimeout = setTimeout(async () => {
				try {
					if (this.collection === 'folders') {
						await this.deleteFolder(this.entity.id)
					} else if (this.collection === 'places') {
						await this.deletePlace(this.entity.id)
					}
				} catch (error) {
					if (this.collection === 'folders') {
						showError(t('inventory', 'An error occurred, unable to delete the folder.'))
					} else if (this.collection === 'places') {
						showError(t('inventory', 'An error occurred, unable to delete the place.'))
					}
					console.error(error)
				} finally {
					clearInterval(this.deleteInterval)
					this.deleteTimeout = null
					this.deleteInterval = null
					this.countdown = CD_DURATION
				}
			}, 1e3 * CD_DURATION)
		},
		/**
		 * Cancels the deletion of a calendar
		 */
		cancelDelete() {
			clearTimeout(this.deleteTimeout)
			clearInterval(this.deleteInterval)
			this.deleteTimeout = null
			this.deleteInterval = null
			this.countdown = CD_DURATION
		},
	},
}
</script>

<style lang="scss">
.row__icon {
	color: var(--color-primary-element);
	margin-right: 2px;
}
</style>
