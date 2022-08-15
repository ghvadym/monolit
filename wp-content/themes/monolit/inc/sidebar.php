<?php

add_action('widgets_init', 'custom_sidebar');
function custom_sidebar()
{
    register_my_sidebar('Our Services', 'our-services');
    register_my_sidebar('Our Company', 'our-company');
}

function register_my_sidebar($title, $slug)
{
    register_sidebar([
        'name'          => $title,
        'id'            => $slug,
        'description'   => '',
        'class'         => '',
        'before_widget' => '<div class="footer_menu">',
        'after_widget'  => "</div>\n",
        'before_title'  => '<h2 class="footer_menu_title">',
        'after_title'   => "</h2>\n",
    ]);
}