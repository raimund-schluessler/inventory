<?php
    script('inventory', 'vendor/papaparse/papaparse.min');
    script('inventory', 'vendor/vue/dist/vue.min');
    script('inventory', 'vendor/vue-router/dist/vue-router.min');
    script('inventory', 'vendor/axios/dist/axios.min');
    script('inventory', 'public/app');
    style('inventory', 'style');
    style('inventory', 'sprite');
?>

<div id="app">
    <div id="app-navigation">
        <ul>
            <router-link
                tag="li"
                v-for="view in views"
                :to="'/' + view.id"
                active-class="active">
                <a>
                    <span class="icon" :class="'svg-' + view.id"></span><span class="title">{{ view.name }}</span>
                </a>
            </router-link>
        </ul>
    </div>

    <div id="app-content">
        <router-view class="content-wrapper"></router-view>
    </div>
</div>
