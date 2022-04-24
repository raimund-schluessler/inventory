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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU AFFERO GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU Affero General Public
License along with this library. If not, see <http://www.gnu.org/licenses/>.

-->

<template>
	<AppSidebar :title="(place && !loading) ? place.name : ''"
		:title-editable="editingTitle"
		:title-tooltip="place ? place.path : null"
		@update:titleEditable="editTitle"
		@update:title="updateTitle"
		@submit-title="saveTitle()"
		@close="closeAppSidebar()">
		<template v-if="(place && place.description && !loading) || editingDescription"
			#description>
			<pre><span>{{ place.description }}</span><br><br></pre>
			<textarea ref="description__editor"
				v-model="place.description"
				v-click-outside="() => { editDescription(false) }"
				@keyup.27="editDescription(false)"
				@keydown.enter.ctrl.prevent="setDescription()"
				@change="setDescription()"
				@click="editDescription(true)" />
		</template>

		<template v-if="place && !loading" #secondary-actions>
			<ActionButton :close-after-click="true"
				@click="editTitle(true)">
				<template #icon>
					<Pencil :size="20" />
				</template>
				{{ t('inventory', 'Rename') }}
			</ActionButton>
			<ActionButton :close-after-click="true"
				@click.stop="editDescription(true)">
				<template #icon>
					<TextBoxOutline :size="20" />
				</template>
				{{ t('inventory', 'Edit description') }}
			</ActionButton>
			<ActionButton :close-after-click="true"
				@click.stop="addUuid = true">
				<template #icon>
					<Plus :size="20" />
				</template>
				{{ t('inventory', 'Add UUID') }}
			</ActionButton>
			<ActionButton :close-after-click="true" @click="openQrModal('move')">
				<template #icon>
					<QrcodePlus :size="20" />
				</template>
				{{ t('inventory', 'Move items to place') }}
			</ActionButton>
		</template>
		<template v-if="place && !loading" #default>
			<!-- qrcode -->
			<QrScanModal :qr-modal-open.sync="qrModalOpen" :status-string="statusMessage" @recognized-qr-code="foundUuid" />
			<Modal v-if="showBarcode"
				id="qrcode-modal"
				size="small"
				@close="closeBarcode">
				<div>
					<canvas ref="canvas" class="qrcode" />
				</div>
			</Modal>
			<div v-if="place.uuids.length || addUuid">
				<h3>{{ t('inventory', 'Linked UUIDs') }}</h3>
			</div>
			<div v-if="addUuid"
				v-click-outside="() => { addUuid = false }"
				class="uuid-input">
				<form name="add-uuid">
					<input v-model="newUuid"
						v-focus
						:placeholder="t('inventory', 'Add UUID')"
						type="text"
						@keyup.27="addUuid = false">
				</form>
				<Actions>
					<ActionButton v-if="newUuidValid"
						key="add"
						@click="setUuid()">
						<template #icon>
							<Check :size="20" />
						</template>
						{{ t('inventory', 'Add UUID') }}
					</ActionButton>
					<ActionButton v-else
						key="scan"
						:close-after-click="true"
						@click="openQrModal('add')">
						<template #icon>
							<QrcodeScan :size="20" />
						</template>
						{{ t('inventory', 'Scan QR code') }}
					</ActionButton>
				</Actions>
			</div>
			<div v-if="place.uuids.length" key="uuids">
				<div>
					<ul>
						<li v-for="uuid in place.uuids" :key="`uuids${uuid.id}`" class="uuid-item">
							<span>{{ uuid.uuid }}</span>
							<Actions>
								<ActionButton :close-after-click="true" @click="openBarcode(uuid.uuid)">
									<template #icon>
										<Qrcode :size="20" />
									</template>
									{{ t('inventory', 'Show QR Code') }}
								</ActionButton>
								<ActionButton @click="removeUuid(uuid.uuid)">
									<template #icon>
										<Delete :size="20" />
									</template>
									{{ t('inventory', 'Delete UUID') }}
								</ActionButton>
							</Actions>
						</li>
					</ul>
				</div>
			</div>
		</template>
		<template v-else #default>
			<EmptyContent v-if="loading">
				<template #icon>
					<Loading :size="50" />
				</template>
				{{ t('inventory', 'Loading the place.') }}
			</EmptyContent>
			<EmptyContent v-else>
				<template #icon>
					<Magnify :size="50" />
				</template>
				{{ t('inventory', 'Place not found.') }}
			</EmptyContent>
		</template>
	</AppSidebar>
</template>

<script>
import Loading from '../components/Loading.vue'
import QrScanModal from '../components/QrScanModal.vue'
import focus from '../directives/focus.vue'
import showBarcode from '../mixins/showBarcode.js'
import { encodePath } from '../utils/encodePath.js'

import { translate as t, translatePlural as n } from '@nextcloud/l10n'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import AppSidebar from '@nextcloud/vue/dist/Components/AppSidebar'
import EmptyContent from '@nextcloud/vue/dist/Components/EmptyContent'

import Check from 'vue-material-design-icons/Check'
import Delete from 'vue-material-design-icons/Delete'
import Magnify from 'vue-material-design-icons/Magnify'
import Pencil from 'vue-material-design-icons/Pencil'
import Plus from 'vue-material-design-icons/Plus'
import Qrcode from 'vue-material-design-icons/Qrcode'
import QrcodePlus from 'vue-material-design-icons/QrcodePlus'
import QrcodeScan from 'vue-material-design-icons/QrcodeScan'
import TextBoxOutline from 'vue-material-design-icons/TextBoxOutline'

