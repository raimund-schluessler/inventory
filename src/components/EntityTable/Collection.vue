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
	<div :class="{ 'row--selected': isSelected, 'row--deleted': !!deleteTimeout }"
		class="row row--collection handler"
		@click.ctrl="selectEntity(entity)">
		<div class="column column--selection">
			<input :id="`select-folder-${entity.id}-${uuid}`"
				:value="entity.id"
				:checked="isSelected"
				class="selectCheckBox checkbox"
				type="checkbox">
			<label v-if="showActions" :for="`select-folder-${entity.id}-${uuid}`" @click.prevent="selectEntity(entity)">
				<span class="hidden-visually">
					{{ t('inventory', 'Select') }}
				</span>
			</label>
		</div>
		<div class="column">
			<RouterLink :to="`/${collection}/${entity.path}`"
				tag="a"
				@click.ctrl.prevent>
				<div class="thumbnail">
					<div :style="{ backgroundImage: getThumbnailUrl }"
						class="thumbnail__image folder" />
				</div>
				<div class="text">
					<span v-if="!renaming">{{ entity.name }}</span>
				</div>
			</RouterLink>
			<form v-if="renaming" v-click-outside="{ handler: finishRenaming, middleware: checkClickOutside }" @submit.prevent="rename">
				<input v-model="newName"
					v-focus
					@keyup="checkName">
			</form>
		</div>
		<div class="column column--actions">
			<Actions
				v-if="!deleteTimeout && showActions"
				:boundaries-element="boundaries">
				<ActionButton class="startRename"
					:close-after-click="true"
					@click="startRename">
					<Pencil slot="icon" :size="24" decorative />
					{{ t('inventory', 'Rename') }}
				</ActionButton>
				<ActionButton @click="scheduleDelete">
					<Delete slot="icon" :size="24" decorative />
					{{ deleteString }}
				</ActionButton>
			</Actions>
			<Actions
				v-if="!!deleteTimeout"
				:boundaries-element="boundaries">
				<ActionButton
					@click.prevent.stop="cancelDelete">
					<Undo slot="icon" :size="24" decorative />
					{{ undoString }}
				</ActionButton>
			</Actions>
		</div>
	</div>
</template>

<script>
import focus from '../../directives/focus.vue'

import { showError } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'

import Delete from 'vue-material-design-icons/Delete.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Undo from 'vue-material-design-icons/Undo.vue'

import { mapActions } from 'vuex'
import ClickOutside from 'v-click-outside'

const CD_DURATION = 7

export default {
	components: {
		Actions,
		ActionButton,
		Delete,
		Pencil,
		Undo,
	},
	directives: {
		ClickOutside: ClickOutside.directive,
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
	},
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
		getThumbnailUrl() {
			return `url(${generateUrl('apps/theming/img/core/filetypes/folder.svg?v=17')})`
		},
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
		...mapActions([
			'renameFolder',
			'renamePlace',
			'deleteFolder',
			'deletePlace',
		]),

		startRename() {
			this.newName = this.entity.name
			this.renaming = true
		},

		finishRenaming($event) {
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
				this.renamePlace({ placeID: this.entity.id, newName: this.newName })
			}
		},

		checkClickOutside($event) {
			return !$event.target.classList.contains('startRename')
		},

		/**
		 * Check if the name is allowed
		 *
		 * @param {Object} $event The event
		 */
		checkName($event) {
			if ($event.keyCode === 27) {
				this.finishRenaming($event)
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
			this.deleteTimeout = setTimeout(async() => {
				try {
					if (this.collection === 'folders') {
						await this.deleteFolder(this.entity.id)
					} else if (this.collection === 'places') {
						await this.deletePlace(this.entity.id)
					}
				} catch (error) {
					if (this.collection === 'folders') {
						showError(this.$t('inventory', 'An error occurred, unable to delete the folder.'))
					} else if (this.collection === 'places') {
						showError(this.$t('inventory', 'An error occurred, unable to delete the place.'))
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
