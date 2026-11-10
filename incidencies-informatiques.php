<?php
/**
 * @package Nodes_Incidencies_informatiques
 * @version 1.0.1
 */

/**
 * Plugin Name: Nodes: Incidències informàtiques
 * Plugin URI: https://agora.xtec.cat/nodes/incidencies-informatiques
 * Description: Extensió per la gestió interna de les incidències informàtiques del centre educatiu.
 * Author: Xavier Meler
 * Version: 1.0.1
 * Author URI: https://dossier.xtec.cat/jmeler/
 */

if (!defined('ABSPATH')) {
    exit;
}

include plugin_dir_path(__FILE__) . 'register_post_type.php';
include plugin_dir_path(__FILE__) . 'meta_box.php';
include plugin_dir_path(__FILE__) . 'admin_ui_list.php';
include plugin_dir_path(__FILE__) . 'post_template.php';
include plugin_dir_path(__FILE__) . 'export_csv.php';
include plugin_dir_path(__FILE__) . 'styles.php';

add_action('init', 'nodes_incidencies_register_post_type');
add_action('init', 'nodes_incidencies_register_taxonomy');
register_activation_hook(__FILE__, 'nodes_incidencies_activate');
add_action('init', 'nodes_incidencies_set_terms');
add_action('init', 'nodes_incidencies_end_activation');

function nodes_incidencies_activate() {
    add_option('Activated_incidencies', 'nodes_incidencies');
    flush_rewrite_rules();
}

function nodes_incidencies_end_activation() {
    if (get_option('Activated_incidencies') == 'nodes_incidencies') {
        delete_option('Activated_incidencies');
    }
}

// Incidencies privades per defecte
add_filter('wp_insert_post_data', 'nodes_incidencies_forca_privada', 10, 2);

function nodes_incidencies_forca_privada($data, $postarr) {

    if ($data['post_type'] !== 'nodes_incidencies') {
        return $data;
    }

    if (current_user_can('edit_others_posts')) {
        return $data;
    }

    if (in_array($data['post_status'], ['pending', 'publish', 'future'], true)) {
        $data['post_status'] = 'private';
    }

    return $data;
}

add_action('post_submitbox_misc_actions', 'wpse118970_change_visibility_metabox');
function wpse118970_change_visibility_metabox() {
    global $post;
      if (!$post instanceof WP_Post) {
        return;
    }
    if ($post->post_type != 'nodes_incidencies' || current_user_can('edit_others_posts'))
        return;
    $post->post_password = '';
    $visibility = 'private';
    $visibility_trans = __('Private');
    ?>
    <script type="text/javascript">
        (function ($) {
            try {
                $('#post-visibility-display').text('<?php echo esc_js($visibility_trans); ?>');
                $('#hidden-post-visibility').val('<?php echo esc_js($visibility); ?>');
            } catch (err) {
            }
        })(jQuery);
    </script>
    <?php
}

// Si els usuaris no han fet login, no poden consultar les incidències
// (ni la vista individual ni els arxius de taxonomia)
add_action('template_redirect', function () {
    if (is_user_logged_in()) {
        return;
    }

       $es_contingut_incidencies = is_singular('nodes_incidencies')
        || is_post_type_archive('nodes_incidencies')
        || is_tax(['nodes_estat_inc', 'nodes_ambit_inc', 'nodes_ubicacio_inc']);

    if ($es_contingut_incidencies) {
        wp_safe_redirect(wp_login_url(get_permalink()), 307);
        exit;
    }
});


// Els arxius de taxonomia han de llistar incidències, no articles
add_action('pre_get_posts', 'nodes_incidencies_tax_archives_query');

function nodes_incidencies_tax_archives_query($query) {

    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->is_tax([
        'nodes_estat_inc',
        'nodes_ambit_inc',
        'nodes_ubicacio_inc',
    ])) {
        $query->set('post_type', 'nodes_incidencies');
    }
}

;