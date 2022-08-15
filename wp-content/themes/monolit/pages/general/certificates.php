<?php
$title = get_field('cert_title', 'options');
$images = get_field('cert_gallery', 'options');

if (empty($images)) {
    return;
}
?>

<section class="our_certif">
    <?php if ($title): ?>
        <h2 class="our_certif_title">
            <?php echo $title; ?>
        </h2>
    <?php endif; ?>

    <div class="">
        <div class="carousel carousel_certif" data-certif="">
            <button class="carousel-button prev certif_prev_btn" data-certif-button="prev"></button>
            <button class="carousel-button next certif_next_btn" data-certif-button="next"></button>
            <div class="slides" data-slides="">

                <?php foreach ($images as $index => $image): ?>
                    <div class="slideCertif">
                        <div class="certif_slide_position">
                            <div class="prev_slide-certif">
                                <a class="our_certif_item_img_container">
                                    <img src="<?php echo $images[--$index]; ?>" alt="">
                                </a>
                            </div>
                            <div class="current_slide-certif">
                                <a class="our_certif_item_img_container">
                                    <img src="<?php echo $image; ?>" alt="">
                                </a>
                            </div>
                            <div class="next_slide-certif">
                                <a class="our_certif_item_img_container">
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