<div id="newItemsView">
Add multiple new items (as comma separated list).

    <form id="newItems" action="#/items/new" method="post">
        <textarea ng-change="parseInput()" ng-model="rawInput"></textarea>
        Parsed items:
        <?php print_unescaped($this->inc('part.itemstable')); ?>
        <input type="submit" value="<?php p($l->t('Enlist')); ?>">
    </form>
</div>
