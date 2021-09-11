<!--
Nextcloud - Inventory

@author Raimund Schlüßler
@copyright 2020 Raimund Schlüßler <raimund.schluessler@mailbox.org>

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
	<AppNavigationSettings>
		<div>
			<button @click="downloadUuidPdf">
				{{ t('inventory', 'Download UUID sticker sheet') }}
			</button>
			<button @click="selectFolder">
				{{ t('inventory', 'Select attachment folder') }}
			</button>
		</div>
	</AppNavigationSettings>
</template>

<script>
import { getFilePickerBuilder } from '@nextcloud/dialogs'
import { translate as t } from '@nextcloud/l10n'
import AppNavigationSettings from '@nextcloud/vue/dist/Components/AppNavigationSettings'

import { v4 as uuidv4 } from 'uuid'
import bwipjs from 'bwip-js'
import { jsPDF } from 'jspdf'

export default {
	components: {
		AppNavigationSettings,
	},
	methods: {
		t,

		async selectFolder() {
			const folder = await this.$store.dispatch('getAttachmentFolder')

			const path = await getFilePickerBuilder(t('inventory', 'Select attachment folder'))
				.addMimeTypeFilter('httpd/unix-directory')
				.startAt(folder)
				.allowDirectories(true)
				.build()
				.pick()

			this.$store.dispatch('setAttachmentFolder', { path })
		},
		/**
		 * Creates a PDF file with multiple randomly generated UUID qrcodes.
		 */
		async downloadUuidPdf() {
			// eslint-disable-next-line new-cap
			const doc = new jsPDF()

			const printerOffsetLeft = 1
			const printerOffsetTop = -1
			const offsetLeft = 12 - printerOffsetLeft
			const offsetTop = 15 - printerOffsetTop
			const padding = 2
			const spacing = 27
			const columnCount = 7
			const rowCount = 10
			const size = 20
			// Create canvas to write on
			const canvas = document.createElement('canvas')
			for (let column = 0; column < columnCount; column++) {
				for (let row = 0; row < rowCount; row++) {
					// Generate UUID qrcode on canvas
					bwipjs.toCanvas(canvas, {
						bcid: 'qrcode',
						scale: 1,
						text: uuidv4(),
						height: 20,
						includetext: true,
					})
					// Add the qrcode to the PDF
					doc.addImage(
						canvas,
						'PNG',
						spacing * column + offsetLeft + padding,
						spacing * row + offsetTop + padding,
						size,
						size
					)
				}
			}
			doc.text(t('inventory', 'UUID sticker sheet'), 85, 10)
			// Save the PDF for download
			await doc.save('UUIDs.pdf')
		},
	},
}
</script>

<style scoped lang="scss">
	button {
		width: 100%;
	}
</style>
