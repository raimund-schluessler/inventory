<!--
Nextcloud - Inventory

@author Raimund Schlüßler
@copyright 2020 Raimund Schlüßler <raimund.schluessler@mailbox.org>

@author Julius Härtl
@copyright 2020 Julius Härtl <jus@bitgrid.net>

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
	<div class="attachments"
		@dragover.prevent="!isDraggingOver && (isDraggingOver = true)"
		@dragleave.prevent="isDraggingOver && (isDraggingOver = false)"
		@drop.prevent="handleDropFiles">
		<div class="attachments__wrapper">
			<ul>
				<li v-for="attachment in attachments" :key="attachment.id" class="attachment">
					<a class="fileicon" :style="attachmentMimetype(attachment)" :href="attachment.url" />
					<div class="details">
						<a :href="attachmentUrl(attachment)">
							<div class="filename">
								<span class="basename">{{ attachment.extendedData.info.filename }}</span>
								<span class="extension">{{ '.' + attachment.extendedData.info.extension }}</span>
							</div>
							<span class="filesize">{{ attachment.extendedData.filesize | bytes }}</span>
							<span class="filedate">{{ attachment.lastModified | relativeDateFilter }}</span>
							<span class="filedate">{{ t('inventory', 'by') + ' ' + attachment.createdBy }}</span>
						</a>
					</div>
					<Actions>
						<ActionButton
							icon="icon-delete"
							:close-after-click="true"
							@click="deleteAttachment(attachment)">
							{{ t('inventory', 'Delete attachment') }}
						</ActionButton>
						<ActionButton v-if="canUnlink(attachment)"
							icon="icon-close"
							:close-after-click="true"
							@click="unlinkAttachment(attachment)">
							{{ t('inventory', 'Unlink attachment') }}
						</ActionButton>
					</Actions>
				</li>
				<li v-if="!attachments.length">
					{{ t('inventory', 'No files attached.') }}
				</li>
			</ul>
		</div>
		<Actions>
			<ActionButton
				icon="icon-upload"
				:close-after-click="true"
				@click="upload">
				{{ t('inventory', 'Upload attachment') }}
			</ActionButton>
			<ActionButton
				icon="icon-folder"
				:close-after-click="true"
				@click="select">
				{{ t('inventory', 'Select attachment') }}
			</ActionButton>
		</Actions>
		<input ref="localAttachments"
			type="file"
			style="display: none;"
			@change="handleUploadFile">

		<transition name="fade" mode="out-in">
			<div
				v-show="isDraggingOver"
				class="dragover">
				<div class="drop-hint">
					<div class="drop-hint__icon icon-upload" />
					<h2
						class="drop-hint__text">
						{{ t('inventory', 'Drop your files to upload') }}
					</h2>
				</div>
			</div>
		</transition>

		<Modal v-if="modalShow" :title="t('inventory', 'File already exists')" @close="modalShow=false">
			<div class="modal__content">
				<h2>{{ t('inventory', 'File already exists') }}</h2>
				<p>
					{{ t('inventory', 'A file with the name {filename} already exists.', {filename: file.name}) }}
				</p>
				<p>
					{{ t('inventory', 'Do you want to overwrite it?') }}
				</p>
				<button class="primary" @click="overrideAttachment">
					{{ t('inventory', 'Overwrite file') }}
				</button>
				<button @click="modalShow=false">
					{{ t('inventory', 'Keep existing file') }}
				</button>
			</div>
		</Modal>
	</div>
</template>

<script>
import { showError, getFilePickerBuilder } from '@nextcloud/dialogs'
import { formatFileSize } from '@nextcloud/files'
import { generateUrl } from '@nextcloud/router'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import Modal from '@nextcloud/vue/dist/Components/Modal'

