<?php

add_action('wp_enqueue_scripts', 'register_scripts');
add_action('after_setup_theme', 'theme_setup_settings');
add_action('admin_menu', 'remove_default_post_types');

add_filter('upload_mimes', 'upload_allow_types');
add_filter('show_admin_bar', '__return_false');
add_filter('gutenberg_use_widgets_block_editor', '__return_false');
add_filter('use_widgets_block_editor', '__return_false');
add_filter('post_type_link', 'change_post_type_link', 10, 4);

function register_scripts()
{
    theme_styles();
    theme_scripts();
}

function theme_styles()
{
    wp_enqueue_style('monolit-main-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('monolit-style', get_template_directory_uri() . '/assets/css/styles.css');
    wp_enqueue_style('monolit-app-style', get_template_directory_uri() . '/assets/css/app.css');
}

function theme_scripts()
{
    wp_enqueue_script('monolit-slider-script', get_template_directory_uri() . '/assets/js/slick-slider.js', ['jquery'], time(), true);
    wp_enqueue_script('monolit-script', get_template_directory_uri() . '/assets/js/scripts.js', ['jquery'], time(), true);
    wp_enqueue_script('monolit-main-script', get_template_directory_uri() . '/assets/js/app.js', ['jquery'], time(), true);
    wp_enqueue_script('monolit-jquery-script', get_template_directory_uri() . '/assets/js/jquery.js', ['jquery'], time(), true);

    wp_localize_script('monolit-main-script', 'myajax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
    ]);
}

function upload_allow_types($types)
{
    $types['svg'] = 'image/svg+xml';
    $types['webp'] = 'image/webp';
    return $types;
}

function theme_setup_settings()
{
    register_nav_menus([
        'main_header'  => 'Main Header',
        'our_services' => 'Our Services',
        'our_company'  => 'Our Company',
    ]);

    add_theme_support('post-thumbnails', ['articles']);
    add_theme_support('custom-logo', [
        'unlink-homepage-logo' => true,
    ]);

    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title' => __('Общие настройки', 'monolit'),
            'menu_title' => __('Общие настройки', 'monolit'),
            'menu_slug'  => 'theme-general-settings',
            'capability' => 'edit_posts',
            'redirect'   => false,
        ]);
    }
}

function remove_default_post_types()
{
    remove_menu_page('edit.php');
    remove_menu_page('edit-comments.php');
}

function change_post_type_link($postLink, $post)
{
    if ($post->post_type !== 'services') {
        return $postLink;
    }

    if (!strpos($postLink, '%services_cat%')) {
        return $postLink;
    }

    $terms = get_the_terms($post->ID, 'services_categories');

    if (!empty($terms)) {
        return str_replace('%services_cat%', $terms[0]->slug, $postLink);
    } else {
        return str_replace('%services_cat%', 'category', $postLink);
    }
}
