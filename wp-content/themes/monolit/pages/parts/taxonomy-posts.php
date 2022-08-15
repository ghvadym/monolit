<?php
if (empty($term)) {
    return;
}
$taxObject = get_taxonomy($term->taxonomy);

$posts = get_posts([
    'post_type'        => $taxObject->object_type,
    'orderby'          => 'menu_order',
    'order'            => 'asc',
    'post_status'      => 'publish',
    'numberposts'      => -1,
    'suppress_filters' => false,
    'tax_query'        => [
        [
            'taxonomy' => $term->taxonomy,
            'field'    => 'term_id',
            'terms'    => $term->term_id,
        ],
    ],
]);

if (empty($posts)) {
    return;
}

foreach ($posts as $post): ?>
    <li>
        <a href="<?php the_permalink($post); ?>">
            <?php echo $post->post_title; ?>
        </a>
    </li>
<?php endforeach;


