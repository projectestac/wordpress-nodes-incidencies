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

include plugin_dir_path(__FILE__) . 'register_post_type.php';
include plugin_dir_path(__FILE__) . 'meta_box.php';
include plugin_dir_path(__FILE__) . 'admin_ui_list.php';
include plugin_dir_path(__FILE__) . 'post_template.php';
include plugin_dir_path(__FILE__) . 'export_csv.php';
include plugin_dir_path(__FILE__) . 'styles.php';

add_action('init', 'nodes_incidencies_register_post_type');
add_action('pre_get_posts', 'add_nodes_incidencies_to_query');
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
add_action('transition_post_status', 'wpse118970_post_status_new', 10, 3);
function wpse118970_post_status_new($new_status, $old_status, $post) {
    if (!current_user_can('edit_others_posts') && $post->post_type == 'nodes_incidencies' && $new_status == 'pending' && $old_status != $new_status) {
        $post->post_status = 'private';
        wp_update_post($post);
    }
}

add_action('post_submitbox_misc_actions', 'wpse118970_change_visibility_metabox');
function wpse118970_change_visibility_metabox() {
    global $post;
    if ($post->post_type != 'nodes_incidencies' || current_user_can('edit_others_posts'))
        return;
    $post->post_password = '';
    $visibility = 'private';
    $visibility_trans = __('Private');
    ?>
    <script type="text/javascript">
        (function ($) {
            try {
                $('#post-visibility-display').text('<?php echo $visibility_trans; ?>');
                $('#hidden-post-visibility').val('<?php echo $visibility; ?>');
            } catch (err) {
            }
        })(jQuery);
    </script>
    <?php
}

// Si els usuaris no han fet login, no poden consultar les incidències publicades
add_action('template_redirect', function () {
    if (!is_user_logged_in() && ('nodes_incidencies' == get_post_type())) {
        wp_redirect(esc_url(wp_login_url()), 307);
    }

});
