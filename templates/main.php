<?php
    script('inventory', 'vendor/angular/angular.min');
    script('inventory', 'vendor/angular-route/angular-route.min');
    script('inventory', 'vendor/angular-animate/angular-animate.min');
    script('inventory', 'vendor/angular-sanitize/angular-sanitize.min');
    script('inventory', 'public/app');
    style('inventory', 'style');
?>

<div ng-app="Inventory" id="app" ng-cloak ng-controller="AppController">
    <div id="app-navigation">
        <ul>
            <li ng-repeat="view in views"
                ng-class="{active: route.viewID == view.id}">
                <a href="#/{{ view.id }}">
                    <span class="icon {{ view.id }}">
                    </span>
                    <span class="title">{{ view.name }}</span>
                </a>
            </li>
        </ul>
    </div>

    <div id="app-content">
        <div class="content-wrapper">
            <?php print_unescaped($this->inc('part.items')); ?>
            <?php print_unescaped($this->inc('part.categories')); ?>
            <?php print_unescaped($this->inc('part.places')); ?>
        </div>
    </div>
</div>
