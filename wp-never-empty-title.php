<?php

/**
 * Plugin name: WP Never Empty Title
 * Author: Julien Maury
 * Author URI: https://www.julien-maury.dev
 * Version: 0.2
 * Description: Pas de titre pas de chocolat !
 */

defined('DB_USER')
    or die;

define('WP_NEC_URL', plugin_dir_url(__FILE__));
define('WP_NEC_VERSION', '0.111');

add_action('admin_init', function () {
    load_plugin_textdomain('wp-nec', false, basename(dirname(__FILE__)) . '/languages');
});

add_action('admin_enqueue_scripts', function ($hook_suffix) {

    if (!in_array($hook_suffix, ['post.php', 'post-new.php'], true)) {
        return false;
    }

    $post_types = apply_filters('wp_nec/post_types_to_bail', array_keys(get_post_types()));

    if (!in_array(get_post_type(), $post_types, true)) {
        return false;
    }

    wp_register_script('no-empty-title', WP_NEC_URL . 'js/script-save-post.js', ['jquery'], WP_NEC_VERSION, true);
    wp_enqueue_script('no-empty-title');
    wp_localize_script('no-empty-title', 'dataLoc', ['message' => __('Title is required.', 'wp-nec')]);

    return true;
});

// no js users a.k.a sometimes little crooks
add_action('transition_post_status', function ($new_status, $old_status, $post) {
    $title   = $post->post_title;

    $post_types = apply_filters('wp_nec/post_types_to_bail', array_keys(get_post_types()));

    if (!in_array($post->post_type, $post_types, true)) {
        return false;
    }

    if ($new_status === 'publish' && (empty($title))) {
        wp_die(__('The post needs at least a title !', 'wp-nec'));
    }

    return true;
}, 10, 3);
