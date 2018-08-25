<?php
    script('inventory', 'merged');
    style('inventory', 'style');

    if ($OC_Version[0] < 14) {
        style('inventory', 'style13');
    }

?>
