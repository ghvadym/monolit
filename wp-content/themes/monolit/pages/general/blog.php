<?php
$count = get_field('blog_posts_count', 'options');
$title = get_field('blog_title', 'options');
$blogUrl = get_field('blog_url', 'options');

$posts = getPosts([
    'post_type'   => 'articles',
    'numberposts' => $count ?: 3,
]);

if (empty($posts)) {
    return;
}
?>

<section class="blog" id="blog">

    <?php titleHtml($title); ?>

    <div class="blog_container">
        <?php foreach ($posts as $post): ?>

            <div class="container_blog_item">
                <div class="blog_img_container">
                    <a href="<?php the_permalink($post); ?>">
                        <img src="<?php echo get_the_post_thumbnail_url($post); ?>" alt="Виды промышленных полов">
                    </a>
                </div>
                <div class="blog_item_text">
                    <div class="blog_item_text_description">
                        <a href="<?php the_permalink($post); ?>">
                            <?php echo $post->post_title; ?>
                        </a>
                    </div>
                    <div class="blog_item_info">
                        <p class="blog_item_data">
                            <?php echo date_i18n('d F Y г. H:i'); ?>
                        </p>
                        <a class="blog_item_button" href="<?php the_permalink($post); ?>">
                            <?php _e('Перейти', 'monolit'); ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    </div>

    <a href="<?php echo $blogUrl ?: '/articles/'; ?>" class="blog_button">
        <?php _e('Просмотреть', 'monolit'); ?>
    </a>

</section>