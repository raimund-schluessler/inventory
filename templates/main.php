<?php
    script('inventory', 'vendor/angular/angular.min');
    script('inventory', 'vendor/angular-route/angular-route.min');
    script('inventory', 'vendor/angular-animate/angular-animate.min');
    script('inventory', 'vendor/angular-sanitize/angular-sanitize.min');
    script('inventory', 'public/app');
    style('inventory', 'style');
?>

<div ng-app="Inventory" id="app">
    <div id="app-navigation">
        <ul>
            <li>
                <a><?php p($l->t('Places')); ?></a>
            </li>
            <li>
                <a><?php p($l->t('Items')); ?></a>
            </li>
        </ul>
    </div>

    <div id="app-content">
    </div>
</div>
