<?php
/**
 * ownCloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2016 Raimund Schlüßler raimund.schluessler@googlemail.com
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

namespace OCA\Inventory\Service;

use OCP\IConfig;

class ItemsService {

	private $userId;
	private $appName;

	public function __construct($userId, $appName) {
		$this->userId = $userId;
		$this->appName = $appName;
	}

	/**
	 * get items
	 *
	 * @return array
	 */
	public function get() {
		$items = array(
			array(
				'id' => 0,
				'description' => 'Festool Oberfräse OF1400',
				'place' => 'Schrank Flur',
				'categories' => 'Elektro Werkzeug, Holzbearbeitung, Fräsen, Festool'
				),
			array(
				'id' => 1,
				'description' => 'Festool Fräser 18/40',
				'place' => 'Schrank Flur',
				'categories' => 'Elektro Werkzeug, Holzbearbeitung, Fräsen, Festool'
				),
		);
		return $items;
	}
}
