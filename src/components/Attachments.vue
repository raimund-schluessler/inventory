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
		</li>
		<li v-if="!attachments.length">
			{{ t('inventory', 'No files attached.') }}
		</li>
	</ul>
</template>

<script>

export default {
	filters: {
		bytes: function(bytes) {
			if (isNaN(parseFloat(bytes, 10)) || !isFinite(bytes)) {
				return '-'
			}
			const precision = 2
			var units = ['bytes', 'kB', 'MB', 'GB', 'TB', 'PB']
			var number = Math.floor(Math.log(bytes) / Math.log(1024))
			return (bytes / Math.pow(1024, Math.floor(number))).toFixed(precision) + ' ' + units[number]
		},
		relativeDateFilter: function(timestamp) {
			return OC.Util.relativeModifiedDate(timestamp * 1000)
		},
	},
	props: {
		attachments: {
			type: Array,
			default: () => [],
		},
	},
	methods: {

		attachmentMimetype(attachment) {
			const url = OC.MimeType.getIconUrl(attachment.extendedData.mimetype)
			return {
				'background-image': `url("${url}")`
			}
		},

		attachmentUrl(attachment) {
			if (attachment.instanceid) {
				return OC.generateUrl(`/apps/inventory/item/${attachment.itemid}/instance/${attachment.instanceid}/attachment/${attachment.id}`)
			} else {
				return OC.generateUrl(`/apps/inventory/item/${attachment.itemid}/attachment/${attachment.id}`)
			}
		},
	}
}
</script>
