<div ng-controller="ItemsController" ng-if="route.viewID == 'items'">
    <table id="itemstable">
        <thead>
            <tr>
                <th id="headerItem">
                    <div id="headerItem-container">
                        <input id="select_all_items" class="select-all checkbox" type="checkbox">
                        <label for="select_all_items">
                            <span class="hidden-visually"><?php p($l->t('Select All')); ?></span>
                        </label>
                        <a class="name sort columntitle" data-sort="name">
                            <span><?php p($l->t('Item')); ?></span>
                            <span class="sort-indicator icon-triangle-n"></span>
                        </a>
                    </div>
                </th>
                <th id="headerPlace">
                    <a class="size sort columntitle" data-sort="size">
                        <span><?php p($l->t('Place')); ?></span>
                        <span class="sort-indicator hidden icon-triangle-s"></span>
                    </a>
                </th>
                <th id="headerCategories">
                    <a class="size sort columntitle" data-sort="size">
                        <span><?php p($l->t('Categories')); ?></span>
                        <span class="sort-indicator hidden icon-triangle-s"></span>
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat='item in items'>
                <td>{{ item.description }}</td>
                <td>{{ item.place }}</td>
                <td>{{ item.categories }}</td>
            </tr>
        </tbody>
    </table>
</div>
