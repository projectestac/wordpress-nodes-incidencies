<?php

function nodes_incidencies_informatiques_register_post_type() {

    // CPT: nodes_incidencies_informatiques
    // TODO: Internacionalització

    $labels = [
        'name' => __('Incidències', 'nodes'),
        'singular_name' => __('Incidència', 'nodes'),
        'all_items' => __('Totes les incidències', 'nodes'),
        'add_new' => __('Obre una incidència', 'nodes'),
        'add_new_item' => __('Obre una incidència nova', 'nodes'),
        'edit_item' => __('Edita', 'nodes'),
        'new_item' => __('Incidència nova', 'nodes'),
        'view_item' => __('Visualitza', 'nodes'),
        'search_items' => __('Cerca', 'nodes'),
        'not_found' => __('No s\'han trobat incidències informàtiques', 'nodes'),
        'not_found_in_trash' => __('No hi ha incidències informàtiques a la paperera', 'nodes'),
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'show_in_rest' => false,
        'hierarchical' => false,
        'menu_icon' => 'dashicons-laptop',
        'supports' => [
            'title',
            'editor',
            'thumbnail'
        ],
        'rewrite' => ['slug' => 'incidencies-informatiques'],
        'has_archive' => true,
        'register_meta_box_cb' => 'add_incidencies_informatiques_metaboxes'
    ];

    register_post_type('nodes_incidencies', $args);
}

add_action('init', 'nodes_incidencies_informatiques_register_post_type');
add_action('pre_get_posts', 'add_my_post_types_to_query');

function add_my_post_types_to_query($query) {
    if (is_home() && $query->is_main_query())
        $query->set('post_type', ['post', 'nodes_incidencies']);
    return $query;
}

// TAXONOMIES
function nodes_incidencies_register_taxonomy() {

    // Estat de la incidència
    $labels = [
        'name' => __('Estats de les incidències', 'nodes'),
        'singular_name' => __('Estat', 'nodes'),
        'search_items' => __('Cerca als estats', 'nodes'),
        'all_items' => __('Tots els estats', 'nodes'),
        'edit_item' => __('Edita l\'estat', 'nodes'),
        'update_item' => __('Actualitza els estats', 'nodes'),
        'add_new_item' => __('Afegeix un estat nou', 'nodes'),
        'new_item_name' => __('Nom de l\'estat nou', 'nodes'),
        'menu_name' => __('Estats', 'nodes'),
    ];

    $args = [
        'labels' => $labels,
        'hierarchical' => true,
        'sort' => true,
        'args' => ['orderby' => 'term_order'],
        'rewrite' => ['slug' => 'estats_incidencia'],
        'show_admin_column' => true,
        'show_in_rest' => true
    ];

    register_taxonomy('nodes_estat_inc', ['nodes_incidencies'], $args);

    // TAXONOMY Àmbit
    $labels = [
        'name' => __('Tipus d\'incidències', 'nodes'),
        'singular_name' => __('Tipus', 'nodes'),
        'search_items' => __('Cerca als tipus', 'nodes'),
        'all_items' => __('Tots els tipus', 'nodes'),
        'edit_item' => __('Edita el tipus', 'nodes'),
        'update_item' => __('Actualitza el tipus', 'nodes'),
        'add_new_item' => __('Afegeix un tipus nou', 'nodes'),
        'new_item_name' => __('Nom del tipus nou', 'nodes'),
        'menu_name' => __('Tipus', 'nodes'),
    ];

    $args = [
        'labels' => $labels,
        'hierarchical' => true,
        'sort' => true,
        'args' => ['orderby' => 'term_order'],
        'rewrite' => ['slug' => 'ambit_incidencia'],
        'show_admin_column' => true,
        'show_in_rest' => true
    ];

    register_taxonomy('nodes_ambit_inc', array('nodes_incidencies'), $args);

    // TAXONOMY Ubicació
    $labels = [
        'name' => __('Ubicacions', 'nodes'),
        'singular_name' => __('Ubicació', 'nodes'),
        'search_items' => __('Cerca a les ubicacions', 'nodes'),
        'all_items' => __('Totes les ubicacions', 'nodes'),
        'edit_item' => __('Edita la ubicació', 'nodes'),
        'update_item' => __('Actualitza la ubicació', 'nodes'),
        'add_new_item' => __('Afegeix una ubicació nova', 'nodes'),
        'new_item_name' => __('Nom de la ubicació nova', 'nodes'),
        'menu_name' => __('Ubicacions', 'nodes'),
    ];

    $args = [
        'labels' => $labels,
        'hierarchical' => true,
        'sort' => true,
        'args' => ['orderby' => 'term_order'],
        'rewrite' => ['slug' => 'ubicacio_incidencia'],
        'show_admin_column' => true,
        'show_in_rest' => true
    ];

    register_taxonomy('nodes_ubicacio_inc', array('nodes_incidencies'), $args);

}

// Register all taxonomies
add_action('init', 'nodes_incidencies_register_taxonomy');
