<?php

if (!defined('ABSPATH')) {
    exit;
}

/* Export CSV */

function admin_incidencies_list_add_export_button($which) {

    global $typenow;

    if ('nodes_incidencies' === $typenow && 'top' === $which) {
        ?>
        <input type="submit" name="export_all_incidencies" class="button export"
               value="<?php _e('Exporta'); ?>"/>
        <?php wp_nonce_field('nodes_export_incidencies_' . get_current_user_id(), 'nodes_incidencies_export_nonce'); ?>
        <?php
    }

}

add_action('manage_posts_extra_tablenav', 'admin_incidencies_list_add_export_button', 20, 1);

// Neutralitza valors que s'interpretarien com a fórmula (injecció CSV): els que comencen per
// tabulador o retorn de carro, i els que tenen =, +, - o @ com a primer caràcter visible, encara
// que hi hagi espais (també no separables) al davant, perquè alguns fulls de càlcul els ignoren.
function nodes_incidencies_csv_safe($v) {
    if (is_string($v) && preg_match('/^(?:[\t\r]|(?:\s|\xC2\xA0)*[=+\-@])/', $v)) {
        return "'" . $v;
    }
    return $v;
}

function func_export_all_incidencies() {

    if (!isset($_GET['export_all_incidencies'])) {
        return;
    }

    // Cal poder crear entrades: és el permís que dona accés al llistat d'incidències
    if (!is_user_logged_in() || !current_user_can('edit_posts')) {
        wp_die(
            __('No tens permisos per exportar.'),
            __('Accés denegat'),
            [
                'response'  => 403,
                'back_link' => true,
            ]
        );
    }

    check_admin_referer('nodes_export_incidencies_' . get_current_user_id(), 'nodes_incidencies_export_nonce');

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    $arg = [
        'post_type' => 'nodes_incidencies',
        'post_status' => ['publish', 'private'],
        'posts_per_page' => -1,
    ];

    // Els professors (sense edit_others_posts) només exporten les incidències que han creat ells
    if (!current_user_can('edit_others_posts')) {
        $arg['author'] = get_current_user_id();
    }

    global $post;
    $arr_post = get_posts($arg);

    if ($arr_post) {
        $previous_error_reporting = error_reporting(0);
        $data = date('d.m.Y');
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="incidencies-' . $data . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $file = fopen('php://output', 'w');

        // BOM UTF-8 perquè Excel mostri bé els accents
        fwrite($file, "\xEF\xBB\xBF");

        fputcsv($file, [
            'Data',
            'ID',
            'Títol',
            'Descripció',
            'SACE',
            'Núm.Sèrie',
            'Tipus',
            'Ubicació',
            'Estat',
            'Visibilitat',
            'Autor'
        ]);

        foreach ($arr_post as $post) {
            setup_postdata($post);

            $data_inc = get_the_date('d/m/Y H:i');

            // Títol tal com està desat: get_the_title() aplica wptexturize i retorna entitats HTML (&#8217;)
            $titol = $post->post_title;
            $descripcio = trim(strip_tags(get_the_content()));

            $sace = get_post_meta(get_the_ID(), 'sace', true);
            $serial_number = get_post_meta(get_the_ID(), 'serialnumber', true);

            // Tipus
            $amb = wp_get_post_terms(get_the_ID(), 'nodes_ambit_inc', ['fields' => 'names']);
            if (!is_array($amb)) {
                $amb = [];
            }

            // Ubicacions
            $ubi = wp_get_post_terms(get_the_ID(), 'nodes_ubicacio_inc', ['fields' => 'names']);
            if (!is_array($ubi)) {
                $ubi = [];
            }

            // Estats
            $sta = wp_get_post_terms(get_the_ID(), 'nodes_estat_inc', ['fields' => 'names']);
            if (!is_array($sta)) {
                $sta = [];
            }

            // Visibilitat (publicada / privada) i autor
            $estats_pub = [
                'publish' => 'Publicada',
                'private' => 'Privada',
                'pending' => 'Pendent',
            ];
            $post_status = get_post_status(get_the_ID());
            $visibilitat = $estats_pub[$post_status] ?? $post_status;
            $autor = get_the_author_meta('display_name', $post->post_author);

            fputcsv($file, [
                $data_inc,
                get_the_ID(),
                nodes_incidencies_csv_safe($titol),
                nodes_incidencies_csv_safe($descripcio),
                nodes_incidencies_csv_safe($sace),
                nodes_incidencies_csv_safe($serial_number),
                nodes_incidencies_csv_safe(implode(',', $amb)),
                nodes_incidencies_csv_safe(implode(',', $ubi)),
                nodes_incidencies_csv_safe(implode(',', $sta)),
                nodes_incidencies_csv_safe($visibilitat),
                nodes_incidencies_csv_safe($autor)
            ]);
        }
        error_reporting($previous_error_reporting);
        exit();
    }

}

add_action('admin_init', 'func_export_all_incidencies', 20);
