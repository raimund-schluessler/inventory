<?php
    script('inventory', 'vendor/papaparse/papaparse.min');
    script('inventory', 'vendor/vue/dist/vue.min');
    script('inventory', 'public/app');
    style('inventory', 'style');
    style('inventory', 'sprite');
?>

<div id="app">
    <div id="app-navigation">
        <ul>
            <li v-for="view in views">
                <!-- ng-class="{active: route.current.scope.name == view.id}"> -->
                <a :href="'#' + view.id">
                    <span :class="'icon svg-' + view.id"></span><span class="title">{{ view.name }}</span>
                </a>
            </li>
        </ul>
    </div>

    <div id="app-content">
        <!-- <div ng-view class="content-wrapper">
        </div> -->
    </div>
</div>