import ClickOutside from 'v-click-outside'
import { mapGetters, mapActions } from 'vuex'

export default {
	components: {
		Actions,
		ActionButton,
		AppSidebar,
		Check,
		Delete,
		EmptyContent,
		Loading,
		Magnify,
		Pencil,
		Plus,
		Qrcode,
		QrcodePlus,
		QrcodeScan,
		QrScanModal,
		TextBoxOutline,
	},
	directives: {
		ClickOutside: ClickOutside.directive,
		focus,
	},
	mixins: [showBarcode],
	/**
	 * Before we navigate to a new place, we save possible edits to the place title.
	 *
	 * @param {object} to The target route object being navigated to.
	 * @param {object} from The current route being navigated away from.
	 * @param {Function} next This function must be called to resolve the hook.
	 */
	beforeRouteUpdate(to, from, next) {
		this.saveTitle()
		next()
	},
	props: {
		path: {
			type: String,
			default: '',
		},
		collection: {
			type: String,
			default: 'places',
		},
		folder: {
			type: String,
			default: '',
		},
	},
	data() {
		return {
			title: '',
			editingTitle: false,
			newTitle: '',
			titleSaved: true,
			loading: false,
			addUuid: false,
			newUuid: '',
			qrModalOpen: false,
			qrTarget: '',
			statusMessage: '',
			resetStatusTimeout: null,
			editingDescription: false,
		}
	},
	computed: {
		...mapGetters({
			place: 'getPlace',
		}),

		placePath() {
			return this.folder ? this.folder : this.path
		},

		/**
		 * Checks that the new UUID is valid and not already used for this instance.
		 *
		 * @return {boolean} Whether the new UUID is valid
		 */
		newUuidValid() {
			const uuidArray = this.place.uuids.map(uuid => {
				return uuid.uuid
			})
			return /^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/.test(this.newUuid) && !uuidArray.includes(this.newUuid)
		},
	},

	watch: {
		folder: 'loadPlace',
	},
	created() {
		this.loadPlace()
	},
	methods: {
		t,
		n,

		...mapActions([
			'getPlaceByPath',
			'renamePlace',
			'moveItemByUuid',
			'addUuidToPlace',
			'deleteUuidFromPlace',
			'setPlaceDescription',
		]),

		openQrModal(target) {
			this.qrTarget = target
			this.qrModalOpen = true
		},

		async foundUuid(uuid) {
			if (this.collection === 'places') {
				if (this.qrTarget === 'move') {
					const item = await this.moveItemByUuid({ newPath: this.placePath, uuid })
					this.setStatusMessage(t('inventory', 'Item "{item}" moved', { item: item?.data?.name }))
				} else if (this.qrTarget === 'add') {
					this.newUuid = uuid
					this.qrModalOpen = false
				}
			}
		},

		async setUuid() {
			if (this.newUuidValid) {
				await this.addUuidToPlace({ place: this.place, uuid: this.newUuid })
				this.newUuid = ''
			}
		},

		async removeUuid(uuid) {
			await this.deleteUuidFromPlace({ place: this.place, uuid })
		},

		editDescription(editing) {
			this.editingDescription = editing
			if (editing) {
				this.$nextTick(
					() => {
						this.$refs.description__editor.focus()
					}
				)
			}
		},

		async setDescription() {
			const description = this.place.description
			await this.setPlaceDescription({ place: this.place, description })
			this.editingDescription = false
		},

		setStatusMessage(message) {
			this.statusMessage = message
			if (this.resetStatusTimeout) {
				clearTimeout(this.resetStatusTimeout)
			}
			this.resetStatusTimeout = setTimeout(
				() => {
					this.statusMessage = ''
				}, 3000
			)
		},

		async loadPlace() {
			// If the current place is the correct one, do nothing
			if (this.place?.path === this.placePath) {
				return
			}

			this.loading = true

			await this.getPlaceByPath(this.placePath)

			this.loading = false
		},

		closeAppSidebar() {
			this.saveTitle()
			this.$router.push(`/${this.collection}/${encodePath(this.path)}`)
		},

		editTitle(editing) {
			if (!this.editingTitle && editing) {
				this.newTitle = this.place.name
			}
			this.editingTitle = editing
		},

		updateTitle(title) {
			this.newTitle = title
			this.titleSaved = false
		},

		saveTitle(place = this.place) {
			if (!this.titleSaved && this.newTitle !== place.name && this.collection === 'places') {
				this.renamePlace({ place: this.place, newName: this.newTitle })
			}
			this.titleSaved = true
		},

	},
}
</script>

<style lang="scss" scoped>
::v-deep .app-sidebar-tabs {
	padding: 0 6px 0 10px;
}

::v-deep .app-sidebar-header__description {
	position: relative;
}

pre {
	border: 0 none !important;
	display: block;
	margin: 0;
	outline: 0 none;
	padding: 0 !important;
	visibility: hidden;
}

textarea {
	position: absolute;
	top: 0;
	width: 100%;
	height: 100%;
	font-size: 100%;
	resize: none;
	margin: 0;
}

.uuid-input {
	display: flex;
	line-height: 44px;

	form {
		flex-grow: 1;

		input {
			width: 100%;
		}
	}
}

.uuid-item {
	display: flex;

	span {
		line-height: 44px;
	}
}
</style>
