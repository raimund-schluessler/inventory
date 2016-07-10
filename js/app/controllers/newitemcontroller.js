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

angular.module('Inventory').controller('NewItemController', [
	'$scope', '$route', '$timeout', '$location', '$routeParams', 'Persistence', 'ItemsModel', function($scope, $route, $timeout, $location, $routeParams, Persistence, ItemsModel) {
		'use strict';
		var NewItemController = (function() {
			function NewItemController(_$scope, _$route, _$timeout, _$location, _$routeparams, _persistence, _$itemsmodel) {
				this._$scope = _$scope;
				this._$scope.name = 'newItem';

				this._$route = _$route;
				this._$timeout = _$timeout;
				this._$location = _$location;
				this._$routeparams = _$routeparams;
				this._persistence = _persistence;
				this._$itemsmodel = _$itemsmodel;
				this._$scope.route = this._$routeparams;
				this._$scope.rawInput = "";
				this._$scope.csvConfig = {
					delimiter: ";",
					newline: "\n",
				};

				this._$scope.items = [];

				this._$scope.parseInput = function () {
					var results = Papa.parse(_$scope.rawInput, _$scope.csvConfig);
					_$scope.items = [];
					for (i = 0; i < results.data.length; i++) {
						var it = results.data[i];
						var item = {
							'id':			'',
							'maker':		it[0],
							'name':			it[1],
							'description':	it[2],
							'place':		{
								'name': it[3]
							},
							'categories':	it[4].split(',').map(function(s) {
								return {'name':	String.prototype.trim.apply(s)};
							}),
							'price':		it[5],
							'link':			it[6],
							'count':		it[7]
						}
						_$scope.items.push(item);
					}
				};

				this._$scope.enlist = function () {
					for (i=0; i<_$scope.items.length; i++) {
						_persistence.enlist(_$scope.items[i]);
					}
					_$scope.items = [];
					_$scope.rawInput = "";
				}

				console.log('NewItemController loaded.')
			}
			return NewItemController;
		})();
		return new NewItemController($scope, $route, $timeout, $location, $routeParams, Persistence, ItemsModel);
	}
]);
