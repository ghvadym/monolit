<?php
if (empty($services)) {
    return;
}
?>

<section class="main">
    <div class="carousel" data-carousel="">
        <button class="carousel-button prev" data-carousel-button="prev"></button>
        <button class="carousel-button next" data-carousel-button="next"></button>
        <div class="slides" data-slides="">
            <?php foreach ($services as $index => $service):
                $termFields = get_fields('term_' . $service->term_id); ?>
                <div class="slide" <?php echo $index == 0 ? 'data-active="true"' : ''; ?>>
                    <?php if ($termFields['service_image']): ?>
                        <img src="<?php echo $termFields['service_image']; ?>" alt="<?php echo $service->name; ?>">
                    <?php endif; ?>
                    <div class="slide_container">
                        <div class="main_screen_title">
                            <?php echo $service->name; ?>
                        </div>
                        <p class="main_screen_suptitle">
                            <?php echo $service->description; ?>
                        </p>
                        <a href="<?php echo get_term_link($service); ?>" class="main_screen_button">
                            <?php _e('Просмотреть', 'monolit'); ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php titleHtml(get_the_title()); ?>