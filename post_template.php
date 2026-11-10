<?php

if (!defined('ABSPATH')) {
    exit;
}

add_filter('the_content', 'add_info_in_nodes_incidencies', 1);

function add_info_in_nodes_incidencies($content) {

    if (is_singular('nodes_incidencies')) {
        $id = get_the_ID();

        $new_content = $content . '<hr />';
        $new_content .= '<ul>';

        // Escapar sempre la sortida de meta guardada per l'usuari
        $sace = get_post_meta($id, 'sace', true);
        if ($sace) {
            $new_content .= '<li><strong>SACE:</strong>' . '&nbsp;' . esc_html($sace) . '</li>';
        }

        $serialnumber = get_post_meta($id, 'serialnumber', true);
        if ($serialnumber) {
            $new_content .= '<li><strong>Número de sèrie:</strong>' . '&nbsp;' . esc_html($serialnumber) . '</li>';
        }

        $remedy = get_post_meta($id, 'remedy', true);
        if ($remedy) {
            $new_content .= '<li><strong>Remedy:</strong>' . '&nbsp;' . esc_html($remedy) . '</li>';
        }

        // Tipus
        $terms = get_terms([
            'taxonomy' => 'nodes_ambit_inc',
            'object_ids' => $id,
            'hide_empty' => false,
        ]);

        $new_content .= '<li><strong>Tipus:</strong>' . '&nbsp;';
        $nodes_ambit = '';
        foreach ($terms as $term) {
            $nodes_ambit .= esc_html($term->name) . ', ';
        }
        $new_content .= rtrim($nodes_ambit, ', ') . '</li>';

        // Ubicacions
        $terms = get_terms([
            'taxonomy' => 'nodes_ubicacio_inc',
            'object_ids' => $id,
            'hide_empty' => false,
        ]);

        $new_content .= '<li><strong>Ubicació:</strong>' . '&nbsp;';
        $nodes_ubicacions = '';
        foreach ($terms as $term) {
            $nodes_ubicacions .= esc_html($term->name) . ', ';
        }
        $new_content .= rtrim($nodes_ubicacions, ', ') . '</li>';

        // Estats
        $terms = get_terms([
            'taxonomy' => 'nodes_estat_inc',
            'object_ids' => $id,
            'hide_empty' => false,
        ]);

        $new_content .= '<li><strong>Estats:</strong>' . '&nbsp;';
        $nodes_estats = '';
        foreach ($terms as $term) {
            $nodes_estats .= esc_html($term->name) . ', ';
        }
        $new_content .= rtrim($nodes_estats, ', ') . '</li>';

        // Informat per:
        $new_content .= '<li><strong>Informat per:</strong>' . '&nbsp;' . esc_html(get_the_author()) . '</li>';
        $new_content .= '<li><strong>Data:</strong>' . '&nbsp;' . esc_html(get_the_date()) . '</li>';
        $new_content .= '</ul>';
        return $new_content;

    }

    return $content;
}

// Excloure les incidències dels sitemaps públics
add_filter('wp_sitemaps_post_types', function ($post_types) {
    unset($post_types['nodes_incidencies']);
    return $post_types;
});

add_filter('wp_sitemaps_taxonomies', function ($taxonomies) {
    unset(
        $taxonomies['nodes_estat_inc'],
        $taxonomies['nodes_ambit_inc'],
        $taxonomies['nodes_ubicacio_inc']
    );
    return $taxonomies;
});