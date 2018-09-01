<?php
    script('inventory', 'inventory');
    style('inventory', 'inventory');

    if ($OC_Version[0] < 14) {
        style('inventory', 'inventory13');
    }

?>
