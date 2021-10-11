<?php

// Create CPT nodes_incidencies, taxonomies and predefined terms

function nodes_incidencies_register_post_type() {

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
        'has_archive' => false,
        'publicly_queryable' => true,
        'exclude_from_search' => true,
        'query_var' => false,
        'hierarchical' => false,
        'menu_icon' => 'dashicons-laptop',
        'supports' => [
            'title',
            'editor',
            'thumbnail'
        ],
        'rewrite' => ['slug' => 'incidencies-informatiques'],
        'register_meta_box_cb' => 'add_incidencies_informatiques_metaboxes'
    ];

    register_post_type('nodes_incidencies', $args);

}

// Add nodes_incidencies CPT to query
function add_nodes_incidencies_to_query($query) {
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
        'show_in_rest' => true,
        'capabilities' => [
            'manage_terms' => 'manage_options', //by default only admin
            'edit_terms' => 'manage_options',
            'delete_terms' => 'manage_options',
            'assign_terms' => 'edit_others_posts'  // means administrator', 'editor'
        ]
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

    register_taxonomy('nodes_ambit_inc', ['nodes_incidencies'], $args);

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

    register_taxonomy('nodes_ubicacio_inc', ['nodes_incidencies'], $args);

}

// Set of predefined TERMS
function nodes_incidencies_set_terms() {

    if (get_option('Activated_incidencies') != 'nodes_incidencies') {
        return;
    }

    // Estats
    if (!term_exists('Oberta', 'nodes_estat_inc')) {
        wp_insert_term(
            'Oberta',
            'nodes_estat_inc',
            [
                'description' => 'Incidència pendent de revisar per la coordinació digital',
                'slug' => 'oberta'
            ]
        );
    }

    if (!term_exists('Més dades', 'nodes_estat_inc')) {
        wp_insert_term(
            'Més dades',
            'nodes_estat_inc',
            [
                'description' => 'Es necessiten més dades',
                'slug' => 'mes-dades'
            ]
        );
    }

    if (!term_exists('En procés', 'nodes_estat_inc')) {
        wp_insert_term(
            'En procés',
            'nodes_estat_inc',
            [
                'description' => 'S\'esta revisant/tramitant la incidència per part de la coordinació digital o suport',
                'slug' => 'en-proces'
            ]
        );
    }

    if (!term_exists('Notificat a Remedy', 'nodes_estat_inc')) {
        wp_insert_term(
            'Notificat a Remedy',
            'nodes_estat_inc',
            [
                'description' => 'Notificat a Remedy i esperant resposta',
                'slug' => 'notificat-remedy'
            ]
        );
    }

    if (!term_exists('Cal reobrir', 'nodes_estat_inc')) {
        wp_insert_term(
            'Cal reobrir',
            'nodes_estat_inc',
            [
                'description' => 'Incidència no solucionada que cal reobrir (notificar de nou a Remedy)',
                'slug' => 'cal-reobrir'
            ]
        );
    }

    if (!term_exists('Solucionada', 'nodes_estat_inc')) {
        wp_insert_term(
            'Solucionada',
            'nodes_estat_inc',
            [
                'description' => 'Incidència solucionada correctament',
                'slug' => 'solucionada'
            ]
        );
    }

    // Tipus
    if (!term_exists('Maquinari', 'nodes_ambit_inc')) {
        wp_insert_term(
            'Maquinari',
            'nodes_ambit_inc',
            [
                'description' => 'Incidència relacionada amb el maquinari',
                'slug' => 'maquinari'
            ]
        );
    }

    $maquinari_id = term_exists('Maquinari', 'nodes_ambit_inc');
    $maquinari_id = $maquinari_id['term_id'];

    if (!term_exists('Programari i serveis', 'nodes_ambit_inc')) {
        wp_insert_term(
            'Programari i serveis',
            'nodes_ambit_inc',
            [
                'description' => 'Incidència relacionada amb el programari, aplicacions i serveis corporatius',
                'slug' => 'programari'
            ]
        );
    }
    $programari_id = term_exists('Programari', 'nodes_ambit_inc');
    $programari_id = $programari_id['term_id'];

    wp_insert_term(
        'Impressora',
        'nodes_ambit_inc',
        [
            'description' => 'Incidència relacionada amb una impressora',
            'slug' => 'impressora',
            'parent' => $maquinari_id
        ]
    );

    wp_insert_term(
        'Projector',
        'nodes_ambit_inc',
        [
            'description' => 'Incidència relacionada amb un projector',
            'slug' => 'projector',
            'parent' => $maquinari_id
        ]
    );

    wp_insert_term(
        'Panell interactiu / PDI',
        'nodes_ambit_inc',
        [
            'description' => 'Incidència relacionada amb un panell interactiu o una PDI',
            'slug' => 'panell-pdi',
            'parent' => $maquinari_id
        ]
    );

    wp_insert_term(
        'Portàtil',
        'nodes_ambit_inc',
        [
            'description' => 'Incidència relacionada amb portàtils',
            'slug' => 'portatil',
            'parent' => $maquinari_id
        ]
    );

    wp_insert_term(
        'Ordinador de sobretaula',
        'nodes_ambit_inc',
        [
            'description' => 'Incidència relacionada amb un ordinador de sobretaula',
            'slug' => 'ordinador-sobretaula',
            'parent' => $maquinari_id
        ]
    );

    wp_insert_term(
        'Pantalla',
        'nodes_ambit_inc',
        [
            'description' => 'Incidència relacionada amb una pantalla',
            'slug' => 'pantalla',
            'parent' => $maquinari_id
        ]
    );

    wp_insert_term(
        'Connectivitat WIFI',
        'nodes_ambit_inc',
        [
            'description' => 'Incidència relacionada amb la connectivitat interna',
            'slug' => 'wifi',
            'parent' => $maquinari_id
        ]
    );

    wp_insert_term(
        'Connectivitat a Internet',
        'nodes_ambit_inc',
        [
            'description' => 'Incidència relacionada amb la connectivitat externa (sortida a Internet)',
            'slug' => 'internet',
            'parent' => $maquinari_id
        ]
    );

    wp_insert_term(
        'Google Classroom',
        'nodes_ambit_inc',
        [
            'description' => 'Incidència relacionada el Google Classroom',
            'slug' => 'classroom',
            'parent' => $programari_id
        ]
    );

    wp_insert_term(
        'Google Workspace',
        'nodes_ambit_inc',
        [
            'description' => 'Incidència relacionada el Google Workspace (abans GSuite) del centre',
            'slug' => 'google-workspace',
            'parent' => $programari_id
        ]
    );

    wp_insert_term(
        'Unitats de xarxa',
        'nodes_ambit_inc',
        [
            'description' => 'Incidència relacionada amb unitats de xarxa',
            'slug' => 'unitats-xarxa',
            'parent' => $programari_id
        ]
    );

    wp_insert_term(
        'Moodle',
        'nodes_ambit_inc',
        [
            'description' => 'Incidència relacionada amb el Moodle del centre',
            'slug' => 'moodle',
            'parent' => $programari_id
        ]
    );

    wp_insert_term(
        'Nodes',
        'nodes_ambit_inc',
        [
            'description' => 'Incidència relacionada amb el web del centre',
            'slug' => 'nodes',
            'parent' => $programari_id
        ]
    );

    wp_insert_term(
        'Congelador',
        'nodes_ambit_inc',
        [
            'description' => 'Incidència relacionada amb el programari per congelar els ordinadors (Deep Freeze, etc)',
            'slug' => 'congelador',
            'parent' => $programari_id
        ]
    );

    wp_insert_term(
        'Antivirus',
        'nodes_ambit_inc',
        [
            'description' => 'Incidència relacionada amb els antivirus',
            'slug' => 'antivirus',
            'parent' => $programari_id
        ]
    );

    wp_insert_term(
        'Firewall',
        'nodes_ambit_inc',
        [
            'description' => 'Incidències relacionades amb el filtratge de pàgines web',
            'slug' => 'firewall',
            'parent' => $programari_id
        ]
    );

}
