<?php

add_action('admin_head', 'nodes_incidencies_css');

function nodes_incidencies_css() {

    echo '<style>';
    echo '.column-wps_post_id {width:70px !important}';
    echo '#A2A_SHARE_SAVE_meta {display:none}';
    echo '.post-type-nodes_incidencies #addtag div.term-slug-wrap {display:none}';
    echo '.lbl-sace {display:block;width:300px}';
    echo '.identificadors {
            display: grid;
            grid-template-columns: 200px 400px;
            grid-template-rows: repeat( 3, 1fr );
            grid-auto-flow: column;
          }';
    echo '.lbl-sace {
            grid-column-start: 1;
            grid-column-end: 1;
            grid-row-start: 1;
            grid-row-end: 1;
          }';
    echo '.lbl-serialnumber {
            grid-column-start: 1;
            grid-column-end: 1;
            grid-row-start: 2;
            grid-row-end: 2;
          }';
    echo '.lbl-remedy {
            grid-column-start: 1;
            grid-column-end: 1;
            grid-row-start: 3;
            grid-row-end: 3;
          }';

    // Nodes1 bug
    echo '.post-type-nodes_incidencies .tablenav select {min-width: inherit;}';

    if (!current_user_can('activate_plugins')) {
        echo '#nodes_estat_incdiv { display:none }';
    }

    echo '</style>';
}
