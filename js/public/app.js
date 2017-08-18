/**
 * Nextcloud Inventory - v0.0.1
 *
 * Copyright (c) 2017 - Raimund Schlüßler <raimund.schluessler@mailbox.org>
 *
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING file
 *
 */


if (!OCA.Inventory) {
	/**
	 * @namespace OCA.Inventory
	 */
	OCA.Inventory = {};
}
/**
* @namespace
*/

$(document).ready(function () {
	OCA.Inventory.App = new Vue({
		el: '#app',
		data: {
			views: [
				{
					name: t('inventory', 'Items'),
					id: "items"
				},
				{
					name: t('inventory', 'Places'),
					id: "places"
				},
				{
					name: t('inventory', 'Categories'),
					id: "categories"
				}
			]
		}
	})
});
