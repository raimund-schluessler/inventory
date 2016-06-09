/*

ownCloud - Inventory

@author Raimund Schlüßler
@copyright 2016

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
License as published by the Free Software Foundation; either
version 3 of the License, or any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU AFFERO GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU Affero General Public
License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*/


(function() {
	angular.module('Inventory', ['ngRoute']).config([
		'$provide', '$routeProvider', '$interpolateProvider', '$httpProvider', function($provide, $routeProvider, $interpolateProvider, $httpProvider) {
			var config;
			$provide.value('Config', config = {
				markReadTimeout: 500,
				taskUpdateInterval: 1000 * 600
			});
			$httpProvider.defaults.headers.common['requesttoken'] = oc_requesttoken;
			$routeProvider
			.when('/items/', {
				templateUrl: OC.linkTo('inventory', 'templates/part.items.html'),
				controller: 'ItemsController',
				name: 'items'
			})
			.when('/items/:itemID', {
				templateUrl: OC.linkTo('inventory', 'templates/part.itemdetails.html'),
				name: 'item'
			})
			.when('/places/', {
				templateUrl: OC.linkTo('inventory', 'templates/part.places.html'),
				controller: 'PlacesController',
				name: 'places'
			})
			.when('/categories/', {
				templateUrl: OC.linkTo('inventory', 'templates/part.categories.html'),
				controller: 'CategoriesController',
				name: 'categories'
			})
			.otherwise({
				redirectTo: '/items/'
			});
		}
	]);

	angular.module('Inventory').run([
		'$document', '$rootScope', 'Config', '$timeout', function($document, $rootScope, Config, $timeout) {
			$('link[rel="shortcut icon"]').attr('href', OC.filePath('inventory', 'img', 'favicon.png'));
			return $document.click(function(event) {
				$rootScope.$broadcast('documentClicked', event);
			});
		}
	]);

}).call(this);
