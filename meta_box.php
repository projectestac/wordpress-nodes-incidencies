<?php

// Metabox incidencies_informatiques
function add_incidencies_informatiques_metaboxes() {
    add_meta_box(
        'incidencies_informatiques_template',
        'Identificació (si cal)',
        'incidencies_informatiques_template',
        'nodes_incidencies',
        'normal',
        'default'
    );
}

function incidencies_informatiques_template() {
    global $post;

    // Afegim 'noncename' per seguretat
    echo '<input type="hidden" name="incidencies_informatiques_noncename" id="incidencies_informatiques_noncename" value="' .
        wp_create_nonce(plugin_basename(__FILE__)) . '" />';

    // Recuperem les dades existents, si es que hi ha dades existents.
    $sace = get_post_meta($post->ID, 'sace', true);
    $serialnumber = get_post_meta($post->ID, 'serialnumber', true);
    $remedy = get_post_meta($post->ID, 'remedy', true);

    // L'input que apareixerà quan creem una nova incidència
    echo "<div class='identificadors'>";
    echo '<span class="lbl-sace"><label>SACE</label></span> <input width="300px" type="text" name="sace" value="' . $sace . '" />';
    echo '<span class="lbl-serialnumber"><label>Número de sèrie</label></span> <input width="300px" type="text" name="serialnumber" value="' . $serialnumber . '" />';

    if (current_user_can('editor') || current_user_can('administrator')) {
        echo '<span class="lbl-remedy"><label>Remedy</label></span> <input width="300px" type="text" name="remedy" value="' . $remedy . '" />';
    } else {
        echo '<span class="lbl-remedy"><label>Remedy</label></span> <input width="300px" type="text" name="remedy" value="' . $remedy . '" disabled/>';
    }

    echo '</div>';

}

function save_incidencies_informatiques_meta($post_id, $post) {

    if (!isset($_POST['incidencies_informatiques_noncename'])) {
        return $post->ID;
    }

    if (!wp_verify_nonce($_POST['incidencies_informatiques_noncename'], plugin_basename(__FILE__))) {
        return $post->ID;
    }

    if (!current_user_can('edit_post', $post->ID)) {
        return $post->ID;
    }

    $sace_meta['sace'] = $_POST['sace'];
    $sace_meta['serialnumber'] = $_POST['serialnumber'];
    $sace_meta['remedy'] = $_POST['remedy'];

    foreach ($sace_meta as $key => $value) {
        // Check if it's a revision
        if ($post->post_type == 'revision') {
            return;
        }

        if (get_post_meta($post->ID, $key, false)) {
            // If it has value, update it
            update_post_meta($post->ID, $key, $value);
        } else {
            // If it hasn't value, create it
            add_post_meta($post->ID, $key, $value);
        }

        // If void, delete it
        if (!$value) {
            delete_post_meta($post->ID, $key);
        }
    }

}

add_action('save_post', 'save_incidencies_informatiques_meta', 1, 2);
