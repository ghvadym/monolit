<?php
$title = get_field('partners_title', 'options');
$images = get_field('partners_images', 'options');

if (empty($images)) {
    return;
}
?>

<section class="our_partners">
    <?php if ($title): ?>
        <h2 class="our_partners_title">
            <?php echo $title; ?>
        </h2>
    <?php endif; ?>

    <div class="">
        <div class="carousel carousel_partners" data-partners="">
            <button class="carousel-button prev partners_prev_btn" data-partners-button="prev"></button>
            <button class="carousel-button next partners_next_btn" data-partners-button="next"></button>
            <div class="slides" data-slides="">

                <?php foreach ($images as $index => $image): ?>
                    <div class="slidePartners">
                        <div class="partners_slide_position">
                            <div class="prev_slide-partners">
                                <a class="our_partners_item_img_container">
                                    <img src="<?php echo $images[--$index]; ?>" alt="">
                                </a>
                            </div>
                            <div class="current_slide-partners">
                                <a class="our_partners_item_img_container">
                                    <img src="<?php echo $image; ?>" alt="">
                                </a>
                            </div>
                            <div class="next_slide-partners">
                                <a class="our_partners_item_img_container">
                                    <img src="<?php echo $images[++$index]; ?>" alt="">
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
</section>