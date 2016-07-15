<table id="itemstable">
    <thead>
        <tr>
            <th id="headerMaker">
                <div id="headerMaker-container">
                    <input id="select_all_items" class="select-all checkbox" type="checkbox">
                    <label for="select_all_items">
                        <span class="hidden-visually"><?php p($l->t('Select All')); ?></span>
                    </label>
                    <a class="maker sort columntitle" data-sort="maker">
                        <span><?php p($l->t('Maker')); ?></span>
                        <span class="sort-indicator icon-triangle-n"></span>
                    </a>
                </div>
            </th>
            <th id="headerItem">
                <div id="headerItem-container">
                    <a class="name sort columntitle" data-sort="name">
                        <span><?php p($l->t('Item')); ?></span>
                        <span class="sort-indicator icon-triangle-n"></span>
                    </a>
                </div>
            </th>
            <th id="headerDescription">
                <div id="headerDescription-container">
                    <a class="name sort columntitle" data-sort="description">
                        <span><?php p($l->t('Description')); ?></span>
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
                <a href="#/item/new" id="newItem" ng-show="name == 'items'">
                    <span class="icon icon-add"></span>
                    <span class="hidden-visually">Neu</span>
                </a>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr class="handler"
            ng-repeat='item in filteredItems()'
            ng-click="openDetails(item.id,$event)">
            <td>{{ item.maker }}</td>
            <td>{{ item.name }}</td>
            <td>{{ item.description }}</td>
            <td>{{ item.place.name }}</td>
            <td>
                <ul class="categories">
                    <li ng-repeat='category in item.categories'>
                        <span>{{ category.name }}</span>
                    </li>
                </ul>
            </td>
        </tr>
    </tbody>
</table>
