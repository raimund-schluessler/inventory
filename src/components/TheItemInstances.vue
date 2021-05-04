<!--
Nextcloud - Inventory

@author Raimund Schlüßler
@copyright 2017 Raimund Schlüßler <raimund.schluessler@mailbox.org>

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
	<div>
		<div class="table table--instances">
			<div class="row row--wide-header">
				<div v-for="instanceProperty in instanceProperties"
					:key="`wide-header-${instanceProperty.key}`"
					class="column column--wide-header">
					<span>{{ instanceProperty.name }}</span>
				</div>
				<div class="column column--actions">
					<Actions :boundaries-element="boundaries">
						<ActionButton @click="toggleInstanceInput">
							<Plus slot="icon" :size="24" decorative />
							{{ t('inventory', 'Add instance') }}
						</ActionButton>
					</Actions>
				</div>
			</div>
			<template v-for="instance in item.instances">
				<div v-if="editedInstance.id !== instance.id"
					:key="`instance-${instance.id}`"
					class="row row--properties"
					:class="{active: instanceActive(instance)}">
					<div class="column column--narrow-header column--narrow-spacer" />
					<template v-for="instanceProperty in instanceProperties">
						<div :key="`narrow-header-${instanceProperty.key}`" class="column column--narrow-header">
							<span>{{ instanceProperty.name }}</span>
						</div>
						<div :key="`body-${instanceProperty.key}`" class="column">
							<router-link v-if="instanceProperty.key === 'place' && instance.place" :to="`/places/${instance.place.path}`">
								{{ getInstanceProperty(instance, instanceProperty) }}
							</router-link>
							<span v-else>
								{{ getInstanceProperty(instance, instanceProperty) }}
							</span>
						</div>
					</template>
					<div class="column column--actions">
						<Actions :boundaries-element="boundaries">
							<ActionButton :close-after-click="true" @click="toggleUuidInput(instance)">
								<Plus slot="icon" :size="24" decorative />
								{{ t('inventory', 'Add UUID') }}
							</ActionButton>
							<ActionButton @click="toggleEditInstance(instance)">
								<Pencil slot="icon" :size="24" decorative />
								{{ t('inventory', 'Edit instance') }}
							</ActionButton>
							<ActionButton @click="removeInstance(instance)">
								<Delete slot="icon" :size="24" decorative />
								{{ t('inventory', 'Delete instance') }}
							</ActionButton>
						</Actions>
					</div>
				</div>
				<div v-else
					:key="`editinstance-${instance.id}`"
					v-click-outside="() => { hideEditInstance(instance) }"
					class="row row--properties">
					<div class="column column--narrow-header column--narrow-spacer" />
					<template v-for="instanceProperty in instanceProperties">
						<div :key="`narrow-edit-header-${instanceProperty.key}`" class="column column--narrow-header">
							<span>{{ instanceProperty.name }}</span>
						</div>
						<div :key="`edit-body-${instanceProperty.key}`" class="column column--input">
							<div v-if="instanceProperty.key === 'place'">
								{{ getInstanceProperty(instance, instanceProperty) }}
							</div>
							<input v-else
								v-model="editedInstance[instanceProperty.key]"
								type="text"
								:placeholder="getInstanceProperty(instance, instanceProperty)"
								:name="instanceProperty.key"
								form="edit_instance">
						</div>
					</template>
					<div class="column column--actions">
						<Actions :boundaries-element="boundaries">
							<ActionButton :close-after-click="true" @click="saveInstance">
								<Check slot="icon" :size="24" decorative />
								{{ t('inventory', 'Save instance') }}
							</ActionButton>
						</Actions>
					</div>
				</div>
				<div v-if="addUuidTo === instance.id"
					:key="`uuidInput-${instance.id}`"
					v-click-outside="() => hideUuidInput(instance)"
					class="row row--add-uuid">
					<div class="column column--add-uuid">
						<form name="add-uuid">
							<input v-model="newUuid"
								v-focus
								:placeholder="t('inventory', 'Add UUID')"
								type="text"
								@keyup.27="addUuidTo = null">
						</form>
					</div>
					<div>
						<Actions :boundaries-element="boundaries">
							<ActionButton v-if="newUuidValid(instance.uuids)" @click="setUuid(instance)">
								<Check slot="icon" :size="24" decorative />
								{{ t('inventory', 'Add UUID') }}
							</ActionButton>
							<ActionButton v-else
								:close-after-click="true"
								@click="openQrModal">
								<QrcodeScan slot="icon" :size="24" decorative />
								{{ t('inventory', 'Scan QR code') }}
							</ActionButton>
						</Actions>
					</div>
				</div>
				<div v-if="instance.uuids.length" :key="`uuids${instance.id}`" class="row row--column-2">
					<div class="column column--center">
						{{ t('inventory', 'UUIDs') }}
					</div>
					<div class="column column--uuids">
						<ul>
							<li v-for="uuid in instance.uuids" :key="`uuids${instance.id}${uuid.id}`">
								<span>{{ uuid.uuid }}</span>
								<Actions :boundaries-element="boundaries">
									<ActionButton :close-after-click="true" @click="$emit('open-barcode', uuid.uuid)">
										<Qrcode slot="icon" :size="24" decorative />
										{{ t('inventory', 'Show QR Code') }}
									</ActionButton>
									<ActionButton @click="removeUuid(instance, uuid.uuid)">
										<Delete slot="icon" :size="24" decorative />
										{{ t('inventory', 'Delete UUID') }}
									</ActionButton>
								</Actions>
							</li>
						</ul>
					</div>
				</div>
				<div :key="`attachments${instance.id}`" class="row row--column-2">
					<div class="column column--center">
						{{ t('inventory', 'Attachments') }}
					</div>
					<Attachments :attachments="instance.attachments"
						:item-id="String(item.id)"
						:instance-id="String(instance.id)"
						:loading-attachments="loadingInstanceAttachments({ itemID: item.id, instanceID: instance.id })"
						class="column column--attachments" />
				</div>
			</template>
			<div v-if="!item.instances.length" class="row row--empty">
				<div class="column column--center">
					{{ t('inventory', 'This item has no instances.') }}
				</div>
			</div>
			<div v-if="addingInstance" v-click-outside="hideInstanceInput" class="row row--properties">
				<div class="column column--narrow-header column--narrow-spacer" />
				<template v-for="instanceProperty in instanceProperties">
					<div :key="instanceProperty.key" class="column column--narrow-header">
						<span>{{ instanceProperty.name }}</span>
					</div>
					<div :key="instanceProperty.key" class="column column--input">
						<input v-model="newInstance[instanceProperty.key]"
							type="text"
							:placeholder="instanceProperty.name"
							:name="instanceProperty.key"
							form="new_instance">
					</div>
				</template>
				<div class="column column--actions">
					<Actions :boundaries-element="boundaries">
						<ActionButton icon="icon-checkmark" :close-after-click="true" @click="putInstance">
							{{ t('inventory', 'Add instance') }}
						</ActionButton>
					</Actions>
				</div>
			</div>
		</div>
		<form id="new_instance" method="POST" />
		<form id="edit_instance" method="POST" />
		<!-- qrcode -->
		<QrScanModal :qr-modal-open.sync="qrModalOpen" @recognized-qr-code="foundUuid" />
	</div>
