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

namespace OCA\Inventory\Controller;

use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\TemplateResponse;

/**
 * Controller class for main page.
 */
class PageController extends Controller {

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		if (defined('DEBUG') && DEBUG) {
			\OCP\Util::addScript('inventory', 'vendor/angularjs/angular');
			\OCP\Util::addScript('inventory', 'vendor/angularjs/angular-route');
			\OCP\Util::addScript('inventory', 'vendor/angularjs/angular-animate');
			\OCP\Util::addScript('inventory', 'vendor/angularjs/angular-sanitize');
		} else {
			\OCP\Util::addScript('inventory', 'vendor/angularjs/angular.min');
			\OCP\Util::addScript('inventory', 'vendor/angularjs/angular-route.min');
			\OCP\Util::addScript('inventory', 'vendor/angularjs/angular-animate.min');
			\OCP\Util::addScript('inventory', 'vendor/angularjs/angular-sanitize.min');
		}
		\OCP\Util::addScript('inventory', 'public/app');
		// \OCP\Util::addStyle('inventory', 'vendor/angularui/ui-select/select2');

		$response = new TemplateResponse('inventory', 'main');
		return $response;
	}
}
