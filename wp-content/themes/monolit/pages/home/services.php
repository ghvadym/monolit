<?php
if (empty($services)) {
    return;
}
?>

<section class="services">
    <div class="container">
        <?php foreach ($services as $index => $service): ?>
            <div class="servises_item_container">
                <div class="services_item">
                    <div class="services_title_container">
                        <h2 class="services_item_title">
                            <?php echo $service->name; ?>
                        </h2>
                    </div>
                    <ul class="servises_list">
                        <?php get_template_part_var('pages/parts/taxonomy-posts', ['term' => $service]); ?>
                    </ul>
                    <a href="<?php echo get_term_link($service); ?>" class="servises_button">
                        <?php _e('Перейти', 'monolit'); ?>
                    </a>
                </div>
            </div>
            <?php if ($index == 1):
                get_template_part_var('pages/parts/types-posts');
            endif; ?>
        <?php endforeach; ?>
    </div>
</section>