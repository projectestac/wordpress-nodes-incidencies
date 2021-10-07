<?php
/**
 * @package Nodes_Incidencies_informatiques
 * @version 1.0.0
 */

/**
 * Plugin Name: Nodes: Incidències informàtiques
 * Plugin URI: https://agora.xtec.cat/nodes/incidencies-informatiques
 * Description: Extensió per la gestió interna de les incidències informàtiques del centre educatiu.
 * Author: Xavier Meler
 * Version: 1.0.0
 * Author URI: https://dossier.xtec.cat/jmeler/
 */

include plugin_dir_path(__FILE__) . 'register_post_type.php';
include plugin_dir_path(__FILE__) . 'meta_box.php';
include plugin_dir_path(__FILE__) . 'admin_ui_list.php';
include plugin_dir_path(__FILE__) . 'post_template.php';
include plugin_dir_path(__FILE__) . 'export_csv.php';
include plugin_dir_path(__FILE__) . 'styles.php';
