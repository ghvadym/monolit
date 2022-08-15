<?php

if (!function_exists('dd')) {
    function dd()
    {
        echo '<pre>';
        array_map(function ($x) {
            var_dump($x);
        }, func_get_args());
        die;
    }
}

function get_template_part_var($template, $data = [])
{
    extract($data);
    require locate_template($template . '.php');
}

function getPosts($params = []): array
{
    $args = array_merge([
        'orderby'          => 'date',
        'order'            => 'desc',
        'post_status'      => 'publish',
        'suppress_filters' => false,

    ], $params);

    return get_posts($args);
}

function getImageThumbnail($id = 0, $size = 'large'): string
{
    if (!$id) {
        return '';
    }

    $imgId = get_post_meta($id, 'adv_attachment_id', true) ?? '';

    return wp_get_attachment_image($imgId, $size) ?: defaultImage();
}

function defaultImage(): string
{
    $path = get_template_directory_uri() . '/assets/img/default-image.png';
    return sprintf('<img src="%s" alt="Default image">', $path);
}

function footerWidgets()
{
    $widgets = [
        'our-services',
        'our-company',
    ];
    foreach ($widgets as $widget) {
        if (is_active_sidebar($widget)) {
            dynamic_sidebar($widget);
        }
    }
}

function titleHtml($text = '')
{
    if (!$text) {
        return;
    } ?>

    <div class="title_container">
        <span class="title_line"></span>
        <h2 class="title"><?php echo $text; ?></h2>
        <span class="title_line"></span>
    </div>

    <?php
}