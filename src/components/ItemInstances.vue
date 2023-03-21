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
					<NcActions :boundaries-element="boundaries">
						<NcActionButton @click="showInstanceInput">
							<template #icon>
								<Plus :size="20" />
							</template>
							{{ t('inventory', 'Add instance') }}
						</NcActionButton>
					</NcActions>
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
						<NcActions :boundaries-element="boundaries">
							<NcActionButton :close-after-click="true" @click="showUuidInput(instance)">
								<template #icon>
									<Plus :size="20" />
								</template>
								{{ t('inventory', 'Add UUID') }}
							</NcActionButton>
							<NcActionButton @click="showEditInstance(instance)">
								<template #icon>
									<Pencil :size="20" />
								</template>
								{{ t('inventory', 'Edit instance') }}
							</NcActionButton>
							<NcActionButton @click="removeInstance(instance)">
								<template #icon>
									<Delete :size="20" />
								</template>
								{{ t('inventory', 'Delete instance') }}
							</NcActionButton>
						</NcActions>
					</div>
				</div>
				<div v-else
					:key="`editinstance-${instance.id}`"
					v-click-outside="($event) => { hideEditInstance(instance, $event) }"
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
							<NcDatetimePicker v-else-if="instanceProperty.key === 'date'"
								append-to-body
								:value="new Date(editedInstance.date)"
								format="YYYY-MM-DD"
								type="date"
								@change="setDate" />
							<input v-else
								v-model="editedInstance[instanceProperty.key]"
								type="text"
								:placeholder="getInstanceProperty(instance, instanceProperty)"
								:name="instanceProperty.key"
								form="edit_instance">
						</div>
					</template>
					<div class="column column--actions">
						<NcActions :boundaries-element="boundaries">
							<NcActionButton :close-after-click="true" @click="saveInstance">
								<template #icon>
									<Check :size="20" />
								</template>
								{{ t('inventory', 'Save instance') }}
							</NcActionButton>
						</NcActions>
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
								@keyup.escape="addUuidTo = null">
						</form>
					</div>
					<div>
						<NcActions :boundaries-element="boundaries">
							<NcActionButton v-if="newUuidValid(instance.uuids)"
								key="add"
								@click="setUuid(instance)">
								<template #icon>
									<Check :size="20" />
								</template>
								{{ t('inventory', 'Add UUID') }}
							</NcActionButton>
							<NcActionButton v-else
								key="scan"
								:close-after-click="true"
								@click="openQrModal">
								<template #icon>
									<QrcodeScan :size="20" />
								</template>
								{{ t('inventory', 'Scan QR code') }}
							</NcActionButton>
						</NcActions>
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
								<NcActions :boundaries-element="boundaries">
									<NcActionButton :close-after-click="true" @click="$emit('open-barcode', uuid.uuid)">
										<template #icon>
											<Qrcode :size="20" />
										</template>
										{{ t('inventory', 'Show QR Code') }}
									</NcActionButton>
									<NcActionButton @click="removeUuid(instance, uuid.uuid)">
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
					<div :key="`label-${instanceProperty.key}`" class="column column--narrow-header">
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
					<NcActions :boundaries-element="boundaries">
						<NcActionButton :close-after-click="true" @click="putInstance">
							<template #icon>
								<Check :size="20" />
							</template>
							{{ t('inventory', 'Add instance') }}
						</NcActionButton>
					</NcActions>
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

import { translate as t } from '@nextcloud/l10n'
import {
	NcActions,
	NcActionButton,
	NcDatetimePicker,
} from '@nextcloud/vue'

import Check from 'vue-material-design-icons/Check.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Qrcode from 'vue-material-design-icons/Qrcode.vue'
import QrcodeScan from 'vue-material-design-icons/QrcodeScan.vue'

import { formatISO } from 'date-fns'
import { vOnClickOutside as ClickOutside } from '@vueuse/components'
import { mapActions, mapGetters } from 'vuex'

export default {
	components: {
		NcActions,
		NcActionButton,
		NcDatetimePicker,
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
	emits: [
		'open-barcode',
	],
	data() {
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
			addingInstance: false,
			newInstance: {},
			editedInstance: {},
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
		t,

		instanceActive(instance) {
			return +instance.id === +this.instanceId
		},
		setDate(date) {
			this.editedInstance.date = formatISO(date, { representation: 'date' })
		},
		/**
		 * Checks that the new UUID is valid and not already used for this instance.
		 *
		 * @param {Array} uuids The already used UUIDs
		 * @return {boolean} Whether the new UUID is valid
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

		showEditInstance(instance) {
			if (this.editedInstance.id === instance.id) {
				this.editedInstance = {}
			} else {
				this.editedInstance = Object.assign({}, instance)
			}
		},

		hideEditInstance(instance, $event) {
			/**
			 * If the click originates from the datepicker, we do nothing.
			 */
			 if ($event.target.closest('.mx-datepicker-main')
				|| $event.target.closest('.mx-table')
				|| $event.target.classList.contains('mx-btn')) {
				return
			}
			if (this.editedInstance.id === instance.id) {
				this.editedInstance = {}
			}
		},

		showInstanceInput() {
			this.addingInstance = true
		},

		hideInstanceInput(e) {
			this.addingInstance = false
		},

		showUuidInput(instance) {
			if (this.addUuidTo === instance.id) {
				this.addUuidTo = null
			} else {
				this.addUuidTo = instance.id
			}
		},

		hideUuidInput(instance) {
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

<style lang="scss" scoped>
.table {
	white-space: unset;
	width: 100%;

	.row {
		display: grid;
		min-height: 44px;
		border-bottom: 1px solid var(--color-border-dark);
	}

	.column {
		display: flex;
		align-items: center;
		overflow: hidden;
		padding: 0 10px;

		&--actions {
			padding: 0;
		}
	}

	&--instances {
		.row {
			grid-template-columns: 75px 75px 110px 140px 2fr 1fr 2fr 44px;

			&--column-2 {
				grid-template-columns: 100px 1fr;
			}

			&--add-uuid {
				grid-template-columns: 1fr 44px;
			}

			&--empty {
				grid-template-columns: 1fr;
			}

			.column {
				&--uuids,
				&--attachments {
					padding-right: 0;
				}

				&--center {
					display: flex;
					justify-content: center;
				}

				&--narrow-header {
					display: none;
				}

				&--uuids ul {
					width: 100%;

					> li {
						display: flex;
						align-items: center;

						.action-item {
							margin-left: auto;
						}
					}
				}

				&--add-uuid form {
					width: 100%;
				}

				input {
					width: 100%;
				}
			}
		}

		@media only screen and (max-width: 800px) {
			.row {
				&--wide-header {
					grid-template-columns: 1fr;

					.column {
						&--wide-header {
							display: none;
						}
					}
				}

				&--properties {
					grid-template-columns: 100px 1fr;
					grid-template-rows: repeat(8, 44px);

					.column {
						&--narrow-header {
							display: flex;
						}

						&--narrow-spacer {
							order: -2;
						}

						&--actions {
							order: -1;
						}

						&--input {
							padding-right: 0;
						}
					}
				}
			}
		}
	}
}
</style>
