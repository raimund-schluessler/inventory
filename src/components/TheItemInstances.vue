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
					<th v-for="instanceProperty in instanceProperties" :key="instanceProperty.key">
						<span>{{ instanceProperty.name }}</span>
					</th>
					<th />
				</tr>
			</thead>
			<tbody>
				<template v-for="instance in item.instances">
					<tr :key="'instance-' + instance.id" class="handler">
						<td v-for="instanceProperty in instanceProperties" :key="instanceProperty.key">
							{{ getInstanceProperty(instance, instanceProperty) }}
						</td>
						<td>
							<div>
								<span class="icon icon-bw icon-plus" :instanceId="instance.id" @click="showUuidInput(instance)" />
							</div>
						</td>
					</tr>
					<tr v-if="addUuidTo === instance.id" :key="'uuidInput-' + instance.id"
						v-click-outside="($event) => hideUuidInput($event, instance)"
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
						<td>
							<div>
								<span class="icon icon-bw icon-trash" :instanceId="instance.id" @click="removeUuid(instance, uuid.uuid)" />
								<span class="icon icon-bw icon-qrcode" @click="showQRcode(uuid.uuid)" />
							</div>
						</td>
					</tr>
				</template>
			</tbody>
		</table>
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
import qr from 'qr-image'
import { Modal } from 'nextcloud-vue'

export default {
	components: {
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
				}, {
					key: 'available',
					name: t('inventory', 'Available'),
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
				},
			],
			newUuid: '',
			addUuidTo: null,
			qrcode: null,
		}
	},
	methods: {

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

		showUuidInput: function(instance) {
			this.addUuidTo = instance.id
		},

		hideUuidInput: function(e, instance) {
			if (+e.target.getAttribute('instanceId') !== instance.id && instance.id === this.addUuidTo) {
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

		async setUuid(instance) {
			await this.addUuid({ item: this.item, instance, uuid: this.newUuid })
			this.newUuid = ''
		},

		removeUuid: function(instance, uuid) {
			this.deleteUuid({ item: this.item, instance, uuid })
		},

		...mapActions([
			'addUuid',
			'deleteUuid',
		])
	}
}
</script>
