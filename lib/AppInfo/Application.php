<?php
/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2019 Raimund Schlüßler raimund.schluessler@mailbox.org
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

namespace OCA\Inventory\AppInfo;

use \OCP\AppFramework\App;
use OCA\Inventory\Middleware\ExceptionMiddleware;

class Application extends App {


	public function __construct (array $urlParams=array()) {
		parent::__construct('inventory', $urlParams);

		$container = $this->getContainer();

		/**
		 * Middleware
		 */

		$container->registerService('ExceptionMiddleware', function($c){
			return new ExceptionMiddleware();
		});

		$container->registerMiddleware('ExceptionMiddleware');

		/**
		 * Add worker-src blob to content security policy, so that
		 * https://github.com/gruhn/vue-qrcode-reader works correctly.
		 */
		if(class_exists('\\OCP\\AppFramework\\Http\\EmptyContentSecurityPolicy')) {
			$manager = \OC::$server->getContentSecurityPolicyManager();

			$policy = new \OCP\AppFramework\Http\EmptyContentSecurityPolicy();
			$policy->addAllowedWorkerSrcDomain('blob:');
			// This line is for Safari on iOS and Mac, which doesn't know 'worker-src' yet.
			$policy->addAllowedChildSrcDomain('blob:');

			$manager->addDefaultPolicy($policy);
		}
	}
}