export default {
	components: {
		Actions,
		ActionButton,
		Modal,
	},
	filters: {
		bytes(bytes) {
			if (isNaN(parseFloat(bytes, 10)) || !isFinite(bytes)) {
				return '-'
			}
			const precision = 2
			const units = ['bytes', 'kB', 'MB', 'GB', 'TB', 'PB']
			const number = Math.floor(Math.log(bytes) / Math.log(1024))
			return (bytes / Math.pow(1024, Math.floor(number))).toFixed(precision) + ' ' + units[number]
		},
		relativeDateFilter(timestamp) {
			return OC.Util.relativeModifiedDate(timestamp * 1000)
		},
	},
	props: {
		attachments: {
			type: Array,
			default: () => [],
		},
		itemId: {
			type: String,
			required: true,
		},
		instanceId: {
			type: String,
			default: null,
		},
	},
	data() {
		return {
			modalShow: false,
			file: '',
			overwriteAttachment: null,
			isDraggingOver: false,
			maxUploadSize: 16e7,
		}
	},
	methods: {
		/**
		 * Whether an attachment can be unlinked or only deleted.
		 * Files that have been linked start with a '/' which indicates the path is absolute
		 *
		 * @param {Attachment} attachment The attachment to check
		 * @returns {Boolean}
		 */
		canUnlink(attachment) {
			return attachment.basename.startsWith('/')
		},

		async unlinkAttachment(attachment) {
			try {
				await this.$store.dispatch('unlinkAttachment', {
					itemId: this.itemId,
					attachmentId: attachment.id,
					instanceId: this.instanceId,
				})
			} catch (err) {
				showError(err.response.data.message)
			}
		},

		async deleteAttachment(attachment) {
			try {
				await this.$store.dispatch('deleteAttachment', {
					itemId: this.itemId,
					attachmentId: attachment.id,
					instanceId: this.instanceId,
				})
			} catch (err) {
				showError(err.response.data.message)
			}
		},

		upload() {
			this.$refs.localAttachments.click()
		},

		async select() {
			const attachment = await getFilePickerBuilder(t('inventory', 'Select a file to link as attachment')).build().pick()
			try {
				await this.$store.dispatch('linkAttachment', {
					itemId: this.itemId,
					attachment,
					instanceId: this.instanceId,
				})
			} catch (err) {
				showError(err.response.data.message)
			}
		},

		handleDropFiles(event) {
			this.isDraggingOver = false
			this.onLocalAttachmentSelected(event.dataTransfer.files[0])
			event.dataTransfer.value = ''
		},

		handleUploadFile(event) {
			this.onLocalAttachmentSelected(event.target.files[0])
			event.target.value = ''
		},

		async onLocalAttachmentSelected(file) {
			if (this.maxUploadSize > 0 && file.size > this.maxUploadSize) {
				showError(
					t('inventory', 'Failed to upload {name}', { name: file.name }) + ' - '
						+ t('inventory', 'Maximum file size of {size} exceeded', { size: formatFileSize(this.maxUploadSize) })
				)
				event.target.value = ''
				return
			}

			const bodyFormData = new FormData()
			bodyFormData.append('itemId', this.itemId)
			if (this.instanceId) {
				bodyFormData.append('instanceId', this.instanceId)
			}
			bodyFormData.append('file', file)
			try {
				await this.$store.dispatch('createAttachment', {
					itemId: this.itemId,
					formData: bodyFormData,
					instanceId: this.instanceId,
				})
			} catch (err) {
				if (err.response.data.status === 409) {
					this.file = file
					this.overwriteAttachment = err.response.data.data
					this.modalShow = true
				} else {
					showError(err.response.data.message)
				}
			}
		},

		overrideAttachment() {
			const bodyFormData = new FormData()
			bodyFormData.append('itemId', this.itemId)
			bodyFormData.append('file', this.file)
			this.$store.dispatch('updateAttachment', {
				itemId: this.itemId,
				attachmentId: this.overwriteAttachment.id,
				formData: bodyFormData,
				instanceId: this.instanceId,
			})

			this.modalShow = false
		},

		attachmentMimetype(attachment) {
			const url = OC.MimeType.getIconUrl(attachment.extendedData.mimetype)
			return {
				'background-image': `url("${url}")`,
			}
		},

		attachmentUrl(attachment) {
			if (attachment.instanceid) {
				return generateUrl(`/apps/inventory/item/${attachment.itemid}/instance/${attachment.instanceid}/attachment/${attachment.id}/display`)
			} else {
				return generateUrl(`/apps/inventory/item/${attachment.itemid}/attachment/${attachment.id}/display`)
			}
		},
	},
}
</script>

<style scoped lang="scss">

	.modal__content {
		width: 25vw;
		min-width: 250px;
		height: 120px;
		text-align: center;
		margin: 20px 20px 60px 20px;

		button {
			float: right;
			margin: 40px 3px 3px 0;
		}
	}

	.drop-hint__text {
		text-align: center;
	}
</style>
