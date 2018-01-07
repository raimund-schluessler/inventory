<?php
/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2017 Raimund Schlüßler raimund.schluessler@mailbox.org
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
use \OCP\AppFramework\IAppContainer;
use \OCA\Inventory\Service\IteminstanceService;
use \OCA\Inventory\Service\ItemsService;
use \OCA\Inventory\Db\IteminstanceMapper;
use \OCA\Inventory\Db\PlaceMapper;
use \OCA\Inventory\Db\ItemMapper;
use \OCA\Inventory\Db\CategoryMapper;
use \OCA\Inventory\Db\ItemcategoriesMapper;
use \OCA\Inventory\Db\ItemparentMapper;
use \OCA\Inventory\Db\ItemrelationMapper;

class Application extends App {


	public function __construct (array $urlParams=array()) {
		parent::__construct('inventory', $urlParams);

		$container = $this->getContainer();

		/**
		 * Services
		 */

		$container->registerService('IteminstanceService', function(IAppContainer $c) {
			return new IteminstanceService(
				$c->query('UserId'),
				$c->query('AppName'),
				$c->query('IteminstanceMapper'),
				$c->query('PlaceMapper')
			);
		});

		$container->registerService('ItemsService', function(IAppContainer $c) {
			return new ItemsService(
				$c->query('UserId'),
				$c->query('AppName'),
				$c->query('ItemMapper'),
				$c->query('IteminstanceService'),
				$c->query('CategoryMapper'),
				$c->query('ItemcategoriesMapper'),
				$c->query('ItemparentMapper'),
				$c->query('ItemrelationMapper')
			);
		});

		/**
		 * Mappers
		 */

		$container->registerService('IteminstanceMapper', function($c){
			return new IteminstanceMapper(
				$c->query('ServerContainer')->getDatabaseConnection()
			);
		});

		$container->registerService('PlaceMapper', function($c){
			return new PlaceMapper(
				$c->query('ServerContainer')->getDatabaseConnection()
			);
		});

		$container->registerService('ItemMapper', function($c){
			return new ItemMapper(
				$c->query('ServerContainer')->getDatabaseConnection()
			);
		});

		$container->registerService('CategoryMapper', function($c){
			return new CategoryMapper(
				$c->query('ServerContainer')->getDatabaseConnection()
			);
		});

		$container->registerService('ItemcategoriesMapper', function($c){
			return new ItemcategoriesMapper(
				$c->query('ServerContainer')->getDatabaseConnection()
			);
		});

		$container->registerService('ItemparentMapper', function($c){
			return new ItemparentMapper(
				$c->query('ServerContainer')->getDatabaseConnection()
			);
		});

		$container->registerService('ItemrelationMapper', function($c){
			return new ItemrelationMapper(
				$c->query('ServerContainer')->getDatabaseConnection()
			);
		});
	}
}
