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
					<th v-for="instanceProperty in instanceProperties" :key="instanceProperty.key" :class="instanceProperty.width">
						<span>{{ instanceProperty.name }}</span>
					</th>
					<th class="actions">
						<div class="add-instance">
							<span add-instance="true" class="icon icon-bw icon-plus" @click="toggleInstanceInput" />
						</div>
					</th>
				</tr>
			</thead>
			<tbody>
				<template v-for="instance in item.instances">
					<tr v-if="editedInstance.id !== instance.id" :key="'instance-' + instance.id" class="handler">
						<td v-for="instanceProperty in instanceProperties" :key="instanceProperty.key" :class="instanceProperty.width">
							{{ getInstanceProperty(instance, instanceProperty) }}
						</td>
						<td class="actions">
							<div>
								<Dropdown :menu="instanceActions(instance)" />
							</div>
						</td>
					</tr>
					<tr v-else :key="'editinstance-' + instance.id" v-click-outside="() => { hideEditInstance(instance) }">
						<td v-for="instanceProperty in instanceProperties" :key="instanceProperty.key" :class="instanceProperty.width">
							<div v-if="instanceProperty.key === 'place'">
								{{ getInstanceProperty(instance, instanceProperty) }}
							</div>
							<input v-else v-model="editedInstance[instanceProperty.key]"
								type="text"
								:placeholder="getInstanceProperty(instance, instanceProperty)"
								:name="instanceProperty.key"
								form="edit_instance"
							>
						</td>
						<td class="actions">
							<!-- Submit button -->
							<button type="submit" form="edit_instance">
								{{ t('inventory', 'Save') }}
							</button>
						</td>
					</tr>
					<tr v-if="addUuidTo === instance.id" :key="'uuidInput-' + instance.id"
						v-click-outside="() => hideUuidInput(instance)"
					>
						<td :colspan="instanceProperties.length + 1">
							<form name="addUuid" @submit.prevent="setUuid(instance)">
								<input v-model="newUuid"
									v-focus
									placeholder="Add UUID"
									@keyup.27="addUuidTo = null"
								>
							</form>
						</td>
					</tr>
					<tr v-for="uuid in instance.uuids" :key="'uuids' + instance.id + uuid.id">
						<td :colspan="instanceProperties.length">
							{{ uuid.uuid }}
						</td>
						<td class="actions">
							<Dropdown :menu="uuidActions(instance, uuid.uuid)" />
						</td>
					</tr>
				</template>
				<tr v-if="!item.instances.length">
					<td class="center" :colspan="instanceProperties.length + 1">
						{{ t('inventory', 'This item has no instances.') }}
					</td>
				</tr>
				<tr v-if="addingInstance" v-click-outside="hideInstanceInput">
					<td v-for="instanceProperty in instanceProperties" :key="instanceProperty.key" :class="instanceProperty.width">
						<input v-model="newInstance[instanceProperty.key]"
							type="text"
							:placeholder="instanceProperty.name"
							:name="instanceProperty.key"
							form="new_instance"
						>
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
		<modal v-if="qrcode" id="qrcode-modal"
			@close="closeQrModal"
		>
			<img :src="`data:image/svg+xml;base64,${qrcode}`" class="qrcode" width="400">
		</modal>
	</div>
</template>

<script>
import { mapActions } from 'vuex'
import focus from '../directives/focus'
import ClickOutside from 'vue-click-outside'
import Dropdown from './Dropdown.vue'
import qr from 'qr-image'
import { Modal } from 'nextcloud-vue'

export default {
	components: {
		Dropdown,
		Modal,
	},
	directives: {
		ClickOutside,
		focus,
	},
	props: {
		item: {
			type: Object,
			required: true,
		}
	},
	data: function() {
		return {
			instanceProperties: [
				{
					key: 'count',
					name: t('inventory', 'Count'),
					width: 'narrow',
				}, {
					key: 'available',
					name: t('inventory', 'Available'),
					width: 'narrow',
				}, {
					key: 'price',
					name: t('inventory', 'Price'),
				}, {
					key: 'date',
					name: t('inventory', 'Date'),
				}, {
					key: 'vendor',
					name: t('inventory', 'Vendor'),
				}, {
					key: 'place',
					fn: this.getPlace,
					name: t('inventory', 'Place'),
				}, {
					key: 'comment',
					name: t('inventory', 'Comment'),
					width: 'wide',
				},
			],
			newUuid: '',
			addUuidTo: null,
			addingInstance: false,
			newInstance: {},
			qrcode: null,
			editedInstance: {},
		}
	},
	methods: {

		uuidActions(instance, uuid) {
			return [
				{
					icon: 'icon-qrcode',
					text: t('inventory', 'Show QR Code'),
					action: () => { this.showQRcode(uuid) },
				},
				{
					icon: 'icon-delete',
					text: t('inventory', 'Delete UUID'),
					action: () => { this.removeUuid(instance, uuid) },
				}
			]
		},

		instanceActions(instance) {
			return [
				{
					icon: 'icon-add',
					text: t('inventory', 'Add UUID'),
					action: () => { this.toggleUuidInput(instance) }
				},
				{
					icon: 'icon-rename',
					text: t('inventory', 'Edit instance'),
					action: () => { this.toggleEditInstance(instance) }
				},
				{
					icon: 'icon-delete',
					text: t('inventory', 'Delete instance'),
					action: () => { this.removeInstance(instance) }
				}

			]
		},

		/**
		 * Generate a qrcode for the UUID
		 * @param {String} uuid The UUID
		 */
		showQRcode(uuid) {
			if (uuid.length > 0) {
				this.qrcode = btoa(qr.imageSync(uuid, { type: 'svg' }))
			}
		},

		// reset the current qrcode
		closeQrModal() {
			this.qrcode = null
		},

		toggleEditInstance: function(instance) {
			if (this.editedInstance.id === instance.id) {
				this.editedInstance = {}
			} else {
				this.editedInstance = Object.assign({}, instance)
			}
		},

		hideEditInstance: function(instance) {
			if (this.editedInstance.id === instance.id) {
				this.editedInstance = {}
			}
		},

		toggleInstanceInput: function() {
			this.addingInstance = !this.addingInstance
		},

		hideInstanceInput: function(e) {
			if (!e.target.getAttribute('add-instance')) {
				this.addingInstance = false
			}
		},

		toggleUuidInput: function(instance) {
			if (this.addUuidTo === instance.id) {
				this.addUuidTo = null
			} else {
				this.addUuidTo = instance.id
			}
		},

		hideUuidInput: function(instance) {
			if (instance.id === this.addUuidTo) {
				this.addUuidTo = null
			}
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
			await this.addUuid({ item: this.item, instance, uuid: this.newUuid })
			this.newUuid = ''
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
		])
	}
}
</script>
