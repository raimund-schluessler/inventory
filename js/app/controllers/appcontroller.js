/**
 * ownCloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2016 Raimund Schlüßler <raimund.schluessler@googlemail.com>
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

angular.module('Inventory').controller('AppController', [
	'$scope', '$route', '$timeout', '$location', '$routeParams', 'Persistence', function($scope, $route, $timeout, $location, $routeParams, Persistence) {
		'use strict';
		var AppController = (function() {
			function AppController(_$scope, _$route, _$timeout, _$location, _$routeparams, _persistence) {
				this._$scope = _$scope;
				this._$route = _$route;
				this._$timeout = _$timeout;
				this._$location = _$location;
				this._$routeparams = _$routeparams;
				this._persistence = _persistence;
				this._$scope.route = this._$routeparams;
				this._$scope.views = [
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
				];
			}
			return AppController;
		})();
		return new AppController($scope, $route, $timeout, $location, $routeParams, Persistence);
	}
]);
