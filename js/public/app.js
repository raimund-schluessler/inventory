(function(angular, $, moment, undefined){

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

})(window.angular, window.jQuery, window.moment);