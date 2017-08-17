<?php
    script('inventory', 'vendor/angular/angular.min');
    script('inventory', 'vendor/angular-route/angular-route.min');
    script('inventory', 'vendor/angular-animate/angular-animate.min');
    script('inventory', 'vendor/angular-sanitize/angular-sanitize.min');
    script('inventory', 'vendor/papaparse/papaparse.min');
    script('inventory', 'public/app');
    style('inventory', 'style');
    style('inventory', 'sprite');
?>

<div ng-app="Inventory" id="app" ng-cloak ng-controller="AppController">
    <div id="app-navigation">
        <ul>
            <li ng-repeat="view in views"
                ng-class="{active: route.current.scope.name == view.id}">
                <a href="#/{{ view.id }}">
                    <span class="icon svg-{{ view.id }}"></span><span class="title">{{ view.name }}</span>
                </a>
            </li>
        </ul>
    </div>

    <div id="app-content">
        <div ng-view class="content-wrapper">
        </div>
    </div>
</div>
