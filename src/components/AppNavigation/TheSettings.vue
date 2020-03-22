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
	<div>
		<button @click="selectFolder">
			{{ t('inventory', 'Select attachment folder') }}
		</button>
	</div>
</template>

<script>
export default {
	methods: {
		async selectFolder() {
			const folder = await this.$store.dispatch('getAttachmentFolder')

			OC.dialogs.filepicker(
				t('inventory', 'Select attachment folder'),
				this.setFolder,
				false,
				'[\'httpd/unix-directory\']',
				true,
				undefined,
				folder,
				{ allowDirectoryChooser: true }
			)
		},

		setFolder(path) {
			this.$store.dispatch('setAttachmentFolder', { path })
		},
	},
}
</script>

<style scoped lang="scss">
	button {
		width: 100%;
	}
</style>
