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

namespace OCA\Inventory;

use \OCA\Inventory\AppInfo\Application;

$application = new Application();

$application->registerRoutes($this, array('routes' => array(
	// page
	array('name' => 'page#index', 'url' => '/', 'verb' => 'GET'),
	// templates
	array('name' => 'page#templates', 'url' => '/templates/{template}', 'verb' => 'GET'),

	// items
	array('name' => 'items#get',	'url' => '/items', 'verb' => 'GET'),

)));
