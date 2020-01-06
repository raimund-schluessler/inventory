<?php
/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2020 Raimund Schlüßler <raimund.schluessler@mailbox.org>
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

class FoldersService {

	private $userId;
	private $settings;
	private $AppName;

	/**
	 * @param string $userId
	 * @param IConfig $settings
	 * @param string $AppName
	 */
	public function __construct(string $userId, IConfig $settings, string $AppName) {
		$this->userId = $userId;
		$this->appName = $AppName;
		$this->settings = $settings;
	}

	/**
	 * Get the current settings
	 *
	 * @return array
	 */
	public function getByPath($path):array {
		return array(
			'1' => array(
				'type' => 'folder',
				'id' => 2,
				'name' => 'Bohrer',
				'path' => '/Werkzeug/Bohrer'
			),
			'2' => array(
				'type' => 'folder',
				'id' => 3,
				'name' => 'Sägen',
				'path' => '/Werkzeug/Sägen'
			),
		);
	}
}
