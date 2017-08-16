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
	'use strict';
	var __hasProp = {}.hasOwnProperty,
		__extends = function(child, parent) {
			for (var key in parent) {
				if (__hasProp.call(parent, key)) child[key] = parent[key];
			}
			function ctor() {
				this.constructor = child;
			}
			ctor.prototype = parent.prototype;
			child.prototype = new ctor();
			child.__super__ = parent.prototype;
			return child;
		},
		__indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

	angular.module('Inventory').factory('ItemsModel', [
		'_Model', function(_Model) {
			var ItemsModel = (function(_super) {
				__extends(ItemsModel, _super);

				function ItemsModel() {
					this._nameCache = {};
					ItemsModel.__super__.constructor.call(this);
				}

				ItemsModel.prototype.filteredItems = function(needle) {
					var ancestors, parentID, ret, item, items, _i, _len;
					ret = [];
					items = this.getAll();
					if (!needle) {
						ret = items;
					} else {
						for (_i = 0, _len = items.length; _i < _len; _i++) {
							item = items[_i];
							if (this.test(item, needle)) {
								ret.push(item);
							}
						}
					}
					return ret;
				};

				ItemsModel.prototype.test = function (item, filter) {
					var needles = filter.split(' ');
					for (var needle of needles) {
						if (!this.filterItemsByString(item, needle)) {
							return false;
						}
					}
					return true;
				}

				ItemsModel.prototype.filterItemsByString = function(item, filter) {
					var category, comment, key, keys, value, _i, _j, _len, _len1, _ref, _ref1;
					keys = ['name', 'maker', 'description'];
					filter = filter.toLowerCase();
					for (key in item) {
						value = item[key];
						if (__indexOf.call(keys, key) >= 0) {
							if (key === 'comments') {
								// _ref = item.comments;
								// for (_i = 0, _len = _ref.length; _i < _len; _i++) {
								// 	comment = _ref[_i];
								// 	if (comment.comment.toLowerCase().indexOf(filter) !== -1) {
								// 		return true;
								// 	}
								// }
							} else if (value.toLowerCase().indexOf(filter) !== -1) {
								return true;
							}
						}
					}
					return false;
				};

				return ItemsModel;

			})(_Model);
			return new ItemsModel();
		}
	]);

}).call(this);
