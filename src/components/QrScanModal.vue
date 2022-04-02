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
	<Modal v-if="qrModalOpen"
		:out-transition="true"
		size="large"
		@close="closeModal">
		<QrcodeStream @decode="onDecode" @init="onInit" />
		<div v-if="statusString" class="status-bar">
			<span>{{ statusString }}</span>
		</div>
	</Modal>
</template>

<script>
import Modal from '@nextcloud/vue/dist/Components/Modal'

import { QrcodeStream } from 'vue-qrcode-reader'

export default {
	components: {
		Modal,
		QrcodeStream,
	},
	props: {
		qrModalOpen: {
			type: Boolean,
			default: false,
		},
		statusString: {
			type: String,
			default: '',
		},
	},
	data() {
		return {
			error: '',
		}
	},
	methods: {
		closeModal(event) {
			this.$emit('update:qrModalOpen', false)
		},
		onDecode(result) {
			this.$emit('recognized-qr-code', result)
		},

		async onInit(promise) {
			try {
				await promise
			} catch (error) {
				if (error.name === 'NotAllowedError') {
					this.error = 'ERROR: you need to grant camera access permisson'
				} else if (error.name === 'NotFoundError') {
					this.error = 'ERROR: no camera on this device'
				} else if (error.name === 'NotSupportedError') {
					this.error = 'ERROR: secure context required (HTTPS, localhost)'
				} else if (error.name === 'NotReadableError') {
					this.error = 'ERROR: is the camera already in use?'
				} else if (error.name === 'OverconstrainedError') {
					this.error = 'ERROR: installed cameras are not suitable'
				} else if (error.name === 'StreamApiNotSupportedError') {
					this.error = 'ERROR: Stream API is not supported in this browser'
				}
				console.debug(error)
			}
		},
	},
}
</script>

<style lang="scss" scoped>
:deep(.modal-container) {
	position: relative;
}

.status-bar {
	position: absolute;
	width: 100%;
	display: flex;
	justify-content: center;
	margin-top: -50px;

	span {
		border-radius: var(--border-radius-pill);
		text-align: center;
		padding: 10px;
		background-color: var(--color-main-background-translucent);
	}
}
</style>
