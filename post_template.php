<?php

add_filter('the_content', 'add_info_in_nodes_incidencies', 1);

function add_info_in_nodes_incidencies($content) {

    if (is_singular('nodes_incidencies')) {
        $id = get_the_ID();

        $new_content = $content . '<hr />';
        $new_content .= '<ul>';

        $sace = get_post_meta($id, 'sace', true);
        if ($sace) {
            $new_content .= '<li><strong>SACE:</strong>'. '&nbsp;' . $sace . '</li>';
        }

        $serialnumber = get_post_meta($id, 'serialnumber', true);
        if ($serialnumber) {
            $new_content .= '<li><strong>Número de sèrie:</strong>'. '&nbsp;' . $serialnumber . '</li>';
        }

        $remedy = get_post_meta($id, 'remedy', true);
        if ($remedy) {
            $new_content .= '<li><strong>Remedy:</strong>'. '&nbsp;' . $remedy . '</li>';
        }

        // Tipus
        $terms = $terms = get_terms([
            'taxonomy' => 'nodes_ambit_inc',
            'object_ids' => $id,
            'hide_empty' => false,
        ]);

        $new_content .= '<li><strong>Tipus:</strong>'. '&nbsp;' ;
        $nodes_ambit = '';
        foreach ($terms as $term) {
            $nodes_ambit .= $term->name . ', ';
        }
        $new_content .= rtrim($nodes_ambit, ', ') . '</li>';

        // Ubicacions
        $terms = $terms = get_terms([
            'taxonomy' => 'nodes_ubicacio_inc',
            'object_ids' => $id,
            'hide_empty' => false,
        ]);

        $new_content .= '<li><strong>Ubicació:</strong>'. '&nbsp;' ;
        $nodes_ubicacions = '';
        foreach ($terms as $term) {
            $nodes_ubicacions .= $term->name . ', ';
        }
        $new_content .= rtrim($nodes_ubicacions, ', ') . '</li>';

        // Estats
        $terms = $terms = get_terms(array(
            'taxonomy' => 'nodes_estat_inc',
            'object_ids' => $id,
            'hide_empty' => false,
        ));

        $new_content .= '<li><strong>Estats:</strong>'. '&nbsp;' ;
        $nodes_estats = '';
        foreach ($terms as $term) {
            $nodes_estats .= $term->name . ', ';
        }
        $new_content .= rtrim($nodes_estats, ', ') . '</li>';

        // Informat per:
        $new_content .= '<li><strong>Informat per:</strong>'. '&nbsp;'  . get_the_author() . '</li>';
        $new_content .= '<li><strong>Data:</strong>'. '&nbsp;'  . get_the_date() . '</li>';
        $new_content .= '</ul>';
        return $new_content;

    }

    return $content;
}
