/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2017 Raimund Schlüßler <raimund.schluessler@mailbox.org>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
'use strict';

import {App} from "./app";

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
	OCA.Inventory.App = new App();
	OCA.Inventory.App.start();

	var version = OC.config.version.split('.');

	if (version[0] >= 14) {
		OC.Search = new OCA.Search(OCA.Inventory.App.Vue.filter, OCA.Inventory.App.Vue.cleanSearch);
	} else {
		OCA.Inventory.App.Search = {
			attach: function (search) {
				search.setFilter('inventory', OCA.Inventory.App.Vue.filter);
			}
		};

		OC.Plugins.register('OCA.Search', OCA.Inventory.App.Search);
	}
});
