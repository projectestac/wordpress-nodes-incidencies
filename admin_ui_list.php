<?php

if (!defined('ABSPATH')) {
    exit;
}

// Show custom data in "Incidències" admin ui

add_filter('manage_nodes_incidencies_posts_columns', 'nodes_incidencies_admin_ui_columns');

function nodes_incidencies_admin_ui_columns($columns) {
    return [
        'cb' => $columns['cb'],
        'wps_post_id' => __('ID'),
        'imatge' => __('Imatge', 'nodes'),
        'title' => __('Title'),
        'ambit' => __('Tipus', 'nodes'),
        'ubicacio' => __('Ubicació', 'nodes'),
        'estat' => __('Estat', 'nodes'),
        'sace' => __('SACE', 'nodes'),
        'serialnumber' => __('N.Sèrie', 'nodes'),
        'remedy' => __('Remedy', 'nodes'),
        'author' => __('Informat per', 'nodes'),
        'date' => __('Data', 'nodes'),
    ];
}

add_action('manage_nodes_incidencies_posts_custom_column', 'nodes_incidencies_column', 10, 2);

function nodes_incidencies_column($column, $post_id) {

    // Escapar sempre la sortida de meta guardada per l'usuari
    if ('sace' === $column) {
        echo esc_html(get_post_meta($post_id, 'sace', true));
    }

    if ('serialnumber' === $column) {
        echo esc_html(get_post_meta($post_id, 'serialnumber', true));
    }

    if ('remedy' === $column) {
        echo esc_html(get_post_meta($post_id, 'remedy', true));
    }

    if ('wps_post_id' === $column) {
        echo (int) $post_id;
    }

    if ('imatge' === $column) {
        if (has_post_thumbnail($post_id)) {
            echo get_the_post_thumbnail($post_id, [40, 40], ['style' => 'width:40px;height:40px;object-fit:cover;border-radius:3px;']);
        } else {
            echo '—';
        }
    }

    if ('estat' === $column) {
        $url_base = 'edit.php?post_type=nodes_incidencies';
        $estats = get_the_terms($post_id, 'nodes_estat_inc');

        if (is_array($estats)) {
            $estats_list = '';
            foreach ($estats as $estat) {
                $estats_list .= " <a href='" . esc_url($url_base . "&nodes_estat_inc=" . $estat->slug) . "'>" . esc_html($estat->name) . "</a>,";
            }
            echo rtrim($estats_list, ','); // remove last comma
        }
    }

    if ('ambit' === $column) {
        $url_base = 'edit.php?post_type=nodes_incidencies';
        $ambits = get_the_terms($post_id, 'nodes_ambit_inc');

        if (is_array($ambits)) {
            $ambits_list = '';
            foreach ($ambits as $ambit) {
                $ambits_list .= " <a href='" . esc_url($url_base . "&nodes_ambit_inc=" . $ambit->slug) . "'>" . esc_html($ambit->name) . "</a>,";
            }
            echo rtrim($ambits_list, ','); // remove last comma
        }
    }

    if ('ubicacio' === $column) {
        $url_base = 'edit.php?post_type=nodes_incidencies';
        $ubicacions = get_the_terms($post_id, 'nodes_ubicacio_inc');

        if (is_array($ubicacions)) {
            $ubicacio_list = '';
            foreach ($ubicacions as $ubi) {
                $ubicacio_list .= " <a href='" . esc_url($url_base . "&nodes_ubicacio_inc=" . $ubi->slug) . "'>" . esc_html($ubi->name) . "</a>,";
            }
            echo rtrim($ubicacio_list, ','); // remove last comma
        }
    }

}

// Define sortable
add_filter('manage_edit-nodes_incidencies_sortable_columns', 'nodes_incidencies_sortable_columns');

function nodes_incidencies_sortable_columns($columns) {
    $columns['wps_post_id'] = 'ID';
    return $columns;
}

// Pre-selected "Oberta"
add_filter('wp_terms_checklist_args', function ($args, $post_id) {

    if ($args['taxonomy'] !== 'nodes_estat_inc') {
        return $args;
    }

    // Only do this for new posts, i.e. doesn't overwrite a post that has already been saved
    if (isset($_GET['post'])) {
        return $args;
    }

    if (term_exists('Oberta', 'nodes_estat_inc')) {
        $estat = term_exists('Oberta', 'nodes_estat_inc');
        $args['selected_cats'][] = $estat['term_id'];
    }

    return $args;

}, 10, 2);

