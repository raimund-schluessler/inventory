<?php
/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2019 Raimund Schlüßler <raimund.schluessler@mailbox.org>
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

class SettingsService {
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
	public function get():array {
		$settings = [
			'sortOrder' => (string)$this->settings->getUserValue($this->userId, $this->appName, 'sortOrder'),
			'sortDirection' => (bool)$this->settings->getUserValue($this->userId, $this->appName, 'sortDirection')
		];
		return $settings;
	}

	/**
	 * Set setting of type to new value
	 *
	 * @param $setting
	 * @param $value
	 * @return bool
	 */
	public function set($setting, $value):bool {
		$this->settings->setUserValue($this->userId, $this->appName, $setting, $value);
		return true;
	}
}
