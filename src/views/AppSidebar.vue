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
	<NcAppSidebar :name="(place && !loading) ? place.name : ''"
		:name-editable="editingName"
		:name-tooltip="place ? place.path : null"
		@update:name-editable="editName"
		@update:name="updateName"
		@submit-name="saveName()"
		@close="closeAppSidebar()">
		<template v-if="(place && place.description && !loading) || editingDescription"
			#description>
			<pre><span>{{ place.description }}</span><br><br></pre>
			<textarea ref="description__editor"
				v-model="place.description"
				v-click-outside="() => { editDescription(false) }"
				@keyup.escape="editDescription(false)"
				@keydown.enter.ctrl.prevent="setDescription()"
				@change="setDescription()"
				@click="editDescription(true)" />
		</template>

		<template v-if="place && !loading" #secondary-actions>
			<NcActionButton :close-after-click="true"
				@click="editName(true)">
				<template #icon>
					<Pencil :size="20" />
				</template>
				{{ t('inventory', 'Rename') }}
			</NcActionButton>
			<NcActionButton :close-after-click="true"
				@click.stop="editDescription(true)">
				<template #icon>
					<TextBoxOutline :size="20" />
				</template>
				{{ t('inventory', 'Edit description') }}
			</NcActionButton>
			<NcActionButton :close-after-click="true"
				@click.stop="addUuid = true">
				<template #icon>
					<Plus :size="20" />
				</template>
				{{ t('inventory', 'Add UUID') }}
			</NcActionButton>
			<NcActionButton :close-after-click="true" @click="openQrModal('move')">
				<template #icon>
					<QrcodePlus :size="20" />
				</template>
				{{ t('inventory', 'Move items to place') }}
			</NcActionButton>
		</template>
		<template v-if="place && !loading" #default>
			<!-- qrcode -->
			<QrScanModal v-model:qr-modal-open="qrModalOpen" :status-string="statusMessage" @codesDetected="foundUuid" />
			<NcModal v-if="showBarcode"
				class="qrcode-modal"
				size="small"
				@close="closeBarcode">
				<div>
					<canvas ref="canvas" class="qrcode" />
				</div>
			</NcModal>
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
						@keyup.escape="addUuid = false">
				</form>
				<NcActions>
					<NcActionButton v-if="newUuidValid"
						key="add"
						@click="setUuid()">
						<template #icon>
							<Check :size="20" />
						</template>
						{{ t('inventory', 'Add UUID') }}
					</NcActionButton>
					<NcActionButton v-else
						key="scan"
						:close-after-click="true"
						@click="openQrModal('add')">
						<template #icon>
							<QrcodeScan :size="20" />
						</template>
						{{ t('inventory', 'Scan QR code') }}
					</NcActionButton>
				</NcActions>
			</div>
			<div v-if="place.uuids.length" key="uuids">
				<div>
					<ul>
						<li v-for="uuid in place.uuids" :key="`uuids${uuid.id}`" class="uuid-item">
							<span>{{ uuid.uuid }}</span>
							<NcActions>
								<NcActionButton :close-after-click="true" @click="openBarcode(uuid.uuid)">
									<template #icon>
										<Qrcode :size="20" />
									</template>
									{{ t('inventory', 'Show QR Code') }}
								</NcActionButton>
								<NcActionButton @click="removeUuid(uuid.uuid)">
									<template #icon>
										<Delete :size="20" />
									</template>
									{{ t('inventory', 'Delete UUID') }}
								</NcActionButton>
							</NcActions>
						</li>
					</ul>
				</div>
			</div>
		</template>
		<template v-else #default>
			<NcEmptyContent v-if="loading" :name="t('inventory', 'Loading the place.')">
				<template #icon>
					<NcLoadingIcon :size="50" />
				</template>
			</NcEmptyContent>
			<NcEmptyContent v-else :name="t('inventory', 'Place not found.')">
				<template #icon>
					<Magnify :size="50" />
				</template>
			</NcEmptyContent>
		</template>
	</NcAppSidebar>
</template>

<script>
import QrScanModal from '../components/QrScanModal.vue'
import focus from '../directives/focus.vue'
import showBarcode from '../mixins/showBarcode.js'
import { encodePath } from '../utils/encodePath.js'
import isUuid from '../utils/isUuid.js'

import { translate as t, translatePlural as n } from '@nextcloud/l10n'
import {
	NcActions,
	NcActionButton,
	NcAppSidebar,
	NcEmptyContent,
	NcLoadingIcon,
} from '@nextcloud/vue'

import Check from 'vue-material-design-icons/Check.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Qrcode from 'vue-material-design-icons/Qrcode.vue'
import QrcodePlus from 'vue-material-design-icons/QrcodePlus.vue'
import QrcodeScan from 'vue-material-design-icons/QrcodeScan.vue'
import TextBoxOutline from 'vue-material-design-icons/TextBoxOutline.vue'

import { vOnClickOutside as ClickOutside } from '@vueuse/components'
import { mapGetters, mapActions } from 'vuex'

export default {
	components: {
		NcActions,
		NcActionButton,
		NcAppSidebar,
		Check,
		Delete,
		NcEmptyContent,
		NcLoadingIcon,
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
		ClickOutside,
		focus,
	},
	mixins: [showBarcode],
	/**
	 * Before we navigate to a new place, we save possible edits to the place name.
	 *
	 * @param {object} to The target route object being navigated to.
	 * @param {object} from The current route being navigated away from.
	 * @param {Function} next This function must be called to resolve the hook.
	 */
	beforeRouteUpdate(to, from, next) {
		this.saveName()
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
			name: '',
			editingName: false,
			newName: '',
			nameSaved: true,
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

		async foundUuid(codes) {
			// We use the first valid UUID
			const code = codes.find(code => isUuid(code.rawValue))
			if (!code) return

			if (this.collection === 'places') {
				if (this.qrTarget === 'move') {
					const item = await this.moveItemByUuid({ newPath: this.placePath, uuid: code.rawValue })
					this.setStatusMessage(t('inventory', 'Item "{item}" moved', { item: item?.data?.name }))
				} else if (this.qrTarget === 'add') {
					this.newUuid = code.rawValue
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
					},
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
				}, 3000,
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
			this.saveName()
			this.$router.push(`/${this.collection}/${encodePath(this.path)}`)
		},

		editName(editing) {
			if (!this.editingName && editing) {
				this.newName = this.place.name
			}
			this.editingName = editing
		},

		updateName(name) {
			this.newName = name
			this.nameSaved = false
		},

		saveName(place = this.place) {
			if (!this.nameSaved && this.newName !== place.name && this.collection === 'places') {
				this.renamePlace({ place: this.place, newName: this.newName })
			}
			this.nameSaved = true
		},

	},
}
</script>

<style lang="scss" scoped>
:deep(.app-sidebar-tabs) {
	padding: 0 6px 0 10px;
}

:deep(.app-sidebar-header__description) {
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

.qrcode-modal .modal-container .qrcode {
	min-width: 200px;
	max-width: 100%;
	width: 400px;
}

.uuid-item {
	display: flex;

	span {
		line-height: 44px;
	}
}
</style>
