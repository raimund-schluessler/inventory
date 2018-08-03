<?php
    script('inventory', 'merged');
    style('inventory', 'style');
?>

<div id="app-navigation">
    <ul>
        <router-link
            tag="li"
            v-for="view in views"
            :to="'/' + view.id"
            :key="view.id"
            :class="'icon-' + view.id"
            active-class="active">
            <a class="sprite">
                <span class="title">{{ view.name }}</span>
            </a>
        </router-link>
    </ul>
</div>

<div id="app-content">
    <router-view class="content-wrapper"></router-view>
</div>