// Cerca a la llista d'incidències: inclou també els camps meta (SACE, número de sèrie, Remedy).
// Condició compartida pels tres filtres SQL de sota.
function nodes_incidencies_es_cerca_admin() {
    global $pagenow;
    return is_admin()
        && 'edit.php' === $pagenow
        && isset($_GET['post_type'])
        && 'nodes_incidencies' === $_GET['post_type']
        && !empty($_GET['s']);
}

add_filter('posts_join', 'nodes_incidencies_search_join', 10, 2);

function nodes_incidencies_search_join($join, $query) {

    global $wpdb;

    if (nodes_incidencies_es_cerca_admin() && $query->is_main_query()) {
        $join .= " LEFT JOIN {$wpdb->postmeta}
                  ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id
                  AND {$wpdb->postmeta}.meta_key IN ('sace', 'serialnumber', 'remedy') ";
    }

    return $join;

}

add_filter('posts_where', 'nodes_incidencies_search_where', 10, 2);

function nodes_incidencies_search_where($where, $query) {

    global $wpdb;

    if (nodes_incidencies_es_cerca_admin() && $query->is_main_query()) {
        $where = preg_replace(
            "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)", $where);
    }

    return $where;

}

add_filter('posts_groupby', 'nodes_incidencies_search_groupby', 10, 2);

// Evita resultats duplicats quan més d'un camp meta coincideix amb la cerca
function nodes_incidencies_search_groupby($groupby, $query) {

    global $wpdb;

    if (nodes_incidencies_es_cerca_admin() && $query->is_main_query()) {
        $groupby = "{$wpdb->posts}.ID";
    }

    return $groupby;

}

// Dropdown filters with custom fields 
add_action('restrict_manage_posts', 'nodes_incidencies_admin_posts_filter_restrict_manage_posts');

/**
 * First create the dropdown
 * @return void
 * @author Ohad Raz
 */
function nodes_incidencies_admin_posts_filter_restrict_manage_posts() {

    $type = 'post';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }

    //only add filter to post type you want
    if ('nodes_incidencies' == $type) {
        // Change this to the list of values you want to show
        // in 'label' => 'value' format

        $terms = get_terms([
            'taxonomy' => 'nodes_estat_inc',
            'hide_empty' => false,
        ]);

        $values = [];

        foreach ($terms as $term) {
            $values[$term->name] = $term->slug;
        }
        ?>

        <select name="nodes_estat_inc">
            <option value=""><?php _e('Tots els estats', 'nodes'); ?></option>
            <?php
            $current_v = $_GET['nodes_estat_inc'] ?? '';
            foreach ($values as $label => $value) {
                printf
                (
                    '<option value="%s"%s>%s</option>',
                    esc_attr($value),
                    $value == $current_v ? ' selected="selected"' : '',
                    esc_html($label)
                );
            }
            ?>
        </select>

        <?php
        $terms = get_terms([
            'taxonomy' => 'nodes_ubicacio_inc',
            'hide_empty' => false,
        ]);
        $values = [];

        foreach ($terms as $term) {
            $values[$term->name] = $term->slug;
        }
        ?>

        <select name="nodes_ubicacio_inc">
            <option value=""><?php _e('Totes les ubicacions', 'nodes'); ?></option>
            <?php
            $current_v = $_GET['nodes_ubicacio_inc'] ?? '';
            foreach ($values as $label => $value) {
                printf
                (
                    '<option value="%s"%s>%s</option>',
                    esc_attr($value),
                    $value == $current_v ? ' selected="selected"' : '',
                    esc_html($label)
                );
            }
            ?>
        </select>

        <?php
        $terms = get_terms([
            'taxonomy' => 'nodes_ambit_inc',
            'hide_empty' => false,
        ]);
        $values = [];

        foreach ($terms as $term) {
            $values[$term->name] = $term->slug;
        }
        ?>

        <select name="nodes_ambit_inc">
            <option value=""><?php _e('Tots els tipus', 'nodes'); ?></option>
            <?php
            $current_v = $_GET['nodes_ambit_inc'] ?? '';
            foreach ($values as $label => $value) {
                printf
                (
                    '<option value="%s"%s>%s</option>',
                    esc_attr($value),
                    $value == $current_v ? ' selected="selected"' : '',
                    esc_html($label)
                );
            }
            ?>
        </select>

        <?php
    }

}


add_filter('views_edit-nodes_incidencies', 'nodes_incidencies_quick_links_labels');

// TODO: internacionalització
function nodes_incidencies_quick_links_labels($views) {

    if (isset($views['trash'])) {
        $views['trash'] = str_replace('Paperera', 'Arxivades', $views['trash']);
    }

    if (isset($views['mine'])) {
        $views['mine'] = str_replace('Els meus', 'Les meves', $views['mine']);
    }
    return $views;

}
