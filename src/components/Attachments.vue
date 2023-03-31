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
		:class="{ 'attachments--empty': !attachments.length }"
		@dragover.prevent="!isDraggingOver && (isDraggingOver = true)"
		@dragleave.prevent="isDraggingOver && (isDraggingOver = false)"
		@drop.prevent="handleDropFiles">
		<div class="attachments__wrapper">
			<ul>
				<li class="attachments__header">
					<div class="label">
						{{ t('inventory', 'Attachments') }}
					</div>
					<NcActions :boundaries-element="boundaries">
						<NcActionButton :close-after-click="true"
							@click="upload">
							<template #icon>
								<Upload :size="20" />
							</template>
							{{ t('inventory', 'Upload attachment') }}
						</NcActionButton>
						<NcActionButton :close-after-click="true"
							@click="select">
							<template #icon>
								<Folder :size="20" />
							</template>
							{{ t('inventory', 'Select attachment') }}
						</NcActionButton>
					</NcActions>
				</li>
				<li v-for="attachment in attachments" :key="attachment.id" class="attachment">
					<a class="fileicon" :style="attachmentMimetype(attachment)" :href="attachment.url" />
					<div class="details">
						<a :href="attachmentUrl(attachment)">
							<div class="filename">
								<span class="basename">{{ attachment.extendedData.info.filename }}</span>
								<span class="extension">{{ '.' + attachment.extendedData.info.extension }}</span>
							</div>
							<span class="filesize">{{ bytes(attachment.extendedData.filesize) }}</span>
							<span class="filedate">{{ relativeDate(attachment.lastModified) }}</span>
							<span class="filedate">{{ t('inventory', 'by {username}', { username: attachment.createdBy }) }}</span>
						</a>
					</div>
					<NcActions :boundaries-element="boundaries">
						<NcActionLink :href="fileLink(attachment)"
							target="_blank"
							:close-after-click="true">
							<template #icon>
								<OpenInNew :size="20" />
							</template>
							{{ t('inventory', 'Show in files') }}
						</NcActionLink>
						<NcActionButton v-if="canUnlink(attachment)"
							:close-after-click="true"
							@click="unlinkAttachment(attachment)">
							<template #icon>
								<Close :size="20" />
							</template>
							{{ t('inventory', 'Unlink attachment') }}
						</NcActionButton>
						<NcActionButton :close-after-click="true"
							@click="deleteAttachment(attachment)">
							<template #icon>
								<Delete :size="20" />
							</template>
							{{ t('inventory', 'Delete attachment') }}
						</NcActionButton>
					</NcActions>
				</li>
				<li v-if="loadingAttachments" class="attachment attachment--placeholder">
					<NcLoadingIcon />
					<span class="message">{{ t('inventory', 'Load attachments from server.') }}</span>
				</li>
				<li v-else-if="!attachments.length" class="attachment attachment--placeholder">
					<span>{{ t('inventory', 'No files attached.') }}</span>
				</li>
			</ul>
		</div>
		<input ref="localAttachments"
			type="file"
			style="display: none;"
			@change="handleUploadFile">

		<transition name="fade" mode="out-in">
			<div v-show="isDraggingOver"
				class="dragover">
				<div class="drop-hint">
					<div class="drop-hint__icon icon-upload" />
					<h2 class="drop-hint__text">
						{{ t('inventory', 'Drop your files to upload') }}
					</h2>
				</div>
			</div>
		</transition>

		<NcModal v-if="modalShow"
			class="modal-attachments"
			:title="t('inventory', 'File already exists')"
			@close="modalShow=false">
			<div class="modal__content">
				<h2>{{ t('inventory', 'File already exists') }}</h2>
				<p>
					{{ t('inventory', 'A file with the name {filename} already exists.', {filename: file.name}) }}
				</p>
				<p>
					{{ t('inventory', 'Do you want to overwrite it?') }}
				</p>
				<div class="button-wrapper">
					<NcButton @click="overrideAttachment">
						{{ t('inventory', 'Overwrite file') }}
					</NcButton>
					<NcButton type="primary" @click="modalShow=false">
						{{ t('inventory', 'Keep existing file') }}
					</NcButton>
				</div>
			</div>
		</NcModal>
	</div>
</template>

