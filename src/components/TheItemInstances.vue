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
		<table class="instances">
			<thead>
				<tr>
					<th v-for="instanceProperty in instanceProperties" :key="instanceProperty.key" :class="instanceProperty.class">
						<span>{{ instanceProperty.name }}</span>
					</th>
					<th class="actions">
						<div>
							<Actions>
								<ActionButton icon="icon-add" @click="toggleInstanceInput">
									{{ t('inventory', 'Add instance') }}
								</ActionButton>
							</Actions>
						</div>
					</th>
				</tr>
			</thead>
			<tbody>
				<template v-for="instance in item.instances">
					<tr v-if="editedInstance.id !== instance.id" :key="`instance-${instance.id}`" class="handler">
						<td v-for="instanceProperty in instanceProperties" :key="instanceProperty.key" :class="instanceProperty.class">
							{{ getInstanceProperty(instance, instanceProperty) }}
						</td>
						<td class="actions">
							<div>
								<Actions>
									<ActionButton icon="icon-add" :close-after-click="true" @click="toggleUuidInput(instance)">
										{{ t('inventory', 'Add UUID') }}
									</ActionButton>
									<ActionButton icon="icon-rename" @click="toggleEditInstance(instance)">
										{{ t('inventory', 'Edit instance') }}
									</ActionButton>
									<ActionButton icon="icon-delete" @click="removeInstance(instance)">
										{{ t('inventory', 'Delete instance') }}
									</ActionButton>
								</Actions>
							</div>
						</td>
					</tr>
					<tr v-else :key="`editinstance-${instance.id}`" v-click-outside="() => { hideEditInstance(instance) }">
						<td v-for="instanceProperty in instanceProperties" :key="instanceProperty.key" :class="instanceProperty.class">
							<div v-if="instanceProperty.key === 'place'">
								{{ getInstanceProperty(instance, instanceProperty) }}
							</div>
							<input v-else
								v-model="editedInstance[instanceProperty.key]"
								type="text"
								:placeholder="getInstanceProperty(instance, instanceProperty)"
								:name="instanceProperty.key"
								form="edit_instance">
						</td>
						<td class="actions">
							<!-- Submit button -->
							<button type="submit" form="edit_instance">
								{{ t('inventory', 'Save') }}
							</button>
						</td>
					</tr>
					<tr v-if="addUuidTo === instance.id"
						:key="`uuidInput-${instance.id}`"
						v-click-outside="() => hideUuidInput(instance)">
						<td :colspan="instanceProperties.length + 1" class="add-uuid">
							<form name="addUuid" @submit.prevent="setUuid(instance)">
								<input v-model="newUuid"
									v-focus
									:placeholder="t('inventory', 'Add UUID')"
									type="text"
									@keyup.27="addUuidTo = null">
								<input type="button"
									:class="{valid: newUuidValid(instance.uuids)}"
									class="icon-qrcode"
									@click="openQrModal">
								<input v-if="newUuidValid(instance.uuids)"
									type="submit"
									class="icon-confirm"
									value="">
							</form>
						</td>
					</tr>
					<tr v-if="instance.uuids.length" :key="`uuids${instance.id}`">
						<td class="center">
							{{ t('inventory', 'UUIDs') }}
						</td>
						<td class="uuids" :colspan="instanceProperties.length">
							<ul>
								<li v-for="uuid in instance.uuids" :key="`uuids${instance.id}${uuid.id}`">
									<span>{{ uuid.uuid }}</span>
									<div class="actions">
										<Actions>
											<ActionButton icon="icon-qrcode" :close-after-click="true" @click="$emit('openBarcode', uuid.uuid)">
												{{ t('inventory', 'Show QR Code') }}
											</ActionButton>
											<ActionButton icon="icon-delete" @click="removeUuid(instance, uuid.uuid)">
												{{ t('inventory', 'Delete UUID') }}
											</ActionButton>
										</Actions>
									</div>
								</li>
							</ul>
						</td>
					</tr>
					<tr v-if="instance.attachments && instance.attachments.length" :key="`attachments${instance.id}`">
						<td class="center">
							{{ t('inventory', 'Attachments') }}
						</td>
						<td :colspan="instanceProperties.length" class="attachment-list">
							<Attachments :attachments="instance.attachments" />
						</td>
					</tr>
				</template>
				<tr v-if="!item.instances.length">
					<td class="center" :colspan="instanceProperties.length + 1">
						{{ t('inventory', 'This item has no instances.') }}
					</td>
				</tr>
				<tr v-if="addingInstance" v-click-outside="hideInstanceInput">
					<td v-for="instanceProperty in instanceProperties" :key="instanceProperty.key" :class="instanceProperty.class">
						<input v-model="newInstance[instanceProperty.key]"
							type="text"
							:placeholder="instanceProperty.name"
							:name="instanceProperty.key"
							form="new_instance">
					</td>
					<td class="actions">
						<!-- Submit button -->
						<button type="submit" form="new_instance">
							{{ t('inventory', 'Save') }}
						</button>
					</td>
				</tr>
			</tbody>
		</table>
		<form id="new_instance" method="POST" @submit.prevent="putInstance" />
		<form id="edit_instance" method="POST" @submit.prevent="saveInstance" />
		<!-- qrcode -->
		<QrScanModal :qr-modal-open.sync="qrModalOpen" @recognizedQrCode="foundUuid" />
	</div>
</template>

<script>
import { mapActions } from 'vuex'
import focus from '../directives/focus'
import Attachments from './Attachments.vue'
import ClickOutside from 'vue-click-outside'
import QrScanModal from './QrScanModal.vue'
import { Actions } from '@nextcloud/vue/dist/Components/Actions'
import { ActionButton } from '@nextcloud/vue/dist/Components/ActionButton'

export default {
	components: {
		Actions,
		ActionButton,
		Attachments,
		QrScanModal,
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
	},
	data: function() {
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
					class: 'hide-if-narrow',
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
					class: 'hide-if-narrow',
				},
			],
			newUuid: '',
			addUuidTo: null,
			addingInstance: false,
			newInstance: {},
			editedInstance: {},
			closing: true,
			qrModalOpen: false,
		}
	},
	methods: {
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

		openQrModal: function() {
			this.qrModalOpen = true
		},

		foundUuid(uuid) {
			this.newUuid = uuid
			this.qrModalOpen = false
		},

		toggleEditInstance: function(instance) {
			if (this.editedInstance.id === instance.id) {
				this.editedInstance = {}
			} else {
				this.editedInstance = Object.assign({}, instance)
				this.closing = false
			}
		},

		hideEditInstance: function(instance) {
			if (this.closing && this.editedInstance.id === instance.id) {
				this.editedInstance = {}
			}
			this.closing = true
		},

		toggleInstanceInput: function() {
			this.addingInstance = !this.addingInstance
			// Temporarily disable the click-outside-directive
			if (this.addingInstance) {
				this.closing = false
			}
		},

		hideInstanceInput: function(e) {
			if (this.closing) {
				this.addingInstance = false
			}
			this.closing = true
		},

		toggleUuidInput: function(instance) {
			if (this.addUuidTo === instance.id) {
				this.addUuidTo = null
			} else {
				this.addUuidTo = instance.id
				// Temporarily disable the click-outside-directive
				this.closing = false
			}
		},

		hideUuidInput: function(instance) {
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

		removeInstance: function(instance) {
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

		removeUuid: function(instance, uuid) {
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
