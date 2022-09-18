/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 *
 * @copyright 2021 Raimund Schlüßler <raimund.schluessler@mailbox.org>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library. If not, see <http://www.gnu.org/licenses/>.
 *
 */
import NcModal from '@nextcloud/vue/dist/Components/NcModal.js'

import bwipjs from 'bwip-js'

export default {
	components: {
		NcModal,
	},
	data() {
		return {
			showBarcode: false,
		}
	},
	methods: {
		/**
		 * Generate a barcode for the given string
		 *
		 * @param {string} value The string to show as barcode
		 * @param {string} type The barcode type
		 * @param {string} options The barcode options
		 */
		 openBarcode(value, type = 'qrcode', options = '') {
			if (value.length > 0) {
				this.showBarcode = true
				// We have to wait for the modal to render before
				// drawing on the qr code canvas.
				this.$nextTick(() => {
					bwipjs.toCanvas(this.$refs.canvas, {
						bcid: type,
						scale: 6,
						text: value,
						height: 20,
						includetext: true,
					})
				})
			}
		},

		// reset the current qrcode
		closeBarcode() {
			this.showBarcode = false
		},
	},
}
