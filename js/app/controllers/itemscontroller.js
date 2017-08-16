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

angular.module('Inventory').controller('ItemsController', [
	'$scope', '$route', '$timeout', '$location', '$routeParams', 'Persistence', 'ItemsModel', 'SearchBusinessLayer', function($scope, $route, $timeout, $location, $routeParams, Persistence, ItemsModel, SearchBusinessLayer) {
		'use strict';
		var ItemsController = (function() {
			function ItemsController(_$scope, _$route, _$timeout, _$location, _$routeparams, _persistence, _$itemsmodel, _$searchbusinesslayer) {
				this._$scope = _$scope;
				this._$scope.name = 'items';

				this._$route = _$route;
				this._$timeout = _$timeout;
				this._$location = _$location;
				this._$routeparams = _$routeparams;
				this._persistence = _persistence;
				this._$itemsmodel = _$itemsmodel;
				this._$scope.route = this._$routeparams;
				this._persistence.getItems();
				this._$scope.items = this._$itemsmodel.getAll();
				this._$searchbusinesslayer = _$searchbusinesslayer;

				this._$scope.openDetails = function(id, $event) {
					var viewID = _$scope.route.viewID
					if ($($event.currentTarget).is($($event.target).closest('.handler'))) {
						$location.path('items/' + id);
					}
				};

				// this._$scope.filterItemsByString = function(item) {
				// 	return function(item) {
				// 		var searchstring = _searchbusinesslayer.getFilter();
				// 		return _$tasksmodel.filterTasksByString(task, filter);
				// 	};
				// };

				this._$scope.filteredItems = function() {
					var filter = _$searchbusinesslayer.getFilter();
					// var filter = 'as';
					return _$itemsmodel.filteredItems(filter);
				};
			}
			return ItemsController;
		})();
		return new ItemsController($scope, $route, $timeout, $location, $routeParams, Persistence, ItemsModel, SearchBusinessLayer);
	}
]);
