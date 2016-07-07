(function(angular, $, oc_requesttoken, undefined){

/**
 * ownCloud Inventory App - v0.0.1
 *
 * Copyright (c) 2016 - Raimund Schlüßler <raimund.schluessler@googlemail.com>
 *
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING file
 *
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
				templateUrl: OC.generateUrl('/apps/inventory/templates/part.items', {}),
				controller: 'ItemsController',
				name: 'items'
			})
			.when('/items/:itemID', {
				templateUrl: OC.generateUrl('/apps/inventory/templates/part.itemdetails', {}),
				controller: 'ItemController',
				name: 'item'
			})
			.when('/item/new', {
				templateUrl: OC.generateUrl('/apps/inventory/templates/part.item.new', {}),
				controller: 'NewItemController',
				name: 'newitem'
			})
			.when('/places/', {
				templateUrl: OC.generateUrl('/apps/inventory/templates/part.places', {}),
				controller: 'PlacesController',
				name: 'places'
			})
			.when('/categories/', {
				templateUrl: OC.generateUrl('/apps/inventory/templates/part.categories', {}),
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
				this._$scope.route = _$route;
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

angular.module('Inventory').controller('CategoriesController', [
	'$scope', '$route', '$timeout', '$location', '$routeParams', 'Persistence', 'ItemsModel', function($scope, $route, $timeout, $location, $routeParams, Persistence, ItemsModel) {
		'use strict';
		var CategoriesController = (function() {
			function CategoriesController(_$scope, _$route, _$timeout, _$location, _$routeparams, _persistence, _$itemsmodel) {
				this._$scope = _$scope;
				this._$scope.name = 'categories';

				this._$route = _$route;
				this._$timeout = _$timeout;
				this._$location = _$location;
				this._$routeparams = _$routeparams;
				this._persistence = _persistence;
				this._$scope.route = this._$routeparams;
			}
			return CategoriesController;
		})();
		return new CategoriesController($scope, $route, $timeout, $location, $routeParams, Persistence, ItemsModel);
	}
]);

angular.module('Inventory').controller('ItemController', [
	'$scope', '$route', '$timeout', '$location', '$routeParams', 'Persistence', 'ItemsModel', function($scope, $route, $timeout, $location, $routeParams, Persistence, ItemsModel) {
		'use strict';
		var ItemController = (function() {
			function ItemController(_$scope, _$route, _$timeout, _$location, _$routeparams, _persistence, _$itemsmodel) {
				this._$scope = _$scope;
				this._$scope.name = 'newItem';

				this._$route = _$route;
				this._$timeout = _$timeout;
				this._$location = _$location;
				this._$routeparams = _$routeparams;
				this._persistence = _persistence;
				this._$itemsmodel = _$itemsmodel;
				this._$scope.route = this._$routeparams;
				this._persistence.getItems();
				this._$scope.items = this._$itemsmodel.getAll();

				console.log('ItemController loaded.')
			}
			return ItemController;
		})();
		return new ItemController($scope, $route, $timeout, $location, $routeParams, Persistence, ItemsModel);
	}
]);

angular.module('Inventory').controller('ItemsController', [
	'$scope', '$route', '$timeout', '$location', '$routeParams', 'Persistence', 'ItemsModel', function($scope, $route, $timeout, $location, $routeParams, Persistence, ItemsModel) {
		'use strict';
		var ItemsController = (function() {
			function ItemsController(_$scope, _$route, _$timeout, _$location, _$routeparams, _persistence, _$itemsmodel) {
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

				this._$scope.openDetails = function(id, $event) {
					var viewID = _$scope.route.viewID
					if ($($event.currentTarget).is($($event.target).closest('.handler'))) {
						$location.path('items/' + id);
					}
				};
			}
			return ItemsController;
		})();
		return new ItemsController($scope, $route, $timeout, $location, $routeParams, Persistence, ItemsModel);
	}
]);

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
							'place':		it[3],
							'categories':	it[4].split(',').map(function(s) {
								return String.prototype.trim.apply(s);
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

angular.module('Inventory').controller('PlacesController', [
	'$scope', '$route', '$timeout', '$location', '$routeParams', 'Persistence', 'ItemsModel', function($scope, $route, $timeout, $location, $routeParams, Persistence, ItemsModel) {
		'use strict';
		var PlacesController = (function() {
			function PlacesController(_$scope, _$route, _$timeout, _$location, _$routeparams, _persistence, _$itemsmodel) {
				this._$scope = _$scope;
				this._$scope.name = 'places';

				this._$route = _$route;
				this._$timeout = _$timeout;
				this._$location = _$location;
				this._$routeparams = _$routeparams;
				this._persistence = _persistence;
				this._$scope.route = this._$routeparams;
			}
			return PlacesController;
		})();
		return new PlacesController($scope, $route, $timeout, $location, $routeParams, Persistence, ItemsModel);
	}
]);

angular.module('Inventory').factory('Loading', [
	function() {
		'use strict';
		var Loading = (function() {
			function Loading() {
				this.count = 0;
			}

			Loading.prototype.increase = function() {
				return this.count += 1;
			};

			Loading.prototype.decrease = function() {
				return this.count -= 1;
			};

			Loading.prototype.getCount = function() {
				return this.count;
			};

			Loading.prototype.isLoading = function() {
				return this.count > 0;
			};

			return Loading;

		})();
		return new Loading();
	}
]);

(function() {
	'use strict';
  angular.module('Inventory').factory('_Model', [
	function() {
	  var Model;
	  Model = (function() {
		function Model() {
		  this._data = [];
		  this._dataMap = {};
		  this._filterCache = {};
		}

		Model.prototype.handle = function(data) {
		  var item, _i, _len, _results;
		  _results = [];
		  for (_i = 0, _len = data.length; _i < _len; _i++) {
			item = data[_i];
			_results.push(this.add(item));
		  }
		  return _results;
		};

		Model.prototype.add = function(data, clearCache) {
		  if (clearCache === null) {
			clearCache = true;
		  }
		  if (clearCache) {
			this._invalidateCache();
		  }
		  if (angular.isDefined(this._dataMap[data.id])) {
			return this.update(data, clearCache);
		  } else {
			this._data.push(data);
			this._dataMap[data.id] = data;
		  }
		};

		Model.prototype.update = function(data, clearCache) {
		  var entry, key, value, _results;
		  if (clearCache === null) {
			clearCache = true;
		  }
		  if (clearCache) {
			this._invalidateCache();
		  }
		  entry = this.getById(data.id);
		  _results = [];
		  for (key in data) {
			value = data[key];
			if (key === 'id') {
			  continue;
			} else {
			  _results.push(entry[key] = value);
			}
		  }
		  return _results;
		};

		Model.prototype.getById = function(id) {
		  return this._dataMap[id];
		};

		Model.prototype.getAll = function() {
		  return this._data;
		};

		Model.prototype.removeById = function(id, clearCache) {
		  var counter, data, entry, _i, _len, _ref;
		  if (clearCache === null) {
			clearCache = true;
		  }
		  _ref = this._data;
		  for (counter = _i = 0, _len = _ref.length; _i < _len; counter = ++_i) {
			entry = _ref[counter];
			if (entry.id === id) {
			  this._data.splice(counter, 1);
			  data = this._dataMap[id];
			  delete this._dataMap[id];
			  if (clearCache) {
				this._invalidateCache();
			  }
			  return data;
			}
		  }
		};

		Model.prototype.clear = function() {
		  this._data.length = 0;
		  this._dataMap = {};
		  return this._invalidateCache();
		};

		Model.prototype._invalidateCache = function() {
		  this._filterCache = {};
		};

		Model.prototype.get = function(query) {
		  var hash;
		  hash = query.hashCode();
		  if (!angular.isDefined(this._filterCache[hash])) {
			this._filterCache[hash] = query.exec(this._data);
		  }
		  return this._filterCache[hash];
		};

		Model.prototype.size = function() {
		  return this._data.length;
		};

		return Model;

	  })();
	  return Model;
	}
  ]);

}).call(this);

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
		};

	angular.module('Inventory').factory('ItemsModel', [
		'_Model', function(_Model) {
			var ItemsModel = (function(_super) {
				__extends(ItemsModel, _super);

				function ItemsModel() {
					this._nameCache = {};
					ItemsModel.__super__.constructor.call(this);
				}

				return ItemsModel;

			})(_Model);
			return new ItemsModel();
		}
	]);

}).call(this);

angular.module('Inventory').factory('Persistence', [
	'Request', 'Loading', '$rootScope', '$q', function(Request, Loading, $rootScope, $q) {
		'use strict';
		var Persistence = (function() {
			function Persistence(_request, _Loading, _$rootScope) {
				this._request = _request;
				this._Loading = _Loading;
				this._$rootScope = _$rootScope;
			}

			Persistence.prototype.getItems = function(onSuccess, showLoading) {
				var failureCallbackWrapper, params, successCallbackWrapper,
				_this = this;
				if (showLoading === null) {
					showLoading = true;
				}
				if (!onSuccess) {
					onSuccess = function() {};
				}
				if (showLoading) {
					this._Loading.increase();
					successCallbackWrapper = function(data) {
						onSuccess();
						return _this._Loading.decrease();
					};
					failureCallbackWrapper = function(data) {
						return _this._Loading.decrease();
					};
				} else {
					successCallbackWrapper = function(data) {
						return onSuccess();
					};
					failureCallbackWrapper = function(data) {};
				}
				params = {
					onSuccess: successCallbackWrapper,
					onFailure: failureCallbackWrapper
				};
				return this._request.get('/apps/inventory/items', params);
			};

			Persistence.prototype.enlist = function(item) {
				var params = {
					data: {
						item: item
					}
				};
				return this._request.post('/apps/inventory/item/add', params);
			};

		return Persistence;
	  })();
	  return new Persistence(Request, Loading, $rootScope);
	}
]);

angular.module('Inventory').factory('Publisher', [
	'ItemsModel', function(ItemsModel) {
		'use strict';
		var Publisher = (function() {
			function Publisher(_$itemsmodel) {
				this._$itemsmodel = _$itemsmodel;
				this._subscriptions = {};
				this.subscribeObjectTo(this._$itemsmodel, 'items');
			}

			Publisher.prototype.subscribeObjectTo = function(object, name) {
				var base = this._subscriptions;
				if (!base[name]) {
					base[name] = [];
				}
				return this._subscriptions[name].push(object);
			};

			Publisher.prototype.publishDataTo = function(data, name) {
				var ref, results, subscriber, _i, _len;
				ref = this._subscriptions[name] || [];
				results = [];
				for (_i = 0, _len = ref.length; _i < _len; _i++) {
					subscriber = ref[_i];
					results.push(subscriber.handle(data));
				}
				return results;
			};
			return Publisher;
		})();
		return new Publisher(ItemsModel);
	}
]);

angular.module('Inventory').factory('Request', [
	'$http', 'Publisher', function($http, Publisher) {
		'use strict';
		var Request = (function() {
			function Request($http, publisher) {
				this.$http = $http;
				this.publisher = publisher;
				this.count = 0;
				this.initialized = false;
				this.shelvedRequests = [];
				this.initialized = true;
				this._executeShelvedRequests();
			}

			Request.prototype.request = function(route, data) {
				var defaultConfig, defaultData, url;
				if (data === null) {
					data = {};
				}
				defaultData = {
					routeParams: {},
					data: {},
					onSuccess: function() {
						return {};
					},
					onFailure: function() {
						return {};
					},
					config: {}
				};
				angular.extend(defaultData, data);
				if (!this.initialized) {
					this._shelveRequest(route, defaultData);
					return;
				}
				url = OC.generateUrl(route, defaultData.routeParams);
				defaultConfig = {
					url: url,
					data: defaultData.data
				};
				angular.extend(defaultConfig, defaultData.config);
				if (defaultConfig.method === 'GET') {
					defaultConfig.params = defaultConfig.data;
				}
				return this.$http(defaultConfig).success((function(_this) {
					return function(data, status, headers, config) {
						var name, ref, value;
						ref = data.data;
						for (name in ref) {
							value = ref[name];
							_this.publisher.publishDataTo(value, name);
						}
						return defaultData.onSuccess(data, status, headers, config);
					};
				})(this)).error(function(data, status, headers, config) {
					return defaultData.onFailure(data, status, headers, config);
				});
			};

			Request.prototype.post = function(route, data) {
				if (data === null) {
					data = {};
				}
				if (!data.config) {
					data.config = {};
				}
				data.config.method = 'POST';
				return this.request(route, data);
			};

			Request.prototype.get = function(route, data) {
				if (data === null) {
					data = {};
				}
				if (!data.config) {
					data.config = {};
				}
				data.config.method = 'GET';
				return this.request(route, data);
			};

			Request.prototype.put = function(route, data) {
				if (data === null) {
					data = {};
				}
				if (!data.config) {
					data.config = {};
				}
				data.config.method = 'PUT';
				return this.request(route, data);
			};

			Request.prototype["delete"] = function(route, data) {
				if (data === null) {
					data = {};
				}
				if (!data.config) {
					data.config = {};
				}
				data.config.method = 'DELETE';
				return this.request(route, data);
			};

			Request.prototype._shelveRequest = function(route, data) {
				var request = {
					route: route,
					data: data
				};
				return this.shelvedRequests.push(request);
			};

			Request.prototype._executeShelvedRequests = function() {
				var r, ref, results, _i, _len;
				ref = this.shelvedRequests;
				results = [];
				for (_i = 0, _len = ref.length; _i < _len; _i++) {
					r = ref[_i];
					results.push(this.request(r.route, r.data));
				}
				return results;
			};
			return Request;
		})();
	return new Request($http, Publisher);
	}
]);

})(window.angular, window.jQuery, oc_requesttoken);