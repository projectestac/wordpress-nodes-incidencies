<?php

/* Export CSV */

function admin_incidencies_list_add_export_button($which) {

    global $typenow;

    if ('nodes_incidencies' === $typenow && 'top' === $which) {
        ?>
        <input type="submit" name="export_all_incidencies" class="button export"
               value="<?php _e('Exporta'); ?>"/>
        <?php
    }

}

add_action('manage_posts_extra_tablenav', 'admin_incidencies_list_add_export_button', 20, 1);

function func_export_all_incidencies() {

    if (isset($_GET['export_all_incidencies'])) {
        $arg = [
            'post_type' => 'nodes_incidencies',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ];

        global $post;
        $arr_post = get_posts($arg);

        if ($arr_post) {
            $data = date('d.m.Y');
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="incidències-' . $data . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Data',
                'ID',
                'Títol',
                'Descripció',
                'SACE',
                'Núm.Sèrie',
                'Tipus',
                'Ubicació',
                'Estat'
            ]);

            foreach ($arr_post as $post) {
                setup_postdata($post);

                $data_inc = get_the_date('d/m/Y H:i');

                $titol = get_the_title();
                $descripcio = trim(strip_tags(get_the_content()));

                $sace = get_post_meta(get_the_ID(), 'sace', true);
                $serial_number = get_post_meta(get_the_ID(), 'serialnumber', true);

                // Tipus
                $ambits = get_the_terms(get_the_ID(), 'nodes_ambit_inc', '', ',');

                $amb = [];
                if (!empty($ambits)) {
                    foreach ($ambits as $ambit) {
                        $amb[] = $ambit->name;
                    }
                }

                // Ubicacions
                $ubicacions = get_the_terms(get_the_ID(), 'nodes_ubicacio_inc', '', ',');
                $ubi = [];

                if (!empty($ubicacions)) {
                    foreach ($ubicacions as $ubicacio) {
                        $ubi[] = $ubicacio->name;
                    }
                }

                // Estats
                $estats = get_the_terms(get_the_ID(), 'nodes_estat_inc', '', ',');

                $sta = [];
                if (!empty($estats)) {
                    foreach ($estats as $estat) {
                        $sta[] = $estat->name;
                    }
                }

                fputcsv($file, [
                    $data_inc,
                    get_the_ID(),
                    $titol,
                    $descripcio,
                    $sace,
                    $serial_number,
                    implode(',', $amb),
                    implode(',', $ubi),
                    implode(',', $sta)
                ]);
            }

            exit();
        }
    }

}

add_action('init', 'func_export_all_incidencies');
