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
 
(function() {
	var __bind = function(fn, me){
		return function(){
			return fn.apply(me, arguments);
		};
	};

	angular.module('Inventory').factory('SearchBusinessLayer', [
		'$rootScope', '$routeParams', '$location', function($rootScope, $routeParams, $location) {
			var SearchBusinessLayer;
			SearchBusinessLayer = (function() {
				function SearchBusinessLayer(_$rootScope, _$routeparams, _$location) {
					this._$rootScope = _$rootScope;
					this._$routeparams = _$routeparams;
					this._$location = _$location;
					this.getFilter = __bind(this.getFilter, this);
					this.setFilter = __bind(this.setFilter, this);
					this.attach = __bind(this.attach, this);
					this.initialize();
					this._$searchString = '';
				}

				SearchBusinessLayer.prototype.attach = function(search) {
					var _this = this;
					search.setFilter('inventory', function(query) {
						return _this._$rootScope.$apply(function() {
							return _this.setFilter(query);
						});
					});
					search.setRenderer('item', this.renderItemResult.bind(this));
					return search.setHandler('item', this.handleItemClick.bind(this));
				};

				SearchBusinessLayer.prototype.setFilter = function(query) {
					return this._$searchString = query;
				};

				SearchBusinessLayer.prototype.getFilter = function() {
					return this._$searchString;
				};

				SearchBusinessLayer.prototype.initialize = function() {
					var _this = this;
					this.handleItemClick = function($row, result, event) {
					// return _this._$location.path('/lists/' + result.calendarid + '/tasks/' + result.id);
					};
					this.renderItemResult = function($row, result) {
					//   var $template;
					//   if (!_this._$tasksmodel.filterTasks(result, _this._$routeparams.listID) || !_this._$tasksmodel.isLoaded(result)) {
					//     $template = $('div.task-item.template');
					//     $template = $template.clone();
					//     $row = $('<tr class="result"></tr>').append($template.removeClass('template'));
					//     $row.data('result', result);
					//     $row.find('span.title').text(result.name);
					//     if (result.starred) {
					//       $row.find('span.task-star').addClass('task-starred');
					//     }
					//     if (result.completed) {
					//       $row.find('div.task-item').addClass('done');
					//       $row.find('span.task-checkbox').addClass('task-checked');
					//     }
					//     if (result.complete) {
					//       $row.find('div.percentdone').css({
					//         'width': result.complete + '%',
					//         'background-color': '' + _this._$listsmodel.getColor(result.calendarid)
					//       });
					//     }
					//     if (result.note) {
					//       $row.find('div.title-wrapper').addClass('attachment');
					//     }
					//     return $row;
					//   } else {
					//     return null;
					//   }
					};
					return OC.Plugins.register('OCA.Search', this);
				};

				return SearchBusinessLayer;

			})();
			return new SearchBusinessLayer($rootScope, $routeParams, $location);
		}
	]);
}).call(this);
