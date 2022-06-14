<!--
Nextcloud - Inventory

@author Raimund Schlüßler
@copyright 2022 Raimund Schlüßler <raimund.schluessler@mailbox.org>

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
	<!-- eslint-disable-next-line vue/no-v-html -->
	<div v-html="svg" />
</template>

<script>
import DOMPurify from 'dompurify'

export default {
	name: 'SVGViewer',
	props: {
		svgBase64: {
			type: String,
			default: '',
		},
	},
	computed: {
		svg() {
			try {
				return DOMPurify.sanitize(atob(this.svgBase64.split(',')[1]))
			} catch (e) {
				return ''
			}
		},
	},
}
</script>
