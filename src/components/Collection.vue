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
	<tr :class="{ selected: isSelected, deleted: !!deleteTimeout }"
		class="handler"
		@click.ctrl="selectEntity(entity)">
		<td class="selection">
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
		</td>
		<td colspan="5">
			<div>
				<RouterLink :to="`/${collection}/${entity.path}`"
					tag="a"
					@click.ctrl.prevent>
					<div class="thumbnail-wrapper">
						<div :style="{ backgroundImage: getThumbnailUrl }"
							class="thumbnail folder" />
					</div>
					<span v-show="!renaming">{{ entity.name }}</span>
				</RouterLink>
				<form v-if="renaming" v-click-outside="{ handler: finishRenaming, middleware: checkClickOutside }" @submit.prevent="rename">
					<input v-model="newName"
						v-focus
						@keyup="checkName">
				</form>
				<Actions v-if="!deleteTimeout && showActions">
					<ActionButton class="startRename"
						icon="icon-rename"
						:close-after-click="true"
						@click="startRename">
						{{ t('inventory', 'Rename') }}
					</ActionButton>
					<ActionButton icon="icon-delete" @click="scheduleDelete">
						{{ deleteString }}
					</ActionButton>
				</Actions>
				<Actions v-if="!!deleteTimeout">
					<ActionButton
						icon="icon-history"
						@click.prevent.stop="cancelDelete">
						{{ undoString }}
					</ActionButton>
				</Actions>
			</div>
		</td>
	</tr>
</template>

<script>
import { mapActions } from 'vuex'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import ClickOutside from 'v-click-outside'
import focus from '../directives/focus'
import { generateUrl } from '@nextcloud/router'

const CD_DURATION = 7

export default {
	components: {
		Actions,
		ActionButton,
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
						this.$toast.error(this.$t('inventory', 'An error occurred, unable to delete the folder.'))
					} else if (this.collection === 'places') {
						this.$toast.error(this.$t('inventory', 'An error occurred, unable to delete the place.'))
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