</template>

<script>
import focus from '../directives/focus.vue'
import Attachments from './Attachments.vue'
import QrScanModal from './QrScanModal.vue'

import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'

import Check from 'vue-material-design-icons/Check.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Qrcode from 'vue-material-design-icons/Qrcode.vue'
import QrcodeScan from 'vue-material-design-icons/QrcodeScan.vue'

import ClickOutside from 'vue-click-outside'
import { mapActions, mapGetters } from 'vuex'

export default {
	components: {
		Actions,
		ActionButton,
		Attachments,
		QrScanModal,
		Check,
		Delete,
		Pencil,
		Plus,
		Qrcode,
		QrcodeScan,
	},
	directives: {
		ClickOutside,
		focus,
	},
	props: {
		item: {
			type: Object,
			required: true,
		},
		instanceId: {
			type: String,
			default: null,
		},
	},
	data() {
		return {
			instanceProperties: [
				{
					key: 'count',
					name: this.t('inventory', 'Count'),
				}, {
					key: 'available',
					name: this.t('inventory', 'Available'),
				}, {
					key: 'price',
					name: this.t('inventory', 'Price'),
				}, {
					key: 'date',
					name: this.t('inventory', 'Date'),
				}, {
					key: 'vendor',
					name: this.t('inventory', 'Vendor'),
				}, {
					key: 'place',
					fn: this.getPlace,
					name: this.t('inventory', 'Place'),
				}, {
					key: 'comment',
					name: this.t('inventory', 'Comment'),
				},
			],
			newUuid: '',
			addUuidTo: null,
			addingInstance: false,
			newInstance: {},
			editedInstance: {},
			closing: true,
			qrModalOpen: false,
			// Hack to fix https://github.com/nextcloud/nextcloud-vue/issues/1384
			boundaries: document.querySelector('#content-vue'),
		}
	},
	computed: {
		...mapGetters({
			loadingInstanceAttachments: 'loadingInstanceAttachments',
		}),
	},
	methods: {
		instanceActive(instance) {
			return +instance.id === +this.instanceId
		},
		/**
		 * Checks that the new UUID is valid and not already used for this instance.
		 * @param {Array} uuids The already used UUIDs
		 * @returns {Boolean} Whether the new UUID is valid
		 */
		newUuidValid(uuids) {
			const uuidArray = uuids.map(uuid => {
				return uuid.uuid
			})
			return /^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/.test(this.newUuid) && !uuidArray.includes(this.newUuid)
		},

		openQrModal() {
			this.qrModalOpen = true
		},

		foundUuid(uuid) {
			this.newUuid = uuid
			this.qrModalOpen = false
		},

		toggleEditInstance(instance) {
			if (this.editedInstance.id === instance.id) {
				this.editedInstance = {}
			} else {
				this.editedInstance = Object.assign({}, instance)
				this.closing = false
			}
		},

		hideEditInstance(instance) {
			if (this.closing && this.editedInstance.id === instance.id) {
				this.editedInstance = {}
			}
			this.closing = true
		},

		toggleInstanceInput() {
			this.addingInstance = !this.addingInstance
			// Temporarily disable the click-outside-directive
			if (this.addingInstance) {
				this.closing = false
			}
		},

		hideInstanceInput(e) {
			if (this.closing) {
				this.addingInstance = false
			}
			this.closing = true
		},

		toggleUuidInput(instance) {
			if (this.addUuidTo === instance.id) {
				this.addUuidTo = null
			} else {
				this.addUuidTo = instance.id
				// Temporarily disable the click-outside-directive
				this.closing = false
			}
		},

		hideUuidInput(instance) {
			if (this.closing && instance.id === this.addUuidTo) {
				this.addUuidTo = null
			}
			// Enable closing the menus again
			this.closing = true
		},

		getPlace(place) {
			if (place) {
				return place.name
			} else {
				return ''
			}
		},
		getInstanceProperty(instance, instanceProperty) {
			if (typeof instanceProperty.fn === 'function') {
				return instanceProperty.fn(instance[instanceProperty.key])
			} else {
				return instance[instanceProperty.key]
			}
		},

		async putInstance() {
			await this.addInstance({ item: this.item, instance: this.newInstance })
			this.newInstance = {}
		},

		removeInstance(instance) {
			this.deleteInstance({ item: this.item, instance })
		},

		async saveInstance() {
			await this.editInstance({ item: this.item, instance: this.editedInstance })
			this.editedInstance = {}
		},

		async setUuid(instance) {
			if (this.newUuidValid(instance.uuids)) {
				await this.addUuid({ item: this.item, instance, uuid: this.newUuid })
				this.newUuid = ''
			}
		},

		removeUuid(instance, uuid) {
			this.deleteUuid({ item: this.item, instance, uuid })
		},

		...mapActions([
			'addInstance',
			'deleteInstance',
			'editInstance',
			'addUuid',
			'deleteUuid',
		]),
	},
}
</script>
