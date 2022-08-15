<?php

function createPostTypes()
{
    createPostType('services', [
        'supports' => ['title', 'editor', 'page-attributes'],
        'labels'   => [
            'name'          => __('Наши услуги', 'monolit'),
            'singular_name' => __('Услуга', 'monolit'),
            'add_new'       => __('Добавить новую Услугу', 'monolit'),
            'add_new_item'  => __('Добавление новой Услуги', 'monolit'),
            'view_item'     => __('Просмотреть услугу', 'monolit'),
            'search_items'  => __('Найти услугу', 'monolit'),
            'not_found'     => __('Услуга не найдена', 'monolit'),
            'menu_name'     => __('Наши услуги', 'monolit'),
        ],
        'rewrite' => [
            'slug'       => 'services/%services_cat%',
            'with_front' => false,
        ],
    ]);

    createPostType('types', [
        'supports' => ['title', 'editor', 'page-attributes'],
        'labels'   => [
            'name'          => __('Типы помещений', 'monolit'),
            'singular_name' => __('Помещение', 'monolit'),
            'add_new'       => __('Добавить новое Помещение', 'monolit'),
            'add_new_item'  => __('Добавление нового Помещения', 'monolit'),
            'view_item'     => __('Просмотреть помещение', 'monolit'),
            'search_items'  => __('Найти помещение', 'monolit'),
            'not_found'     => __('Помещение не найдена', 'monolit'),
            'menu_name'     => __('Типы помещений', 'monolit'),
        ],
    ]);

    createPostType('articles', [
        'labels'   => [
            'name'          => __('Блог', 'monolit'),
            'singular_name' => __('Блог', 'monolit'),
            'add_new'       => __('Добавить новый Пост', 'monolit'),
            'add_new_item'  => __('Добавление нового Поста', 'monolit'),
            'view_item'     => __('Просмотреть Пост', 'monolit'),
            'search_items'  => __('Найти Пост', 'monolit'),
            'not_found'     => __('Пост не найден', 'monolit'),
            'menu_name'     => __('Блог', 'monolit'),
        ],
    ]);

    createTaxonomy('services_categories', 'services', [
        'labels'  => [
            'singular_name'     => __('Категории услуг', 'monolit'),
            'search_items'      => __('Найти категорию', 'monolit'),
            'all_items'         => __('Все категории', 'monolit'),
            'parent_item'       => __('Родительская категория', 'monolit'),
            'parent_item_colon' => __('Родительская категория:', 'monolit'),
            'edit_item'         => __('Редактировать категорию', 'monolit'),
            'update_item'       => __('Обновить категорию', 'monolit'),
            'add_new_item'      => __('Добавить новую категорию', 'monolit'),
            'new_item_name'     => __('Имя новой категории', 'monolit'),
            'menu_name'         => __('Категории услуг', 'monolit'),
        ],
    ]);
}

function createPostType($postType, $args = [])
{
    $args = array_merge([
        'public'        => true,
        'show_ui'       => true,
        'has_archive'   => true,
        'menu_position' => 20,
        'hierarchical'  => true,
        'supports'      => ['title', 'editor', 'thumbnail'],
    ], $args);

    register_post_type($postType, $args);
}

function createTaxonomy($taxonomy, $postType, $args = [])
{
    $args = array_merge([
        'description'  => '',
        'public'       => true,
        'hierarchical' => true,
        'has_archive'  => true,
    ], $args);

    register_taxonomy($taxonomy, $postType, $args);
}

add_action('init', 'createPostTypes');