<script>
import { showError, getFilePickerBuilder } from '@nextcloud/dialogs'
import { formatFileSize } from '@nextcloud/files'
import { translate as t } from '@nextcloud/l10n'
import moment from '@nextcloud/moment'
import { generateUrl } from '@nextcloud/router'
import {
	NcActions,
	NcActionButton,
	NcActionLink,
	NcButton,
	NcModal,
	NcLoadingIcon,
} from '@nextcloud/vue'

import Close from 'vue-material-design-icons/Close.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Folder from 'vue-material-design-icons/Folder.vue'
import OpenInNew from 'vue-material-design-icons/OpenInNew.vue'
import Upload from 'vue-material-design-icons/Upload.vue'

export default {
	components: {
		NcActions,
		NcActionButton,
		NcActionLink,
		NcButton,
		NcModal,
		NcLoadingIcon,
		Close,
		Delete,
		Folder,
		OpenInNew,
		Upload,
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
		loadingAttachments: {
			type: Boolean,
			default: false,
		},
	},
	data() {
		return {
			modalShow: false,
			file: '',
			overwriteAttachment: null,
			isDraggingOver: false,
			maxUploadSize: 16e7,
			// Hack to fix https://github.com/nextcloud/nextcloud-vue/issues/1384
			boundaries: document.querySelector('#content-vue'),
		}
	},
	methods: {
		t,

		/**
		 * Whether an attachment can be unlinked or only deleted.
		 * Files that have been linked start with a '/' which indicates the path is absolute
		 *
		 * @param {object} attachment The attachment to check
		 * @return {boolean}
		 */
		canUnlink(attachment) {
			return attachment.basename.startsWith('/')
		},

		bytes(bytes) {
			if (isNaN(parseFloat(bytes, 10)) || !isFinite(bytes)) {
				return '-'
			}
			const precision = 2
			const units = ['bytes', 'kB', 'MB', 'GB', 'TB', 'PB']
			const number = Math.floor(Math.log(bytes) / Math.log(1024))
			return (bytes / Math.pow(1024, Math.floor(number))).toFixed(precision) + ' ' + units[number]
		},
		relativeDate(timestamp) {
			return moment.unix(timestamp).fromNow()
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

		fileLink(attachment) {
			return generateUrl(attachment.link)
		},
	},
}
</script>

<style lang="scss" scoped>
.attachments {
	display: flex;
	align-items: center;
	flex-wrap: wrap;

	&--empty {
		flex-wrap: nowrap;
	}

	&__wrapper {
		display: inline-block;
		flex: 1 1 auto;

		>ul {
			display: grid;
			grid-gap: 5px;
			grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
			grid-auto-flow: dense;

			.attachments__header {
				display: flex;

				.label {
					line-height: 44px;
				}
				.action-item {
					margin-left: auto;
				}
			}

			li.attachment {
				display: flex;
				padding: 3px 0;
				align-items: center;

				&--placeholder {
					.icon {
						height: 38px;
						width: 38px;
					}
					.message {
						padding-left: 10px;
					}
				}

				&.deleted {
					opacity: .5;
				}

				.fileicon {
					display: inline-block;
					min-width: 32px;
					width: 32px;
					height: 32px;
					background-size: contain;
					margin-bottom: auto;
				}
				.details {
					flex-grow: 1;
					flex-shrink: 1;
					min-width: 0;
					flex-basis: 50%;
					line-height: 110%;
					padding: 2px;
				}
				.filename {
					width: 70%;
					display: flex;
					white-space: nowrap;
					.basename {
						white-space: nowrap;
						overflow: hidden;
						text-overflow: ellipsis;
						padding-bottom: 2px;
					}
					.extension {
						opacity: .7;
					}
				}
				.filesize,
				.filedate {
					font-size: 90%;
					color: var(--color-text-lighter);
					padding-right: 2px;
				}
			}
		}
	}
	.dragover {
		width: 100%;

		.drop-hint__text {
			text-align: center;
		}
	}
}

.modal-attachments {
	.modal__content {
		min-width: 250px;
		text-align: center;
		margin: 20px;

		.button-wrapper {
			display: flex;
			justify-content: center;
			padding-top: 40px;

			.button-vue {
				margin: 0 20px;
			}
		}
	}
}
</style>
