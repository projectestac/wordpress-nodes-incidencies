<?php

// Show custom data in "Incidències" admin ui

add_filter('manage_nodes_incidencies_posts_columns', 'nodes_incidencies_admin_ui_columns');

function nodes_incidencies_admin_ui_columns($columns) {
    return [
        'cb' => $columns['cb'],
        'wps_post_id' => __('ID'),
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

    if ('sace' === $column) {
        echo get_post_meta($post_id, 'sace', true);
    }

    if ('serialnumber' === $column) {
        echo get_post_meta($post_id, 'serialnumber', true);
    }

    if ('remedy' === $column) {
        echo get_post_meta($post_id, 'remedy', true);
    }

    if ('wps_post_id' === $column) {
        echo $post_id;
    }

    if ('estat' === $column) {
        $url_base = 'edit.php?post_type=nodes_incidencies';
        $estats = get_the_terms($post_id, 'nodes_estat_inc');

        if (is_array($estats)) {
            $estats_list = '';
            foreach ($estats as $estat) {
                $estats_list .= " <a href='" . $url_base . "&nodes_estat_inc=" . $estat->slug . "'>" . $estat->name . "</a>,";
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
                $ambits_list .= " <a href='" . $url_base . "&nodes_ambit_inc=" . $ambit->slug . "'>" . $ambit->name . "</a>,";
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
                $ubicacio_list .= " <a href='" . $url_base . "&nodes_ubicacio_inc=" . $ubi->slug . "'>" . $ubi->name . "</a>,";
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

add_filter('posts_join', 'nodes_incidencies_search_join');

function nodes_incidencies_search_join($join) {

    global $pagenow, $wpdb;

    // I want the filter only when performing a search on edit page of Custom Post Type named "nodes_incidencies".
    if (isset($_GET['post_type']) && is_admin() && 'edit.php' === $pagenow && 'nodes_incidencies' === $_GET['post_type'] && !empty($_GET['s'])) {
        $join .= 'LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }

    return $join;

}

add_filter('posts_where', 'nodes_incidencies_search_where');

function nodes_incidencies_search_where($where) {

    global $pagenow, $wpdb;

    // I want the filter only when performing a search on edit page of Custom Post Type named "nodes_incidencies".
    if (isset($_GET['post_type']) && is_admin() && 'edit.php' === $pagenow && 'nodes_incidencies' === $_GET['post_type'] && !empty($_GET['s'])) {
        $where = preg_replace(
            "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)", $where);
        $where .= " GROUP BY {$wpdb->posts}.id"; // Solves duplicated results
    }

    return $where;

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
                    $value,
                    $value == $current_v ? ' selected="selected"' : '',
                    $label
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
            <option value=""><?php _e('Tots les ubicacions', 'nodes'); ?></option>
            <?php
            $current_v = $_GET['nodes_ubicacio_inc'] ?? '';
            foreach ($values as $label => $value) {
                printf
                (
                    '<option value="%s"%s>%s</option>',
                    $value,
                    $value == $current_v ? ' selected="selected"' : '',
                    $label
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
                    $value,
                    $value == $current_v ? ' selected="selected"' : '',
                    $label
                );
            }
            ?>
        </select>

        <?php
    }

}

add_filter('parse_query', 'nodes_incidencies_posts_filter');

/**
 * if submitted filter by post meta
 *
 * @param  (wp_query object) $query
 *
 * @return Void
 * @author Ohad Raz
 */
function nodes_incidencies_posts_filter($query) {

    global $pagenow;
    $type = 'post';

    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }

    if ('nodes_incidencies' == $type && is_admin() && $pagenow == 'edit.php'
        && isset($_GET['ADMIN_FILTER_FIELD_VALUE']) && $_GET['ADMIN_FILTER_FIELD_VALUE'] != '') {
        $query->query_vars['meta_key'] = 'nodes_estat_inc';
        $query->query_vars['meta_value'] = $_GET['ADMIN_FILTER_FIELD_VALUE'];
    }

    if ('nodes_incidencies' == $type && is_admin() && $pagenow == 'edit.php'
        && isset($_GET['ADMIN_FILTER_FIELD_VALUE']) && $_GET['ADMIN_FILTER_FIELD_VALUE'] != '') {
        $query->query_vars['meta_key'] = 'nodes_ubicacio_inc';
        $query->query_vars['meta_value'] = $_GET['ADMIN_FILTER_FIELD_VALUE'];
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
