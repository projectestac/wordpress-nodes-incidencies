<?php
/**
 * @package Nodes_Incidencies_informatiques
 * @version 1.1.0
 */

/**
 * Plugin Name: Nodes: Incidències informàtiques
 * Plugin URI: https://agora.xtec.cat/nodes/incidencies-informatiques
 * Description: Extensió per la gestió interna de les incidències informàtiques del centre educatiu.
 * Author: Xavier Meler, Òscar Benítez
 * Version: 1.1.0
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

    // En activar l'extensió, 'init' ja s'ha executat i el CPT i les taxonomies no estan registrats.
    // Cal registrar-los abans del flush perquè les regles de reescriptura els incloguin.
    nodes_incidencies_register_post_type();
    nodes_incidencies_register_taxonomy();
    flush_rewrite_rules();
}

function nodes_incidencies_end_activation() {
    if (get_option('Activated_incidencies') === 'nodes_incidencies') {
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
    if ($post->post_type !== 'nodes_incidencies' || current_user_can('edit_others_posts'))
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

// Cert si la petició és d'un visitant anònim al web públic. No s'aplica a l'administració,
// al WP-Cron ni a la línia d'ordres (scripts del Portal), on no hi ha usuari però sí accés legítim.
function nodes_incidencies_es_anonim_public() {
    if (is_user_logged_in() || wp_doing_cron() || 'cli' === PHP_SAPI) {
        return false;
    }
    return !is_admin() || wp_doing_ajax();
}

// Cert si el paràmetre post_type de la consulta (cadena o array) inclou les incidències.
function nodes_incidencies_consulta_inclou_cpt($query) {
    return in_array('nodes_incidencies', (array) $query->get('post_type'), true);
}

// Si els usuaris no han fet login, no poden consultar les incidències: ni la vista individual,
// ni els arxius (de tipus, de taxonomia, de data, d'autor), ni la cerca, ni els feeds.
// El post_type es comprova directament perquè, si arriba com a array (?post_type[]=...),
// is_post_type_archive() retorna fals i la consulta llistaria les incidències.
add_action('template_redirect', function () {
    global $wp_query;

    if (!nodes_incidencies_es_anonim_public()) {
        return;
    }

    $es_contingut_incidencies = is_singular('nodes_incidencies')
        || is_post_type_archive('nodes_incidencies')
        || is_tax(['nodes_estat_inc', 'nodes_ambit_inc', 'nodes_ubicacio_inc'])
        || nodes_incidencies_consulta_inclou_cpt($wp_query);

    if ($es_contingut_incidencies) {
        $redirect_to = is_singular('nodes_incidencies')
            ? get_permalink()
            : set_url_scheme('//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        wp_safe_redirect(wp_login_url($redirect_to), 307);
        exit;
    }
});

// Xarxa de seguretat: treu les incidències dels resultats de qualsevol consulta secundària
// (ginys, blocs, extensions de tercers...) feta per a un visitant anònim. La consulta principal
// no es toca perquè la redirecció anterior ja l'atura i així es manté la redirecció al login
// des de la vista individual.
add_filter('the_posts', function ($posts, $query) {
    if (empty($posts) || $query->is_main_query() || !nodes_incidencies_es_anonim_public()) {
        return $posts;
    }

    return array_values(array_filter($posts, static function ($post) {
        return 'nodes_incidencies' !== get_post_type($post);
    }));
}, 10, 2);

// oEmbed (/wp-json/oembed/1.0/embed?url=...) exposa el títol i l'autor de qualsevol entrada
// publicada. Per als anònims, es tracta una incidència com si no existís (resposta 404).
add_filter('oembed_request_post_id', function ($post_id) {
    if ($post_id && 'nodes_incidencies' === get_post_type($post_id) && nodes_incidencies_es_anonim_public()) {
        return 0;
    }
    return $post_id;
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
