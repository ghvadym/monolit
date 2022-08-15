<?php
$typesObject = get_post_type_object('types');
$posts = get_posts([
    'post_type'        => 'types',
    'orderby'          => 'menu_order',
    'order'            => 'asc',
    'post_status'      => 'publish',
    'numberposts'      => -1,
    'suppress_filters' => false,
]);

if (empty($posts)) {
    return;
}
?>
<div class="servises_item_container">
    <div class="services_item">
        <div class="services_title_container">
            <h2 class="services_item_title">
                <?php echo $typesObject->labels->name; ?>
            </h2>
        </div>

        <ul class="servises_list">
            <?php foreach ($posts as $post): ?>
                <li>
                    <a href="<?php the_permalink($post); ?>">
                        <?php echo $post->post_title; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <a href="" class="servises_button">
            <?php _e('Перейти', 'monolit'); ?>
        </a>
    </div>
</div>